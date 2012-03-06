<?php
define("TABLE", trim($_GET["table"]));
if (TABLE == "") {
	die("Table required!");
}

require_once("_cfg.php");
require_once("_fns.php");

define("TABLE_PATH", SAVE_PATH ."/". DB_NAME .".". TABLE);
if (!is_dir(TABLE_PATH)) {
	mkdir(TABLE_PATH, 0777, true);
	chmod(TABLE_PATH, 0777);
}

mydump_connect();

$link = "dl.php?file=";
$page = mydump_paginate();
$document_title = "Table Name: `".DB_NAME."`.`".TABLE."`";
?>
<? include_once("header.php"); ?>

<div class="topnav">
	<span class="back-links"><a href="table_list.php" class="back">Back to Table List</a></span>
	<b>Table Name:</b> <code><?="`".DB_NAME."`.`".TABLE."`"?></code>
</div>

<form>
<table class="list-table" border="1">
	<tr>
		<th width="1%">Offset</th>
		<th width="50%">Dump File</th>
		<th width="20%">Status</th>
		<th>Action</th>
		
		<th width="2%"><input type="checkbox" onclick="if(this.checked){for(var i=0,f=document.forms[0];i<f.elements.length;i++)f.elements[i].checked=1;}else{for(var i=0,f=document.forms[0];i<f.elements.length;i++)f.elements[i].checked=0;}"></th>
	</tr>
	<? list($folder_y, $folder_m, $folder_d) = explode("-", date("Y-m-d"));
		if ($page):
			for ($i = 0; $i < $page; $i++):
				$filename = DB_NAME .".". TABLE ."-". ($i*LIMIT) ."-". (($i+1)*LIMIT) .".sql";
				if (GZIP === true) $filename .= ".gz";
				$basename = basename($filename);
	?>
	<tr>
		<td align="center"><?=$i?></td>
		<td><span role="folder-y"><?=$folder_y?></span>-<span role="folder-m"><?=$folder_m?></span>-<span role="folder-d"><?=$folder_d?></span>-<?=$basename?></td>
		<td id="status-<?=$i?>">--</td>
		<td><a href="table_dump.php?table=<?=TABLE?>&offset=<?=$i?>" onclick="dump(<?=$i?>); return !1;">Dump</a></td>
		<td align="center"><input type="checkbox" value="<?=$basename?>" role="filename" offset="<?=$i?>"></td>
	</tr>
	<? 	endfor; ?>
	<? else:
		$filename = DB_NAME .".". TABLE ."-". (0*LIMIT) ."-". ((0+1)*LIMIT) .".sql";
		if (GZIP === true) $filename .= ".gz";
		$exists = file_exists($filename);
		$basename = basename($filename);
	?>
	<tr>
		<td align="center">0</td>
		<td><span role="folder-y"><?=$folder_y?></span>-<span role="folder-m"><?=$folder_m?></span>-<span role="folder-d"><?=$folder_d?></span>-<?=$basename?></td>
		<td id="status-0">--</td>
		<td><a href="table_dump.php?table=<?=TABLE?>&offset=0" onclick="dump(0); return !1;">Dump</a></td>
		<td align="center"><input type="checkbox" value="<?=$basename?>" role="filename" offset="0"></td>
	</tr>
	<? endif; ?>
</table>
</form>

<div class="dump-action">
	Dump Folder: 
	<select id="dumpundump_y" onchange="setDumpFileFolderPrefix('Y')">
		<? for ($i=date("Y"); $i>=date("Y")-10; $i--): ?>
		<option value="<?=$i?>"><?=$i?></option>
		<? endfor; ?>
	</select>
	<select id="dumpundump_m" onchange="setDumpFileFolderPrefix('M')">
		<? $month = date("m");
			for ($i=1; $i<=12; $i++): if($i<10) $i = "0$i"; ?>
		<option value="<?=$i?>"<? if($i==$month) echo " selected"; ?>><?=$i?></option>
		<? endfor; ?>
	</select>
	<select id="dumpundump_d" onchange="setDumpFileFolderPrefix('D')">
		<? $day = date("d");
			for ($i=1; $i<=31; $i++): if($i<10) $i = "0$i"; ?>
		<option value="<?=$i?>"<? if($i==$day) echo " selected"; ?>><?=$i?></option>
		<? endfor; ?>
	</select>
	<button onclick="dumpAll()">Dump!</button>
