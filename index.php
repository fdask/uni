<?php
require 'header.inc.php';

$courses = getCourses();

$courses_output = array();

foreach ($courses as $course) {
	// true if no videos in this course have been watched
	$new = true;

	$courseid = $course['id'];

	$str = "";

	// have we seen all the videos in this course?  assume true
	$watchedallmodules = true;

	$cr = courseRemaining($courseid);
	$modules = getModules($courseid);

	$modules_output = array();
	$total_course_time = 0;
	$watched_course_time = 0;
	
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
				$new = false;
			} else {
				$str3 .= "<a href='video.php?vid={$video['id']}'>{$video['name']} - {$video['length']}</a>";
				$watchedallvideos = false;
				$watchedallmodules = false;
			}

			// get video notes
			$video_note = getVideoNote($video['id']);

			if ($video_note) {
				$str3 .= "<i class='fa fa-file-o'></i>";
			}

			$total_module_time += toSeconds($video['length']);

			$str3 .= "</li>";

			$videos_output[] = $str3;
		}

		// start the module output
		$str2 .= "<li>";

		if ($watchedallvideos) {
			$str2 .= "<h3 id='m_{$module['id']}'><del>{$module['name']} ({$mr['count']} / {$mr['total']}) - (" . toTime($watched_module_time) . " / " . toTime($total_module_time) . ")</del>";
		} else {
			$str2 .= "<h3 id='m_{$module['id']}'>{$module['name']} ({$mr['count']} / {$mr['total']})  - (" . toTime($watched_module_time) . " / " . toTime($total_module_time) . ")";
		}

		// get module notes
		$module_notes = getModuleNotes($moduleid);

		if ($module_notes) {
			$str2 .= "<i class='fa fa-file-o'></i>";
		}

		$str2 .= "</h3>";
		$str2 .= "<ul>";
		$str2 .= implode("\n", $videos_output);
		$str2 .= "</ul>";
		$str2 .= "</li>";

		$modules_output[] = $str2;

		$watched_course_time += $watched_module_time;
		$total_course_time += $total_module_time;
	}

	// put together the individual course
	if ($watchedallmodules) {
		$str .= "<li class='completed'><h2 id='c_$courseid'><del>{$course['name']} ({$cr['count']} / {$cr['total']}) - (" . toTime($watched_course_time) . " / " . toTime($total_course_time) . ")</del>";
	} else {
		$str .= "<li class='" . (($new) ? 'new' : 'started') . "'><h2 id='c_$courseid'>{$course['name']} ({$cr['count']} / {$cr['total']}) - (" . toTime($watched_course_time) . " / " . toTime($total_course_time) . ")";
	}

	// get the course Notes
	$course_notes = getCourseNotes($courseid);

	if ($course_notes) {
		$str .= "<i class='fa fa-file-o'></i>";
	}

	$str .= "</h2>";

	$str .= "<ul class='modules'>";
	$str .= implode("\n", $modules_output);
	$str .= "</ul>";

	$courses_output[] = $str;
}
?>
Show
<label for='showNew'>New</label> <input type='checkbox' name='showNew' id='showNew' checked='checked'>
<label for='showStarted'>Started</label> <input type='checkbox' name='showStarted' id='showStarted' checked='checked'>
<label for='showCompleted'>Completed</label> <input type='checkbox' name='showCompleted' id='showCompleted' checked='checked'>
<?php
echo "<ul id='container'>";
echo implode("\n", $courses_output);
echo "</ul>";
?>	
