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





SELECT *
FROM file_managed
WHERE TIMESTAMP < UNIX_TIMESTAMP('2006-12-31')

returns zero records

but we have, for eg (first one), VoscurComplaints.pdf, last modified Aug 17 2006

doing a SELECT for that filename shows that file with a timestamp of 0

there are 258 files with a timestamp of zero. From a visual inspection, these correspond to the first 250 odd files in voscur.org/files, all dated in 2006, 2007, maybe 2008. So these can be deleted.

SELECT fm.fid, fm.filename, fm.uri, fu.module, fu.type, fu.id
FROM file_managed AS fm JOIN file_usage AS fu
ON fm.fid = fu.fid
WHERE timestamp = 0

Even though it's pretty obvious that this old stuff is junk, I'm still paranoid about deleting files and records. Database tables can be backed up easily enough, but files are more tricky... Can I move the files somewhere instead of deleting them? Move them all into one folder and then download it?



This moves a file without updating the database:

for ($i = 1; $i <= $result->rowCount(); $i++) {
  $record = $result->fetchObject();

  $file = file_load($record->fid);
  if ($file != NULL) {
      $source = $file->uri;
      $destination = "public://" . "_bak/" . $file->filename;
      file_unmanaged_move($source,$destination,FILE_EXISTS_RENAME);
  } 
}

NB the last modified value is changed to now (i.e. the time it was moved to the new folder)

