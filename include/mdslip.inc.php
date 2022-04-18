<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['14'];
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
		$whdua .= " and qct_sub_plant = '".$app_subplan."'";
	}
	if($_POST['qct_sub_plant']) {
		$whdua .= " and qct_sub_plant = '".$_POST['qct_sub_plant']."'";
	}
	if($_POST['qct_code']) {
		$whdua .= " and qct_code = '".$_POST['qct_code']."'";
	}
	if($_POST['qct_cap']) {
		$whdua .= " and qct_cap = ".$_POST['qct_cap'];
	}
	if($_POST['qct_desc']) {
		$whdua .= " and lower(qct_desc) like lower('%".$_POST['qct_desc']."%')";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_cb_slip_tank where 1=1 $whsatu $whdua";
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
		$sql = "SELECT * from qc_cb_slip_tank where 1=1 $whsatu $whdua order by $sidx $sord $limit";
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
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qct_sub_plant'].'\',\''.$ro['qct_code'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qct_sub_plant'].'\',\''.$ro['qct_code'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qct_sub_plant'],$ro['qct_code'],$ro['qct_cap'],$ro['qct_desc'],$ro['kontrol']); 
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_plan_id;
	$pkey_qct_sub_plant= $_POST['pkey_qct_sub_plant'];
	$pkey_qct_code = $_POST['pkey_qct_code'];
	$qct_sub_plant = $_POST['qct_sub_plant'];
	$qct_code = $_POST['qct_code'];
	$qct_cap = $_POST['qct_cap'] ? $_POST['qct_cap'] : 'NULL';
	$qct_desc = $_POST['qct_desc'];
	if($stat=='add'){
		$sql = "INSERT INTO qc_cb_slip_tank(qct_sub_plant,qct_code,qct_cap,qct_desc) values('{$qct_sub_plant}','{$qct_code}',{$qct_cap},'{$qct_desc}')";
	}else if($stat=='edit'){
		$sql = "UPDATE qc_cb_slip_tank set qct_sub_plant='{$qct_sub_plant}', qct_code='{$qct_code}', qct_cap={$qct_cap}, qct_desc='{$qct_desc}' where qct_sub_plant='{$pkey_qct_sub_plant}' and qct_code='{$pkey_qct_code}'";
	}
	echo dbsave_plan($app_plan_id, $sql);
}

function hapus(){
	global $app_plan_id;
	$pkey_qct_sub_plant= $_POST['qct_sub_plant'];
	$pkey_qct_code = $_POST['qct_code'];
	$sql = "DELETE from qc_cb_slip_tank where qct_sub_plant='{$pkey_qct_sub_plant}' and qct_code='{$pkey_qct_code}'";
	echo dbsave_plan($app_plan_id, $sql);
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit") {
		$pkey_qct_sub_plant= $_POST['qct_sub_plant'];
		$pkey_qct_code = $_POST['qct_code'];
		$sql0 = "SELECT * from qc_cb_slip_tank where qct_sub_plant='{$pkey_qct_sub_plant}' and qct_code='{$pkey_qct_code}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$responce->qct_sub_plant = $rhead[qct_sub_plant];
		$responce->qct_code = $rhead[qct_code];
		$responce->qct_cap = $rhead[qct_cap];
		$responce->qct_desc = $rhead[qct_desc];
		$responce->sub_plan = cbo_subplant($rhead[qct_sub_plant]);
	} else if($stat == "add"){
		$responce->sub_plan = cbo_subplant();
	}
    echo json_encode($responce);
}

?>