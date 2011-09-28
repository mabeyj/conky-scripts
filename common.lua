---
-- Utility functions.
--
-- @author Jimmy Mabey

---
-- Outputs current uptime.
--
-- @return Conky string
function conky_uptime()
	return '${voffset 4}${color1}Uptime$color'
	     ..'${goto 60}${font 16bit:size=12}'..pad('${uptime}', 14)..'$font'
end

---
-- Outputs CPU usage.
--
-- @return Conky string
function conky_cpus()
	local start = 40
	local position = start

	local output = '${voffset 9}${color1}CPU$color${voffset -9}'
	             ..'${font 16bit:size=12}'

	-- Read number of CPUs
	local command = io.popen('grep -c processor /proc/cpuinfo')
	local num_cpus = command:read('*a')
	command:close()

	for cpu = 1, num_cpus
	do
		output = output..'${goto '..position..'}${voffset 9}'
		       ..pad('${cpu cpu'..cpu..'}', 3)..'%${voffset -9}$color4'
		       ..'${goto '..(position + 65)..'}'
		       ..'${cpugraph cpu'..cpu..' 20,100 000000 0092e6 -t}$color'
	
		position = position + 175

		if (position > 500)
		then
			-- Start new row
			output = output..'\n${voffset -8}'
			position = start
		end
	end

	return output..'$font'
end

---
-- Outputs memory usage.
--
-- @return Conky string
function conky_memory()
	return '${color1}Memory$color'
	     ..'${goto 60}${font 16bit:size=12}${mem}/${memmax}'
	     ..'$alignr'..pad('${memperc}', 3)..'% $font'
	     ..'$color4${voffset -10}${memgraph 20,200 000000 00e675 -t}$color'
end

---
-- Outputs load averages.
--
-- @return Conky string
function conky_load()
	-- Not sure why graph needs a voffset
	return '${color1}Load$color'
	     ..'${goto 60}${font 16bit:size=12}'..pad('${loadavg 1}', 6)..'$font 1'
	     ..'${voffset -2}${font 16bit:size=12}'..pad('${loadavg 1}', 6)..'$font 5'
	     ..'${voffset -2}${font 16bit:size=12}'..pad('${loadavg 2}', 6)..'$font 15'
	     ..'$color4${offset 5}${voffset -10}$alignr${loadgraph 20,200 000000 d50000 -t}'
	     ..'$color'
end

---
-- Pads and right-aligns a parsed Conky string to the given number of spaces.
--
-- @param command      Conky string
-- @param num_spacing  Amount of padding
-- @return String
function pad(command, num_spaces)
	return string.format('%'..num_spaces..'s', conky_parse(command))
end

-- Conky seems to have a bug where certain values are not initialized when the
-- graphs are drawn the first time, causing a crash.  Calling those functions
-- with graphs here seems to fix it.
conky_cpus()
conky_memory()
conky_load()
