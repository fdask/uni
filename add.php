#!/usr/bin/php
<?php
require '/usr/share/nginx/html/uni/uni.inc.php';

$DESTDIR = "/usr/share/nginx/html/uni/files";
$COURSEID = 69;

$module_sequence = 0;
$module_total = 0;

$module_created = array();

// for each directory in the current folder
foreach (glob("*") as $dir) {
	if (preg_match("@(\d+) - (.*)@", $dir, $matches)) {
		//$module_sequence++;
		$module_sequence = intval($matches[1]) + 1;
		$module_name = $matches[2];

		$video_count = 0;

		// create the module
		$module_id = addModule($COURSEID, $module_sequence, $module_name);

		// for each file in this folder
		foreach (glob("$dir/*") as $file) {
			echo $file . "\n";

			$bits = explode("/", $file);

			if (preg_match("@(\d+) ([^\.]+) 201\d+\.(mp4|wmv|mov|webm)@", end($bits), $matches2)) {
				$video_count++;
				$video_sequence = intval($matches2[1]) + 1;
				$video_name = $matches2[2];

				// create the video
				addVideo($module_id, $video_sequence, $video_name, $file);
			}
		}

		$module_total += $video_count;
	}
}

// now we need to convert any videos to mp4, rename them to the appropriate file and copy them to the destination directory
$query = "SELECT * FROM videos WHERE moduleid IN (SELECT id FROM modules WHERE courseid = $COURSEID)";

$res = mysql_query($query);

while ($row = mysql_fetch_assoc($res)) {
	echo "Processing $full\n";
   $file = basename($row['location']);

   $full = getcwd() . "/{$row['location']}";

   // make sure the file hasn't been converted already
   if (strpos($row['location'], "files/") !== 0) {
      if (strpos($file, ".wmv") !== false || strpos($file, ".mov") !== false || strpos($file, ".webm") !== false) {
         $newfile = $row['id'] . "_" . str_replace(array(".wmv", ".mov", ".webm"), ".mp4", $file);
         shell_exec("/usr/bin/ffmpeg -i " . escapeshellarg($full) . " -vcodec libx264 -vpre medium $DESTDIR/" . escapeshellarg($newfile));
      } else {
         $newfile = $row['id'] . "_" . $file;
         copy($full, "$DESTDIR/$newfile");
      }

      $query2 = "
      UPDATE
         videos
      SET
         location = '" . mysql_real_escape_string("files/$newfile") . "'
      WHERE
         id = {$row['id']}";

      mysql_query($query2);
   } else {
      echo "Skipping {$row['location']} because it seems to have been converted already!\n";
   }
}

$query = 'SELECT * FROM videos WHERE length IS NULL';

$res = mysql_query($query);

while ($row = mysql_fetch_assoc($res)) {
   $location = "$DESTDIR/" . basename($row['location']);

   $cap = shell_exec("ffmpeg -i " . escapeshellarg($location) . " 2>&1 | grep Duration | awk '{print $2}' | tr -d ,");

   $bits = explode(":", $cap);
   $mins = $bits[1];
   $secs = $bits[2];

   $bits2 = explode(".", $secs);
   $secs = $bits2[0];

   if (trim($mins) && trim($secs)) {
      $length = "$mins:$secs";

      $query = "
      UPDATE
         videos
      SET
         length = '$length'
      WHERE
         id = {$row['id']}";

      mysql_query($query) or die(mysql_error());
   } else {
      echo "error on {$row['id']}\n";

      if (preg_match("@files/(\d+)_(\d+)_\d+@", $location, $matches)) {
         if ($matches[1] == $matches[2]) {
            $newloc = str_replace("{$matches[1]}_{$matches[1]}", $matches[1], $location);

            $query = "
            UPDATE
               videos
            SET
               location = '$newloc',
               length = NULL
            WHERE
               id = {$row['id']}";

            mysql_query($query);
         }
      }
   }
}
