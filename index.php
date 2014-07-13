<?php
require 'header.inc.php';

$courses = getCourses();
?>
Show
<label for='showNew'>New</label> <input type='checkbox' name='showNew' id='showNew' checked='checked'>
<label for='showStarted'>Started</label> <input type='checkbox' name='showStarted' id='showStarted' checked='checked'>
<label for='showCompleted'>Completed</label> <input type='checkbox' name='showCompleted' id='showCompleted' checked='checked'>
<?php 
echo getCourseNav($courses); 
?>
