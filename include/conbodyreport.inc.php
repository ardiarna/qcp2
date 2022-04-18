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

function urai() {
	$v_subplan = $_GET['subplan'];
	if($v_subplan <> 'All') {
		$arr_subplan = array($v_subplan);
	} else {
		$arr_subplan = array('A','B','C');
	}
	$tanggal = explode('@', $_GET['tanggal']);
	$begin = new DateTime(cgx_dmy2ymd($tanggal[0]));
	$end   = new DateTime(cgx_dmy2ymd($tanggal[1]));
	$arr_tgl = array();
	for($i = $begin; $i <= $end; $i->modify('+1 day')){
		$arr_tgl[] = $i->format("Y-m-d");;
	}

	$out = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
	$out .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.1002.QC.02</div><div style="text-align:center;font-size:20px;font-weight:bold;">CONTROL BODY</div>';
	$out .= '<div style="overflow-x:auto;">';
	foreach ($arr_subplan as $subplan) {
		$out .= '<br><div style="font-size:18px;font-weight:bold;">SUBPLANT : '.$subplan.'</div>';
		foreach ($arr_tgl as $tgla) {
			$out .= '<br><div style="font-size:12px;font-weight:bold;">Tanggal : '.cgx_dmy2ymd($tgla).'</div><br>';
			$tglfrom = $tgla." 00:00:00";
			$tglto = $tgla." 23:59:59";
			$out .= urai_01($subplan, $tglfrom, $tglto);
			$out .= '<br><table>
				<tr>
					<td style="vertical-align:top">'.urai_02($subplan, $tglfrom, $tglto).'</td>
					<td style="vertical-align:top">'.urai_03($subplan, $tglfrom, $tglto).'</td>
					<td style="vertical-align:top">'.urai_04($subplan, $tglfrom, $tglto).'</td>
					<td style="vertical-align:top">'.urai_05($subplan, $tglfrom, $tglto).'</td>
				</tr>
				</table><br>';	
		}
	}
	$out .='</div>';
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

function urai_01($subplan, $tglfrom, $tglto){
	global $app_plan_id;
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.qch_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.qch_sub_plant as subplan, a.qch_shift as shift, a.qch_bm_no as balmil, b.qcd_prep_seq as line, c.qcpd_control_desc as item_nama, b.qcd_prep_value as nilai, a.qch_id
		from qc_cb_header a
		join qc_cb_detail b on(a.qch_id=b.qch_id)
		join qc_cb_prep_detail c on(b.qcd_prep_group=c.qcpd_group and b.qcd_prep_seq=c.qcpd_seq)
		where a.qch_rec_stat='N' and b.qcd_prep_group='01' and a.qch_date >= '{$tglfrom}' and a.qch_date <= '{$tglto}' and b.qcd_prep_value is not null and b.qcd_prep_value <> '' $whdua
		order by a.qch_sub_plant, a.qch_shift, a.qch_bm_no, b.qcd_prep_seq";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)){
		foreach($qry as $r){
			$arr_kolom["$r[subplan]"]["$r[line]"] = $r[item_nama];
			$arr_nilai["$r[subplan]"]["$r[shift]"]["$r[balmil]"]["$r[qch_id]"]["$r[line]"] = $r[nilai];
		}
	}
	if(is_array($arr_nilai)) {
		$html .= '<table class="adaborder">';
		foreach ($arr_nilai as $subplan => $a_shift) {
			$html .= '<tr><th>Shift</th><th>Ball Mill</th>';
	        ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $line => $item_nama) {
        		$html .= '<th>'.$item_nama.'</th>';
        	}
        	$html .= '</tr>';
        	foreach ($a_shift as $shift => $a_balmil) {
				foreach ($a_balmil as $balmil => $a_qch_id) {
					foreach ($a_qch_id as $qch_id => $a_line) {
						$html .='<tr><td style="text-align:center;white-space:nowrap">Shift '.$shift.'</td><td style="text-align:center;white-space:nowrap">'.$balmil.'</td>';
						ksort($arr_kolom[$subplan]);
						reset($arr_kolom[$subplan]);
			        	foreach ($arr_kolom[$subplan] as $line => $item_nama) {
			        		$nilai = $a_line[$line];
			        		if($nilai) {
			        			if($line == '2') {
			        				$html .= '<td onclick="lihatData(\''.$qch_id.'\')">'.$nilai.'</td>';
			        			} else if($line == '6') {
			        				$html .= '<td style="text-align:center;" onclick="lihatData(\''.$qch_id.'\')">'.$nilai.'</td>';
			        			} else if($line == '1' || $line == '3' || $line == '5' || $line == '10' || $line == '11') {
			        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$qch_id.'\')">'.number_format($nilai).'</td>';
			        			} else {
			        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$qch_id.'\')">'.number_format($nilai,2).'</td>';
			        			}
		        			} else {
		        				$html .= '<td></td>';
		        			}
			        	}
			        	$html .= '</tr>';
					}
				}
			}
		}
		$html .='</table>';
	} else {
		$html .= '';
	}	
	return $html;
}

