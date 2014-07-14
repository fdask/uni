Online University
=================
A web based application for presenting and managing progress through video training courses.

This application is designed to work with mp4 video files, structured into a hiarchy of courses/modules/videos, similar to what you see on popular sites such as Lynda.com and Pluralsight.  The application uses PHP, MySQL, HTML, Javascript, and CSS.  It makes extensive use of jQuery, and has only been tested in a current (as of July 13th, 2014) version of Chrome.  

Features
--------
* Bookmarks - Save your spot to instantly return to where you left off, even mid video
* Progress Tracking - Index page lists all the courses you've added, and shows you instantly where you left off
* Listings by State - Choose to show or hide courses you've completed, only just started, or haven't touched at all!
* Notes - Keep notes on individual videos
* Full screen support - No page refreshes between videos means fullscreen playing won't be interrupted
* Continuous Play - Start a module, and play through all the videos right to the end without a break
* Automatic conversion to MP4 - Using ffmpeg, non mp4 files will be converted automatically as they are imported

Pre-Requisites
--------------
Nothing abnormal is required to run.  A linux box with access to the following should suffice.

* Mplayer
* PHP 
* MySQL
* Web Server (apache/nginx, etc)

Setup
-----
###Database Setup
####Create the Database
Create a database in your MySQL install to hold the data.  Open up a MySQL prompt, and do 

```CREATE DATABASE university;```

####Import the database schema
You will find a dump of the schema in a file called uni.sql in the root folder.  Load it into the database using

```mysql -u <username> -p university < ./uni.sql```

###Import your video files
Right now the course import is a little rough, and requires a lot of steps.  Hope to clean this up in future releases.

####Note Before Import
This application assumes the following structure for your course files.

	CourseName/
		1. Module One/
			1. Video File One.mp4
			2. Video Two.avi
		2. Module Two/
			1. The Best Module!
			2. Summary

The import process uses the numbering on the module folders, and video filenames to determine the correct sequence for the course playlist.  Spacing, punctuation, etc, can vary as regular expressions are used for the matching.

If your files do not follow this structure, you will have to manually adjust it before attempting an import.

####Add the Course
An entry in the 'courses' table needs to be manually added as the first step.  Load up your MySQL CLI and do something like:

```INSERT INTO courses (name, company, link) VALUES ('The Best Math Course', 'Open Source Inc.', 'http://www.opensourcecourse.com');```

Only the name field is required here.  Company and link are just there for reference. Make note of the id of this newly inserted course.

```SELECT LAST_INSERT_ID();```

####Update the import script 
A PHP script (add.php) is used for the importing.  It is meant to be run from the command line, but before we do that, it needs a few edits.

Line 3, update the INSTALLDIR variable with the full path to where you put this code.  No trailing slash.
```php
$INSTALLDIR = '/usr/share/nginx/html/uni';
```

Line 4, update the FFMPEG variable with the full path to where ffmpeg is installed.
```php
$FFMPEG = "/usr/bin/ffmpeg";
```

Line 9, replace '69' with the id of the row you just added into courses.
```php
$COURSEID = 69;
```

Next, on lines 16-18, there is a little regexp that tries to get modules in your course.  Edit the regexp to match your folder structure.  You want to ensure the expression is capturing 2 bits, the module number, and the module name.

```php
if (preg_match("@(\d+) - (.*)@", $dir, $matches)) {
	$module_sequence = intval($matches[1]);
	$module_name = $matches[2];
```

Then, on lines 29-31, there is a similar block of code but this time for the video names.  Again, we want two pieces captured from the regexp; video number, and name.
```php
if (preg_match("@(\d+) ([^\.]+) 201\d+\.(mp4|wmv|mov|webm)@", end($bits), $matches2)) {
	$video_sequence = intval($matches2[1]);
	$video_name = $matches2[2];
```

####Create the files directory
All the MP4 files this application uses will be stored in a 'files/' folder.  The repo currently has a symlink instead of an actual directory, so you'll probably need to delete it and either create an actual directory, or another symlink.

####Run the import script
You want to be inside the course folder (viewing a listing of the module folders) when you run add.php.   The flow of add.php goes as follows:

1. Scan the files and folders in the current directly, adding the modules and video details into the database.
2. For all the video files just added to the database, run them through ffmpeg, converting to mp4 if necessary, saving the output to the files/ directory in the codebase.  Your original files are left untouched.
3. The new mp4s in the files/ directory are scanned, updating the length column in the videos table.

####Update MySQL Connection String
There is only one place in the code where a mysql connection is established.  And that is in uni.inc.php on lines 2 and 3.  Change the paramters here to match your settings.
```php
mysql_connect('localhost', 'root', 'testing');
mysql_select_db('university');
```

If all was successful, you should now be able to fire up your browser, point it to the index.php and see a listing of the files!

Todo
----
- Add in combined notes popup when clicking notes icons on index screen
- Bookmark icons on index page should link directly to the bookmarked spot in the video
- Add tags/categories, reviews?
