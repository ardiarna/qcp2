<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['51'];
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
	case "cboline":
		cboline($_POST['subplan']);
		break;
	case "cbomotif":
		cbomotif();
		break;
	case "detailtabel":
		detailtabel($_POST['stat']);
		break;
	case "cariprevqcdaily":
		cariprevqcdaily();
		break;
}

function urai(){
	global $app_plan_id, $akses, $app_subplan;
	$page = $_POST['page']; 
	$rows = $_POST['rows']; 
	$sidx = $_POST['sidx']; 
	$sord = $_POST['sord'];
	$subplan_kode = $_GET['subplan'];
	$tanggal = explode('-', $_GET['tanggal']);
	$bulan = $tanggal[0];
	$tahun = $tanggal[1];
	$whsatu = dptKondisiWhere($_POST['_search'],$_POST['filters'],$_POST['searchField'],$_POST['searchOper'],$_POST['searchString']);
	$whdua = "";
	if($app_subplan <> 'All') {
		$whdua .= " and qex_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and qex_sub_plant = '".$subplan_kode."'";
	}
	if($bulan <> 'All') {
		$whdua .= " and date_part('month',qex_date)='".$bulan."'";
	}
	if($tahun <> 'All') {
		$whdua .= " and date_part('year',qex_date)='".$tahun."'";
	}
	if($_POST['qex_id']) {
		$whdua .= " and qex_id = '".$_POST['qex_id']."'";
	}
	if($_POST['qex_sub_plant']) {
		$whdua .= " and qex_sub_plant = '".$_POST['qex_sub_plant']."'";
	}
	if($_POST['qex_line']) {
		$whdua .= " and qex_line = '".$_POST['qex_line']."'";
	}
	if($_POST['qex_date']) {
		$whdua .= " and qex_date = '".$_POST['qex_date']."'";
	}
	if($_POST['qex_motif']) {
		$whdua .= " and lower(qex_motif) like '%".strtolower($_POST['qex_motif'])."%'";
	}
	
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qcdaily_exp where qex_rec_status='N' $whsatu $whdua";
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
		$sql = "SELECT qex_id, qex_sub_plant, qex_line, qex_date, qex_motif, qex_seri, qex_shading, qex_exp, qex_eco, qex_kw from qcdaily_exp where qex_rec_status='N' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['qex_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qex_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qex_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['qex_date']);
			$ro['date'] = $datetime[0];
			$ro['time'] = substr($datetime[1],0,5);
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qex_id'],$ro['qex_sub_plant'],$ro['qex_line'],$ro['date'],$ro['time'],$ro['qex_motif'],$ro['qex_seri'],$ro['qex_shading'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function suburai(){
	global $app_plan_id;
	$qex_id = $_POST['qex_id'];
	$sql = "SELECT qcdaily_eco.*, qmd_nama, qss_desc from qcdaily_eco join qc_md_defect on(qcdaily_eco.qec_defect_kode=qc_md_defect.qmd_kode) join qc_md_defect on(qcdaily_eco.qec_exp=qc_md_defect.qssd_seq) where qex_id = '{$qex_id}' order by qec_defect_kode, qec_exp";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	foreach($qry as $ro){
		$responce->rows[$i]['cell']=array($ro['qss_desc'],$ro['qec_exp'],$ro['qmd_nama'],$ro['qec_m2'],$ro['qkw_m2']);
		$i++;
	}		
	echo json_encode($responce);	
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[qex_date] = cgx_dmy2ymd($r[qex_date])." ".$r[qex_time].":00";
	$r[qex_exp] = cgx_angka($r[qex_exp]);
	$r[qex_eco] = cgx_angka($r[qex_eco]);
	$r[qex_kw] = cgx_angka($r[qex_kw]);
	if($stat == "add") {
		$r[qex_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[qex_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(qex_id) as qex_id_max from qcdaily_exp where qex_sub_plant = '{$r[qex_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[qex_id_max] == ''){
			$mx[qex_id_max] = 0;
		} else {
			$mx[qex_id_max] = substr($mx[qex_id_max],-7);
		}
		$urutbaru = $mx[qex_id_max]+1;
		$r[qex_id] = $app_plan_id.$r[qex_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		$sql = "INSERT into qcdaily_exp(qex_id, qex_sub_plant, qex_line, qex_date, qex_motif, qex_seri, qex_shading, qex_rec_status, qex_exp, qex_eco, qex_kw, qex_user_create, qex_date_create) values('{$r[qex_id]}', '{$r[qex_sub_plant]}', '{$r[qex_line]}', '{$r[qex_date]}', '{$r[qex_motif]}', '{$r[qex_seri]}', '{$r[qex_shading]}', 'N', {$r[qex_exp]}, {$r[qex_eco]}, {$r[qex_kw]}, '{$r[qex_user_create]}', '{$r[qex_date_create]}'); DELETE from prev_qcdaily where pq_plant_kode = '{$r[qex_sub_plant]}' and pq_line_kode = '{$r[qex_line]}'; INSERT into prev_qcdaily values('{$r[qex_sub_plant]}', '{$r[qex_line]}', '{$r[qex_motif]}', '{$r[qex_seri]}', '{$r[qex_shading]}');";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k2sql = "";
			foreach ($r[qec_defect_kode] as $i => $value) {
				if($value){
					$r[qec_m2][$i] = cgx_angka($r[qec_m2][$i]);
					$r[qkw_m2][$i] = cgx_angka($r[qkw_m2][$i]);
					$k2sql .= "INSERT into qcdaily_eco(qec_id, qec_sub_plant, qec_line, qec_date, qec_motif, qec_seri, qec_shading, qec_rec_status, qec_defect_kode, qec_m2, qec_keterangan) values('{$r[qex_id]}', '{$r[qex_sub_plant]}', '{$r[qex_line]}', '{$r[qex_date]}', '{$r[qex_motif]}', '{$r[qex_seri]}', '{$r[qex_shading]}', 'N', '{$r[qec_defect_kode][$i]}', {$r[qec_m2][$i]}, '{$r[qec_keterangan][$i]}');";
					$k2sql .= "INSERT into qcdaily_kw(qkw_id, qkw_sub_plant, qkw_line, qkw_date, qkw_motif, qkw_seri, qkw_shading, qkw_rec_status, qkw_defect_kode, qkw_m2, qkw_keterangan) values('{$r[qex_id]}', '{$r[qex_sub_plant]}', '{$r[qex_line]}', '{$r[qex_date]}', '{$r[qex_motif]}', '{$r[qex_seri]}', '{$r[qex_shading]}', 'N', '{$r[qec_defect_kode][$i]}', {$r[qkw_m2][$i]}, '{$r[qkw_keterangan][$i]}');";
				}
			}
			$out = dbsave_plan($app_plan_id, $k2sql);
		} else {
			$out = $xsql;
		}
	} else if($stat=='edit') {
		$r[qex_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[qex_date_modify] = date("Y-m-d H:i:s");
		$sql = "UPDATE qcdaily_exp set qex_line = '{$r[qex_line]}', qex_date = '{$r[qex_date]}', qex_motif = '{$r[qex_motif]}', qex_seri = '{$r[qex_seri]}', qex_shading = '{$r[qex_shading]}', qex_exp = {$r[qex_exp]}, qex_eco = {$r[qex_eco]}, qex_kw = {$r[qex_kw]}, qex_user_modify = '{$r[qex_user_modify]}', qex_date_modify = '{$r[qex_date_modify]}' where qex_id = '{$r[qex_id]}';  DELETE from prev_qcdaily where pq_plant_kode = '{$r[qex_sub_plant]}' and pq_line_kode = '{$r[qex_line]}'; INSERT into prev_qcdaily values('{$r[qex_sub_plant]}', '{$r[qex_line]}', '{$r[qex_motif]}', '{$r[qex_seri]}', '{$r[qex_shading]}');";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qcdaily_eco where qec_id = '{$r[qex_id]}';DELETE from qcdaily_kw where qkw_id = '{$r[qex_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			if($x1sql == "OK") {
				$k2sql = "";
				foreach ($r[qec_defect_kode] as $i => $value) {
					if($value){
						$r[qec_m2][$i] = cgx_angka($r[qec_m2][$i]);
						$r[qkw_m2][$i] = cgx_angka($r[qkw_m2][$i]);
						$k2sql .= "INSERT into qcdaily_eco(qec_id, qec_sub_plant, qec_line, qec_date, qec_motif, qec_seri, qec_shading, qec_rec_status, qec_defect_kode, qec_m2, qec_keterangan) values('{$r[qex_id]}', '{$r[qex_sub_plant]}', '{$r[qex_line]}', '{$r[qex_date]}', '{$r[qex_motif]}', '{$r[qex_seri]}', '{$r[qex_shading]}', 'N', '{$r[qec_defect_kode][$i]}', {$r[qec_m2][$i]}, '{$r[qec_keterangan][$i]}');";
						$k2sql .= "INSERT into qcdaily_kw(qkw_id, qkw_sub_plant, qkw_line, qkw_date, qkw_motif, qkw_seri, qkw_shading, qkw_rec_status, qkw_defect_kode, qkw_m2, qkw_keterangan) values('{$r[qex_id]}', '{$r[qex_sub_plant]}', '{$r[qex_line]}', '{$r[qex_date]}', '{$r[qex_motif]}', '{$r[qex_seri]}', '{$r[qex_shading]}', 'N', '{$r[qec_defect_kode][$i]}', {$r[qkw_m2][$i]}, '{$r[qkw_keterangan][$i]}');";
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
	$qex_id = $_POST['kode'];
	$sql = "UPDATE qcdaily_exp set qex_rec_status='C' where qex_id = '{$qex_id}';";
	echo dbsave_plan($app_plan_id, $sql); 
}

function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant();
	$out .= $withselect ? "</select>" : "";
	echo $out;
}

function cboline($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qml_kode, qml_nama from qc_md_line where qml_plant_code = '{$subplan}' order by qml_kode";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[qml_kode] == $nilai){
				$out .= "<option value='$r[qml_kode]' selected>$r[qml_nama]</option>";
			} else {
				$out .= "<option value='$r[qml_kode]'>$r[qml_nama]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;	
	}
}

function cbomotif($nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qmm_nama from qc_md_motif order by qmm_nama";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[qmm_nama] == $nilai){
				$out .= "<option selected>$r[qmm_nama]</option>";
			} else {
				$out .= "<option>$r[qmm_nama]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;	
	}
}

function cbodefect($nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qmd_kode, qmd_nama from qc_md_defect order by qmd_kode";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[qmd_kode] == $nilai) {
				$out .= "<option value='$r[qmd_kode]' selected>$r[qmd_nama]</option>";
			} else {
				$out .= "<option value='$r[qmd_kode]'>$r[qmd_nama]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;
	}
}

function cariprevqcdaily() {
	global $app_plan_id;
	$subplan = $_POST['subplan'];
	$line = $_POST['line'];
	$sql = "SELECT * from prev_qcdaily where pq_plant_kode = '{$subplan}' and pq_line_kode = '{$line}'";
	$r = dbselect_plan($app_plan_id, $sql);
	$responce->qex_motif = $r[pq_motif];
    $responce->qex_seri = $r[pq_seri];
    $responce->qex_shading = $r[pq_shading];
    echo json_encode($responce);
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit" || $stat == "view") {
		$qex_id = $_POST['kode'];
		$sql0 = "SELECT * from qcdaily_exp where qex_id = '{$qex_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['qex_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$rhead['time'] = substr($datetime[1],0,5);
		$arr_defect = array();
		$qec_m2 = array();
		$qec_keterangan = array();
		$qkw_m2 = array();
		$qkw_keterangan = array();
		$sql1 = "SELECT qec_defect_kode, qec_m2, qec_keterangan from qcdaily_eco where qec_id = '{$qex_id}' order by qec_defect_kode";
		$qry1 = dbselect_plan_all($app_plan_id, $sql1);
		foreach($qry1 as $r1) {
			$arr_defect["$r1[qec_defect_kode]"] = $r1[qec_defect_kode];
			$qec_m2["$r1[qec_defect_kode]"] = $r1[qec_m2];
			$qec_keterangan["$r1[qec_defect_kode]"] = $r1[qec_keterangan];
		}
		$sql2 = "SELECT qkw_defect_kode, qkw_m2, qkw_keterangan from qcdaily_kw where qkw_id = '{$qex_id}' order by qkw_defect_kode";
		$qry2 = dbselect_plan_all($app_plan_id, $sql2);
		foreach($qry2 as $r2) {
			$arr_defect["$r2[qkw_defect_kode]"] = $r2[qkw_defect_kode];
			$qkw_m2["$r2[qkw_defect_kode]"] = $r2[qkw_m2];
			$qkw_keterangan["$r2[qkw_defect_kode]"] = $r2[qkw_keterangan];
		}
	} else {
		$arr_defect = array('','','','','','','','');
	}
	$out = '<table id="tabeldetail" class="table table-bordered table-striped table-condensed table-hover">
		<tr">
    	<th>DEFECT</th>
    	<th>ECO</th>
    	<th>KW</th>
    	</tr>';
	$i = 0;
	$qex_eco = 0;
	$qex_kw = 0;
	foreach($arr_defect as $defect) {
		$out .= '<tr <tr id="trdet_ke_'.$i.'">
    	<td><select class="form-control input-sm" name="qec_defect_kode['.$i.']" id="qec_defect_kode_'.$i.'">'.cbodefect($defect,true).'</select></td>
    	<td><input class="form-control input-sm text-right" type="text" placeholder="meter persegi" name="qec_m2['.$i.']" id="qec_m2_'.$i.'" value="'.$qec_m2[$defect].'" onkeyup="hanyanumerik(this.id,this.value);hitungTotalEco();"><input class="form-control input-sm" style="margin-top:5px;" type="text" placeholder="keterangan" name="qec_keterangan['.$i.']" id="qec_keterangan_'.$i.'" value="'.$qec_keterangan[$defect].'"></td>
    	<td><input class="form-control input-sm text-right" type="text" placeholder="meter persegi" name="qkw_m2['.$i.']" id="qkw_m2_'.$i.'" value="'.$qkw_m2[$defect].'" onkeyup="hanyanumerik(this.id,this.value);hitungTotalKw();"><input class="form-control input-sm" style="margin-top:5px;" type="text" placeholder="keterangan" name="qkw_keterangan['.$i.']" id="qkw_keterangan_'.$i.'" value="'.$qkw_keterangan[$defect].'"></td>
    	</tr>';
		$i++;
		$qex_eco += $qec_m2[$defect];
		$qex_kw += $qkw_m2[$defect];
	}
	$out .= '<tr>
    	<td class="text-center"><strong>TOTAL</strong></td>
    	<td><input class="form-control input-sm text-right" type="text" name="qex_eco" id="qex_eco" value="'.$qex_eco.'"" readonly></td>
    	<td><input class="form-control input-sm text-right" type="text" name="qex_kw" id="qex_kw" value="'.$qex_kw.'"" readonly></td>
    	</tr>';
	if($stat == "edit" || $stat == "add") {
		$out .= '<tr>
		    <td colspan="3" class="text-center"><input type="hidden" id="barisLast" value="'.$i.'"><button type="button" class="btn btn-primary btn-sm" onClick="simpanData(\''.$stat.'\')">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button></td>
		    </tr>';
	} else {
		$out .= '<tr>
		    <td colspan="3" class="text-center"><button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button></td>
		    </tr>';
	}
	$out .= '</table>';

	if($stat == "edit" || $stat == "view") {
    	$responce->qex_id = $rhead[qex_id];
	    $responce->qex_date = $rhead[date];
	    $responce->qex_time = $rhead[time];
	    $responce->qex_line = cboline($rhead[qex_sub_plant],$rhead[qex_line],true);
	    $responce->qex_motif = $rhead[qex_motif];
	    $responce->qex_seri = $rhead[qex_seri];
	    $responce->qex_shading = $rhead[qex_shading];
	    $responce->qex_sub_plant = $rhead[qex_sub_plant];
	    $responce->qex_exp = $rhead[qex_exp];
    }
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>