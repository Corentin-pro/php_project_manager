<?php

// Checking previous install
if( file_exists("install.lock") )
{
	echo "A install.lock has been found, the application has been already installed. Make sure to clean the database and delete the install.lock file.";
	return;
}

// Checking PHP version
if( 0 > version_compare(PHP_VERSION, '5.6.0') )
{
	echo 'The PHP version needs to be at least 5.6, your PHP version is ' . PHP_VERSION;
	return;
}

// Checking database connection
require_once('config.php');
require_once('Manager/DatabaseManager.php');

global $pdo;
if( null === $pdo )
{
	echo "Cannot connect to your database, please check config.php";
	return;
}

// Checking SQL script file
$sql_file_name = "Database/project_" . DATABASE_TYPE . ".sql";
if( !file_exists($sql_file_name) )
{
	echo "Cannot find SQL script to create the database, please check DATABASE_TYPE in config.php (" , $sql_file_name , " file not found)";
	return;
}

// Executing SQL script
if( false !== \DatabaseManager\exec( file_get_contents($sql_file_name) ) )
{
	echo "Installation succeeded.";
	$lock_file_handle = fopen("install.lock", "w"); // Creating a locking file to avoid using this install.php again
	fclose($lock_file_handle);
}
else
{
	echo "SQL script failed, please try again from a clean database";
}

?>
