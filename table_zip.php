<?php
define("TABLE", trim($_GET["table"]));
if (TABLE == "") {
	die("Table required!");
}

if (isset($_GET["delete"]) && !empty($_GET["file"])) {
	$file = trim($_GET["file"]);
	@unlink($file);
	if (isset($_GET["ajax"])) {
		die("File deleted!");
	} else {
		$return = trim($_GET["return"]);
		if (!empty($return)) {
			redirect("$return?table=". TABLE ."&folder=". dirname($file));
		} else {
			redirect("table_view.php?table=". TABLE);
		}
	}
}

require_once("_cfg.php");
require_once("_fns.php");

$folder = trim($_GET["folder"]);
if (empty($folder)) {
	die("Path required!");
}

define("TABLE_PATH", $folder);
if (!is_dir(TABLE_PATH)) {
	die("Error: No dump folder!");
}

$re = GZIP === true ? "gz" : "sql";
$globs = glob(TABLE_PATH ."/*.{sql,gz}", GLOB_BRACE);
$files = array();
foreach ($globs as $glob) {
	if (!empty($glob)) {
		$files[] = $glob;
	}
}

if (!empty($files)) {
	require_once("_pclzip.lib.php");
	$folder = substr($folder, strrpos($folder, "/")+1, strlen($folder));
	$zip = TABLE_PATH ."/$folder-". DB_NAME .".". TABLE .".zip";
	$archive = new PclZip($zip);
	$v_list = $archive->create(
		$files
		, PCLZIP_OPT_REMOVE_PATH, dirname($zip)
		// , PCLZIP_OPT_ADD_PATH, TABLE_PATH
	);

	if ($v_list == 0) {
		die("Error: ".$archive->errorInfo(true));
	}
	
	if (isset($_GET["ajax"])) {
		die("<a href='dl.php?file=$zip'>Download Folder</a> [<a onclick='deletePathZip(\"".hash("crc32", dirname($zip))."\",this.href); return !1;' href='table_zip.php?table=".TABLE."&file=$zip&delete&ajax'>Delete</a>]");
	} else {
		redirect("dl.php?file=$zip");
	}
}

die("Error: No dump files!");