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

function urai(){
	global $app_plan_id;
	$subplan  = $_GET['subplan'];
	$fh_shift = $_GET['fh_shift'];
	$fh_kiln  = $_GET['fh_kiln'];

	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= "and a.fh_sub_plant = '".$subplan."'";
	}

	if($fh_kiln <> 'All') {
		$whdua .= "and a.fh_kiln = '".$fh_kiln."'";
	}

	if($fh_shift <> 'All') {
		$whdua .= "and a.fh_shift = '".$fh_shift."'";
	}


	$sql = "SELECT a.fh_sub_plant, a.fh_date, a.fh_kiln, a.fh_shift, b.fc_group, c.fc_desc, d.fc_gdparrent, b.fc_gdid, d.fc_gddesc,  '' as fhd_std, d.fc_gdunit, a.fh_id,  b.fhd_value
			from qc_fg_firing_header a 
			left join qc_fg_firing_detail b on a.fh_id = b.fh_id
			left join qc_fg_firing_group c on b.fc_group = c.fc_group
			left join qc_fg_firing_group_detail d 
				on a.fh_sub_plant = d.fc_sub_plant and b.fc_group = d.fc_group and b.fc_group = d.fc_group and b.fc_gdid = d.fc_gdid
			where a.fh_status = 'N' and a.fh_date >= '{$tglfrom}' and a.fh_date <= '{$tglto}' $whdua
			ORDER BY a.fh_sub_plant, a.fh_date, a.fh_kiln, a.fh_shift, b.fc_group, CAST(b.fc_gdid AS int), a.fh_id ASC";
	$responce->sql = $sql; 
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)) {
		foreach($qry as $r){
			$datetime = explode(' ',$r[fh_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			
			$arr_grup["$r[fc_group]"] = $r[fc_desc];

			$arr_nilai["$r[fh_sub_plant]"]["$r[tgl]"]["$r[fc_group]"]["$r[fc_gdparrent]"]["$r[fc_gdid]"]["$r[fh_kiln]"]["$r[fh_shift]"]["$r[fh_id]"] = $r[fhd_value];
			$arr_kolom["$r[fh_sub_plant]"]["$r[tgl]"]["$r[fc_group]"]["$r[fh_kiln]"]["$r[fh_shift]"] = $r[fh_shift];

			$arr_item["$r[fc_gdid]"] = $r[fc_gddesc].'##'.$r[fc_gdunit];

			

		}
	}


	if(is_array($arr_nilai)) {

		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
		$html .= '<div style="text-align:right;font-size:14px;font-weight:bold;">F.1003.QC.01</div>
				  <div style="text-align:center;font-size:20px;font-weight:bold;">QC FIRING & CALIBRO / PLANAR FINISH PRODUCT</div>
				  <table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';



		foreach ($arr_nilai as $plan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_grup) {
				$html .= '<div style="text-align:left;font-size:16px;font-weight:bold;">SUBPLAN '.$plan.' | '.$tgl.'</div>';
				$html .= '<div style="overflow-x:auto;"><table class="adaborder">';

				$ngrup =1;
				foreach ($a_grup as $grup => $a_item) {
					
					$jml_all_kol = 0;
					foreach($arr_kolom[$plan][$tgl][$grup] as $kiln => $a_shift){
						$jmlshift = count($a_shift);
						$jml_all_kol += $jmlshift;
					}

					$html .= '<tr>
								<th style="background:#D1D1D1;" colspan="'.(4+$jml_all_kol).'" class="text-left">'.$ngrup.'. '.$arr_grup[$grup].'</th>
							  </tr>';
			
					$html .= '<tr>';
					$html .= '<th rowspan="2" style="background:#D1D1D1;" width="50px;">NO.</th>';
					$html .= '<th rowspan="2" style="background:#D1D1D1;">DESCRIPSI</th>';
					$html .= '<th rowspan="2" style="background:#D1D1D1;">STANDAR</th>';
					$html .= '<th rowspan="2" style="background:#D1D1D1;">UNIT</th>';

					foreach($arr_kolom[$plan][$tgl][$grup] as $kiln2 => $a_shift2){
						$jmlshift2 = count($a_shift2);
						$html .= '<th style="background:#D1D1D1;" colspan="'.$jmlshift2.'">KILN '.Romawi($kiln2).'</th>';
					}

					$html .= '</tr>';

					$html .= '<tr>';
					foreach($arr_kolom[$plan][$tgl][$grup] as $kiln3 => $a_shift3){
						foreach($a_shift3 as $shift3){
							$html .= '<th style="background:#D1D1D1;">SHIFT '.Romawi($shift3).'</th>';
						}
					}
					$html .= '</tr>';




					$no_item = 1;
					foreach($a_item[0] as $fc_gdid => $a_kiln){

						$fc_gdidVal = explode('##', $arr_item[$fc_gdid]);
						$html .= '<tr>';
						$html .= '<th>'.$no_item.'</th>';
						$html .= '<td>'.$fc_gdidVal[0].'</td>';
						$html .= '<td>&nbsp;</td>';
						$html .= '<td class="text-center">'.$fc_gdidVal[1].'</td>';

						foreach($a_kiln as $kiln4 => $a_shift4){
							foreach($a_shift4 as $shift4 => $a_id){
								$html .= '<td class="text-center">';
								if(is_array($a_id)){
									foreach($a_id as $fh_id => $fhd_value){
										$html .= ' <span onclick="lihatData(\''.$fh_id.'\')">'.$fhd_value.'</span>';
									}
								}else{
									$html .= '&nbsp';
								}
								$html .= '</td>';
							}
						}


						$html .= '</tr>';


					$no_item++;


						if(is_array($a_item[$fc_gdid])){
							foreach($a_item[$fc_gdid] as $fc_gdid2 => $a_kiln5){
								$fc_gdidVal2 = explode('##', $arr_item[$fc_gdid2]);
								$html .= '<tr>';
								$html .= '<th>&nbsp;</th>';
								$html .= '<td>'.$fc_gdidVal2[0].'</td>';
								$html .= '<td>&nbsp;</td>';
								$html .= '<td class="text-center">'.$fc_gdidVal2[1].'</td>';

								foreach($a_kiln5 as $kiln5 => $a_shift5){
									foreach($a_shift5 as $shift5 => $a_id2){
										$html .= '<td class="text-center">';
										if(is_array($a_id2)){
											foreach($a_id2 as $fh_id2 => $fhd_value2){
												$html .= ' <span onclick="lihatData(\''.$fh_id2.'\')">'.$fhd_value2.'</span>';
											}
										}else{
											$html .= '&nbsp';
										}
										$html .= '</td>';

									}
								}
								$html .= '</tr>';
							}
						}

					}


					$html .= '<tr><th colspan="'.(4+$jml_all_kol).'">&nbsp;</th></tr>';

				$ngrup++;
				}

				$html .= '</table></div"><br>';

			}
				
		}

	} else {
		$html = 'TIDAKADA';
	}
	$responce->detailtabel = $html; 
	echo json_encode($responce);
}

function excel() {
	$html = 'Belum tersedia..';
	echo $html;
}

function lihatdata(){
	global $app_plan_id;
	$fh_id = $_POST['fh_id'];
	$sql = "SELECT * from qc_fg_firing_header WHERE fh_id = '{$fh_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$datetime = explode(' ',$rh[fh_date]);
	$rh[tgl] = cgx_dmy2ymd($datetime[0]);
	// $rh[jam] = substr($datetime[1],0,5);
	$rh[jam] = '';
			
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[fh_id].'</td><td>Di-input Oleh : '.$rh[fh_user_create].'</td></tr><tr><td>Subplant : '.$rh[fh_sub_plant].'</td><td>Tanggal Input : '.$rh[fh_date_create].'</td></tr><tr><td>Tanggal : '.$rh[tgl].'</td><td>Di-edit Oleh : '.$rh[fh_user_modify].'</td></tr><tr><td>Jam : '.$rh[jam].'</td><td>Tanggal Edit : '.$rh[fh_date_modify].'</td></tr></table>';
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