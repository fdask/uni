<?php
require 'uni.inc.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['videoid'])) {
	$vid = $data['videoid'];

	$action = $data['action'];

	switch ($action) {
		case "watched":
			recordViewing($vid);
			break;
		case "unwatch":
			clearViewing($vid);
			break;
		case "saveNote":
			$note = $data['note'];
			saveNote($vid, $note);
			break;
		default:
	}
} 

echo "success";
exit;
