<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['13'];
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
		$whdua .= " and qcs_sub_plant = '".$app_subplan."'";
	}
	if($_POST['qcs_sub_plant']) {
		$whdua .= " and qcs_sub_plant = '".$_POST['qcs_sub_plant']."'";
	}
	if($_POST['qcs_code']) {
		$whdua .= " and qcs_code = '".$_POST['qcs_code']."'";
	}
	if($_POST['qcs_cap']) {
		$whdua .= " and qcs_cap = ".$_POST['qcs_cap'];
	}
	if($_POST['qcs_desc']) {
		$whdua .= " and lower(qcs_desc) like lower('%".$_POST['qcs_desc']."%')";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_cb_silo where 1=1 $whsatu $whdua";
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
		$sql = "SELECT * from qc_cb_silo where 1=1 $whsatu $whdua order by $sidx $sord $limit";
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
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qcs_sub_plant'].'\',\''.$ro['qcs_code'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qcs_sub_plant'].'\',\''.$ro['qcs_code'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qcs_sub_plant'],$ro['qcs_code'],$ro['qcs_cap'],$ro['qcs_desc'],$ro['kontrol']); 
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_plan_id;
	$pkey_qcs_sub_plant= $_POST['pkey_qcs_sub_plant'];
	$pkey_qcs_code = $_POST['pkey_qcs_code'];
	$qcs_sub_plant = $_POST['qcs_sub_plant'];
	$qcs_code = $_POST['qcs_code'];
	$qcs_cap = $_POST['qcs_cap'] ? $_POST['qcs_cap'] : 'NULL';
	$qcs_desc = $_POST['qcs_desc'];
	if($stat=='add'){
		$sql = "INSERT INTO qc_cb_silo(qcs_sub_plant,qcs_code,qcs_cap,qcs_desc) values('{$qcs_sub_plant}','{$qcs_code}',{$qcs_cap},'{$qcs_desc}')";
	}else if($stat=='edit'){
		$sql = "UPDATE qc_cb_silo set qcs_sub_plant='{$qcs_sub_plant}', qcs_code='{$qcs_code}', qcs_cap={$qcs_cap}, qcs_desc='{$qcs_desc}' where qcs_sub_plant='{$pkey_qcs_sub_plant}' and qcs_code='{$pkey_qcs_code}'";
	}
	echo dbsave_plan($app_plan_id, $sql);
}

function hapus(){
	global $app_plan_id;
	$pkey_qcs_sub_plant= $_POST['qcs_sub_plant'];
	$pkey_qcs_code = $_POST['qcs_code'];
	$sql = "DELETE from qc_cb_silo where qcs_sub_plant='{$pkey_qcs_sub_plant}' and qcs_code='{$pkey_qcs_code}'";
	echo dbsave_plan($app_plan_id, $sql);
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit") {
		$pkey_qcs_sub_plant= $_POST['qcs_sub_plant'];
		$pkey_qcs_code = $_POST['qcs_code'];
		$sql0 = "SELECT * from qc_cb_silo where qcs_sub_plant='{$pkey_qcs_sub_plant}' and qcs_code='{$pkey_qcs_code}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$responce->qcs_sub_plant = $rhead[qcs_sub_plant];
		$responce->qcs_code = $rhead[qcs_code];
		$responce->qcs_cap = $rhead[qcs_cap];
		$responce->qcs_desc = $rhead[qcs_desc];
		$responce->sub_plan = cbo_subplant($rhead[qcs_sub_plant]);
	} else if($stat == "add"){
		$responce->sub_plan = cbo_subplant();
	}
    echo json_encode($responce);
}

?>