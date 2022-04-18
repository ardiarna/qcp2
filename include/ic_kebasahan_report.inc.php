<?php

include_once("../libs/init.php");

if($_GET["mode"]) {
	$oper = $_GET["mode"];
} else {
	$oper = $_POST["oper"];
}

switch ($oper) {
	case "urai":
		urai();
		break;
	case "excel":
		excel();
		break;
	case "lihatdata":
		lihatdata();
		break;
	case "cbosubplant":
		cbosubplant($_GET['withselect']);
		break;
}

function excel() {
	header("Content-type: application/x-msexcel"); 
	header('Content-Disposition: attachment; filename="DATA_KEBASAHAN_MATERIAL.xls"');
	echo urai(true);
}

function urai($excel = false){
	if($excel){
		$border = "border='1'";
	}else{
		$border = "";
	}

	global $app_plan_id, $nama_plan;
	$tanggal  = explode('@', $_GET['tanggal']);
	$tglfrom  = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto 	  = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	

	$sql = "SELECT * FROM qc_ic_kebasahan_data
			WHERE ic_rec_stat = 'N' AND ic_date >= '{$tglfrom}' and ic_date <= '{$tglto}' $whdua 
			ORDER BY ic_date, ic_id ASC";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)){
		foreach($qry as $r){
			$datetime = explode(' ',$r[ic_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			$r[jam] = substr($datetime[1],0,5);

			$r[ic_hasil]  = $r[ic_hasil] == 'Y' ? 'OK' : 'NOT OK';


			$arr_nilai["$r[tgl]"]["$r[ic_id]"] = $r[ic_no_kendaraan].'@@@'.$r[ic_no_sj].'@@@'.$r[ic_no_po].'@@@'.$r[ic_kd_material].'@@@'.$r[ic_nm_material].'@@@'.$r[ic_sub_kontraktor].'@@@'.$r[ic_kadar_air].'@@@'.$r[ic_lw].'@@@'.$r[ic_visco].'@@@'.$r[ic_residu].'@@@'.$r[ic_keterangan].'@@@'.$r[ic_hasil].'@@@'.$r[ic_ap_kabag_sts].'@@@'.$r[ic_ap_kabag_note].'@@@'.$r[ic_ap_pm_sts].'@@@'.$r[ic_ap_pm_note]; 
		}
	}

	if(is_array($arr_nilai)) {
		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;}table.adaborder th,table.adaborder td{border:1px solid black;} .str{ mso-number-format:\@; } </style>';
		$html .= '
				  <div style="text-align:center;font-size:20px;font-weight:bold;">DATA - DATA KEBASAHAN RAW MATERIAL</div>
				  <div style="text-align:center;font-size:14px;font-weight:bold;">TGL : '.$tgljudul.'</div><br>';
		
		foreach ($arr_nilai as $tgl => $a_id) {
			$html .= '<div style="overflow-x:auto;"><table class="adaborder" id="tbl01" '.$border.'>';
			$html .= '<tr><th colspan="17" style="background: #D1D1D1;text-align:left;">TGL : '.$tgl.'</th></tr>';
			$html .= '<tr>
						<th rowspan="2" style="width:50px;min-width:50px;">NO</th>
						<th rowspan="2" style="width:100px;min-width:100px;">NO. KENDARAAN</th>
						<th rowspan="2" style="width:120px;min-width:120px;">NO. SJ</th>
						<th rowspan="2" style="width:120px;min-width:120px;">NO. PO</th>
						<th rowspan="2" style="width:120px;min-width:120px;">KODE MATERIAL</th>
						<th rowspan="2" style="width:200px;min-width:200px;">NAMA MATERIAL</th>
						<th rowspan="2" style="width:200px;min-width:200px;">SUPPLIER</th>
						<th rowspan="2" style="width:70px;min-width:70px;">KADAR AIR</th>
						<th rowspan="2" style="width:15px;min-width:50px;">LW</th>
						<th rowspan="2" style="width:105x;min-width:50px;">VICSO</th>
						<th rowspan="2" style="width:50;min-width:50px;">RESIDU</th>
						<th rowspan="2" style="width:200px;min-width:200px;">KETERANGAN</th>
						<th rowspan="2" style="width:50px;min-width:50px;">STATUS</th>
						<th colspan="2">APPROVE KABAG</th>
						<th colspan="2">APPROVE PM</th>
					 </tr>';

			$html .= '<tr>
						<th style="width:100px;min-width:100px;">STATUS</th>
						<th style="width:200px;min-width:200px;">CATATAN</th>

						<th style="width:100px;min-width:100px;">STATUS</th>
						<th style="width:200px;min-width:200px;">CATATAN</th>
					 </tr>';

			$no =1;
			foreach ($a_id as $id => $val1) {
				$val = explode('@@@', $val1);
				$html .= '<tr>
							<td align="center">'.$no.'</td>
							<td align="center"><span onclick="lihatData(\''.$id.'\')">'.$val[0].'</span></td>
							<td align="center"><span onclick="lihatData(\''.$id.'\')">'.$val[1].'</span></td>
							<td align="center"><span onclick="lihatData(\''.$id.'\')">'.$val[2].'</span></td>
							<td align="center"><span onclick="lihatData(\''.$id.'\')">'.$val[3].'</span></td>
							<td align="left"><span onclick="lihatData(\''.$id.'\')">'.$val[4].'</span></td>
							<td align="left"><span onclick="lihatData(\''.$id.'\')">'.$val[5].'</span></td>
							<td align="right"><span onclick="lihatData(\''.$id.'\')">'.$val[6].'</span></td>
							<td align="right"><span onclick="lihatData(\''.$id.'\')">'.$val[7].'</span></td>
							<td align="right"><span onclick="lihatData(\''.$id.'\')">'.$val[8].'</span></td>
							<td align="right"><span onclick="lihatData(\''.$id.'\')">'.$val[9].'</span></td>
							<td align="left"><span onclick="lihatData(\''.$id.'\')">'.$val[10].'</span></td>
							<td align="center"><span onclick="lihatData(\''.$id.'\')">'.$val[11].'</span></td>
							<td align="center"><span onclick="lihatData(\''.$id.'\')">'.cbo_status($val[12]).'</span></td>
							<td><span onclick="lihatData(\''.$id.'\')">'.$val[13].'</span></td>
							<td align="center"><span onclick="lihatData(\''.$id.'\')">'.cbo_status($val[14]).'</span></td>
							<td><span onclick="lihatData(\''.$id.'\')">'.$val[15].'</span></td>
						 </tr>';
			$no++;
			}		 

			$html .='</table></div><br>';
		}
	} else {
		$html = 'TIDAKADA';
	}
	if($excel){
		return $html;
	}else{
		$responce->detailtabel = $html; 
		echo json_encode($responce);
	}
}