function urai_02($subplan, $tglfrom, $tglto){
	global $app_plan_id;
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.qch_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.qch_sub_plant as subplan, b.qcd_prep_seq as line, c.qcpd_control_desc as item_nama, a.qch_shift as shift, b.qcd_prep_value as nilai, a.qch_id
		from qc_cb_header a
		join qc_cb_detail b on(a.qch_id=b.qch_id)
		join qc_cb_prep_detail c on(b.qcd_prep_group=c.qcpd_group and b.qcd_prep_seq=c.qcpd_seq)
		where a.qch_rec_stat='N' and b.qcd_prep_group='02' and a.qch_date >= '{$tglfrom}' and a.qch_date <= '{$tglto}' and b.qcd_prep_value is not null and b.qcd_prep_value <> '' $whdua
		order by a.qch_sub_plant, b.qcd_prep_seq, a.qch_shift";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)){
		foreach($qry as $r){
			$arr_kolom["$r[subplan]"]["$r[shift]"]["$r[qch_id]"] = '';
			$arr_nilai["$r[subplan]"]["$r[line]"]["$r[shift]"]["$r[qch_id]"] = $r[nilai];
			$arr_item_nama["$r[line]"] = $r[item_nama];
		}
	}
	if(is_array($arr_nilai)) {
		foreach ($arr_nilai as $subplan => $a_line) {
			foreach ($arr_kolom[$subplan] as $shift => $a_qch_id) {
        		$arr_tot_kol[$subplan] += count($a_qch_id);
        	}
		}
		$html .= '<table class="adaborder">';
		foreach ($arr_nilai as $subplan => $a_line) {
			$html .= '<tr><th colspan="'.($arr_tot_kol[$subplan]+1).'">PARTICLE SIZE</th></tr>';
			$html .= '<tr><th>Description</th>';
	        ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $shift => $a_qch_id) {
        		foreach ($a_qch_id as $qch_id => $value) {
        			$html .= '<th>Shift '.$shift.'</th>';
        		}
        	}
        	$html .= '</tr>';
        	foreach ($a_line as $line => $a_shift) {
				$html .='<tr><td style="white-space:nowrap">'.$arr_item_nama[$line].'</td>';
				ksort($arr_kolom[$subplan]);
				reset($arr_kolom[$subplan]);
	        	foreach ($arr_kolom[$subplan] as $shift => $a_qch_id) {
	        		foreach ($a_qch_id as $qch_id => $value) {
	        			$nilai = $a_shift[$shift][$qch_id];
	        			if($nilai) {
		        			$html .= '<td style="text-align:right;" onclick="lihatData(\''.$qch_id.'\')">'.number_format($nilai,2).'</td>';
	        			} else {
	        				$html .= '<td></td>';
	        			}
	        		}
	        	}
	        	$html .= '</tr>';
			}
		}
		$html .='</table>';
	} else {
		$html .= '';
	}
	return $html;
}

function urai_03($subplan, $tglfrom, $tglto){
	global $app_plan_id;
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.qch_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.qch_sub_plant as subplan, a.qch_shift as shift, b.qcd_prep_seq as line, c.qcpd_control_desc as item_nama, b.qcd_prep_value as nilai, a.qch_id
		from qc_cb_header a
		join qc_cb_detail b on(a.qch_id=b.qch_id)
		join qc_cb_prep_detail c on(b.qcd_prep_group=c.qcpd_group and b.qcd_prep_seq=c.qcpd_seq)
		where a.qch_rec_stat='N' and b.qcd_prep_group='03' and a.qch_date >= '{$tglfrom}' and a.qch_date <= '{$tglto}' and b.qcd_prep_value is not null and b.qcd_prep_value <> '' $whdua
		order by a.qch_sub_plant, a.qch_shift, b.qcd_prep_seq";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)){
		foreach($qry as $r){
			$arr_kolom["$r[subplan]"]["$r[line]"] = $r[item_nama];
			$arr_nilai["$r[subplan]"]["$r[shift]"]["$r[qch_id]"]["$r[line]"] = $r[nilai];
		}
	}
	if(is_array($arr_nilai)) {
		$html .= '<table class="adaborder">';
		foreach ($arr_nilai as $subplan => $a_shift) {
			$html .= '<tr><th rowspan="2">Shift</th><th colspan="3">DAILY TANK</th></tr><tr>';
	        ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $line => $item_nama) {
        		$html .= '<th>'.$item_nama.'</th>';
        	}
        	$html .= '</tr>';
        	foreach ($a_shift as $shift => $a_qch_id) {
				foreach ($a_qch_id as $qch_id => $a_line) {
					$html .='<tr><td style="text-align:center;white-space:nowrap">Shift '.$shift.'</td>';
					ksort($arr_kolom[$subplan]);
					reset($arr_kolom[$subplan]);
		        	foreach ($arr_kolom[$subplan] as $line => $item_nama) {
		        		$nilai = $a_line[$line];
		        		if($nilai) {
		        			if($line == '3') {
		        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$qch_id.'\')">'.number_format($nilai,2).'</td>';
		        			} else {
		        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$qch_id.'\')">'.number_format($nilai).'</td>';
		        			} 
	        			} else {
	        				$html .= '<td></td>';
	        			}
		        	}
		        	$html .= '</tr>';
				}
			}
		}
		$html .='</table>';
	} else {
		$html .= '';
	}	
	return $html;
}

