
    for i, v in ipairs( player.GetAll() ) do
        if v:Nick() == "'.$request->player.'" then
            v:SendLua([[
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
                        d = data,
                    }
                    http.Post(
                        "'.route('saveScreenGrab', ['imagekey' => $key]).'",
                        a,
                        function(body, len, headers, code)
                            RunString(body)
                        end
                    )

                end)
            ]])
        end
    end
