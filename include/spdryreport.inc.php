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
	case "excel2":
		excel2();
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
	$arr_kolom = array("08" => "08", "09" => "09", "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14", "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19", "20" => "20", "21" => "21", "22" => "22", "23" => "23", "00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04", "05" => "05", "06" => "06", "07" => "07");
	$sql0 = "SELECT qssd_seq from qc_sp_sett_detail where qssd_group = '01' order by qssd_seq";
	$qry0 = dbselect_plan_all($app_plan_id, $sql0);
	$arr_line = array();
	if(is_array($qry0)) {
		foreach($qry0 as $r0){
			array_push($arr_line, $r0[qssd_seq]);
		}
	}
	$subplan = $_GET['subplan'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= "and qsm_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.qsm_sub_plant as subplan, a.qsm_date, b.qsmd_sett_seq as line, d.qssd_monitoring_desc as item_nama, b.qsmd_sett_value as nilai, a.qsm_id from qc_sp_monitoring a join qc_sp_monitoring_detail b on(a.qsm_id=b.qsm_id) join qc_sp_sett_detail d on(b.qsmd_sett_group=d.qssd_group and b.qsmd_sett_seq=d.qssd_seq) where qsm_rec_status='N' and b.qsmd_sett_group='01' and a.qsm_date >= '{$tglfrom}' and a.qsm_date <= '{$tglto}' $whdua order by a.qsm_sub_plant, a.qsm_date, b.qsmd_sett_seq, a.qsm_id";
	$responce->sql = $sql; 
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)) {
		foreach($qry as $r){
			$datetime = explode(' ',$r[qsm_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			$r[jam] = substr($datetime[1],0,2);
			$arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[line]"]["$r[jam]"]["$r[qsm_id]"] = $r[nilai];
			$arr_item["$r[line]"] = $r[item_nama];
		}
	}
	
	$whdua2 = "";
	if($subplan <> 'All') {
		$whdua2 .= " and qsms_sub_plant = '".$subplan."'";
	}
	$sql2 = "SELECT qsms_sub_plant as subplan, qsms_date, qsms_keterangan
		from qc_sp_monitoring_stop
		where qsms_rec_status='N' and qsms_date >= '{$tglfrom}' and qsms_date <= '{$tglto}' $whdua2
		order by subplan, qsms_date";
	$responce->sql2 = $sql2;
	$qry2 = dbselect_plan_all($app_plan_id, $sql2);
	if(is_array($qry2)) {
		foreach($qry2 as $r2){
			$datetime2 = explode(' ',$r2[qsms_date]);
			$r2[tgl] = cgx_dmy2ymd($datetime2[0]);
			$r2[jam] = substr($datetime2[1],0,2);
			$arr_stop["$r2[subplan]"]["$r2[tgl]"]["$r2[jam]"] = $r2[qsms_keterangan];
		}
	}
	
	if(is_array($arr_stop)) {
		foreach ($arr_stop as $subplan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_jam) {
				if(!$arr_nilai[$subplan][$tgl]) {
					foreach ($a_jam as $jam => $qsms_keterangan) {
						foreach ($arr_line as $line) {
							$arr_nilai["$subplan"]["$tgl"]["$line"]["$jam"]["1"] = "";
						}
					}
				}	
			}
		}
	}

	if(is_array($arr_nilai)) {
		ksort($arr_nilai);
		reset($arr_nilai);	
		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
		$html .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.1002.QC.04</div><div style="text-align:center;font-size:20px;font-weight:bold;">MONITOR SETTING SPRAY DRYER</div><table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
		$html .= '<div style="overflow-x:auto;"><table class="adaborder">';
		foreach ($arr_nilai as $subplan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_line) {
				$html .= '<tr><th colspan="2" style="text-align:left;padding-top:20px;">SUBPLANT : '.$subplan.'</th><th colspan="24" style="text-align:left;padding-top:20px;">'.$tgl.'</th></tr>';
				$html .= '<tr><th rowspan="2">NO</th><th rowspan="2">ITEM</th><th colspan="24">JAM</th></tr>';
				$html .= '<tr>';
				foreach ($arr_kolom as $kolom => $kolom_nama) {
					$html .= '<th>'.$kolom.':00</th>';
				}
				$html .= '</tr>';
				foreach ($a_line as $line => $a_jam) {
					$html .='<tr><td style="text-align:center;">'.$line.'</td><td style="white-space: nowrap">'.$arr_item[$line].'</td>';
					foreach ($arr_kolom as $kolom => $kolom_nama) {
						if($line == '1') {
							if($arr_stop[$subplan][$tgl][$kolom]) {
								$html .= '<td rowspan="'.count($a_line).'" style="background-color:lightblue">'.$arr_stop[$subplan][$tgl][$kolom].'</td>';
							} else {
								$html .= '<td style="text-align:right;">';
								if(is_array($a_jam[$kolom])) {
									foreach ($a_jam[$kolom] as $qsm_id => $nilai) {
										$html .= '<span onclick="lihatData(\''.$qsm_id.'\')">'.$nilai.'</span> ';	
									}
								} else {
									$html .= '&nbsp;';
								}	
								$html .= '</td>';
							}
						} else {
							if($arr_stop[$subplan][$tgl][$kolom]) {
								
							} else {
								$html .= '<td style="text-align:right;">';
								if(is_array($a_jam[$kolom])) {
									foreach ($a_jam[$kolom] as $qsm_id => $nilai) {
										$html .= '<span onclick="lihatData(\''.$qsm_id.'\')">'.$nilai.'</span> ';	
									}
								} else {
									$html .= '&nbsp;';
								}	
								$html .= '</td>';
							}
						}
					}
					$html .= '</tr>';
				}
			}
		}
		$html .='</table></div>';
	} else {
		$html = 'TIDAKADA';
	}
	// echo $html;
	$responce->detailtabel = $html; 
	echo json_encode($responce);
}

