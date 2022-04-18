<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['28'];
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
	case "cbosubplant":
		cbosubplant($_GET['withselect']);
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
	$subplan_kode = $_GET['subplan'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$whsatu = dptKondisiWhere($_POST['_search'],$_POST['filters'],$_POST['searchField'],$_POST['searchOper'],$_POST['searchString']);
	$whdua = "";
	if($app_subplan <> 'All') {
		$whdua .= " and qfh_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and qfh_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['qfh_id']) {
		$whdua .= " and qfh_id = '".$_POST['qfh_id']."'";
	}
	if($_POST['qfh_sub_plant']) {
		$whdua .= " and qfh_sub_plant = '".$_POST['qfh_sub_plant']."'";
	}
	if($_POST['qfh_date']) {
		$whdua .= " and qfh_date = '".$_POST['qfh_date']."'";
	}
	if($_POST['qfh_findings']) {
		$whdua .= " and lower(qfh_findings) like '%".strtolower($_POST['qfh_findings'])."%'";
	}
	if($_POST['qfh_rec_stat']) {
		$whdua .= " and qfh_rec_stat = '".$_POST['qfh_rec_stat']."'";
	}
	if($_POST['qfh_reported_to']) {
		$whdua .= " and lower(qfh_reported_to) = '".strtolower($_POST['qfh_reported_to'])."'";
	}
	if($_POST['qfh_done_by']) {
		$whdua .= " and lower(qfh_done_by) = '".strtolower($_POST['qfh_done_by'])."'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_ft_header where 1=1 and qfh_date >= '{$tglfrom}' and qfh_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT * from qc_ft_header 
			where 1=1 and qfh_date >= '{$tglfrom}' and qfh_date <= '{$tglto}' $whsatu $whdua
			order by $sidx $sord $limit";
		$qry = dbselect_plan_all($app_plan_id, $sql);
		$i = 0;
	} else { 
		$total_pages = 1; 
	}
	if($page > $total_pages) $page = $total_pages; 
	$responce->page = $page; 
	$responce->total = $total_pages; 
	$responce->records = $count; 
	if($count > 0) {
		foreach($qry as $ro){
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['qfh_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qfh_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qfh_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['qfh_date']);
			$ro['date'] = $datetime[0];
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qfh_id'],$ro['qfh_sub_plant'],$ro['date'],$ro['qfh_findings'],$ro['qfh_rec_stat'],$ro['qfh_reported_to'],$ro['qfh_done_by'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[qfh_date] = cgx_dmy2ymd($r[qfh_date]);
	if($stat == "add") {
		$r[qfh_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[qfh_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(qfh_id) as qfh_id_max from qc_ft_header where qfh_sub_plant = '{$r[qfh_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[qfh_id_max] == ''){
			$mx[qfh_id_max] = 0;
		} else {
			$mx[qfh_id_max] = substr($mx[qfh_id_max],-7);
		}
		$urutbaru = $mx[qfh_id_max]+1;
		$r[qfh_id] = $app_plan_id.$r[qfh_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		$sql = "INSERT INTO qc_ft_header(qfh_sub_plant, qfh_id, qfh_date, qfh_findings, qfh_reported_to, qfh_done_by, qfh_rec_stat, qfh_user_create, qfh_date_create) values('{$r[qfh_sub_plant]}', '{$r[qfh_id]}', '{$r[qfh_date]}', '{$r[qfh_findings]}', '{$r[qfh_reported_to]}', '{$r[qfh_done_by]}', '1', '{$r[qfh_user_create]}', '{$r[qfh_date_create]}');";
	} else if($stat=='edit') {
		$sql = "UPDATE qc_ft_header set qfh_date = '{$r[qfh_date]}', qfh_findings = '{$r[qfh_findings]}', qfh_reported_to = '{$r[qfh_reported_to]}', qfh_done_by = '{$r[qfh_done_by]}', qfh_rec_stat = '{$r[qfh_rec_stat]}' where qfh_id = '{$r[qfh_id]}';";
	}
	echo dbsave_plan($app_plan_id, $sql);
}

function hapus(){
	global $app_plan_id;
	$qfh_id = $_POST['kode'];
	$sql = "DELETE from qc_ft_header where qfh_id = '{$qfh_id}';";
	echo dbsave_plan($app_plan_id, $sql); 
}

function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant();
	$out .= $withselect ? "</select>" : "";
	echo $out;
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit" || $stat == "view") {
		$qfh_id = $_POST['kode'];
		$sql0 = "SELECT * from qc_ft_header where qfh_id = '{$qfh_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['qfh_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$responce->qfh_id = $rhead[qfh_id];
		$responce->qfh_date = $rhead[date];
		$responce->qfh_sub_plant = $rhead[qfh_sub_plant];
		$responce->qfh_rec_stat = $rhead[qfh_rec_stat];
		$responce->qfh_findings = $rhead[qfh_findings];
		$responce->qfh_reported_to = $rhead[qfh_reported_to];
		$responce->qfh_done_by = $rhead[qfh_done_by];
	}
    echo json_encode($responce);
}

?>