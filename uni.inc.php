<?php
mysql_connect('localhost', 'root', 'testing');
mysql_select_db('university');

function getCourses() {
	$query = "
	SELECT
		id,
		name,
		company,
		link
	FROM
		courses";

	$res = mysql_query($query);

	if (mysql_num_rows($res)) {
		$ret = array();

		while ($row = mysql_fetch_assoc($res)) {
			$ret[] = $row;
		}

		return $ret;
	}

	return false;
}

function getCourse($courseid) {
	$query = "
	SELECT
		id,
		name,
		company,
		link
	FROM
		courses
	WHERE
		id = " . intval($courseid);

	$res = mysql_query($query) or die(mysql_error());

	if (mysql_num_rows($res)) {
		$row = mysql_fetch_assoc($res);

		return $row;
	}

	return false;
}

function addModule($course_id, $module_sequence, $module_name) {
	$query = "
	INSERT INTO modules (
		courseid,
		name,
		sequence
	) VALUES (
		$course_id,
		'" . mysql_real_escape_string($module_name) . "',
		$module_sequence
	)";

	if (mysql_query($query)) {
		return mysql_insert_id();
	}

	return false;
}

function getModules($courseid) {
	$query = "
	SELECT
		id,
		courseid,
		name,
		sequence
	FROM
		modules
	WHERE
		courseid = $courseid
	ORDER BY sequence";

	$res = mysql_query($query);

	if (mysql_num_rows($res)) {
		$ret = array();

		while ($row = mysql_fetch_assoc($res)) {
			$ret[] = $row;
		}

		return $ret;
	}

	return false;
}

function getModule($moduleid) {
	$query = "
	SELECT
		id,
		courseid,
		name,
		sequence
	FROM
		modules
	WHERE
		id = $moduleid";

	$res = mysql_query($query);

	if (mysql_num_rows($res)) {
		$row = mysql_fetch_assoc($res);

		return $row;
	}

	return false;
}

function addVideo($module_id, $video_sequence, $video_name, $location) {
	$query = "
	INSERT INTO videos (
		moduleid,
		name,
		length,
		location,
		sequence
	) VALUES (
		$module_id,
		'" . mysql_real_escape_string($video_name) . "',
		null,
		'" . mysql_real_escape_string($location) . "',
		$video_sequence	
	)";

	if (mysql_query($query)) {
		return mysql_insert_id();
	}

	return false;
}

function deleteVideo($videoid) {
	$v = getVideo($videoid);

	$moduleid = $v['moduleid'];
	$sequence = $v['sequence'];

	$videos = getVideos($moduleid);

	$query = "DELETE FROM videos WHERE id = $videoid";

	if (mysql_query($query)) {
		// now we have to re-order the rest of the videos in the sequence
		foreach ($videos as $video) {
			if ($video['sequence'] > $sequence) {
				$new_seq = $video['sequence'] - 1;

				$query = "
				UPDATE
					videos
				SET
					sequence = $new_seq 
				WHERE
					id = {$video['id']}";

				mysql_query($query);
			}
		}
	}
}

function getVideos($moduleid) {
	$query = "
	SELECT
		v.id,
		v.moduleid,
		v.name,
		v.length,
		v.location,
		v.sequence,
		bm.time
	FROM
		videos v
	LEFT JOIN bookmarks bm ON (v.id = bm.videoid)
	WHERE
		v.moduleid = $moduleid
	ORDER BY sequence";

	$res = mysql_query($query);

	if (mysql_num_rows($res)) {
		$ret = array();

		while ($row = mysql_fetch_assoc($res)) {
			$ret[] = $row;
		}

		return $ret;
	}

	return false;
}

function getVideo($videoid) {
	$query = "
	SELECT
		v.id,
		v.moduleid,
		v.name,
		v.length,
		v.location,
		v.sequence,
		m.name as modulename,
		m.id as moduleid,
		c.name as coursename,
		c.id as courseid
	FROM
		videos v
	INNER JOIN
		modules m ON (v.moduleid = m.id)
	INNER JOIN
		courses c ON (m.courseid = c.id)
	WHERE
		v.id = $videoid";

	$res = mysql_query($query);

	if (mysql_num_rows($res)) {
		$row = mysql_fetch_assoc($res);

		return $row;
	}

	return false;
}

