<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['40'];
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
	case "suburai":
		suburai();
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
		$whdua .= " and qih_sub_plant = '".$app_subplan."'";
	}
	
	if($_POST['qih_id']) {
		$whdua .= " and qih_id = '".$_POST['qih_id']."'";
	}
	if($_POST['qih_sub_plant']) {
		$whdua .= " and qih_sub_plant = '".$_POST['qih_sub_plant']."'";
	}
	if($_POST['qih_date']) {
		$whdua .= " and qih_date = '".$_POST['qih_date']."'";
	}
	if(!$sidx) $sidx = 1;

		$start = $rows * $page - $rows;
		$limit = "limit ".$rows." offset ".$start;
		$sql = "SELECT qih_id, qih_sub_plant, qih_date from qc_air_header where qih_rec_status is null and qih_date >= '{$tglfrom}' and qih_date <= '{$tglto}' $whsatu $whdua
			order by qih_id ";
		$qry = dbselect_plan_all($app_plan_id, $sql);
		$i = 0;
	
	if($page > $total_pages) $page = $total_pages; 
	$responce->page = $page; 
	$responce->total = $total_pages; 
	$responce->records = $count; 
	foreach($qry as $ro){
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['qih_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qih_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qih_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView."".$btnEdit."".$btnDel;
			$datetime = explode(' ',$ro['qih_date']);
			$ro['date'] = $datetime[0];
			$ro['time'] = substr($datetime[1],0,5);
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qih_id'],$ro['qih_sub_plant'],$ro['date'],$ro['time'],$ro['kontrol']);
			$i++;
	}
	echo json_encode($responce);
}

function suburai(){
	global $app_plan_id;
	$qih_id = $_POST['qih_id'];
	$sql = "SELECT qc_air_detail.*, qssd_monitoring_desc, qss_desc from qc_air_detail join qc_sp_sett_master on(qc_air_detail.qid_group=qc_sp_sett_master.qss_group) join qc_sp_sett_detail on(qc_air_detail.qid_r=qc_sp_sett_detail.qssd_seq) where qih_id = '{$qih_id}' order by qid_group, qid_r";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	foreach($qry as $ro){
		$responce->rows[$i]['cell']=array($ro['qss_desc'],$ro['qid_r'],$ro['qssd_monitoring_desc'],$ro['qid_s'],$ro['qid_t']);
		$i++;
	}		
	echo json_encode($responce);	
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	// $kode=$_POST['kode'];
	$r[qih_date] = cgx_dmy2ymd($r[qih_date])." ".$r[qih_time].":00";
	$r[qih_user_create] = $_SESSION[$app_id]['user']['user_name'];
	$r[qih_date_create] = date("Y-m-d H:i:s");
	if($stat == "add") {
		$sql = "SELECT max(qih_id) as qih_id_max from qc_air_header where qih_sub_plant = '{$r[qih_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[qih_id_max] == ''){
			$mx[qih_id_max] = 0;
		} else {
			$mx[qih_id_max] = substr($mx[qih_id_max],-7);
		}
		$urutbaru = $mx[qih_id_max]+1;
		$r[qih_id] = $app_plan_id.$r[qih_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		$sql = "INSERT into qc_air_header(qih_id, qih_sub_plant, qih_date, qih_user_create, qih_date_create) values('{$r[qih_id]}', '{$r[qih_sub_plant]}', '2018-12-12 17:03:00', '{$r[qih_user_create]}', '{$r[qih_date_create]}');";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k2sql = "";
			$kolam='';
			$no_kol=7;
			foreach ($r[qid_kolam] as $i => $value) {
				$kolam.=$r[qid_kolam][$i];		
				if($i!=$no_kol){
					$kolam.=",";
				}
			}
			$k2sql .= "INSERT into qc_air_detail (qih_id, qid_deep_wheel2, qid_deep_wheel3, qid_data_mushola, qid_glazing_line, qid_kolam,qid_pdam) values('{$r[qih_id]}', '{$r[qih_wheel2]}', '{$r[qih_wheel3]}', '{$r[qih_mus]}', '{$r[qih_glaze]}', '{$kolam}','{$r[qih_pdam]}');";
			$out = dbsave_plan($app_plan_id, $k2sql);
		} else {
			$out = $xsql;
		}
	} else if($stat=='edit') {
		$r[qih_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[qih_date_modify] = date("Y-m-d H:i:s");
		$sql = "UPDATE qc_air_header set qih_user_modify = 'f', qih_date_modify = '{$r[qih_date_modify]}' where qih_id = '{$r[qlh_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_air_detail where qih_id = '{$r[qlh_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			if($x1sql == "OK") {
			$k2sql = "";
			$kolam='';
			$no_kol=7;
			foreach ($r[qid_kolam] as $i => $value) {
				$kolam.=$r[qid_kolam][$i];		
				if($i!=$no_kol){
					$kolam.=",";
				}
			}
			$k2sql .= "INSERT into qc_air_detail (qih_id, qid_deep_wheel2, qid_deep_wheel3, qid_data_mushola, qid_glazing_line, qid_kolam,qid_pdam) values('{$r[qlh_id]}', '{$r[qih_wheel2]}', '{$r[qih_wheel3]}', '{$r[qih_mus]}', '{$r[qih_glaze]}', '{$kolam}','{$r[qih_pdam]}');";
				$out = dbsave_plan($app_plan_id, $k2sql);	
			} else {
				$out = $x1sql;
			}
		} else {
			$out = $xsql;
		}
	}
	echo $out;
}

