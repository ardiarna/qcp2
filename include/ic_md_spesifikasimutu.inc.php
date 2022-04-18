
<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['122'];
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
	case "simpan":
		simpan();
		break;
	case "listcopy":
		listcopy($_POST['kode']);
		break;
	case "savecopy":
		savecopy();
		break;
	case "cbosatuan":
		cbosatuan($_POST['kode']);
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

	
	
	$whsatu = dptKondisiWhere($_POST['_search'],$_POST['filters'],$_POST['searchField'],$_POST['searchOper'],$_POST['searchString']);
	$whdua = "";

	if($_POST['item_kode']) {
		$whdua .= " and item_kode ilike '%".$_POST['item_kode']."%'";
	}

	if($_POST['item_nama']) {
		$whdua .= " and item_nama ilike '%".$_POST['item_nama']."%'";
	}
	

	$wsimiliar = "AND item_kode similar to '(00.|01.|04.)%' ";

	if(!$sidx) $sidx = 1;

	$sql = "SELECT count(*) as count FROM item WHERE 1=1 $wsimiliar $whsatu $whdua";
	$r = dbselect_plan($app_plan_id,$sql);
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
		$sql = "SELECT distinct item_kode, item_nama FROM item WHERE 1=1 $wsimiliar $whsatu $whdua ORDER BY item_nama";
		$qry = dbselect_plan_all($app_plan_id,$sql);
		$i = 0;
	} else { 
		$total_pages = 1; 
	}

	if($page > $total_pages) $page = $total_pages; 
	$responce->page = $page; 
	$responce->total = $total_pages; 
	$responce->records = $count; 
	$responce->sql = $sql; 
	if($count > 0) {
		foreach($qry as $ro){
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="DetilData(\''.$ro['item_kode'].'\',\''.$ro['item_nama'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$ro['kontrol'] = $btnView;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['item_kode'],$ro['item_nama'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function listcopy($kode){
	global $app_plan_id, $akses, $app_subplan;

	$sql = "SELECT distinct ic_kd_material, ic_nm_material 
			FROM qc_ic_spesifikasimutu 
			WHERE ic_kd_material <> '{$kode}'
			ORDER BY ic_nm_material";
	$qry = dbselect_plan_all($app_plan_id, $sql);

	$out  = '<option></option>';
	if($qry){
		foreach($qry as $ro){

			$ro[ic_nm_material] = strtoupper($ro[ic_nm_material]);
			$out .= '<option value="'.$ro[ic_kd_material].'">'.$ro[ic_nm_material].'</option>';
			
		}
	}

	$responce->list = $out;
	$responce->nama = $nama;
	$responce->sql = $sql;

	echo json_encode($responce);

}

function savecopy(){
	global $app_plan_id,$app_id;

	$cp_kode_from  	= $_POST['cp_kode_from'];
	$cp_kode_to 	= $_POST['cp_kode_to'];
	$cp_name_to 	= $_POST['cp_name_to'];

	$ic_user = $_SESSION[$app_id]['user']['user_name'];
	$ic_date = date("Y-m-d H:i:s");



	$sql = "SELECT * FROM qc_ic_spesifikasimutu WHERE ic_kd_material = '{$cp_kode_from}' AND ic_status = 'N'";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	

	$sqlins = '';
	foreach($qry as $r){
		$ic_kd_material = $cp_kode_to;
		$ic_nm_material = $cp_name_to;
		$ic_kd_group 	= $r[ic_kd_group];
		$ic_nm_group 	= $r[ic_nm_group];
		$ic_nmdesc 		= $r[ic_nmdesc];
		$ic_std 		= $r[ic_std];
		$ic_satuan 		= $r[ic_satuan];
		$ic_urut 		= $r[ic_urut];


		$sqlins .= "INSERT INTO qc_ic_spesifikasimutu(
						ic_kd_material, ic_nm_material, ic_kd_group, ic_nm_group, ic_nmdesc, ic_std, ic_urut, ic_satuan, ic_status, ic_user_create, ic_date_create) 
					VALUES ('{$ic_kd_material}', '{$ic_nm_material}', '{$ic_kd_group}','{$ic_nm_group}','{$ic_nmdesc}','{$ic_std}','{$ic_urut}', '{$ic_satuan}', 'N', '{$ic_user}','{$ic_date}');";
		
	}
	
	if(!empty($sqlins)){

		//delete data lama
		$sql_del = "UPDATE qc_ic_spesifikasimutu ic_status = 'C' WHERE ic_kd_material = '{$cp_kode_to}'";
		dbsave_plan($app_plan_id, $sql_del);

		$hsl = dbsave_plan($app_plan_id, $sqlins);
		if($hsl == 'OK'){
			$out = "Data Berhasil diimport!";
		}else{
			$out = $hsl;
		}
	}else{
		$out = "Data Spesifikasi Mutu tidaka ada!";
	}

	echo $out;
}

