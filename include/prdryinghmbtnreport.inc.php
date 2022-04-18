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
	$subplan = $_GET['subplan'];
	$shift   = $_GET['hph_shift'];
	$press   = $_GET['hph_press'];
	$line 	 = $_GET['hph_line'];

	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];

	$whdua = "";
	if($subplan <> 'All') {
	    $whdua .= "and hph_sub_plant = '".$subplan."'";
	}

	if($shift <> 'All') {
	    $whdua .= "and hph_shift = '".$shift."'";
	}

	if($press <> 'All') {
	    $whdua .= "and hph_press = '".$press."'";
	}

	if($line <> 'All') {
	    $whdua .= "and hph_line = '".$line."'";
	}

	$sql = "SELECT hph_sub_plant as subplan, hph_date, hph_shift , hph_press, hph_line, hph_id
			from qc_pd_hp_header where hph_status = 'N' $whdua and hph_date >= '{$tglfrom}' and hph_date <= '{$tglto}'
			ORDER BY hph_sub_plant, hph_date, hph_shift, hph_press, hph_line, hph_id ASC";

	$responce->sql = $sql; 
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)) {
	    foreach($qry as $r){
	        $datetime = explode(' ',$r[hph_date]);
	        $r[tgl] = cgx_dmy2ymd($datetime[0]);
	        $r[jam] = substr($datetime[1],0,2);

	        $arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[hph_shift]"]["$r[hph_press]"]["$r[hph_line]"]["$r[hph_id]"] = $r[hph_id];
	       
	    }
	}


	if(is_array($arr_nilai)) {
		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
		$html .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.901.PP.14</div>
				  <div style="text-align:center;font-size:20px;font-weight:bold;">LAPORAN HAMBATAN PRESS</div>
				  <table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
		foreach ($arr_nilai as $subplan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_shift) {
				foreach ($a_shift as $shift => $a_press) {
					foreach ($a_press as $press => $a_line) {
						foreach ($a_line as $line => $a_id) {
							$html .= '<div style="overflow-x:auto;"><table class="adaborder">';
							$html .= '<tr>';
	                        $html .= '   <th class="text-left" colspan="4">SUB PLANT : '.$subplan.' | TANGGAL : '.$tgl.'</th>';
	                        $html .= '</tr>';
	                        $html .= '<tr>';
	                        $html .= '   <th class="text-left" colspan="4">SHIFT : '.$shift.' | PRESS : '.$press.' | LINE : '.$line.'</th>';
	                        $html .= '</tr>';
							
							$html .= '<tr>';
							$html .= '   <th style="background:#D1D1D1;">JAM START</th>
										 <th style="background:#D1D1D1;">JAM STOP</th>
										 <th style="background:#D1D1D1;">JML MENIT</th>
										 <th style="background:#D1D1D1;">KET. HAMBATAN</th>';
							$html .= '</tr>';

							foreach ($a_id as $iddata => $id) {
								
								$sql2 = "SELECT to_char(hpd_date_start, 'HH:MI') AS starttime, to_char(hpd_date_stop, 'HH:MI') AS stoptime, hpd_value
										from qc_pd_hp_detail where hph_id = '{$id}' ORDER BY hpd_date_start ASC";

								$responce->sql = $sql2; 
								$qry2 = dbselect_plan_all($app_plan_id, $sql2);
								if(is_array($qry2)) {
								    foreach($qry2 as $r2){
								        $jml = strtotime($r2[stoptime])-strtotime($r2[starttime]);
										$jmlmenit = $jml/60;

										$html .= '<tr>';
										$html .= '   <td style="text-align:center;"><span onclick="lihatData(\''.$id.'\')">'.$r2[starttime].'</span></td>
													 <td style="text-align:center;"><span onclick="lihatData(\''.$id.'\')">'.$r2[stoptime].'</span></td>
													 <td style="text-align:center;">'.$jmlmenit.'</td>
													 <td><span onclick="lihatData(\''.$id.'\')">'.nl2br(htmlspecialchars($r2[hpd_value])).'</span></td>';
										$html .= '</tr>';
								    }
								}else{
										$html .= '<tr><td colspan="4">&nbsp;</td></tr>';
								}
							}

							$html .= '</table></div>';
							$html .= '<br>';
							$html .= '<br>';
						}
					}
				}
			}
		}
	}else{
		$html = 'TIDAKADA';
	}
	
	$responce->detailtabel = $html; 
	echo json_encode($responce);
}

