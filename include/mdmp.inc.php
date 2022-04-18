<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['16'];
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
	case "cbopress":
		cbopress($_POST['subplan']);
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
		$whdua .= " and qpm_sub_plant = '".$app_subplan."'";
	}
	if($_POST['qpm_sub_plant']) {
		$whdua .= " and qpm_sub_plant = '".$_POST['qpm_sub_plant']."'";
	}
	if($_POST['qpm_press_code']) {
		$whdua .= " and qpm_press_code = '".$_POST['qpm_press_code']."'";
	}
	if($_POST['qpm_code']) {
		$whdua .= " and qpm_code = '".$_POST['qpm_code']."'";
	}
	if($_POST['qpm_desc']) {
		$whdua .= " and lower(qpm_desc) like lower('%".$_POST['qpm_desc']."%')";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_pd_mouldset where 1=1 $whsatu $whdua";
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
		$sql = "SELECT * from qc_pd_mouldset where 1=1 $whsatu $whdua order by $sidx $sord $limit";
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
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qpm_sub_plant'].'\',\''.$ro['qpm_press_code'].'\',\''.$ro['qpm_code'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qpm_sub_plant'].'\',\''.$ro['qpm_press_code'].'\',\''.$ro['qpm_code'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qpm_sub_plant'],$ro['qpm_press_code'],$ro['qpm_code'],$ro['qpm_desc'],$ro['kontrol']); 
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_plan_id;
	$pkey_qpm_sub_plant= $_POST['pkey_qpm_sub_plant'];
	$pkey_qpm_press_code = $_POST['pkey_qpm_press_code'];
	$qpm_sub_plant = $_POST['qpm_sub_plant'];
	$qpm_press_code = $_POST['qpm_press_code'];
	$qpm_code = $_POST['qpm_code'] ? $_POST['qpm_code'] : 'NULL';
	$qpm_desc = $_POST['qpm_desc'];
	if($stat=='add'){
		$sql = "INSERT INTO qc_pd_mouldset(qpm_sub_plant,qpm_press_code,qpm_code,qpm_desc) values('{$qpm_sub_plant}','{$qpm_press_code}',{$qpm_code},'{$qpm_desc}')";
	}else if($stat=='edit'){
		$sql = "UPDATE qc_pd_mouldset set qpm_sub_plant='{$qpm_sub_plant}', qpm_press_code='{$qpm_press_code}', qpm_code={$qpm_code}, qpm_desc='{$qpm_desc}' where qpm_sub_plant='{$pkey_qpm_sub_plant}' and qpm_press_code='{$pkey_qpm_press_code}'";
	}
	echo dbsave_plan($app_plan_id, $sql);
}

function hapus(){
	global $app_plan_id;
	$pkey_qpm_sub_plant= $_POST['qpm_sub_plant'];
	$pkey_qpm_press_code = $_POST['qpm_press_code'];
	$pkey_qpm_code = $_POST['qpm_code'];
	$sql = "DELETE from qc_pd_mouldset where qpm_sub_plant='{$pkey_qpm_sub_plant}' and qpm_press_code='{$pkey_qpm_press_code}' and qpm_code='{$pkey_qpm_code}'";
	echo dbsave_plan($app_plan_id, $sql);
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit") {
		$pkey_qpm_sub_plant= $_POST['qpm_sub_plant'];
		$pkey_qpm_press_code = $_POST['qpm_press_code'];
		$pkey_qpm_code = $_POST['qpm_code'];
		$sql0 = "SELECT * from qc_pd_mouldset where qpm_sub_plant='{$pkey_qpm_sub_plant}' and qpm_press_code='{$pkey_qpm_press_code}' and qpm_code='{$pkey_qpm_code}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$responce->qpm_sub_plant = $rhead[qpm_sub_plant];
		$responce->qpm_press_code = $rhead[qpm_press_code];
		$responce->qpm_code = $rhead[qpm_code];
		$responce->qpm_desc = $rhead[qpm_desc];
		$responce->qpm_press_codehtml = cbopress($rhead[qpm_sub_plant],$rhead[qpm_press_code],true);
		$responce->sub_plan = cbo_subplant($rhead[qpm_sub_plant]);
	} else if($stat == "add"){
		$responce->sub_plan = cbo_subplant();
	}
    echo json_encode($responce);
}

function cbopress($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qpp_code, qpp_desc from qc_pd_press where qpp_sub_plant = '{$subplan}'";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[qpp_code] == $nilai){
				$out .= "<option value='{$r[qpp_code]}' selected>$r[qpp_desc]</option>";
			} else {
				$out .= "<option value='{$r[qpp_code]}'>$r[qpp_desc]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;	
	}
}

?>