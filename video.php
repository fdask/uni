<?php
require 'header.inc.php';

$p_id = getPrevVideo($videoid);
$n_id = getNextVideo($videoid);

echo "<button type='button' id='unwatch' data-vid='$videoid'>Clear Watches</button>";

$note = getNote($videoid);
?>
<div id='mainarea'>
	<video id='video' width="600" controls autoplay>
		<source src="<?php echo $v['location']; ?>" type="video/mp4">
		Your browser does not support the video tag.
	</video>
	<textarea id='note'><?php if ($note) echo $note['note']; ?></textarea>
	<button type='button' id='saveNote' data-vid='<?php echo $videoid; ?>'>Save Note</button>
	<nav>
		<?php
		if ($p_id) {
			echo "<button class='navbutton' type='button' data-vid='{$p_id}'>Previous</button>";
		} else {
			echo "<button class='navbutton' type='button' disabled='disabled'>Previous</button>";
		}

		if ($n_id) {
			echo "<button class='navbutton' type='button' data-vid='{$n_id}'>Next</button>";
		} else {
			echo "<button class='navbutton' type='button' disabled='disabled'>Next</button>";
		}
		?>
		<span id='videoid' style='display: none;'><?php echo $v['id']; ?></span>
	</nav>
</div>
