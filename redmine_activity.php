<?php

$URL = '';
$MAX_ENTRIES = 15;

$xml = parse_feed($URL);

echo '${color1}Redmine activity$color', "\n",
	'${color2}Last updated:$color ',
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

	// ${goto} lightens the outline for some reason
	echo '$hr', "\n", $date,
		'${tab 35}${color2}Project:$color ', escape($project),
		'${tab 175}${color2}Author:$color ', escape($entry->author->name), '${tab 100}';

	switch ($type)
	{
		case 'issues':
			$tracker = $title_parts[2];
			$summary = $title_parts[4];

			$comment = sanitize($entry->content, 100);

			echo '${color2}Issue \#${color}', $number,  "\n",
				escape($summary);

			// Sometimes status is listed in paretheses after issue number
			if (preg_match('/\(([^)]+)\)/', $title_parts[3], $issue_parts))
			{
				$status = $issue_parts[1];
				echo '${tab 500 0}${color2}Status:$color ', escape($status);
			}

			echo "\n";

			if (strlen($comment))
			{
				echo '${color3}', $comment, '$color', "\n";
			}
		break;
		case 'revisions':
			// Redmine truncates the commit message
			$message = $title_parts[4];

			echo '${color2}Revision$color ', $number, "\n",
				'${color3}', escape($message), '$color', "\n";
		break;
		default:
			echo 'Unknown update';
		break;
	}
}

function escape($text)
{
	$text = preg_replace('/#/', '\\\\#', $text);
	$text = preg_replace('/\$/', '$$', $text);
	return $text;
}

function sanitize($text, $length)
{
	$text = html_entity_decode(trim($text));
	$text = str_replace('<br />', ' ', $text);
	$text = strip_tags($text);
	$text = preg_replace('/[\n\r\t]/', ' ', $text);
	$text = preg_replace('/  /', ' ', $text);

	if (strlen($text) > $length)
	{
		$text = substr($text, 0, $length-1).'…';
	}

	return escape($text);
}
