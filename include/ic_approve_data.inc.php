<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['121'];
$app_subplan = $_SESSION[$app_id]['user']['sub_plan'];
$user    = $_SESSION[$app_id]['user']['user_name'];



//CEK AKSES APPROVE
$sql_apr = " SELECT * FROM qc_ic_in_appr WHERE appr_uname = '$user' ";
$r_apr   = dbselect_plan($app_plan_id, $sql_apr);
$kdjab   = $r_apr['appr_jab'];

$arr_jab = array(1,2);

if(in_array($kdjab, $arr_jab)){
	$inlist = true;
}else{
	$inlist = false;
}
 

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
	case "detaillist":
		detaillist();
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

function setapproval(){
	global $app_plan_id, $app_id, $user, $kdjab, $inlist;
	

	$sts   = $_POST['sts'];
	$ket   = $_POST['ket'];

	$tanggal = date("Y-m-d H:i:s");


	$ic_id ='';
	if($_POST['jenis'] == 'single'){
		$ic_id   = "'".$_POST['id']."'";
	}else{

		$pieces = explode(',', $_POST['id']);
		foreach($pieces as $idd){
			if(!empty($ic_id)){
				$ic_id .= ",";
			}

			$ic_id .= "'".$idd."'";
		}
	}

	
	if($kdjab == '1'){
		//PM
		$field = "ic_ap_pm_user = '$user', ic_ap_pm_sts = '$sts', ic_ap_pm_date = '$tanggal', ic_ap_pm_note = '$ket'";
	}elseif($kdjab == '2'){
		//KABAG
		$field = "ic_ap_kabag_user = '$user', ic_ap_kabag_sts = '$sts', ic_ap_kabag_date = '$tanggal', ic_ap_kabag_note = '$ket'";
	}else{
		$field = '';
	}

	if(!empty($field)){
		$sql = "UPDATE qc_ic_kebasahan_data SET $field WHERE ic_id IN ($ic_id)";
		$out = dbsave_plan($app_plan_id, $sql); 
	}else{
		$out = 'Maaf, Anda tidak punyak akses untuk Approve!';
	}


	$responce->respons = $out;
	echo json_encode($responce);
}


