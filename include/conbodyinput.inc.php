<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['20'];
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
	case "cboshift":
		cboshift();
		break;
	case "cbobalmil":
		cbobalmil($_POST['subplan']);
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
		$whdua .= " and qch_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and qch_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['qch_id']) {
		$whdua .= " and qch_id = '".$_POST['qch_id']."'";
	}
	if($_POST['qch_sub_plant']) {
		$whdua .= " and qch_sub_plant = '".$_POST['qch_sub_plant']."'";
	}
	if($_POST['qch_date']) {
		$whdua .= " and qch_date = '".$_POST['qch_date']."'";
	}
	if($_POST['qch_shift']) {
		$whdua .= " and qch_shift = '".$_POST['qch_shift']."'";
	}
	if($_POST['qch_bm_no']) {
		$whdua .= " and qch_bm_no = '".$_POST['qch_bm_no']."'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_cb_header where qch_rec_stat='N' and qch_date >= '{$tglfrom}' and qch_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT qch_id, qch_sub_plant, qch_date, qch_shift, qch_bm_no, qch_user_create, qch_date_create, qch_user_modify, qch_date_modify from qc_cb_header where qch_rec_stat='N' and qch_date >= '{$tglfrom}' and qch_date <= '{$tglto}' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['qch_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qch_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qch_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['qch_date']);
			$ro['date'] = $datetime[0];
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qch_id'],$ro['qch_sub_plant'],$ro['date'],$ro['qch_shift'],$ro['qch_bm_no'],$ro['qch_user_create'],$ro['qch_date_create'],$ro['qch_user_modify'],$ro['qch_date_modify'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function suburai(){
	global $app_plan_id;
	$qch_id = $_POST['qch_id'];
	$sql = "SELECT qc_cb_detail.*, qcpm_desc, qcpd_control_desc, qgu_code from qc_cb_detail join qc_cb_prep_master on(qc_cb_detail.qcd_prep_group=qc_cb_prep_master.qcpm_group) join qc_cb_prep_detail on(qc_cb_detail.qcd_prep_group=qc_cb_prep_detail.qcpd_group and qc_cb_detail.qcd_prep_seq=qc_cb_prep_detail.qcpd_seq) left join qc_gen_um on(qc_cb_prep_detail.qcpd_um_id=qc_gen_um.qgu_id) where qch_id = '{$qch_id}' order by qcd_prep_group, qcd_prep_seq";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	foreach($qry as $ro){
		$responce->rows[$i]['cell']=array($ro['qcpm_desc'],$ro['qcd_prep_seq'],$ro['qcpd_control_desc'],"",$ro['qcd_silo_no'],$ro['qcd_slip_no'],$ro['qcd_prep_value']);
		$i++;
	}		
	echo json_encode($responce);	
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[qch_date] = cgx_dmy2ymd($r[qch_date]);
	$r[qch_shift] = cgx_angka($r[qch_shift]);
	if($stat == "add") {
		$r[qch_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[qch_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(qch_id) as qch_id_max from qc_cb_header where qch_sub_plant = '{$r[qch_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[qch_id_max] == ''){
			$mx[qch_id_max] = 0;
		} else {
			$mx[qch_id_max] = substr($mx[qch_id_max],-7);
		}
		$urutbaru = $mx[qch_id_max]+1;
		$r[qch_id] = $app_plan_id.$r[qch_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		$sql = "INSERT into qc_cb_header(qch_id, qch_sub_plant, qch_date, qch_rec_stat, qch_user_create, qch_date_create, qch_shift, qch_bm_no) values('{$r[qch_id]}', '{$r[qch_sub_plant]}', '{$r[qch_date]}', 'N', '{$r[qch_user_create]}', '{$r[qch_date_create]}', {$r[qch_shift]}, '{$r[qch_bm_no]}');";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k2sql = "";
			foreach ($r[qcd_prep_seq] as $i => $value) {
				if (($r[qcd_prep_group][$i] == '04' && !$r[qcd_silo_no][$i]) || ($r[qcd_prep_group][$i] == '05' && !$r[qcd_slip_no][$i])) {
				} else {
					$k2sql .= "INSERT into qc_cb_detail(qch_id, qcd_silo_no, qcd_slip_no, qcd_prep_group, qcd_prep_seq, qcd_prep_remark, qcd_prep_value) values('{$r[qch_id]}', '{$r[qcd_silo_no][$i]}', '{$r[qcd_slip_no][$i]}', '{$r[qcd_prep_group][$i]}', {$r[qcd_prep_seq][$i]}, '{$r[qcd_prep_remark][$i]}', '{$r[qcd_prep_value][$i]}');";
				}
			}
			$out = dbsave_plan($app_plan_id, $k2sql);
		} else {
			$out = $xsql;
		}
	} else if($stat=='edit') {
		$r[qch_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[qch_date_modify] = date("Y-m-d H:i:s");
		$sql = "UPDATE qc_cb_header set qch_bm_no = '{$r[qch_bm_no]}', qch_user_modify = '{$r[qch_user_modify]}', qch_date_modify = '{$r[qch_date_modify]}' where qch_id = '{$r[qch_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_cb_detail where qch_id = '{$r[qch_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			if($x1sql == "OK") {
				$k2sql = "";
				foreach ($r[qcd_prep_seq] as $i => $value) {
					if ($r[qcd_prep_group][$i] == '05' && !$r[qcd_slip_no][$i]) {
					} else {
						$k2sql .= "INSERT into qc_cb_detail(qch_id, qcd_silo_no, qcd_slip_no, qcd_prep_group, qcd_prep_seq, qcd_prep_remark, qcd_prep_value) values('{$r[qch_id]}', '{$r[qcd_silo_no][$i]}', '{$r[qcd_slip_no][$i]}', '{$r[qcd_prep_group][$i]}', {$r[qcd_prep_seq][$i]}, '{$r[qcd_prep_remark][$i]}', '{$r[qcd_prep_value][$i]}');";
					}
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
	$qch_id = $_POST['kode'];
	$sql = "UPDATE qc_cb_header set qch_rec_stat='C' where qch_id = '{$qch_id}';";
	echo dbsave_plan($app_plan_id, $sql); 
}

function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant();
	$out .= $withselect ? "</select>" : "";
	echo $out;
}

function cboshift($nilai = "TIDAKADA"){
	$out = cbo_shift($nilai);
	echo $out;
}

function cbobalmil($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qbm_kode, qbm_desc from qc_bm_unit where qbm_plant_code = '{$subplan}' order by qbm_kode";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if(trim($r[qbm_kode]) == $nilai){
				$out .= "<option value='$r[qbm_kode]' selected>$r[qbm_kode]</option>";
			} else {
				$out .= "<option value='$r[qbm_kode]'>$r[qbm_kode]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;
	}
}

function cbosilo($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qcs_code, qcs_desc from qc_cb_silo where qcs_sub_plant = '{$subplan}'";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[qcs_code] == $nilai) {
				$out .= "<option value='$r[qcs_code]' selected>$r[qcs_desc]</option>";
			} else {
				$out .= "<option value='$r[qcs_code]'>$r[qcs_desc]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;
	}
}

function cboslip($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qct_code, qct_desc from qc_cb_slip_tank where qct_sub_plant = '{$subplan}'";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[qct_code] == $nilai) {
				$out .= "<option value='$r[qct_code]' selected>$r[qct_desc]</option>";
			} else {
				$out .= "<option value='$r[qct_code]'>$r[qct_desc]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;
	}
}

