<?php

include_once("../libs/init.php");

$sTable_h = "qc_pd_cm_header";
$sTable_d = "qc_pd_cm_detail";

$akses = $_SESSION[$app_id]['app_priv']['94'];
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
	case "cbomotif":
		cbomotif();
		break;
	case "cbodefect":
		cbodefect();
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
		$whdua .= " and rg_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and rg_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['rg_id']) {
		$whdua .= " and rg_id = '".$_POST['rg_id']."'";
	}
	if($_POST['rg_sub_plant']) {
		$whdua .= " and rg_sub_plant = '".$_POST['rg_sub_plant']."'";
	}
	if($_POST['rg_line']) {
		$whdua .= " and rg_line= '".$_POST['rg_line']."'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) AS count FROM qc_fg_rg_header WHERE rg_status = 'N' AND rg_date >= '{$tglfrom}' AND rg_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT rg_id, rg_sub_plant, rg_date, rg_line, rg_motif FROM qc_fg_rg_header 
			    WHERE rg_status = 'N' and rg_date >= '{$tglfrom}' and rg_date <= '{$tglto}' $whsatu $whdua
				ORDER BY $sidx $sord $limit";
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['rg_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['rg_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['rg_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['rg_date']);
			$ro['date'] = $datetime[0];
			$ro['time'] = substr($datetime[1],0,5);
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['rg_sub_plant'],$ro['rg_id'],$ro['date'],$ro['time'],$ro['rg_line'],$ro['rg_motif'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}



function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[rg_date] = cgx_dmy2ymd($r[rg_date])." ".$r[rg_time].":00";

	if($r[rg_time] >= "08:00" && $r[rg_time] <= "15:59"){
		$r[rg_shift] = 1;
	} else if($r[rg_time] >= "16:00" && $r[rg_time] <= "23:59"){
		$r[rg_shift] = 2;
	} else {
		$r[rg_shift] = 3;
	}

	if($stat == "add") {
		$r[rg_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[rg_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(rg_id) as rg_id_max from qc_fg_rg_header where rg_sub_plant = '{$r[rg_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[rg_id_max] == ''){
			$mx[rg_id_max] = 0;
		} else {
			$mx[rg_id_max] = substr($mx[rg_id_max],-7);
		}
		$urutbaru = $mx[rg_id_max]+1;
		$r[rg_id] = $app_plan_id.$r[rg_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		
		//cek jika duplikat
		$qdup = "SELECT COUNT(*) AS jmldup from qc_fg_rg_header 
				 WHERE rg_status = 'N' AND rg_sub_plant = '{$r[rg_sub_plant]}' AND rg_date = '{$r[rg_date]}' AND rg_line = '{$r[rg_line]}' ";
		$ddup = dbselect_plan($app_plan_id, $qdup);

		if($ddup[jmldup] > 0){
			$out = "Terjadi Duplikat Data";
		}else{
			$sql = "INSERT into qc_fg_rg_header(rg_sub_plant, rg_id, rg_date, rg_line, rg_shift, rg_user_create, rg_date_create, rg_status, rg_motif) values('{$r[rg_sub_plant]}','{$r[rg_id]}',  '{$r[rg_date]}', '{$r[rg_line]}', '{$r[rg_shift]}','{$r[rg_user_create]}', '{$r[rg_date_create]}', 'N', '{$r[rg_motif]}');";
			$xsql = dbsave_plan($app_plan_id, $sql); 
			$out = $xsql;
			if($xsql == "OK") {
				$r[qty_export] = cgx_angka($r[qty_export]);
				$k2sql = "INSERT into qc_fg_rg_detail(rg_id, rg_qly, rg_per_2h, rg_shading, rg_size, rg_calibro) 
					values('{$r[rg_id]}', 1, {$r[qty_export]}, '{$r[shd_export]}', '{$r[siz_export]}', '{$r[cbr_export]}');";
				foreach ($r[defect_kode_ekonomi] as $i => $value) {
					if($r[defect_kode_ekonomi][$i] <> '' && $r[per_2h_ekonomi][$i] <> ''){
						$k2sql .= "INSERT into qc_fg_rg_detail(rg_id, rg_qly, rg_per_2h, rg_shading, rg_size, rg_calibro, rg_defect_kode) 
							values('{$r[rg_id]}', 2, {$r[per_2h_ekonomi][$i]}, '{$r[shd_ekonomi]}', '{$r[siz_ekonomi]}', '{$r[cbr_ekonomi]}', '{$r[defect_kode_ekonomi][$i]}');";
					}
				}
				foreach ($r[defect_kode_rijsor] as $i => $value) {
					if($r[defect_kode_rijsor][$i] <> '' && $r[per_2h_rijsor][$i] <> ''){
						$k2sql .= "INSERT into qc_fg_rg_detail(rg_id, rg_qly, rg_per_2h, rg_shading, rg_size, rg_calibro, rg_defect_kode) 
							values('{$r[rg_id]}', 4, {$r[per_2h_rijsor][$i]}, '{$r[shd_rijsor]}', '{$r[siz_rijsor]}', '{$r[cbr_rijsor]}', '{$r[defect_kode_rijsor][$i]}');";
					}
				}
				foreach ($r[defect_kode_rijpal] as $i => $value) {
					if($r[defect_kode_rijpal][$i] <> '' && $r[per_2h_rijpal][$i] <> ''){
						$k2sql .= "INSERT into qc_fg_rg_detail(rg_id, rg_qly, rg_per_2h, rg_shading, rg_size, rg_calibro, rg_defect_kode) 
							values('{$r[rg_id]}', 5, {$r[per_2h_rijpal][$i]}, '{$r[shd_rijpal]}', '{$r[siz_rijpal]}', '{$r[cbr_rijpal]}', '{$r[defect_kode_rijpal][$i]}');";
					}
				}
				foreach ($r[defect_kode_rijbua] as $i => $value) {
					if($r[defect_kode_rijbua][$i] <> '' && $r[per_2h_rijbua][$i] <> ''){
						$k2sql .= "INSERT into qc_fg_rg_detail(rg_id, rg_qly, rg_per_2h, rg_shading, rg_size, rg_calibro, rg_defect_kode) 
							values('{$r[rg_id]}', 6, {$r[per_2h_rijbua][$i]}, '{$r[shd_rijbua]}', '{$r[siz_rijbua]}', '{$r[cbr_rijbua]}', '{$r[defect_kode_rijbua][$i]}');";
					}
				}
				$out = dbsave_plan($app_plan_id, $k2sql);
			} else {
				$out = $xsql;
			}
		}
	} else if($stat=='edit') {
		$r[rg_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[rg_date_modify] = date("Y-m-d H:i:s");
		$sql = "UPDATE qc_fg_rg_header set rg_motif = '{$r[rg_motif]}', rg_user_modify = '{$r[rg_user_modify]}', rg_date_modify = '{$r[rg_date_modify]}' WHERE rg_id = '{$r[rg_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_fg_rg_detail where rg_id = '{$r[rg_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			$r[qty_export] = cgx_angka($r[qty_export]);
			$k2sql = "INSERT into qc_fg_rg_detail(rg_id, rg_qly, rg_per_2h, rg_shading, rg_size, rg_calibro) 
				values('{$r[rg_id]}', 1, {$r[qty_export]}, '{$r[shd_export]}', '{$r[siz_export]}', '{$r[cbr_export]}');";
			foreach ($r[defect_kode_ekonomi] as $i => $value) {
				if($r[defect_kode_ekonomi][$i] <> '' && $r[per_2h_ekonomi][$i] <> ''){
					$k2sql .= "INSERT into qc_fg_rg_detail(rg_id, rg_qly, rg_per_2h, rg_shading, rg_size, rg_calibro, rg_defect_kode) 
						values('{$r[rg_id]}', 2, {$r[per_2h_ekonomi][$i]}, '{$r[shd_ekonomi]}', '{$r[siz_ekonomi]}', '{$r[cbr_ekonomi]}', '{$r[defect_kode_ekonomi][$i]}');";
				}
			}
			foreach ($r[defect_kode_rijsor] as $i => $value) {
				if($r[defect_kode_rijsor][$i] <> '' && $r[per_2h_rijsor][$i] <> ''){
					$k2sql .= "INSERT into qc_fg_rg_detail(rg_id, rg_qly, rg_per_2h, rg_shading, rg_size, rg_calibro, rg_defect_kode) 
						values('{$r[rg_id]}', 4, {$r[per_2h_rijsor][$i]}, '{$r[shd_rijsor]}', '{$r[siz_rijsor]}', '{$r[cbr_rijsor]}', '{$r[defect_kode_rijsor][$i]}');";
				}
			}
			foreach ($r[defect_kode_rijpal] as $i => $value) {
				if($r[defect_kode_rijpal][$i] <> '' && $r[per_2h_rijpal][$i] <> ''){
					$k2sql .= "INSERT into qc_fg_rg_detail(rg_id, rg_qly, rg_per_2h, rg_shading, rg_size, rg_calibro, rg_defect_kode) 
						values('{$r[rg_id]}', 5, {$r[per_2h_rijpal][$i]}, '{$r[shd_rijpal]}', '{$r[siz_rijpal]}', '{$r[cbr_rijpal]}', '{$r[defect_kode_rijpal][$i]}');";
				}
			}
			foreach ($r[defect_kode_rijbua] as $i => $value) {
				if($r[defect_kode_rijbua][$i] <> '' && $r[per_2h_rijbua][$i] <> ''){
					$k2sql .= "INSERT into qc_fg_rg_detail(rg_id, rg_qly, rg_per_2h, rg_shading, rg_size, rg_calibro, rg_defect_kode) 
						values('{$r[rg_id]}', 6, {$r[per_2h_rijbua][$i]}, '{$r[shd_rijbua]}', '{$r[siz_rijbua]}', '{$r[cbr_rijbua]}', '{$r[defect_kode_rijbua][$i]}');";
				}
			}
			$out = dbsave_plan($app_plan_id, $k2sql);
		} else {
			$out = $xsql;
		}
	}
	echo $out;
}

function hapus(){
	global $app_plan_id;
	$rg_id = $_POST['kode'];

	$sql = "UPDATE qc_fg_rg_header SET rg_status = 'C' WHERE rg_id = '{$rg_id}';";
	echo dbsave_plan($app_plan_id, $sql); 
}


function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant();
	$out .= $withselect ? "</select>" : "";
	echo $out;
}

function cbosize($sizeVal = ""){
	global $app_plan_id;

	$sql = "SELECT rg_size FROM (
				SELECT 'S' AS rg_size, 1 AS ordering
				UNION
				SELECT 'N' AS rg_size, 2 AS ordering
				UNION
				SELECT 'X' AS rg_size, 3 AS ordering
				UNION
				SELECT 'L' AS rg_size, 4 AS ordering
				UNION
				SELECT 'XL' AS rg_size, 5 AS ordering
				UNION
				SELECT 'LL' AS rg_size, 6 AS ordering
			) as vs ORDER BY ordering ASC";
 
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[rg_size] == $sizeVal) {
				$out .= "<option value='$r[rg_size]' selected>$r[rg_size]</option>";
			} else {
				$out .= "<option value='$r[rg_size]'>$r[rg_size]</option>";
			}	
		}	
	}
	return $out;
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

