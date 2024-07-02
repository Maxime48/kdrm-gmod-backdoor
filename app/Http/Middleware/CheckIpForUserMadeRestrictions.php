<?php

namespace App\Http\Middleware;

use App\Models\IpBan_Servers;
use App\Models\Logs;
use App\Models\PSCRGRB_player_requests;
use App\Models\Scrgb_Image_Requests;
use App\Models\servers;
use App\Models\User;
use Closure;
use DateTime;
use Illuminate\Http\Request;

class CheckIpForUserMadeRestrictions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = null;

        //possible parameters:
        // $key in parameter
        // $rkey in parameter
        // $imagekey in parameter

        //usages of those parameters:
        // $key: infection key in User model and Scrgb_Image_Requests::where('SCRGBimageKey', $key)
        // $rkey: PSCRGRB_player_requests::where('PlayerRequestKey',$rkey)
        // $imagekey: Scrgb_Image_Requests::where('SCRGBimageKey', $imagekey)



        if ($request->has('key')) {
            // Identify user based on the infection key sent as a parameter
            $infectionKey = $request->input('key');
            $user = User::where('infectionKey', $infectionKey)->first();

            if(!$user){
                $imageRequest = Scrgb_Image_Requests::where('SCRGBimageKey', $infectionKey)->first();
                if($imageRequest){
                    $user = User::where('id', $imageRequest->user_id)->first();
                }
            }

        } elseif ($request->has('imagekey')) {
            // Identify user based on the image key sent as a parameter in Scrgb_Image_Requests

            $imageKey = $request->input('imagekey');
            $imageRequest = Scrgb_Image_Requests::where('SCRGBimageKey', $imageKey)->first();
            if ($imageRequest) {
                $user = User::where('id', $imageRequest->user_id)->first();
            }

        } elseif ($request->has('rkey')) {
            // Identify user based on the player request key sent as a parameter in PSCRGRB_player_requests
            $playerRequestKey = $request->input('rkey');
            $playerRequest = PSCRGRB_player_requests::where('PlayerRequestKey', $playerRequestKey)->first();
            if ($playerRequest) {
                $server = servers::where('id', $playerRequest->server_id)->first();
                if ($server) {
                    $user = User::where('id', $server->user_id)->first();
                }
            }
        }

        if ($user) {
            // Retrieve the server's or game client IP address
            $serverIp = $request->ip();

            // Retrieve the banned IPs associated with the user that are not global
            $bannedIps = IpBan_Servers::where('user_id', $user->id)
                ->where('global', false)
                ->pluck('forbiddenIp');

            foreach ($bannedIps as $bannedIp) {
                if ($this->ipMatchesPattern($serverIp, $bannedIp)) {
                    // Generate a unique mocking message for the blocked user
                    $message = $this->generateMockingMessage($serverIp);

                    $log = new Logs();
                    $log->level = 'notice';
                    $log->message = 'User-specific IP ban triggered for IP ' . $serverIp . ' with path ' . $request->path();
                    $log->user_id = $user->id;
                    $log->save();

                    // Return a response with the mocking message
                    return response()->json(['error' => $message], 403);
                }
            }
        }

        return $next($request);
    }

    /**
     * Check if the given IP matches the pattern.
     *
     * @param string $ip
     * @param string $pattern
     * @return bool
     */
    private function ipMatchesPattern(string $ip, string $pattern): bool
    {
        $ipSegments = explode('.', $ip);
        $patternSegments = explode('.', $pattern);

        foreach ($ipSegments as $index => $segment) {
            if ($patternSegments[$index] !== '*' && $patternSegments[$index] !== $segment) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate a unique mocking message for the blocked user.
     *
     * @param string $ip
     * @return string
     */
    private function generateMockingMessage(string $ip): string
    {
        $messages = [
            "Get out, bozo! Your sorry IP address ($ip) is banned.",
            "Sorry, but your pathetic IP address ($ip) has been blacklisted. Don't even bother!",
            "Access denied for IP address $ip. You've hit a wall of shame. Better luck next life.",
            "Sorry, Shmongus! Your lame IP address ($ip) is not welcome here. Run along now.",
            "GTFO! Your worthless IP address ($ip) is permanently blocked. No redemption for you!",
            "Access to this resource is forbidden for IP address $ip. No funny business allowed. We see through your BS.",
            "Nice try, but your sad IP address ($ip) is on the naughty list. No cookies for you!",
            "Sorry, but we're not serving your pathetic IP address ($ip) today. Cry about it somewhere else.",
            "Your IP address ($ip) is banned. Don't even try to come back! You're a lost cause.",
            "Listen, I don't know who you think you are, but your trash IP address ($ip) is banned. Try a VPN or something, loser.",
            "Yo, mf! Your IP address ($ip) is banned. Keep trying, I'm enjoying watching you fail.",
            "Hey, genius! Your sorry excuse for an IP address ($ip) is banned. Time to find a new hobby, maybe knitting?",
            "My n*gga, your IP address ($ip) is banned. You're not welcome here. Go back to your mom's basement.",
            "Sh*t, son! This is automatic IP ban territory. Your IP address ($ip) is banned. Don't even try to come back.",
            "*insert mocking message here blabla u ip baned* ($ip) is banned.",
            "CHTO ZA HUJNYA? Your IP address ($ip) is banned. Don't even try to come back Shmongus.",
            "YA TEBYA ZABANIL, SHMONGUS! Your IP address ($ip) is banned. Don't even try to come back.",
            "YEBAT TVOYU MAT! Your IP address ($ip) is banned. Don't even try to come back.",
            "GEROYAM SLAVA! Your IP address ($ip) is banned. Don't even try to come back.",
            "We know this is bypass-able by a proxy or vpn but we don't care. Your IP address ($ip) is banned. Don't even try to come back.",
        ];

        return $messages[array_rand($messages)];
    }
}
