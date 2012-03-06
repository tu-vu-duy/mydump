<?php
ob_start();
header("Pragma: no-cache");
header("Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate, post-check=0, pre-check=0");

$file = trim($_GET["file"]);
if (!file_exists($file)) {
	die("File not found!");
}

$filesize = filesize($file);
$memlimit = return_bytes(ini_get("memory_limit"));
if ($filesize > $memlimit) {
	die("<pre>Allowed memory size of '<b>$memlimit</b>' bytes exhausted!\nYou need to download '<b>$file</b>' via FTP.</pre>");
}

header("Content-Description: File Transfer");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=". basename($file));
header("Content-Transfer-Encoding: binary");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: public");
header("Content-Length: ". $filesize);
ob_clean();
flush();
readfile($file);

function return_bytes($val) {
	if (!$val) return;
	if (is_numeric($value)) return $val;
	$val = trim($val);
	$unt = strtoupper($val[strlen($val)-1]);
	if ($unt == "K") return $val = $val * 1024;
	if ($unt == "M") return $val = $val * 1048576;
	if ($unt == "G") return $val = $val * 1073741824;
}