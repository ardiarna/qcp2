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
		$whdua .= " and qph_sub_plant = '".$app_subplan."'";
	}
	if($_POST['qph_sub_plant']) {
		$whdua .= " and qph_sub_plant = '".$_POST['qph_sub_plant']."'";
	}
	if($_POST['qph_code']) {
		$whdua .= " and qph_code = '".$_POST['qph_code']."'";
	}
	if($_POST['qph_cap']) {
		$whdua .= " and qph_cap = ".$_POST['qph_cap'];
	}
	if($_POST['qph_desc']) {
		$whdua .= " and lower(qph_desc) like lower('%".$_POST['qph_desc']."%')";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_pd_hd where 1=1 $whsatu $whdua";
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
		$sql = "SELECT * from qc_pd_hd where 1=1 $whsatu $whdua order by $sidx $sord $limit";
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
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qph_sub_plant'].'\',\''.$ro['qph_code'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qph_sub_plant'].'\',\''.$ro['qph_code'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qph_sub_plant'],$ro['qph_code'],$ro['qph_cap'],$ro['qph_desc'],$ro['kontrol']); 
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_plan_id;
	$pkey_qph_sub_plant= $_POST['pkey_qph_sub_plant'];
	$pkey_qph_code = $_POST['pkey_qph_code'];
	$qph_sub_plant = $_POST['qph_sub_plant'];
	$qph_code = $_POST['qph_code'];
	$qph_cap = $_POST['qph_cap'] ? $_POST['qph_cap'] : 'NULL';
	$qph_desc = $_POST['qph_desc'];
	if($stat=='add'){
		$sql = "INSERT INTO qc_pd_hd(qph_sub_plant,qph_code,qph_cap,qph_desc) values('{$qph_sub_plant}','{$qph_code}',{$qph_cap},'{$qph_desc}')";
	}else if($stat=='edit'){
		$sql = "UPDATE qc_pd_hd set qph_sub_plant='{$qph_sub_plant}', qph_code='{$qph_code}', qph_cap={$qph_cap}, qph_desc='{$qph_desc}' where qph_sub_plant='{$pkey_qph_sub_plant}' and qph_code='{$pkey_qph_code}'";
	}
	echo dbsave_plan($app_plan_id, $sql);
}

function hapus(){
	global $app_plan_id;
	$pkey_qph_sub_plant= $_POST['qph_sub_plant'];
	$pkey_qph_code = $_POST['qph_code'];
	$sql = "DELETE from qc_pd_hd where qph_sub_plant='{$pkey_qph_sub_plant}' and qph_code='{$pkey_qph_code}'";
	echo dbsave_plan($app_plan_id, $sql);
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit") {
		$pkey_qph_sub_plant= $_POST['qph_sub_plant'];
		$pkey_qph_code = $_POST['qph_code'];
		$sql0 = "SELECT * from qc_pd_hd where qph_sub_plant='{$pkey_qph_sub_plant}' and qph_code='{$pkey_qph_code}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$responce->qph_sub_plant = $rhead[qph_sub_plant];
		$responce->qph_code = $rhead[qph_code];
		$responce->qph_cap = $rhead[qph_cap];
		$responce->qph_desc = $rhead[qph_desc];
		$responce->sub_plan = cbo_subplant($rhead[qph_sub_plant]);
	} else if($stat == "add"){
		$responce->sub_plan = cbo_subplant();
	}
    echo json_encode($responce);
}

?>