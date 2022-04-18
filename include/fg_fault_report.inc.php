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
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= "and a.fgf_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.fgf_sub_plant as subplan, a.fgf_id, a.fgf_date, a.fgf_kiln, a.fgf_quality, a.fgf_type,
				   b.fapr_id, c.fapr_desc, b.eco_value, b.rj_value from qc_fg_fault_header a 
			join qc_fg_fault_detail b on a.fgf_id = b.fgf_id
			join qc_fg_fault_parameter c on b.fapr_id = c.fapr_id and a.fgf_sub_plant = c.sub_plant
			WHERE a.fgf_status='N' $whdua
			AND a.fgf_date >= '{$tglfrom}' 
			AND a.fgf_date <= '{$tglto}'
			ORDER BY a.fgf_sub_plant, a.fgf_date, a.fgf_kiln, a.fgf_id, CAST(b.fapr_id AS int) ASC";
	$responce->sql = $sql; 
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)) {
		foreach($qry as $r){
			$datetime = explode(' ',$r[fgf_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			$r[jam] = substr($datetime[1],0,2);
			
			$arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"]["$r[fapr_id]"]["$r[fgf_id]"] = $r[eco_value].'@'.$r[rj_value];

			$arr_quality["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"]["$r[fgf_id]"] = $r[fgf_quality];
			$arr_type["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"]["$r[fgf_id]"] = $r[fgf_type];


			$arr_line["$r[subplan]"]["$r[fapr_id]"] = $r[fapr_desc];

			$jmlcell["$r[fgf_kiln]"]["$r[jam]"] = $r[jam];
			$arr_jam["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"] = $r[jam];

			$arr_sumeco["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"] += $r[eco_value];
			$arr_sumrj["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"] += $r[rj_value];
		}
	}

	if(is_array($arr_nilai)) {

		
		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
		$html .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.1003.QC.02</div>
				  <div style="text-align:center;font-size:20px;font-weight:bold;">FAULT ANALISIS</div>
				  <table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
		$html .= '<div style="overflow-x:auto;"><table class="adaborder">';
		foreach ($arr_nilai as $subplan => $a_tgl) {

			

			foreach ($a_tgl as $tgl => $a_kiln) {

				$jmljamall = 0;
				foreach ($a_kiln as $kiln => $a_jam) {
					$jmljamall += count($a_jam);
				}

				$html .= '<tr><th colspan="'.(($jmljamall*2)+2).'" style="text-align:left;padding-top:20px;">SUBPLANT : '.$subplan.' | '.$tgl.'</th></tr>';
				$html .= '<tr style="background:#D1D1D1;"><th rowspan="2">NO</th>
							  <th rowspan="2">DEFECT</th>';
				foreach ($a_kiln as $kiln => $a_jam) {
					$jmljam = count($a_jam);
					$html .= '<th colspan="'.($jmljam*2).'">KILN '.Romawi($kiln).'</th>';
				}
				$html .= '</tr>';

				$html .= '<tr style="background:#D1D1D1;">';
				foreach ($a_kiln as $kiln => $a_jam) {
					$jmljam = count($a_jam);
					$html .= '<th colspan="'.($jmljam*2).'">AVERAGE DEFECT</th>';
				}
				$html .= '</tr>';
				
				$html .= '<tr>';
				$html .= '<td>&nbsp;</td>';
				$html .= '<td>Export Quality (%)</td>';

				foreach ($a_kiln as $kiln => $a_jam) {
					foreach ($a_jam as $jam => $item) {
						$html .= '<td colspan="2" class="text-center">';
						if(is_array($arr_quality[$subplan][$tgl][$kiln][$jam])) {
							foreach ($arr_quality[$subplan][$tgl][$kiln][$jam] as $q_id => $quality) {
								$html .= '<span onclick="lihatData(\''.$q_id.'\')">'.$quality.'</span>';
							}
						}else{
							$html .= '&nbsp;';
						}
						$html .= '</td>';
					}
				}

				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>&nbsp;</td>';
				$html .= '<td>Motive / Type</td>';

				foreach ($a_kiln as $kiln => $a_jam) {
					foreach ($a_jam as $jam => $item) {
						$html .= '<td colspan="2" class="text-center">';
						if(is_array($arr_type[$subplan][$tgl][$kiln][$jam])) {
							foreach ($arr_type[$subplan][$tgl][$kiln][$jam] as $t_id => $type) {
								$html .= '<span onclick="lihatData(\''.$t_id.'\')">'.$type.'</span>';
							}
						}else{
							$html .= '&nbsp;';
						}
						$html .= '</td>';
					}
				}

				$html .= '</tr>';


				$html .= '<tr>';
				$html .= '<td>&nbsp;</td>';
				$html .= '<td>Time</td>';
				foreach ($a_kiln as $kiln => $a_jam) {
					foreach ($a_jam as $jam => $item) {
						$html .= '<td colspan="2" class="text-center">'.$jam.':00</td>';
					}
				}
				$html .= '</tr>';

				$html .= '<tr style="background:#D1D1D1;">';
				$html .= '<th>&nbsp;</th>';
				$html .= '<th>&nbsp;</th>';
				foreach ($a_kiln as $kiln => $a_jam) {
					foreach ($a_jam as $jam => $item) {
						$html .= '<th class="text-center">ECO</th>';
						$html .= '<th class="text-center">RJ</th>';
					}
				}

				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>&nbsp;</td>';
				$html .= '<td>&nbsp;</td>';
				foreach ($a_kiln as $kiln => $a_jam) {
					foreach ($a_jam as $jam => $item) {
						$html .= '<td>&nbsp;</td>';
						$html .= '<td>&nbsp;</td>';
					}
				}
				$html .= '</tr>';

				$no_line =1;
				foreach ($arr_line[$subplan] as $line_id => $lineval) {
					$html .= '<tr>';
					$html .= '<td class="text-center">'.$no_line.'</td>';
					$html .= '<td>'.$lineval.'</td>';
					
					foreach ($a_kiln as $kiln => $a_jam) {
						foreach ($a_jam as $jam => $item) {
							if(is_array($arr_nilai[$subplan][$tgl][$kiln][$jam][$line_id])) {
								foreach ($arr_nilai[$subplan][$tgl][$kiln][$jam][$line_id] as $fgf_id => $value) {
									$val = explode("@",$value);
									$eco_value = $val[0];
									$rj_value  = $val[1];

									if($eco_value == 0){
										$eco_value = '&nbsp;';
									}else{
										$eco_value = $eco_value;
									}

									if($rj_value == 0){
										$rj_value = '&nbsp;';
									}else{
										$rj_value = $rj_value;
									}

									$html .= '<td class="text-right"><span onclick="lihatData(\''.$fgf_id.'\')">'.$eco_value.'</span></td>';
									$html .= '<td class="text-right"><span onclick="lihatData(\''.$fgf_id.'\')">'.$rj_value.'</span></td>';

									
								}
							}else{
								$html .= '<td>&nbsp;</td>';
								$html .= '<td>&nbsp;</td>';
							}
						}
					}


					$html .= '</tr>';
				$no_line++;
				}

				$html .= '<tr style="background:#D1D1D1;">';
				$html .= '<th>&nbsp;</th>';
				$html .= '<th class="text-center"> T O T A L </th>';
				foreach ($a_kiln as $kiln => $a_jam) {
					foreach ($a_jam as $jam => $item) {
						$ttl_sumeco = $arr_sumeco[$subplan][$tgl][$kiln][$jam];
						$ttl_sumrj  = $arr_sumrj[$subplan][$tgl][$kiln][$jam];

						$html .= '<th class="text-right">'.$ttl_sumeco.'</th>';
						$html .= '<th class="text-right">'.$ttl_sumrj.'</th>';
					}
				}
				$html .= '</tr>';

			}			  

		}

		$html .='</table></div>';

	} else {
		$html = 'TIDAKADA';
	}
	$responce->detailtabel = $html; 
	echo json_encode($responce);
}

function excel() {
	require_once("../libs/PHPExcel.php");
	global $app_plan_id;

	global $app_plan_id;
		$subplan = $_GET['subplan'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= "and a.fgf_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.fgf_sub_plant as subplan, a.fgf_id, a.fgf_date, a.fgf_kiln, a.fgf_quality, a.fgf_type,
				   b.fapr_id, c.fapr_desc, b.eco_value, b.rj_value from qc_fg_fault_header a 
			join qc_fg_fault_detail b on a.fgf_id = b.fgf_id
			join qc_fg_fault_parameter c on b.fapr_id = c.fapr_id and a.fgf_sub_plant = c.sub_plant
			WHERE a.fgf_status='N' $whdua
			AND a.fgf_date >= '{$tglfrom}' 
			AND a.fgf_date <= '{$tglto}'
			ORDER BY a.fgf_sub_plant, a.fgf_date, a.fgf_kiln, a.fgf_id, CAST(b.fapr_id AS int) ASC";
	$responce->sql = $sql; 
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)) {
		foreach($qry as $r){
			$datetime = explode(' ',$r[fgf_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			$r[jam] = substr($datetime[1],0,2);
			
			$arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"]["$r[fapr_id]"]["$r[fgf_id]"] = $r[eco_value].'@'.$r[rj_value];

			$arr_quality["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"]["$r[fgf_id]"] = $r[fgf_quality];
			$arr_type["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"]["$r[fgf_id]"] = $r[fgf_type];


			$arr_line["$r[subplan]"]["$r[fapr_id]"] = $r[fapr_desc];

			$arr_kiln["$r[fgf_kiln]"] = $r[fgf_kiln];
			
			$arr_cell["$r[fgf_kiln]"]["$r[jam]"] = $r[jam];

			$arr_jam["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"] = $r[jam];

			$arr_sumeco["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"] += $r[eco_value];
			$arr_sumrj["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"] += $r[rj_value];
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
       				'argb' => 'D1D1D1'
    			),
    			'endcolor' => array(
        			'argb' => 'D1D1D1'
    			),
			)
		));
		$coljudul = new PHPExcel_Style();
		$coljudul->applyFromArray(array(
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
		$oexcel->getProperties()->setCreator("Ilman Fahrur Rozy")
								->setLastModifiedBy("Ilman Fahrur Rozy");
		$si = $oexcel->setActiveSheetIndex(0);
		


		$baris = 1;

		$jmljamall = 6;

		$cel_all = 2+($jmljamall*2); 


		$si->mergeCells($icell[0].$baris.':'.$icell[$cel_all].$baris);
		$si->setCellValue($icell[0].$baris,'No : F.1003.QC.02');
		$si->setSharedStyle($colkanan, $icell[0].$baris.':'.$icell[$cel_all].$baris);
		$baris++;


		$si->mergeCells($icell[0].$baris.':'.$icell[$cel_all].$baris);
		$si->setCellValue($icell[0].$baris,'FAULT ANALISIS');
		$si->setSharedStyle($coljudul, $icell[0].$baris.':'.$icell[$cel_all].$baris);
		$baris++;

		$si->mergeCells($icell[0].$baris.':'.$icell[$cel_all].$baris);
		$si->setCellValue($icell[0].$baris,'TGL : '.$tgljudul);
		$si->setSharedStyle($coljudul, $icell[0].$baris.':'.$icell[$cel_all].$baris);
		$baris++;


		foreach ($arr_nilai as $subplan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_kiln) {
				$si->mergeCells($icell[0].$baris.':'.$icell[$cel_all].$baris);
				$si->setCellValue($icell[0].$baris,'SUBPLAN : '.$subplan.' | '.$tgl);
				$si->setSharedStyle($colboldaja, $icell[0].$baris.':'.$icell[$cel_all].$baris);
				$baris++;

				$si->mergeCells($icell[0].$baris.':'.$icell[0].($baris+1));
				$si->setCellValue($icell[0].$baris, 'NO');
				$si->mergeCells($icell[1].$baris.':'.$icell[1].($baris+1));
				$si->setCellValue($icell[1].$baris, 'DEFECT');
				$si->setSharedStyle($colwrap, $icell[0].$baris.':'.$icell[1].($baris+1));

				$cell_headkiln = 2;
				foreach ($a_kiln as $kiln => $a_jam) {
					$jmljam = count($a_jam)*2;
					$mergkiln = $cell_headkiln+$jmljam-1;

					$si->mergeCells($icell[$cell_headkiln].$baris.':'.$icell[$mergkiln].$baris);
					$si->setCellValue($icell[$cell_headkiln].$baris, 'KILN '.Romawi($kiln));
					$si->setSharedStyle($colwrap, $icell[$cell_headkiln].$baris.':'.$icell[$mergkiln].$baris);
					$cell_headkiln = $mergkiln+1;
				}
				$baris++;

				$cell_headkiln = 2;
				foreach ($a_kiln as $kiln => $a_jam) {
					$jmljam = count($a_jam)*2;
					$mergkiln = $cell_headkiln+$jmljam-1;

					$si->mergeCells($icell[$cell_headkiln].$baris.':'.$icell[$mergkiln].$baris);
					$si->setCellValue($icell[$cell_headkiln].$baris, 'AVERAGE DEFECT');
					$si->setSharedStyle($colwrap, $icell[$cell_headkiln].$baris.':'.$icell[$mergkiln].$baris);
					$cell_headkiln = $mergkiln+1;
				}
				$baris++;


				$si->setCellValue($icell[0].$baris, '');
				$si->setCellValue($icell[1].$baris, 'Export Quality ( % )');
				$c_a1 = 2;
				$c_a2 = 3;
				foreach ($a_kiln as $kiln => $a_jam) {

					foreach ($a_jam as $jam => $item) {
						$si->mergeCells($icell[$c_a1].$baris.':'.$icell[$c_a2].$baris);
						if(is_array($arr_quality[$subplan][$tgl][$kiln][$jam])) {
							foreach ($arr_quality[$subplan][$tgl][$kiln][$jam] as $q_id => $quality) {
								$si->setCellValue($icell[$c_a1].$baris, $quality);
							}
						}else{
							$si->setCellValue($icell[$c_a1].$baris, '');
						}
						$c_a1+= 2;
						$c_a2+= 2;
					}
				}
				$si->setSharedStyle($coltengah, $icell[0].$baris.':'.$icell[($c_a2-2)].$baris);
				$baris++;

				$si->setCellValue($icell[0].$baris, '');
				$si->setCellValue($icell[1].$baris, 'Motive / Type');
				$c_a1 = 2;
				$c_a2 = 3;
				foreach ($a_kiln as $kiln => $a_jam) {

					foreach ($a_jam as $jam => $item) {
						$si->mergeCells($icell[$c_a1].$baris.':'.$icell[$c_a2].$baris);
						if(is_array($arr_type[$subplan][$tgl][$kiln][$jam])) {
							foreach ($arr_type[$subplan][$tgl][$kiln][$jam] as $q_id => $type) {
								$si->setCellValue($icell[$c_a1].$baris, $type);
							}
						}else{
							$si->setCellValue($icell[$c_a1].$baris, '');
						}
						$c_a1+= 2;
						$c_a2+= 2;
					}
				}
				$si->setSharedStyle($coltengah, $icell[0].$baris.':'.$icell[($c_a2-2)].$baris);
				$baris++;


				$si->setCellValue($icell[0].$baris, '');
				$si->setCellValue($icell[1].$baris, 'Time');
				$c_a1 = 2;
				$c_a2 = 3;
				foreach ($a_kiln as $kiln => $a_jam) {
					foreach ($a_jam as $jam => $item) {
						$si->mergeCells($icell[$c_a1].$baris.':'.$icell[$c_a2].$baris);
						$si->setCellValue($icell[$c_a1].$baris, $jam.':00');
						$c_a1+= 2;
						$c_a2+= 2;
					}
				}
				$si->setSharedStyle($coltengah, $icell[0].$baris.':'.$icell[($c_a2-2)].$baris);
				$baris++;


				$si->setCellValue($icell[0].$baris, '');
				$si->setCellValue($icell[1].$baris, '');
				$c_a1 = 2;
				$c_a2 = 3;
				foreach ($a_kiln as $kiln => $a_jam) {
					foreach ($a_jam as $jam => $item) {
						$si->setCellValue($icell[$c_a1].$baris,'ECO');
						$si->setCellValue($icell[$c_a2].$baris,'RJ');
						$c_a1+= 2;
						$c_a2+= 2;
					}
				}
				$si->setSharedStyle($colwrap, $icell[0].$baris.':'.$icell[($c_a2-2)].$baris);
				$baris++;

				$si->setCellValue($icell[0].$baris, '');
				$si->setCellValue($icell[1].$baris, '');
				$c_a1 = 2;
				$c_a2 = 3;
				foreach ($a_kiln as $kiln => $a_jam) {
					foreach ($a_jam as $jam => $item) {
						$si->setCellValue($icell[$c_a1].$baris,'');
						$si->setCellValue($icell[$c_a2].$baris,'');
						$c_a1+= 2;
						$c_a2+= 2;
					}
				}
				$si->setSharedStyle($colborder, $icell[0].$baris.':'.$icell[($c_a2-2)].$baris);
				$baris++;

				$no_line =1;
				foreach ($arr_line[$subplan] as $line_id => $lineval) {

					$si->setCellValue($icell[0].$baris,$no_line);
					$si->setCellValue($icell[1].$baris,$lineval);
					$si->setSharedStyle($colborder, $icell[0].$baris.':'.$icell[1].$baris);
					
					$c_a1 = 2;
					$c_a2 = 3;
					foreach ($a_kiln as $kiln => $a_jam) {
						foreach ($a_jam as $jam => $item) {
							if(is_array($arr_nilai[$subplan][$tgl][$kiln][$jam][$line_id])) {
								foreach ($arr_nilai[$subplan][$tgl][$kiln][$jam][$line_id] as $fgf_id => $value) {
									$val = explode("@",$value);
									$eco_value = $val[0];
									$rj_value  = $val[1];

									if($eco_value == 0){
										$eco_value = '';
									}else{
										$eco_value = $eco_value;
									}

									if($rj_value == 0){
										$rj_value = '';
									}else{
										$rj_value = $rj_value;
									}

									$si->setCellValue($icell[$c_a1].$baris,$eco_value);
									$si->setCellValue($icell[$c_a2].$baris,$rj_value);
								$c_a1+= 2;
								$c_a2+= 2;	
								}
							}else{
								$si->setCellValue($icell[$c_a1].$baris,'');
								$si->setCellValue($icell[$c_a2].$baris,'');
								$c_a1+= 2;
								$c_a2+= 2;
							}
						}
					}
					$si->setSharedStyle($colborder, $icell[0].$baris.':'.$icell[($c_a2-2)].$baris);


				$baris++;
				$no_line++;
				}


				$html .= '<tr style="background:#3e3b3b1f;">';
				$html .= '<th>&nbsp;</th>';
				$html .= '<th class="text-center"> T O T A L </th>';

				$si->setCellValue($icell[0].$baris,'');
				$si->setCellValue($icell[1].$baris,'T O T A L');

				$c_a1 = 2;
				$c_a2 = 3;
				foreach ($a_kiln as $kiln => $a_jam) {
					foreach ($a_jam as $jam => $item) {
						$ttl_sumeco = $arr_sumeco[$subplan][$tgl][$kiln][$jam];
						$ttl_sumrj  = $arr_sumrj[$subplan][$tgl][$kiln][$jam];

						$si->setCellValue($icell[$c_a1].$baris,$ttl_sumeco);
						$si->setCellValue($icell[$c_a2].$baris,$ttl_sumrj);
					$c_a1+= 2;
					$c_a2+= 2;
					}
				}
				$si->setSharedStyle($colwrap, $icell[0].$baris.':'.$icell[($c_a2-2)].$baris);
				$baris++;

				$baris+=3;
			}
		}
		

		$si->setTitle('Sheet1');
		$oexcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		if($_GET["tipe"] == "xlsx") {
			header('Content-Disposition: attachment;filename=Fault_Analisis.xlsx');
		} else if($_GET["tipe"] == "xls") {
			header('Content-Disposition: attachment;filename=Fault_Analisis.xls');
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
	$fgf_id = $_POST['qsm_id'];
	$sql = "SELECT * from qc_fg_fault_header WHERE fgf_id = '{$fgf_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$datetime = explode(' ',$rh[fgf_date]);
	$rh[tgl] = cgx_dmy2ymd($datetime[0]);
	$rh[jam] = substr($datetime[1],0,5);
			
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[fgf_id].'</td><td>Di-input Oleh : '.$rh[fgf_user_create].'</td></tr><tr><td>Subplant : '.$rh[fgf_sub_plant].'</td><td>Tanggal Input : '.$rh[fgf_date_create].'</td></tr><tr><td>Tanggal : '.$rh[tgl].'</td><td>Di-edit Oleh : '.$rh[fgf_user_modify].'</td></tr><tr><td>Jam : '.$rh[jam].'</td><td>Tanggal Edit : '.$rh[fgf_date_modify].'</td></tr></table>';
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