function getVideoObject($videoid) {
	$query = "
   SELECT
      v.id,
      v.moduleid,
      v.name,
      v.length,
      v.location AS loc,
      v.sequence,
      m.name AS modulename,
      m.id AS moduleid,
      c.name AS coursename,
      c.id AS courseid,
		w.date AS lastwatch,
		b.time AS bookmark,
		n.note AS note
   FROM
      videos v
   INNER JOIN
      modules m ON (v.moduleid = m.id)
   INNER JOIN
      courses c ON (m.courseid = c.id)
	LEFT JOIN watched w ON (w.videoid = v.id)
	LEFT JOIN bookmarks b ON (b.videoid = v.id)
	LEFT JOIN notes n ON (n.videoid = v.id)
   WHERE
      v.id = $videoid
	GROUP BY v.id";

	$res = mysql_query($query) or die(mysql_error());

	if (mysql_num_rows($res) > 0) {
		$row = mysql_fetch_assoc($res);
		$row['navNext'] = getNextVideo($videoid);
		$row['navPrev'] = getPrevVideo($videoid);
		$row['last'] = lastInModule($videoid);

		return $row;
	}

	return false;
}

function getNextVideo($videoid) {
	$video = getVideo($videoid);

	// see if there is video with one up in the sequence
	$query = "
	SELECT
		id
	FROM
		videos
	WHERE
		moduleid = {$video['moduleid']} AND
		sequence = {$video['sequence']} + 1"; 

	$res = mysql_query($query) or die(mysql_error());

	if (mysql_num_rows($res)) {
		$row = mysql_fetch_assoc($res);
	
		return $row['id'];
	}

	// if not, see if there is a next module we can take the first video from
	$module = getModule($video['moduleid']);

	$query = "
	SELECT
		id,
		courseid
	FROM
		modules
	WHERE
		sequence = {$module['sequence']} + 1 AND
		courseid = {$module['courseid']}";

	$res = mysql_query($query);

	if (mysql_num_rows($res)) {
		$row = mysql_fetch_assoc($res);
		$moduleid = $row['id'];
		$courseid = $row['courseid'];

		$query = "
		SELECT
			id
		FROM
			videos
		WHERE
			moduleid = $moduleid AND
			sequence = 1";

		$res = mysql_query($query) or die(mysql_error());
		
		if (mysql_num_rows($res)) {
			$row = mysql_fetch_assoc($res);

			return $row['id'];
		}
	}

	return false;
}

function getPrevVideo($videoid) {
	$video = getVideo($videoid);

	// if we are are greater than one in sequence, return the prior video id
	if ($video['sequence'] > 1) {
		$query = "
		SELECT
			id
		FROM
			videos
		WHERE
			moduleid = {$video['moduleid']} AND
			sequence = {$video['sequence']} - 1";

		$res = mysql_query($query);

		if (mysql_num_rows($res)) {
			$row = mysql_fetch_assoc($res);
	
			return $row['id'];
		}
	} else {
		// see if we have a prior module to pull a video from
		$module = getModule($video['moduleid']);

		if ($module['sequence'] > 1) {
			// see if there is a prior module
			$query = "
			SELECT
				id
			FROM
				modules
			WHERE
				courseid = {$module['courseid']} AND
				sequence = {$module['sequence']} - 1";
	
			$res = mysql_query($query);

			if (mysql_num_rows($res)) {
				$row = mysql_fetch_assoc($res);

				$moduleid = $row['id'];

				$videos = getVideos($moduleid);

				// find the highest sequence number in the videos
				$high = 0;
				$id = 0;

				foreach ($videos as $video) {
					if ($video['sequence'] > $high) {
						$high = $video['sequence'];
						$id = $video['id'];
					}
				}

				return $id;
			}
		}
	}

	return false;
}

function recordViewing($videoid) {
	$query = "
	INSERT INTO watched (
		videoid,
		date
	) VALUES (
		$videoid,
		NOW()
	)";	

	return mysql_query($query);
}

function haveWatched($videoid) {
	$query = "
	SELECT 
		date
	FROM
		watched
	WHERE
		videoid = $videoid";

	$res = mysql_query($query);

	if (mysql_num_rows($res)) {
		$row = mysql_fetch_assoc($res);

		return $row['date'];
	}

	return false;
}

function moduleRemaining($moduleid) {
	$videos = getVideos($moduleid);
	
	$count = 0;
	$total = 0;

	foreach ($videos as $video) {
		$total++;

		if (haveWatched($video['id'])) {
			$count++;
		}
	}

	return array('count' => $count, 'total' => $total);
}

function courseRemaining($courseid) {
	$modules = getModules($courseid);

	$count = 0;
	$total = 0;

	foreach ($modules as $module) {
		$ret = moduleRemaining($module['id']);
		$total += $ret['total'];
		$count += $ret['count'];
	}

	return array('count' => $count, 'total' => $total);
}

