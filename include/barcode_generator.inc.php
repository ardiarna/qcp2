<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['155'];
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
	if($_POST['ic_kd_material']) {
		$whdua .= " and ic_kd_material ilike '%".$_POST['ic_kd_material']."%'";
	}
	
	if($_POST['ic_nm_material']) {
		$whdua .= " and ic_nm_material ilike '%".$_POST['ic_nm_material']."%'";
	}

	if($_POST['ic_no_kendaraan']) {
		$whdua .= " and ic_no_kendaraan ilike '%".$_POST['ic_no_kendaraan']."%'";
	}
	
	if($_POST['ic_no_po']) {
		$whdua .= " and a.ic_no_po ilike '%".$_POST['ic_no_po']."%'";
	}
	if($_POST['ic_no_sj']) {
		$whdua .= " and a.ic_no_sj ilike '%".$_POST['ic_no_sj']."%'";
	}
	

	if(!$sidx) $sidx = 1;
	
	$sql = "SELECT count(*) as count  FROM qc_ic_kebasahan_data
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
		$sql = "SELECT * FROM qc_ic_kebasahan_data
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


			if($ro['ic_ap_pm_sts'] == 'Y'){
				$btnpm = '<i class="fa fa-check" style="color:green;" title="PM"><i>';
			}elseif($ro['ic_ap_pm_sts'] == 'N'){
				$btnpm = '<i class="fa fa-close" style="color:red;" title="PM"><i>';
			}else{
				$btnpm = '<i class="fa fa-question" style="color:black;" title="PM"><i>';
			}

			if($ro['ic_ap_kabag_sts'] == 'Y'){
				$btnkabag = '<i class="fa fa-check" style="color:green;" title="Kabag"><i>';
			}elseif($ro['ic_ap_kabag_sts'] == 'N'){
				$btnkabag = '<i class="fa fa-close" style="color:red;" title="Kabag"><i>';
			}else{
				$btnkabag = '<i class="fa fa-question" style="color:black;" title="Kabag"><i>';
			}


			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" title="Lihat" onClick="lihatData(\''.$ro['ic_id'].'\')"><span class="glyphicon glyphicon-search"></span></button> ' : '';
			
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" title="Ubah" onClick="editData(\''.$ro['ic_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';


			if(!empty($ro['ic_bpb_kode'])){
				$btnEdit = '';
			}

			$ro['kontrol'] = $btnView.$btnEdit;
			$ro['ic_apr'] = $btnkabag.' <i style="color:black;">|<i> '.$btnpm;
			
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['ic_id'],$ro['date'],$ro['ic_kd_material'],$ro['ic_nm_material'],$ro['ic_no_kendaraan'],$ro['ic_no_po'],$ro['ic_no_sj'],$ro['ic_bpb_kode'],$ro['ic_apr'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_id, $app_plan_id, $app_conn;
	$r = $_REQUEST;
	
	$r[ic_kd_material] 	= $r[ic_kd_material];
	$r[ic_no_po] 	 	= strtoupper($r[ic_no_po]);
	$r[ic_no_sj] 	 	= strtoupper($r[ic_no_sj]);

	$r[pojs_user] = $_SESSION[$app_id]['user']['user_name'];
	$r[pojs_date] = date("Y-m-d H:i:s");


	if($app_conn){

		if(cek_po($r[ic_no_po])){

			$sql_ho = " SELECT a.porder_kode, b.item_kode, c.company 
						FROM porders a 
						LEFT JOIN porderitem b ON a.porder_kode = b.porder_kode 
						LEFT JOIN supplier c ON a.supplier_kode = c.supplier_kode 
						WHERE a.porder_kode = '{$r[ic_no_po]}' AND b.item_kode = '{$r[ic_kd_material]}' LIMIT 1";
			$d_ho   = dbselect($sql_ho);

			$r[ic_sub_kontraktor] = $d_ho['company'];

			if(empty($d_ho['porder_kode'])){
				echo "No. PO atau Item tidak ditemukan di Armasi!" ;
			}else{

				$sql = "UPDATE qc_ic_kebasahan_data SET ic_no_po = '{$r[ic_no_po]}', ic_no_sj = '{$r[ic_no_sj]}', 
							ic_sub_kontraktor = '{$r[ic_sub_kontraktor]}', pojs_user = '{$r[pojs_user]}', 
							pojs_date = '{$r[pojs_date]}' 
						WHERE ic_id = '{$r[ic_id]}';";
				$xsql = dbsave_plan($app_plan_id, $sql); 
				$out = $xsql;
				echo $out;
			}

		}else{
			echo "Format No. PO tidak sesuai!" ;
		}

	}else{
		echo "Tidak ada sambungan ke HO" ;
	}
}

function cek_po($nopo){
	global $app_plan_id;

	$arrkd = array('PLK','PIM');

	$exp   = explode('/', $nopo);
	$po_j  =  $exp[0];
	$po_p  =  $exp[1];
	$po_t  =  strlen($exp[2]);
	$po_k  =  strlen($exp[3]);

	if( in_array($po_j, $arrkd) && $po_p == $app_plan_id && $po_t == 2 && $po_k == 5){
		return true;
	}else{
		return false;
	}
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