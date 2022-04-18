<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['116'];
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
	case "pilihMaterial":
		pilihMaterial();
		break;
	case "cbosubplant":
		cbosubplant($_GET['withselect']);
		break;
	case "cbobox":
		cbobox($_POST['subplan'],'TIDAKADA',true);
		break;
	case "detailtabel":
		detailtabel($_POST['stat']);
		break;
}



function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[ic_date] = cgx_dmy2ymd($r[ic_date]);

	if($stat == "add") {
		$sWid = "";
	}else{
		$sWid = "AND ic_id <> '{$r[ic_id]}' ";
	}
 
	$sql_count = "SELECT ic_id FROM qc_ic_mb_header WHERE ic_idmasuk = '{$r[ic_idmasuk]}' AND ic_rec_stat = 'N' $sWid ";
	$mx_count = dbselect_plan($app_plan_id, $sql_count);

	if($mx_count[ic_id] <> ''){
		echo 'Data Sudah ada, ID : '.$mx_count[ic_id];
	}else{
		if($stat == "add") {
			$r[ic_user_create] = $_SESSION[$app_id]['user']['user_name'];
			$r[ic_date_create] = date("Y-m-d H:i:s");
			$sql = "SELECT max(ic_id) as ic_id_max FROM qc_ic_mb_header WHERE ic_sub_plant = '{$r[ic_sub_plant]}'";
			$mx = dbselect_plan($app_plan_id, $sql);
			if($mx[ic_id_max] == ''){
				$mx[ic_id_max] = 0;
			} else {
				$mx[ic_id_max] = substr($mx[ic_id_max],-7);
			}
			$urutbaru = $mx[ic_id_max]+1;
			$r[ic_id] = $app_plan_id.$r[ic_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);

			if(!empty($r[icd_group])){

				$sql = "INSERT INTO qc_ic_mb_header(
							ic_sub_plant, ic_id, ic_date, ic_idmasuk, ic_rec_stat, ic_user_create, ic_date_create
						) VALUES (
							'{$r[ic_sub_plant]}', '{$r[ic_id]}', '{$r[ic_date]}', '{$r[ic_idmasuk]}', 'N', '{$r[ic_user_create]}', '{$r[ic_date_create]}');";
				$xsql = dbsave_plan($app_plan_id, $sql); 
				
				if($xsql == "OK") {
					
					$sql_h = "UPDATE qc_ic_kebasahan_data SET ic_idmb = '{$r[ic_id]}' WHERE ic_id = '{$r[ic_idmasuk]}';";
					dbsave_plan($app_plan_id, $sql_h);

					$k2sql = "";
					foreach ($r[icd_group] as $i => $group) {
						$k2sql .= " INSERT INTO qc_ic_mb_detail(ic_id, icd_group, icd_seq, icd_value) 
										VALUES ( '{$r[ic_id]}', '{$group}', '{$r[icd_seq][$i]}', '{$r[icd_value][$i]}');";
					}

					if(!empty($k2sql)){
						$out = dbsave_plan($app_plan_id, $k2sql);
					}else{
						$out = $xsql;
					}
				} else {
					$out = $xsql;
				}
			}else{
				$out = 'Parameter harus di isi!';
			}
		} else if($stat=='edit') {


			if(!empty($r[icd_group])){

				$r[ic_user_modify] = $_SESSION[$app_id]['user']['user_name'];
				$r[ic_date_modify] = date("Y-m-d H:i:s");
				$sql = "UPDATE qc_ic_mb_header SET ic_idmasuk = '{$r[ic_idmasuk]}', ic_user_modify = '{$r[ic_user_modify]}', ic_date_modify = '{$r[ic_date_modify]}' WHERE ic_id = '{$r[ic_id]}';";
				$xsql = dbsave_plan($app_plan_id, $sql); 
				if($xsql == "OK") {
					$k1sql = "DELETE FROM qc_ic_mb_detail WHERE ic_id = '{$r[ic_id]}';";
					$x1sql = dbsave_plan($app_plan_id, $k1sql);
					if($x1sql == "OK") {
						$k2sql = "";
						foreach ($r[icd_group] as $i => $group) {
							$k2sql .= " INSERT INTO qc_ic_mb_detail(ic_id, icd_group, icd_seq, icd_value) 
											VALUES ( '{$r[ic_id]}', '{$group}', '{$r[icd_seq][$i]}', '{$r[icd_value][$i]}');";
						}

						if(!empty($k2sql)){
							$out = dbsave_plan($app_plan_id, $k2sql);
						}else{
							$out = $x1sql;
						}
					} else {
						$out = $x1sql;
					}
				} else {
					$out = $xsql;
				}
			}else{
				$out = 'Parameter harus di isi!';
			}
		}
		echo $out;
	}
}


