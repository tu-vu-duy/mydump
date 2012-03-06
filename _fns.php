<?php
function mydump_connect() {
	@mysql_connect(DB_HOST, DB_USER, DB_PASS)
		or die("Could not connect to db: ". mysql_error());
	@mysql_select_db(DB_NAME)
		or die("Could not select db: ". mysql_error());
	
	mydump_set_charset();
}

function mydump_set_charset() {
	$charset = @mydump_table_info("collation");
	if (!empty($charset)) {
		mysql_set_charset($charset);
	}
}

function mydump_create_table() {
	$o = mydump_fetch_object("SHOW CREATE TABLE `". TABLE ."`");
	$c = $o->{"Create Table"};
	if (!empty($c)) {
		// add IF NOT EXISTS
		$c = preg_replace("/^CREATE TABLE\s+(IF\s+NOT\s+EXISTS|)/i", "CREATE TABLE IF NOT EXISTS ", $c);
		$c = trim($c, ";") . ";";
		return $c;
	}
}

function mydump_dump() {
	$dump = "-- MyDump (MySQL Dump Tool)\n
-- Version: 1.0
-- Author: qeremy@gmail.com
-- Homapage: http://qeremy.com/mydump\n
-- Host: ". mysql_get_host_info() ."
-- Time: ". date("M j, Y @ H:i A P") ."
-- MySQL: ". mysql_get_server_info() ."
-- PHP: ". phpversion() ."\n\n";
	$dump .= "-- --------------------------------------------------------\n\n";
	$dump .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";
	$dump .= "--\n-- Database: `". DB_NAME ."`\n--\n\n";
	$dump .= "CREATE DATABASE IF NOT EXISTS ". DB_NAME .";\nUSE ". DB_NAME .";\n\n";
	$dump .= "-- --------------------------------------------------------\n\n";
	$dump .= "--\n-- Table structure for table `". TABLE ."`\n--\n\n";
	$dump .= mydump_create_table() ."\n\n";
	$dump .= "--\n-- Dumping data for table `". TABLE ."`\n--\n\n";
	return $dump;
}

function mydump_paginate() {
	$rows = mydump_table_info("rows");
	return $rows ? @ceil($rows / LIMIT) : 0;
}

function mydump_fetch_table_rows($offs = 0) {
	$offs = $offs * LIMIT;
	$rows = mydump_fetch_object_all("SELECT * FROM `". TABLE ."` LIMIT $offs,". LIMIT);
	return $rows;
}

function mydump_sanitize_data($data) {
	static $gpc;
	if (!isset($gpc)) {
		$gpc = get_magic_quotes_gpc();
	}
	
	if ($gpc) {
		$data = stripslashes($data);
	}
	$data = addslashes($data);
	
	return str_replace(array("\r", "\n", "\t"), array("\\r", "\\n", "\\t"), $data);
}

function mydump_prepare_insert_data($data) {
	if (!empty($data)) {
		$values = array();
		foreach ($data as $d) {
			$a = array();
			foreach ($d as $k => $v) {
				$i = mydump_field_info($k);
				$v = mydump_sanitize_data($v);
				$v = $i["type"] == "string"
					? (($i["null"] == 1 && $v === "") ? "NULL" : "'$v'")
					: (($i["null"] == 1 && $v === "") ? "NULL" : $v);
				// fix
				if ($i["type"] == "string" && $i["null"] == 1 && ($v == "'NULL'" || $v == "'null'")) {
					$v = "NULL";
				}
				$a[] = $v;
			}
			$values[] = "(". join(", ", $a) .")";
		}
		if (!empty($values)) {
			$insert = "INSERT INTO `". TABLE ."` VALUES\n";
			$insert .= join(", \n", $values);
			$insert .= ";";
			return $insert;
		}
	}
}

function mydump_table_info($x = null) {
	static $info;
	if (!isset($info)) {
		$info = array();
		$r = mydump_fetch_object("SHOW TABLE STATUS LIKE '". TABLE ."'");
		if ($r->Collation) {
			$r->Collation = preg_replace("/^(.*?)_.*/", "\\1", $r->Collation);
		}
		foreach ($r as $k => $v) {
			$info[strtolower($k)] = $v;
		}
	}
	return $x ? $info[$x] : $info;
}

