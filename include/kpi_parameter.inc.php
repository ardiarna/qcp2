<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['126'];
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
	case "getdata":
		getdata($_POST['stat']);
		break;
	case "formdata":
		formdata($_POST['stat'],$_POST['iddept'],$_POST['iddivisi'],$_POST['id']);
		break;
	case "simpanform":
		simpanform();
		break;
	case "detailtabel":
		detailtabel($_POST['iddept'],$_POST['iddivisi']);
		break;
	case "hapus2":
		hapus2();
		break;
}

function simpanform(){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	if($r[aksidata] == "add"){
		$sql = "SELECT max(kpi_id) as kpi_id_max from qc_kpi_parameter";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[kpi_id_max] == ''){
			$mx[kpi_id_max] = 0;
		} else {
			$mx[kpi_id_max] = $mx[kpi_id_max];
		}
		$r[kpi_id] = $mx[kpi_id_max]+1;


		$sql2 = "INSERT INTO qc_kpi_parameter(kpi_id, kpi_parent, kpi_dept, kpi_divisi, kpi_cat, kpi_desc, kpi_bobot, kpi_satuan, kpi_target, kpi_periode)
				 VALUES ('{$r[kpi_id]}', '{$r[kpi_parent]}', '{$r[kpi_dept]}', '{$r[kpi_divisi]}', '{$r[kpi_cat]}', '{$r[kpi_desc]}', '{$r[kpi_bobot]}', '{$r[kpi_satuan]}', '{$r[kpi_target]}', '{$r[kpi_periode]}');";
		$out = dbsave_plan($app_plan_id, $sql2); 
	}else{
		$sql2 = "UPDATE qc_kpi_parameter SET kpi_cat = '{$r[kpi_cat]}', kpi_desc = '{$r[kpi_desc]}', kpi_bobot = '{$r[kpi_bobot]}', kpi_satuan = '{$r[kpi_satuan]}', 
					    kpi_target = '{$r[kpi_target]}', kpi_periode = '{$r[kpi_periode]}' WHERE kpi_id = '{$r[kpi_id]}' ";
		$out = dbsave_plan($app_plan_id, $sql2); 
	}
	echo $out;
}

function hapus2(){
	global $app_plan_id;
	$id = $_POST['id'];
	$sql = "DELETE FROM qc_kpi_parameter WHERE kpi_parent = '{$id}';DELETE FROM qc_kpi_parameter WHERE kpi_id = '{$id}';";
	echo dbsave_plan($app_plan_id, $sql); 
}


function combocat($nilai){
	$qry = array('' => '', '1' => 'QUALITY', '2' => 'QUANTITY');

	$out = "";
	foreach ($qry as $key => $value) {
		if($key == $nilai){
			$out .= '<option value="'.$key.'" Selected>'.$value.'</option>';
		}else{
			$out .= '<option value="'.$key.'">'.$value.'</option>';
		}
	}
	return $out;
}

