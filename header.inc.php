<?php 
require 'uni.inc.php';
?>
<!doctype html>
<html lang="en">
  <head>
      <meta charset="utf-8">
      <title>fdask university</title>
      <script src='jquery.min.js'></script>
      <script src='jquery-ui.min.js'></script>
      <script src='uni.js'></script>
		<link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
      <link rel='stylesheet' href='uni.css'>
   </head>
   <body>
		<nav>
			[ <a href='index.php'>Home</a> ]
		
			<?php
			$videoid = $_GET['vid'];

			if ($videoid) {
				$v = getVideo($videoid);

				echo "[ <span class='course'><a href='index.php#c_{$v['courseid']}'>{$v['coursename']}</a></span> &gt; <span class='module'><a href='index.php#m_{$v['moduleid']}'>{$v['modulename']}</a></span> &gt; <span class='video'>{$v['name']}</span> ]";
			}
			?>
		</nav>

