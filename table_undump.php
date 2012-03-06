<?php
define("TABLE", trim($_GET["table"]));
if (TABLE == "") {
	die("Table required!");
}

require_once("_cfg.php");
require_once("_fns.php");

$folder = trim($_GET["folder"]);

if (isset($_GET["empty_folder"]) && !empty($folder)) {
	$glob = glob("$folder/*");
	foreach ($glob as $g) {
		@unlink($g);
	}
	redirect("table_browse.php?table=". TABLE ."&folder=$folder");
} 

if (isset($_GET["delete_folder"]) && !empty($folder)) {
	delete_folder($folder);
	redirect("table_view.php?table=". TABLE);
} 

define("TABLE_PATH", SAVE_PATH ."/$folder-". DB_NAME .".". TABLE ."/$folder");
if (TABLE_PATH == "") {
	die("Folder required!");
}

$offsets = array();
foreach ((array) $_POST["files"] as $offset => $file) {
	$file = urldecode($file);
	if (file_exists(TABLE_PATH ."/". $file)) {
		@unlink(TABLE_PATH ."/". $file);
		$offsets[] = $offset;
	}
}
die(join("|", $offsets));