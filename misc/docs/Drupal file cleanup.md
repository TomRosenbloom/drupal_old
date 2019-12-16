Drupal file cleanup

```php
//db_query to find all files not attached to a node:
$result = db_query("SELECT fid FROM file_managed WHERE NOT EXISTS (SELECT * FROM file_usage WHERE file_managed.fid = file_usage.fid) ");

//Delete file & database entry
for ($i = 1; $i <= $result->rowCount(); $i++) {
  $record = $result->fetchObject();
  $file = file_load($record->fid);
  if ($file != NULL) {
    //file_delete($file);
      var_dump($file);
  } }
```

Can I run something like this on the voscur server??

Just this results in a 500 (no surprise) but I can generally run PHP 'hello world' type stuff

if I run just this locally I get undefined function, so yes, unsurprisingly I need to provide a lot more context for this to work (duh)

Running locally I can create a database connection with something like this:

```php
define('DB_SOURCE', 'mysql');
define('DB_HOST', 'localhost');
define('DB_DATABASE',  'drupal7');
define('DB_USERNAME', 'admin');
define('DB_PASSWORD', 'foobarbaz');

try {
	$dsn = DB_SOURCE . ":host="  . DB_HOST . ';dbname=' . DB_DATABASE;
	$connection = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
	$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
	echo 'ERROR: ' . $e->getMessage();
}
```

...but obvs you don't do this kind of thing in the real world.

So, is it possible to pick up a database connection from the Drupal API, or something?

Database credentials are in settings.php (drupal/sites/voscur.org/settings.php)

This works on my local machine:

```php
define('DRUPAL_ROOT', getcwd());

require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_DATABASE);

$result = db_query("SELECT * FROM url_alias LIMIT 10");

for ($i = 1; $i <= $result->rowCount(); $i++) {
  $record = $result->fetchObject();
  echo $record->alias, "<br>";
}
```

...and in the voscur site! So now to have some dangerous fun.

Now this works (echoes the id of the 11 orphaned files):

```php
define('DRUPAL_ROOT', getcwd());

require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_DATABASE);

$result = db_query("
SELECT fid 
FROM file_managed 
WHERE NOT EXISTS 
(SELECT * FROM file_usage WHERE file_managed.fid = file_usage.fid)
");

for ($i = 1; $i <= $result->rowCount(); $i++) {
  $record = $result->fetchObject();
  echo $record->fid, " ", $record->filename, " ", $record->uri "<br>";  
}
```

NB the results of this (12th December) are below. That last one is interesting. I found it in the file system and it was created today, but there is no corresponding job advert on the external website.

32371 BOPF Volunteer Policy 2013.doc private://BOPF Volunteer Policy 2013.doc
32381 BOPF Volunteer Agreement 2013.doc private://BOPF Volunteer Agreement 2013.doc
32391 Confidentiality Agreement.doc private://Confidentiality Agreement.doc
50008 gmap_markers.js public://js/gmap_markers.js
51200 feed_me.css public://spamicide/feed_me.css
51203 voscur_spam.css public://spamicide/voscur_spam.css
57313 school_girl_image.png public://default_images/school_girl_image.png
57371 school_girl_image.png public://default_images/school_girl_image_0.png
57376 school_girl_image.png public://default_images/school_girl_image_1.png
57377 school_girl_image.png public://default_images/school_girl_image_2.png
71908 Cafe%20Manager%20Job%20Advert%20December%202019.docx public://Cafe%20Manager%20Job%20Advert%20December%202019.docx 



## Plan

I need to a variant of the above but with a date in the query i.e. to remove from the database and the file system material before a certain date, starting with something v conservative in case of cock up.

First thing is to understand the database schema where it relates to files (and in general, for that matter). Here is a good reference:  http://posulliv.github.io/2012/08/02/drupal-er-diagram/ . There are possibilities to view/export an ER diagram in phpmyadmin, but I think mysql workbench is a better bet for this. Doesn't help very much actually - too many tables and no relation links shown. Anyway I downloaded his diagram.

It seems there are just the two tables related to file management: file_managed and file_usage.

**file_managed** 

a 'directory' of files - the name of the file, where it is located, its type and size, a timestamp for when it was added, who added it, and a status flag

**file_usage**

where files are being used - by what module, the type of usage, id of 'the object using the file', a count of the number of times it is used

examples

In my local site, file id 14 is a jpeg that I used in 'content/social-value-briefing' which is the url alias for node 19. In file_usage the module using it is 'file', the type is 'node' and the object-using id is 19.

File id 11 is used again by 'file', but with type 'taxonomy_term' of id 38. It is an icon I used for the 'level' of a training event. If I navigate through the UI through structure->taxonomy->Training level->list terms->intermediate->edit I can see that this is taxonomy term id 38. table taxonomy_term_data has a row with id 38, 'vid' 5, name 'Intermediate' etc. taxonomy_vocabulary id 5 is 'Training level' and so on.