function pilihMaterial() {
	global $app_plan_id;
	if($_POST['txt_cari']) {
		$txt_cari = strtoupper($_POST['txt_cari']);
		$whsatu = "AND (
						ic_id ilike '%{$txt_cari}%' OR
						ic_nm_material ilike '%{$txt_cari}%' OR
						ic_kadar_air ilike '%{$txt_cari}%'
				   )";
	}

	$wsimiliar = '';
	// $wsimiliar = "AND ic_kd_material similar to '(00.001|00.003)%' ";


	$sql = "SELECT * FROM qc_ic_kebasahan_data 
			WHERE ic_rec_stat = 'N' AND ic_hasil = 'Y' $wsimiliar
			AND COALESCE(ic_idmb, '') = '' AND COALESCE(ic_idmk, '') = '' $whsatu
			";
	$qry = dbselect_plan_all($app_plan_id, $sql);

	$out = '<table class="table table-bordered table-striped table-condensed">
				<tbody>
					<tr>
						<th>#</th>
						<th>ID MASUK</th>
						<th>TGL MASUK</th>
						<th>KODE MATERIAL</th>
						<th>NAMA MATERIAL</th>
					</tr>';

	if(is_array($qry)) {
		foreach($qry as $r) {
			$r[ic_date] = date('d-m-Y', strtotime($r[ic_date]));

			$out .= '<tr>
						<td class="text-center">
							<span class="glyphicon glyphicon-ok" onClick="setMaterial(\''.$r[ic_id].'\');"></span>
						</td>
						<td class="text-center">'.$r[ic_id].'</td>
						<td class="text-center">'.$r[ic_date].'</td>
						<td class="text-center">'.$r[ic_kd_material].'</td>
						<td class="text-left">'.$r[ic_nm_material].'</td>
					</tr>';
		}
	} else {
		$out .= '<tr><td colspan="5">Pencarian : '.$txt_cari.' tidak ditemukan...</td></tr>';
	}	

	$out .= '</tbody></table>';

	$responce->out = $out;
	echo json_encode($responce);
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
		$whdua .= " and a.ic_sub_plant like '%".$app_subplan."%'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and a.ic_sub_plant like '%".$app_subplan."%'";
	}
	if($_POST['ic_id']) {
		$whdua .= " and a.ic_id like '%".$_POST['ic_id']."%'";
	}
	
	if($_POST['ic_sub_plant']) {
		$whdua .= " and a.ic_sub_plant like '%".$_POST['ic_sub_plant']."%'";
	}

	if($_POST['ic_date']) {
		$whdua .= " and ic_date = '".$_POST['ic_date']."'";
	}

	if($_POST['ic_nm_material']) {
		$whdua .= " AND b.ic_nm_material ilike '%".$_POST['ic_nm_material']."%'";
	}

	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count 
			FROM qc_ic_mb_header a LEFT JOIN qc_ic_kebasahan_data b ON a.ic_idmasuk = b.ic_id 
			WHERE a.ic_rec_stat='N' AND a.ic_date >= '{$tglfrom}' AND a.ic_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT a.*, b.ic_nm_material 
				FROM qc_ic_mb_header a LEFT JOIN qc_ic_kebasahan_data b ON a.ic_idmasuk = b.ic_id 
				WHERE a.ic_rec_stat='N'  AND a.ic_date >= '{$tglfrom}' AND a.ic_date <= '{$tglto}' 
				$whsatu $whdua 
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
	$responce->sql = $sql; 
	if($count > 0) {
		foreach($qry as $ro){

		  $btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['ic_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['ic_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['ic_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			
			$ro['date'] = date('d-m-Y', strtotime($ro[ic_date]));

			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['ic_id'],$ro['ic_sub_plant'],$ro['date'],$ro['ic_nm_material'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function hapus(){
	global $app_plan_id,$app_id;
	$ic_id = $_POST['kode'];
	$r[ic_user_modify] = $_SESSION[$app_id]['user']['user_name'];
	$r[ic_date_modify] = date("Y-m-d H:i:s");


	$sql = "UPDATE qc_ic_mb_header SET ic_rec_stat = 'C', ic_user_modify = '{$r[ic_user_modify]}', 
				   ic_date_modify = '{$r[ic_date_modify]}'
			WHERE ic_id = '{$ic_id}';";
	$out =  dbsave_plan($app_plan_id, $sql); 

	if($out == 'OK'){
		$sql_h = "UPDATE qc_ic_kebasahan_data SET ic_idmb = NULL WHERE ic_idmb = '{$ic_id}';";
		dbsave_plan($app_plan_id, $sql_h);
	}

	echo $out;
}

function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant();
	$out .= $withselect ? "</select>" : "";
	echo $out;
}

function detailtabel($stat) {
	global $app_plan_id,$app_id;

	$ic_id 	  = $_POST['kode'];
	$id_masuk = $_POST['id_masuk'];
	$id_copy  = $_POST['id_copy'];

	


	if($stat == "edit" || $stat == "view" ) { 
		$sql0 = "SELECT * FROM qc_ic_mb_header WHERE ic_id = '{$ic_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$id_masuk = $rhead['ic_idmasuk'];

	}

	//cek kd material
	$sql_msk 	= "SELECT * FROM qc_ic_kebasahan_data WHERE ic_id = '{$id_masuk}'";
	$r_msk   	= dbselect_plan($app_plan_id, $sql_msk);
	$kdmaterial = $r_msk['ic_kd_material'];


	//lopping detil 
	$ic_id = !empty($id_copy) ? $id_copy : $ic_id;
	$sql2 = "SELECT * FROM qc_ic_mb_detail WHERE ic_id = '{$ic_id}' ORDER BY icd_group, icd_seq ASC";
	$qry2 = dbselect_plan_all($app_plan_id, $sql2);
	if(is_array($qry2)){
		foreach($qry2 as $r2){
			$arr_isi["$r2[icd_group]"]["$r2[icd_seq]"] = $r2[icd_value];
		}
	}


	// query parameter start
	$sql = "SELECT a.ic_kd_group, b.pm_groupname, a.ic_kd_seq, b.pm_desc, b.pm_sat, a.ic_std
				FROM qc_ic_spesifikasimutu a
				LEFT JOIN qc_ic_parameter b ON a.ic_kd_group = b.pm_groupid AND a.ic_kd_seq = b.pm_seq
				WHERE ic_kd_material = '{$kdmaterial}' AND a.ic_status = 'Y' ORDER BY a.ic_kd_group, b.pm_urut";
	
	
	$qry = dbselect_plan_all($app_plan_id, $sql);

	if(is_array($qry)){
		foreach($qry as $r){
			$arr_seq["$r[ic_kd_group]"]["$r[ic_kd_seq]"] = $r[pm_desc].'@@'.$r[ic_std].'@@'.$r[pm_sat];
			$arr_grup["$r[ic_kd_group]"] = $r[pm_groupname];			
		}
	}
	// query parameter end


	if($stat == 'add' && !empty($kdmaterial)){

		$sqlcp = "SELECT a.ic_id
				  FROM qc_ic_mb_header a LEFT JOIN qc_ic_kebasahan_data b ON a.ic_idmasuk = b.ic_id  
				  WHERE b.ic_kd_material = '{$kdmaterial}' AND a.ic_rec_stat = 'N' ORDER BY a.ic_date DESC LIMIT 1 ";
		$rcp   = dbselect_plan($app_plan_id, $sqlcp);
		$idcopy= $rcp['ic_id'];

		$btncopy = '<a href="javascript:void(0);" title="Copy" class="btn btn-info btn-xs" onClick="copyData(\''.$id_masuk.'\',\''.$idcopy.'\')">
						<span class="fa fa-copy"></span> 
					</a>';

		$txtcopy .= '<tr>
				    	<td colspan="5" class="text-left">'.$btncopy.' Copy Data Terakhir</td>
					 </tr>';
	}


	$out = '<table class="table table-bordered table-condensed table-striped">';
	$out .= $txtcopy;
	$out .= '	<tr>
			    	<th width="30">NO</th>    
			    	<th width="150">DESKRIPSI</th>
			    	<th width="50">STANDAR</th>
			    	<th width="50">SATUAN</th>
			    	<th width="100">NILAI</th>
		    	</tr>';

	if(is_array($arr_seq)) {
		
		$i = 0;
		$no = 0;	
		foreach ($arr_seq as $group => $a_seq) {

				$groupval = $arr_grup[$group];	

				$out .= '<tr>
					    	<th>&nbsp;</th>
					    	<th class="text-left">'.$groupval.'</th>
					    	<th class="text-left">&nbsp;</th>
					    	<th class="text-left">&nbsp;</th>
					    	<th class="text-left">&nbsp;</th>
						 </tr>';

			foreach ($a_seq as $seq => $value) {
				$val = explode('@@', $value);



				

				$isi = '';
				if(is_array($arr_isi)){
					$isi = $arr_isi[$group][$seq];
				}

				if(empty($isi) || $stat == "add"){
					if($group == 1 && $seq == 1){
						$isi = $r_msk['ic_kadar_air'];  
					}elseif($group == 2){
						if($seq == 16){
							$isi = $r_msk['ic_lw'];  
						}elseif($seq == 17){
						 	$isi = $r_msk['ic_visco'];  
						}
					}
				}


				$out .= '<tr>
					    	<td class="text-center">'.++$no.'</td>    
					    	<td>'.$val[0].'</td>    
					    	<td class="text-center">'.$val[1].'</td>    
					    	<td class="text-center">'.$val[2].'</td>    
					    	<td class="text-center">
					    		<input type="hidden" readonly name="icd_group['.$i.']" id="icd_group_'.$i.'" value="'.$group.'">
					    		<input type="hidden" readonly name="icd_seq['.$i.']" id="icd_seq_'.$i.'" value="'.$seq.'">
					    		<input class="form-control input-sm" type="text" name="icd_value['.$i.']" id="icd_value_'.$i.'" value="'.$isi.'">
					    	</td>    
				    	</tr>';
				$i++;
			}
		}

	}else{
		if(!empty($id_masuk)){
			$out .= '<tr>
					    <td colspan="5" class="text-center">
					    	Parameter untuk Material [ '.$r_msk['ic_kd_material'].' - '.$r_msk['ic_nm_material'].' ] belum ada, silahkan isi di Master Data : Spesifikasi Mutu</td>
				     </tr>';
		}
	}

	if($stat == "view") {
		$out .= '<tr>
		    <td colspan="5" class="text-center"><button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button></td>
		    </tr>';
	} else {
		$out .= '<tr>
		    <td colspan="5" class="text-center"><button type="button" class="btn btn-primary btn-sm" onClick="simpanData(\''.$stat.'\')">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button></td>
		    </tr>';
	}
	
	$out .= '</table>';



	if($stat == "edit" || $stat == "view") {

		$datetime = explode(' ',$rhead['ic_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);



		$responce->ic_id 			= $rhead[ic_id];
		$responce->ic_date 			= $rhead[date];
		$responce->ic_sub_plant 	= $rhead[ic_sub_plant];


		

	}


	$responce->ic_idmasuk 		= $r_msk[ic_id];
	$responce->ic_date_in 		= date('d-m-Y', strtotime($r_msk[ic_date_in]));
	$responce->ic_nm_material 	= $r_msk[ic_nm_material];
	$responce->ic_kadar_air 	= $r_msk[ic_kadar_air];
	$responce->ic_lw 			= $r_msk[ic_lw];
	$responce->ic_visco 		= $r_msk[ic_visco];
	$responce->ic_residu 		= $r_msk[ic_residu];



    $responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>