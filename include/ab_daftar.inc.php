<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['48'];
$app_subplan = $_SESSION[$app_id]['user']['sub_plan'];

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
	case "cboabnama":
		cboabnama();
		break;
	case "detailtabel":
		detailtabel($_POST['stat']);
		break;
}

function urai(){
	global $app_plan_id, $akses, $app_subplan;
	$page = $_POST['page']; // get the requested page 
	$rows = $_POST['rows']; // get how many rows we want to have into the grid 
	$sidx = $_POST['sidx']; // get index row - i.e. user click to sort 
	$sord = $_POST['sord']; // get the direction
	$whsatu = dptKondisiWhere($_POST['_search'],$_POST['filters'],$_POST['searchField'],$_POST['searchOper'],$_POST['searchString']);
	$whdua = "";
	if($_POST['qab_nama']) {
		$whdua .= " and lower(qab_nama) like lower('%".$_POST['qab_nama']."%')";
	}
	if($_POST['qab_nomor']) {
		$whdua .= " and lower(qab_nomor) like lower('%".$_POST['qab_nomor']."%')";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_alat_berat where 1=1 $whsatu $whdua";
	$r = dbselect_plan($app_plan_id, $sql);
	$count = $r['count'];
	if($count > 0) { 
		if($rows == -1){
			$total_pages = 1;
			$limit = "";
		} else {
			$total_pages = ceil($count / $rows);
			$start = $rows * $page - $rows;
			$limit = "limit ".$rows." offset ".$start;
		}
		$sql = "SELECT * from qc_alat_berat where 1=1 $whsatu $whdua order by $sidx $sord $limit";
		$qry = dbselect_plan_all($app_plan_id, $sql);
		$i = 0;
	} else { 
		$total_pages = 1; 
	}
	if($page > $total_pages) $page = $total_pages; 
	$responce->page = $page; 
	$responce->total = $total_pages; 
	$responce->records = $count; 
	if( $count > 0 ) {
		foreach($qry as $ro){
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qab_nama'].'\',\''.$ro['qab_nomor'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qab_nama'].'\',\''.$ro['qab_nomor'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qab_nama'],$ro['qab_nomor'],$ro['kontrol']); 
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_plan_id;
	$pkey_qab_nama = $_POST['pkey_qab_nama'];
	$pkey_qab_nomor = $_POST['pkey_qab_nomor'];
	$qab_nama = $_POST['qab_nama'];
	$qab_nomor = $_POST['qab_nomor'];
	if($stat=='add'){
		$sql = "INSERT INTO qc_alat_berat(qab_nama,qab_nomor) values('{$qab_nama}','{$qab_nomor}')";
	}else if($stat=='edit'){
		$sql = "UPDATE qc_alat_berat set qab_nama='{$qab_nama}', qab_nomor='{$qab_nomor}' where qab_nama='{$pkey_qab_nama}' and qab_nomor='{$pkey_qab_nomor}'";
	}
	echo dbsave_plan($app_plan_id, $sql);
}

function hapus(){
	global $app_plan_id;
	$pkey_qab_nama = $_POST['qab_nama'];
	$pkey_qab_nomor = $_POST['qab_nomor'];
	$sql = "DELETE from qc_alat_berat where qab_nama='{$pkey_qab_nama}' and qab_nomor='{$pkey_qab_nomor}'";
	echo dbsave_plan($app_plan_id, $sql);
}

function cboabnama($nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT distinct qab_nama from qc_alat_berat";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[qab_nama] == $nilai){
				$out .= "<option selected>$r[qab_nama]</option>";
			} else {
				$out .= "<option>$r[qab_nama]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;	
	}
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit") {
		$pkey_qab_nama = $_POST['qab_nama'];
		$pkey_qab_nomor = $_POST['qab_nomor'];
		$sql0 = "SELECT * from qc_alat_berat where qab_nama='{$pkey_qab_nama}' and qab_nomor='{$pkey_qab_nomor}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$responce->qab_nama = $rhead[qab_nama];
		$responce->qab_nomor = $rhead[qab_nomor];
	} 
    echo json_encode($responce);
}

?>