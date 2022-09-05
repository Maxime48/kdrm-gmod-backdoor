<?php

namespace App\Http\Controllers\kermini\special;

use App\Http\Controllers\Controller;
use App\Models\IpBan_Servers;
use App\Models\Logs;
use App\Rules\ipv4_range;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use TimeHunter\LaravelGoogleReCaptchaV3\Validations\GoogleReCaptchaV3ValidationRule;

class IpBlocker extends Controller
{
    private $restrictions = 5;

    /**
     * Shows all the IPs blocked by the user
     *
     * @param $pageid
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function UserBlockedIps($pageid=NULL, Request $request){
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

        $hmrestrictions = IpBan_Servers::where('user_id',request()->user()->id)->count();
        $buttons = ceil($hmrestrictions / $this->restrictions);

        if(
            $pageid==null
            or $pageid<1
            or $pageid > $buttons
        ){
            $pageid = 1;
        } // setting default page

        $restrictions = IpBan_Servers::where('user_id',request()->user()->id)->get()->reverse()
            ->splice(($pageid - 1) * $this->restrictions, $this->restrictions);

        return view('ipblck.dashboard', compact(
            'restrictions',
            'buttons'
        ));
    }

    /**
     * Registers a new IP restriction
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function UserPostNew (Request $request){
        $customMessages = [
            'required' => ':attribute is required.'
        ];

        $validator = Validator::make($request->all(), [
            'ip' => new ipv4_range,
            'g-recaptcha-response' => [new GoogleReCaptchaV3ValidationRule('UserIpBlock')]
        ],$customMessages);
        if ($validator->fails()) {
            return redirect()->back()->with(
                'status', 'Query invalid'
            )->withErrors($validator);
        }else {
            if(
                IpBan_Servers::where('user_id',$request->user()->id)
                    ->where('forbiddenIp',$request->ip)
                    ->count() == 0
            ){
                $restriction = new IpBan_Servers();
                $restriction->forbiddenIp = $request->ip;
                $restriction->global = 0;
                $restriction->user_id = $request->user()->id;
                $restriction->save();
            }else{
                return redirect()->back()->with(
                    'status', "This restriction already exists"
                );
            }
        }

        return redirect()->route('UserBlockedIps');
    }

    /**
     * Displays the restriction edition page
     *
     * @param $restriction
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function UserEditRestriction($restriction, Request $request){
        $restriction = IpBan_Servers::where('id', $restriction);

        if(
            $restriction->count() == 0 or
            $restriction->first()->user_id != $request->user()->id
        ){
            return redirect()->back()->with(
                'status', "You can't use resources you don't have"
            );
        }

        $restriction = $restriction->first();
        return view('ipblck.editrestriction', compact(
            'restriction'
        ));
    }

    /**
     * Handles the restriction's new data for edition
     *
     * @param $restriction
     * @param Request $request
     * @return RedirectResponse
     */
    public function UserEditRestrictionPost($restriction, Request $request){
        $customMessages = [
            'required' => ':attribute is required.'
        ];

        $validator = Validator::make($request->all(), [
            'ip' => new ipv4_range,
            'g-recaptcha-response' => [new GoogleReCaptchaV3ValidationRule('editrestriction')]
        ],$customMessages);
        if ($validator->fails()) {
            return redirect()->back()->with(
                'status', 'Query invalid'
            )->withErrors($validator);
        }else {
            $restriction = IpBan_Servers::where('id', $restriction);

            if(
                $restriction->count() == 0 or
                $restriction->first()->user_id != $request->user()->id
            ){
                return redirect()->back()->with(
                    'status', "You can't use resources you don't have"
                );
            }

            $restriction = $restriction->first();
            $restriction->forbiddenIp = $request->ip;
            $restriction->save();

        }

        return redirect()->route('UserBlockedIps');
    }

    /**
     * Deletes the selected restriction
     *
     * @param $restriction
     * @param Request $request
     * @return RedirectResponse
     */
    public function UserDeleteRestriction($restriction, Request $request){
        $restriction = IpBan_Servers::where('id', $restriction);

        if(
            $restriction->count() == 0 or
            $restriction->first()->user_id != $request->user()->id
        ){
            return redirect()->back()->with(
                'status', "You can't use resources you don't have"
            );
        }

        $restriction->first()->delete();

        return redirect()->route('UserBlockedIps');
    }

    /**
     * Displays all the blocked IPs for admins
     *
     * @param $pageid
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function AdminBlockedIps($pageid=NULL, Request $request){
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

        $hmrestrictions = IpBan_Servers::all()->count();
        $buttons = ceil($hmrestrictions / $this->restrictions);

        if(
            $pageid==null
            or $pageid<1
            or $pageid > $buttons
        ){
            $pageid = 1;
        } // setting default page

        $restrictions = IpBan_Servers::all()->reverse()
            ->splice(($pageid - 1) * $this->restrictions, $this->restrictions);

        return view('admin.ipblck.dashboard', compact(
            'restrictions',
            'buttons'
        ));
    }

    /**
     * Handles data for a new global restriction
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function AdminPostNew(Request $request){
        $customMessages = [
            'required' => ':attribute is required.'
        ];

        $validator = Validator::make($request->all(), [
            'ip' => new ipv4_range,
            'g-recaptcha-response' => [new GoogleReCaptchaV3ValidationRule('AdminIpBlock')]
        ],$customMessages);
        if ($validator->fails()) {
            return redirect()->back()->with(
                'status', 'Query invalid'
            )->withErrors($validator);
        }else {
            if(
                IpBan_Servers::where('global','1')
                    ->where('forbiddenIp',$request->ip)
                    ->count() == 0
            ) {
                $restriction = new IpBan_Servers();
                $restriction->forbiddenIp = $request->ip;
                $restriction->global = 1;
                $restriction->user_id = $request->user()->id;
                $restriction->save();

                $log = new Logs();
                $log->level = 'critical';
                $log->message = $request->user()->name . ' added a global restriction ('.$request->ip.')';
                $log->user_id = $request->user()->id;
                $log->save();
            }
        }

        return redirect()->route('AdminBlockedIps');
    }

    public function AdminEditRestriction($restriction, Request $request){
        $restriction = IpBan_Servers::where('id', $restriction);

        if(
            $restriction->count() == 0 or
            $request->user()->admin != 2
        ){
            return redirect()->back()->with(
                'status', "Not authorized to edit this."
            );
        }

        $restriction = $restriction->first();
        return view('admin.ipblck.editrestriction', compact(
            'restriction'
        ));
    }
}