</div>

<div class="table-info">
	<div class="caption">Table Dumps</div>
	<table width="100%" border="1">
		<tr>
			<th width="65%">Dump Folder</th>
			<th width="10%">Dump Files</th>
			<th>Action</th>
		</tr>
		<?
			$dump_dirs = get_table_dump_dirs(TABLE_PATH);
			if (!empty($dump_dirs)):
				foreach ($dump_dirs as $dump_dir):
					$dump_files_count = count(get_table_dump_files($dump_dir));
					$dump_dir_hash = hash("crc32", $dump_dir);
					$dump_dir_zip = "$dump_dir/".DB_NAME.".".TABLE.".zip";
		?>
		<tr>
			<td><a href="table_browse.php?table=<?=TABLE?>&folder=<?=$dump_dir?>"><?=$dump_dir?></a></td>
			<td><?=$dump_files_count?></td>
			<td nowrap>
				<a href="table_zip.php?table=<?=TABLE?>&folder=<?=$dump_dir?>&dl"<? if (!$dump_files_count){ ?> onclick="alert('No dump files!'); return !1;"<? } ?>>Download Folder</a>
				&nbsp;|&nbsp;
				<a href="table_undump.php?table=<?=TABLE?>&folder=<?=$dump_dir?>&delete_folder" onclick="return confirm('Delete Folder?');">Delete Folder</a>
				<!--<a onclick="generatePathZip('<?=$dump_dir_hash?>',this.href); return !1;" href="table_zip.php?table=<?=TABLE?>&folder=<?=$dump_dir?>&ajax">Zip Folder</a>
				&nbsp;|&nbsp;
				<span id="zip_download_<?=$dump_dir_hash?>">
					<? if (file_exists($dump_dir_zip)): ?>
					<a href="dl.php?file=<?=$dump_dir_zip?>">Download Folder</a> [<a onclick="deletePathZip('<?=$dump_dir_hash?>',this.href); return !1;" href="table_zip.php?table=<?=TABLE?>&file=<?=$dump_dir_zip?>&delete&ajax">Delete</a>]
					<? else: ?>
					Download
					<? endif; ?>
				</span>-->
			</td>
		</tr>
		<? 	endforeach; ?>
		<? else: ?>
		<tr>
			<td colspan="3">No table dumps!</td>
		</tr>
		<? endif; ?>
	</table>
</div>

<div class="table-info">
	<div class="caption">Table Fields</div>
	<table border="1">
		<tr>
			<th>Field</th>
			<th width="1%">Type</th>
			<th width="1%">Collation</th>
			<th width="15%">Attributes</th>
			<th width="10%">Null</th>
			<th width="1%">Default</th>
			<th width="1%">Extra</th>
		</tr>
		<? $infos = @mydump_fetch_object_all("SHOW FULL FIELDS FROM `".TABLE."`");
			foreach ($infos as $info):
				$info_field = $info->Field;
				$tmp = (array) explode(" ", $info->Type); 
				$info_type = $tmp[0]; 
				$info_collation = $info->Collation;
				$info_attributes = strtoupper($tmp[1]);
				$info_null = ucwords(strtolower($info->Null));
				$info_default = ($info->Null == "YES" && $info->Default == "") ? "<i>NULL</i>" : $info->Default;
				$info_extra = $info->Extra;
		?>
		<tr>
			<td><?=$info_field?></td>
			<td><?=$info_type?></td>
			<td><?=$info_collation?></td>
			<td><?=$info_attributes?></td>
			<td><?=$info_null?></td>
			<td><?=$info_default?></td>
			<td><?=$info_extra?></td>
		</tr>
		<? endforeach; unset($infos, $info); ?>
	</table>
