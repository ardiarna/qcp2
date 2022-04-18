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
}


function urai($excel = false){
	if($excel){
		$border = "border='1'";
	}else{
		$border = "";
	}
	global $app_plan_id;
	$subplan = $_GET['subplan'];
	$line 	 = $_GET['gqa_line'];

	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];

	$arr_shift['1'] = array("07" => "07", "08" => "08", "09" => "09", "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14");
	$arr_shift['2'] = array("15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19", "20" => "20", "21" => "21", "22" => "22");
	$arr_shift['3'] = array("23" => "23", "00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04", "05" => "05", "06" => "06");
	
	$whdua = "";
	if($subplan <> 'All') {
	    $whdua .= "and a.gqa_sub_plant = '".$subplan."'";
	}

	if($line <> 'All') {
	    $whdua .= "and a.gqa_line = '".$line."'";
	}

	$sql = "SELECT *
		FROM gl_qc_app a
		WHERE gqa_app_type <> 'R' and a.gqa_date >= '{$tglfrom}' and a.gqa_date <= '{$tglto}' $whdua
		ORDER BY a.gqa_sub_plant, a.gqa_line, a.gqa_date, a.gqa_app_type, a.gqa_reo ASC";

	$responce->sql = $sql; 
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)) {
	    foreach($qry as $r){
	        $datetime = explode(' ',$r[gqa_date]);
	        $r[tgl] = cgx_dmy2ymd($datetime[0]);
	        $r[jam] = substr($datetime[1],0,2);
	        $r[gqa_shift] = check_shift(substr($datetime[1],0,5));

	        $arr_nilai["$r[gqa_sub_plant]"]["$r[tgl]"]["$r[gqa_line]"]["$r[gqa_shift]"]["$r[jam]"]["$r[gqa_app_type]"]["$r[gqa_reo]"] = $r[gqa_reo_val];
	        $arr_header["$r[gqa_sub_plant]"]["$r[tgl]"]["$r[gqa_line]"]["$r[gqa_shift]"]["$r[jam]"] = $r[gqa_motif];
	        $arr_group["$r[gqa_app_type]"]["$r[gqa_reo]"] = $r[gqa_reo];
	    }
	}
	ksort($arr_group);
	if(is_array($arr_nilai)) {
		$arr_groupVal = array('E' => 'ENGOBE', 'G' => 'GLAZE', 'P' => 'PASTA', 'P2' => 'PASTA 2', 'P3' => 'PASTA 3', 'S' => 'SPRAY');
		$arr_seqVal = array('L' => 'LW', 'V' => 'VISCO', 'B' => 'BERAT');

		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
		$html .= '<div style="text-align:right;font-size:13px;font-weight:bold;"></div>
				  <div style="text-align:center;font-size:20px;font-weight:bold;">DATA KONTROL GLAZING LINE</div>
				  <div style="text-align:center;font-size:14px;font-weight:bold;">'.$tgljudul.'</div>';


		$jmlcolall = 3;
		foreach ($arr_group as $group => $a_seq) {
        	$jmlcolall += count($a_seq);
        }


		foreach ($arr_nilai as $subplan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_line) {
				foreach ($a_line as $line => $a_shift) {
					ksort($a_shift);

					$html .= '<div style="overflow-x:auto;"><table class="adaborder" '.$border.'>';
					$html .= '<tr>';
					$html .= '   <th class="text-left" colspan="'.$jmlcolall.'">SUB PLANT : '.$subplan.' | TANGGAL : '.$tgl.' | LINE : '.$line.'</th>';
					$html .= '</tr>';

					$html .= '<tr>';
	                $html .= '   <th align="center" rowspan="2">SHIFT</th>';
	                $html .= '   <th align="center" rowspan="2">JAM</th>';
	                $html .= '   <th align="center" rowspan="2">KODE PRODUKSI</th>';
	                foreach ($arr_group as $group => $a_seq) {
	                	$jmlcolgrp = count($a_seq);
	                	$html .= '   <th colspan="'.$jmlcolgrp.'">'.strtoupper($arr_groupVal[$group]).'</th>';
	                }
	                $html .= '</tr>';

	                $html .= '<tr>';
	                foreach ($arr_group as $group => $a_seq) {
	                	foreach ($a_seq as $seq) {
	                		$html .= '   <th>'.strtoupper($arr_seqVal[$seq]).'</th>';
	                	}
	                }
	                $html .= '</tr>';

	                foreach ($arr_shift as $shift => $arr_jam) {
	                // foreach ($a_shift as $shift => $a_jam) {
	                	$html .= '<tr>';
						$html .= '   <th>'.Romawi($shift).'</th>';
						$lopcell = $jmlcolall-1;
						for ($i=1; $i <= $lopcell; $i++) { 
							$html .= '   <th>&nbsp;</th>';
						}
						$html .= '</tr>';

						foreach ($arr_jam as $jam => $jam_lbl) {
						// foreach ($a_jam as $jam => $a_group) {

		                	$html .= '<tr>';
							$html .= '   <th>&nbsp;</th>';
							$html .= '   <th>'.$jam.':00</th>';
							$html .= '   <td align="center">'.$arr_header[$subplan][$tgl][$line][$shift][$jam].'</td>';
							
							foreach ($arr_group as $group => $a_seq) {
			                	foreach ($a_seq as $seq) {
									$html .= '   <td align="center">'.$arr_nilai[$subplan][$tgl][$line][$shift][$jam][$group][$seq].'</td>';
			                	}
			                }
							$html .= '</tr>';
		                }
	                }

					$html .= '</table></div>';
					$html .= '<br>';
				}
			}	
		}
	}else{
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
	header("Content-type: application/x-msexcel"); 
	header('Content-Disposition: attachment; filename="Data_kontrol_glazing_line.xls"');
	echo urai(true);
}

function lihatdata(){
	global $app_plan_id;
	$gqa_id = $_POST['gqa_id'];
	$sql = "SELECT * from qc_gl_header where gqa_id = '{$gqa_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$datetime = explode(' ',$rh[gqa_date]);
	$rh[tgl] = cgx_dmy2ymd($datetime[0]);
	$rh[time] = substr($datetime[1],0,5);
			
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[gqa_id].'</td><td>Di-input Oleh : '.$rh[qgh_user_create].'</td></tr><tr><td>Subplant : '.$rh[qgh_sub_plant].'</td><td>Tanggal Input : '.$rh[gqa_date_create].'</td></tr><tr><td>Tanggal : '.$rh[tgl].'</td><td>Di-edit Oleh : '.$rh[qgh_user_modify].'</td></tr><tr><td>Jam : '.$rh[time].'</td><td>Tanggal Edit : '.$rh[gqa_date_modify].'</td></tr></table>';
	$responce->hasil=$out;
    echo json_encode($responce);

}






?>