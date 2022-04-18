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
	case "test":
		test();
		break;
}

function urai(){
	global $app_plan_id;
	$subplan = $_GET['subplan'];
	$tanggal = $_GET['tanggal'];
	$tglfrom = cgx_dmy2ymd($tanggal)." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal)." 23:59:59";
	$tgljudul = $tanggal;
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.qgh_subplant = '".$subplan."'";
	}
	$sql = "SELECT a.qgh_subplant as subplan, b.qgd_motif as motif, a.qgh_shift as shift, a.qgh_id, b.qgd_hasil, b.qgd_reject, b.qgd_hambatan, qgh_absensi, qgh_keterangan
		from qc_gl_header a
		join qc_gl_detail b on(a.qgh_id=b.qgh_id)
		where qgh_rec_stat = 'N' and a.qgh_date >= '{$tglfrom}' and a.qgh_date <= '{$tglto}' $whdua
		order by subplan, motif, shift, qgh_id";
	$responce->sql = $sql;
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)){
		foreach($qry as $r){
			$arr_kolom["$r[subplan]"]["$r[shift]"] = '';
			$arr_nilai["$r[subplan]"]["$r[motif]"]["$r[shift]"]["$r[qgh_id]"] = $r[qgd_hasil].'@@'.$r[qgd_reject].'@@'.$r[qgd_hambatan];
			$arr_absensi["$r[subplan]"]["$r[shift]"]["$r[qgh_id]"] = $r[qgh_absensi];
			$arr_keterangan["$r[subplan]"]["$r[shift]"]["$r[qgh_id]"] = $r[qgh_keterangan];
		}
	}
	if(is_array($arr_nilai)) {
		$sqlham = "SELECT qmh_code, qmh_nama from qc_md_hambatan order by qmh_code";
		$qryham = dbselect_plan_all($app_plan_id, $sqlham);
		if(is_array($qryham)) {
			$jml_hambatan = count($qryham);
			$jml_kolom = 4; 
			$jml_baris = ceil($jml_hambatan/$jml_kolom); 
			$idx = 1; 
			foreach($qryham as $rham) {
				$row_id = $idx % $jml_baris;
				$rows[$row_id] .= '<td style="font-size:80%;">'.$rham[qmh_code].' : '.$rham[qmh_nama].'</td>';
				$idx++;
			}
			$ham_html ='<table style="width:100%"><tbody><tr><td colspan="4" style="font-size:80%;">Kode Hambatan : <td></tr>'; 
			foreach ($rows as $cur_row) { 
				$ham_html .= '<tr>'.$cur_row.'</tr>'; 
			} 
			$ham_html .='</tbody></table>';
		}
		$out = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
		$out .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.</div><div style="text-align:center;font-size:20px;font-weight:bold;">LAPORAN GLAZE LINE</div><table style="margin:0 auto;"><tr><td>TANGGAL : </td><td>'.$tgljudul.'</td></tr></table><div style="overflow-x:auto;">';
		foreach ($arr_nilai as $subplan => $a_motif) {
			$out .= '<div style="font-size:18px;font-weight:bold;">SUBPLANT : '.$subplan.'</div>';
			$out .= '<div style="font-size:13px;font-weight:bold;margin-top:10px;">I. HASIL PRODUKSI</div>';
			$out .= '<table class="adaborder"><tbody>';
			$out .= '<tr><th rowspan="2" style="width:35px;max-width:35px;">NO</th><th rowspan="2">CODE</th>';
	        ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $shift => $value) {
        		$out .= '<th colspan="3">SHIFT - '.$shift.'</th>';
        	}
        	$out .= '<th colspan="3">TOTAL</th></tr><tr>';
        	ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $shift => $value) {
        		$out .= '<th style="width:80px;max-width:80px;">HASIL, M2</th><th style="width:80px;max-width:80px;">REJECT, M2</th><th style="width:60px;max-width:60px;">HAMBATAN</th>';
        	}
        	$out .= '<th style="width:90px;max-width:90px;">HASIL, M2</th><th style="width:90px;max-width:90px;">REJECT, M2</th><th style="width:100px;max-width:100px;">TOTAL</th></tr>';
        	$i = 1;
        	foreach ($a_motif as $motif => $a_shift) {
        		$kol_hasil = 0;
        		$kol_reject = 0;
				$out .='<tr><td style="text-align:center;">'.$i.'</td><td style="white-space:nowrap">'.$motif.'</td>';
				ksort($arr_kolom[$subplan]);
				reset($arr_kolom[$subplan]);
	        	foreach ($arr_kolom[$subplan] as $shift => $value) {
	        		if(is_array($a_shift[$shift])) {
	        			$hasil = '';
	        			$reject = '';
	        			$hambatan = '';
	        			foreach ($a_shift[$shift] as $qgh_id => $nilai) {
		        			$r = explode("@@",$nilai);
		        			if($r[0]){
		        				$hasil .= '<span onclick="lihatData(\''.$qgh_id.'\')">'.number_format($r[0]).'</span> ';
		        				$kol_hasil += $r[0];
		        				$row_hasil[$subplan][$shift] += $r[0];
		        				$tot_hasil[$subplan] += $r[0];
		        			}
		        			if($r[1]){
		        				$reject .= '<span onclick="lihatData(\''.$qgh_id.'\')">'.number_format($r[1]).'</span> ';
		        				$kol_reject += $r[1];
		        				$row_reject[$subplan][$shift] += $r[1];
		        				$tot_reject[$subplan] += $r[1];
		        			}
		        			if($r[2]){
		        				$hambatan .= '<span onclick="lihatData(\''.$qgh_id.'\')">'.$r[2].'</span> ';
		        			}
		        		}
	        			$out .= '<td style="text-align:right;">'.$hasil.'</td><td style="text-align:right;">'.$reject.'</td><td style="text-align:center;">'.$hambatan.'</td>';
	        		} else {
	        			$out .= '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
	        		}	
	        	}
	        	$kol_hare = $kol_hasil+$kol_reject;
	        	$out .= '<td style="text-align:right;">'.number_format($kol_hasil).'</td><td style="text-align:right;">'.number_format($kol_reject).'</td><td style="text-align:right;">'.number_format($kol_hare).'</td>';
	        	$out .= '</tr>';
	        	$i++;
			}
			$out .='<tr style="background-color:#cadbf7;"><td colspan="2" style="font-weight:bold;">TOTAL</td>';
			ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
			foreach ($arr_kolom[$subplan] as $shift => $value) {
				$out .= '<td style="text-align:right;">'.number_format($row_hasil[$subplan][$shift]).'</td><td style="text-align:right;">'.number_format($row_reject[$subplan][$shift]).'</td><td>&nbsp;</td>';	
			}
			$tot_hare[$subplan] = $tot_hasil[$subplan] + $tot_reject[$subplan];
			$out .= '<td style="text-align:right;">'.number_format($tot_hasil[$subplan]).'</td><td style="text-align:right;">'.number_format($tot_reject[$subplan]).'</td><td style="text-align:right;">'.number_format($tot_hare[$subplan]).'</td>';
			$out .= '</tr>';
			$out .='</tbody></table>';

			$out .= '<div style="font-size:13px;font-weight:bold;margin-top:5px;">II. ABSENSI</div>';
			$out .= '<table style="width:100%"><tbody><tr><td style="width:48%;vertical-align:top;"><table class="adaborder"><tbody><tr style="background-color:#cadbf7;">';
			ksort($arr_absensi[$subplan]);
			reset($arr_absensi[$subplan]);
			foreach ($arr_absensi[$subplan] as $shift => $a_qgh_id) {	
				$out .= '<th>SHIFT - '.$shift.'</th>';
			}
			$out .= '</tr><tr style="height:100%">';
			ksort($arr_absensi[$subplan]);
			reset($arr_absensi[$subplan]);
			foreach ($arr_absensi[$subplan] as $shift => $a_qgh_id) {
				$out .= '<td>';
				if(count($a_qgh_id) > 1) {
					$out .= '<ul style="margin-left:-30px;">';
					foreach ($a_qgh_id as $qgh_id => $absensi) {
						$out .= '<li><pre onclick="lihatData(\''.$qgh_id.'\')">'.$absensi.'</pre></li>';	
					}	
					$out .= '</ul>';
				} else {
					foreach ($a_qgh_id as $qgh_id => $absensi) {
						$out .= '<pre onclick="lihatData(\''.$qgh_id.'\')">'.$absensi.'</pre>';	
					}
				}
				$out .= '</td>';
			}
			$out .='</tr></tbody></table></td><td style="width:52%;vertical-align:top;">'.$ham_html.'</td></tr></tbody></table>';

			$out .= '<div style="font-size:13px;font-weight:bold;margin-top:5px;">III. KETERANGAN</div>';
			$out .= '<table class="adaborder" style="margin-bottom:20px;"><tbody><tr style="height:130px;">';
			ksort($arr_keterangan[$subplan]);
			reset($arr_keterangan[$subplan]);
			foreach ($arr_keterangan[$subplan] as $shift => $a_qgh_id) {
				$out .= '<td style="border-bottom:0px;">';
				if(count($a_qgh_id) > 1) {
					$out .= '<ul style="margin-left:-30px;">';
					foreach ($a_qgh_id as $qgh_id => $keterangan) {
						$out .= '<li><pre onclick="lihatData(\''.$qgh_id.'\')">'.$keterangan.'</pre></li>';	
					}	
					$out .= '</ul>';
				} else {
					foreach ($a_qgh_id as $qgh_id => $keterangan) {
						$out .= '<pre onclick="lihatData(\''.$qgh_id.'\')">'.$keterangan.'</pre>';	
					}
				}
				$out .= '</td>';	
			}
			$out .='</tr><tr>';
			ksort($arr_keterangan[$subplan]);
			reset($arr_keterangan[$subplan]);
			foreach ($arr_keterangan[$subplan] as $shift => $a_qgh_id) {	
				$out .= '<th style="border-top:0px;">KAREGU SHIFT - '.$shift.' '.$subplan.'</th>';
			}
			$out .='</tr></tbody></table>';
		}
		$out .='</div>';
	} else {
		$out = 'TIDAKADA';
	}
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