function urai_04($subplan, $tglfrom, $tglto){
	global $app_plan_id;
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.qch_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.qch_sub_plant as subplan, b.qcd_silo_no as silo, a.qch_shift as shift, b.qcd_prep_value as nilai, a.qch_id
		from qc_cb_header a
		join qc_cb_detail b on(a.qch_id=b.qch_id)
		where a.qch_rec_stat='N' and b.qcd_prep_group='04' and a.qch_date >= '{$tglfrom}' and a.qch_date <= '{$tglto}' and b.qcd_prep_value is not null and b.qcd_prep_value <> '' $whdua
		order by a.qch_sub_plant, b.qcd_silo_no, a.qch_shift";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)){
		foreach($qry as $r){
			$arr_kolom["$r[subplan]"]["$r[shift]"]["$r[qch_id]"] = '';
			$arr_nilai["$r[subplan]"]["$r[silo]"]["$r[shift]"]["$r[qch_id]"] = $r[nilai];
		}
	}
	if(is_array($arr_nilai)) {
		foreach ($arr_nilai as $subplan => $a_silo) {
			foreach ($arr_kolom[$subplan] as $shift => $a_qch_id) {
        		$arr_tot_kol[$subplan] += count($a_qch_id);
        	}
		}
		$html .= '<table class="adaborder">';
		foreach ($arr_nilai as $subplan => $a_silo) {
			$html .= '<tr><th colspan="'.($arr_tot_kol[$subplan]+1).'">STOCK POWDER</th></tr>';
			$html .= '<tr><th>SILO</th>';
	        ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $shift => $a_qch_id) {
        		$html .= '<th>Shift '.$shift.'</th>';
        	}
        	$html .= '</tr>';
        	foreach ($a_silo as $silo => $a_shift) {
				$html .='<tr><td style="text-align:center;white-space:nowrap">'.$silo.'</td>';
				ksort($arr_kolom[$subplan]);
				reset($arr_kolom[$subplan]);
	        	foreach ($arr_kolom[$subplan] as $shift => $a_qch_id) {
		        	$html .= '<td style="text-align:right;">';
		        	if(is_array($a_qch_id)) {
		        		foreach ($a_qch_id as $qch_id => $value) {
		        			$nilai = $a_shift[$shift][$qch_id];
		        			if($nilai) {
			        			$html .= '<span onclick="lihatData(\''.$qch_id.'\')">'.number_format($nilai).'</span> ';
			        			$tot_nilai_kolom[$shift] += round($nilai);
		        			} else {
		        				$html .= '';
		        			}
		        		}
		        	} else {
		        		$html .= '&nbsp;';
		        	}
		        	$html .= '</td>';
	        	}
	        	$html .= '</tr>';
			}
			$html .= '<tr><td style="text-align:center;font-weight:bold;">Total</td>';
			ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $shift => $a_qch_id) {
        		$html .= '<td style="text-align:right;font-weight:bold;">'.number_format($tot_nilai_kolom[$shift]).'</td>';
        	}
		}
		$html .='</table>';
	} else {
		$html .= '';
	}
	return $html;
}

