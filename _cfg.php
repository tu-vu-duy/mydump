<?php
ob_start();

@set_time_limit(0);

header("Pragma: no-cache");
header("Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate, post-check=0, pre-check=0");

// set up your db configs
define("DB_HOST", "");
define("DB_USER", "");
define("DB_PASS", "");
define("DB_NAME", "");

// set your time zone
define("TIMEZONE", "Europe/Istanbul");

// let it be true
define("GZIP", true);

// INSERT INTO limit sql query
define("LIMIT", 50);

// the path that sql files to go
define("SAVE_PATH", ".sql");

if (!is_dir(SAVE_PATH)) {
	mkdir(SAVE_PATH, 0777, true);
	chmod(SAVE_PATH, 0777);
	
	// secure path
	@file_put_contents(SAVE_PATH ."/.htaccess", 
"Options -Indexes
<Files ~ \"\.(sql|gz|zip)$\">
	order allow,deny
	deny from all
</Files>");
}

// set timezone
date_default_timezone_set(TIMEZONE);

if (!session_id()) {
	session_start();
}

/**************************************************************
 * DO NOT forget to secure /mydump directory by using $_SESSION
 **************************************************************/

// secure session
// if ($_SESSION["admin"] != "YES") {
	// die("Not authorized!");
// }
