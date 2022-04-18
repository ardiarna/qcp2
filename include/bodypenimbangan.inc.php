<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['18'];
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
	case "copy":
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
	case "cbokodebody":
		cbokodebody($_POST['subplan']);
		break;
	case "cboballmill":
		cboballmill($_POST['subplan']);
		break;
	case "txtkapasitas":
		txtkapasitas();
		break;
	case "detailtabel":
		detailtabel($_POST['stat']);
		break;
	case "additem":
		additem();
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
		$whdua .= " and qbh_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and qbh_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['qbh_id']) {
		$whdua .= " and qbh_id = '".$_POST['qbh_id']."'";
	}
	if($_POST['qbh_sub_plant']) {
		$whdua .= " and qbh_sub_plant = '".$_POST['qbh_sub_plant']."'";
	}
	if($_POST['qbh_date']) {
		$whdua .= " and qbh_date = '".$_POST['qbh_date']."'";
	}
	if($_POST['qbh_shift']) {
		$whdua .= " and qbh_shift = ".$_POST['qbh_shift']."";
	}
	if($_POST['qbh_body_code']) {
		$whdua .= " and lower(qbh_body_code) like '%".strtolower($_POST['qbh_body_code'])."%'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_bm_header where qbh_rec_status='N' and qbh_date >= '{$tglfrom}' and qbh_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT * from qc_bm_header 
			where qbh_rec_status='N' and qbh_date >= '{$tglfrom}' and qbh_date <= '{$tglto}' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['qbh_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnCopy = $akses[add] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="copyData(\''.$ro['qbh_id'].'\')"><span class="glyphicon glyphicon-plus"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qbh_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qbh_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnCopy.$btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qbh_id'],$ro['qbh_sub_plant'],$ro['qbh_date'],$ro['qbh_shift'],$ro['qbh_body_code'],$ro['qbh_bm_no'],$ro['qbh_volume'],$ro['qbh_kode_pbd'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function suburai(){
	global $app_plan_id;
	$qbh_id = $_POST['qbh_id'];
	$sql = "SELECT * from qc_bm_detail where qbh_id = '{$qbh_id}' order by qbd_material_type";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	foreach($qry as $ro){
		$ro['qbd_material_type'] = $ro['qbd_material_type'] == "1" ? "MATERIAL" : "ADDITIVE";
		$responce->rows[$i]['cell']=array($ro['qbd_material_type'],$ro['qbd_material_code'],$ro['qbd_material_name'],$ro['qbd_box_unit'],$ro['qbd_formula'],$ro['qbd_dw'],$ro['qbd_mc'],$ro['qbd_ww'],$ro['qbd_value'],$ro['qbd_remark']);
		$i++;
	}		
	echo json_encode($responce);	
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[qbh_date] = cgx_dmy2ymd($r[qbh_date]);
	$r[qbh_shift] = cgx_angka($r[qbh_shift]);
	$r[qbh_volume] = cgx_angka($r[qbh_volume]);
	if($stat == "add") {
		$r[qbh_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[qbh_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(qbh_id) as qbh_id_max from qc_bm_header where qbh_sub_plant = '{$r[qbh_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[qbh_id_max] == ''){
			$mx[qbh_id_max] = 0;
		} else {
			$mx[qbh_id_max] = substr($mx[qbh_id_max],-7);
		}
		$urutbaru = $mx[qbh_id_max]+1;
		$r[qbh_id] = $app_plan_id.$r[qbh_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		$sql = "INSERT into qc_bm_header(qbh_sub_plant, qbh_id, qbh_date, qbh_shift, qbh_bm_no, qbh_body_code, qbh_volume, qbh_user_create, qbh_date_create,qbh_rec_status) values('{$r[qbh_sub_plant]}', '{$r[qbh_id]}', '{$r[qbh_date]}', {$r[qbh_shift]}, '{$r[qbh_bm_no]}', '{$r[qbh_body_code]}', {$r[qbh_volume]}, '{$r[qbh_user_create]}', '{$r[qbh_date_create]}', 'N');";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k2sql = "";
			foreach ($r[qbd_material_code] as $i => $value) {
				$r[qbd_formula][$i] = cgx_angka($r[qbd_formula][$i]);
				$r[qbd_dw][$i] = cgx_angka($r[qbd_dw][$i]);
				$r[qbd_mc][$i] = cgx_angka($r[qbd_mc][$i]);
				$r[qbd_ww][$i] = cgx_angka($r[qbd_ww][$i]);
				$r[qbd_value][$i] = cgx_angka($r[qbd_value][$i]);
				$k2sql .= "INSERT into qc_bm_detail(qbh_id, qbd_box_unit, qbd_material_code, qbd_material_type, qbd_formula, qbd_dw, qbd_mc, qbd_ww, qbd_remark, qbd_value) values('{$r[qbh_id]}', '{$r[qbd_box_unit][$i]}', '{$r[qbd_material_code][$i]}', '{$r[qbd_material_type][$i]}', {$r[qbd_formula][$i]}, {$r[qbd_dw][$i]}, {$r[qbd_mc][$i]}, {$r[qbd_ww][$i]}, '{$r[qbd_remark][$i]}', {$r[qbd_value][$i]});";
			}
			$out = dbsave_plan($app_plan_id, $k2sql);
		} else {
			$out = $xsql;
		}
	} else if($stat=='edit') {
		$r[qbh_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[qbh_date_modify] = date("Y-m-d H:i:s");
		$sql = "UPDATE qc_bm_header set qbh_bm_no = '{$r[qbh_bm_no]}', qbh_volume = {$r[qbh_volume]}, qbh_user_modify = '{$r[qbh_user_modify]}', qbh_date_modify = '{$r[qbh_date_modify]}' where qbh_id = '{$r[qbh_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_bm_detail where qbh_id = '{$r[qbh_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			if($x1sql == "OK") {
				$k2sql = "";
				foreach ($r[qbd_material_code] as $i => $value) {
					$r[qbd_formula][$i] = cgx_angka($r[qbd_formula][$i]);
					$r[qbd_dw][$i] = cgx_angka($r[qbd_dw][$i]);
					$r[qbd_mc][$i] = cgx_angka($r[qbd_mc][$i]);
					$r[qbd_ww][$i] = cgx_angka($r[qbd_ww][$i]);
					$r[qbd_value][$i] = cgx_angka($r[qbd_value][$i]);
					$k2sql .= "INSERT into qc_bm_detail(qbh_id, qbd_box_unit, qbd_material_code, qbd_material_type, qbd_formula, qbd_dw, qbd_mc, qbd_ww, qbd_remark, qbd_value) values('{$r[qbh_id]}', '{$r[qbd_box_unit][$i]}', '{$r[qbd_material_code][$i]}', '{$r[qbd_material_type][$i]}', {$r[qbd_formula][$i]}, {$r[qbd_dw][$i]}, {$r[qbd_mc][$i]}, {$r[qbd_ww][$i]}, '{$r[qbd_remark][$i]}', {$r[qbd_value][$i]});";
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
	$qbh_id = $_POST['kode'];
	$sql = "UPDATE qc_bm_header set qbh_rec_status='C' where qbh_id = '{$qbh_id}';";
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

function cbokodebody($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$subplan = substr($subplan,-1);
	$sql = "SELECT komposisi_kode from tbl_komposisi_produksi where jenis='body' and plan_kode='{$app_plan_id}' and sub_plan = '{$subplan}' order by komposisi_kode";
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

function cboballmill($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qbm_kode from qc_bm_unit where qbm_plant_code = '{$subplan}' order by qbm_kode";
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

function txtkapasitas(){
	global $app_plan_id;
	$subplan = $_POST['subplan'];
	$kode = $_POST['kode'];
	$sql = "SELECT qbm_capacity from qc_bm_unit where qbm_plant_code = '{$subplan}' and qbm_kode = '{$kode}'";
	$r = dbselect_plan($app_plan_id, $sql);
	echo $r['qbm_capacity'];
}

function cbobox($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qbu_kode from qc_box_unit where qbu_sub_plant = '{$subplan}' order by qbu_kode";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[qbu_kode] == $nilai) {
				$out .= "<option selected>$r[qbu_kode]</option>";
			} else {
				$out .= "<option>$r[qbu_kode]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;	
	}
}

function cboitem($tipe, $isret = false){
	global $app_plan_id;
	if($tipe == "1") {
		$sql = "SELECT distinct item_kode,item_nama from qry_detail_komposisi where kelompok not in ('ADDITIVE') order by item_nama";
	} else {
		$sql = "SELECT distinct item_kode,item_nama from qry_detail_komposisi where kelompok in ('ADDITIVE') order by item_nama";	
	}
	$qry = dbselect_all($sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			$out .= "<option value='$r[item_kode]'>$r[item_nama]</option>";	
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
	if($stat == "edit" || $stat == "view" || $stat == "copy") { 
		$qbh_id = $_POST['kode'];
		$sql = "SELECT * from qc_bm_header where qbh_id = '{$qbh_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql);
	} else {
		$komposisi_kode = $_POST['kode'];;
		$volume = $_POST['volume'];
		$grand = dbselect("SELECT sum(formula) as formula from qry_detail_komposisi where komposisi_kode like '%{$komposisi_kode}' and plan_kode='{$app_plan_id}' and kelompok not in ('ADDITIVE')");
		$rhead[qbh_sub_plant] = $_POST['subplan'];
	}
	$jmlMaterial = 0;
	$jmlAdditive = 0;
	$i = 0;
	$out = '<table id="tabeldetail" class="table table-bordered table-condensed table-hover">
		<tr>
        <th colspan="2" rowspan="2" style="vertical-align:middle;">NAMA ITEM</th>
        <th rowspan="2" style="vertical-align:middle;">NO. BOX</th>
        <th colspan="4" style="vertical-align:middle;">TARGET</th>
        <th rowspan="2" width="110" style="vertical-align:middle;">NILAI</th>
        <th rowspan="2" style="vertical-align:middle;">REMARK</th>
        </tr><tr>
        <th width="70" style="vertical-align:middle;">FORMULA (%)</th>
        <th width="110" style="vertical-align:middle;">DW (kg)</th>
        <th width="70" style="vertical-align:middle;">M.C (%)</th>
        <th width="110" style="vertical-align:middle;">WW (kg)</th></tr>';
    $variable = array('MATERIAL' => 1, 'ADDITIVE' => 2);
    foreach ($variable as $key => $value) {
    	if($value == '1') {
    		$readonlyfr = 'readonly';
    		$readonlymc = '';
    		$readonlydw = 'readonly';
    		$readonlyww = 'readonly';
    	} else {
    		$readonlyfr = '';
    		$readonlymc = '';
    		$readonlydw = '';
    		$readonlyww = '';
    	}
    	$out .= '<tr>
		    <td colspan="9" style="padding-left:55px;"><strong>'.$key.'</strong>&nbsp;&nbsp;<button type="button" class="btn btn-success btn-xs" onClick="tambahItem('.$value.')">Tambah Item</button></td>
			</tr>';
		if($stat == "edit" || $stat == "view" || $stat == "copy") {
			$sql = "SELECT qc_bm_detail.*, item.item_nama as qbd_material_name from qc_bm_detail left join item on qc_bm_detail.qbd_material_code=item.item_kode where qbh_id = '{$qbh_id}' and qbd_material_type = '{$value}'";
			$qry = dbselect_plan_all($app_plan_id, $sql);
		} else {
			$where = $key == "MATERIAL" ? "and kelompok not in ('ADDITIVE')" : "and kelompok in ('ADDITIVE')";
			$sql = "SELECT item_kode as qbd_material_code, item_nama as qbd_material_name, formula as qbd_formula, mc as qbd_mc  from qry_detail_komposisi where komposisi_kode like '%{$komposisi_kode}' and plan_kode='{$app_plan_id}' $where order by item_kode asc";
			$qry = dbselect_all($sql);	
		}
		foreach($qry as $r) {
			if($stat == "add") {
				$r['qbd_dw'] = ($r['qbd_formula']/$grand['formula'])*$volume;
				// $r['qbd_ww'] = $r['qbd_dw']/((100-$r['qbd_mc'])/100);
				$r['qbd_ww'] = ($r['qbd_formula']/($grand['formula']-$r['qbd_mc']))*$volume;
			}
			$r['qbd_value'] = $r['qbd_value'] == 0 ? "" : $r['qbd_value'];
			if($stat == "copy") { 
				$r['qbd_value'] = "";
			}
			$cbobox = $key == "MATERIAL" ? '<select class="form-control input-sm" name="qbd_box_unit['.$i.']" id="qbd_box_unit_'.$i.'">'.cbobox($rhead[qbh_sub_plant],$r['qbd_box_unit'],true).'</select>' : '';
			//<span class="glyphicon glyphicon-remove" onClick="hapusItem('.$value.','.$i.')"></span>
			$out .= '<tr id="trdet_ke_'.$i.'">
	            <td width="20"></td>
	            <td><input type="hidden" name="qbd_material_type['.$i.']" value="'.$value.'"><input type="hidden" name="qbd_material_code['.$i.']" id="qbd_material_code_'.$i.'" value="'.$r['qbd_material_code'].'"><input class="form-control input-sm" type="text" name="qbd_material_name['.$i.']" id="qbd_material_name_'.$i.'" value="'.$r['qbd_material_name'].'"></td>
	            <td>'.$cbobox.'</td>
	            <td><input class="form-control input-sm text-right" type="text" name="qbd_formula['.$i.']" id="qbd_formula_'.$value.'_'.$i.'" value="'.$r['qbd_formula'].'" onkeyup="hitungDwWw();" '.$readonlyfr.'></td>
	            <td><input class="form-control input-sm text-right" type="text" name="qbd_dw['.$i.']" id="qbd_dw_'.$i.'" value="'.round($r['qbd_dw']).'" '.$readonlydw.'></td>
	            <td><input class="form-control input-sm text-right" type="text" name="qbd_mc['.$i.']" id="qbd_mc_'.$i.'" value="'.$r['qbd_mc'].'" onkeyup="hanyanumerik(this.id,this.value);hitungDwWw();" '.$readonlymc.'></td>
	            <td><input class="form-control input-sm text-right" type="text" name="qbd_ww['.$i.']" id="qbd_ww_'.$i.'" value="'.round($r['qbd_ww']).'" '.$readonlyww.'></td>
	            <td><input class="form-control input-sm text-right" type="text" name="qbd_value['.$i.']" id="qbd_value_'.$i.'" value="'.$r['qbd_value'].'" onkeyup="hanyanumerik(this.id,this.value);"></td>
	            <td><input class="form-control input-sm" type="text" name="qbd_remark['.$i.']" id="qbd_remark_'.$i.'" value="'.$r['qbd_remark'].'"></td>
	        	</tr>';
	        $jml["$key"] = $jml["$key"] + 1;
	        $i++;
		}
    }
    if($stat == "edit" || $stat == "add" || $stat == "copy") {
		$out .= '<tr>
		    <td colspan="9" class="text-center"><input type="hidden" id="barisLast" value="'.$i.'"><input type="hidden" id="jmlMaterial" value="'.$jml["MATERIAL"].'"><input type="hidden" id="jmlAdditive" value="'.$jml["ADDITIVE"].'"><button type="button" class="btn btn-primary btn-sm" onClick="simpanData(\''.$stat.'\')">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button></td>
		    </tr>';
	} else {
		$out .= '<tr>
		    <td colspan="9" class="text-center"><input type="hidden" id="barisLast" value="'.$i.'"><input type="hidden" id="jmlMaterial" value="'.$jml["MATERIAL"].'"><input type="hidden" id="jmlAdditive" value="'.$jml["ADDITIVE"].'"><button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button></td>
		    </tr>';
	}
	$out .= '</table>';
    
    if($stat == "edit" || $stat == "view" || $stat == "copy") {
    	$rhead[qbh_bm_no] = $stat == "copy" ? "" : $rhead[qbh_bm_no];
    	$responce->qbh_id = $rhead[qbh_id];
	    $responce->qbh_date = cgx_dmy2ymd($rhead[qbh_date]);
	    $responce->qbh_shift = cbo_shift($rhead[qbh_shift]);
	    $responce->qbh_sub_plant = $rhead[qbh_sub_plant];
	    $responce->qbh_bm_no = cboballmill($rhead[qbh_sub_plant],$rhead[qbh_bm_no],true);
	    $responce->qbh_body_code = cbokodebody($rhead[qbh_sub_plant],$rhead[qbh_body_code],true);
	    $responce->qbh_volume = $rhead[qbh_volume];    
    }
    $responce->detailtabel = $out; 
	echo json_encode($responce);
}

function additem() {
	$subplan = $_POST['subplan'];
	$tipe = $_POST['tipe'];
	$responce->qbd_material_code = cboitem($tipe,true);
	$responce->qbd_box_unit = cbobox($subplan,"TIDAKADA",true);
	echo json_encode($responce);
}

?>