then I have the option of deleting database records manually, or I can do it programmatically with some variant of code in [https://api.drupal.org/api/drupal/includes%21database%21database.inc/function/db_delete/7.x](https://api.drupal.org/api/drupal/includes!database!database.inc/function/db_delete/7.x)

DELETE file_managed, file_usage
FROM file_managed AS fm JOIN file_usage AS fu
ON fm.fid = fu.fid
WHERE timestamp = 0

LIMIT 100 - can't use limit in multi table delete



I'm really confused now. I manually deleted ALL the records (count was 516 = 2 x 258) because I couldn't use the LIMIT. Then I realised that was stupid because I wouldn't then be able to find the disconnected files to move them in the file system. so I went to the backed up tables, but for these the file_load() doesn't find physical files for records 101-258. Those files are definitely in the files dir, and not in files/_bak (which has 100 files in it as it should have). So why doesn't file_load() find them now? Maybe file_load() takes account of  what's in the database... yes, that is certainly true

so what if I put the deleted records back in the live tables? dodgy I think because of auto incrementing id - not sure what will happen with that...

I think this is a minor cock up that I just accept. The result is there are 158 or so files that I can't move or delete programmatically.  (If I run the script with a query on the _bak tables these 258 file records are still found, but not if I run it on the live tables). Some time later I might manualy delete these files (or god forbid ask Circle) but for now I should move on and try with older files that do have a timestamp.





SELECT *
FROM file_managed
WHERE TIMESTAMP < UNIX_TIMESTAMP('2006-12-31')

returns zero records, and indeed if I check via phpmyadmin the earliest timestamp 1244621264 is 10th June 2009

but there are files in /files/ with dates going back to 2006. Is there any way I can delete files from the file system that aren't in the database? Or do I just go back to Circle and ask them to delete every file prior to some date?

Ok, this:

```php
$dir_path = "sites/voscur.org/files/";
$file = "15Mar05.pdf";
$path = $dir . $file;

// single file delete
function deleteFile($path){
//    if (!unlink($path)) {
//	  echo ("Error deleting $path");
//	} else {
//	  echo ("Deleted $path");
//	}
echo "DELETE ", $path, "<br><br>";
}

// test read dir contents
$files = array();
$dir = new DirectoryIterator($dir_path);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot() && $fileinfo->getMTime() < '1244621264') {
        $files[] = array('filename' => $fileinfo->getFilename(), 'MTime' => $fileinfo->getMTime());
    }
}
usort($files, function($a, $b) {
    return $a['MTime'] - $b['MTime'];
});
$i = 0;
foreach($files as $file){
    echo ++$i, ": ", $file['MTime'], " ", date('Y-m-d H:i:s', $file['MTime']), " ", $file['filename'], "<br><br>";
    $file_path = $dir_path . $file['filename'];
    if($file['MTime'] < '1244621264'){
	    deleteFile($file_path);    
    } else {
        echo "no delete", "<br><br>";
    }
}
```

The output of this (saved in downloads folder of my PC) is 960 files that would be deleted. These are files that are on the file system but not in the database. The last timestamp in this list is 1244113195 2009-06-04 11:59:55. The first timestamp in the database is 1244621264 10th June 2009. So there's a gap of six days, but that seems plausible enough.

OK so I ran this (in a slightly modified form with an additional failsafe of limiting number of deletions by testing value of $i - so I had to run it a few times, raising the bar gradually from 1 to 10 to 50). This has reduced disk usage from 12.39 GB to 12.14, so that's a start...

Next day...

I imagined that perhaps from this point forward there would be proper correlation between db records and file system, so ran a script that finds files in db before a certain date (end of 2009) and then looks for those files on the file system. This found only fourteen files in the db but there are loads on the file system.

So... it seems like - for these long past years anyway - I should just delete separately (1) files from the file system (using the previous script) and (2) records from db (either in a script, or probably just via phpmyadmin).

I do wonder about this query result though... what happens if I remove the join to file_usage? I bet that turns up more files. But this just means I need to run two delete queries - one using the join between file_managed and file_usage (to ensure we get rid of orphaned records in file_usage) and one just on file_managed.







SELECT fm.fid, fm.filename, fm.uri, fu.module, fu.type, fu.id
FROM file_managed AS fm JOIN file_usage AS fu
ON fm.fid = fu.fid
WHERE timestamp < UNIX_TIMESTAMP('2009-12-31')



DELETE file_managed, file_usage
FROM file_managed JOIN file_usage
ON file_managed.fid = file_usage.fid
WHERE timestamp < UNIX_TIMESTAMP('2010-12-31')



In fact there were no records in file_managed with no corresponding record in file_usage, because this query returned no rows:

SELECT *
FROM file_managed
WHERE timestamp < UNIX_TIMESTAMP('2009-12-31')

...so we don't know why there are files on the file system not represented in the db. Just one o' them things...



NB the benefit of starting from the db to delete files from the filesystem is that I can use the Drupal file_load() and file_delete() functions, and these might be doing some more tidying up behind the scenes... i.e. a more controlled way of deleting files. But there seem to be so few files that are in the db - e.g. 12 in the whole of 2010 that it doesn't seem worth worrying about.

Moving forward through the years, there is much more data, but also we are straying into times when content does show in the UI. In that case are there additional risks to manual behind the scenes deletion? One thing for sure, it's completely horrendous deleting old content (e.g. events) via the UI because you can only do 50 at a time, and it's convoluted - and also dangerous because you could easily accidentally delete the first 50 instead of the last. The worry with behind the scenes deletion, as I come more up-to-date, is that I might delete something still being used - a logo image perhaps. We need to check on file usage before deletion is the answer I suppose... (is there a media library for things like logos? Answer: only for events, not for job ads or e-bulletins)





Having reduced the problem so that we no longer in the red wrt file usage, time to take a step back and look again to the wider world on this issue...

https://drupal.stackexchange.com/questions/20633/how-to-find-orphaned-files-and-images-which-are-not-linked-from-any-css-styleshe

https://www.drupal.org/node/733258#comment-5582764

https://www.drupal.org/project/fancy_file_delete

https://www.drupal.org/project/file_checker

Maybe should look at an event that's visible via the UI and see if I can find all of its traces in the db/filesystem.



One interesting little nugget from the sources above - instead of deleting a row from file_managed you can set status to zero and then it should be 'deleted automatically on Drupal's cron run'.



Another possibility - or a desirable feature I would say - is to allow more than 50 items to be selected in the UI. There's some links about this from here: https://blog.arvixe.com/drupal-bulk-deleting-content/

I presume there's a module for this (or I could make one), but again I'm up against the fact I can't install modules - maybe need to speak to Circle about this, if I can't do it myself...



Coming back to this another day...

Through tackling another problem, I found a bit more relevant info. There are two places in Drupal that images can end up (that I know of), one is ../files/editors/ which is where images included via HTML editors end up and the other is the /files/ folder which is where other image uploads go i.e. files that are added not within HTML text but as an accompanying image for an event, job ad whatever where an image is specified in the content type. Now, when you add an image in an HTML editor you can pick an existing one (not easy but you can), but when you add a content type image you can only upload from your local machine. consequently the files folder is full of some_logo_0.jpg, some_logo_1.jpg etc. from repreated uploads of same named images. The benefit of this is it means I'm ok deleting old content - there is no chance that an old image is being used by a newer piece of content. Ok that's fine for images, what about other types of content like pdfs and word docs? How do these end up in there?

Looking at 'module' and 'type' fields from the file_usage table, you can see if a file came via HTML editor or not. Module is always either 'file' or 'imce'. The latter is relatively much less, but they are still appearing. For safety's sake I could maybe add a where clause to exclude imce related files?

...or can I develop the query so it finds more useful info about the node (the node id is present in file_usage)

SELECT fm.fid, fm.filename, fm.uri, fu.module, fu.type, fu.id, n.type, FROM_UNIXTIME(n.created)
FROM file_managed AS fm
JOIN file_usage AS fu ON (fm.fid = fu.fid)
JOIN node AS n ON (fu.id = n.nid)
ORDER BY n.created DESC

or:

SELECT DISTINCT(n.type)
FROM file_managed AS fm
JOIN file_usage AS fu ON (fm.fid = fu.fid)
JOIN node AS n ON (fu.id = n.nid)

which returns these 19 node types (every node type that has at least one uploaded file associated with it - NB if you run the query just on the node table, there are 26 node types):

| type                |      |
| ------------------- | ---- |
| advocate_update     |      |
| blog                |      |
| webform             |      |
| policy              |      |
| resources           |      |
| case_studies        |      |
| news_article        |      |
| local_success_story |      |
| job                 |      |
| past_events         |      |
| new_job_entry_page  |      |
| event               |      |
| frontpage_image     |      |
| notice              |      |
| image               |      |
| page                |      |
| staffupdate         |      |
| board               |      |
| story               |      |

This gives us the ones that don't have any associated file uploads:

SELECT DISTINCT(n.type)
FROM node AS n
WHERE n.type NOT IN (SELECT DISTINCT(n.type)
FROM file_managed AS fm
JOIN file_usage AS fu ON (fm.fid = fu.fid)
JOIN node AS n ON (fu.id = n.nid))

...and they are:

forum
group
information_pool_time
services
voscur_home_page_banner
new_job_ad
poll

Just doing a straight listing of the node_type table there are 32 node types, suggesting that we have 6 node types that are not being used.



This could be pointing to a way of doing some more general cleaning up? If any of these node types is obsolete they can be deleted wholesale?? 

(How can I get the most recent datetime that a node type was used? Maybe by using group instead of distinct. Or maybe with a left/right join to a node type table...)



SELECT *
FROM node AS n
RIGHT OUTER JOIN node_type AS nt ON n.type = nt.type
WHERE n.nid IS NULL

gives us node types (the right table in the join) that don't have a record in the nodes (left) table

panel
volunteer_post_offered
commissioning_intention
recent_content
volunteer_opportunities
non_voscur_events
live_chat_pages

...but there are 7 of these...



Voscur nodes:

| type                    | name                    | count |      |
| ----------------------- | ----------------------- | ----- | ---- |
| page                    | Page                    | 629   |      |
| story                   | Story                   | 2258  |      |
| blog                    | Blog entry              | 26    |      |
| event                   | Event                   | 1662  |      |
| notice                  | Notice                  | 414   |      |
| past_events             | Past Events             | 8     |      |
| image                   | Image                   | 126   |      |
| poll                    | Poll                    | 2     |      |
| webform                 | Webform                 | 125   |      |
| panel                   | Panel                   | -     |      |
| forum                   | Forum topic             | 48    |      |
| information_pool_itme   | Information Pool Item   | 1     |      |
| volunteer_post_offered  | Volunteer Post Offered  | -     |      |
| staffupdate             | Staff Update            | 48    |      |
| commissioning_intention | Commissioning Intention | -     |      |
| board                   | Board                   | 1     |      |
| new_job_entry_page      | Job Advertisement       | 7694  |      |
| frontpage_image         | Frontpage image         | 42    |      |
| group                   | Group                   | 1     |      |
| voscur_home_page_banner | Voscur Home Page Banner | 23    |      |
| recent_content          | Recent Content          | -     |      |
| news_article            | News Article            | 727   |      |
| services                | Services                | 36    |      |
| local_success_story     | Local Success Story     | 5     |      |
| resources               | Resources               | 114   |      |
| policy                  | Policy                  | 7     |      |
| volunteer_opportunities | Volunteer Opportunities | -     |      |
| non_voscur_events       | Non-Voscur Events       | -     |      |
| case_studies            | Case Studies            | 4     |      |
| live_chat_pages         | Live Chat Pages         | -     |      |
| new_job_ad              | New Job Ad              | 2     |      |
| advocate_update         | Advocate Update         | 1     |      |

For a count of node types that are in use:

SELECT nid, type, COUNT(type)
FROM node
GROUP BY type

Added the figures into the table above. I should go through these and delete (with David) from the UI. Of course I could just have used the UI to go through content types (is there a way to see what the query is behind various UI actions?). NB you can do node export from the UI which would be a way of backing up.



https://www.axelerant.com/resources/team-blog/clean-up-unwanted-content-in-a-drupal-website





footnote

The ideal thing would be a script - with an interactive front end - that do an analysis of the files in the db/file system and offer options to delete/keep. (1) an option to find orphaned files (2) enter dates, select/delete files etc. (show size etc of the file)

Has anyone already done this? Possibly not, because it shouldn't be necessary in a properly managed site. 



Some limits on where I can run php scripts - /../files/somefolder/ does an auto file download. Also, tried /drupal-7.62/scripts/ but that has proved unworkable because I can't work out how to include bootstrap.inc and what have you. I think I just have to run stuff in the root for now. (Probably the way to do this is to create a module, but I can't install modules...)

