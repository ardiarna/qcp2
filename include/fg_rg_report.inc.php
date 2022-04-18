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

function urai($excel = false){
	global $app_plan_id;

	if($excel){
		$border = "border='1'";
	}else{
		$border = "";
	}


	$arr_qly = array('1' => 'Export', '2' => 'Ekonomi', '4' => 'Reject Sortir', '5' => 'Reject Pallet', '6' => 'Reject Buang');
	$subplan = $_GET['subplan'];
	$shift   = $_GET['shift'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= "and a.rg_sub_plant = '".$subplan."'";
	}

	if($shift <> 'All') {
		$whdua .= "and a.rg_shift = '".$shift."'";
	}

	$sql = "SELECT a.rg_sub_plant AS subplan, a.rg_id, a.rg_date, a.rg_shift, a.rg_line, b.rg_qly, b.rg_per_2h, b.rg_shading, b.rg_size, b.rg_calibro, b.rg_desc, c.qmd_nama as defect_nama
			FROM qc_fg_rg_header a 
			JOIN qc_fg_rg_detail b on a.rg_id = b.rg_id
			LEFT JOIN qc_md_defect c on b.rg_defect_kode = c.qmd_kode
			WHERE a.rg_status='N' $whdua
			AND a.rg_date >= '{$tglfrom}' 
			AND a.rg_date <= '{$tglto}' 
			ORDER BY a.rg_sub_plant, a.rg_date, a.rg_line, a.rg_id, b.rg_qly ASC";
	$responce->sql = $sql; 
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$arr_n_rg_per_2h  = array();
	if(is_array($qry)) {
		foreach($qry as $r){
			$datetime = explode(' ',$r[rg_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			$r[jam] = substr($datetime[1],0,2);

			$arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[rg_line]"]["$r[rg_shift]"]["$r[jam]"]["$r[rg_qly]"]["$r[rg_id]"] = $r[rg_id];
			$arr_sum2h["$r[subplan]"]["$r[tgl]"]["$r[rg_line]"]["$r[rg_shift]"]["$r[jam]"] += $r[rg_per_2h];
			$arr_sumcalibro["$r[subplan]"]["$r[tgl]"]["$r[rg_line]"]["$r[rg_shift]"]["$r[jam]"] += $r[rg_calibro];
			$arr_n_rg_per_2h["$r[rg_id]"]["$r[rg_qly]"]  += $r[rg_per_2h];
			$arr_n_rg_shading["$r[rg_id]"]["$r[rg_qly]"] = $r[rg_shading];
			$arr_n_rg_size["$r[rg_id]"]["$r[rg_qly]"]    = $r[rg_size];
			$arr_n_rg_calibro["$r[rg_id]"]["$r[rg_qly]"] = $r[rg_calibro];
			$arr_n_rg_desc["$r[rg_id]"]["$r[rg_qly]"]    = $r[rg_desc];
			$arr_defect["$r[rg_id]"]["$r[rg_qly]"]["$r[defect_nama]"]  += $r[rg_per_2h];
			$arr_sumaccum["$r[subplan]"]["$r[tgl]"]["$r[rg_line]"]["$r[rg_shift]"] += $r[rg_per_2h];

		}
	}

	if(is_array($arr_nilai)) {

		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}.str{ mso-number-format:\@; }</style>';
		$html .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.1003.QC.03</div>
				  <div style="text-align:center;font-size:20px;font-weight:bold;">DATA RENDEMENT GROUP</div>
				  <table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
		
		foreach ($arr_nilai as $subplan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_line) {
				foreach ($a_line as $line => $a_shift) {
					foreach ($a_shift as $shift => $a_jam) {
						$accum = array();
						$jmjam = count($a_jam);
						$html .= '<div style="overflow-x:auto;"><table class="adaborder" '.$border.'>';
						$html .= '<tr><th colspan="3" style="text-align:center;background: #D1D1D1;">SUBPLANT : '.$subplan.'</th>
								  	<th colspan="3" style="text-align:center;background: #D1D1D1;">TGL : '.$tgl.'</th>
								  	<th colspan="2" style="text-align:center;background: #D1D1D1;">LINE : '.$line.'</th>
								  	<th colspan="2" style="text-align:center;background: #D1D1D1;">SHIFT : '.Romawi($shift).'</th>
							 	 </tr>';
						$html .= '<tr>
									  <th rowspan="2" width="50">JAM</th>
									  <th rowspan="2" width="70">QLY</th>
									  <th colspan="4">TOTAL</th>
									  <th rowspan="2" width="100">SHADING</th>
									  <th rowspan="2" width="100">SIZE</th>
									  <th rowspan="2" width="100">CALIBRO</th>
									  <th rowspan="2">KETERANGAN</th>
								  </tr>';
						$html .= '<tr>';
						$html .= '<th width="100">ACCUM</th>';
						$html .= '<th width="100">%</th>';
						$html .= '<th width="100">QTY</th>';
						$html .= '<th width="100">%</th>';
						$html .= '</tr>';

						$urutjam = 1;
						foreach ($a_jam as $jam => $a_qly) {

							$ttl2hour   = 0;
							$ttlcalibro = 0;

							$no=1;
							foreach ($a_qly as $qly => $a_id) {
								if($no == 1){
									$jamVal = $jam;
								}else{
									$jamVal = '&nbsp;';
								}
								$html .= '<tr>';
								$html .= '<td class="text-center">'.$jamVal.'</td>';
								$html .= '<td class="text-left">'.$arr_qly[$qly].'</td>';

								if(is_array($a_id)) {
									foreach ($a_id as $id => $val) {
										$per2hn = $arr_n_rg_per_2h[$id][$qly];
										if($per2hn <= 0){
											$per2hVal = '';
										}else{
											$per2hVal = $per2hn;
										}

										$accum[$qly] += $per2hn;

										$shadingVal = $arr_n_rg_shading[$id][$qly];
										$sizeVal    = $arr_n_rg_size[$id][$qly];
										$calibroVal = $arr_n_rg_calibro[$id][$qly];
										if($arr_n_rg_desc[$id][$qly]) {
											$descVal    = $arr_n_rg_desc[$id][$qly];
										}else if(is_array($arr_defect[$id][$qly])) {
											$descVal = "";
											foreach ($arr_defect[$id][$qly] as $defect_nama => $nil) {
												if($defect_nama) {
													$descVal .= $defect_nama." ".$nil.", ";
												}
											}

											$descVal = substr($descVal, 0, strlen($descVal)-2);
										}
										$per2h   = ' <span onclick="lihatData(\''.$id.'\')">'.$per2hVal.'</span>';
										$shading = ' <span onclick="lihatData(\''.$id.'\')">'.$shadingVal.'</span>';
										$size    = ' <span onclick="lihatData(\''.$id.'\')">'.$sizeVal.'</span>';
										$calibro = ' <span onclick="lihatData(\''.$id.'\')">'.$calibroVal.'</span>';
										$desc    = ' <span onclick="lihatData(\''.$id.'\')">'.$descVal.'</span>';
									}
								}

								$ttl2hour = $arr_sum2h[$subplan][$tgl][$line][$shift][$jam];
								if($per2hVal == ''){
									$persenper2h = '';
								}else{
									$persenper2h = ($per2hVal/$ttl2hour)*100;
									$persenper2h = number_format($persenper2h,1);

								}

								$ttlcalibro = $arr_sumcalibro[$subplan][$tgl][$line][$shift][$jam];
								

								if($jmjam > 1 && $urutjam == 1){
									$accumVal = "";
								}else{
									$accumVal = $accum[$qly];  
								}

								if($jmjam == $urutjam){
									
									$ttlaccum = $arr_sumaccum[$subplan][$tgl][$line][$shift];
									$persenaccum    = ($accumVal/$ttlaccum)*100;
									if($persenaccum == 100){
										$persenaccumVal = 100;
									}else{
										$persenaccumVal = number_format($persenaccum,1);
									}	
								}else{
									$persenaccumVal = "";
									$ttlaccum = '';
								}


								$html .= '<td class="text-right">'.$accumVal.'</td>';
								$html .= '<td class="text-center">'.$persenaccumVal.'</td>';
								$html .= '<td class="text-right">'.$per2h.'</td>';
								$html .= '<td class="text-center">'.$persenper2h.'</td>';
								$html .= '<td class="text-center">'.$shading.'</td>';
								$html .= '<td class="text-center">'.$size.'</td>';
								$html .= '<td class="text-right">'.$calibro.'</td>';
								$html .= '<td>'.$desc.'</td>';
								$html .= '</tr>';
							$no++;
							}

								if($ttl2hour == 0){
									$ttl2hour = '';
								}else{
									$ttl2hour = $ttl2hour;
								}

								if($ttlcalibro == 0){
									$ttlcalibro = '';
								}else{
									$ttlcalibro = $ttlcalibro;
								}

								$html .= '<tr>';
								$html .= '<th style="background: #D1D1D1;">&nbsp;</th>';
								$html .= '<th class="text-center" style="background: #D1D1D1;">TOTAL</th>';
								$html .= '<th class="text-right" style="background: #D1D1D1;">'.$ttlaccum.'</th>';
								$html .= '<th style="background: #D1D1D1;">&nbsp;</th>';
								$html .= '<th class="text-right" style="background: #D1D1D1;">'.$ttl2hour.'</th>';
								$html .= '<th style="background: #D1D1D1;">&nbsp;</th>';
								$html .= '<th style="background: #D1D1D1;">&nbsp;</th>';
								$html .= '<th style="background: #D1D1D1;">&nbsp;</th>';
								$html .= '<th class="text-right" style="background: #D1D1D1;">'.$ttlcalibro.'</th>';
								$html .= '<th style="background: #D1D1D1;">&nbsp;</th>';
								$html .= '</tr>';

								$html .= '<tr>';
								$html .= '<td colspan="10">&nbsp;</td>';
								$html .= '</tr>';
						
						$urutjam++;
						}

						$html .='</table></div><br><br>';
					}
				}
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

function excel() {

	$tanggal = str_replace('@', ' s/d ', $_GET['tanggal']);

	header("Content-type: application/x-msexcel"); 
	header('Content-Disposition: attachment; filename="RENDEMENT_GROUP('.$tanggal.').xls"');
	echo urai(true);
}

function lihatdata(){
	global $app_plan_id;
	$rg_id = $_POST['rg_id'];
	$sql = "SELECT * from qc_fg_rg_header WHERE rg_id = '{$rg_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$datetime = explode(' ',$rh[rg_date]);
	$rh[tgl] = cgx_dmy2ymd($datetime[0]);
	$rh[jam] = substr($datetime[1],0,5);
			
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[rg_id].'</td><td>Di-input Oleh : '.$rh[rg_user_create].'</td></tr><tr><td>Subplant : '.$rh[rg_sub_plant].'</td><td>Tanggal Input : '.$rh[rg_date_create].'</td></tr><tr><td>Tanggal : '.$rh[tgl].'</td><td>Di-edit Oleh : '.$rh[rg_user_modify].'</td></tr><tr><td>Jam : '.$rh[jam].'</td><td>Tanggal Edit : '.$rh[rg_date_modify].'</td></tr></table>';
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