<?php
require 'header.inc.php';

$p_id = getPrevVideo($videoid);
$n_id = getNextVideo($videoid);

$lim = 0;

if (lastInModule($v['moduleid']) == $videoid) {
	$lim = 1;
}

echo "<input type='hidden' name='lastInModule' id='lastInModule' value='$lim' />";
echo "<button type='button' id='unwatch' data-vid='$videoid'>Clear Watches</button>";

$note = getNote($videoid);
?>
<div id='mainarea'>
	<div id='video-container'>
		<video id='video' width="600" controls autoplay>
			<source src="<?php echo $v['location']; ?>" type="video/mp4">
			Your browser does not support the video tag.
		</video>
		<div id='video-controls'>
			<?php
			if ($p_id) {
				echo "<button class='navbutton' id='previousButton' type='button' data-vid='{$p_id}'>Previous</button>";
			} else {
				echo "<button class='navbutton' id='previousButton' type='button' disabled='disabled'>Previous</button>";
			}

			if ($n_id) {
				echo "<button class='navbutton' id='nextButton' type='button' data-vid='{$n_id}'>Next</button>";
			} else {
				echo "<button class='navbutton' id='nextButton' type='button' disabled='disabled'>Next</button>";
			}
			?>
		</div>
	</div>

	<input type='checkbox' name='playToModuleEnd' id='playToModuleEnd' <?php if (isset($_GET['cp']) && $_GET['cp'] == "1") echo "checked='checked'"; ?>/> <label for='playToModuleEnd'>Play to Module End</label><br>
	<textarea id='note'><?php if ($note) echo $note['note']; ?></textarea>
	<button type='button' id='saveNote' data-vid='<?php echo $videoid; ?>'>Save Note</button>
	<span id='videoid' style='display: none;'><?php echo $v['id']; ?></span>
</div>
