<?php

namespace App\Http\Controllers\kermini\special;

use App\Http\Controllers\Controller;
use App\Models\servers;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            $server = $server->first();

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

                $Players = $Query->GetPlayers();
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

}