</div>

<div class="table-info">
	<div class="caption">Table Indexes</div>
	<?
		$infos = array(); $noindexinfo = true;
		$result = @mysql_query("SHOW INDEX FROM `".TABLE."`");
		while ($row = mysql_fetch_object($result)) {
			if ($row->Non_unique == "0" && $row->Key_name == "PRIMARY") {
				$infos["PRIMARY"] = array("field" => $row->Column_name, "cardinality" => $row->Cardinality?$row->Cardinality:"None");
				$noindexinfo = false;
			}
			if ($row->Non_unique == "0" && $row->Key_name != "PRIMARY") {
				$infos["UNIQUE"][$row->Key_name][$row->Seq_in_index] = array("field" => $row->Column_name, "cardinality" => $row->Cardinality?$row->Cardinality:"None");
				$noindexinfo = false;
			}
			if ($row->Non_unique == "1" && $row->Index_type != "FULLTEXT") {
				$infos["INDEX"][$row->Key_name][] =  array("field" => $row->Column_name, "cardinality" => $row->Cardinality?$row->Cardinality:"None");
				$noindexinfo = false;
			}
			if ($row->Non_unique == "1" && $row->Index_type == "FULLTEXT") {
				$infos["FULLTEXT"][$row->Key_name][$row->Seq_in_index] = array("field" => $row->Column_name, "cardinality" => $row->Cardinality?$row->Cardinality:"None");
				$noindexinfo = false;
			}
		}
	?>
	<table border="1">
		<tr>
			<th width="40%">Keyname</th>
			<th width="15%">Type</th>
			<th width="15%">Cardinality</th>
			<th width="30%">Field</th>
		</tr>
		<? if ($noindexinfo): ?>
		<tr>
			<td colspan="4">No index info!</td>
		</tr>
		<? endif; ?>
		<? // primary
			if ($infos["PRIMARY"]): ?>
		<tr>
			<td>PRIMARY</td>
			<td>PRIMARY</td>
			<td><?=$infos["PRIMARY"]["cardinality"]?></td>
			<td><?=$infos["PRIMARY"]["field"]?></td>
		</tr>
		<? endif; ?>
		<? // unique
			if ($infos["UNIQUE"]):
				foreach ($infos["UNIQUE"] as $unique_field => $uniques):
					foreach ($uniques as $unique):
		?>
		<tr>
			<td><?=$unique_field?></td>
			<td>UNIQUE</td>
			<td><?=$unique["cardinality"]?></td>
			<td><?=$unique["field"]?></td>
		</tr>
		<? 		endforeach;
				endforeach;
			endif; ?>
		
		<? // index
			if ($infos["INDEX"]):
				foreach ($infos["INDEX"] as $index_field => $indexs):
					if (count($indexs) == 1):
		?>
		<tr>
			<td><?=$index_field?></td>
			<td>INDEX</td>
			<td><?=$indexs[0]["cardinality"]?></td>
			<td><?=$indexs[0]["field"]?></td>
		</tr>
		<? 		else: ?>
		<tr>
			<td><?=$index_field?></td>
			<td>INDEX</td>
			<td><?=$indexs[1]["cardinality"]?></td>
			<td class="multi-fulltext">
				<?
					foreach ($indexs as $index):
						echo "<div>{$index["field"]}</div>";
					endforeach;
				?>
			</td>
		</tr>
		<? 		endif;
				endforeach;
			endif; ?>
		
		<? // fulltext
			if ($infos["FULLTEXT"]):
				foreach ($infos["FULLTEXT"] as $fulltext_field => $fulltexts):
					if (count($fulltexts) == 1):
		?>
		<tr>
			<td><?=$fulltext_field?></td>
			<td>FULLTEXT</td>
			<td><?=$fulltexts[1]["cardinality"]?></td>
			<td><?=$fulltexts[1]["field"]?></td>
		</tr>
		<? 		else: ?>
		<tr>
			<td><?=$fulltext_field?></td>
			<td>FULLTEXT</td>
			<td><?=$fulltexts[1]["cardinality"]?></td>
			<td class="multi-fulltext">
				<?
					foreach ($fulltexts as $fulltext):
						echo "<div>{$fulltext["field"]}</div>";
					endforeach;
				?>
			</td>
		</tr>
		<? 		endif;
				endforeach;
			endif; ?>
	</table>