function excel() {
	require_once("../libs/PHPExcel.php");
	global $app_plan_id;
	$subplan = $_GET['subplan'];
	$tanggal = $_GET['tanggal'];
	$tglfrom = cgx_dmy2ymd($tanggal)." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal)." 23:59:59";
	$tgljudul = $tanggal;
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.qgh_subplant = '".$subplan."'";
	}
	$sql = "SELECT a.qgh_subplant as subplan, b.qgd_motif as motif, a.qgh_shift as shift, a.qgh_id, b.qgd_hasil, b.qgd_reject, b.qgd_hambatan, qgh_absensi, qgh_keterangan
		from qc_gl_header a
		join qc_gl_detail b on(a.qgh_id=b.qgh_id)
		where qgh_rec_stat = 'N' and a.qgh_date >= '{$tglfrom}' and a.qgh_date <= '{$tglto}' $whdua
		order by subplan, motif, shift, qgh_id";
	$responce->sql = $sql;
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)){
		foreach($qry as $r){
			$arr_kolom["$r[subplan]"]["$r[shift]"] = '';
			$arr_nilai["$r[subplan]"]["$r[motif]"]["$r[shift]"]["$r[qgh_id]"] = $r[qgd_hasil].'@@'.$r[qgd_reject].'@@'.$r[qgd_hambatan];
			$arr_absensi["$r[subplan]"]["$r[shift]"]["$r[qgh_id]"] = $r[qgh_absensi];
			$arr_keterangan["$r[subplan]"]["$r[shift]"]["$r[qgh_id]"] = $r[qgh_keterangan];
		}
	}
	if(is_array($arr_nilai)) {
		$sqlham = "SELECT qmh_code, qmh_nama from qc_md_hambatan order by qmh_code";
		$qryham = dbselect_plan_all($app_plan_id, $sqlham);
		if(is_array($qryham)) {
			$jml_hambatan = count($qryham);
			$jml_kolom = 4; 
			$jml_baris = ceil($jml_hambatan/$jml_kolom); 
			$idx = 1; 
			foreach($qryham as $rham) {
				$row_id = $idx % $jml_baris;
				$rows[$row_id] .= '<td style="font-size:80%;">'.$rham[qmh_code].' : '.$rham[qmh_nama].'</td>';
				$idx++;
			}
			$ham_html ='<table style="width:100%"><tbody><tr><td colspan="4" style="font-size:80%;">Kode Hambatan : <td></tr>'; 
			foreach ($rows as $cur_row) { 
				$ham_html .= '<tr>'.$cur_row.'</tr>'; 
			} 
			$ham_html .='</tbody></table>';
		}
    	$tot_kolom_max = 13;
		$icell = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP","BQ","BR","BS","BT","BU","BV","BW","BX","BY","BZ","CA","CB","CC","CD","CE","CF","CG","CH","CI","CJ","CK","CL","CM","CN","CO","CP","CQ","CR","CS","CT","CU","CV","CW","CX","CY","CZ","DA","DB","DC","DD","DE","DF","DG","DH","DI","DJ","DK","DL","DM","DN","DO","DP","DQ","DR","DS","DT","DU","DV","DW","DX","DY","DZ");
		$oexcel = new PHPExcel();
		$coltitleSy = new PHPExcel_Style();
		$coltitleSy->applyFromArray(array(
			'font'		=> array(
				'bold' 	=> true
			),
		    'alignment' => array(
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		    	'vertical'		=> PHPExcel_Style_Alignment::VERTICAL_CENTER
		    ),
		    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    )
		));
		$colwrap = new PHPExcel_Style();
		$colwrap->applyFromArray(array(
			'alignment' => array(
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		    	'vertical'		=> PHPExcel_Style_Alignment::VERTICAL_CENTER,
		    	'wrap' => true
		    ),
		    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    )
		));
		$colborder = new PHPExcel_Style();
		$colborder->applyFromArray(array(
		    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    )
		));
		$colkoloryellow = new PHPExcel_Style();
		$colkoloryellow->applyFromArray(array(
		    'fill' 	=> array(
				'type'    	=> PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
       				'argb' => 'FFFFFF00'
    			),
    			'endcolor' => array(
        			'argb' => 'FFFFFF00'
    			),
			),
			'font'		=> array(
				'bold' 	=> true
			),
		    'alignment' => array(
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
		    ),
		    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    )
		));
		$colkolorgreen = new PHPExcel_Style();
		$colkolorgreen->applyFromArray(array(
		    'fill' 	=> array(
				'type'    	=> PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
       				'argb' => 'FF00FF00'
    			),
    			'endcolor' => array(
        			'argb' => 'FF00FF00'
    			),
			),
			'font'		=> array(
				'bold' 	=> true
			),
		    'alignment' => array(
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
		    ),
		    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    )
		));
		$coltengah = new PHPExcel_Style();
		$coltengah->applyFromArray(array(
			'font'		=> array(
				'bold' 	=> true
			),
		    'alignment' => array(
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		    	'vertical'		=> PHPExcel_Style_Alignment::VERTICAL_CENTER
		    )
		));
		$colkanan = new PHPExcel_Style();
		$colkanan->applyFromArray(array(
			'font'		=> array(
				'bold' 	=> true
			),
		    'alignment' => array(
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
		    	'vertical'		=> PHPExcel_Style_Alignment::VERTICAL_CENTER
		    )
		));
		$colkiri = new PHPExcel_Style();
		$colkiri->applyFromArray(array(
			'font'		=> array(
				'bold' 	=> true
			),
		    'alignment' => array(
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
		    	'vertical'		=> PHPExcel_Style_Alignment::VERTICAL_CENTER
		    )
		));
		$oexcel->getProperties()->setCreator("Ardi Fianto")
								->setLastModifiedBy("Ardi Fianto");
		$si = $oexcel->setActiveSheetIndex(0);
		
		$baris = 1;
		$si->mergeCells($icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
		$si->setCellValue($icell[0].$baris,'No : F.');
		$si->setSharedStyle($colkanan, $icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
		$baris++;
		$si->mergeCells($icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
		$si->setCellValue($icell[0].$baris,'LAPORAN GLAZE LINE');
		$baris++;
		$si->mergeCells($icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
		$si->setCellValue($icell[0].$baris,$tgljudul);
		$si->setSharedStyle($coltengah, $icell[0].($baris-1).':'.$icell[$tot_kolom_max].$baris);
		$baris++;
		$zcell = 0;
		foreach ($arr_nilai as $subplan => $a_motif) {
			$si->mergeCells($icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
			$si->setCellValue($icell[0].$baris,'SUBPLANT : '.$subplan);
			$si->setSharedStyle($colkiri, $icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
			$baris+=2;
			$si->mergeCells($icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
			$si->setCellValue($icell[0].$baris,'I. HASIL PRODUKSI');
			$si->setSharedStyle($colkiri, $icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
			$baris++;
			$si->mergeCells($icell[0].$baris.':'.$icell[0].($baris+1));
			$si->setCellValue($icell[0].$baris,'NO');
			$si->mergeCells($icell[1].$baris.':'.$icell[1].($baris+1));
			$si->setCellValue($icell[1].$baris,'CODE');
			$nexcel = 2;
			ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $shift => $value) {
        		$akolspan = $nexcel+2;
        		$si->mergeCells($icell[$nexcel].$baris.':'.$icell[$akolspan].$baris);
				$si->setCellValue($icell[$nexcel].$baris,'SHIFT - '.$shift);
        		$nexcel = $akolspan+1;
        	}
        	$akolspan = $nexcel+2;
    		$si->mergeCells($icell[$nexcel].$baris.':'.$icell[$akolspan].$baris);
			$si->setCellValue($icell[$nexcel].$baris,'TOTAL');
			$baris++;
			$nexcel = 2;
        	ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $shift => $value) {
        		$si->setCellValue($icell[$nexcel].$baris,'HASIL, M2');
		        $nexcel++;
        		$si->setCellValue($icell[$nexcel].$baris,'REJECT, M2');
		        $nexcel++;
        		$si->setCellValue($icell[$nexcel].$baris,'HAMBATAN');
		        $nexcel++;
        	}
        	$si->setCellValue($icell[$nexcel].$baris,'HASIL, M2');
	        $nexcel++;
    		$si->setCellValue($icell[$nexcel].$baris,'REJECT, M2');
	        $nexcel++;
    		$si->setCellValue($icell[$nexcel].$baris,'TOTAL');
    		$si->setSharedStyle($coltitleSy, $icell[0].($baris-1).':'.$icell[$nexcel].$baris);
    		$baris++;
    		$i = 1;
        	foreach ($a_motif as $motif => $a_shift) {
        		$kol_hasil = 0;
        		$kol_reject = 0;
        		$si->setCellValue($icell[0].$baris,$i);
        		$si->setCellValue($icell[1].$baris,$motif);
        		$nexcel = 2;
				ksort($arr_kolom[$subplan]);
				reset($arr_kolom[$subplan]);
	        	foreach ($arr_kolom[$subplan] as $shift => $value) {
	        		if(is_array($a_shift[$shift])) {
	        			$hasil = '';
	        			$reject = '';
	        			$hambatan = '';
	        			if(count($a_shift[$shift]) > 1){
	        				foreach ($a_shift[$shift] as $qgh_id => $nilai) {
			        			$r = explode("@@",$nilai);
			        			if($r[0]){
			        				$hasil .= $r[0].' ';
			        				$kol_hasil += $r[0];
			        				$row_hasil[$subplan][$shift] += $r[0];
			        				$tot_hasil[$subplan] += $r[0];
			        			}
			        			if($r[1]){
			        				$reject .= $r[1].' ';
			        				$kol_reject += $r[1];
			        				$row_reject[$subplan][$shift] += $r[1];
			        				$tot_reject[$subplan] += $r[1];
			        			}
			        			if($r[2]){
			        				$hambatan .= $r[2].' ';
			        			}
			        		}
	        			} else {
	        				foreach ($a_shift[$shift] as $qgh_id => $nilai) {
			        			$r = explode("@@",$nilai);
			        			if($r[0]){
			        				$hasil = $r[0];
			        				$kol_hasil += $r[0];
			        				$row_hasil[$subplan][$shift] += $r[0];
			        				$tot_hasil[$subplan] += $r[0];
			        			}
			        			if($r[1]){
			        				$reject = $r[1];
			        				$kol_reject += $r[1];
			        				$row_reject[$subplan][$shift] += $r[1];
			        				$tot_reject[$subplan] += $r[1];
			        			}
			        			if($r[2]){
			        				$hambatan = $r[2].' ';
			        			}
			        		}
	        			}
		        		$si->setCellValue($icell[$nexcel].$baris,$hasil);
		        		$nexcel++;
		        		$si->setCellValue($icell[$nexcel].$baris,$reject);
		        		$nexcel++;
		        		$si->setCellValue($icell[$nexcel].$baris,$hambatan);
		        		$nexcel++;
	        		} else {
	        			$si->setCellValue($icell[$nexcel].$baris,'');
		        		$nexcel++;
		        		$si->setCellValue($icell[$nexcel].$baris,'');
		        		$nexcel++;
		        		$si->setCellValue($icell[$nexcel].$baris,'');
		        		$nexcel++;
	        		}	
	        	}
	        	$kol_hare = $kol_hasil+$kol_reject;
	        	$si->setCellValue($icell[$nexcel].$baris,$kol_hasil);
        		$nexcel++;
        		$si->setCellValue($icell[$nexcel].$baris,$kol_reject);
        		$nexcel++;
        		$si->setCellValue($icell[$nexcel].$baris,$kol_hare);
        		$si->setSharedStyle($colborder, $icell[0].$baris.':'.$icell[$nexcel].$baris);
	        	$baris++;
	        	$i++;
			}
			$si->mergeCells($icell[0].$baris.':'.$icell[1].$baris);
			$si->setCellValue($icell[0].$baris,'TOTAL');
			$nexcel = 2;
			ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
			foreach ($arr_kolom[$subplan] as $shift => $value) {
				$si->setCellValue($icell[$nexcel].$baris,$row_hasil[$subplan][$shift]);
		        $nexcel++;
				$si->setCellValue($icell[$nexcel].$baris,$row_reject[$subplan][$shift]);
		        $nexcel++;
				$si->setCellValue($icell[$nexcel].$baris,'');
		        $nexcel++;
			}
			$tot_hare[$subplan] = $tot_hasil[$subplan] + $tot_reject[$subplan];
			$si->setCellValue($icell[$nexcel].$baris,$tot_hasil[$subplan]);
    		$nexcel++;
    		$si->setCellValue($icell[$nexcel].$baris,$tot_reject[$subplan]);
    		$nexcel++;
    		$si->setCellValue($icell[$nexcel].$baris,$tot_hare[$subplan]);
    		$si->setSharedStyle($colborder, $icell[0].$baris.':'.$icell[$nexcel].$baris);
        	$baris+=2;

			$si->mergeCells($icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
			$si->setCellValue($icell[0].$baris,'II. ABSENSI');
			$si->setSharedStyle($colkiri, $icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
			$baris++;
			$nexcel = 0;
			ksort($arr_absensi[$subplan]);
			reset($arr_absensi[$subplan]);
			foreach ($arr_absensi[$subplan] as $shift => $a_qgh_id) {
				$akolspan = $nexcel+2;
        		$si->mergeCells($icell[$nexcel].$baris.':'.$icell[$akolspan].$baris);	
				$si->setCellValue($icell[$nexcel].$baris,'SHIFT - '.$shift);
		        $nexcel = $akolspan+1;
			}
			$si->setSharedStyle($coltitleSy, $icell[0].$baris.':'.$icell[($nexcel-1)].$baris);
			$baris++;
			$nexcel = 0;
			ksort($arr_absensi[$subplan]);
			reset($arr_absensi[$subplan]);
			foreach ($arr_absensi[$subplan] as $shift => $a_qgh_id) {
				$txt_absensi = '';
				foreach ($a_qgh_id as $qgh_id => $absensi) {
					$txt_absensi .= $absensi.' ';	
				}
				$akolspan = $nexcel+2;
        		$si->mergeCells($icell[$nexcel].$baris.':'.$icell[$akolspan].$baris);
				$si->setCellValue($icell[$nexcel].$baris,$txt_absensi);
		        $nexcel = $akolspan+1;
			}
			$si->setSharedStyle($colwrap, $icell[0].$baris.':'.$icell[($nexcel-1)].$baris);
			$baris+=2;

			$si->mergeCells($icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
			$si->setCellValue($icell[0].$baris,'III. KETERANGAN');
			$si->setSharedStyle($colkiri, $icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
			$baris++;
			$nexcel = 0;
			ksort($arr_keterangan[$subplan]);
			reset($arr_keterangan[$subplan]);
			foreach ($arr_keterangan[$subplan] as $shift => $a_qgh_id) {
				$txt_keterangan = '';
				foreach ($a_qgh_id as $qgh_id => $keterangan) {
					$txt_keterangan .= $keterangan.' ';	
				}
				$akolspan = $nexcel+3;
        		$si->mergeCells($icell[$nexcel].$baris.':'.$icell[$akolspan].$baris);
				$si->setCellValue($icell[$nexcel].$baris,$txt_keterangan);
		        $nexcel = $akolspan+1;	
			}
			$baris++;
			$nexcel = 0;
			ksort($arr_keterangan[$subplan]);
			reset($arr_keterangan[$subplan]);
			foreach ($arr_keterangan[$subplan] as $shift => $a_qgh_id) {
				$akolspan = $nexcel+3;
        		$si->mergeCells($icell[$nexcel].$baris.':'.$icell[$akolspan].$baris);
				$si->setCellValue($icell[$nexcel].$baris,'KAREGU SHIFT - '.$shift.' '.$subplan);
		        $nexcel = $akolspan+1;	
			}
			$si->setSharedStyle($colwrap, $icell[0].($baris-1).':'.$icell[($nexcel-1)].$baris);
			$baris+=3;
		}
		$si->setTitle('Sheet1');
		$oexcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		if($_GET["tipe"] == "xlsx") {
			header('Content-Disposition: attachment;filename=Glaze_Line.xlsx');
		} else if($_GET["tipe"] == "xls") {
			header('Content-Disposition: attachment;filename=Glaze_Line.xls');
		}
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0
		if($_GET["tipe"] == "xlsx") {
			$objWriter = PHPExcel_IOFactory::createWriter($oexcel, 'Excel2007');
		} else if($_GET["tipe"] == "xls") {
			$objWriter = PHPExcel_IOFactory::createWriter($oexcel, 'Excel5');
		}
		$objWriter->save('php://output');
		exit;
	} else {
		echo 'TIDAKADA';
	}
}

function lihatdata(){
	global $app_plan_id;
	$qgh_id = $_POST['qgh_id'];
	$sql = "SELECT a.*
		from qc_gl_header a
		where a.qgh_id = '{$qgh_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[qgh_id].'</td><td>Di-input Oleh : '.$rh[qgh_user_create].'</td></tr><tr><td>Subplant : '.$rh[qgh_subplant].'</td><td>Tanggal Input : '.$rh[qgh_date_create].'</td></tr><tr><td>Tanggal : '.cgx_dmy2ymd(substr($rh[qgh_date],0,10)).'</td><td>Di-edit Oleh : '.$rh[qgh_user_modify].'</td></tr><tr><td>Shift : '.$rh[qgh_shift].'</td><td>Tanggal Edit : '.$rh[qgh_date_modify].'</td></tr></table>';
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