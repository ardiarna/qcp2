<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['35'];
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
		$whdua .= " and qml_plant_code = '".$app_subplan."'";
	}
	if($_POST['qml_plant_code']) {
		$whdua .= " and qml_plant_code = '".$_POST['qml_plant_code']."'";
	}
	if($_POST['qml_kode']) {
		$whdua .= " and qml_kode = '".$_POST['qml_kode']."'";
	}
	if($_POST['qml_nama']) {
		$whdua .= " and lower(qml_nama) like lower('%".$_POST['qml_nama']."%')";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_md_line where 1=1 $whsatu $whdua";
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
		$sql = "SELECT * from qc_md_line where 1=1 $whsatu $whdua order by $sidx $sord $limit";
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
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qml_plant_code'].'\',\''.$ro['qml_kode'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qml_plant_code'].'\',\''.$ro['qml_kode'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qml_plant_code'],$ro['qml_kode'],$ro['qml_nama'],$ro['kontrol']); 
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_plan_id;
	$pkey_qml_plant_code = $_POST['pkey_qml_plant_code'];
	$pkey_qml_kode = $_POST['pkey_qml_kode'];
	$qml_plant_code = $_POST['qml_plant_code'];
	$qml_kode = $_POST['qml_kode'];
	$qml_nama = $_POST['qml_nama'];
	if($stat=='add'){
		$sql = "INSERT INTO qc_md_line(qml_plant_code,qml_kode,qml_nama) values('{$qml_plant_code}','{$qml_kode}','{$qml_nama}')";
	}else if($stat=='edit'){
		$sql = "UPDATE qc_md_line set qml_plant_code='{$qml_plant_code}', qml_kode='{$qml_kode}', qml_nama='{$qml_nama}' where qml_plant_code='{$pkey_qml_plant_code}' and qml_kode='{$pkey_qml_kode}'";
	}
	echo dbsave_plan($app_plan_id, $sql);
}

function hapus(){
	global $app_plan_id;
	$pkey_qml_plant_code = $_POST['qml_plant_code'];
	$pkey_qml_kode = $_POST['qml_kode'];
	$sql = "DELETE from qc_md_line where qml_plant_code='{$pkey_qml_plant_code}' and qml_kode='{$pkey_qml_kode}'";
	echo dbsave_plan($app_plan_id, $sql);
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit") {
		$pkey_qml_plant_code = $_POST['qml_plant_code'];
		$pkey_qml_kode = $_POST['qml_kode'];
		$sql0 = "SELECT * from qc_md_line where qml_plant_code='{$pkey_qml_plant_code}' and qml_kode='{$pkey_qml_kode}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$responce->qml_plant_code = $rhead[qml_plant_code];
		$responce->qml_kode = $rhead[qml_kode];
		$responce->qml_nama = $rhead[qml_nama];
		$responce->sub_plan = cbo_subplant($rhead[qml_plant_code]);
	} else if($stat == "add"){
		$responce->sub_plan = cbo_subplant();
	}
    echo json_encode($responce);
}

?>