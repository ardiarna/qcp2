<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['24'];
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
		$whdua .= " and qsm_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and qsm_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['qsm_id']) {
		$whdua .= " and qsm_id = '".$_POST['qsm_id']."'";
	}
	if($_POST['qsm_sub_plant']) {
		$whdua .= " and qsm_sub_plant = '".$_POST['qsm_sub_plant']."'";
	}
	if($_POST['qsm_date']) {
		$whdua .= " and qsm_date = '".$_POST['qsm_date']."'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_sp_monitoring where qsm_rec_status='N' and qsm_date >= '{$tglfrom}' and qsm_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT qsm_id, qsm_sub_plant, qsm_date from qc_sp_monitoring where qsm_rec_status='N' and qsm_date >= '{$tglfrom}' and qsm_date <= '{$tglto}' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['qsm_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qsm_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qsm_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['qsm_date']);
			$ro['date'] = $datetime[0];
			$ro['time'] = substr($datetime[1],0,5);
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qsm_id'],$ro['qsm_sub_plant'],$ro['date'],$ro['time'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function suburai(){
	global $app_plan_id;
	$qsm_id = $_POST['qsm_id'];
	$sql = "SELECT qc_sp_monitoring_detail.*, qssd_monitoring_desc, qss_desc from qc_sp_monitoring_detail join qc_sp_sett_master on(qc_sp_monitoring_detail.qsmd_sett_group=qc_sp_sett_master.qss_group) join qc_sp_sett_detail on(qc_sp_monitoring_detail.qsmd_sett_seq=qc_sp_sett_detail.qssd_seq) where qsm_id = '{$qsm_id}' order by qsmd_sett_group, qsmd_sett_seq";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	foreach($qry as $ro){
		$responce->rows[$i]['cell']=array($ro['qss_desc'],$ro['qsmd_sett_seq'],$ro['qssd_monitoring_desc'],$ro['qsmd_sett_remark'],$ro['qsmd_sett_value']);
		$i++;
	}		
	echo json_encode($responce);	
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[qsm_date] = cgx_dmy2ymd($r[qsm_date])." ".$r[qsm_time].":00";
	if($stat == "add") {
		$r[qsm_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[qsm_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(qsm_id) as qsm_id_max from qc_sp_monitoring where qsm_sub_plant = '{$r[qsm_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[qsm_id_max] == ''){
			$mx[qsm_id_max] = 0;
		} else {
			$mx[qsm_id_max] = substr($mx[qsm_id_max],-7);
		}
		$urutbaru = $mx[qsm_id_max]+1;
		$r[qsm_id] = $app_plan_id.$r[qsm_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		$sql = "INSERT into qc_sp_monitoring(qsm_id, qsm_sub_plant, qsm_date, qsm_rec_status, qsm_user_create, qsm_date_create) values('{$r[qsm_id]}', '{$r[qsm_sub_plant]}', '{$r[qsm_date]}', 'N', '{$r[qsm_user_create]}', '{$r[qsm_date_create]}');";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k2sql = "";
			foreach ($r[qsmd_sett_seq] as $i => $value) {
				$r[qsmd_sett_value][$i] = cgx_angka($r[qsmd_sett_value][$i]);
				$k2sql .= "INSERT into qc_sp_monitoring_detail(qsm_id, qsmd_sett_group, qsmd_sett_seq, qsmd_sett_remark, qsmd_sett_value) values('{$r[qsm_id]}', '{$r[qsmd_sett_group][$i]}', {$r[qsmd_sett_seq][$i]}, '{$r[qsmd_sett_remark][$i]}', {$r[qsmd_sett_value][$i]});";
			}
			$out = dbsave_plan($app_plan_id, $k2sql);
		} else {
			$out = $xsql;
		}
	} else if($stat=='edit') {
		$r[qsm_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[qsm_date_modify] = date("Y-m-d H:i:s");
		$sql = "UPDATE qc_sp_monitoring set qsm_user_modify = '{$r[qsm_user_modify]}', qsm_date_modify = '{$r[qsm_date_modify]}' where qsm_id = '{$r[qsm_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_sp_monitoring_detail where qsm_id = '{$r[qsm_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			if($x1sql == "OK") {
				$k2sql = "";
				foreach ($r[qsmd_sett_seq] as $i => $value) {
					$r[qsmd_sett_value][$i] = cgx_angka($r[qsmd_sett_value][$i]);
					$k2sql .= "INSERT into qc_sp_monitoring_detail(qsm_id, qsmd_sett_group, qsmd_sett_seq, qsmd_sett_remark, qsmd_sett_value) values('{$r[qsm_id]}', '{$r[qsmd_sett_group][$i]}', {$r[qsmd_sett_seq][$i]}, '{$r[qsmd_sett_remark][$i]}', {$r[qsmd_sett_value][$i]});";
				}
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
	$qsm_id = $_POST['kode'];
	$sql = "UPDATE qc_sp_monitoring set qsm_rec_status='C' where qsm_id = '{$qsm_id}';";
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
		$qsm_id = $_POST['kode'];
		$sql0 = "SELECT * from qc_sp_monitoring where qsm_id = '{$qsm_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['qsm_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$rhead['time'] = substr($datetime[1],0,5);
		$sql = "SELECT distinct qsmd_sett_group, qss_desc from qc_sp_monitoring_detail join qc_sp_sett_master on(qc_sp_monitoring_detail.qsmd_sett_group=qc_sp_sett_master.qss_group) where qsm_id = '{$qsm_id}' order by qsmd_sett_group";
	} else {
		$sql = "SELECT qss_group as qsmd_sett_group, qss_desc from qc_sp_sett_master order by qss_group";
	}
	$k = 0;
	$i = 0;
	$out = '<table class="table table-bordered table-striped table-condensed">
		<tr">
    	<th width="50">NO</th>    
    	<th>DESKRIPSI</th>
    	<th width="90">NILAI</th>
    	<th>REMARK</th>
    	</tr>';
	$qry = dbselect_plan_all($app_plan_id, $sql);
	foreach($qry as $r) {
		$out .= '<tr><td colspan="4" style="padding-left:55px;"><span onClick="hideGrup('.$k.')"><strong>'.$r[qss_desc].'</strong></span></td></tr>';
        if($stat == "edit" || $stat == "view") {
			$sql2 = "SELECT qc_sp_monitoring_detail.*, qssd_monitoring_desc from qc_sp_monitoring_detail join qc_sp_sett_detail on(qc_sp_monitoring_detail.qsmd_sett_seq=qc_sp_sett_detail.qssd_seq) where qsm_id = '{$qsm_id}' and qsmd_sett_group = '{$r[qsmd_sett_group]}' order by qsmd_sett_seq";
		} else {
			$sql2 = "SELECT qssd_group as qsmd_sett_group, qssd_seq as qsmd_sett_seq, qssd_monitoring_desc from qc_sp_sett_detail where qssd_group = '{$r[qsmd_sett_group]}' order by qssd_seq";
		}
		$qry2 = dbselect_plan_all($app_plan_id, $sql2);
		foreach($qry2 as $r2) {
			$out .= '<tr id="trdet_ke_'.$i.'" class="trgrup_ke_'.$k.'">
	        	<td class="text-center"><input type="hidden" name="qsmd_sett_group['.$i.']" id="qsmd_sett_group_'.$i.'" value="'.$r2[qsmd_sett_group].'"><input type="hidden" name="qsmd_sett_seq['.$i.']" id="qsmd_sett_seq_'.$i.'" value="'.$r2[qsmd_sett_seq].'">'.$r2[qsmd_sett_seq].'</td>
	        	<td>'.$r2[qssd_monitoring_desc].'</td>
	        	<td><input class="form-control input-sm" type="text" name="qsmd_sett_value['.$i.']" id="qsmd_sett_value_'.$i.'" value="'.$r2[qsmd_sett_value].'"></td>
	        	<td><input class="form-control input-sm" type="text" name="qsmd_sett_remark['.$i.']" id="qsmd_sett_remark_'.$i.'" value="'.$r2[qsmd_sett_remark].'"></td>
	        	</tr>';
			$i++;
		}
        $k++;
	}
	if($stat == "edit" || $stat == "add") {
		$out .= '<tr>
		    <td colspan="4" class="text-center"><button type="button" class="btn btn-primary btn-sm" onClick="simpanData(\''.$stat.'\')">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button></td>
		    </tr>';
	} else {
		$out .= '<tr>
		    <td colspan="4" class="text-center"><button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button></td>
		    </tr>';
	}
	$out .= '</table>';

	if($stat == "edit" || $stat == "view") {
    	$responce->qsm_id = $rhead[qsm_id];
	    $responce->qsm_date = $rhead[date];
	    $responce->qsm_time = $rhead[time];
	    $responce->qsm_sub_plant = $rhead[qsm_sub_plant];
    }
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>