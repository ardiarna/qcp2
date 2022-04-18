<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['26'];
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
	case "cboline":
		cboline();
		break;
	case "cbopress":
		cbopress($_POST['subplan']);
		break;
	case "detailtabel":
		detailtabel($_POST['stat']);
		break;
}

function cboline($nilai = "", $isret = false){
	$qry = array("","1","2","3");
	if(is_array($qry)) {
		foreach($qry as $r) {
			if($r == $nilai) {
				$out .= "<option value='{$r}' selected>$r</option>";
			} else {
				$out .= "<option value='{$r}'>$r</option>";
			}	
		}	
	}

	if($isret){
		return $out;
	} else {
		echo $out;
	}
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
		$whdua .= " and qph_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and qph_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['qph_id']) {
		$whdua .= " and qph_id = '".$_POST['qph_id']."'";
	}
	if($_POST['qph_sub_plant']) {
		$whdua .= " and qph_sub_plant = '".$_POST['qph_sub_plant']."'";
	}
	if($_POST['qph_date']) {
		$whdua .= " and qph_date = '".$_POST['qph_date']."'";
	}
	if($_POST['qph_shift']) {
		$whdua .= " and qph_shift = '".$_POST['qph_shift']."'";
	}
	if($_POST['qph_no_line']) {
		$whdua .= " and qph_no_line = '".$_POST['qph_no_line']."'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_pd_header where qph_rec_stat='N' and qph_date >= '{$tglfrom}' and qph_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT qph_id, qph_sub_plant, qph_date, qph_shift, qph_no_line from qc_pd_header where qph_rec_stat='N' and qph_date >= '{$tglfrom}' and qph_date <= '{$tglto}' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['qph_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qph_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qph_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['qph_date']);
			$ro['date'] = $datetime[0];
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qph_id'],$ro['qph_sub_plant'],$ro['date'],$ro['qph_shift'],$ro['qph_no_line'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function suburai(){
	global $app_plan_id;
	$qph_id = $_POST['qph_id'];
	$sql = "SELECT qc_pd_detail.*, qpg_desc, qpgd_control_desc, qgu_code from qc_pd_detail join qc_pd_group on(qc_pd_detail.qpd_pd_group=qc_pd_group.qpg_group) join qc_pd_group_detail on(qc_pd_detail.qpd_pd_group=qc_pd_group_detail.qpgd_group and qc_pd_detail.qpd_pd_seq=qc_pd_group_detail.qpgd_seq) left join qc_gen_um on(qc_pd_group_detail.qpgd_um_id=qc_gen_um.qgu_id) where qph_id = '{$qph_id}' order by qpd_pd_group, qpd_pd_seq";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	foreach($qry as $ro){
		$responce->rows[$i]['cell']=array($ro['qpg_desc'],$ro['qpd_pd_seq_'],$ro['qpgd_control_desc'],$ro['qpd_standart'],$ro['qgu_code'],$ro['qpd_mould_no'],$ro['qpd_hd_no'],$ro['qpd_pd_remark'],$ro['qpd_pd_value']);
		$i++;
	}		
	echo json_encode($responce);	
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;

	// if($_SESSION[$app_id]['user']['user_name'] != 'rozy'){
	// 	echo 'Maaf, sedang dalam penyesuaian, IT';exit();
	// }

	$r[qph_shift] = cgx_angka($r[qph_shift]);
	$r[qph_date] = cgx_dmy2ymd($r[qph_date]);
	if($stat == "add") {
		$r[qph_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[qph_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(qph_id) as qph_id_max from qc_pd_header where qph_sub_plant = '{$r[qph_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[qph_id_max] == ''){
			$mx[qph_id_max] = 0;
		} else {
			$mx[qph_id_max] = substr($mx[qph_id_max],-7);
		}
		$urutbaru = $mx[qph_id_max]+1;
		$r[qph_id] = $app_plan_id.$r[qph_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		$sql = "INSERT into qc_pd_header(qph_id, qph_sub_plant, qph_date, qph_rec_stat, qph_user_create, qph_date_create, qph_shift, qph_no_line) values('{$r[qph_id]}', '{$r[qph_sub_plant]}', '{$r[qph_date]}', 'N', '{$r[qph_user_create]}', '{$r[qph_date_create]}', {$r[qph_shift]}, '{$r[qph_no_line]}');";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k2sql = "";
			foreach ($r[qpd_pd_seq] as $i => $value) {
				$r[qpd_pd_value][$i] = cgx_angka($r[qpd_pd_value][$i]);
				if($r[qpd_pd_valmax][$i] == ''){$r[qpd_pd_valmax][$i] = 0;}
				$k2sql .= "INSERT into qc_pd_detail(qph_id, qpd_mould_no, qpd_hd_no, qpd_pd_group, qpd_pd_seq, qpd_pd_remark, qpd_pd_value, qpd_pd_valmax) values('{$r[qph_id]}', '{$r[qpd_mould_no][$i]}', '{$r[qpd_hd_no][$i]}', '{$r[qpd_pd_group][$i]}', {$r[qpd_pd_seq][$i]}, '{$r[qpd_pd_remark][$i]}', {$r[qpd_pd_value][$i]}, '{$r[qpd_pd_valmax][$i]}');";
			}
			$out = dbsave_plan($app_plan_id, $k2sql);
		} else {
			$out = $xsql;
		}
	} else if($stat=='edit') {
		$r[qph_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[qph_date_modify] = date("Y-m-d H:i:s");
		$sql = "UPDATE qc_pd_header set qph_no_line = '{$r[qph_no_line]}', qph_user_modify = '{$r[qph_user_modify]}', qph_date_modify = '{$r[qph_date_modify]}' where qph_id = '{$r[qph_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_pd_detail where qph_id = '{$r[qph_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			if($x1sql == "OK") {
				$k2sql = "";
				foreach ($r[qpd_pd_seq] as $i => $value) {
					$r[qpd_pd_value][$i] = cgx_angka($r[qpd_pd_value][$i]);
					if($r[qpd_pd_valmax][$i] == ''){$r[qpd_pd_valmax][$i] = 0;}
					$k2sql .= "INSERT into qc_pd_detail(qph_id, qpd_mould_no, qpd_hd_no, qpd_pd_group, qpd_pd_seq, qpd_pd_remark, qpd_pd_value, qpd_pd_valmax) values('{$r[qph_id]}', '{$r[qpd_mould_no][$i]}', '{$r[qpd_hd_no][$i]}', '{$r[qpd_pd_group][$i]}', {$r[qpd_pd_seq][$i]}, '{$r[qpd_pd_remark][$i]}', {$r[qpd_pd_value][$i]}, '{$r[qpd_pd_valmax][$i]}');";
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
	$qph_id = $_POST['kode'];
	$sql = "UPDATE qc_pd_header set qph_rec_stat='C' where qph_id = '{$qph_id}';";
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

function cbopress($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qpp_code, qpp_desc from qc_pd_press where qpp_sub_plant = '{$subplan}' order by qpp_code";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if(trim($r[qpp_code]) == $nilai){
				$out .= "<option value='$r[qpp_code]' selected>$r[qpp_code]</option>";
			} else {
				$out .= "<option value='$r[qpp_code]'>$r[qpp_code]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;
	}
}

function cbomp($subplan, $press, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qpm_code, qpm_desc from qc_pd_mouldset where qpm_sub_plant = '{$subplan}' and qpm_press_code = '{$press}' order by qpm_code asc";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[qpm_code] == $nilai) {
				$out .= "<option value='$r[qpm_code]' selected>$r[qpm_desc]</option>";
			} else {
				$out .= "<option value='$r[qpm_code]'>$r[qpm_desc]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;
	}
}

function cbohd($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qph_code, qph_desc from qc_pd_hd where qph_sub_plant = '{$subplan}'";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[qph_code] == $nilai) {
				$out .= "<option value='$r[qph_code]' selected>$r[qph_desc]</option>";
			} else {
				$out .= "<option value='$r[qph_code]'>$r[qph_desc]</option>";
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
		$qph_id = $_POST['kode'];
		$sql0 = "SELECT * from qc_pd_header where qph_id = '{$qph_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['qph_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$sql = "SELECT distinct qpd_pd_group, qpg_desc from qc_pd_detail join qc_pd_group on(qc_pd_detail.qpd_pd_group=qc_pd_group.qpg_group) where qph_id = '{$qph_id}' order by qpd_pd_group";
	} else {
		$rhead[qph_sub_plant] = $_POST['subplan'];
		$rhead[qph_no_line] = $_POST['no_line'];
		$sql = "SELECT qpg_group as qpd_pd_group, qpg_desc from qc_pd_group order by qpg_group";
		$stylehid = 'style="display:none;"';
	}
	$k = 0;
	$i = 0;
	$out = '<table class="table">';
	$qry = dbselect_plan_all($app_plan_id, $sql);
	foreach($qry as $r) {
		$out .= '<tr><td><button type="button" class="btn btn-default btn-sm btn-block" style="text-align:left;" onClick="hideGrup(\''.$k.'\')">'.$r[qpd_pd_group].'. INPUT '.$r[qpg_desc].'</button></td></tr>
			<tr id="trgrup_ke_'.$k.'" '.$stylehid.'><td><table class="table table-bordered table-condensed table-striped">
			<tr>
        	<th width="50">NO</th>    
        	<th>'.$r[qpg_desc].'</th>
        	<th width="90">STD</th>
        	<th width="90">UNIT</th>';
        if($r[qpd_pd_group] == "02"){
        	$out .= '<th width="90">MP</th>';
        	$out .= '<th width="90">MIN</th>';
        	$out .= '<th width="90">MAX</th>';
        } else if($r[qpd_pd_group] == "03"){
        	$out .= '<th width="90">HD</th>';
        	$out .= '<th width="90">MIN</th>';
        	$out .= '<th width="90">MAX</th>';
        }

        
        
        if($r[qpd_pd_group] == "01"){
        	$out .= '<th width="90">NILAI</th>';
        	$out .= '<th>REMARK</th>';
        }  
        $out .= '</tr>';
        if($stat == "edit" || $stat == "view") {
			$sql2 = "SELECT qc_pd_detail.*, qpgd_control_desc, qpgd_standar, qgu_code 
					 from qc_pd_detail 
					 join qc_pd_group_detail on( 
					 	qc_pd_detail.qpd_pd_group = qc_pd_group_detail.qpgd_group and qc_pd_detail.qpd_pd_seq = qc_pd_group_detail.qpgd_seq
					 	and qc_pd_group_detail.qpgd_subplant = '{$rhead[qph_sub_plant]}'
					 ) left join qc_gen_um on(qc_pd_group_detail.qpgd_um_id=qc_gen_um.qgu_id) where qph_id = '{$qph_id}' and qpd_pd_group = '{$r[qpd_pd_group]}' order by qpd_pd_seq";
		} else {
			$sql2 = "SELECT qpgd_group as qpd_pd_group, qpgd_seq as qpd_pd_seq, qpgd_control_desc, qpgd_standar, qgu_code 
					 from qc_pd_group_detail 
					 left join qc_gen_um on(qc_pd_group_detail.qpgd_um_id=qc_gen_um.qgu_id) 
					 where qpgd_group = '{$r[qpd_pd_group]}' and qpgd_isactive = 'Y' 
					 and qpgd_subplant = '{$rhead[qph_sub_plant]}'
					 order by qpgd_seq";
		}
		$qry2 = dbselect_plan_all($app_plan_id, $sql2);
		foreach($qry2 as $r2) {
			$out .= '<tr>
	        	<td class="text-center"><input type="hidden" name="qpd_pd_group['.$i.']" id="qpd_pd_group_'.$i.'" value="'.$r2[qpd_pd_group].'"><input type="hidden" name="qpd_pd_seq['.$i.']" id="qpd_pd_seq_'.$i.'" value="'.$r2[qpd_pd_seq].'">'.$r2[qpd_pd_seq].'</td>
	        	<td>'.$r2[qpgd_control_desc].'</td>
	        	<td class="text-center">'.$r2[qpgd_standar].'</td>
	        	<td class="text-center">'.$r2[qgu_code].'</td>';
	        if($r[qpd_pd_group] == "02"){
	        	$out .= '<td><select class="form-control input-sm" name="qpd_mould_no['.$i.']" id="qpd_mould_no_'.$i.'">'.cbomp($rhead[qph_sub_plant],$rhead[qph_no_line],$r2[qpd_mould_no],true).'</select></td>';
	        	$out .= '<td><input class="form-control input-sm" type="text" name="qpd_pd_value['.$i.']" id="qpd_pd_value_'.$i.'" value="'.$r2[qpd_pd_value].'"></td>';
	        	$out .= '<td><input class="form-control input-sm" type="text" name="qpd_pd_valmax['.$i.']" id="qpd_pd_valmax_'.$i.'" value="'.$r2[qpd_pd_valmax].'"></td>';
	        } else if($r[qpd_pd_group] == "03"){
	        	$out .= '<td><select class="form-control input-sm" name="qpd_hd_no['.$i.']" id="qpd_hd_no_'.$i.'">'.cbohd($rhead[qph_sub_plant],$r2[qpd_hd_no],true).'</select></td>';
	        	$out .= '<td><input class="form-control input-sm" type="text" name="qpd_pd_value['.$i.']" id="qpd_pd_value_'.$i.'" value="'.$r2[qpd_pd_value].'"></td>';
	        	$out .= '<td><input class="form-control input-sm" type="text" name="qpd_pd_valmax['.$i.']" id="qpd_pd_valmax_'.$i.'" value="'.$r2[qpd_pd_valmax].'"></td>';
	        }
	        
	        if($r[qpd_pd_group] == "01"){
	        	$out .= '<td><input class="form-control input-sm" type="text" name="qpd_pd_value['.$i.']" id="qpd_pd_value_'.$i.'" value="'.$r2[qpd_pd_value].'"></td>';
	        	$out .= '<td><input class="form-control input-sm" type="text" name="qpd_pd_remark['.$i.']" id="qpd_pd_remark_'.$i.'" value="'.$r2[qpd_pd_remark].'"></td>';
	        }
	        $out .= '</tr>';
			$i++;
		}
        $k++;
        $out .= '</table></td></tr>';
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
	$out .= '</table>';
	
    if($stat == "edit" || $stat == "view") {
    	$responce->qph_id = $rhead[qph_id];
    	$responce->qph_sub_plant = $rhead[qph_sub_plant];
    	$responce->qph_date = $rhead[date];
    	$responce->qph_shift = cbo_shift($rhead[qph_shift]);
    	$responce->qph_no_line = cboline($rhead[qph_no_line], true);
    }
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>