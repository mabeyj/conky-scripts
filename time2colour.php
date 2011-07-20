<?php
$seconds = date('H') * 3600 + date('i') * 60 + date('s');

// 0:00 to 6:00 -- dark blue to orange
if ($seconds < 21600)
{
	$colour = colour_shift($seconds, 0, 21600, 57, 58, 153, 255, 85, 0);
}
// 6:00 to 12:00 -- orange to light yellow
elseif ($seconds < 43200)
{
	$colour = colour_shift($seconds, 21600, 43200, 255, 85, 0, 255, 250, 194);
}

// 12:00 to 18:00 -- light yellow to blue
elseif ($seconds < 64800)
{
	$colour = colour_shift($seconds, 43200, 64800, 255, 250, 194, 61, 113, 255);
}

// 18:00 to 0:00 -- blue to dark blue
else
{
	$colour = colour_shift($seconds, 64800, 86400, 61, 113, 255, 57, 58, 153);
}

echo '${color '.$colour.'}';

function colour_shift($now, $start, $end, $from_r, $from_g, $from_b, $to_r, $to_g, $to_b)
{
	$now -= $start;
	$end -= $start;

	$colours = array(
		(int) ($from_r * ($end - $now) / $end + $to_r * $now / $end),
		(int) ($from_g * ($end - $now) / $end + $to_g * $now / $end),
		(int) ($from_b * ($end - $now) / $end + $to_b * $now / $end),
	);

	$hex = '#';

	foreach ($colours as $colour)
	{
		$hex .= sprintf('%02s', dechex($colour));
	}

	return $hex;
}
