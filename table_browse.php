<?php
define("TABLE", trim($_GET["table"]));
if (TABLE == "") {
	die("'".TABLE."' folder is not exists!");
}

$folder = trim($_GET["folder"]);
if (!is_dir($folder)) {
	die("'$folder' folder is not exists!");
}

require_once("_cfg.php");
require_once("_fns.php");

$dir = htmlentities($folder, ENT_QUOTES, "UTF-8");
$document_title = "Table Folder: $dir";
?>
<? include_once("header.php"); ?>
<div class="topnav">
	<span class="back-links"><a href="table_view.php?table=<?=TABLE?>">Back to Table</a> - <a href="table_list.php">Back to Table List</a></span>
	<b>Dump Folder:</b> <code><?=$dir?></code>
</div>

<table width="100%" border="1">
	<tr>
		<th width="50%">Dump File</th>
		<th width="20%">Size</th>
		<th width="20%">Last Modified</th>
		<th width="10%">Action</th>
	</tr>
	<?
		$files = get_table_dump_files($folder); //p($files);
		if (!empty($files)):
			foreach ($files as $file):
	?>
	<tr>
		<td><?=basename($file)?></td>
		<td><?=byte_format(sprintf("%u", @filesize($file)))?></td>
		<td><?=date("Y-m-d H:i:s", @filemtime($file))?></td>
		<td nowrap>
			<a href="dl.php?file=<?=$file?>">Download</a>
			&nbsp;|&nbsp;
			<a href="table_zip.php?table=<?=TABLE?>&file=<?=$file?>&return=table_browse.php&delete">Delete</a>
		</td>
	</tr>
	<? 	endforeach; ?>
	<? else: ?>
	<tr>
		<td colspan="4">No dump files!</td>
	</tr>
	<? endif; ?>
</table>

<div align="right" style="margin-top:12px">
	<? if (!empty($files)): ?>
	<a href="table_zip.php?table=<?=TABLE?>&folder=<?=$folder?>&dl">Download Folder</a>
	&nbsp;|&nbsp;
	<a href="table_undump.php?table=<?=TABLE?>&folder=<?=$folder?>&empty_folder" onclick="return confirm('Empty Folder?');">Empty Folder</a>
	&nbsp;|&nbsp;
	<? endif; ?>
	<a href="table_undump.php?table=<?=TABLE?>&folder=<?=$folder?>&delete_folder" onclick="return confirm('Delete Folder?');">Delete Folder</a>
</div>

<? include_once("footer.php"); ?>