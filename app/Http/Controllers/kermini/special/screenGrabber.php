<?php

namespace App\Http\Controllers\kermini\special;

use App\Http\Controllers\Controller;
use App\Models\servers;
use Illuminate\Http\Request;
use xPaw\SourceQuery\SourceQuery;

class screenGrabber extends Controller
{

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

}