</div>

<script>
function dump(offset) {
	ajaxGet("table_dump.php?table=<?=TABLE?>&folder="+ getDumpFolderName() +"&offset="+ offset +"&ajax&fucking_microsoft_internet_explorer_fix="+(+new Date), function(){
		if (this.readyState == 4 && this.status == 200) {
			el("status-"+ offset).innerHTML = "OK";
		} else {
			el("status-"+ offset).innerHTML = "Processing...";
		}
	});
}

var offsets = [];
function dumpAll() {
	if (!offsets.length) {
		var cb = document.getElementsByTagName("input");
		var i, l, c;
		for (i = 0, l = cb.length; i < l; i++) {
			c = cb[i];
			if (c.getAttribute("role") == "filename" && c.checked) {
				var offset = c.getAttribute("offset");
				offsets.push(offset);
			}
		}
	}
	
	if (!offsets.length) return;
	
	var offset = offsets.shift();
	el("status-"+ offset).innerHTML = "Processing...";
	ajaxGet("table_dump.php?table=<?=TABLE?>&folder="+ getDumpFolderName() +"&offset="+ offset +"&ajax&fucking_microsoft_internet_explorer_fix="+(+new Date), function(){
		if (this.readyState == 4 && this.status == 200) {
			el("status-"+ offset).innerHTML = "OK";
			if (offsets.length) {
				dumpAll();
			} else {
				location.reload();
			}
		} else {
			el("status-"+ offset).innerHTML = "Processing...";
		}
	});
}

function generatePathZip(id, url) {
	ajaxGet(url, function(){
		if (this.readyState == 4 && this.status == 200) {
			var r = this.responseText;
			if (r.indexOf("Error") == -1) {
				el("zip_download_"+ id).innerHTML = r;
			} else {
				alert(r);
			}
		}
	});
}

function deletePathZip(id, url) {
	ajaxGet(url, function(){
		if (this.readyState == 4 && this.status == 200) {
			el("zip_download_"+ id).innerHTML = "Download";
		}
	});
}

function getDumpFolderName() {
	var y = el("dumpundump_y").value,
		 m = el("dumpundump_m").value,
		 d = el("dumpundump_d").value;
	return y +"-"+ m +"-" + d;
}

function setDumpFileFolderPrefix(x) {
	var a = document.getElementsByTagName("span"), r, c, i, l;
	for (i = 0, l = a.length; i < l; i++) {
		c = a[i];
		r = c.getAttribute("role");
		if (r && r == "folder-y" && x == "Y") {
			c.innerHTML = el("dumpundump_y").value;
		}
		if (r && r == "folder-m" && x == "M") {
			c.innerHTML = el("dumpundump_m").value;
		}
		if (r && r == "folder-d" && x == "D") {
			c.innerHTML = el("dumpundump_d").value;
		}
	}
}

function ajaxGet(url, callback) {
	var req = new XMLHttpRequest();
	req.onreadystatechange = callback;
	req.open("GET", url, true);
	req.send(null);
}

function ajaxPost(url, params, callback) {
	var req = new XMLHttpRequest();
	req.onreadystatechange = callback;
	req.open("POST", url, true);
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send(params);
}

function el(e) { return document.getElementById(e); }
</script>

<? include_once("footer.php"); ?>
