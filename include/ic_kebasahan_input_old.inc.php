<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['112'];
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
		detailtabel($_POST['stat']);
		break;
	case "pilihMaterial":
		pilihMaterial();
		break;
	case "pilihSubkon":
		pilihSubkon();
		break;
	case "cbobox":
		cbobox($_POST['subplant'],$_POST['nilai'],true);
		break;
	case "setapproval":
		setapproval();
		break;
}

function pilihMaterial() {
	global $app_plan_id;
	if($_POST['txt_cari']) {
		$txt_cari = strtoupper($_POST['txt_cari']);
		$whsatu = "AND ( upper(item_nama) like '%{$txt_cari}%' OR item_kode like '%{$txt_cari}%' ) ";
	}

	$wsimiliar = "AND item_kode similar to '(00.|01.|04.)%' ";

	$sql = "SELECT distinct item_kode, item_nama FROM item WHERE 1=1 $wsimiliar $whsatu order by item_nama";
	$qry = dbselect_plan_all($app_plan_id,$sql);


	$out = '<table class="table table-bordered table-striped table-condensed">
				<tbody>
					<tr>
						<th>#</th>
						<th>KODE MATERIAL</th>
						<th>NAMA MATERIAL</th>
					</tr>';
	if(is_array($qry)) {
		foreach($qry as $r) {
			$out .= '<tr>
						<td class="text-center"><span class="glyphicon glyphicon-ok" onClick="setMaterial(\''.$r[item_kode].'\',\''.$r[item_nama].'\');"></span></td>
						<td class="text-center">'.$r[item_kode].'</td>
						<td>'.strtoupper($r[item_nama]).'</td>
					</tr>';
		}
	} else {
		$out .= '<tr><td colspan="3">Pencarian : '.$txt_cari.' tidak ditemukan...</td></tr>';
	}	
	$out .= '</tbody></table>';

	$responce->out = $out;
	echo json_encode($responce);
}

function pilihSubkon() {
	global $app_plan_id;
	if($_POST['txt_cari']) {
		$txt_cari = strtoupper($_POST['txt_cari']);
		$whsatu = "AND (subkon_id like '%{$txt_cari}%' OR subkon_name like '%{$txt_cari}%') ";
	}
	$sql = "SELECT * FROM qc_md_subkon WHERE subkon_status = 'N' $whsatu ORDER BY subkon_id ASC";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out = '<table class="table table-bordered table-striped table-condensed">
				<tbody>
					<tr>
						<th>#</th>
						<th>KODE SUB KONTRAKTOR</th>
						<th>NAMA SUB KONTRAKTOR</th>
					</tr>';
	if(is_array($qry)) {
		foreach($qry as $r) {
			$out .= '<tr>
						<td class="text-center"><span class="glyphicon glyphicon-ok" onClick="setSubkon(\''.$r[subkon_id].'\',\''.$r[subkon_name].'\');"></span></td>
						<td class="text-center">'.$r[subkon_id].'</td>
						<td>'.strtoupper($r[subkon_name]).'</td>
					</tr>';
		}
	} else {
		$out .= '<tr><td colspan="3">Pencarian : '.$txt_cari.' tidak ditemukan...</td></tr>';
	}	
	$out .= '</tbody></table>';

	$responce->out = $out;
	echo json_encode($responce);
}

function cbobox($subplan, $nilai = "TIDAKADA", $irset = false){
	global $app_plan_id, $app_id;
	if(empty($subplan)){
		$subplan = $_SESSION[$app_id]['user']['sub_plan'];
	}

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

	if($irset){
		echo $out;
	}else{
		return $out;
	}

}