function clearViewing($videoid) {
	$query = "
	DELETE FROM watched
	WHERE
		videoid = $videoid";

	return mysql_query($query);
}

function saveNote($videoid, $note) {
	$query = "
	REPLACE INTO notes (
		videoid,
		note
	) VALUES (
		$videoid,
		'" . mysql_real_escape_string($note) . "'
	)";

	return mysql_query($query) or die(mysql_error());
}

function getCourseNotes($courseid) {
	$notes = array();

	$mods = getModules($courseid);

	foreach ($mods as $mod) {
		$note = getModuleNotes($mod['id']);

		if ($note) {
			$notes[$mod['id']] = $note;
		}
	}

	return empty($notes) ? false : $notes;
}

function getModuleNotes($moduleid) {
	$notes = array();

	$vids = getVideos($moduleid);

	foreach ($vids as $vid) {
		$note = getVideoNote($vid['id']);

		if ($note) {
			$notes[] = getVideoNote($vid['id']);
		}
	}

	return empty($notes) ? false : $notes;
}

function getVideoNote($videoid) {
	$query = "
	SELECT
		id,
		videoid,
		note
	FROM
		notes
	WHERE
		videoid = $videoid";

	$res = mysql_query($query);

	if (mysql_num_rows($res)) {
		$row = mysql_fetch_assoc($res);

		return $row;
	}

	return false;
}

function toSeconds($time) {
	$bits = explode(":", $time);

	if (count($bits) == 2) {
		$minutes = $bits[0];
		$seconds = $bits[1];

		$seconds += ($minutes * 60);

		return $seconds;
	} else if (count($bits) === 3) {
		$hours = $bits[0];
		$minutes = $bits[1];
		$seconds = $bits[2];

		$seconds += ($minutes * 60) + ($hours * 3600);

		return $seconds;
	}

	return false;
}

function toTime($seconds) {
	// calculate the hours
	$hours = floor($seconds / 3600);
	$mins = floor(floor($seconds % 3600) / 60);
	$secs = $seconds % 60;

	if ($hours > 0) {
		$ret = "$hours:" . str_pad($mins, 2, "0", STR_PAD_LEFT) . ":" . str_pad($secs, 2, "0", STR_PAD_LEFT);
	} else {
		$ret = str_pad($mins, 2, "0", STR_PAD_LEFT) . ":" . str_pad($secs, 2, "0", STR_PAD_LEFT);
	}

	return $ret;
}

function lastInModule($moduleid) {
	$query = "
	SELECT
		id
	FROM
		videos
	WHERE
		moduleid = $moduleid
	ORDER BY sequence DESC
	LIMIT 1";

	$res = mysql_query($query);

	if (mysql_num_rows($res)) {
		$row = mysql_fetch_assoc($res);

		return $row['id'];
	}

	return false;
}

function addBookmark($videoid, $time) {
	// we only want to allow ONE bookmark per course!  make this so
	clearBookmark($videoid);

	$query = "
	INSERT INTO bookmarks (
		videoid,
		time,
		date
	) VALUES (
		$videoid,
		'" . mysql_real_escape_string($time) . "',
		NOW()
	)";

	return mysql_query($query);
}

function getBookmark($videoid) {
	$query = "
	SELECT
		videoid,
		time,
		date
	FROM
		bookmarks
	WHERE
		videoid = $videoid
	ORDER BY time DESC";

	$res = mysql_query($query);

	if (mysql_num_rows($res)) {
		$row = mysql_fetch_assoc($res);

		return $row;	
	}

	return false;	
}

function clearBookmark($videoid) {
	$query = "DELETE FROM bookmarks WHERE videoid = $videoid";

	return mysql_query($query);
}

function getCourseNav($courses) {
	$courses_output = array();

	foreach ($courses as $course) {
		// true if no videos in this course have been watched
		$new = true;

		// true if a bookmark exists in this course
		$course_bookmark = false;

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

			// true if there is a bookmarked video in this module
			$module_bookmark = false;

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

				if ($video['time']) {
					$str3 .= "<i class='fa fa-bookmark-o'></i>";
					$course_bookmark = true;
					$module_bookmark = true;
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

			if ($module_bookmark) {
				$str2 .= "<i class='fa fa-bookmark-o'></i>";
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

		if ($course_bookmark) {
			$str .= "<i class='fa fa-bookmark-o'></i>";
		}

		$str .= "</h2>";

		$str .= "<ul class='modules'>";
		$str .= implode("\n", $modules_output);
		$str .= "</ul>";

		$courses_output[] = $str;
	}

	return "<ul id='container'>" . implode("\n", $courses_output) . "</ul>";
}
?>
