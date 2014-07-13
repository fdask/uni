<?php
require 'header.inc.php';
?>
      <button type='button' id='unwatch' disabled='disabled'>Clear Watches</button>
      <button type='button' id='clearBookmark' disabled='disabled'>Clear Bookmark</button>
      <button type='button' id='jumpToBookmark' disabled='disabled'>Jump To Bookmark</button>

      <div id='mainarea'>
         <div id='video-container'>
            <video id='video' width="600" controls autoplay>
               <source src="" type="video/mp4">
               Your browser does not support the video tag.
            </video>
            <div id='video-controls'>
               <button class='navbutton' id='previousButton' type='button' disabled='disabled'>Previous</button>
               <button id='addBookmark' type='button'>Add Bookmark</button>
               <button class='navbutton' id='nextButton' type='button' disabled='disabled'>Next</button>
            </div>
         </div>

         <input type='checkbox' name='playToModuleEnd' id='playToModuleEnd' /> <label for='playToModuleEnd'>Play to Module End</label><br>
         <textarea id='note'></textarea>
         <button type='button' id='saveNote'>Save Note</button>
      </div>
      <div id='coursenav'>
      </div>
		<?php
		if (isset($_GET['vid'])) {
			?>
		<script>
		var myVid = new Video(<?php echo $_GET['vid']; ?>, {
			video: "#video",
			note: "#note",
			bookmarkSet: "#addBookmark",
			bookmarkClear: "#clearBookmark",
			bookmarkJump: "#jumpToBookmark",
			noteSet: "#saveNote",
			watchClear: "#unwatch",
			navNext: "#nextButton",
			navPrev: "#previousButton",
			moduleEnd: "#playToModuleEnd"
		});
		</script>
			<?php
		}
		?>
	</body>
</html>
