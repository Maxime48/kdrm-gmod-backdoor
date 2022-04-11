<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;

/**
 * Controller handling banning/promoting/unbanning/demoting
 *
 * Note: Not yet documented, will get a rework soon after the implementation of the major features
 *
 */
class usersModActions extends Controller
{

    /**
     * Returns the name corresponding to the user's rank
     *
     * @param User $u
     * @return string
     */
    private function admin_toString($u){
        return (
        $u->admin == -1 ? 'Banned' :
            (
            $u->admin == 0 ? 'User' :
                (
                $u->admin == 1 ? 'Moderator' :
                    (
                    $u->admin == 2 ? 'Admin' : 'Undefined'
                    )
                )
            )
        );
    }

    private function isadmin($id, $r, $mode = 0)
    {
        //old function that needs rework

        // 0: check admin
        // 1: immunity
        // 2, 3: bigboss check
        $user = User::findOrFail($id);
        switch ($mode) {
            case 0:
                return in_array($user->admin, array(1, 2)); //user is the user corresponding to the id passed in parameter
            case 1:
                return $user->admin == 2; //user is the user corresponding to the id passed in parameter
            case 2:
                return $_ENV['MASTER_EMAIL'] == $r->user()->email; // $r is the request object
            case 3:
                return $_ENV['MASTER_EMAIL'] == $r->email; // $r is the user object
        }
    }

    public function ban($id, Request $request)
    {
        // ( not immune or mode 2 force ) and not targetofban(mode2)
        $u = User::findOrFail($id);
        if (
            (
                !$this->isadmin($id, $request, 1) and
                !$this->isadmin($id, $u, 3) and
                $request->user()->admin > $u->admin
            ) or (
                $this->isadmin($id, $request, 2)
            )
        ) {
            $u = User::findOrFail($id);
            $previousrank = $this->admin_toString($u); //gets rank before ban

            $u->admin = -1;
            $u->infection_key= 'none';
            $u->update();

            $log = new Logs();
            $log->level = 'critical';
            $log->message = 'User ('.$previousrank.') Banned by '. $request->user()->name. '('.$this->admin_toString($request->user()).')' . '| id['.$request->user()->id.'] ';
            $log->user_id = $id;
            $log->save();

            return back()->with('status', 'The user: ' . $u->name . " has been banned 🗿");
        }
        return back()->with('status', 'Error while banning the user');
    }

    public function unban($id, Request $request)
    {
        $u = User::findOrFail($id);
        if (
            $u->admin == -1 and
            $request->user()->admin > $u->admin
        ) {
            $u->admin = 0;
            $u->update();

            $log = new Logs();
            $log->level = 'warning';
            $log->message = 'User Unbanned by '. $request->user()->name. '('.$this->admin_toString($request->user()).')' . ' id['.$request->user()->id.'] ';
            $log->user_id = $id;
            $log->save();

            return back()->with('status', 'The user: ' . $u->name . " has been unbanned 🗿");
        }
        return back()->with('status', 'Error while unbanning the user');
    }

    public function promote($id, Request $request)
    {
        $u = User::findOrFail($id);
        if (
            (
                in_array($u->admin, array(1, 0)) and
                $request->user()->admin > $u->admin + 1
            ) or (
                $this->isadmin($id, $request, 2) and
                $u->admin != 2
            )
        ) {
            $u->admin++;
            $u->update();

            $log = new Logs();
            $log->level = 'warning';
            $log->message = 'User Promoted by '. $request->user()->name. '('.$this->admin_toString($request->user()).')' . ' id['.$request->user()->id.'] to '.$this->admin_toString($u);
            $log->user_id = $id;
            $log->save();

            return back()->with('status', 'The user: ' . $u->name . " has been promoted 🗿");
        }
        return back()->with('status', 'Error while promoting the user');
    }

    public function promotedown($id, Request $request)
    {
        $u = User::findOrFail($id);
        $overseer = $this->isadmin($id, $request, 2);
        if (
            (
                $overseer or
                !in_array($u->admin, array(2, 0))
            ) and (
                $request->user()->admin > $u->admin or
                $overseer
            ) and (
                $u->admin != -1
            )
        ) {
            $u->admin--;
            $u->update();

            $log = new Logs();
            $log->level = 'critical';
            $log->message = 'User Demoted by '. $request->user()->name. '('.$this->admin_toString($request->user()).')' . ' id['.$request->user()->id.'] to '.$this->admin_toString($u);
            $log->user_id = $id;
            $log->save();

            return back()->with('status', 'The user: ' . $u->name . " has been demoted 🗿");
        }
        return back()->with('status', 'Error while demoting the user');
    }

}
