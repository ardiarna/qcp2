<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['56'];
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
		$whdua .= " and qsms_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and qsms_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['qsms_id']) {
		$whdua .= " and qsms_id = '".$_POST['qsms_id']."'";
	}
	if($_POST['qsms_sub_plant']) {
		$whdua .= " and qsms_sub_plant = '".$_POST['qsms_sub_plant']."'";
	}
	if($_POST['qsms_date']) {
		$whdua .= " and qsms_date = '".$_POST['qsms_date']."'";
	}
	if($_POST['qsms_keterangan']) {
		$whdua .= " and lower(qsms_keterangan) like '%".strtolower($_POST['qsms_keterangan'])."%'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_sp_monitoring_stop where qsms_rec_status='N' and qsms_date >= '{$tglfrom}' and qsms_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT qsms_id, qsms_sub_plant, qsms_date, qsms_keterangan from qc_sp_monitoring_stop where qsms_rec_status='N' and qsms_date >= '{$tglfrom}' and qsms_date <= '{$tglto}' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['qsms_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qsms_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qsms_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['qsms_date']);
			$ro['date'] = $datetime[0];
			$ro['time'] = substr($datetime[1],0,5);
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qsms_id'],$ro['qsms_sub_plant'],$ro['date'],$ro['time'],$ro['qsms_keterangan'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	if($stat == "add") {
		$r[qsms_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[qsms_date_create] = date("Y-m-d H:i:s");
		if($r[qsms_sdtime]) {
			$qsms_time = intval(substr($r[qsms_time],0,2));
			$qsms_sdtime = intval(substr($r[qsms_sdtime],0,2));
			if($qsms_sdtime > $qsms_time) {
				for ($i=$qsms_time; $i <= $qsms_sdtime; $i++) {
					$r_time = $i <= 9 ? "0".$i : $i;
					$qsms_date = cgx_dmy2ymd($r[qsms_date])." ".$r_time.":00";
					$sqlcek = "SELECT max(qsms_id) as qsms_id_max from qc_sp_monitoring_stop where qsms_sub_plant = '{$r[qsms_sub_plant]}'";
					$mx = dbselect_plan($app_plan_id, $sqlcek);
					if($mx[qsms_id_max] == ''){
						$mx[qsms_id_max] = 0;
					} else {
						$mx[qsms_id_max] = substr($mx[qsms_id_max],-7);
					}
					$urutbaru = $mx[qsms_id_max]+1;
					$r[qsms_id] = $app_plan_id.$r[qsms_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
					$sql = "INSERT into qc_sp_monitoring_stop(qsms_id, qsms_sub_plant, qsms_date, qsms_rec_status, qsms_keterangan, qsms_user_create, qsms_date_create) values('{$r[qsms_id]}', '{$r[qsms_sub_plant]}', '{$qsms_date}', 'N', '{$r[qsms_keterangan]}', '{$r[qsms_user_create]}', '{$r[qsms_date_create]}');";
					$xsql = dbsave_plan($app_plan_id, $sql); 
					$out = $xsql;
				}
			} else {
				$r[qsms_date] = cgx_dmy2ymd($r[qsms_date])." ".$r[qsms_time].":00";
				$sqlcek = "SELECT max(qsms_id) as qsms_id_max from qc_sp_monitoring_stop where qsms_sub_plant = '{$r[qsms_sub_plant]}'";
				$mx = dbselect_plan($app_plan_id, $sqlcek);
				if($mx[qsms_id_max] == ''){
					$mx[qsms_id_max] = 0;
				} else {
					$mx[qsms_id_max] = substr($mx[qsms_id_max],-7);
				}
				$urutbaru = $mx[qsms_id_max]+1;
				$r[qsms_id] = $app_plan_id.$r[qsms_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
				$sql = "INSERT into qc_sp_monitoring_stop(qsms_id, qsms_sub_plant, qsms_date, qsms_rec_status, qsms_keterangan, qsms_user_create, qsms_date_create) values('{$r[qsms_id]}', '{$r[qsms_sub_plant]}', '{$r[qsms_date]}', 'N', '{$r[qsms_keterangan]}', '{$r[qsms_user_create]}', '{$r[qsms_date_create]}');";
				$xsql = dbsave_plan($app_plan_id, $sql); 
				$out = $xsql;
			}
		} else {
			$r[qsms_date] = cgx_dmy2ymd($r[qsms_date])." ".$r[qsms_time].":00";
			$sqlcek = "SELECT max(qsms_id) as qsms_id_max from qc_sp_monitoring_stop where qsms_sub_plant = '{$r[qsms_sub_plant]}'";
			$mx = dbselect_plan($app_plan_id, $sqlcek);
			if($mx[qsms_id_max] == ''){
				$mx[qsms_id_max] = 0;
			} else {
				$mx[qsms_id_max] = substr($mx[qsms_id_max],-7);
			}
			$urutbaru = $mx[qsms_id_max]+1;
			$r[qsms_id] = $app_plan_id.$r[qsms_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
			$sql = "INSERT into qc_sp_monitoring_stop(qsms_id, qsms_sub_plant, qsms_date, qsms_rec_status, qsms_keterangan, qsms_user_create, qsms_date_create) values('{$r[qsms_id]}', '{$r[qsms_sub_plant]}', '{$r[qsms_date]}', 'N', '{$r[qsms_keterangan]}', '{$r[qsms_user_create]}', '{$r[qsms_date_create]}');";
			$xsql = dbsave_plan($app_plan_id, $sql); 
			$out = $xsql;
		}
	} else if($stat=='edit') {
		$r[qsms_date] = cgx_dmy2ymd($r[qsms_date])." ".$r[qsms_time].":00";
		$r[qsms_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[qsms_date_modify] = date("Y-m-d H:i:s");
		$sql = "UPDATE qc_sp_monitoring_stop set qsms_keterangan = '{$r[qsms_keterangan]}', qsms_user_modify = '{$r[qsms_user_modify]}', qsms_date_modify = '{$r[qsms_date_modify]}' where qsms_id = '{$r[qsms_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		$out = $xsql;
	}
	echo $out;
}

function hapus(){
	global $app_plan_id;
	$qsms_id = $_POST['kode'];
	$sql = "UPDATE qc_sp_monitoring_stop set qsms_rec_status='C' where qsms_id = '{$qsms_id}';";
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
		$qsms_id = $_POST['kode'];
		$sql0 = "SELECT * from qc_sp_monitoring_stop where qsms_id = '{$qsms_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['qsms_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$rhead['time'] = substr($datetime[1],0,5);
		$responce->qsms_id = $rhead[qsms_id];
	    $responce->qsms_date = $rhead[date];
	    $responce->qsms_time = $rhead[time];
	    $responce->qsms_sub_plant = $rhead[qsms_sub_plant];
	    $responce->qsms_keterangan = $rhead[qsms_keterangan];
	}
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>