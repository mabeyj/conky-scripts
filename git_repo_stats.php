<?php

require_once 'VersionControl/Git.php';

$config = json_decode(file_get_contents('git_repo_stats.json'));

if ( ! isset($config))
{
	echo "Failed to read config\n";
	exit(1);
}

echo '${color1}Git status$color', "\n";

foreach ($config->repositories as $path)
{
	$repo = new VersionControl_Git($path);

	$staged   = get_stats($repo, TRUE);
	$unstaged = get_stats($repo, FALSE);
	$total    = 0;

	foreach (array_keys($staged) as $name)
	{
		$total += $staged[$name] + $unstaged[$name];
	}

	if ($total > 0)
	{
		echo '${color #222222}${voffset -4}$hr', "\n",
			'${color1}', realpath($path), '${color2}${alignr}',
			'Branch:$color ', get_branch($repo), '$color', "\n";

		section('Staged', $staged);
		echo '${offset 20}';
		section('Unstaged', $unstaged);
		echo "\n";
	}
}

function get_stats(VersionControl_Git $repo, $staged)
{
	$output = $repo->getCommand('diff')
		->setOptions(array(
			'shortstat' => TRUE,
			'staged'    => $staged,
		))
		->execute();

	preg_match_all('/\d+/', $output, $matches);

	list($files, $insertions, $deletions) = count($matches[0])
		? $matches[0] : array(0, 0, 0);

	return array(
		'files'      => (int) $files,
		'insertions' => (int) $insertions,
		'deletions'  => (int) $deletions,
	);
}

function get_branch(VersionControl_Git $repo)
{
	$output = $repo->getCommand('branch')->execute();
	preg_match('/^\* (\w+)/', $output, $matches);

	return isset($matches[1]) ? $matches[1] : '(None)';
}

function section($name, $stats)
{
	echo '${color2}', $name, ':$color ',
		zeroes($stats['files'], 3), ' / ',
		zeroes($stats['insertions'], 4, 'green'), ' / ',
		zeroes($stats['deletions'], 4, 'red');
}

function zeroes($number, $zeroes, $colour = '')
{
	$output = sprintf("%0${zeroes}d", $number);
	$output = preg_replace('/([1-9])/', '${color '.$colour.'}$1', $output);

	return '${color #444444}'.$output.'${color}';
}
