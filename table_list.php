<?php
require_once("_cfg.php");
require_once("_fns.php");

$_SESSION["mydump"]["altered_tables"] = array();

mydump_connect();

$tables = mydump_show_tables();
$document_title = "Database Name: `". DB_NAME ."`";
?>
<? include_once("header.php"); ?>

<div class="topnav">
	<b>Database Name:</b> <code><?="`". DB_NAME ."`"?></code>
</div>

<table class="list-table" border="1">
	<tr>
		<th width="20%">Name</th>
		<th width="8%">Engine</th>
		<th width="8%">Rows</th>
		<th width="10%">Data Length</th>
		<th width="10%">Index Length</th>
		<th width="10%">Auto Increment </th>
		<th width="5%">Collation</th>
		<th width="10%">Size</th>
	</tr>
	<? foreach ($tables as $table): ?>
	<tr>
		<? foreach ($table as $k => $v):
				if ($k == "Name") $v = "<a href='table_view.php?table=$v'>$v</a>";
				if ($k == "Data Length" || $k == "Index Length" || $k == "Size") $v = byte_format($v);
				if ($k == "Auto Increment" && !$v) $v = 1;
		?>
		<td><?=$v?></td>
		<? endforeach; ?>
	</tr>
	<? endforeach; ?>
</table>

<? include_once("footer.php"); ?>