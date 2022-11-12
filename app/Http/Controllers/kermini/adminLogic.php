<?php

namespace App\Http\Controllers\kermini;

use App\Http\Controllers\Controller;
use App\Models\global_payloads;
use App\Models\images;
use App\Models\Logs;
use App\Models\payloads;
use App\Models\servers;
use App\Models\user_payloads;
use DateTime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use mysql_xdevapi\Table;
use TimeHunter\LaravelGoogleReCaptchaV3\Validations\GoogleReCaptchaV3ValidationRule;

/**
 * Class made to handle all the logic associated with moderation actions
 */
class adminLogic extends Controller
{

    /**
     * @var int How many logs should be displayed on admin view
     */
    private $logsperpage = 5;

    /**
     * @var int How many servers should be displayed on admin view
     */
    private $serversperpage = 20;

    /**
     * @var int How many images should be displayed on admin view
     */
    private $imagesperpage = 8;

    /**
     * @var int How many payloads should be displayed on admin view
     */
    private $payloadsperpage = 5;

    //tools
    /**
     * Calculates the time elapsed between a date and the current one
     *
     * @param  string  $datetime a date
     * @param  boolean   $full change the date format (untested)
     * @return String
     */
    public static function time_elapsed_string(String $datetime, bool $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    /**
     * Gets a user's object based on an id
     *
     * @param  string  $id user's id
     * @return object user's object
     */
    public static function getUserById($id){
        return DB::table('users')->where('id', $id)->first();
    }

    /**
     * Returns the admin view for the logs by default
     *
     * @param $pageid page's number
     * @return Application|Factory|View|RedirectResponse
     */
    public function getLogs($pageid=null, Request $request){

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

        $hmlogs = Logs::count();
        $buttons = ceil($hmlogs / $this->logsperpage);

        if(
            $pageid==null
            or $pageid<1
            or $pageid > $buttons
        ){
            $pageid = 1;
        } // setting default page

        $logs = Logs::all()->reverse()
            ->splice(($pageid - 1) * $this->logsperpage, $this->logsperpage);

        return view('admin.logs', compact(
            'logs',
            'buttons'
        ));
    }

    /**
     * Returns the admin view for the servers
     *
     * @param $pageid page's number
     * @return Application|Factory|View|RedirectResponse
     */
    public function serverList($pageid=null, Request $request){

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

        $hmservers = servers::count();
        $buttons = ceil($hmservers / $this->serversperpage);

        if(
            $pageid==null
            or $pageid<1
            or $pageid > $buttons
        ){
            $pageid = 1;
        } // setting default page

        $servers = servers::all()->reverse()
            ->splice(($pageid - 1) * $this->serversperpage, $this->serversperpage);

        return view('admin.servers', compact(
            'servers',
            'buttons'
        ));
    }

    /**
     * Returns the admin view for images
     *
     * @param $pageid
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function allImages($pageid=null, Request $request){
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

        $hmimages = images::count();
        $buttons = ceil($hmimages / $this->imagesperpage);

        if(
            $pageid==null
            or $pageid<1
            or $pageid > $buttons
        ){
            $pageid = 1;
        } // setting default page

        $images = images::all()->reverse()
            ->splice(($pageid - 1) * $this->imagesperpage, $this->imagesperpage);

        //redirect to view
        return view('admin.images.dashboard', compact(
           'images',
           'buttons'
        ));
    }

    /**
     * Displays all the user-created payloads
     *
     * @param $pageid
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function allPayloads($pageid=null, Request $request){
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

        $hmpayloads = user_payloads::count();
        $buttons = ceil($hmpayloads / $this->payloadsperpage);

        if(
            $pageid==null
            or $pageid<1
            or $pageid > $buttons
        ){
            $pageid = 1;
        } // setting default page

        $payloads = user_payloads::all()->reverse()
            ->splice(($pageid - 1) * $this->payloadsperpage, $this->payloadsperpage);

        return view('admin.payload.dashboard', compact(
            'payloads',
            'buttons'
        ));

    }

    /**
     * Displays the GlobalPayloads
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

        return view('admin.payload.global', compact(
            'payloads',
            'buttons'
        ));
    }

    /**
     * Shows the global payloads creation page
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function CreateGlobalPayload(){
        return view('admin.payload.create_global_payload');
    }

    /**
     * Handles the request for creating a new global payload
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function CreateGlobalPayloadPost(Request $request){
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

            $gpayload = new global_payloads();
            $gpayload->content = $request->ccontent;
            $gpayload->description = $request->description;
            $gpayload->user_id = $request->user()->id;
            $gpayload->copies = 0;
            $gpayload->save();

            $log = new Logs();
            $log->level = 'alert';
            $log->message = $request->user()->name . ' created a global payload ('
            . Str::limit($request->description, 200, $end='...)');
            $log->user_id = $request->user()->id;
            $log->save();

        }

        return redirect()->route('GlobalPayloads');
    }

    /**
     * Handles a global payload deletion request
     *
     * @param $payloadid
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteGlobalPayload($payloadid, Request $request){
        $payload = global_payloads::where('id', $payloadid)->get();
        if(
            $payload->count() == 0
        ){
            return redirect()->back()->with(
                'status', "You can't use resources you don't have"
            );
        }

        $log = new Logs();
        $log->level = 'alert';
        $log->message = $request->user()->name . ' deleted a global payload ('
            . Str::limit($payload->first()->description, 200, $end='...)');
        $log->user_id = $request->user()->id;
        $log->save();

        $payload->first()->delete(); //delete payload
        return redirect()->route('GlobalPayloads');
    }

    /**
     * Shows the global payload edition page
     *
     * Checks if the payloadid exists
     * @param $payloadid
     * @return Application|Factory|View|RedirectResponse
     */
    public function editGlobalPayload($payloadid){
        $payload = global_payloads::where('id', $payloadid)->get();
        if(
            $payload->count() == 0
        ){
            return redirect()->back()->with(
                'status', "This payload does not exist"
            );
        }

        $payload = $payload->first();
        return view('admin.payload.editGlobalPayload', compact(
            'payload'
        ));
    }

    /**
     * Handles the request with the edited global payload data
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function editGlobalPayloadPost(Request $request){
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

            $payload = global_payloads::where('id', $request->payloadid)->get();
            if(
                $payload->count() == 0
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

            $log = new Logs();
            $log->level = 'alert';
            $log->message = $request->user()->name . ' edited a global payload ('
                . Str::limit($payload->description, 200, $end='...)');
            $log->user_id = $request->user()->id;
            $log->save();

        }

        return redirect()->route('GlobalPayloads');
    }
}
