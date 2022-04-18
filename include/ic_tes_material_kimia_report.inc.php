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
	header('Content-Disposition: attachment; filename="HASIL_TEST_MATERIAL_KIMIA.xls"');
	echo urai(true);
}

function urai($excel = false){
	if($excel){
		$border = "border='1'";
	}else{
		$border = "";
	}

	global $app_plan_id, $nama_plan;
	$subplan  = $_GET['subplan'];
	$tanggal  = explode('@', $_GET['tanggal']);
	$tglfrom  = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto 	  = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and ic_sub_plant = '".$subplan."'";
	}

	$sql = "SELECT * FROM qc_ic_teskimia_data WHERE ic_rec_stat = 'N' AND ic_date >= '{$tglfrom}' and ic_date <= '{$tglto}' $whdua 
			ORDER BY ic_sub_plant, ic_date, ic_id ASC";


	$sql = "SELECT a.ic_sub_plant, a.ic_id, a.ic_date, a.berat, a.flatness, a.pinhole, a.glossy, 
				   b.ic_kd_material, b.ic_nm_material, b.ic_lw, b.ic_visco, c.subkon_name 
			FROM qc_ic_teskimia_data a 
			LEFT JOIN qc_ic_kebasahan_data b ON a.ic_idmasuk = b.ic_id
			LEFT JOIN qc_md_subkon c ON b.ic_sub_kontraktor = c.subkon_id
			WHERE a.ic_rec_stat = 'N' AND a.ic_date >= '{$tglfrom}' and a.ic_date <= '{$tglto}'
			ORDER BY a.ic_sub_plant, a.ic_date, a.ic_id, b.ic_kd_material ASC";

	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)){
		foreach($qry as $r){
			$datetime = explode(' ',$r[ic_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			$r[jam] = substr($datetime[1],0,5);

			$arr_nilai["$r[ic_sub_plant]"]["$r[tgl]"]["$r[ic_id]"] = $r[ic_kd_material].'@@@'.$r[ic_nm_material].'@@@'.$r[subkon_name].'@@@'.$r[ic_lw].'@@@'.$r[ic_visco].'@@@'.$r[berat].'@@@'.$r[glossy].'@@@'.$r[flatness].'@@@'.$r[pinhole].'@@@'.$r[keterangan].'@@@'.$r[kesimpulan]; 
		}
	}

	if(is_array($arr_nilai)) {
		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;} .str{ mso-number-format:\@; } </style>';
		$html .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.1001.QC.03</div>
				  <div style="text-align:center;font-size:20px;font-weight:bold;">HASIL TEST MATERIAL KIMIA</div>
				  <div style="text-align:center;font-size:14px;font-weight:bold;">TGL : '.$tgljudul.'</div><br>';
		
		foreach ($arr_nilai as $subplan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_id) {
				$html .= '<div style="overflow-x:auto;"><table class="adaborder" id="tbl01" '.$border.'>';
				$html .= '<tr><th colspan="12" style="background: #D1D1D1;text-align:left;">SUBPLANT : '.$subplan.' | '.$tgl.'</th></tr>';
				$html .= '<tr>
							<th width="30px;">NO</th>
							<th width="120px;">KODE MATERIAL</th>
							<th width="250px;">NAMA MATERIAL</th>
							<th width="150px;">SUB KONTRAKTOR</th>
							<th width="80px;">LW</th>
							<th width="80px;">VISCO</th>
							<th width="80px;">BERAT</th>
							<th width="80px;">GLOSSY</th>
							<th width="80px;">FLATNESS</th>
							<th width="80px;">PIN HOLE</th>
							<th width="80px;">KETERANGAN</th>
							<th width="80px;">KESIMPULAN</th>
						 </tr>';

				$no =1;
				foreach ($a_id as $id => $val1) {
					$val = explode('@@@', $val1);

					$val[6] = $val[6] == 'Y' ? $val[6] = 'OK' : $val[6] = $val[6] = 'NOT OK';
					$val[7] = $val[7] == 'Y' ? $val[7] = 'OK' : $val[7] = $val[7] = 'NOT OK';
					$val[8] = $val[8] == 'Y' ? $val[8] = 'OK' : $val[8] = $val[8] = 'NOT OK';



					$html .= '<tr>
								<td align="center">'.$no.'</td>
								<td align="center"><span onclick="lihatData(\''.$id.'\')">'.$val[0].'</span></td>
								<td align="left"><span onclick="lihatData(\''.$id.'\')">'.$val[1].'</span></td>
								<td align="left"><span onclick="lihatData(\''.$id.'\')">'.$val[2].'</span></td>
								<td align="center"><span onclick="lihatData(\''.$id.'\')">'.$val[3].'</span></td>
								<td align="center"><span onclick="lihatData(\''.$id.'\')">'.$val[4].'</span></td>
								<td align="center"><span onclick="lihatData(\''.$id.'\')">'.$val[5].'</span></td>
								<td align="center"><span onclick="lihatData(\''.$id.'\')">'.$val[6].'</span></td>
								<td align="center"><span onclick="lihatData(\''.$id.'\')">'.$val[7].'</span></td>
								<td align="center"><span onclick="lihatData(\''.$id.'\')">'.$val[8].'</span></td>
								<td align="center"><span onclick="lihatData(\''.$id.'\')">'.$val[9].'</span></td>
								<td align="left"><span onclick="lihatData(\''.$id.'\')">'.$val[10].'</span></td>
							 </tr>';
				$no++;
				}		 

				$html .='</table></div><br>';
			}
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
	$sql = "SELECT * FROM qc_ic_teskimia_data WHERE ic_id = '{$kode}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$datetime = explode(' ',$rh[ic_date]);
	$rh[tgl] = cgx_dmy2ymd($datetime[0]);

	$out = '<table class="table table-striped table-bordered table-condensed">
				<tr>
					<td width="50%">ID : '.$rh[ic_id].'</td>
					<td width="50%">Subplant : '.$rh[ic_sub_plant].'</td>
				</tr>
				<tr>
					<td>Tanggal : '.$rh[tgl].'</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>User Input : '.$rh[ic_user_create].'</td>
					<td>User Edit : '.$rh[ic_user_modify].'</td>
				</tr>
				<tr>
					<td>tanggal Input : '.$rh[ic_date_create].'</td>
					<td>tanggal Edit : '.$rh[ic_date_modify].'</td>
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



?>