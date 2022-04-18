<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['30'];

if($_GET["mode"]) {
	$oper = $_GET["mode"];
} else {
	$oper = $_POST["oper"];
}

switch ($oper) {
	case "urai":
		urai();
		break;
	case "add":
		simpan("add");
		break;
	case "edit":
		simpan("edit");
		break;
	case "hapus":
		hapus();
		break;
	case "detailtabel":
		detailtabel($_POST['stat']);
		break;
	case "cboparent":
		cboparent();
		break;
	case "naiksort":
		ubahsort("naik");
		break;
	case "turunsort":
		ubahsort("turun");
		break;
}

function display_node($parent, $padding) {
	global $app_plan_id, $akses;
	
	if($parent > 0) {
		$whparent = "a.am_parent = ".$parent;
	} else {
		$whparent = "a.am_parent is null";
	}
	$sql = "SELECT a.am_id, a.am_label, a.am_link, a.am_parent, a.am_class, a.am_sort, b.am_count, c.max_sort, a.am_stats
		from app_menu a 
		left join (SELECT am_parent, count(*) as am_count from app_menu group by am_parent) b ON(a.am_id=b.am_parent)
		left join (SELECT am_parent, max(am_sort) as max_sort from app_menu group by am_parent) c ON(coalesce(a.am_parent,0)=coalesce(c.am_parent,0))
		where $whparent order by a.am_sort";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	foreach($qry as $ro){
		$btn_tambah = $akses[add] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="tambahData(\''.$ro[am_id].'\')"><span class="glyphicon glyphicon-plus"></span></button> ' : '';
		$btn_edit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro[am_id].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
		$btn_hapus = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro[am_id].'\',\''.$ro[am_label].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
		if($akses[edit] == 'Y') {
			$btn_up = $ro[am_sort] != "1" ? '<button class="btn btn-default btn-xs" onClick="naikSort(\''.$ro[am_id].'\',\''.$ro[am_sort].'\',\''.$ro[am_parent].'\')"><span class="glyphicon glyphicon-chevron-up"></span></button> ' : '';
			$btn_down = $ro[am_sort] != $ro[max_sort] ? '<button class="btn btn-default btn-xs" onClick="turunSort(\''.$ro[am_id].'\',\''.$ro[am_sort].'\',\''.$ro[am_parent].'\')"><span class="glyphicon glyphicon-chevron-down"></span></button> ' : '';
		}

		if($ro[am_stats] == 'Y'){
			$warna = "";
		}else{
			$warna = "style='color:red;'";
		}

		$out .= '<tr>
    		<td>'.$ro[am_id].'</td>
    		<td style="padding-left:'.$padding.'px;"><i class="'.$ro[am_class].'"></i> <span '.$warna.'>'.$ro[am_label].'<span></td>
    		<td><span '.$warna.'>'.$ro[am_link].'</span></td>
    		<td class="text-center"><span '.$warna.'>'.$ro[am_stats].'</span></td>
    		<td class="text-center">'.$btn_tambah.$btn_edit.$btn_hapus.$btn_up.$btn_down.'</td>
    		</tr>';
		if ($ro[am_count] > 0) {
			$out .= display_node($ro[am_id], $padding+20);
        }
	}
	return $out;
}