function formdata($stat,$kpi_dept,$kpi_divisi,$kpi_id) {
	global $app_plan_id, $app_subplan;

	$style_a = '';
	$style_b = 'style="display:none;"';

	$kpi_parent = $kpi_id;

	if($stat == "edit"){
		$sqld = "SELECT * FROM qc_kpi_parameter WHERE kpi_id = '$kpi_id'";
		$qryd = dbselect_plan($app_plan_id, $sqld);
		$kpi_parent = $qryd['kpi_parent'];
		$style_a = 'style="display:none;"';
		$style_b = '';
	}

	if($stat == "add"){
		$sqld2 = "SELECT kpi_cat FROM qc_kpi_parameter WHERE kpi_id = '$kpi_id'";
		$qryd2 = dbselect_plan($app_plan_id, $sqld2);
		$qryd[kpi_cat] = $qryd2['kpi_cat'];

		if($kpi_parent == '0'){
			$style_a = '';
			$style_b = 'style="display:none;"';
		}else{
			$style_a = 'style="display:none;"';
			$style_b = '';
		}

		$qryd[kpi_periode] = 'Daily';
	}



	$out  = '<form class="form-horizontal" id="frm1">
				<input type="hidden" name="aksidata" id="aksidata" value="'.$stat.'" readonly>
				<input type="hidden" name="kpi_dept" id="kpi_dept" value="'.$kpi_dept.'" readonly>
				<input type="hidden" name="kpi_divisi" id="kpi_divisi" value="'.$kpi_divisi.'" readonly>
				<input type="hidden" name="kpi_parent" id="kpi_parent" value="'.$kpi_parent.'" readonly>
				<input type="hidden" name="kpi_id" id="kpi_id" value="'.$kpi_id.'" readonly>';
	$out .= '<table class="table table-bordered table-condensed table-striped" id="tabeldetail">';

	$judul = "FORM PARAMETER";
	$out .= '<tr>
    			<th style="vertical-align:middle;text-align:left;">CATEGORY</th>
    			<td>
    				<select '.$style_a.' class="form-control input-sm" name="kpi_cat" id="kpi_cat">'.combocat($qryd[kpi_cat]).'</select>
    				<select '.$style_b.' class="form-control input-sm" name="kpi_cat2" id="kpi_cat2" disabled>'.combocat($qryd[kpi_cat]).'</select>
    			</td>
			</tr>';
	$out .= '<tr>
    			<th style="vertical-align:middle;text-align:left;">DESCIPTION</th>
    			<td>
    				<input type="text" class="form-control input-sm" name="kpi_desc" id="kpi_desc" value="'.$qryd[kpi_desc].'">
    			</td>
			</tr>';

	$out .= '<tr>
    			<th style="vertical-align:middle;text-align:left;">BOBOT</th>
    			<td>
    				<input type="text" class="form-control input-sm" name="kpi_bobot" id="kpi_bobot" value="'.$qryd[kpi_bobot].'">
    			</td>
			</tr>';
	$out .= '<tr>
    			<th style="vertical-align:middle;text-align:left;">SATUAN</th>
    			<td>
    				<input type="text" class="form-control input-sm" name="kpi_satuan" id="kpi_satuan" value="'.$qryd[kpi_satuan].'">
    			</td>
			</tr>';
	$out .= '<tr>
    			<th style="vertical-align:middle;text-align:left;">TARGET</th>
    			<td>
    				<input type="text" class="form-control input-sm" name="kpi_target" id="kpi_target" value="'.$qryd[kpi_target].'">
    			</td>
			</tr>';
	$out .= '<tr>
    			<th style="vertical-align:middle;text-align:left;">PERIODE / WAKTU</th>
    			<td>
    				<input type="text" class="form-control input-sm" name="kpi_periode" id="kpi_periode" value="'.$qryd[kpi_periode].'">
    			</td>
			</tr>';
    $out .= '</table>';
    $out .= '</form>';

	$responce->titleform = $judul.' | '.strtoupper($stat);
	$responce->isiform   = $out;
	echo json_encode($responce);

}

function getdata($stat) {
	global $app_plan_id, $app_subplan;
	if($stat == "edit" || $stat == "view") {
		$idh = $_POST['kode'];
		$sql0 = "SELECT * from qc_kpi_header WHERE idh = '{$idh}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);

    	$responce->idh = $rhead[idh];
    	$responce->tahun = $rhead[tahun];
    	$responce->departemen = $rhead[departemen];
    	$responce->divisi = $rhead[divisi];
    
		echo json_encode($responce);
    }
}

