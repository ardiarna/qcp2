<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['11'];
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
	$page = $_POST['page']; // get the requested page 
	$rows = $_POST['rows']; // get how many rows we want to have into the grid 
	$sidx = $_POST['sidx']; // get index row - i.e. user click to sort 
	$sord = $_POST['sord']; // get the direction
	$whsatu = dptKondisiWhere($_POST['_search'],$_POST['filters'],$_POST['searchField'],$_POST['searchOper'],$_POST['searchString']);
	$whdua = "";
	if($app_subplan <> 'All') {
		$whdua .= " and qbm_plant_code = '".$app_subplan."'";
	}
	if($_POST['qbm_plant_code']) {
		$whdua .= " and qbm_plant_code = '".$_POST['qbm_plant_code']."'";
	}
	if($_POST['qbm_kode']) {
		$whdua .= " and qbm_kode = '".$_POST['qbm_kode']."'";
	}
	if($_POST['qbm_capacity']) {
		$whdua .= " and qbm_capacity = ".$_POST['qbm_capacity'];
	}
	if($_POST['qbm_desc']) {
		$whdua .= " and lower(qbm_desc) like lower('%".$_POST['qbm_desc']."%')";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_bm_unit where 1=1 $whsatu $whdua";
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
		$sql = "SELECT * from qc_bm_unit where 1=1 $whsatu $whdua order by $sidx $sord $limit";
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
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qbm_plant_code'].'\',\''.$ro['qbm_kode'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qbm_plant_code'].'\',\''.$ro['qbm_kode'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qbm_plant_code'],$ro['qbm_kode'],$ro['qbm_capacity'],$ro['qbm_desc'],$ro['kontrol']); 
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_plan_id;
	$pkey_qbm_plant_code= $_POST['pkey_qbm_plant_code'];
	$pkey_qbm_kode = $_POST['pkey_qbm_kode'];
	$qbm_plant_code = $_POST['qbm_plant_code'];
	$qbm_kode = $_POST['qbm_kode'];
	$qbm_capacity = $_POST['qbm_capacity'] ? $_POST['qbm_capacity'] : 'NULL';
	$qbm_desc = $_POST['qbm_desc'];
	if($stat=='add'){
		$sql = "INSERT INTO qc_bm_unit(qbm_plant_code,qbm_kode,qbm_capacity,qbm_desc) values('{$qbm_plant_code}','{$qbm_kode}',{$qbm_capacity},'{$qbm_desc}')";
	}else if($stat=='edit'){
		$sql = "UPDATE qc_bm_unit set qbm_plant_code='{$qbm_plant_code}', qbm_kode='{$qbm_kode}', qbm_capacity={$qbm_capacity}, qbm_desc='{$qbm_desc}' where qbm_plant_code='{$pkey_qbm_plant_code}' and qbm_kode='{$pkey_qbm_kode}'";
	}
	echo dbsave_plan($app_plan_id, $sql);
}

function hapus(){
	global $app_plan_id;
	$pkey_qbm_plant_code= $_POST['qbm_plant_code'];
	$pkey_qbm_kode = $_POST['qbm_kode'];
	$sql = "DELETE from qc_bm_unit where qbm_plant_code='{$pkey_qbm_plant_code}' and qbm_kode='{$pkey_qbm_kode}'";
	echo dbsave_plan($app_plan_id, $sql);
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit") {
		$pkey_qbm_plant_code= $_POST['qbm_plant_code'];
		$pkey_qbm_kode = $_POST['qbm_kode'];
		$sql0 = "SELECT * from qc_bm_unit where qbm_plant_code='{$pkey_qbm_plant_code}' and qbm_kode='{$pkey_qbm_kode}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$responce->qbm_plant_code = $rhead[qbm_plant_code];
		$responce->qbm_kode = $rhead[qbm_kode];
		$responce->qbm_capacity = $rhead[qbm_capacity];
		$responce->qbm_desc = $rhead[qbm_desc];
		$responce->sub_plan = cbo_subplant($rhead[qbm_plant_code]);
	} else if($stat == "add"){
		$responce->sub_plan = cbo_subplant();
	}
    echo json_encode($responce);
}

?>