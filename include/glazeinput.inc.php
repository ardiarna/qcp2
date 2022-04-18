<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['22'];
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
	case "cbokodeglaze":
		cbokodeglaze($_POST['kategori']);
		break;
	case "cboballmill":
		cboballmill($_POST['subplan']);
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
		$whdua .= " and qgh_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and qgh_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['qgh_id']) {
		$whdua .= " and qgh_id = '".$_POST['qgh_id']."'";
	}
	if($_POST['qgh_sub_plant']) {
		$whdua .= " and qgh_sub_plant = '".$_POST['qgh_sub_plant']."'";
	}
	if($_POST['qgh_date']) {
		$whdua .= " and qgh_date = '".$_POST['qgh_date']."'";
	}
	if($_POST['qgh_shift']) {
		$whdua .= " and qgh_shift = ".$_POST['qgh_shift']."";
	}
	if($_POST['qgh_category']) {
		$whdua .= " and qgh_category = '".$_POST['qgh_category']."'";
	}
	if($_POST['qgh_bmg_no']) {
		$whdua .= " and qgh_bmg_no = '".$_POST['qgh_bmg_no']."'";
	}
	if($_POST['qgh_glaze_code']) {
		$whdua .= " and lower(qgh_glaze_code) like '%".strtolower($_POST['qgh_glaze_code'])."%'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_gp_header where qgh_rec_stat='N' and qgh_date >= '{$tglfrom}' and qgh_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT * from qc_gp_header 
			where qgh_rec_stat='N'  and qgh_date >= '{$tglfrom}' and qgh_date <= '{$tglto}' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['qgh_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qgh_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qgh_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['qgh_date']);
			$ro['date'] = $datetime[0];
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qgh_id'],$ro['qgh_sub_plant'],$ro['date'],$ro['qgh_shift'],$ro['qgh_category'],$ro['qgh_bmg_no'],$ro['qgh_glaze_code'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function suburai(){
	global $app_plan_id;
	$qgh_id = $_POST['qgh_id'];
	$sql = "SELECT qc_gp_detail.*, qggm_desc, qgdm_control_desc, qgu_code from qc_gp_detail join qc_gp_group_master on(qc_gp_detail.qgd_prep_group=qc_gp_group_master.qggm_group) join qc_gp_detail_master on(qc_gp_detail.qgd_prep_group=qc_gp_detail_master.qgdm_group and qc_gp_detail.qgd_prep_seq=qc_gp_detail_master.qgdm_seq) left join qc_gen_um on(qc_gp_detail_master.qgdm_um_id=qc_gen_um.qgu_id) where qgh_id = '{$qgh_id}' order by qgd_prep_seq";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	foreach($qry as $ro){
		$responce->rows[$i]['cell']=array($ro['qggm_desc'],$ro['qgd_prep_seq'],$ro['qgdm_control_desc'],$ro['qgd_standard_id'],$ro['qgu_code'],$ro['qgd_prep_value'],$ro['qgd_prep_remark']);
		$i++;
	}		
	echo json_encode($responce);	
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[qgh_date] = cgx_dmy2ymd($r[qgh_date]);
	$r[qgh_shift] = cgx_angka($r[qgh_shift]);
	if($stat == "add") {
		$r[qgd_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[qgd_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(qgh_id) as qgh_id_max from qc_gp_header where qgh_sub_plant = '{$r[qgh_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[qgh_id_max] == ''){
			$mx[qgh_id_max] = 0;
		} else {
			$mx[qgh_id_max] = substr($mx[qgh_id_max],-7);
		}
		$urutbaru = $mx[qgh_id_max]+1;
		$r[qgh_id] = $app_plan_id.$r[qgh_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		$sql = "INSERT into qc_gp_header(qgh_sub_plant, qgh_id, qgh_date, qgh_shift, qgh_category, qgh_bmg_no, qgh_glaze_code, qgh_rec_stat, qgd_user_create, qgd_date_create) values('{$r[qgh_sub_plant]}', '{$r[qgh_id]}', '{$r[qgh_date]}', {$r[qgh_shift]}, '{$r[qgh_category]}', '{$r[qgh_bmg_no]}', '{$r[qgh_glaze_code]}', 'N', '{$r[qgd_user_create]}', '{$r[qgd_date_create]}');";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k2sql = "";
			foreach ($r[qgd_prep_seq] as $i => $value) {
				$r[qgd_prep_seq][$i] = cgx_angka($r[qgd_prep_seq][$i]);
				$r[qgd_standard_id][$i] = cgx_angka($r[qgd_standard_id][$i]);
				$k2sql .= "INSERT into qc_gp_detail(qgh_id, qgd_prep_group, qgd_prep_seq, qgd_standard_id, qgd_prep_remark, qgd_prep_value) values('{$r[qgh_id]}', '{$r[qgd_prep_group][$i]}', {$r[qgd_prep_seq][$i]}, {$r[qgd_standard_id][$i]}, '{$r[qgd_prep_remark][$i]}', '{$r[qgd_prep_value][$i]}');";
			}
			$out = dbsave_plan($app_plan_id, $k2sql);
		} else {
			$out = $xsql;
		}
	} else if($stat=='edit') {
		$r[qgd_user_mofify] = $_SESSION[$app_id]['user']['user_name'];
		$r[qgd_date_modify] = date("Y-m-d H:i:s");
		$sql = "UPDATE qc_gp_header set qgh_category = '{$r[qgh_category]}', qgh_bmg_no = '{$r[qgh_bmg_no]}', qgh_glaze_code = '{$r[qgh_glaze_code]}', qgd_user_mofify = '{$r[qgd_user_mofify]}', qgd_date_modify = '{$r[qgd_date_modify]}' where qgh_id = '{$r[qgh_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_gp_detail where qgh_id = '{$r[qgh_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			if($x1sql == "OK") {
				$k2sql = "";
				foreach ($r[qgd_prep_seq] as $i => $value) {
					$r[qgd_prep_seq][$i] = cgx_angka($r[qgd_prep_seq][$i]);
					$r[qgd_standard_id][$i] = cgx_angka($r[qgd_standard_id][$i]);
					$k2sql .= "INSERT into qc_gp_detail(qgh_id, qgd_prep_group, qgd_prep_seq, qgd_standard_id, qgd_prep_remark, qgd_prep_value) values('{$r[qgh_id]}', '{$r[qgd_prep_group][$i]}', {$r[qgd_prep_seq][$i]}, {$r[qgd_standard_id][$i]}, '{$r[qgd_prep_remark][$i]}', '{$r[qgd_prep_value][$i]}');";
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
	$qgh_id = $_POST['kode'];
	$sql = "UPDATE qc_gp_header set qgh_rec_stat = 'C' where qgh_id = '{$qgh_id}';";
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

function cbokodeglaze($kategori, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$jenis_formula = $kategori == "1" ? "engobe" : "glaze";
	$sql = "SELECT distinct komposisi_kode as kode_formula from tbl_komposisi_produksi where plan_kode='{$app_plan_id}' and jenis='{$jenis_formula}' order by komposisi_kode";
	$qry = dbselect_all($sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if ($r[kode_formula] == $nilai) {
				$out .= "<option selected>$r[kode_formula]</option>";
			} else {
				$out .= "<option>$r[kode_formula]</option>";
			}	
		}	
	}

	if($isret){
		return $out;
	} else {
		echo $out;
	}
}

// function cboballmill($subplan, $nilai = "TIDAKADA", $isret = false){
// 	global $app_plan_id;
// 	$sql = "SELECT qgb_code, qgb_desc from qc_gp_bmg where qgb_sub_plant = '{$subplan}'";
// 	$qry = dbselect_plan_all($app_plan_id, $sql);
// 	$out .= "<option></option>";
// 	if(is_array($qry)) {
// 		foreach($qry as $r){
// 			if($r[qgb_code] == $nilai) {
// 				$out .= "<option value='$r[qgb_code]' selected>$r[qgb_code]</option>";
// 			} else {
// 				$out .= "<option value='$r[qgb_code]'>$r[qgb_code]</option>";
// 			}	
// 		}	
// 	}
	
// 	if($isret){
// 		return $out;
// 	} else {
// 		echo $out;
// 	}
// }

function cboballmill($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qbm_kode from qc_bm_unit where qbm_plant_code = '{$subplan}'";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[qbm_kode] == $nilai){
				$out .= "<option selected>$r[qbm_kode]</option>";
			} else {
				$out .= "<option>$r[qbm_kode]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;	
	}
}

function cbooknotok($nilai = "TIDAKADA", $isret = false){
	$variable = array('OK' => 'OK', 'Not OK' => 'Not OK');
	$out .= "<option></option>";
	foreach ($variable as $key => $value) {
		if($key == $nilai){
			$out .= "<option value='$key' selected>$value</option>";
		} else {
			$out .= "<option value='$key'>$value</option>";
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
		$qgh_id = $_POST['kode'];
		$sql0 = "SELECT * from qc_gp_header where qgh_id = '{$qgh_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$sql = "SELECT distinct qgd_prep_group, qggm_desc from qc_gp_detail join qc_gp_group_master on(qc_gp_detail.qgd_prep_group=qc_gp_group_master.qggm_group) where qgh_id = '{$qgh_id}' order by qgd_prep_group";
	} else {
		$sql = "SELECT qggm_group as qgd_prep_group, qggm_desc from qc_gp_group_master order by qggm_group";
	}
	$k = 0;
	$i = 0;
	$out = '<table class="table table-bordered table-condensed table-striped">
		<tr>
    	<th width="50">NO</th>    
    	<th>DESKRIPSI</th>
    	<th width="70">STANDAR</th>
    	<th width="70">UNIT</th>
    	<th width="90">NILAI</th>
    	<th>REMARK</th>
    	</tr>';
	$qry = dbselect_plan_all($app_plan_id, $sql);

	foreach($qry as $r) {
		$out .= '<tr><td colspan="6" style="padding-left:55px;"><span onClick="hideGrup('.$k.')"><strong>'.$r[qggm_desc].'</strong></span></td></tr>';
    	if($stat == "edit" || $stat == "view") {
    		$sql2 = "SELECT qc_gp_detail.*, qgdm_control_desc, qgu_code from qc_gp_detail join qc_gp_detail_master on(qc_gp_detail.qgd_prep_group=qc_gp_detail_master.qgdm_group and qc_gp_detail.qgd_prep_seq=qc_gp_detail_master.qgdm_seq) left join qc_gen_um on(qc_gp_detail_master.qgdm_um_id=qc_gen_um.qgu_id) where qgh_id = '{$qgh_id}' and qgd_prep_group = '{$r[qgd_prep_group]}' order by qgd_prep_seq";
    	} else {
    		$sql2 = "SELECT qgdm_group as qgd_prep_group, qgdm_seq as qgd_prep_seq, qgdm_control_desc, qgu_code from qc_gp_detail_master left join qc_gen_um on(qc_gp_detail_master.qgdm_um_id=qc_gen_um.qgu_id) where qgdm_group = '{$r[qgd_prep_group]}' order by qgdm_seq";		
    	}
        $qry2 = dbselect_plan_all($app_plan_id, $sql2);
		foreach($qry2 as $r2) {
			$out .= '<tr id="trdet_ke_'.$i.'" class="trgrup_ke_'.$k.'">
	        	<td class="text-center"><input type="hidden" name="qgd_prep_group['.$i.']" value="'.$r2[qgd_prep_group].'"><input type="hidden" name="qgd_prep_seq['.$i.']" id="qgd_prep_seq_'.$i.'" value="'.$r2[qgd_prep_seq].'">'.$r2[qgd_prep_seq].'</td>
	        	<td>'.$r2[qgdm_control_desc].'</td>
	        	<td></td>
	        	<td>'.$r2[qgu_code].'</td>';
	        if($r2[qgd_prep_group] == "01" && ($r2[qgd_prep_seq] == 5 || $r2[qgd_prep_seq] == 6)) {
	        	$out .= '<td><select class="form-control input-sm" name="qgd_prep_value['.$i.']" id="qgd_prep_value_'.$i.'">'.cbooknotok($r2[qgd_prep_value],true).'</select></td>';
	        } else {
	        	$out .= '<td><input class="form-control input-sm" type="text" name="qgd_prep_value['.$i.']" id="qgd_prep_value_'.$i.'" value="'.$r2[qgd_prep_value].'"></td>';
	        }
	        $out .= '<td><input class="form-control input-sm" type="text" name="qgd_prep_remark['.$i.']" id="qgd_prep_remark_'.$i.'" value="'.$r2[qgd_prep_remark].'"></td>
	        	</tr>';
			$i++;
		}
        $k++;
	}
	if($stat == "edit" || $stat == "add") {
		$out .= '<tr>
		    <td colspan="6" class="text-center"><button type="button" class="btn btn-primary btn-sm" onClick="simpanData(\''.$stat.'\')">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button></td>
		    </tr>';
		} else {
			$out .= '<tr>
		    <td colspan="6" class="text-center"><button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button></td>
		    </tr>';
		}
	$out .= '</table>';

	if($stat == "edit" || $stat == "view") {
		$datetime = explode(' ',$rhead['qgh_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$responce->qgh_id = $rhead[qgh_id];
		$responce->qgh_date = $rhead[date];
		$responce->qgh_shift =  cbo_shift($rhead[qgh_shift]);
		$responce->qgh_sub_plant = $rhead[qgh_sub_plant];
		$responce->qgh_category = $rhead[qgh_category];
		$responce->qgh_bmg_no = cboballmill($rhead[qgh_sub_plant],$rhead[qgh_bmg_no],true);
		$responce->qgh_glaze_code = cbokodeglaze($rhead[qgh_category],$rhead[qgh_glaze_code],true);	
	}
    $responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>