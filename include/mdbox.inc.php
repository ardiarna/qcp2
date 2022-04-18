<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['12'];
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
	case "detailtabel":
		detailtabel($_POST['stat']);
		break;
}

function urai(){
	global $app_plan_id, $akses, $app_subplan;
	$page = $_POST['page']; 
	$rows = $_POST['rows'];
	$sidx = $_POST['sidx']; 
	$sord = $_POST['sord'];
	$whsatu = dptKondisiWhere($_POST['_search'],$_POST['filters'],$_POST['searchField'],$_POST['searchOper'],$_POST['searchString']);
	$whdua = "";
	if($app_subplan <> 'All') {
		$whdua .= " and qbu_sub_plant = '".$app_subplan."'";
	}
	if($_POST['qbu_sub_plant']) {
		$whdua .= " and qbu_sub_plant = '".$_POST['qbu_sub_plant']."'";
	}
	if($_POST['qbu_kode']) {
		$whdua .= " and qbu_kode = '".$_POST['qbu_kode']."'";
	}
	if($_POST['qbu_desc']) {
		$whdua .= " and lower(qbu_desc) like lower('%".$_POST['qbu_desc']."%')";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_box_unit where 1=1 $whsatu $whdua";
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
		$sql = "SELECT * from qc_box_unit where 1=1 $whsatu $whdua order by $sidx $sord $limit";
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
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qbu_sub_plant'].'\',\''.$ro['qbu_kode'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qbu_sub_plant'].'\',\''.$ro['qbu_kode'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qbu_sub_plant'],$ro['qbu_kode'],$ro['qbu_desc'],$ro['kontrol']); 
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_plan_id;
	$pkey_qbu_sub_plant = $_POST['pkey_qbu_sub_plant'];
	$pkey_qbu_kode = $_POST['pkey_qbu_kode'];
	$qbu_sub_plant = $_POST['qbu_sub_plant'];
	$qbu_kode = $_POST['qbu_kode'];
	$qbu_desc = $_POST['qbu_desc'];
	if($stat=='add'){
		$sql = "INSERT INTO qc_box_unit(qbu_sub_plant,qbu_kode,qbu_desc) values('{$qbu_sub_plant}','{$qbu_kode}','{$qbu_desc}')";
	}else if($stat=='edit'){
		$sql = "UPDATE qc_box_unit set qbu_sub_plant='{$qbu_sub_plant}', qbu_kode='{$qbu_kode}', qbu_desc='{$qbu_desc}' where qbu_sub_plant='{$pkey_qbu_sub_plant}' and qbu_kode='{$pkey_qbu_kode}'";
	}
	echo dbsave_plan($app_plan_id, $sql);
}

function hapus(){
	global $app_plan_id;
	$pkey_qbu_sub_plant = $_POST['qbu_sub_plant'];
	$pkey_qbu_kode = $_POST['qbu_kode'];
	$sql = "DELETE from qc_box_unit where qbu_sub_plant='{$pkey_qbu_sub_plant}' and qbu_kode='{$pkey_qbu_kode}'";
	echo dbsave_plan($app_plan_id, $sql);
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit") {
		$pkey_qbu_sub_plant = $_POST['qbu_sub_plant'];
		$pkey_qbu_kode = $_POST['qbu_kode'];
		$sql0 = "SELECT * from qc_box_unit where qbu_sub_plant='{$pkey_qbu_sub_plant}' and qbu_kode='{$pkey_qbu_kode}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$responce->qbu_sub_plant = $rhead[qbu_sub_plant];
		$responce->qbu_kode = $rhead[qbu_kode];
		$responce->qbu_desc = $rhead[qbu_desc];
		$responce->sub_plan = cbo_subplant($rhead[qbu_sub_plant]);
	} else if($stat == "add"){
		$responce->sub_plan = cbo_subplant();
	}
    echo json_encode($responce);
}

?>