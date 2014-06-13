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
		case "clearBookmark":
			clearBookmark($vid);
			break;
		case "addBookmark":
			$time = $data['time'];
			addBookmark($vid, $time);
			break;
		default:
	}
} 

echo json_encode("success");
exit;