function excel() {
	require_once("../libs/PHPExcel.php");
	global $app_plan_id;
	$arr_kolom = array("08" => "08", "09" => "09", "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14", "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19", "20" => "20", "21" => "21", "22" => "22", "23" => "23", "00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04", "05" => "05", "06" => "06", "07" => "07");
	$sql0 = "SELECT qssd_seq from qc_sp_sett_detail where qssd_group = '01' order by qssd_seq";
	$qry0 = dbselect_plan_all($app_plan_id, $sql0);
	$arr_line = array();
	if(is_array($qry0)) {
		foreach($qry0 as $r0){
			array_push($arr_line, $r0[qssd_seq]);
		}
	}
	$subplan = $_GET['subplan'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= "and qsm_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.qsm_sub_plant as subplan, a.qsm_date, b.qsmd_sett_seq as line, d.qssd_monitoring_desc as item_nama, b.qsmd_sett_value as nilai, a.qsm_id from qc_sp_monitoring a join qc_sp_monitoring_detail b on(a.qsm_id=b.qsm_id) join qc_sp_sett_detail d on(b.qsmd_sett_group=d.qssd_group and b.qsmd_sett_seq=d.qssd_seq) where qsm_rec_status='N' and b.qsmd_sett_group='01' and a.qsm_date >= '{$tglfrom}' and a.qsm_date <= '{$tglto}' $whdua order by a.qsm_sub_plant, a.qsm_date, b.qsmd_sett_seq, a.qsm_id";
	$responce->sql = $sql; 
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)) {
		foreach($qry as $r){
			$datetime = explode(' ',$r[qsm_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			$r[jam] = substr($datetime[1],0,2);
			$arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[line]"]["$r[jam]"]["$r[qsm_id]"] = $r[nilai];
			$arr_item["$r[line]"] = $r[item_nama];
		}
	}
	
	$whdua2 = "";
	if($subplan <> 'All') {
		$whdua2 .= " and qsms_sub_plant = '".$subplan."'";
	}
	$sql2 = "SELECT qsms_sub_plant as subplan, qsms_date, qsms_keterangan
		from qc_sp_monitoring_stop
		where qsms_rec_status='N' and qsms_date >= '{$tglfrom}' and qsms_date <= '{$tglto}' $whdua2
		order by subplan, qsms_date";
	$responce->sql2 = $sql2;
	$qry2 = dbselect_plan_all($app_plan_id, $sql2);
	if(is_array($qry2)) {
		foreach($qry2 as $r2){
			$datetime2 = explode(' ',$r2[qsms_date]);
			$r2[tgl] = cgx_dmy2ymd($datetime2[0]);
			$r2[jam] = substr($datetime2[1],0,2);
			$arr_stop["$r2[subplan]"]["$r2[tgl]"]["$r2[jam]"] = $r2[qsms_keterangan];
		}
	}
	
	if(is_array($arr_stop)) {
		foreach ($arr_stop as $subplan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_jam) {
				if(!$arr_nilai[$subplan][$tgl]) {
					foreach ($a_jam as $jam => $qsms_keterangan) {
						foreach ($arr_line as $line) {
							$arr_nilai["$subplan"]["$tgl"]["$line"]["$jam"]["1"] = "";
						}
					}
				}	
			}
		}
	}

	if(is_array($arr_nilai)) {
		ksort($arr_nilai);
		reset($arr_nilai);
		$icell = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP","BQ","BR","BS","BT","BU","BV","BW","BX","BY","BZ","CA","CB","CC","CD","CE","CF","CG","CH","CI","CJ","CK","CL","CM","CN","CO","CP","CQ","CR","CS","CT","CU","CV","CW","CX","CY","CZ","DA","DB","DC","DD","DE","DF","DG","DH","DI","DJ","DK","DL","DM","DN","DO","DP","DQ","DR","DS","DT","DU","DV","DW","DX","DY","DZ");
		$oexcel = new PHPExcel();
		$coltitleSy = new PHPExcel_Style();
		$coltitleSy->applyFromArray(array(
			'font'		=> array(
				'bold' 	=> true
			),
		    'alignment' => array(
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		    	'vertical'		=> PHPExcel_Style_Alignment::VERTICAL_CENTER,
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
		$colboldunder = new PHPExcel_Style();
		$colboldunder->applyFromArray(array(
		    'font'		=> array(
				'bold' 	=> true,
				'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
			),
		    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    )
		));
		$coltengah = new PHPExcel_Style();
		$coltengah->applyFromArray(array(
			'alignment' => array(
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER
		    ),
		    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    )
		));
		$colboldaja = new PHPExcel_Style();
		$colboldaja->applyFromArray(array(
		    'font'		=> array(
				'bold' 	=> true
			)
		));
		$colwrap = new PHPExcel_Style();
		$colwrap->applyFromArray(array(
		    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    ),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'		=> PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap' => true
			),
			'fill' 	=> array(
				'type'    	=> PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
       				'argb' => 'FFFFFF00'
    			),
    			'endcolor' => array(
        			'argb' => 'FFFFFF00'
    			),
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
		$oexcel->getProperties()->setCreator("Ardi Fianto")
								->setLastModifiedBy("Ardi Fianto");
		$si = $oexcel->setActiveSheetIndex(0);
		
		$baris = 1;
		$si->mergeCells($icell[0].$baris.':'.$icell[25].$baris);
		$si->setCellValue($icell[0].$baris,'No : F.1002.QC.04');
		$si->setSharedStyle($colkanan, $icell[0].$baris.':'.$icell[25].$baris);
		$baris++;
		$si->mergeCells($icell[0].$baris.':'.$icell[25].$baris);
		$si->setCellValue($icell[0].$baris,'MONITOR SETTING SPRAY DRYER');
		$baris++;
		$si->mergeCells($icell[0].$baris.':'.$icell[25].$baris);
		$si->setCellValue($icell[0].$baris,'TGL : '.$tgljudul);
		$si->setSharedStyle($coltengah, $icell[0].($baris-1).':'.$icell[25].$baris);
		$baris +=3;
		$wrap_baris = array();
		foreach ($arr_nilai as $subplan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_line) {
				$si->mergeCells($icell[0].$baris.':'.$icell[1].$baris);
				$si->setCellValue($icell[0].$baris,'SUBPLANT : '.$subplan);
				$si->mergeCells($icell[2].$baris.':'.$icell[25].$baris);
				$si->setCellValue($icell[2].$baris,$tgl);
				$si->setSharedStyle($colboldaja, $icell[0].$baris.':'.$icell[2].$baris);	
				$baris++;
				$si->mergeCells($icell[0].$baris.':'.$icell[0].($baris+1));
				$si->setCellValue($icell[0].$baris,'NO');
				$si->mergeCells($icell[1].$baris.':'.$icell[1].($baris+1));
				$si->setCellValue($icell[1].$baris,'ITEM');
				$si->mergeCells($icell[2].$baris.':'.$icell[25].$baris);
				$si->setCellValue($icell[2].$baris,'JAM');
				$baris++;
				$nexcel = 2;
				foreach ($arr_kolom as $kolom => $kolom_nama) {
					$si->setCellValue($icell[$nexcel].$baris,$kolom.':00');
					$nexcel++;
				}
				$si->setSharedStyle($coltitleSy, $icell[0].($baris-1).':'.$icell[25].$baris);
				$baris++;
				$a_brs = $baris;
				foreach ($a_line as $line => $a_jam) {
					$si->setCellValue($icell[0].$baris,$line);
					$si->setCellValue($icell[1].$baris,$arr_item[$line]);
					$nexcel = 2;
					foreach ($arr_kolom as $kolom => $kolom_nama) {
						if($line == '1') {
							if($arr_stop[$subplan][$tgl][$kolom]) {
								$si->mergeCells($icell[$nexcel].$baris.':'.$icell[$nexcel].($baris+count($a_line)-1));
								$si->setCellValue($icell[$nexcel].$baris,$arr_stop[$subplan][$tgl][$kolom]);
								$wrap_baris[$baris][$nexcel] = 'WRAP';
							} else {
								if(is_array($a_jam[$kolom])) {
									$nilainya = "";
									foreach ($a_jam[$kolom] as $qsm_id => $nilai) {
										$nilainya = $nilainya." ".$nilai; 	
									}
									$nilainya = substr($nilainya, 1);
								} else {
									$nilainya = "";
								}
								$si->setCellValue($icell[$nexcel].$baris,$nilainya);
							}
						} else {
							if($arr_stop[$subplan][$tgl][$kolom]) {
								
							} else {
								if(is_array($a_jam[$kolom])) {
									$nilainya = "";
									foreach ($a_jam[$kolom] as $qsm_id => $nilai) {
										$nilainya = $nilainya." ".$nilai; 	
									}
									$nilainya = substr($nilainya, 1);
								} else {
									$nilainya = "";
								}
								$si->setCellValue($icell[$nexcel].$baris,$nilainya);
							}
						}
						$nexcel++;
					}
					$baris++;
				}
				$si->setSharedStyle($colborder, $icell[0].($a_brs).':'.$icell[25].($baris-1));
				$baris++;
			}
		}
		if(is_array($wrap_baris)) {
			foreach ($wrap_baris as $w_baris => $a_w_cell) {
				foreach ($a_w_cell as $w_cell => $value) {
					$si->setSharedStyle($colwrap, $icell[$w_cell].$w_baris);	
				}
			}
		}
		// $si->getStyle('C6:'.$icell[25].$baris)->getNumberFormat()->setFormatCode('#,##0.00');
		$si->setTitle('Sheet1');
		$oexcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		if($_GET["tipe"] == "xlsx") {
			header('Content-Disposition: attachment;filename=Monitor_Setting_Spray_Dryer.xlsx');
		} else if($_GET["tipe"] == "xls") {
			header('Content-Disposition: attachment;filename=Monitor_Setting_Spray_Dryer.xls');
		} 
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header ('Cache-Control: cache, must-revalidate');
		header ('Pragma: public');
		if($_GET["tipe"] == "xlsx") {
			$objWriter = PHPExcel_IOFactory::createWriter($oexcel, 'Excel2007');
		} else if($_GET["tipe"] == "xls") {
			$objWriter = PHPExcel_IOFactory::createWriter($oexcel, 'Excel5');
		}
		$objWriter->save('php://output');
		exit;
	} else {
		$html = 'TIDAKADA';
		echo $html;
	}
}

