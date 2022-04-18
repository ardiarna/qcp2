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
		$whdua .= " and qlh_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and qlh_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['qlh_id']) {
		$whdua .= " and qlh_id = '".$_POST['qlh_id']."'";
	}
	if($_POST['qlh_sub_plant']) {
		$whdua .= " and qlh_sub_plant = '".$_POST['qlh_sub_plant']."'";
	}
	if($_POST['qlh_date']) {
		$whdua .= " and qlh_date = '".$_POST['qlh_date']."'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_listrik_header where qlh_rec_status='N' and qlh_date >= '{$tglfrom}' and qlh_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT qlh_id, qlh_sub_plant, qlh_date from qc_listrik_header where qlh_rec_status='N' and qlh_date >= '{$tglfrom}' and qlh_date <= '{$tglto}' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['qlh_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qlh_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qlh_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['qlh_date']);
			$ro['date'] = $datetime[0];
			$ro['time'] = substr($datetime[1],0,5);
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qlh_id'],$ro['qlh_sub_plant'],$ro['date'],$ro['time'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function suburai(){
	global $app_plan_id;
	$qlh_id = $_POST['qlh_id'];
	$sql = "SELECT qc_listrik_detail.*, qssd_monitoring_desc, qss_desc from qc_listrik_detail join qc_sp_sett_master on(qc_listrik_detail.qld_group=qc_sp_sett_master.qss_group) join qc_sp_sett_detail on(qc_listrik_detail.qld_r=qc_sp_sett_detail.qssd_seq) where qlh_id = '{$qlh_id}' order by qld_group, qld_r";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	foreach($qry as $ro){
		$responce->rows[$i]['cell']=array($ro['qss_desc'],$ro['qld_r'],$ro['qssd_monitoring_desc'],$ro['qld_s'],$ro['qld_t']);
		$i++;
	}		
	echo json_encode($responce);	
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[qlh_date] = cgx_dmy2ymd($r[qlh_date])." ".$r[qlh_time].":00";
	if($stat == "add") {
		$r[qlh_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[qlh_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(qlh_id) as qlh_id_max from qc_listrik_header where qlh_sub_plant = '{$r[qlh_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[qlh_id_max] == ''){
			$mx[qlh_id_max] = 0;
		} else {
			$mx[qlh_id_max] = substr($mx[qlh_id_max],-7);
		}
		$urutbaru = $mx[qlh_id_max]+1;
		$r[qlh_id] = $app_plan_id.$r[qlh_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		$sql = "INSERT into qc_listrik_header(qlh_id, qlh_sub_plant, qlh_date, qlh_rec_status, qlh_cap_bank_1, qlh_cap_bank_2, qlh_cap_bank_3, qlh_user_create, qlh_date_create) values('{$r[qlh_id]}', '{$r[qlh_sub_plant]}', '{$r[qlh_date]}', 'N', '{$r[qlh_cap_bank_1]}', '{$r[qlh_cap_bank_2]}', '{$r[qlh_cap_bank_3]}', '{$r[qlh_user_create]}', '{$r[qlh_date_create]}');";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k2sql = "";
			foreach ($r[qld_group] as $i => $value) {
				$k2sql .= "INSERT into qc_listrik_detail(qlh_id, qld_group, qld_r, qld_s, qld_t, qld_v, qld_watt_hour) values('{$r[qlh_id]}', '{$r[qld_group][$i]}', '{$r[qld_r][$i]}', '{$r[qld_s][$i]}', '{$r[qld_t][$i]}', '{$r[qld_v][$i]}', '{$r[qld_watt_hour][$i]}');";
			}
			$out = dbsave_plan($app_plan_id, $k2sql);
		} else {
			$out = $xsql;
		}
	} else if($stat=='edit') {
		$r[qlh_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[qlh_date_modify] = date("Y-m-d H:i:s");
		$sql = "UPDATE qc_listrik_header set qlh_cap_bank_1 = '{$r[qlh_cap_bank_1]}', qlh_cap_bank_2 = '{$r[qlh_cap_bank_2]}', qlh_cap_bank_3 = '{$r[qlh_cap_bank_3]}', qlh_user_modify = '{$r[qlh_user_modify]}', qlh_date_modify = '{$r[qlh_date_modify]}' where qlh_id = '{$r[qlh_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_listrik_detail where qlh_id = '{$r[qlh_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			if($x1sql == "OK") {
				$k2sql = "";
				foreach ($r[qld_r] as $i => $value) {
					$r[qld_t][$i] = cgx_angka($r[qld_t][$i]);
					$k2sql .= "INSERT into qc_listrik_detail(qlh_id, qld_group, qld_r, qld_s, qld_t) values('{$r[qlh_id]}', '{$r[qld_group][$i]}', {$r[qld_r][$i]}, '{$r[qld_s][$i]}', {$r[qld_t][$i]});";
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
	$qlh_id = $_POST['kode'];
	$sql = "UPDATE qc_listrik_header set qlh_rec_status='C' where qlh_id = '{$qlh_id}';";
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
		$sql0 = "SELECT * from qc_listrik_header where qlh_id = '{$qlh_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['qlh_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$rhead['time'] = substr($datetime[1],0,5);
		$sql = "SELECT * from qc_listrik_detail where qlh_id = '{$qlh_id}'";
		$qry = dbselect_plan_all($app_plan_id, $sql);
		$arr_qld_group = array();
		foreach($qry as $r) {
			array_push($arr_qld_group, $r[qld_group]);
			$arr_qld_r["$r[qld_group]"] = $r[qld_r];
			$arr_qld_s["$r[qld_group]"] = $r[qld_s];
			$arr_qld_t["$r[qld_group]"] = $r[qld_t];
			$arr_qld_v["$r[qld_group]"] = $r[qld_v];
			$arr_qld_watt_hour["$r[qld_group]"] = $r[qld_watt_hour];
		}
	} else {
		if($_POST['subplan']) {
			$rhead[qlh_sub_plant] = $_POST['subplan'];
		} else {
			$rhead[qlh_sub_plant] = $app_subplan == 'All'?'A':$app_subplan;	
		}
		if($rhead[qlh_sub_plant] == 'C') {
			$arr_qld_group = array('IN COMP PLN TR-1','IN COMP PLN TR-2','IN COMP PLN TR-3');
		} else {
			$arr_qld_group = array('IN COM GENERATOR','IN COMP PLN TR-1','IN COMP PLN TR-2','IN COMP PLN TR-3');	
		}
	}
	$i = 0;
	$out = '<table class="table table-bordered table-condensed">';
	foreach ($arr_qld_group as $qld_group) {
		$qld_r = $arr_qld_r[$qld_group];
		$qld_s = $arr_qld_s[$qld_group];
		$qld_t = $arr_qld_t[$qld_group];
		$qld_v = $arr_qld_v[$qld_group];
		$qld_watt_hour = $arr_qld_watt_hour[$qld_group];
		$out .= '<tr style="background-color:#f7e8cf"><th colspan="5"><input type="hidden" name="qld_group['.$i.']" id="qld_group_'.$i.'" value="'.$qld_group.'">'.$qld_group.'</th></tr>';
		$out .= '<tr style="background-color:#f7e8cf"><th>R</th><th>S</th><th>T</th><th>V</th><th>Watt Hour Met</th></tr>';
		$out .= '<tr><td style="padding-bottom:20px;"><input class="form-control input-sm" type="text" name="qld_r['.$i.']" id="qld_r_'.$i.'" value="'.$qld_r.'" style="text-align:right;" onkeyup="hanyanumerik(this.id,this.value)"></td><td><input class="form-control input-sm" type="text" name="qld_s['.$i.']" id="qld_s_'.$i.'" value="'.$qld_s.'" style="text-align:right;" onkeyup="hanyanumerik(this.id,this.value)"></td><td><input class="form-control input-sm" type="text" name="qld_t['.$i.']" id="qld_t_'.$i.'" value="'.$qld_t.'" style="text-align:right;" onkeyup="hanyanumerik(this.id,this.value)"></td><td><input class="form-control input-sm" type="text" name="qld_v['.$i.']" id="qld_v_'.$i.'" value="'.$qld_v.'" style="text-align:right;" onkeyup="hanyanumerik(this.id,this.value)"></td><td><input class="form-control input-sm" type="text" name="qld_watt_hour['.$i.']" id="qld_watt_hour_'.$i.'" value="'.$qld_watt_hour.'" style="text-align:right;" onkeyup="hanyanumerik(this.id,this.value)"></td></tr>';
		$i++;
	}
	if($stat == "edit" || $stat == "add") {
		$out .= '<tr>
		    <td colspan="5" class="text-center"><button type="button" class="btn btn-primary btn-sm" onClick="simpanData(\''.$stat.'\')">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button></td>
		    </tr>';
	} else {
		$out .= '<tr>
		    <td colspan="5" class="text-center"><button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button></td>
		    </tr>';
	}
	$out .= '</table>';

	if($stat == "edit" || $stat == "view") {
    	$responce->qlh_id = $rhead[qlh_id];
	    $responce->qlh_date = $rhead[date];
	    $responce->qlh_time = $rhead[time];
	    $responce->qlh_cap_bank_1 = $rhead[qlh_cap_bank_1];
	    $responce->qlh_cap_bank_2 = $rhead[qlh_cap_bank_2];
	    $responce->qlh_cap_bank_3 = $rhead[qlh_cap_bank_3];
    }
    $responce->qlh_sub_plant = $rhead[qlh_sub_plant];
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>