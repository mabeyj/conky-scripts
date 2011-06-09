<?php

$URL = '';
$MAX_ENTRIES = 15;

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
	$date = date('g:i A', strtotime($entry->updated));

	// Get revision/issue number
	preg_match('#(issues|revisions)/(\w+)#', $entry->id, $id_parts);
	list(, $type, $number) = $id_parts;

	// Get project
	preg_match('/^(.+?) - (\w+) ([^:]+): (.+)/', $entry->title, $title_parts);
	$project = $title_parts[1];

	echo '${color #222222}$hr', "\n",
		'${font Ubuntu:size=10}${color1}', $date, '$color$font',
		'${voffset -3}',
		'${goto 280}${color2}Project:$color ', escape(truncate($project, 30)),
		'${goto 500}${color2}Author:$color ', escape(truncate($entry->author->name, 20));

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
				echo '${goto 170}${color2}Status:$color ', escape($status);
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
			$message = $title_parts[4];

			echo '${color2}Revision$color ', truncate($number, 8), "\n",
				'${color3}', escape($message), '$color', "\n";
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
