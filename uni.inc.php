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
		id,
		moduleid,
		name,
		length,
		location,
		sequence
	FROM
		videos
	WHERE
		moduleid = $moduleid
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

function getNote($videoid) {
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
