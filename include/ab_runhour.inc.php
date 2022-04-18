<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['49'];

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
	case "cboshift":
		cboshift();
		break;
	case "cboabnama":
		cboabnama();
		break;
	case "cboabnomor":
		cboabnomor($_POST['qab_nama']);
		break;
	case "detailtabel":
		detailtabel($_POST['stat']);
		break;
}

function urai(){
	global $app_plan_id, $akses;
	$page = $_POST['page']; 
	$rows = $_POST['rows']; 
	$sidx = $_POST['sidx']; 
	$sord = $_POST['sord'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$whsatu = dptKondisiWhere($_POST['_search'],$_POST['filters'],$_POST['searchField'],$_POST['searchOper'],$_POST['searchString']);
	$whdua = "";
	if($_POST['qar_id']) {
		$whdua .= " and qar_id like '%".$_POST['qar_id']."%'";
	}
	if($_POST['qar_date']) {
		$whdua .= " and qar_date = '".$_POST['qar_date']."'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_alber_runhour where qar_rec_stat='N' and qar_date >= '{$tglfrom}' and qar_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT * from qc_alber_runhour where qar_rec_stat='N' and qar_date >= '{$tglfrom}' and qar_date <= '{$tglto}' $whsatu $whdua order by $sidx $sord $limit";
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['qar_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qar_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qar_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro[kontrol] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['qar_date']);
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro[qar_id],cgx_dmy2ymd($datetime[0]),$ro[qar_shift],$ro[qar_ab_nama],$ro[qar_ab_nomor],$ro[qar_awal],$ro[qar_akhir],$ro[qar_remark],$ro[kontrol]);
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[qar_date] = cgx_dmy2ymd($r[qar_date])." 00:00:00";
	if($stat == "add") {
		$r[qar_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[qar_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(qar_id) as qar_id_max from qc_alber_runhour";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[qar_id_max] == ''){
			$mx[qar_id_max] = 0;
		} else {
			$mx[qar_id_max] = substr($mx[qar_id_max],-7);
		}
		$urutbaru = $mx[qar_id_max]+1;
		$r[qar_id] = $app_plan_id."R/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		$sql = "INSERT into qc_alber_runhour(qar_id, qar_date, qar_shift, qar_rec_stat, qar_ab_nama, qar_ab_nomor, qar_awal, qar_akhir, qar_remark, qar_user_create, qar_date_create) values('{$r[qar_id]}', '{$r[qar_date]}', {$r[qar_shift]}, 'N', '{$r[qar_ab_nama]}', '{$r[qar_ab_nomor]}', {$r[qar_awal]}, {$r[qar_akhir]}, '{$r[qar_remark]}', '{$r[qar_user_create]}', '{$r[qar_date_create]}');";
	} else if($stat=='edit') {
		$r[qar_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[qar_date_modify] = date("Y-m-d H:i:s");
		$sql = "UPDATE qc_alber_runhour set qar_date = '{$r[qar_date]}', qar_shift = {$r[qar_shift]}, qar_ab_nama = '{$r[qar_ab_nama]}', qar_ab_nomor = '{$r[qar_ab_nomor]}', qar_awal = {$r[qar_awal]}, qar_akhir = {$r[qar_akhir]}, qar_remark = '{$r[qar_remark]}', qar_user_modify = '{$r[qar_user_modify]}', qar_date_modify = '{$r[qar_date_modify]}' where qar_id = '{$r[qar_id]}';";	
	}
	$xsql = dbsave_plan($app_plan_id, $sql);
	$out = $xsql; 
	echo $out;
}

function hapus(){
	global $app_plan_id;
	$qar_id = $_POST['kode'];
	$sql = "UPDATE qc_alber_runhour set qar_rec_stat='C' where qar_id = '{$qar_id}';";
	echo dbsave_plan($app_plan_id, $sql); 
}

function cboshift($nilai = "TIDAKADA"){
	$out = cbo_shift($nilai);
	echo $out;
}

function cboabnama($nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT distinct qab_nama from qc_alat_berat";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[qab_nama] == $nilai){
				$out .= "<option selected>$r[qab_nama]</option>";
			} else {
				$out .= "<option>$r[qab_nama]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;	
	}
}

function cboabnomor($qab_nama, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qab_nomor from qc_alat_berat where qab_nama = '{$qab_nama}'";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[qab_nomor] == $nilai){
				$out .= "<option selected>$r[qab_nomor]</option>";
			} else {
				$out .= "<option>$r[qab_nomor]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;	
	}
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit" || $stat == "view") {
		$qar_id = $_POST['kode'];
		$sql0 = "SELECT * from qc_alber_runhour where qar_id = '{$qar_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['qar_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$responce->qar_id = $rhead[qar_id];
	    $responce->qar_date = $rhead[date];
	    $responce->qar_shift = cbo_shift($rhead[qar_shift]);
	    $responce->qar_ab_nama = $rhead[qar_ab_nama];
	    $responce->qar_ab_nomor = cboabnomor($rhead[qar_ab_nama],$rhead[qar_ab_nomor],true);
	    $responce->qar_awal = $rhead[qar_awal];
	    $responce->qar_akhir = $rhead[qar_akhir];
	    $responce->qar_remark = $rhead[qar_remark];
	} 
    echo json_encode($responce);
}

?>