function cbokodebody($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$subplan = substr($subplan,-1);
	$sql = "SELECT komposisi_kode from tbl_komposisi_produksi where jenis='body' and plan_kode='{$app_plan_id}' and sub_plan = '{$subplan}'";
	$qry = dbselect_all($sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if(trim($r[komposisi_kode]) == $nilai){
				$out .= "<option selected>$r[komposisi_kode]</option>";
			} else {
				$out .= "<option>$r[komposisi_kode]</option>";
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
	global $app_plan_id, $app_subplan;
	if($stat == "edit" || $stat == "view") {
		$qch_id = $_POST['kode'];
		$sql0 = "SELECT * from qc_cb_header where qch_id = '{$qch_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['qch_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$sql = "SELECT distinct qcd_prep_group, qcpm_desc from qc_cb_detail join qc_cb_prep_master on(qc_cb_detail.qcd_prep_group=qc_cb_prep_master.qcpm_group) where qch_id = '{$qch_id}' order by qcd_prep_group";
	} else {
		if($_POST['subplan']) {
			$rhead[qch_sub_plant] = $_POST['subplan'];
		} else {
			if($app_subplan <> 'All') {
				$rhead[qch_sub_plant] = $app_subplan;
			} else {
				$rhead[qch_sub_plant] = 'A';
			}
		}
		$sql = "SELECT qcpm_group as qcd_prep_group, qcpm_desc from qc_cb_prep_master order by qcpm_group";
		$stylehid = 'style="display:none;"';
	}
	$k = 0;
	$i = 0;
	$out = '<table class="table">';
	$qry = dbselect_plan_all($app_plan_id, $sql);
	foreach($qry as $r) {
		$out .= '<tr><td><button type="button" class="btn btn-default btn-sm btn-block" style="text-align:left;" onClick="hideGrup(\''.$k.'\')">'.$r[qcd_prep_group].'. INPUT '.$r[qcpm_desc].'</button></td></tr>
			<tr id="trgrup_ke_'.$k.'" '.$stylehid.'><td>';
		if($r[qcd_prep_group] == "04") {
			$out .= '<table class="table table-bordered table-condensed table-striped">
				<tr>
	        	<th width="90">SILO</th>';
	        $sql2 = "SELECT qcpd_group as qcd_prep_group, qcpd_seq as qcd_prep_seq, qcpd_control_desc, qgu_code from qc_cb_prep_detail left join qc_gen_um on(qc_cb_prep_detail.qcpd_um_id=qc_gen_um.qgu_id) where qcpd_group = '{$r[qcd_prep_group]}' order by qcpd_seq";
			$qry2 = dbselect_plan_all($app_plan_id, $sql2);
			foreach($qry2 as $r2) {
		        $out .= '<th>'.$r2[qcpd_control_desc].'</th>';
			}
			if($stat == "edit" || $stat == "view") {
				// $sql3 = "SELECT distinct qcd_silo_no from qc_cb_detail where qch_id = '{$qch_id}' and qcd_prep_group = '{$r[qcd_prep_group]}'";
				$sql3 = "SELECT qcs_code as qcd_silo_no from qc_cb_silo where qcs_sub_plant = '{$rhead[qch_sub_plant]}' order by qcs_code";
				$qry3 = dbselect_plan_all($app_plan_id, $sql3);
			} else {
				$sql3 = "SELECT qcs_code as qcd_silo_no from qc_cb_silo where qcs_sub_plant = '{$rhead[qch_sub_plant]}' order by qcs_code";
				$qry3 = dbselect_plan_all($app_plan_id, $sql3);
			}
			$idxsilo = 1;
			$tot_stokpow = 0;
			foreach($qry3 as $r3) {
				$out .= '<tr><td class="text-center"><select class="form-control input-sm" onChange="isiNoSilo(\''.$idxsilo.'\',this.value)">'.cbosilo($rhead[qch_sub_plant],$r3[qcd_silo_no],true).'</select></td>';
		        foreach($qry2 as $r2) {
		        	$out .= '<td><input type="hidden" name="qcd_prep_group['.$i.']" id="qcd_prep_group_'.$i.'" value="'.$r2[qcd_prep_group].'"><input type="hidden" name="qcd_prep_seq['.$i.']" id="qcd_prep_seq_'.$i.'" value="'.$r2[qcd_prep_seq].'">';
		        	if($stat == "edit" || $stat == "view") {
		        		$sql4 = "SELECT qcd_prep_value from qc_cb_detail where qch_id = '{$qch_id}' and qcd_prep_group = '{$r2[qcd_prep_group]}' and qcd_prep_seq = {$r2[qcd_prep_seq]} and qcd_silo_no = '{$r3[qcd_silo_no]}'";
						$r4 = dbselect_plan($app_plan_id, $sql4); 
		        		$out .= '<input class="nosilo_'.$idxsilo.'" type="hidden" name="qcd_silo_no['.$i.']" id="qcd_silo_no_'.$i.'" value="'.$r3[qcd_silo_no].'"><input class="form-control input-sm text-right stokpow" type="text" name="qcd_prep_value['.$i.']" id="qcd_prep_value_'.$i.'" onkeyup="hanyanumerik(this.id,this.value);hitungTotalStokPow();" value="'.$r4[qcd_prep_value].'"></td>';
		        		$tot_stokpow += $r4[qcd_prep_value];
		        	} else {
		        		$out .= '<input class="nosilo_'.$idxsilo.'" type="hidden" name="qcd_silo_no['.$i.']" id="qcd_silo_no_'.$i.'" value="'.$r3[qcd_silo_no].'"><input class="form-control input-sm text-right stokpow" type="text" name="qcd_prep_value['.$i.']" id="qcd_prep_value_'.$i.'" onkeyup="hanyanumerik(this.id,this.value);hitungTotalStokPow();"></td>';
		        	}
					$i++;
				}
				$out .= '</tr>';
				$idxsilo++;
			}
			$out .= '<tr><td class="text-center">TOTAL</td><td><input class="form-control input-sm text-right" type="text" name="tot_stokpow" id="tot_stokpow" value="'.$tot_stokpow.'"" readonly></td></tr>';
	        $k++;
	        $out .= '</table>';
    	} else if($r[qcd_prep_group] == "05") {
			$out .= '<table class="table table-bordered table-condensed table-striped">
				<tr>
	        	<th width="90">NO TANK</th>';
	        $sql2 = "SELECT qcpd_group as qcd_prep_group, qcpd_seq as qcd_prep_seq, qcpd_control_desc, qgu_code from qc_cb_prep_detail left join qc_gen_um on(qc_cb_prep_detail.qcpd_um_id=qc_gen_um.qgu_id) where qcpd_group = '{$r[qcd_prep_group]}' order by qcpd_seq";
			$qry2 = dbselect_plan_all($app_plan_id, $sql2);
			foreach($qry2 as $r2) {
		        $out .= '<th>'.$r2[qcpd_control_desc].'</th>';
			}
			if($stat == "edit" || $stat == "view") {
				// $sql3 = "SELECT distinct qcd_slip_no from qc_cb_detail where qch_id = '{$qch_id}' and qcd_prep_group = '{$r[qcd_prep_group]}'";
				$sql3 = "SELECT qct_code as qcd_slip_no from qc_cb_slip_tank where qct_sub_plant = '{$rhead[qch_sub_plant]}' order by qct_code";
				$qry3 = dbselect_plan_all($app_plan_id, $sql3);
			} else {
				$sql3 = "SELECT qct_code as qcd_slip_no from qc_cb_slip_tank where qct_sub_plant = '{$rhead[qch_sub_plant]}' order by qct_code";
				$qry3 = dbselect_plan_all($app_plan_id, $sql3);
			}
			$idxslip = 1;
			foreach($qry3 as $r3) {
				$out .= '<tr><td class="text-center"><select class="form-control input-sm" onChange="isiNoSlip(\''.$idxslip.'\',this.value)">'.cboslip($rhead[qch_sub_plant],$r3[qcd_slip_no],true).'</select></td>';
		        foreach($qry2 as $r2) {
		        	$out .= '<td><input type="hidden" name="qcd_prep_group['.$i.']" id="qcd_prep_group_'.$i.'" value="'.$r2[qcd_prep_group].'"><input type="hidden" name="qcd_prep_seq['.$i.']" id="qcd_prep_seq_'.$i.'" value="'.$r2[qcd_prep_seq].'">';
		        	if($stat == "edit" || $stat == "view") {
		        		$sql4 = "SELECT qcd_prep_value from qc_cb_detail where qch_id = '{$qch_id}' and qcd_prep_group = '{$r2[qcd_prep_group]}' and qcd_prep_seq = {$r2[qcd_prep_seq]} and qcd_slip_no = '{$r3[qcd_slip_no]}'";
						$r4 = dbselect_plan($app_plan_id, $sql4); 
		        		$out .= '<input class="noslip_'.$idxslip.'" type="hidden" name="qcd_slip_no['.$i.']" id="qcd_slip_no_'.$i.'" value="'.$r3[qcd_slip_no].'"><input class="form-control input-sm" type="text" name="qcd_prep_value['.$i.']" id="qcd_prep_value_'.$i.'" value="'.$r4[qcd_prep_value].'"></td>';
		        	} else {
		        		$out .= '<input class="noslip_'.$idxslip.'" type="hidden" name="qcd_slip_no['.$i.']" id="qcd_slip_no_'.$i.'" value="'.$r3[qcd_slip_no].'"><input class="form-control input-sm" type="text" name="qcd_prep_value['.$i.']" id="qcd_prep_value_'.$i.'"></td>';
		        	}
					$i++;
				}
				$out .= '</tr>';
				$idxslip++;
			}
	        $k++;
	        $out .= '</table>';
    	} else {
    		$out .= '<table class="table table-bordered table-condensed table-striped">
				<tr>
	        	<th width="50">NO</th>    
	        	<th>'.$r[qcpm_desc].'</th>
	        	<th width="90">STANDAR</th>';
	        $out .= '<th width="300">NILAI</th></tr>';
	        if($stat == "edit" || $stat == "view") {
				$sql2 = "SELECT qc_cb_detail.*, qcpd_control_desc, qgu_code from qc_cb_detail join qc_cb_prep_detail on(qc_cb_detail.qcd_prep_group=qc_cb_prep_detail.qcpd_group and qc_cb_detail.qcd_prep_seq=qc_cb_prep_detail.qcpd_seq) left join qc_gen_um on(qc_cb_prep_detail.qcpd_um_id=qc_gen_um.qgu_id) where qch_id = '{$qch_id}' and qcd_prep_group = '{$r[qcd_prep_group]}' order by qcd_prep_seq";
			} else {
				$sql2 = "SELECT qcpd_group as qcd_prep_group, qcpd_seq as qcd_prep_seq, qcpd_control_desc, qgu_code from qc_cb_prep_detail left join qc_gen_um on(qc_cb_prep_detail.qcpd_um_id=qc_gen_um.qgu_id) where qcpd_group = '{$r[qcd_prep_group]}' order by qcpd_seq";
			}
			$qry2 = dbselect_plan_all($app_plan_id, $sql2);
			foreach($qry2 as $r2) {
				$out .= '<tr>
		        	<td class="text-center"><input type="hidden" name="qcd_prep_group['.$i.']" id="qcd_prep_group_'.$i.'" value="'.$r2[qcd_prep_group].'"><input type="hidden" name="qcd_prep_seq['.$i.']" id="qcd_prep_seq_'.$i.'" value="'.$r2[qcd_prep_seq].'">'.$r2[qcd_prep_seq].'</td>
		        	<td>'.$r2[qcpd_control_desc].'</td>
		        	<td></td>';
		        if($r[qcd_prep_group] == "01" && $r2[qcd_prep_seq] == 2) {
		        	$out .= '<td><select class="form-control input-sm" name="qcd_prep_value['.$i.']" id="qcd_prep_value_'.$i.'">'.cbokodebody($rhead[qch_sub_plant],$r2[qcd_prep_value],true).'</select></td>';
		        } else if($r[qcd_prep_group] == "01" && $r2[qcd_prep_seq] == 6) {
		        	$out .= '<td><div class="bootstrap-timepicker"><input class="form-control input-sm" type="text" name="qcd_prep_value['.$i.']" id="qcd_prep_value_'.$i.'" value="'.$r2[qcd_prep_value].'"></div></td>';
		        } else {
		        	$out .= '<td><input class="form-control input-sm" type="text" name="qcd_prep_value['.$i.']" id="qcd_prep_value_'.$i.'" value="'.$r2[qcd_prep_value].'"></td>';
		        }
		        $out .= '</tr>';
				$i++;
			}
	        $k++;
	        $out .= '</table>';
    	}
        $out .= '</td></tr>';
	}
	if($stat == "edit" || $stat == "add") {
		$out .= '<tr>
		    <td class="text-center"><button type="button" class="btn btn-primary btn-sm" onClick="simpanData(\''.$stat.'\')">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button></td>
		    </tr>';
	} else {
		$out .= '<tr>
		    <td class="text-center"><button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button></td>
		    </tr>';
	}
	$out .= '</table><script type="text/javascript">$("#qcd_prep_value_5").timepicker({showInputs:false,showMeridian:false,minuteStep:5});</script>';

    if($stat == "edit" || $stat == "view") {
    	$responce->qch_id = $rhead[qch_id];
    	$responce->qch_sub_plant = $rhead[qch_sub_plant];
    	$responce->qch_date = $rhead[date];
    	$responce->qch_shift = cbo_shift($rhead[qch_shift]);
    	$responce->qch_bm_no = cbobalmil($rhead[qch_sub_plant],$rhead[qch_bm_no],true);
    }
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>