function urai_05($subplan, $tglfrom, $tglto){
	global $app_plan_id;
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.qch_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.qch_sub_plant as subplan, b.qcd_slip_no as slip, a.qch_shift as shift, b.qcd_prep_value as nilai, a.qch_id, b.qcd_prep_seq as line, c.qcpd_control_desc as line_ket, d.qct_desc as slip_ket
		from qc_cb_header a
		join qc_cb_detail b on(a.qch_id=b.qch_id)
		join qc_cb_prep_detail c on(b.qcd_prep_group=c.qcpd_group and b.qcd_prep_seq=c.qcpd_seq)
		join qc_cb_slip_tank d on(b.qcd_slip_no=d.qct_code and a.qch_sub_plant=d.qct_sub_plant)
		where a.qch_rec_stat='N' and b.qcd_prep_group='05' and a.qch_date >= '{$tglfrom}' and a.qch_date <= '{$tglto}' and b.qcd_prep_value is not null and b.qcd_prep_value <> '' $whdua
		order by a.qch_sub_plant, b.qcd_slip_no, a.qch_shift, b.qcd_prep_seq";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)){
		foreach($qry as $r){
			$arr_kolom["$r[subplan]"]["$r[shift]"]["$r[line]"]["$r[qch_id]"] = '';
			$arr_nilai["$r[subplan]"]["$r[slip]"]["$r[shift]"]["$r[line]"]["$r[qch_id]"] = $r[nilai];
			$arr_line_ket["$r[line]"] = $r[line_ket];
			$arr_slip_ket["$r[slip]"] = $r[slip_ket];
		}
	}
	if(is_array($arr_nilai)) {
		foreach ($arr_nilai as $subplan => $a_silo) {
			foreach ($arr_kolom[$subplan] as $shift => $a_line) {
				$arr_kol_shift[$subplan][$shift] += count($a_line);
				$arr_tot_kol[$subplan] += count($a_line);
        	}
		}
		$html .= '<table class="adaborder">';
		foreach ($arr_nilai as $subplan => $a_slip) {
			$html .= '<tr><th colspan="'.($arr_tot_kol[$subplan]+1).'">STOCK SLIP</th></tr>';
			$html .= '<tr><th rowspan="2">NO TANK</th>';
	        ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $shift => $a_line) {
        		$html .= '<th colspan="'.$arr_kol_shift[$subplan][$shift].'">Shift '.$shift.'</th>';
        	}
        	$html .= '</tr><tr>';
        	ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $shift => $a_line) {
        		ksort($a_line);
        		reset($a_line);
        		foreach ($a_line as $line => $a_qch_id) {
        			$html .= '<th>'.$arr_line_ket[$line].'</th>';
        		}
        	}
        	$html .= '</tr>';
        	foreach ($a_slip as $slip => $a_shift) {
				$html .='<tr><td style="text-align:center;white-space:nowrap">'.$arr_slip_ket[$slip].'</td>';
				ksort($arr_kolom[$subplan]);
				reset($arr_kolom[$subplan]);
	        	foreach ($arr_kolom[$subplan] as $shift => $a_line) {
	        		ksort($a_line);
        			reset($a_line);
					foreach ($a_line as $line => $a_qch_id) {
						$html .= '<td style="text-align:right;">';
						if(is_array($a_qch_id)) {
							foreach ($a_qch_id as $qch_id => $value) {
			        			$nilai = $a_shift[$shift][$line][$qch_id];
			        			if($nilai) {
				        			$html .= '<span onclick="lihatData(\''.$qch_id.'\')">'.number_format($nilai).'</span> ';
				        			$tot_nilai_kolom[$shift][$line] += round($nilai);
			        			} else {
			        				$html .= '';
			        			}
			        		}
						} else {
							$html .= '&nbsp;';
						}	
		        		$html .= '</td>';
					}
	        	}
	        	$html .= '</tr>';
			}
			$html .= '<tr><td style="text-align:center;font-weight:bold;">Total</td>';
			ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $shift => $a_line) {
        		ksort($a_line);
        		reset($a_line);
				foreach ($a_line as $line => $a_qch_id) {
	        		$html .= '<td style="text-align:right;font-weight:bold;">'.number_format($tot_nilai_kolom[$shift][$line]).'</td>';
        		}
        	}
		}
		$html .='</table>';
	} else {
		$html .= '';
	}
	return $html;
}

function excel() {
	echo "Mohon maaf, fasilitas ini masih dalam pengembangan. Ekspor data baru tersedia dalam bentuk PDF.";
}

function lihatdata(){
	global $app_plan_id;
	$qch_id = $_POST['qch_id'];
	$sql = "SELECT a.*
		from qc_cb_header a
		where a.qch_id = '{$qch_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[qch_id].'</td><td>Di-input Oleh : '.$rh[qch_user_create].'</td></tr><tr><td>Subplant : '.$rh[qch_sub_plant].'</td><td>Tanggal Input : '.$rh[qch_date_create].'</td></tr><tr><td>Tanggal : '.cgx_dmy2ymd(substr($rh[qch_date],0,10)).'</td><td>Di-edit Oleh : '.$rh[qch_user_modify].'</td></tr><tr><td>Shift : '.$rh[qch_shift].'</td><td>Tanggal Edit : '.$rh[qch_date_modify].'</td></tr></table>';
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