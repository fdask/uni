#!/usr/bin/php
<?php
require 'uni.inc.php';

$query = "SELECT * FROM videos WHERE moduleid >= 229";

$res = mysql_query($query);

while ($row = mysql_fetch_assoc($res)) {
	$file = basename($row['location']);

	$full = "/home/jwallace/compass/{$row['location']}";

	$newfile = $row['id'] . "_" . str_replace(".wmv", ".mp4", $file);

	//shell_exec("/usr/bin/ffmpeg -i " . escapeshellarg($full) . " -vcodec libx264 -vpre medium /usr/share/nginx/html/uni/files/" . escapeshellarg($newfile));

	copy($full, "/usr/share/nginx/html/uni/files/$newfile");

	$query2 = "
	UPDATE
		videos
	SET
		location = 'files/$newfile' 
	WHERE
		id = {$row['id']}";

	mysql_query($query2);
}