function mydump_field_info($x = null) {
	static $info;
	if (!isset($info)) {
		$info = array();
		$r = @mydump_fetch_object_all("SHOW FULL FIELDS FROM `". TABLE ."`");
		if ($r) {
			foreach ($r as $row) {
				$type = preg_match("/(tinyint|smallint|mediumint|int|bigint|float|double|decimal)/i", $row->Type) ? "integer" : "string";
				$null = $row->Null == "NO" ? 0 : 1;
				$info[$row->Field] = array(
					"name" => $row->Field,
					"type" => $type,
					"null" => $null
				);
			}
		}
	}
	return $x ? $info[$x] : $info;
}

function mydump_fetch_object($s) {
	$r = mysql_query($s);
	$o = mysql_fetch_object($r);
	@mysql_free_result($r);
	return $o;
}

function mydump_fetch_object_all($s) {
	$q = mysql_query($s);
	$o = array();
	while ($f = mysql_fetch_object($q)) {
		$o[] = $f;
	}
	@mysql_free_result($q);
	return $o;
}

function mydump_show_tables() {
	$p = array('name', 'engine', 'rows', 'data_length', 'index_length', 'auto_increment', 'collation');
	$r = @mysql_query("SHOW TABLES FROM ". DB_NAME);
	$t = array();
	while ($f = mysql_fetch_row($r)) {
		$n = $f[0];
		$a = array();
		$o = mydump_fetch_object("SHOW TABLE STATUS LIKE '$n'");
		foreach ($o as $k => $v) {
			if (in_array(strtolower($k), $p)) {
				$k = str_replace("_", " ", $k);
				$k = ucwords($k);
				$a[$k] = $v;
			}
		}
		$a["Size"] = $o->Data_length + $o->Index_length;
		$t[] = $a;
	}
	@mysql_free_result($r);
	return $t;
}

function mydump_write($filename, $data) {
	$ok = false;
	// gzip
	if (GZIP === true) {
		$filename .= ".gz";
		$gz = @gzopen($filename, "w9");
		if ($gz) {
			gzwrite($gz, $data);
			gzclose($gz);
			$ok = true;
		}
	} else {
		$ok = @file_put_contents($filename, $data);
	}
	return $ok ? $filename : false;
}

function byte_format($s) {
	if ($s < 1024)
		return number_format($s, 0, ".", ",") ." B";
	if ($s >= 1024 && $s < 1048576)
		return number_format(round($s / 1024), 0, ".", ",") ." KB";
	if ($s >= 1048576 && $s < 1073741824)
		return number_format(round($s / 1048576, 2), 2, ".", ",") ." MB";
	if ($s >= 1073741824)
		return number_format(round($s / 1073741824, 2), 2, ".", ",") ." GB";
}

function p($s, $e = 0) {
	print "<pre>";
	print_r($s);
	print "</pre>"; 
	if ($e) exit;
}

function get_table_dump_dirs($table_dir) {
	$rval = array();
	$scan = @scandir($table_dir);
	foreach($scan as $s) {
		if ($s !== "." && $s !== "..") {
			$dir = "$table_dir/$s";
			if (is_dir($dir)) {
				$rval[] = $dir;
			}
		}
	}
	natsort($rval);
	return $rval;
}

function get_table_dump_files($dump_dir) {
	$rval = array();
	$scan = @scandir($dump_dir);
	foreach($scan as $s) {
		if ($s !== "." && $s !== "..") {
			$file = "$dump_dir/$s";
			if (is_file($file)) {
				$rval[] = $file;
			}
		}
	}
	natsort($rval);
	return $rval;
}

function delete_folder($folder) {
	$glob = glob($folder);
	foreach ($glob as $g) {
		if (is_dir($g)) {
			delete_folder("$g/*");
			rmdir($g);
		} else {
			@unlink($g);
		}
	}
}

function redirect($l) {
	header("HTTP/1.1 302 Found");
	header("Location: ". trim($l));
}