function urai(){
	global $app_plan_id, $akses, $app_subplan, $app_id;
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

	if($_POST['ic_id']) {
		$whdua .= " and ic_id ilike '%".$_POST['ic_id']."%'";
	}

	if($_POST['ic_date']) {
		$whdua .= " and to_char(ic_date, 'DD-MM-YYYY') = '".$_POST['ic_date']."'";
	}
	if($_POST['ic_nm_material']) {
		$whdua .= " and ic_nm_material ilike '%".$_POST['ic_nm_material']."%'";
	}
	if($_POST['ic_no_kendaraan']) {
		$whdua .= " and ic_no_kendaraan ilike '%".$_POST['ic_no_kendaraan']."%'";
	}
	if($_POST['ic_lw']) {
		$whdua .= " and ic_lw = '".$_POST['ic_lw']."'";
	}
	if($_POST['ic_visco']) {
		$whdua .= " and ic_visco = '".$_POST['ic_visco']."'";
	}
	if($_POST['ic_kadar_air']) {
		$whdua .= " and ic_kadar_air = '".$_POST['ic_kadar_air']."'";
	}
	if($_POST['ic_hasil']) {
		$whdua .= " and ic_hasil = '".$_POST['ic_hasil']."'";
	}
	if($_POST['ic_no_po']) {
		$whdua .= " and a.ic_no_po ilike '%".$_POST['ic_no_po']."%'";
	}
	

	if(!$sidx) $sidx = 1;
	
	$sql = "SELECT count(*) as count 
			FROM qc_ic_kebasahan_data a 
 			LEFT JOIN qc_md_subkon b ON a.ic_sub_kontraktor = b.subkon_id 
			WHERE ic_rec_stat='N' and ic_date >= '{$tglfrom}' and ic_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT a.*, b.subkon_name as nm_sub_kontraktor
	 			FROM qc_ic_kebasahan_data a 
	 			LEFT JOIN qc_md_subkon b ON a.ic_sub_kontraktor = b.subkon_id 
	 			WHERE ic_rec_stat='N' and ic_date >= '{$tglfrom}' and ic_date <= '{$tglto}' $whsatu $whdua
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

			$datetime = explode(' ',$ro['ic_date']);
			$ro['date'] = cgx_dmy2ymd($datetime[0]);

			$ro['ic_hasil']     		= $ro['ic_hasil'] == 'Y' ? 'OK' : 'NOT OK' ; 


			if($ro['ic_ap_pm_sts'] == 'Y'){
				$iconpm = '<span class="fa fa-check-square-o"></span>';
			}elseif($ro['ic_ap_pm_sts'] == 'N'){
				$iconpm = '<span class="fa fa-fa-close"></span>';
			}else{
				$iconpm = '<span class="fa fa-question"></span>';;
			}

			if($ro['ic_ap_kabag_sts'] == 'Y'){
				$iconkabag = '<span class="fa fa-check-square-o"></span>';
			}elseif($ro['ic_ap_kabag_sts'] == 'N'){
				$iconkabag = '<span class="fa fa-fa-close"></span>';
			}else{
				$iconkabag = '<span class="fa fa-question"></span>';;
			}

			$btnkabag = '<button class="btn btn-default btn-xs" title="Kabag">'.$iconkabag.'</button>';
			$btnpm    = '<button class="btn btn-default btn-xs" title="PM">'.$iconpm.'</button>';

			$iconapr = $btnkabag.' '.$btnpm;


			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" title="Lihat" onClick="lihatData(\''.$ro['ic_id'].'\')"><span class="glyphicon glyphicon-search"></span></button> ' : '';
			
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" title="Ubah" onClick="editData(\''.$ro['ic_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';

			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" title="Hapus" onClick="hapusData(\''.$ro['ic_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';

			if($ro['ic_ap_kabag_sts'] == 'Y' || $ro['ic_ap_pm_sts'] == 'Y'){
				$btnEdit = '';
				$btnDel  = '';
			}

			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$ro['ic_apr'] = $iconapr;
			
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['ic_id'],$ro['date'],$ro['ic_no_kendaraan'],$ro['ic_nm_material'],$ro['ic_lw'],$ro['ic_visco'],$ro['ic_kadar_air'],$ro['ic_hasil'],$ro['ic_no_po'],$ro['ic_apr'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[ic_date] = cgx_dmy2ymd($r[ic_date]);
	$r[ic_no_kendaraan] = $r[no_prov].' '.$r[no_kend].' '.$r[no_wil];
	$r[ic_kadar_air] 	= cgx_angka($r[ic_kadar_air]);
	$r[ic_lw] 		 	= cgx_angka($r[ic_lw]);
	$r[ic_visco] 	 	= cgx_angka($r[ic_visco]);
	$r[ic_residu] 	 	= cgx_angka($r[ic_residu]);
	$r[ic_no_po] 	 	= strtoupper($r[ic_no_po]);

	$sWid = "";
	if($stat == 'edit'){
		$sWid = "AND ic_id <> '{$r[ic_id]}'";
	}
	
	$sql_dup = "SELECT ic_id FROM qc_ic_kebasahan_data 
				WHERE ic_date = '{$r[ic_date]}' AND ic_no_kendaraan = '{$r[ic_no_kendaraan]}' 
			    AND ic_kd_material = '{$r[ic_kd_material]}' AND ic_rec_stat='N' $sWid";
	$d_dup   = dbselect_plan($app_plan_id, $sql_dup);

	if($d_dup['ic_id'] == '') {
		if($stat == "add") {
			$r[ic_user_create] = $_SESSION[$app_id]['user']['user_name'];
			$r[ic_date_create] = date("Y-m-d H:i:s");
			$sql = "SELECT max(ic_id) as ic_id_max FROM qc_ic_kebasahan_data";
			$mx = dbselect_plan($app_plan_id, $sql);
			if($mx[ic_id_max] == ''){
				$mx[ic_id_max] = 0;
			} else {
				$mx[ic_id_max] = substr($mx[ic_id_max],-7);
			}
			$urutbaru = $mx[ic_id_max]+1;
			$r[ic_id] = $app_plan_id."-".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
			

			$sql = "INSERT INTO qc_ic_kebasahan_data(ic_id, ic_date, ic_no_kendaraan, 
						ic_kd_material, ic_nm_material, ic_sub_kontraktor, ic_kadar_air, ic_keterangan, ic_rec_stat, 
			 			ic_user_create, ic_date_create, ic_lw, ic_visco, ic_residu, ic_hasil, ic_no_sj, ic_no_po) 
					VALUES ('{$r[ic_id]}', '{$r[ic_date]}', '{$r[ic_no_kendaraan]}', 
						'{$r[ic_kd_material]}', '{$r[ic_nm_material]}', '{$r[ic_sub_kontraktor]}', '{$r[ic_kadar_air]}', 
						'{$r[ic_keterangan]}', 'N', '{$r[ic_user_create]}', '{$r[ic_date_create]}', '{$r[ic_lw]}', 
						'{$r[ic_visco]}', '{$r[ic_residu]}', '{$r[ic_hasil]}', '{$r[ic_no_sj]}', '{$r[ic_no_po]}');";
			$xsql = dbsave_plan($app_plan_id, $sql); 
			$out = $xsql;
		} else if($stat=='edit') {
			$r[ic_user_modify] = $_SESSION[$app_id]['user']['user_name'];
			$r[ic_date_modify] = date("Y-m-d H:i:s");
			
			$sql = "UPDATE qc_ic_kebasahan_data 
					SET ic_date = '{$r[ic_date]}', ic_no_kendaraan = '{$r[ic_no_kendaraan]}',
						ic_kd_material = '{$r[ic_kd_material]}', ic_nm_material = '{$r[ic_nm_material]}', 
						ic_sub_kontraktor = '{$r[ic_sub_kontraktor]}', ic_kadar_air = '{$r[ic_kadar_air]}',
						ic_keterangan = '{$r[ic_keterangan]}', ic_lw = '{$r[ic_lw]}', ic_visco = '{$r[ic_visco]}', 
						ic_residu = '{$r[ic_residu]}', ic_hasil = '{$r[ic_hasil]}', ic_no_sj = '{$r[ic_no_sj]}', 
						ic_no_po = '{$r[ic_no_po]}', ic_user_modify = '{$r[ic_user_modify]}', 
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
	$sql = "UPDATE qc_ic_kebasahan_data set ic_rec_stat='C' where ic_id = '{$ic_id}';";
	echo dbsave_plan($app_plan_id, $sql); 
}

function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant();
	$out .= $withselect ? "</select>" : "";
	echo $out;
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == 'edit' || $stat == 'view'){
		$sql  = "SELECT a.*, b.subkon_name as nm_sub_kontraktor
				 FROM qc_ic_kebasahan_data a
				 LEFT JOIN qc_md_subkon b ON a.ic_sub_kontraktor = b.subkon_id 
				 WHERE ic_id = '{$_REQUEST[kode]}'";
		$d    = dbselect_plan($app_plan_id, $sql);

		$datetime = explode(' ',$d['ic_date']);
		$d['date'] = cgx_dmy2ymd($datetime[0]);
		$nken = explode(' ', $d[ic_no_kendaraan]);


		$responce->ic_id   				= $d[ic_id];
		$responce->ic_date   			= $d[date];
		$responce->no_prov   			= $nken[0];
		$responce->no_kend   			= $nken[1];
		$responce->no_wil   			= $nken[2];
		$responce->ic_kd_material   	= $d[ic_kd_material];
		$responce->ic_nm_material   	= $d[ic_nm_material];
		$responce->ic_sub_kontraktor   	= $d[ic_sub_kontraktor];
		$responce->nm_sub_kontraktor   	= $d[nm_sub_kontraktor];
		$responce->ic_kadar_air   		= $d[ic_kadar_air];
		$responce->ic_keterangan   		= $d[ic_keterangan];
		$responce->ic_lw   				= $d[ic_lw];
		$responce->ic_visco   			= $d[ic_visco];
		$responce->ic_residu   			= $d[ic_residu];
		$responce->ic_hasil   			= $d[ic_hasil];
		$responce->ic_no_po   			= $d[ic_no_po];
		$responce->ic_no_sj   			= $d[ic_no_sj];

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

	$responce->detailtabel   = $out;
	echo json_encode($responce);
}

?>