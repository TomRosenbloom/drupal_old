<?php

//define('DB_SOURCE', 'mysql');
//define('DB_HOST', 'localhost');
//define('DB_DATABASE',  'drupal7');
//define('DB_USERNAME', 'admin');
//define('DB_PASSWORD', 'foobarbaz');
//
//
//try {
//	$dsn = DB_SOURCE . ":host="  . DB_HOST . ';dbname=' . DB_DATABASE;
//	$connection = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
//	$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//} catch(PDOException $e) {
//	echo 'ERROR: ' . $e->getMessage();
//}



/**
 * Root directory of Drupal installation.
 */
define('DRUPAL_ROOT', getcwd());

// Bootstrap to database level for db api
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_DATABASE);

$result = db_query("SELECT * FROM url_alias");

for ($i = 1; $i <= $result->rowCount(); $i++) {
  $record = $result->fetchObject();
  echo $record->alias, "<br>";
}