function lihatdata(){
	global $app_plan_id;
	$kode = $_POST['kode'];
	$sql = "SELECT * FROM qc_ic_kebasahan_data WHERE ic_id = '{$kode}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$datetime = explode(' ',$rh[ic_date]);
	$rh[tgl] = cgx_dmy2ymd($datetime[0]);

	$out = '<table class="table table-striped table-bordered table-condensed">
				<tr>
					<td width="50%">ID : '.$rh[ic_id].'</td>
					<td width="50%">Tanggal : '.$rh[tgl].'</td>
				</tr>
				<tr>
					<td>User Input : '.$rh[ic_user_create].'</td>
					<td>User Edit : '.$rh[ic_user_modify].'</td>
				</tr>
				<tr>
					<td>Tanggal Input : '.$rh[ic_date_create].'</td>
					<td>Tanggal Edit : '.$rh[ic_date_modify].'</td>
				</tr>
				<tr>
					<td>Apr. Kabag : '.$rh[ic_ap_kabag_user].' ( '.$rh[ic_ap_kabag_date].' )</td>
					<td>Apr. PM : '.$rh[ic_ap_pm_user].' ( '.$rh[ic_ap_pm_date].' )</td>
				</tr>
			</table>';
	$responce->hasil=$out;
    echo json_encode($responce);

}

function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant("TIDAKADA",true);
	$out .= $withselect ? "</select>" : "";
	echo $out;
}

function cbo_status($sts){
	$qry = array('' => '', 'Y' => 'APPROVE', 'N' => 'NOT APPROVE');
	return $qry[$sts];
}

?>