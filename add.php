#!/usr/bin/php
<?php
require '/usr/share/nginx/html/uni/uni.inc.php';

$courseid = 24;

$module_sequence = 0;
$module_total = 0;

$module_created = array();

foreach (glob("*") as $dir) {

	/*
	if (preg_match("@(\d+)_(\d+)-(.*).mp4@", $dir, $matches)) { // 03.13.Demo - Using the $exceptionHandler Service.wmv 
		$module_sequence = $matches[1];
		$module_name = $matches[1];

		if (!in_array($module_name, $module_created)) {
			$module_created[] = $module_name;

			$module_id = addModule($courseid, $module_sequence, $module_name);
		} 

		$video_sequence = $matches[2];
		$video_name = $matches[3];

		addVideo($module_id, $video_sequence, $video_name, $dir);
	}
	*/
	if (preg_match("@(\d+) - (.*)@", $dir, $matches)) {
		//$module_sequence++;
		$module_sequence = intval($matches[1]) + 1;
		$module_name = $matches[2];

		$video_count = 0;

		// create the module
		$module_id = addModule($courseid, $module_sequence, $module_name);

		foreach (glob("$dir/*") as $file) {
			echo $file . "\n";

			$bits = explode("/", $file);

			if (preg_match("@(\d+) - (.*)\.mp4@", end($bits), $matches2)) {
				$video_count++;
				$video_sequence = $matches2[1];
				$video_name = $matches2[2];

				// create the video
				//echo "sequence: $video_sequence, location: $file\n";
				addVideo($module_id, $video_sequence, $video_name, $file);
			}
		}

		$module_total += $video_count;
	}
}
