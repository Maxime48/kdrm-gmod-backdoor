<?php

namespace App\Http\Middleware;

use App\Models\IpBan_Servers;
use App\Models\Logs;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckIpForGlobalRestriction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $ip = $request->ip();

        $bannedIps = IpBan_Servers::where('global', true)->pluck('forbiddenIp');

        foreach ($bannedIps as $bannedIp) {
            if ($this->ipMatchesPattern($ip, $bannedIp)) {
                // Generate a unique mocking message for the blocked user
                $message = $this->generateMockingMessage($ip);

                $log = new Logs();
                $log->level = 'notice';
                $log->message = ($user->name ?? "Unknown user") . ' was blocked ('.$ip.') via global IP ban.';
                $log->user_id = $user->id ?? null;
                $log->save();

                // Return a response with the mocking message
                return response()->json(['error' => $message], 403);
            }
        }

        return $next($request);
    }

    /**
     * Check if the given IP matches the pattern.
     *
     * @param  string  $ip
     * @param  string  $pattern
     * @return bool
     */
    private function ipMatchesPattern($ip, $pattern)
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
     * @param  string  $ip
     * @return string
     */
    private function generateMockingMessage($ip)
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
