<?php
define("TABLE", trim($_GET["table"]));
if (TABLE == "") {
	die("Table required!");
}

require_once("_cfg.php");
require_once("_fns.php");

$folder = trim($_GET["folder"]);
if (empty($folder)) {
	die("Folder required!");
}

define("TABLE_PATH", SAVE_PATH ."/". DB_NAME .".". TABLE ."/$folder");
if (!is_dir(TABLE_PATH)) {
	mkdir(TABLE_PATH, 0777, true);
	chmod(TABLE_PATH, 0777);
}

mydump_connect();
$dump = mydump_dump();
$page = mydump_paginate();

$i = intval($_GET["offset"]);
$data = mydump_fetch_table_rows($i);
$data = mydump_prepare_insert_data($data);
$filename = TABLE_PATH ."/$folder-". DB_NAME .".". TABLE ."-". ($i*LIMIT) ."-". (($i+1)*LIMIT) .".sql";
$status = $filename = mydump_write($filename, $dump.$data);
if ($status) {
	if (isset($_GET["ajax"])) {
		die("$i|OK|$filename|".byte_format(@filesize($filename)));
	}
	echo "<div style='margin-bottom:12px; font-family:arial'>Status: OK! 
		<br><a href='dl.php?file=$filename'>Download Link</a> (". byte_format(@filesize($filename)) .")
		<br><a href='table_view.php?table=". TABLE ."'>Back to Table</a></div>";
} else {
	if (isset($_GET["ajax"])) {
		die("ERR");
	}
	echo "<div style='margin-bottom:12px; font-family:arial'>Status: ERR! 
		<br><a href='table_view.php?table=". TABLE ."'>Back to Table</a></div>";
}