function excel() {
	require_once("../libs/PHPExcel.php");
	global $app_plan_id;
	$subplan = $_GET['subplan'];
	$shift   = $_GET['hph_shift'];
	$press   = $_GET['hph_press'];
	$line 	 = $_GET['hph_line'];

	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];

	$whdua = "";
	if($subplan <> 'All') {
	    $whdua .= "and hph_sub_plant = '".$subplan."'";
	}

	if($shift <> 'All') {
	    $whdua .= "and hph_shift = '".$shift."'";
	}

	if($press <> 'All') {
	    $whdua .= "and hph_press = '".$press."'";
	}

	if($line <> 'All') {
	    $whdua .= "and hph_line = '".$line."'";
	}

	$sql = "SELECT hph_sub_plant as subplan, hph_date, hph_shift , hph_press, hph_line, hph_id
			from qc_pd_hp_header where hph_status = 'N' $whdua and hph_date >= '{$tglfrom}' and hph_date <= '{$tglto}'
			ORDER BY hph_sub_plant, hph_date, hph_shift, hph_press, hph_line, hph_id ASC";
	
	$responce->sql = $sql; 
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)) {
	    foreach($qry as $r){
	        $datetime = explode(' ',$r[hph_date]);
	        $r[tgl] = cgx_dmy2ymd($datetime[0]);
	        $r[jam] = substr($datetime[1],0,2);

	        $arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[hph_shift]"]["$r[hph_press]"]["$r[hph_line]"]["$r[hph_id]"] = $r[hph_id];
	       
	    }
	}
	
	if(is_array($arr_nilai)) {

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
		$coltitleSy2 = new PHPExcel_Style();
		$coltitleSy2->applyFromArray(array(
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
		    ),
			'fill' 	=> array(
				'type'    	=> PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
       				'argb' => 'D1D1D1'
    			),
    			'endcolor' => array(
        			'argb' => 'D1D1D1'
    			),
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

		$colborder2 = new PHPExcel_Style();
		$colborder2->applyFromArray(array(
		    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    ),
		    'alignment' => array(
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		    	'vertical'		=> PHPExcel_Style_Alignment::VERTICAL_TOP,
		    )
		));

		$colborderjam = new PHPExcel_Style();
		$colborderjam->applyFromArray(array(
		    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    ),
		    'alignment' => array(
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		    	'vertical'		=> PHPExcel_Style_Alignment::VERTICAL_TOP,
		    )
		));

		$colborderket = new PHPExcel_Style();
		$colborderket->applyFromArray(array(
		    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    ),
		    'alignment' => array(
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
		    	'vertical'		=> PHPExcel_Style_Alignment::VERTICAL_TOP,
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
		$colttd = new PHPExcel_Style();
		$colttd->applyFromArray(array(
			'font'		=> array(
				'bold' 	=> true
			),
			'alignment' => array(
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		    	'vertical'		=> PHPExcel_Style_Alignment::VERTICAL_CENTER
		    )
		));		
		$colboldaja = new PHPExcel_Style();
		$colboldaja->applyFromArray(array(
		    'font'		=> array(
				'bold' 	=> true
			)
		));
		$colboldunderline = new PHPExcel_Style();
		$colboldunderline->applyFromArray(array(
		    'font'		=> array(
				'bold' 	=> true,
				'underline'	=> true
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
		$oexcel->getProperties()->setCreator("Ilman Fahrurrozy")
								->setLastModifiedBy("Ilman Fahrurrozy");
		$si = $oexcel->setActiveSheetIndex(0);

		$baris = 1;
		
		$si->mergeCells($icell[0].$baris.':'.$icell[3].$baris);
		$si->setCellValue($icell[0].$baris,'NO. F.901.PP.14');
		$si->setSharedStyle($colkanan, $icell[0].$baris.':'.$icell[3].$baris);
		$baris++;

		$baris++;
		$si->mergeCells($icell[0].$baris.':'.$icell[3].$baris);
		$si->setCellValue($icell[0].$baris,'LAPORAN HAMBATAN PRESS');
		$si->setSharedStyle($coltengah, $icell[0].$baris.':'.$icell[3].$baris);
		$baris++;

		$si->mergeCells($icell[0].$baris.':'.$icell[3].$baris);
		$si->setCellValue($icell[0].$baris,'TANGGAL : '.$tgljudul);
		$si->setSharedStyle($coltengah, $icell[0].$baris.':'.$icell[3].$baris);
		$baris++;
		

		foreach ($arr_nilai as $subplan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_shift) {
				foreach ($a_shift as $shift => $a_press) {
					foreach ($a_press as $press => $a_line) {
						foreach ($a_line as $line => $a_id) {

							$si->mergeCells($icell[0].$baris.':'.$icell[3].$baris);
							$si->setCellValue($icell[0].$baris,'SUB PLANT : '.$subplan.' | TANGGAL : '.$tgl);
							$si->setSharedStyle($colboldaja, $icell[0].$baris.':'.$icell[3].$baris);
							$baris++;

							$si->mergeCells($icell[0].$baris.':'.$icell[3].$baris);
							$si->setCellValue($icell[0].$baris,'SHIFT : '.$shift.' | PRESS : '.$press.' | LINE : '.$line);
							$si->setSharedStyle($colboldaja, $icell[0].$baris.':'.$icell[3].$baris);
							$baris++;

							$si->setCellValue($icell[0].$baris,'JAM START');
							$si->setCellValue($icell[1].$baris,'JAM STOP');
							$si->setCellValue($icell[2].$baris,'JUMLAH MENIT');
							$si->setCellValue($icell[3].$baris,'KETERANGAN HAMBATAN');
							$si->setSharedStyle($coltitleSy2, $icell[0].$baris.':'.$icell[3].$baris);
							$baris++;

							foreach ($a_id as $iddata => $id) {
								
								$sql2 = "SELECT to_char(hpd_date_start, 'HH:MI') AS starttime, to_char(hpd_date_stop, 'HH:MI') AS stoptime, hpd_value
										from qc_pd_hp_detail where hph_id = '{$id}' ORDER BY hpd_date_start ASC";

								$responce->sql = $sql2; 
								$qry2 = dbselect_plan_all($app_plan_id, $sql2);
								if(is_array($qry2)) {
								    foreach($qry2 as $r2){
								        $jml = strtotime($r2[stoptime])-strtotime($r2[starttime]);
										$jmlmenit = $jml/60;

										$si->setCellValue($icell[0].$baris,$r2[starttime]);
										$si->setCellValue($icell[1].$baris,$r2[stoptime]);
										$si->setCellValue($icell[2].$baris,$jmlmenit);
										$si->setCellValue($icell[3].$baris,$r2[hpd_value]);
										$si->setSharedStyle($colborder2, $icell[0].$baris.':'.$icell[2].$baris);
										$si->setSharedStyle($colborder, $icell[3].$baris.':'.$icell[3].$baris);
										$baris++;
								    }
								}else{
										$html .= '<tr><td colspan="4"></td></tr>';
										$si->mergeCells($icell[0].$baris.':'.$icell[3].$baris);
										$baris++;
								}
							}

							$baris+= 2;
						}
					}
				}
			}
		}
		$si->setTitle('Sheet1');
		$oexcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		if($_GET["tipe"] == "xlsx") {
			header('Content-Disposition: attachment;filename=Laporan_hambatan_Press.xlsx');
		} else if($_GET["tipe"] == "xls") {
			header('Content-Disposition: attachment;filename=Laporan_hambatan_Press.xls');
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
		$html = 'TIDAK ADA DATA';
		echo $html;
	}
}

function lihatdata(){
	global $app_plan_id;
	$hph_id = $_POST['hph_id'];
	$sql = "SELECT * from qc_pd_hp_header where hph_id = '{$hph_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$datetime = explode(' ',$rh[hph_date]);
	$rh[tgl] = cgx_dmy2ymd($datetime[0]);

	$datetime2 = explode(' ',$rh[hph_date]);
	$rh[jam] = substr($datetime[1],0,5);
			
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[hph_id].'</td><td>Di-input Oleh : '.$rh[hph_user_create].'</td></tr><tr><td>Subplant : '.$rh[hph_sub_plant].'</td><td>Tanggal Input : '.$rh[hph_date_create].'</td></tr><tr><td>Tanggal : '.$rh[tgl].'</td><td>Di-edit Oleh : '.$rh[hph_user_modify].'</td></tr><tr><td>&nbsp;</td><td>Tanggal Edit : '.$rh[hph_date_modify].'</td></tr></table>';
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