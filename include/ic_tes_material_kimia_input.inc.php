<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['120'];
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
	case "detailtabel":
		detailtabel($_POST['kode']);
		break;
	case "pilihMaterial":
		pilihMaterial();
		break;
}

function pilihMaterial() {
	global $app_plan_id;

	$cari = isset($_POST['txt_cari']) ? $_POST['txt_cari'] : '';
	
	if(!empty($cari)) {
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
			WHERE ic_rec_stat = 'N' AND ic_hasil = 'Y' 
			AND COALESCE(ic_idmb, '') = '' 
			AND COALESCE(ic_idmk, '') = '' $whsatu ";
			
	$qry = dbselect_plan_all($app_plan_id, $sql);

	$out = '<table class="table table-bordered table-striped table-condensed">
				<tbody>
					<tr>
						<th>#</th>
						<th>ID MASUK</th>
						<th>TGL MASUK</th>
						<th>NAMA MATERIAL</th>
						<th>KADAR AIR</th>
						<th>LW</th>
						<th>VISCO</th>
					</tr>';

	if(is_array($qry)) {
		foreach($qry as $r) {
			$r[ic_date] = date('d-m-Y', strtotime($r[ic_date]));

			$out .= '<tr>
						<td class="text-center">
							<span class="glyphicon glyphicon-ok" onClick="setMaterial(\''.$r[ic_id].'\',\''.$r[ic_date].'\',\''.$r[ic_nm_material].'\',\''.$r[ic_kadar_air].'\',\''.$r[ic_lw].'\',\''.$r[ic_visco].'\');"></span>
						</td>
						<td class="text-center">'.$r[ic_id].'</td>
						<td class="text-center">'.$r[ic_date].'</td>
						<td class="text-left">'.$r[ic_nm_material].'</td>
						<td class="text-right">'.$r[ic_kadar_air].'</td>
						<td class="text-right">'.$r[ic_lw].'</td>
						<td class="text-right">'.$r[ic_visco].'</td>
					</tr>';
		}
	} else {
		$out .= '<tr><td colspan="7">Pencarian : '.$txt_cari.' tidak ditemukan...</td></tr>';
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
		$whdua .= " and ic_sub_plant like '%".$app_subplan."%'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and ic_sub_plant like '%".$app_subplan."%'";
	}
	if($_POST['ic_id']) {
		$whdua .= " and a.ic_id like '%".$_POST['a.ic_id']."%'";
	}
	
	if($_POST['ic_sub_plant']) {
		$whdua .= " and ic_sub_plant like '%".$_POST['ic_sub_plant']."%'";
	}

	if($_POST['ic_date']) {
		$whdua .= " and ic_date = '".$_POST['ic_date']."'";
	}

	if($_POST['ic_nm_material']) {
		$whdua .= " AND b.ic_nm_material ilike '%".$_POST['ic_nm_material']."%'";
	}

	
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count 
			FROM qc_ic_teskimia_data a LEFT JOIN qc_ic_kebasahan_data b ON a.ic_idmasuk = b.ic_id 
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
				FROM qc_ic_teskimia_data a LEFT JOIN qc_ic_kebasahan_data b ON a.ic_idmasuk = b.ic_id 
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
	if($count > 0) {
		foreach($qry as $ro){

			$datetime = explode(' ',$ro['ic_date']);
			$ro['date'] = cgx_dmy2ymd($datetime[0]);


			$btnView = '<button class="btn btn-default btn-xs" title="Detail" onClick="lihatData(\''.$ro['ic_id'].'\')"><span class="glyphicon glyphicon-search"></span></button> ';
			
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" title="Ubah" onClick="editData(\''.$ro['ic_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" title="Hapus" onClick="hapusData(\''.$ro['ic_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['ic_sub_plant'],$ro['ic_id'],$ro['date'],$ro['ic_nm_material'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[ic_date] = cgx_dmy2ymd($r[ic_date]);

	$sWid = "";
	if($stat == 'edit'){
		$sWid = "AND ic_id <> '{$r[ic_id]}'";
	}
	
	$sql_dup = "SELECT ic_id FROM qc_ic_teskimia_data WHERE ic_idmasuk = '{$r[ic_idmasuk]}' AND ic_rec_stat='N' $sWid";
	$d_dup   = dbselect_plan($app_plan_id, $sql_dup);


	if($d_dup['ic_id'] == '') {
		if($stat == "add") {
			$r[ic_user_create] = $_SESSION[$app_id]['user']['user_name'];
			$r[ic_date_create] = date("Y-m-d H:i:s");
			$sql = "SELECT max(ic_id) as ic_id_max FROM qc_ic_teskimia_data WHERE ic_sub_plant = '{$r[ic_sub_plant]}'";
			$mx = dbselect_plan($app_plan_id, $sql);
			if($mx[ic_id_max] == ''){
				$mx[ic_id_max] = 0;
			} else {
				$mx[ic_id_max] = substr($mx[ic_id_max],-7);
			}
			$urutbaru = $mx[ic_id_max]+1;
			$r[ic_id] = $app_plan_id.$r[ic_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
			$sql = "INSERT INTO qc_ic_teskimia_data( ic_sub_plant, ic_id, ic_date, ic_idmasuk, no_lot, berat, glossy, flatness, pinhole, keterangan, kesimpulan, ic_rec_stat, ic_user_create, ic_date_create) VALUES ('{$r[ic_sub_plant]}', '{$r[ic_id]}', '{$r[ic_date]}', '{$r[ic_idmasuk]}', '{$r[no_lot]}', '{$r[berat]}', '{$r[glossy]}', '{$r[flatness]}', '{$r[pinhole]}', '{$r[keterangan]}', '{$r[kesimpulan]}', 'N', '{$r[ic_user_create]}', '{$r[ic_date_create]}');";
			$xsql = dbsave_plan($app_plan_id, $sql); 
			$out = $xsql;

			if($out == 'OK'){
				$sql_h = "UPDATE qc_ic_kebasahan_data SET ic_idmk = '{$r[ic_id]}' WHERE ic_id = '{$r[ic_idmasuk]}';";
				dbsave_plan($app_plan_id, $sql_h);
			}


		} else if($stat=='edit') {
			$r[ic_user_modify] = $_SESSION[$app_id]['user']['user_name'];
			$r[ic_date_modify] = date("Y-m-d H:i:s");
			$sql = "UPDATE qc_ic_teskimia_data SET no_lot = '{$r[no_lot]}', berat = '{$r[berat]}', glossy = '{$r[glossy]}',
							flatness = '{$r[flatness]}', pinhole = '{$r[pinhole]}', keterangan = '{$r[keterangan]}', 
							kesimpulan = '{$r[kesimpulan]}', ic_user_modify = '{$r[ic_user_modify]}', 
							ic_date_modify = '{$r[ic_date_modify]}' 
					WHERE ic_id = '{$r[ic_id]}';";
			$xsql = dbsave_plan($app_plan_id, $sql); 
			$out = $xsql;
		}
		echo $out;
	}else{
		echo "Data sudah ada, ID : ".$d_dup['ic_id'] ;
	}
}

function hapus(){
	global $app_plan_id;
	$ic_id = $_POST['kode'];
	$sql = "UPDATE qc_ic_teskimia_data SET ic_rec_stat='C' WHERE ic_id = '{$ic_id}';";
	$out = dbsave_plan($app_plan_id, $sql); 
	if($out == 'OK'){
		$sql_h = "UPDATE qc_ic_kebasahan_data SET ic_idmk = NULL WHERE ic_idmk = '{$ic_id}';";
		dbsave_plan($app_plan_id, $sql_h);
	}

	echo dbsave_plan($app_plan_id, $sql); 
}

function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant();
	$out .= $withselect ? "</select>" : "";
	echo $out;
}


function detailtabel($kode) {
	global $app_plan_id, $app_subplan;
	$stat = $_REQUEST[stat];

	if($stat == 'edit' || $stat == 'view'){

		$sql  = "SELECT a.*, b.ic_nm_material, b.ic_date as ic_date_in, b.ic_kadar_air, b.ic_lw, b.ic_visco
				 FROM qc_ic_teskimia_data a LEFT JOIN qc_ic_kebasahan_data b ON a.ic_idmasuk = b.ic_id  
				 WHERE a.ic_id = '{$kode}'";

		$r    = dbselect_plan($app_plan_id, $sql);

		$datetime = explode(' ',$r['ic_date']);
		$r['date'] = cgx_dmy2ymd($datetime[0]);

		$responce->ic_id   			= $r[ic_id];
		$responce->ic_date   		= $r[date];
		$responce->ic_sub_plant   	= $r[ic_sub_plant];

		$responce->ic_idmasuk		= $r[ic_idmasuk];
		$responce->ic_nm_material   = $r[ic_nm_material];
		$responce->ic_date_in   	= $r[ic_date_in];
		$responce->ic_kadar_air    	= $r[ic_kadar_air];
		$responce->ic_lw    		= $r[ic_lw];
		$responce->ic_visco    		= $r[ic_visco];
		$responce->berat    		= $r[berat];
		$responce->no_lot   		= $r[no_lot];
		$responce->glossy    		= $r[glossy];
		$responce->flatness   		= $r[flatness];
		$responce->pinhole   		= $r[pinhole];
		$responce->keterangan   	= $r[keterangan];
		$responce->kesimpulan   	= $r[kesimpulan];
	}

	if($stat == 'view'){
		$out   = '<div class="col-sm-12" style="margin-top:3px;text-align:center;">
	                <button type="button" class="btn btn-warning btn-sm" onclick="formAwal()">Batal</button>
	              </div>';
	}else{
		$out   = '<div class="col-sm-12" style="margin-top:3px;text-align:center;">
	                <button type="button" class="btn btn-primary btn-sm" onclick="simpanData()" id="btnSimpan">Simpan</button> 
	                <button type="button" class="btn btn-warning btn-sm" onclick="formAwal()">Batal</button>
	              </div>';

	}


	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>