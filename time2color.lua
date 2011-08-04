function conky_time2color()
	local day_start = os.time({
		year   = os.date('%Y'),
		month  = os.date('%m'),
		day    = os.date('%d'),
		hour   = 0,
		minute = 0,
		second = 0
	})

	local seconds = os.time() - day_start
	local color
	
	if seconds < 21600
	then
		color = gradient(seconds, 0, {57, 58, 153}, 21600, {255, 85, 0})
	elseif seconds < 43200
	then
		color = gradient(seconds, 21600, {255, 85, 0}, 43200, {255, 250, 194})
	elseif seconds < 64800
	then
		color = gradient(seconds, 43200, {255, 250, 194}, 64800, {61, 113, 255})
	else
		color = gradient(seconds, 64800, {61, 113, 255}, 86400, {57, 58, 153})
	end

	return '${color '..color..'}'
end

function gradient(seconds, start_sec, start_rgb, end_sec, end_rgb)
	local hex = '#'

	-- Adjust times to be relative to start time
	seconds = seconds - start_sec
	end_sec = end_sec - start_sec

	for i = 1, 3
	do
		local color = math.floor(
			start_rgb[i] * (end_sec - seconds) / end_sec 
			+ end_rgb[i] * seconds / end_sec
		)

		hex = hex..string.format('%02x', color)
	end

	return hex
end