function urai(){
	global $akses;
	$out = '<table id="tabeldetail" class="table table-bordered table-striped table-condensed">
		<tr><th style="width:50px">ID</th><th>LABEL</th><th>LINK</th><th>ACTIVE</th><th width="150">KONTROL</th></tr>';
	if($akses[add] == 'Y') {
		$out .= '<tr><td colspan="4"><button class="btn btn-default btn-xs" onClick="tambahData(\'0\')"><span class="glyphicon glyphicon-plus"></span> Add Main Menu</button></td></tr>';
	}
	$out .= display_node(0, 10);
	$out .= "</table>";

	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

function simpan($stat){
	global $app_plan_id;
	$am_id = $_POST['am_id'];
	$am_label = $_POST['am_label'];
	$am_parent = cgx_null($_POST['am_parent']);
	$am_sort = cgx_null($_POST['am_sort']);
	$am_link = $_POST['am_link'];
	$am_class = $_POST['am_class'];
	$am_stats = $_POST['am_stats'];
	if($stat=='add'){
		$sql = "INSERT INTO app_menu(am_label,am_parent,am_sort,am_link,am_class, am_stats) values('{$am_label}',{$am_parent},{$am_sort},'{$am_link}','{$am_class}','{$am_stats}')";
	}else if($stat=='edit'){
		$sql = "UPDATE app_menu set am_label='{$am_label}', am_parent={$am_parent}, am_sort={$am_sort}, am_link='{$am_link}', am_class='{$am_class}', am_stats='{$am_stats}' where am_id='{$am_id}'";
	}
	echo dbsave_plan($app_plan_id, $sql);
}

function hapus(){
	global $app_plan_id;
	$pkey_am_id= $_POST['am_id'];
	$sql = "DELETE from app_menu where am_id='{$pkey_am_id}'";
	echo dbsave_plan($app_plan_id, $sql);
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit") {
		$pkey_am_id= $_POST['am_id'];
		$sql0 = "SELECT a.*, c.am_label as parent_label 
			from app_menu a left join app_menu c on(a.am_parent=c.am_id) where a.am_id={$pkey_am_id}";
		$rhead =dbselect_plan($app_plan_id, $sql0);
		$responce->am_id = $rhead[am_id];
		$responce->am_label = $rhead[am_label];
		$responce->am_parent = $rhead[am_parent];
		$responce->am_sort = $rhead[am_sort];
		$responce->am_link = $rhead[am_link];
		$responce->am_class = $rhead[am_class];
		$responce->am_stats = $rhead[am_stats];
		$responce->parent_label = $rhead[parent_label];
	} else {
		$pkey_am_id= $_POST['am_id'];
		if($pkey_am_id > 0) {
			$sql0 = "SELECT a.*, coalesce(c.max_sort,0) as max_sort from app_menu a left join (SELECT am_parent, max(am_sort) as max_sort from app_menu group by am_parent) c ON(coalesce(a.am_id,0)=coalesce(c.am_parent,0)) where a.am_id={$pkey_am_id}";	
		} else {
			$sql0 = "SELECT max(am_sort) as max_sort from app_menu where am_parent is null or am_parent = 0";
		}
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$responce->am_id = $rhead[am_id];
		$responce->am_label = $rhead[am_label];
		$responce->am_sort = $rhead[max_sort]+1;
	}		
    echo json_encode($responce);
}

function cboparent($nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT am_id, am_label from app_menu order by am_parent, am_sort";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[am_id] == $nilai){
				$out .= "<option value='{$r[am_id]}' selected>$r[am_id] - $r[am_label]</option>";
			} else {
				$out .= "<option value='{$r[am_id]}'>$r[am_id] - $r[am_label]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;	
	}
}

function ubahsort($stat){
	global $app_plan_id;
	$am_id = $_POST['am_id'];
	$am_sort = $_POST['am_sort'];
	$am_parent = $_POST['am_parent'];
	$new_sort = $stat == "naik" ? $am_sort - 1 : $am_sort + 1;
	if($am_parent) {
		$sql = "UPDATE app_menu set am_sort={$am_sort} where am_parent='{$am_parent}' and am_sort={$new_sort}; UPDATE app_menu set am_sort={$new_sort} where am_id='{$am_id}';";
	} else {
		$sql = "UPDATE app_menu set am_sort={$am_sort} where am_parent is null and am_sort={$new_sort}; UPDATE app_menu set am_sort={$new_sort} where am_id='{$am_id}';";
	}
	echo dbsave_plan($app_plan_id, $sql);
}

?>