function hapus(){
	global $app_plan_id;
	$qih_id = $_POST['kode'];
	$sql = "UPDATE qc_air_header set qih_rec_status='1' where qih_id = '{$qih_id}';";
	echo dbsave_plan($app_plan_id, $sql); 
}

function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant();
	$out .= $withselect ? "</select>" : "";
	echo $out;
}

function detailtabel($stat) {
	global $app_plan_id, $app_subplan;
	if($stat == "edit" || $stat == "view") {
		$qlh_id = $_POST['kode'];
		$sql0 = "SELECT * from qc_air_header where qih_id = '{$qlh_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['qih_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$rhead['time'] = substr($datetime[1],0,5);
		$sql = "SELECT * from qc_air_detail where qih_id = '{$qlh_id}'";
		$qry = dbselect_plan_all($app_plan_id, $sql);
		$arr_qld_group = array();
		foreach($qry as $r) {
			$qid_glazing_line=$r[qid_glazing_line];
			$qid_data_mushola=$r[qid_data_mushola];
			$qid_deep_wheel3=$r[qid_deep_wheel3];
			$qid_deep_wheel2=$r[qid_deep_wheel2];
			$val_kolam= explode(',',$r[qid_kolam]);
		}
    $responce->qlh_sub_plant =substr($qlh_id,1,1); 
    $responce->qlh_id = $qlh_id;
    $responce->qlh_date = $rhead['date'];
    $responce->qlh_time = $rhead['time'];
    $responce->qlh_deep_wheel2 = $qid_deep_wheel2;
    $responce->qlh_deep_wheel3 = $qid_deep_wheel3;
    $responce->qlh_glazing_line = $qid_glazing_line;
    $responce->qlh_data_mushola = $qid_data_mushola;
	
	}
	$out="
	<div class='container col col-md-12' style='margin-top:30px;background-color:#e3f3fc' >
	<h3><i class='fa fa-info-circle' ></i>Data Kolam</h3>
		<div class='row'>";
	$nama_kolam=array('Kolam_1','Kolam_2','Kolam_3','Kolam_4','Kolam_5a','Kolam_6a','Kolam_6b','Kolam_6c');
	for ($i=0; $i <=7 ; $i++) { 
		// $label=str_replace('', replace, subject)
			$out.="
			<div class='col col-md-3'>
				<label>".str_replace('_',' ',$nama_kolam[$i])."</label>
				<input class='form-control' name='qid_kolam[]' id='qid_kolam".$i."' value='".$val_kolam[$i]."' type='text' style='text-align:right;' onkeyup='hanyanumerik(this.id,this.value)'>
			</div>";
	}
	if($stat=='add'){
		$text="Simpan";
	}else{
		$text="Update";
	}
	$out.="<div class='col col-md-12' style='margin-top:30px;background-color:white;'><br><button  class='btn btn-primary btn-sm' onClick=\"simpanData('".$stat."')\">".$text."</button> <button type='button' class='btn btn-warning btn-sm' onClick=\"formAwal()\">Batal</button>";
	$out.="			
		</div>
	</div>";
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>