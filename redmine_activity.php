<?php

$URL = '';
$MAX_ENTRIES = 15;

$STATUS_COLOURS = array(
	'New'         => 'blue',
	'In Progress' => 'yellow',
	'Feedback'    => 'orange',
	'Resolved'    => 'light green',
	'Closed'      => 'green',
	'Rejected'    => 'red',
);

$xml = parse_feed($URL);

echo '${color1}Redmine activity$color',
	'${alignr}${color2}Last updated:$color ',
	date('F j, Y @ g:i A', strtotime($xml->updated)), "\n";

for ($i = 0; $i < count($xml->entry) AND $i < $MAX_ENTRIES; $i++)
{
	output_entry($xml->entry[$i]);
}

function parse_feed($url)
{
	$handle = curl_init($url);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
	$raw_xml = curl_exec($handle);
	curl_close($handle);

	return new SimpleXMLElement($raw_xml);
}

function output_entry(SimpleXMLElement $entry)
{
	global $STATUS_COLOURS;

	$date = date('g:i A', strtotime($entry->updated));
	$author = escape(truncate($entry->author->name, 20));

	// Get revision/issue number
	preg_match('#(issues|revisions)/(\w+)#', $entry->id, $id_parts);
	list(, $type, $number) = $id_parts;

	// Get project
	preg_match('/^(.+?) - (\w+) ([^:]+): (.+)/', $entry->title, $title_parts);
	$project = escape(truncate($title_parts[1], 30));

	echo '${color #222222}$hr', "\n",
		'${font Ubuntu:size=10}${color1}', $date, '$color$font',
		'${voffset -3}',
		'${goto 280}${color2}Project: ',
		'${color ', colourize($project), '}', $project, '$color',
		'${goto 500}${color2}Author: ',
		'${color ', colourize($author), '}', $author, '$color';

	colourize($project);

	switch ($type)
	{
		case 'issues':
			$tracker = $title_parts[2];
			$summary = $title_parts[4];
			$comment = comment($entry->content);

			echo '${goto 70}${color2}', $tracker, ' \#${color}', $number;

			// Sometimes status is listed in paretheses after issue number
			if (preg_match('/\(([^)]+)\)/', $title_parts[3], $issue_parts))
			{
				$status = $issue_parts[1];

				echo '${goto 170}${color2}Status:$color ';

				if (isset($STATUS_COLOURS[$status]))
				{
					echo '${color '.$STATUS_COLOURS[$status].'}';
				}

				echo escape($status), '$color';
			}

			echo "\n", '${offset 70}', escape($summary), "\n";

			if (strlen($comment))
			{
				echo '${font Ubuntu:size=7}${offset 70}${color3}', $comment,
					'$color$font', "\n";
			}
		break;
		case 'revisions':
			// Redmine truncates the commit message
			$message = comment($title_parts[4]);

			echo '${goto 70}${color2}Revision$color ', truncate($number, 8), "\n",
				'${font Ubuntu:size=7}${offset 70}${color3}', $message,
				'$color$font', "\n";
		break;
		default:
			echo 'Unknown update';
		break;
	}

	echo '${voffset -5}';
}

function escape($text)
{
	$text = preg_replace('/#/', '\\\\#', $text);
	$text = preg_replace('/\$/', '$$', $text);
	return $text;
}

function truncate($text, $length)
{
	if (strlen($text) > $length)
	{
		return substr($text, 0, $length-1).'…';
	}

	return $text;
}

function comment($text)
{
	$text = html_entity_decode(trim($text));
	$text = str_replace('<br />', ' ', $text);
	$text = strip_tags($text);
	$text = preg_replace('/[\n\r\t]/', ' ', $text);
	$text = preg_replace('/  /', ' ', $text);
	$text = escape(truncate($text, 200));
	$text = wordwrap($text, 125, "\n".'${offset 70}');

	return $text;
}

function colourize($text)
{
	$rgb = array();
	$hash = md5($text);

	for ($i = 0; $i < 3; $i++)
	{
		// Create value from 64 to 255, based on part of hashed text
		$num = (int) (hexdec(substr($hash, $i * 5, 2)) / 256 * 192 + 64);
		$rgb[] = sprintf('%02s', dechex($num));
	}

	return '#'.implode('', $rgb);
}