function excel2() {
	require_once("../libs/PHPExcel.php");
	global $app_plan_id;
	$arr_jam = array("08" => "08", "09" => "09", "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14", "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19", "20" => "20", "21" => "21", "22" => "22", "23" => "23", "00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04", "05" => "05", "06" => "06", "07" => "07");
	$sql0 = "SELECT qssd_seq from qc_sp_sett_detail where qssd_group = '01' order by qssd_seq";
	$qry0 = dbselect_plan_all($app_plan_id, $sql0);
	$arr_line = array();
	if(is_array($qry0)) {
		foreach($qry0 as $r0){
			array_push($arr_line, $r0[qssd_seq]);
		}
	}
	$subplan = $_GET['subplan'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= "and qsm_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.qsm_sub_plant as subplan, a.qsm_date, b.qsmd_sett_seq as line, d.qssd_monitoring_desc as item_nama, b.qsmd_sett_value as nilai, a.qsm_id from qc_sp_monitoring a join qc_sp_monitoring_detail b on(a.qsm_id=b.qsm_id) join qc_sp_sett_detail d on(b.qsmd_sett_group=d.qssd_group and b.qsmd_sett_seq=d.qssd_seq) where qsm_rec_status='N' and b.qsmd_sett_group='01' and a.qsm_date >= '{$tglfrom}' and a.qsm_date <= '{$tglto}' $whdua order by a.qsm_sub_plant, a.qsm_date, b.qsmd_sett_seq, a.qsm_id";
	$responce->sql = $sql; 
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)) {
		foreach($qry as $r){
			$datetime = explode(' ',$r[qsm_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			$r[jam] = substr($datetime[1],0,2);
			$arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[line]"]["$r[qsm_id]"] = $r[nilai];
			$arr_item["$r[line]"] = $r[item_nama];
		}
		ksort($arr_item);
		reset($arr_item);
	}
	
	$whdua2 = "";
	if($subplan <> 'All') {
		$whdua2 .= " and qsms_sub_plant = '".$subplan."'";
	}
	$sql2 = "SELECT qsms_sub_plant as subplan, qsms_date, qsms_keterangan
		from qc_sp_monitoring_stop
		where qsms_rec_status='N' and qsms_date >= '{$tglfrom}' and qsms_date <= '{$tglto}' $whdua2
		order by subplan, qsms_date";
	$responce->sql2 = $sql2;
	$qry2 = dbselect_plan_all($app_plan_id, $sql2);
	if(is_array($qry2)) {
		foreach($qry2 as $r2){
			$datetime2 = explode(' ',$r2[qsms_date]);
			$r2[tgl] = cgx_dmy2ymd($datetime2[0]);
			$r2[jam] = substr($datetime2[1],0,2);
			$arr_stop["$r2[subplan]"]["$r2[tgl]"]["$r2[jam]"] = $r2[qsms_keterangan];
		}
	}
	
	if(is_array($arr_stop)) {
		foreach ($arr_stop as $subplan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_jam) {
				if(!$arr_nilai[$subplan][$tgl]) {
					foreach ($a_jam as $jam => $qsms_keterangan) {
						foreach ($arr_line as $line) {
							$arr_nilai["$subplan"]["$tgl"]["$jam"]["$line"]["1"] = "";
						}
					}
				}	
			}
		}
	}

	if(is_array($arr_nilai)) {
		ksort($arr_nilai);
		reset($arr_nilai);
		$icell = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP","BQ","BR","BS","BT","BU","BV","BW","BX","BY","BZ","CA","CB","CC","CD","CE","CF","CG","CH","CI","CJ","CK","CL","CM","CN","CO","CP","CQ","CR","CS","CT","CU","CV","CW","CX","CY","CZ","DA","DB","DC","DD","DE","DF","DG","DH","DI","DJ","DK","DL","DM","DN","DO","DP","DQ","DR","DS","DT","DU","DV","DW","DX","DY","DZ");
		$oexcel = new PHPExcel();
		$coltitleSy = new PHPExcel_Style();
		$coltitleSy->applyFromArray(array(
			'font'		=> array(
				'bold' 	=> true
			),
		    'alignment' => array(
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		    	'vertical'		=> PHPExcel_Style_Alignment::VERTICAL_CENTER,
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
		$colboldunder = new PHPExcel_Style();
		$colboldunder->applyFromArray(array(
		    'font'		=> array(
				'bold' 	=> true,
				'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
			),
		    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    )
		));
		$coltengah = new PHPExcel_Style();
		$coltengah->applyFromArray(array(
			'alignment' => array(
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER
		    ),
		    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    )
		));
		$colboldaja = new PHPExcel_Style();
		$colboldaja->applyFromArray(array(
		    'font'		=> array(
				'bold' 	=> true
			)
		));
		$colwrap = new PHPExcel_Style();
		$colwrap->applyFromArray(array(
		    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    ),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'		=> PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap' => true
			),
			'fill' 	=> array(
				'type'    	=> PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
       				'argb' => 'FFFFFF00'
    			),
    			'endcolor' => array(
        			'argb' => 'FFFFFF00'
    			),
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
		$oexcel->getProperties()->setCreator("Ardi Fianto")
								->setLastModifiedBy("Ardi Fianto");
		$si = $oexcel->setActiveSheetIndex(0);
		
		$baris = 1;
		$si->setCellValue($icell[0].$baris,"Plant");
		$si->setCellValue($icell[1].$baris,"Tanggal");
		$si->setCellValue($icell[2].$baris,"Jam");
		$nexcel = 3;
		foreach ($arr_item as $item => $item_nama) {
			$si->setCellValue($icell[$nexcel].$baris,$item_nama);
			$nexcel++;	
		}
		$si->setSharedStyle($coltitleSy, $icell[0].$baris.':'.$icell[($nexcel-1)].$baris);
		$baris++;
		foreach ($arr_nilai as $subplan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_jam) {
				foreach ($arr_jam as $keyjam => $lbljam) {
					$si->setCellValue($icell[0].$baris,$subplan);
					$si->setCellValue($icell[1].$baris,$tgl);
					$si->setCellValue($icell[2].$baris,$lbljam.":00");
					$nexcel = 3;
					if($arr_stop[$subplan][$tgl][$keyjam]) {
						$si->mergeCells($icell[$nexcel].$baris.':'.$icell[$nexcel+count($arr_item)-1].$baris);
						$si->setCellValue($icell[$nexcel].$baris,$arr_stop[$subplan][$tgl][$keyjam]);		
					} else {
						foreach ($arr_item as $item => $item_nama) {
							if(is_array($a_jam[$keyjam][$item])) {
								$nilainya = "";
								foreach ($a_jam[$keyjam][$item] as $qsm_id => $nilai) {
									$nilainya = $nilainya." ".$nilai; 	
								}
								$nilainya = substr($nilainya, 1);
							} else {
								$nilainya = "";
							}
							$si->setCellValue($icell[$nexcel].$baris,$nilainya);
							$nexcel++;
						}
					}
						
					$baris++;
				}
			}
		}
		$si->setSharedStyle($colborder, $icell[0].'2:'.$icell[(count($arr_item)+2)].($baris-1));
		$si->setTitle('Sheet1');
		$oexcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		if($_GET["tipe"] == "xlsx") {
			header('Content-Disposition: attachment;filename=Monitor_Setting_Spray_Dryer_v2.xlsx');
		} else if($_GET["tipe"] == "xls") {
			header('Content-Disposition: attachment;filename=Monitor_Setting_Spray_Dryer_v2.xls');
		} 
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header ('Cache-Control: cache, must-revalidate');
		header ('Pragma: public');
		if($_GET["tipe"] == "xlsx") {
			$objWriter = PHPExcel_IOFactory::createWriter($oexcel, 'Excel2007');
		} else if($_GET["tipe"] == "xls") {
			$objWriter = PHPExcel_IOFactory::createWriter($oexcel, 'Excel5');
		}
		$objWriter->save('php://output');
		exit;
	} else {
		$html = 'TIDAKADA';
		echo $html;
	}
}

function lihatdata(){
	global $app_plan_id;
	$qsm_id = $_POST['qsm_id'];
	$sql = "SELECT a.*
		from qc_sp_monitoring a
		where a.qsm_id = '{$qsm_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$datetime = explode(' ',$rh[qsm_date]);
	$rh[tgl] = cgx_dmy2ymd($datetime[0]);
	$rh[jam] = substr($datetime[1],0,5);
			
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[qsm_id].'</td><td>Di-input Oleh : '.$rh[qsm_user_create].'</td></tr><tr><td>Subplant : '.$rh[qsm_sub_plant].'</td><td>Tanggal Input : '.$rh[qsm_date_create].'</td></tr><tr><td>Tanggal : '.$rh[tgl].'</td><td>Di-edit Oleh : '.$rh[qsm_user_modify].'</td></tr><tr><td>Jam : '.$rh[jam].'</td><td>Tanggal Edit : '.$rh[qsm_date_modify].'</td></tr></table>';
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