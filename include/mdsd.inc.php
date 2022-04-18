<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['17'];
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
		$whdua .= " and qps_sub_plant = '".$app_subplan."'";
	}
	if($_POST['qps_sub_plant']) {
		$whdua .= " and qps_sub_plant = '".$_POST['qps_sub_plant']."'";
	}
	if($_POST['qps_code']) {
		$whdua .= " and qps_code = '".$_POST['qps_code']."'";
	}
	if($_POST['qps_cap']) {
		$whdua .= " and qps_cap = ".$_POST['qps_cap'];
	}
	if($_POST['qps_desc']) {
		$whdua .= " and lower(qps_desc) like lower('%".$_POST['qps_desc']."%')";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_pd_sd where 1=1 $whsatu $whdua";
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
		$sql = "SELECT * from qc_pd_sd where 1=1 $whsatu $whdua order by $sidx $sord $limit";
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
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qps_sub_plant'].'\',\''.$ro['qps_code'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qps_sub_plant'].'\',\''.$ro['qps_code'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qps_sub_plant'],$ro['qps_code'],$ro['qps_cap'],$ro['qps_desc'],$ro['kontrol']); 
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_plan_id;
	$pkey_qps_sub_plant= $_POST['pkey_qps_sub_plant'];
	$pkey_qps_code = $_POST['pkey_qps_code'];
	$qps_sub_plant = $_POST['qps_sub_plant'];
	$qps_code = $_POST['qps_code'];
	$qps_cap = $_POST['qps_cap'] ? $_POST['qps_cap'] : 'NULL';
	$qps_desc = $_POST['qps_desc'];
	if($stat=='add'){
		$sql = "INSERT INTO qc_pd_sd(qps_sub_plant,qps_code,qps_cap,qps_desc) values('{$qps_sub_plant}','{$qps_code}',{$qps_cap},'{$qps_desc}')";
	}else if($stat=='edit'){
		$sql = "UPDATE qc_pd_sd set qps_sub_plant='{$qps_sub_plant}', qps_code='{$qps_code}', qps_cap={$qps_cap}, qps_desc='{$qps_desc}' where qps_sub_plant='{$pkey_qps_sub_plant}' and qps_code='{$pkey_qps_code}'";
	}
	echo dbsave_plan($app_plan_id, $sql);
}

function hapus(){
	global $app_plan_id;
	$pkey_qps_sub_plant= $_POST['qps_sub_plant'];
	$pkey_qps_code = $_POST['qps_code'];
	$sql = "DELETE from qc_pd_sd where qps_sub_plant='{$pkey_qps_sub_plant}' and qps_code='{$pkey_qps_code}'";
	echo dbsave_plan($app_plan_id, $sql);
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit") {
		$pkey_qps_sub_plant= $_POST['qps_sub_plant'];
		$pkey_qps_code = $_POST['qps_code'];
		$sql0 = "SELECT * from qc_pd_sd where qps_sub_plant='{$pkey_qps_sub_plant}' and qps_code='{$pkey_qps_code}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$responce->qps_sub_plant = $rhead[qps_sub_plant];
		$responce->qps_code = $rhead[qps_code];
		$responce->qps_cap = $rhead[qps_cap];
		$responce->qps_desc = $rhead[qps_desc];
		$responce->sub_plan = cbo_subplant($rhead[qps_sub_plant]);
	} else if($stat == "add"){
		$responce->sub_plan = cbo_subplant();
	}
    echo json_encode($responce);
}

?>