<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['15'];
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
	case "suburai":
		suburai();
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
		$whdua .= " and qpp_sub_plant = '".$app_subplan."'";
	}
	if($_POST['qpp_sub_plant']) {
		$whdua .= " and qpp_sub_plant = '".$_POST['qpp_sub_plant']."'";
	}
	if($_POST['qpp_code']) {
		$whdua .= " and qpp_code = '".$_POST['qpp_code']."'";
	}
	if($_POST['qpp_cap']) {
		$whdua .= " and qpp_cap = ".$_POST['qpp_cap'];
	}
	if($_POST['qpp_desc']) {
		$whdua .= " and lower(qpp_desc) like lower('%".$_POST['qpp_desc']."%')";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_pd_press where 1=1 $whsatu $whdua";
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
		$sql = "SELECT * from qc_pd_press where 1=1 $whsatu $whdua order by $sidx $sord $limit";
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
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qpp_sub_plant'].'\',\''.$ro['qpp_code'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qpp_sub_plant'].'\',\''.$ro['qpp_code'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qpp_sub_plant'],$ro['qpp_code'],$ro['qpp_cap'],$ro['qpp_desc'],$ro['kontrol']); 
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_plan_id;
	$pkey_qpp_sub_plant= $_POST['pkey_qpp_sub_plant'];
	$pkey_qpp_code = $_POST['pkey_qpp_code'];
	$qpp_sub_plant = $_POST['qpp_sub_plant'];
	$qpp_code = $_POST['qpp_code'];
	$qpp_cap = $_POST['qpp_cap'] ? $_POST['qpp_cap'] : 'NULL';
	$qpp_desc = $_POST['qpp_desc'];
	if($stat=='add'){
		$sql = "INSERT INTO qc_pd_press(qpp_sub_plant,qpp_code,qpp_cap,qpp_desc) values('{$qpp_sub_plant}','{$qpp_code}',{$qpp_cap},'{$qpp_desc}')";
	}else if($stat=='edit'){
		$sql = "UPDATE qc_pd_press set qpp_sub_plant='{$qpp_sub_plant}', qpp_code='{$qpp_code}', qpp_cap={$qpp_cap}, qpp_desc='{$qpp_desc}' where qpp_sub_plant='{$pkey_qpp_sub_plant}' and qpp_code='{$pkey_qpp_code}'";
	}
	echo dbsave_plan($app_plan_id, $sql);
}

function hapus(){
	global $app_plan_id;
	$pkey_qpp_sub_plant= $_POST['qpp_sub_plant'];
	$pkey_qpp_code = $_POST['qpp_code'];
	$sql = "DELETE from qc_pd_press where qpp_sub_plant='{$pkey_qpp_sub_plant}' and qpp_code='{$pkey_qpp_code}'";
	echo dbsave_plan($app_plan_id, $sql);
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit") {
		$pkey_qpp_sub_plant= $_POST['qpp_sub_plant'];
		$pkey_qpp_code = $_POST['qpp_code'];
		$sql0 = "SELECT * from qc_pd_press where qpp_sub_plant='{$pkey_qpp_sub_plant}' and qpp_code='{$pkey_qpp_code}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$responce->qpp_sub_plant = $rhead[qpp_sub_plant];
		$responce->qpp_code = $rhead[qpp_code];
		$responce->qpp_cap = $rhead[qpp_cap];
		$responce->qpp_desc = $rhead[qpp_desc];
		$responce->sub_plan = cbo_subplant($rhead[qpp_sub_plant]);
	} else if($stat == "add"){
		$responce->sub_plan = cbo_subplant();
	}
    echo json_encode($responce);
}

function suburai(){
	global $app_plan_id;
	$qpp_sub_plant = $_POST['qpp_sub_plant'];
	$qpp_code = $_POST['qpp_code'];
	$sql = "SELECT * from qc_pd_mouldset where qpm_sub_plant='{$qpp_sub_plant}' and qpm_press_code='{$qpp_code}' order by qpm_sub_plant, qpm_press_code, qpm_code";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	foreach($qry as $ro){
		$responce->rows[$i]['cell']=array($ro['qpm_sub_plant'],$ro['qpm_press_code'],$ro['qpm_code'],$ro['qpm_desc']);
		$i++;
	}
	$responce->sql = $sql;		
	echo json_encode($responce);	
}

?>