function simpan(){
	global $app_plan_id,$app_id;
	$r = $_REQUEST;
	$ic_kd_material = $_POST['ic_kd_material'];
	$ic_nm_material = $_POST['ic_nm_material'];
	


	$ic_user = $_SESSION[$app_id]['user']['user_name'];
	$ic_date = date("Y-m-d H:i:s");
	
	
	$sql = "DELETE FROM qc_ic_spesifikasimutu WHERE ic_kd_material = '{$ic_kd_material}';";


	if(is_array($r[ic_kd_group])){
		foreach ($r[ic_kd_group] as $i => $group) {
			$sql .= "INSERT INTO qc_ic_spesifikasimutu(ic_kd_material, ic_nm_material, ic_kd_group, ic_kd_seq, ic_std, ic_status, ic_user_create, ic_date_create) VALUES ('{$ic_kd_material}', '{$ic_nm_material}', '{$group}','{$r[ic_kd_seq][$i]}','{$r[ic_std][$i]}','{$r[ic_status][$i]}','{$ic_user}','{$ic_date}');";
		}
	}
	$hsl = dbsave_plan($app_plan_id, $sql);
	if($hsl == 'OK'){
		$out = "Data Berhasil disimpan";
	}else{
		$out = $hsl;
	}

	echo $out;
}

function detailtabel() {

	global $app_plan_id, $akses, $app_subplan;
	$kode = $_POST['kode'];

	$sql = "SELECT * FROM qc_ic_parameter WHERE pm_status = 'N' ORDER BY pm_groupid, pm_urut ASC";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if($qry){
		foreach ($qry as $r) {
			$arr_pm["$r[pm_groupid]"]["$r[pm_seq]"] = $r[pm_desc].'@@'.$r[pm_sat].'@@'.$r[pm_std];
			$arr_gname["$r[pm_groupid]"] = strtoupper($r[pm_groupname]); 
		}
	}


	$sql_isi = "SELECT * FROM qc_ic_spesifikasimutu WHERE ic_kd_material = '{$kode}' ORDER BY ic_kd_group, ic_kd_seq ASC";
	$qry_isi = dbselect_plan_all($app_plan_id, $sql_isi);
	if($qry_isi){
		foreach ($qry_isi as $r2) {
			$arr_isi["$r2[ic_kd_group]"]["$r2[ic_kd_seq]"] = $r2[ic_std].'@@'.$r2[ic_status];
		}
	}

	$out .= '<table class="table table-bordered table-condensed table-hover">';
	
	$out .= '<tr>
		        	<th style="width:100px;min-width:100px;background-color:#00c0ef;">NO</th>
		        	<th class="text-left;" style="background-color:#00c0ef;">DESCRIPTION</th>
		        	<th style="width:100px;min-width:100px;background-color:#00c0ef;">SATUAN</th>
		        	<th style="width:400px;min-width:400px;background-color:#00c0ef;">STANDARD</th>
		        	<th style="width:100px;min-width:100px;background-color:#00c0ef;">AKTIF</th>
	        	 </tr>';

	if(is_array($arr_pm)){
		$ngrp = 'A';
		$i = 0;
		foreach ($arr_pm as $gid => $a_seq) {
			$out .= '<tr>
			        	<th class="text-center">'.$ngrp.'</th>
			        	<th class="text-left" colspan="4">'.$arr_gname[$gid].'</th>
		        	 </tr>';
		    $no = 0;
			foreach ($a_seq as $seq => $a_val) {
				$val = explode('@@', $a_val);

				if(is_array($arr_isi)){
					$val2 = explode('@@', $arr_isi[$gid][$seq]);

					$valstd = $val2[0];
					$aktif  = $val2[1];
				}else{
					$valstd = '';
					$aktif  = 'Y';
				}
					
				$out .= '<tr>
			        	<td class="text-right">'.++$no.'</td>
			        	<td class="text-left">'.$val[0].'</td>
			        	<td class="text-center">'.$val[1].'</td>
			        	<td class="text-center">
			        	<input class="form-control input-sm" type="hidden" name="ic_kd_group['.$i.']" id="ic_kd_group_'.$i.'" value="'.$gid.'">
			        	<input class="form-control input-sm" type="hidden" name="ic_kd_seq['.$i.']" id="ic_kd_seq_'.$i.'" value="'.$seq.'">
			        	<input class="form-control input-sm" type="text" name="ic_std['.$i.']" id="ic_std_'.$i.'" value="'.$valstd.'">
			        	</td>
			        	<td class="text-center">
			        		<select class="form-control" name="ic_status['.$i.']" id="ic_status_'.$i.'">'.cboaktif($aktif).'</select>
			        	</td>
		        	 </tr>';
				$i++;
			}
			$ngrp++;
		}

		$out .= '<tr>
		        	<th class="text-center" colspan="5">
		        		<a href="javascript:void(0)" class="btn btn-primary" onclick="simpanData()">
		        			<i class="fa fa-save"></i> Simpan
		        		</a>
		        	</th>
	        	 </tr>';
	}else{
		$out .= '<tr>
		        	<td class="text-center">Belum ada parameter...</td>
	        	 </tr>';
	}
	

	$out .= '</table>';

	$responce->detailtabel = $out; 
	echo json_encode($responce);
}


function cboaktif($val = 'Y'){
	$out = '';
	$qry = array('Y','N');

	foreach ($qry as $key) {
		$selected =  $key == $val ? 'selected' : '';
		$out .= '<option value="'.$key.'" '.$selected.'>'.$key.'</option>';
	}
	return $out;
}
?>