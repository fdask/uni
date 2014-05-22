<?php
require 'header.inc.php';

$courses = getCourses();

echo "<ul id='container'>";

foreach ($courses as $course) {
	$courseid = $course['id'];

	$cr = courseRemaining($courseid);

	echo "<li>";
	echo "<h2 id='c_$courseid'>{$course['name']} ({$cr['count']} / {$cr['total']})</h2>";

	$modules = getModules($courseid);

	echo "<ul id='course_{$course['id']}'>";

	foreach ($modules as $module) {
		// have we seen everything in this module?  assume true
		$watchedall = true;

		$moduleid = $module['id'];	
		$videos = getVideos($moduleid);

		// start the output
		$video_output = "<ul id='module_{$module['id']}'>";

		foreach ($videos as $video) {
			$video_output .= "<li>";	

			if (haveWatched($video['id'])) {
				$video_output .= "<del><a href='video.php?vid={$video['id']}'>{$video['name']}</a></del>";
			} else {
				$watchedall = false;
				$video_output .= "<a href='video.php?vid={$video['id']}'>{$video['name']}</a>";
			}

			$video_output .= "</li>";
		}

		echo "<li>\n";

		$mr = moduleRemaining($module['id']);

		if ($watchedall) {
			echo "<h3 id='m_{$module['id']}'><del>{$module['name']} ({$mr['count']} / {$mr['total']})</del></h3>\n";
		} else {
			echo "<h3 id='m_{$module['id']}'>{$module['name']} ({$mr['count']} / {$mr['total']})</h3>\n";
		}

		echo $video_output;
		echo "</ul>\n";
		echo "</li>\n";
	}

	echo "</ul>";
	echo "</li>";
}

echo "</ul>";
?>	
