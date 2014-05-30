<?php
require 'header.inc.php';

$courses = getCourses();

$courses_output = array();

foreach ($courses as $course) {
	$courseid = $course['id'];

	$str = "";

	// have we seen all the videos in this course?  assume true
	$watchedallmodules = true;

	$cr = courseRemaining($courseid);
	$modules = getModules($courseid);

	$modules_output = array();
	
	foreach ($modules as $module) {
		// have we seen everything in this module?  assume true
		$watchedallvideos = true;

		$str2 = "";

		$moduleid = $module['id'];	
		$videos = getVideos($moduleid);

		$mr = moduleRemaining($module['id']);

		$videos_output = array();
		$total_module_time = 0;
		$watched_module_time = 0;

		foreach ($videos as $video) {
			// put together an array of li's for each video
			$str3 = "";
			$str3 .= "<li>";	

			if (haveWatched($video['id'])) {
				$str3 .= "<del><a href='video.php?vid={$video['id']}'>{$video['name']} - {$video['length']}</a></del>";
				$watched_module_time += toSeconds($video['length']);
			} else {
				$str3 .= "<a href='video.php?vid={$video['id']}'>{$video['name']} - {$video['length']}</a>";
				$watchedallvideos = false;
				$watchedallmodules = false;
			}

			$total_module_time += toSeconds($video['length']);

			$str3 .= "</li>";

			$videos_output[] = $str3;
		}

		// start the module output
		$str2 .= "<li>";

		if ($watchedallvideos) {
			$str2 .= "<h3 id='m_{$module['id']}'><del>{$module['name']} ({$mr['count']} / {$mr['total']}) - (" . toTime($watched_module_time) . " / " . toTime($total_module_time) . ")</del></h3>\n";
		} else {
			$str2 .= "<h3 id='m_{$module['id']}'>{$module['name']} ({$mr['count']} / {$mr['total']})  - (" . toTime($watched_module_time) . " / " . toTime($total_module_time) . ")</h3>\n";
		}

		$str2 .= "<ul>";
		$str2 .= implode("\n", $videos_output);
		$str2 .= "</ul>";
		$str2 .= "</li>";

		$modules_output[] = $str2;
	}

	// put together the individual course
	$str .= "<li>";

	if ($watchedallmodules) {
		$str .= "<h2 id='c_$courseid'><del>{$course['name']} ({$cr['count']} / {$cr['total']})</del></h2>";
	} else {
		$str .= "<h2 id='c_$courseid'>{$course['name']} ({$cr['count']} / {$cr['total']})</h2>";
	}

	$str .= "<ul class='modules'>";
	$str .= implode("\n", $modules_output);
	$str .= "</ul>";

	$courses_output[] = $str;
}

echo "<ul id='container'>";
echo implode("\n", $courses_output);
echo "</ul>";
?>	