function detailtabel($stat) {
	global $app_plan_id, $app_subplan;
	if($stat == "edit" || $stat == "view") {
		$rg_id = $_POST['kode'];
		$sql0 = "SELECT * FROM qc_fg_rg_header WHERE rg_id = '{$rg_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['rg_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$rhead['time'] = substr($datetime[1],0,5);
		$sql = "SELECT * from qc_fg_rg_detail WHERE rg_id = '{$rg_id}' order by rg_qly, rg_defect_kode";
		$qry = dbselect_plan_all($app_plan_id, $sql);
		foreach($qry as $r){
			$arr_qty["$r[rg_qly]"] += $r[rg_per_2h];
			$arr_shd["$r[rg_qly]"] = $r[rg_shading];
			$arr_siz["$r[rg_qly]"] = $r[rg_size];
			$arr_cbr["$r[rg_qly]"] = $r[rg_calibro];
			$arr_defect["$r[rg_qly]"]["$r[rg_defect_kode]"] = $r[rg_per_2h];
		}
	}

	$out .= '<table id="tblitem" class="table table-bordered table-condensed table-hover">';
	$out .= '<tr>';		
		$out .= '<th style="vertical-align:middle;">QLY</th>';		
		$out .= '<th style="vertical-align:middle;">QTY</th>';		
		$out .= '<th style="vertical-align:middle;">SHADING</th>';		
		$out .= '<th style="vertical-align:middle;">SIZE</th>';	
		$out .= '<th style="vertical-align:middle;">CALIBRO</th>';	
	$out .= '</tr>';
	$out .= '<tr>';		
		$out .= '<td class="text-left"><b>Export</b></td>';
		$out .= '<td><input class="form-control input-sm text-right" type="text" name="qty_export" id="qty_export" onkeyup="hanyanumerik(this.id,this.value);" value="'.$arr_qty['1'].'"></td>';
		$out .= '<td><input class="form-control input-sm text-right" type="text" name="shd_export" id="shd_export" onkeyup="hanyanumerik(this.id,this.value);" value="'.$arr_shd['1'].'"></td>';
		$out .= '<td><select class="form-control input-sm" name="siz_export" id="siz_export">'.cbosize($arr_siz['1']).'</select></td>';
		$out .= '<td><input class="form-control input-sm text-right" type="text" name="cbr_export" id="cbr_export" onkeyup="hanyanumerik(this.id,this.value);" value="'.$arr_cbr['1'].'"></td>';
	$out .= '</tr>';
	$out .= '<tr>';		
		$out .= '<td class="text-left"><b>Ekonomi</b>';
		if($stat == "edit" || $stat == "add") {
			$out .= '&nbsp;&nbsp;<button type="button" class="btn btn-success btn-xs" onClick="tambahItem(\'ekonomi\')">+</button>';
		}
		$out .= '</td><td><input class="form-control input-sm text-right" type="text" name="qty_ekonomi" id="qty_ekonomi" readonly value="'.$arr_qty['2'].'"></td>';
		$out .= '<td><input class="form-control input-sm text-right" type="text" name="shd_ekonomi" id="shd_ekonomi" onkeyup="hanyanumerik(this.id,this.value);" value="'.$arr_shd['2'].'"></td>';
		$out .= '<td><select class="form-control input-sm" name="siz_ekonomi" id="siz_ekonomi">'.cbosize($arr_siz['2']).'</select></td>';
		$out .= '<td><input class="form-control input-sm text-right" type="text" name="cbr_ekonomi" id="cbr_ekonomi" onkeyup="hanyanumerik(this.id,this.value);" value="'.$arr_cbr['2'].'"></td>';
	$out .= '</tr>';
	$jml_ekonomi = 0;
	if(is_array($arr_defect['2'])) {
		foreach ($arr_defect['2'] as $defect_kode => $nilai) {
			$out .= '<tr>';		
			$out .= '<td>&nbsp;</td>';
			$out .= '<td><select class="form-control input-sm" id="defect_kode_ekonomi_'.$jml_ekonomi.'" name="defect_kode_ekonomi['.$jml_ekonomi.']">'.cbodefect($defect_kode,true).'</select></td>';
			$out .= '<td><input class="form-control input-sm text-right" type="text" id="per_2h_ekonomi_'.$jml_ekonomi.'" name="per_2h_ekonomi['.$jml_ekonomi.']" onkeyup="hanyanumerik(this.id,this.value);hitungTotal(\'ekonomi\');" placeholder="QTY" value="'.$nilai.'"></td>';
			$out .= '</tr>';
			$jml_ekonomi++;
		}
	}
	$out .= '<tr>';		
		$out .= '<td class="text-left"><b>Reject Sortir</b>';
		if($stat == "edit" || $stat == "add") {
			$out .= '&nbsp;&nbsp;<button type="button" class="btn btn-success btn-xs" onClick="tambahItem(\'rijsor\')">+</button>';
		}
		$out .= '</td><td><input class="form-control input-sm text-right" type="text" name="qty_rijsor" id="qty_rijsor" readonly value="'.$arr_qty['4'].'"></td>';
		$out .= '<td><input class="form-control input-sm text-right" type="text" name="shd_rijsor" id="shd_rijsor" onkeyup="hanyanumerik(this.id,this.value);" value="'.$arr_shd['4'].'"></td>';
		$out .= '<td><select class="form-control input-sm" name="siz_rijsor" id="siz_rijsor">'.cbosize($arr_siz['4']).'</select></td>';
		$out .= '<td><input class="form-control input-sm text-right" type="text" name="cbr_rijsor" id="cbr_rijsor" onkeyup="hanyanumerik(this.id,this.value);" value="'.$arr_cbr['4'].'"></td>';
	$out .= '</tr>';
	$jml_rijsor = 0;
	if(is_array($arr_defect['4'])) {
		foreach ($arr_defect['4'] as $defect_kode => $nilai) {
			$out .= '<tr>';		
			$out .= '<td>&nbsp;</td>';
			$out .= '<td><select class="form-control input-sm" id="defect_kode_rijsor_'.$jml_rijsor.'" name="defect_kode_rijsor['.$jml_rijsor.']">'.cbodefect($defect_kode,true).'</select></td>';
			$out .= '<td><input class="form-control input-sm text-right" type="text" id="per_2h_rijsor_'.$jml_rijsor.'" name="per_2h_rijsor['.$jml_rijsor.']" onkeyup="hanyanumerik(this.id,this.value);hitungTotal(\'rijsor\');" placeholder="QTY" value="'.$nilai.'"></td>';
			$out .= '</tr>';
			$jml_rijsor++;
		}
	}
	$out .= '<tr>';		
		$out .= '<td class="text-left"><b>Reject Pallet</b>';
		if($stat == "edit" || $stat == "add") {
			$out .= '&nbsp;&nbsp;<button type="button" class="btn btn-success btn-xs" onClick="tambahItem(\'rijpal\')">+</button>';
		}
		$out .= '</td><td><input class="form-control input-sm text-right" type="text" name="qty_rijpal" id="qty_rijpal" readonly value="'.$arr_qty['5'].'"></td>';
		$out .= '<td><input class="form-control input-sm text-right" type="text" name="shd_rijpal" id="shd_rijpal" onkeyup="hanyanumerik(this.id,this.value);" value="'.$arr_shd['5'].'"></td>';
		$out .= '<td><select class="form-control input-sm" name="siz_rijpal" id="siz_rijpal">'.cbosize($arr_siz['5']).'</select></td>';
		$out .= '<td><input class="form-control input-sm text-right" type="text" name="cbr_rijpal" id="cbr_rijpal" onkeyup="hanyanumerik(this.id,this.value);" value="'.$arr_cbr['5'].'"></td>';
	$out .= '</tr>';
	$jml_rijpal = 0;
	if(is_array($arr_defect['5'])) {
		foreach ($arr_defect['5'] as $defect_kode => $nilai) {
			$out .= '<tr>';		
			$out .= '<td>&nbsp;</td>';
			$out .= '<td><select class="form-control input-sm" id="defect_kode_rijpal_'.$jml_rijpal.'" name="defect_kode_rijpal['.$jml_rijpal.']">'.cbodefect($defect_kode,true).'</select></td>';
			$out .= '<td><input class="form-control input-sm text-right" type="text" id="per_2h_rijpal_'.$jml_rijpal.'" name="per_2h_rijpal['.$jml_rijpal.']" onkeyup="hanyanumerik(this.id,this.value);hitungTotal(\'rijpal\');" placeholder="QTY" value="'.$nilai.'"></td>';
			$out .= '</tr>';
			$jml_rijpal++;
		}
	}
	$out .= '<tr>';		
		$out .= '<td class="text-left"><b>Reject Buang</b>';
		if($stat == "edit" || $stat == "add") {
			$out .= '&nbsp;&nbsp;<button type="button" class="btn btn-success btn-xs" onClick="tambahItem(\'rijbua\')">+</button>';
		}
		$out .= '</td><td><input class="form-control input-sm text-right" type="text" name="qty_rijbua" id="qty_rijbua" readonly value="'.$arr_qty['6'].'"></td>';
		$out .= '<td><input class="form-control input-sm text-right" type="text" name="shd_rijbua" id="shd_rijbua" onkeyup="hanyanumerik(this.id,this.value);" value="'.$arr_shd['6'].'"></td>';
		$out .= '<td><select class="form-control input-sm" name="siz_rijbua" id="siz_rijbua">'.cbosize($arr_siz['6']).'</select></td>';
		$out .= '<td><input class="form-control input-sm text-right" type="text" name="cbr_rijbua" id="cbr_rijbua" onkeyup="hanyanumerik(this.id,this.value);" value="'.$arr_cbr['6'].'"></td>';
	$out .= '</tr>';
	$jml_rijbua = 0;
	if(is_array($arr_defect['6'])) {
		foreach ($arr_defect['6'] as $defect_kode => $nilai) {
			$out .= '<tr>';		
			$out .= '<td>&nbsp;</td>';
			$out .= '<td><select class="form-control input-sm" id="defect_kode_rijbua_'.$jml_rijbua.'" name="defect_kode_rijbua['.$jml_rijbua.']">'.cbodefect($defect_kode,true).'</select></td>';
			$out .= '<td><input class="form-control input-sm text-right" type="text" id="per_2h_rijbua_'.$jml_rijbua.'" name="per_2h_rijbua['.$jml_rijbua.']" onkeyup="hanyanumerik(this.id,this.value);hitungTotal(\'rijbua\');" placeholder="QTY" value="'.$nilai.'"></td>';
			$out .= '</tr>';
			$jml_rijbua++;
		}
	}
	if($stat == "edit" || $stat == "add") {
		$out .= '<tr>
		    <td colspan="5" class="text-center"><button type="button" class="btn btn-primary btn-sm" onClick="simpanData(\''.$stat.'\')">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button>
		    <input type="hidden" id="jml_ekonomi" value="'.$jml_ekonomi.'">
		    <input type="hidden" id="jml_rijsor" value="'.$jml_rijsor.'">
		    <input type="hidden" id="jml_rijpal" value="'.$jml_rijpal.'">
		    <input type="hidden" id="jml_rijbua" value="'.$jml_rijbua.'"></td>
		    </tr>';
	} else {
		$out .= '<tr>
		    <td colspan="5" class="text-center"><button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button></td>
		    </tr>';
	}
	$out .= '</table>';

    if($stat == "edit" || $stat == "view") {
    	$responce->rg_id = $rhead[rg_id];
    	$responce->rg_sub_plant = $rhead[rg_sub_plant];
    	$responce->rg_date = $rhead[date];
    	$responce->rg_time = $rhead[time];
    	$responce->rg_line = $rhead[rg_line];
    	$responce->rg_motif = $rhead[rg_motif];
    }
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>