function urai(){
	global $app_plan_id, $akses, $app_subplan;
	$page = $_POST['page']; 
	$rows = $_POST['rows']; 
	$sidx = $_POST['sidx']; 
	$sord = $_POST['sord'];
	$whsatu = dptKondisiWhere($_POST['_search'],$_POST['filters'],$_POST['searchField'],$_POST['searchOper'],$_POST['searchString']);
	$whdua = "";

	if($_POST['idh']) {
		$whdua .= " and lower(idh) like lower('%".$_POST['idh']."%')";
	}
	if($_POST['tahun']) {
		$whdua .= " and lower(tahun) like lower('%".$_POST['tahun']."%')";
	}
	if($_POST['departemen']) {
		$whdua .= " and lower(departemen) like lower('%".$_POST['departemen']."%')";
	}
	if($_POST['divisi']) {
		$whdua .= " and lower(divisi) like lower('%".$_POST['divisi']."%')";
	}
	
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count FROM qc_kpi_dept a LEFT JOIN qc_kpi_divisi b ON a.iddept = b.iddept where true $whsatu $whdua";
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
		$sql = "SELECT a.iddept, a.nmdept, b.iddivisi, b.nmdivisi FROM qc_kpi_dept a LEFT JOIN qc_kpi_divisi b ON a.iddept = b.iddept 
				where true $whsatu $whdua order by $sidx $sord $limit";
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
			$btnView = '<button class="btn btn-default btn-xs" title="Detail" onClick="detailData(\''.$ro['iddept'].'\',\''.$ro['iddivisi'].'\')"><span class="glyphicon glyphicon-list-alt"></span></button> ';

			$ro['kontrol'] = $btnView;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['nmdept'],$ro['nmdivisi'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;

	$sWid = "";
	if($stat == 'edit'){
		$sWid = "AND idh <> '{$r[idh]}'";
	}
	
	$sql_dup = "SELECT idh from qc_kpi_header where tahun = '{$r[tahun]}' AND departemen = '{$r[departemen]}' AND divisi = '{$r[divisi]}' $sWid";
	$d_dup   = dbselect_plan($app_plan_id, $sql_dup);


	if($d_dup['idh'] == '') {
		if($stat == "add") {
			$sql = "SELECT max(idh) as idh_max from qc_kpi_header";
			$mx = dbselect_plan($app_plan_id, $sql);
			if($mx[idh_max] == ''){
				$mx[idh_max] = 0;
			} else {
				$mx[idh_max] = $mx[idh_max];
			}
			$r[idh] = $mx[idh_max]+1;
			
			$sql = "INSERT into qc_kpi_header(idh, tahun, departemen, divisi) values('{$r[idh]}', '{$r[tahun]}', '{$r[departemen]}', '{$r[divisi]}');";
			$xsql = dbsave_plan($app_plan_id, $sql); 
			$out = $xsql;

		} else if($stat=='edit') {
			$sql = "UPDATE qc_kpi_header set tahun = '{$r[tahun]}', departemen = '{$r[departemen]}', divisi = '{$r[divisi]}' where idh = '{$r[idh]}';";
			$xsql = dbsave_plan($app_plan_id, $sql); 
			$out = $xsql;
		}
		echo $out;
	}else{
		echo "Data sudah ada, ID : ".$d_dup['idh'] ;
	}
}

function hapus(){
	global $app_plan_id;
	$kode = $_POST['kode'];
	$sql = "DELETE FROM qc_kpi_header WHERE idh = '{$kode}';";
	echo dbsave_plan($app_plan_id, $sql); 
}


function detailtabel($iddept,$iddivisi) {
	global $app_plan_id, $app_subplan;
	$sql = "SELECT a.iddept, a.nmdept, b.iddivisi, b.nmdivisi FROM qc_kpi_dept a LEFT JOIN qc_kpi_divisi b ON a.iddept = b.iddept WHERE a.iddept = '$iddept' AND b.iddivisi = '$iddivisi' ";
	$qry = dbselect_plan($app_plan_id, $sql);

	
	
	$out .= '<h3 align="center">'.strtoupper($qry['nmdept']).' - '.strtoupper($qry['nmdivisi']).'</h3></th>';

	$out .= '<button type="button" class="btn btn-warning btn-sm" onClick="formAwal()"><span class="fa fa-reply"></span></button>
			 <button type="button" class="btn btn-default btn-sm" onClick="FormData(\'add\',\''.$iddept.'\',\''.$iddivisi.'\',\'0\')"><span class="glyphicon glyphicon-plus"></span></button>
			 <br>
			 <br>';

	$out .= '<table class="table table-bordered table-hover table-condensed">';
	$out .= '<tr>
				<th>NO</th>
				<th>CATEGORY</th>
				<th colspan="2">DESKRIPSI</th>
				<th>BOBOT (%)</th>
				<th>SATUAN PENGUKURAN</th>
				<th>TARGET</th>
				<th>PERIODE / WAKTU</th>
				<th>AKSI</th>
			  </tr>';

	$sql2 = "SELECT distinct kpi_cat FROM qc_kpi_parameter WHERE kpi_dept = '$iddept' AND kpi_divisi = '$iddivisi' ORDER BY kpi_cat ASC";
	$qry2 = dbselect_plan_all($app_plan_id, $sql2);
	if(is_array($qry2)){
		$no = '1';
		foreach($qry2 as $r2){
			if($r2['kpi_cat'] == 1){
				$catval = 'QUALITY';
			}else{
				$catval = 'QUANTITY';
			}


			$out .= '<tr>
						<th>'.$no.'</th>
						<th>'.$catval.'</th>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					  </tr>';
			$out .= show_parameter($iddept,$iddivisi,$r2['kpi_cat'],0);
		$no++;
		}
	}	
	$out .= '</table>';

	$responce->detailtabel = $out; 
	echo json_encode($responce);
}



function show_parameter($iddept,$iddiv,$cat,$parent){
	global $app_plan_id,$akses;
	$sql = "SELECT * FROM qc_kpi_parameter WHERE kpi_dept = '$iddept' AND kpi_divisi = '$iddiv' AND kpi_parent = '$parent' AND kpi_cat = '$cat' ORDER BY kpi_id ASC";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out = '';
	if(is_array($qry)){
		foreach($qry as $r){
			$arr_nilai["$r[kpi_id]"] = $r[kpi_desc].'@@'.$r[kpi_bobot].'@@'.$r[kpi_satuan].'@@'.$r[kpi_target].'@@'.$r[kpi_periode]; 
		}
	}

	if(is_array($arr_nilai)){
		$no = 'A';
		foreach ($arr_nilai as $id => $value) {
			$sql_count = "SELECT count(*) AS jml FROM qc_kpi_parameter WHERE kpi_dept = '$iddept' AND kpi_divisi = '$iddiv' AND kpi_parent = '$id'";
			$qry_count = dbselect_plan($app_plan_id, $sql_count);
			$jml_sub   = $qry_count['jml'];

			$val = explode('@@', $value);
			
			$btn_tambah = $akses[add] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="FormData(\'add\',\''.$iddept.'\',\''.$iddiv.'\',\''.$id.'\')"><span class="glyphicon glyphicon-plus"></span></button> ' : '';

			$btn_edit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="FormData(\'edit\',\''.$iddept.'\',\''.$iddiv.'\',\''.$id.'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btn_hapus = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData2(\''.$iddept.'\',\''.$iddiv.'\',\''.$id.'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';

			if($parent == 0){

				$out .= '<tr>
							<td>'.$id.'</td>
							<td>&nbsp;</td>
							<th>'.$no.'</th>
							<th style="text-align:left;">'.$val[0].'</th>
							<td style="text-align:center;">'.$val[1].'</td>
							<td style="text-align:center;">'.$val[2].'</td>
							<td style="text-align:center;">'.$val[3].'</td>
							<td style="text-align:center;">'.$val[4].'</td>
							<td style="text-align:center;">'.$btn_tambah.$btn_edit.$btn_hapus.'</td>
						<tr>';
			 $no++;
			}else{
				$out .= '<tr>
							<td>'.$id.'</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>- '.$val[0].'</th>
							<td style="text-align:center;">'.$val[1].'</td>
							<td style="text-align:center;">'.$val[2].'</td>
							<td style="text-align:center;">'.$val[3].'</td>
							<td style="text-align:center;">'.$val[4].'</td>
							<td style="text-align:center;">'.$btn_edit.$btn_hapus.'</td>
						<tr>';
			}

			if($jml_sub > 0){
				$out .= show_parameter($iddept,$iddiv,$cat,$id);
			}
		}
	}else{
		$out .= '<tr><td colspan="9">&nbsp;</td></tr>';
	}

	return $out;
}

?>