function urai(){
	global $app_plan_id, $akses, $app_subplan, $app_id, $user, $kdjab, $inlist;
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
	if($_POST['nm_sub_kontraktor']) {
		$whdua .= " and b.subkon_name ilike '%".$_POST['nm_sub_kontraktor']."%'";
	}
	if($_POST['ic_kadar_air']) {
		$whdua .= " and ic_kadar_air = '".$_POST['ic_kadar_air']."'";
	}
	if($_POST['ic_hasil']) {
		$whdua .= " and ic_hasil = '".$_POST['ic_hasil']."'";
	}


	if(!$sidx) $sidx = 1;


	$stsKabag = $_GET['stsKabag'];
	$stsPm 	  = $_GET['stsPm'];
	$stsUser  = $_GET['stsUser'];


	$whtiga = '';

	if ($inlist) {
		if($kdjab == '1'){
			$whtiga .= " and COALESCE(ic_ap_pm_sts, '') = '".$stsUser."'";
		}elseif($kdjab == '2'){
			$whtiga .= " and COALESCE(ic_ap_kabag_sts, '') = '".$stsUser."'";
		}
	}else{
		$whtiga .= " and COALESCE(ic_ap_pm_sts, '') = '".$stsPm."'";

		$whtiga .= " and COALESCE(ic_ap_kabag_sts, '') = '".$stsKabag."'";
	}
	

	$whstskabag = '';
	if($kdjab == '1'){
		$whstskabag = "AND COALESCE(ic_ap_kabag_sts, '') <> '' ";
	}

	


	$sql = "SELECT count(*) as count 
			FROM qc_ic_kebasahan_data a 
 			LEFT JOIN qc_md_subkon b ON a.ic_sub_kontraktor = b.subkon_id 
			WHERE ic_rec_stat='N' and ic_date >= '{$tglfrom}' and ic_date <= '{$tglto}' $whstskabag $whsatu $whdua $whtiga";
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
	 			WHERE ic_rec_stat='N' and ic_date >= '{$tglfrom}' and ic_date <= '{$tglto}' $whstskabag $whsatu $whdua $whtiga
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
	$responce->sql = $sql; 
	if($count > 0) {
		foreach($qry as $ro){

			$datetime = explode(' ',$ro['ic_date']);
			$ro['date'] = cgx_dmy2ymd($datetime[0]);


			$ro['ic_hasil']     = $ro['ic_hasil'] == 'Y' ? 'OK' : 'NOT OK' ; 

			if($ro['ic_ap_kabag_sts'] == 'Y'){
				$ro['ic_apr_kabag'] = '<i class="fa fa-check" style="color:green;"><i>';
			}elseif($ro['ic_ap_kabag_sts'] == 'N'){
				$ro['ic_apr_kabag'] = '<i class="fa fa-close" style="color:red;"><i>';
			}else{
				$ro['ic_apr_kabag'] = '';
			}

			if($ro['ic_ap_pm_sts'] == 'Y'){
				$ro['ic_apr_pm'] = '<i class="fa fa-check" style="color:green;"><i>';
			}elseif($ro['ic_ap_pm_sts'] == 'N'){
				$ro['ic_apr_pm'] = '<i class="fa fa-close" style="color:red;"><i>';
			}else{
				$ro['ic_apr_pm'] = '';
			}


			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" title="Lihat" onClick="lihatData(\''.$ro['ic_id'].'\')"><span class="glyphicon glyphicon-search"></span></button> ' : '';
			
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" title="Ubah" onClick="editData(\''.$ro['ic_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';

			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" title="Hapus" onClick="hapusData(\''.$ro['ic_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';

			$ro['kontrol'] = $btnEdit.$btnDel;
			$ro['btn_view'] = $btnView;
			
			$responce->rows[$i]['id'] = $ro[ic_id]; 
			$responce->rows[$i]['cell']=array($ro['btn_view'],$ro['ic_id'],$ro['date'],$ro['ic_nm_material'],$ro['ic_lw'],$ro['ic_visco'],$ro['ic_kadar_air'],$ro['ic_hasil'],$ro['ic_apr_kabag'],$ro['ic_apr_pm']);
			$i++;
		}
	}
	echo json_encode($responce);
}



function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant();
	$out .= $withselect ? "</select>" : "";
	echo $out;
}

function detailtabel($stat) {
	global $app_plan_id, $user, $kdjab, $inlist;
	$sql  = "SELECT a.*, b.subkon_name as nm_sub_kontraktor
			 FROM qc_ic_kebasahan_data a
			 LEFT JOIN qc_md_subkon b ON a.ic_sub_kontraktor = b.subkon_id 
			 WHERE ic_id = '{$_REQUEST[kode]}'";
	$d    = dbselect_plan($app_plan_id, $sql);

	$datetime = explode(' ',$d['ic_date']);
	$d['date'] = cgx_dmy2ymd($datetime[0]);

	$hasil = $d[ic_hasil] == 'Y' ? 'OK' : 'NOT OK';


	//kabag
	if($d[ic_ap_kabag_sts] =='Y'){
		$btnkabag = 'APPROVE';
	}elseif($d[ic_ap_kabag_sts] =='N'){
		$btnkabag = 'NOT APPROVE';
	}else{
		$btnkabag = '';
	}

	$catatankabag = !empty($d[ic_ap_kabag_note]) ? $d[ic_ap_kabag_note] : '';

	$apkabag = $btnkabag.' - '.$catatankabag;


	//pm
	if($d[ic_ap_pm_sts] == 'Y'){
		$btnpm = 'APPROVE';
	}elseif($d[ic_ap_pm_sts] == 'N'){
		$btnpm = 'NOT APPROVE';
	}else{
		$btnpm = '';
	}

	$catatanpm = !empty($d[ic_ap_pm_note]) ? $d[ic_ap_pm_note] : '';

	$appm = $btnpm.' - '.$catatanpm;


	$d[ic_keterangan] = !empty($d[ic_keterangan]) ? $d[ic_keterangan] : '';


	if($kdjab == '1'){
		//PM
		$isistatus  = $d[ic_ap_pm_sts];
		$isiket 	= $d[ic_ap_pm_note];
	}elseif($kdjab == '2'){
		//KABAG
		$isistatus  = $d[ic_ap_kabag_sts];
		$isiket 	= $d[ic_ap_kabag_note];
	}else{
		$isistatus  = '';
		$isiket 	= '';
	}

	$out  = '<div style="display:block;max-height:500px;overflow-y:auto;-ms-overflow-style:-ms-autohiding-scrollbar;">';
	$out .= '<table class="table table-bordered table-striped table-condensed">
				<tr>
					<th colspan="2" class="text-left" style="min-width:150px;">ID</th>
					<td colspan="2" style="min-width:200px;">'.$d[ic_id].'</td>
				</tr>
				<tr>
					<th colspan="2" class="text-left">NO KENDARAAN</th>
					<td colspan="2">'.$d[ic_no_kendaraan].'</td>
				</tr>

				<tr>
					<th colspan="2" class="text-left">TANGGAL</th>
					<td colspan="2">'.$d[date].'</td>
				</tr>
				<tr>
					<th colspan="2" class="text-left">MATERIAL</th>
					<td colspan="2">'.$d[ic_nm_material].'</td>
				</tr>
				<tr>
					<th colspan="2" class="text-left">SUB KONTRAKTOR</th>
					<td colspan="2">'.$d[nm_sub_kontraktor].'</td>
				</tr>
				<tr>
					<th class="text-left">KADAR AIR</th>
					<td class="text-left">'.$d[ic_kadar_air].'</td>
					
					<th class="text-left">LW</th>
					<td class="text-left">'.$d[ic_lw].'</td>
				</tr>

				<tr>
					<th class="text-left">VISCO</th>
					<td class="text-left">'.$d[ic_visco].'</td>

					<th class="text-left">RESIDU</th>
					<td class="text-left">'.$d[ic_residu].'</td>
				</tr>
				<tr>
					<th colspan="2" class="text-left">HASIL</th>
					<td colspan="2">'.$hasil.' - '.$d[ic_keterangan].'</td>
				</tr>
				<tr>
					<th colspan="2" class="text-left">APV KABAG</th>
					<td colspan="2">'.$apkabag.'</td>
				</tr>
				<tr>
					<th colspan="2" class="text-left">APV PM</th>
					<td colspan="2">'.$appm.'</td>
				</tr>
			</table>
			</div>
				';


	$footer = '';
	if($inlist){

		$out .= '<form class="form-horizontal" id="frApD">
	                <div class="form-group">
	                    <label class="col-sm-2 control-label" style="text-align:left;">STATUS : </label>
	                    <div class="col-sm-4" style="margin-top:3px;">
	                        <select class="form-control input-sm" name="ap_status" id="ap_status" required>'.cboapprove($isistatus).'<select>
	                    </div>
                    </div>
	                <div class="form-group">
	                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">KETERANGAN : </label>
	                    <div class="col-sm-4" style="margin-top:3px;">
	                        <textarea class="form-control input-sm" name="ap_keterangan" id="ap_keterangan" required>'.$isiket.'</textarea>
	                    </div>
	                </div>
	             </form>';

	    $footer  .= '<button type="button" class="btn btn-info" style="float: right;" onClick="apporve_data(\'single\',\''.$d[ic_id].'\',\''.$d[ic_hasil].'\')">Submit</button>';
   	}
    
    $footer .= '<button type="button" class="btn btn-warning" data-dismiss="modal" style="float: left;">Close</button>';

	$responce->out = $out;
	$responce->footer = $footer;

	echo json_encode($responce);

}

function detaillist() {
	global $app_plan_id, $user, $kdjab, $inlist;

	// kode
	$arr_id = $_POST['kode'];
	$iplodeid = implode(',', $arr_id);

	$ic_id ='';
	if(is_array($arr_id)){
		foreach ($arr_id as $idd) {
			if(!empty($ic_id)){
				$ic_id .= ",";
			}

			$ic_id .= "'".$idd."'";
		}
	}

	
	$sql  = "SELECT * FROM qc_ic_kebasahan_data WHERE ic_id IN ($ic_id) ";
	$qry  = dbselect_plan_all($app_plan_id, $sql);

	if($qry){

		$out  = '<div style="display:block;max-height:500px;overflow-y:auto;-ms-overflow-style:-ms-autohiding-scrollbar;">
				 <table class="table table-bordered table-striped">';
		$out .= '<tr>
					<th>NO</th>
					<th>ID</th>
					<th  nowrap>NO KENDARAAN</th>
					<th>MATERIAL</th>
					<th  nowrap>KADAR AIR</th>
					<th  nowrap>LW</th>
					<th  nowrap>VISCO</th>
					<th  nowrap>RESIDU</th>
				 </tr>';
		$no = 0;
		foreach($qry as $r){
			$out .= '<tr>
						<td class="text-center" style="width:50px;min-width:50px;">'.++$no.'</td>
						<td class="text-center" nowrap>'.$r[ic_id].'</td>
						<td class="text-center" nowrap>'.$r[ic_no_kendaraan].'</td>
						<td class="text-left" nowrap>'.$r[ic_nm_material].'</td>
						<td class="text-right">'.$r[ic_kadar_air].'</td>
						<td class="text-right">'.$r[ic_lw].'</td>
						<td class="text-right">'.$r[ic_visco].'</td>
						<td class="text-right">'.$r[ic_residu].'</td>
					</tr>';
		}

		$out .= '</table></div><br>';
	}
	

	$footer = '';
	if($inlist){

		$out .= '<form class="form-horizontal" id="frApD">
	                <div class="form-group">
	                    <label class="col-sm-2 control-label" style="text-align:left;">STATUS : </label>
	                    <div class="col-sm-4" style="margin-top:3px;">
	                        <select class="form-control input-sm" name="ap_status" id="ap_status">'.cboapprove().'<select>
	                    </div>
                    </div>
	                <div class="form-group">
	                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">KETERANGAN : </label>
	                    <div class="col-sm-4" style="margin-top:3px;">
	                        <textarea class="form-control input-sm" name="ap_keterangan" id="ap_keterangan"></textarea>
	                    </div>
	                </div>
	             </form>';

	    $footer  .= '<button type="button" class="btn btn-info" style="float: right;" onClick="apporve_data(\'list\',\''.$iplodeid.'\')">Submit</button>';
   	}
    
    $footer .= '<button type="button" class="btn btn-warning" data-dismiss="modal" style="float: left;">Close</button>';

	$responce->out = $out;
	$responce->footer = $footer;

	echo json_encode($responce);

}


function cboapprove($val = ''){
	$qry = array('Y' => 'APPROVE', 'N' => 'NOT APPROVE');
	$out = '<option value="">PILIH</option>';
	foreach ($qry as $key => $value) {
		$selected = $val == $key ? 'Selected' : '';  
		$out .= '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
	}
	return $out;
}
?>