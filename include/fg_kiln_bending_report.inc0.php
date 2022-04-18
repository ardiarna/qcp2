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
		$whdua .= "and a.kb_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.kb_sub_plant as subplan, a.kb_date, a.kb_kiln, a.kb_temp, a.kb_speed, a.kb_presi, a.kb_desc, a.kb_wa, a.kb_ac, a.kb_wm, a.kb_tt, b.kbd_posisi, b.kbd_kg, b.kbd_cm, a.kb_id
			from qc.qc_fg_kiln_bending_header a 
			left join qc.qc_fg_kiln_bending_detail b on a.kb_id = b.kb_id
			where a.kb_status = 'N' $whdua and a.kb_date >= '{$tglfrom}' and a.kb_date <= '{$tglto}'
			ORDER BY a.kb_sub_plant, b.kbd_posisi, a.kb_date, a.kb_kiln, a.kb_id ASC";
	$responce->sql = $sql; 
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)) {
		foreach($qry as $r){
			$datetime = explode(' ',$r[kb_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			$r[jam] = substr($datetime[1],0,2);
			

			$arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kbd_posisi]"]["$r[kb_id]"] = $r[kbd_kg].'##'.$r[kbd_cm];
			$arr_posisi["$r[subplan]"]["$r[tgl]"]["$r[kbd_posisi]"] = $r[kbd_posisi];

			$arr_sumkg["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"] += $r[kbd_kg];
			$arr_sumcm["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"] += $r[kbd_cm];


			$arr_temp["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_temp];
			$arr_speed["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_speed];
			$arr_presi["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_presi];
			$arr_desc["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_desc];
			$arr_wa["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_wa];
			$arr_ac["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_ac];
			$arr_wm["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_wm];
			$arr_tt["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_tt];
		}
	}

	if(is_array($arr_nilai)) {

		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
		$html .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.1003.QC.06</div>
				  <div style="text-align:center;font-size:20px;font-weight:bold;">DATA HASIL PENGAMATAN</div>
				  <div style="text-align:center;font-size:20px;font-weight:bold;">SPEED KILN VS BENDING STRENGTH</div>
				  <table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
		$html .= '<div style="overflow-x:auto;"><table class="adaborder">';
		foreach ($arr_nilai as $subplan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_jam) {

				$jmlposisi = count($arr_posisi[$subplan][$tgl]);
				$jmlklm = $jmlposisi+9;
				$html .= '<tr><th colspan="3" style="text-align:left;background:#D1D1D1;">SUBPLANT : '.$subplan.'</th><th colspan="'.($jmlklm-1).'" style="text-align:left;background:#D1D1D1;">'.$tgl.'</th></tr>';
				$html .= '<tr><th rowspan="2">NO</th>
							  <th rowspan="2">JAM</th>
							  <th rowspan="2">KILN</th>
							  <th>TEMP</th>
							  <th>SPEED</th>
							  <th colspan="'.$jmlposisi.'">BENDING STRENGTH / BREAKING STRENGTH</th>
							  <th rowspan="2">B . S <br> RATA - RATA</th>
							  <th rowspan="2">PRESI</th>
							  <th rowspan="2">WATER ABORTION</th>
							  <th rowspan="2">AUTOCLAVE</th>
							  <th rowspan="2">WATERMARK</th>
							  <th rowspan="2">KETERANGAN</th>
						  </tr>';
				$html .= '<tr>';
				$html .= '<th>( C )</th>';
				$html .= '<th>( Menit )</th>';
				foreach ($arr_posisi[$subplan][$tgl] as $posisi) {
					$html .= '<th>POSISI '.$posisi.'</th>';
				}
				$html .= '</tr>';


				$nojam =1;
				foreach ($a_jam as $jam => $a_kiln) {
					$html .= '<tr>';
					$html .= '<th>'.$nojam.'</th>';
					$html .= '<th style="background:#D1D1D1;">'.$jam.':00</th>';

					for ($i=1; $i <= $jmlklm; $i++) { 
						$html .= '<th>&nbsp;</th>';
					}

					$html .= '</tr>';

					

					foreach ($a_kiln as $kiln => $a_posisi) {
						$html .= '<tr>';
						$html .= '<th>&nbsp;</th>';
						$html .= '<th>&nbsp;</th>';
						$html .= '<th>'.Romawi($kiln).'</th>';

							$html .= '<td align="center">';
							if(is_array($arr_temp[$subplan][$tgl][$jam][$kiln])) {
	                            foreach ($arr_temp[$subplan][$tgl][$jam][$kiln] as $tempid => $a_tempVal) {
                                    $html .= '<span onclick="lihatData(\''.$tempid.'\')">'.$a_tempVal.'</span> ';
	                            }
	                        }else{
	                            $html .= '&nbsp;';
	                        }

							$html .= '</td>';	

							$html .= '<td align="center">';
							if(is_array($arr_speed[$subplan][$tgl][$jam][$kiln])) {
	                            foreach ($arr_speed[$subplan][$tgl][$jam][$kiln] as $speedid => $a_speedVal) {
                                    $html .= '<span onclick="lihatData(\''.$speedid.'\')">'.$a_speedVal.'</span> ';
	                            }
	                        }else{
	                            $html .= '&nbsp;';
	                        }

							$html .= '</td>';	



							foreach ($arr_posisi[$subplan][$tgl] as $posisi) {
								$html .= '<td align="center">';
								if(is_array($a_posisi[$posisi])) {
									
									foreach ($a_posisi[$posisi] as $nid => $nidVal) {
										$nidVal = explode( '##', $nidVal);
										$html .= '<span onclick="lihatData(\''.$nid.'\')">'.$nidVal[0].'/'.$nidVal[1].'</span> ';
									}
									
								}else{
									$html .= '&nbsp;';
								}
								$html .= '</td>';
							}


								$jmlposisikiln = count($a_posisi);
                                $TTLnkg = $arr_sumkg[$subplan][$tgl][$jam][$kiln];
                                $TTLncm = $arr_sumcm[$subplan][$tgl][$jam][$kiln];

                                

                                if($jmlposisikiln == 0){
                                    $ratarata1 = '&nbsp;';
                                }else{
                                    $rtkg = ($TTLnkg/$jmlposisikiln);
                                    $rtcm = ($TTLncm/$jmlposisikiln);

                                    $ratarata1 = number_format($rtkg,1).'/'.number_format($rtcm,1); 
                                    if($ratarata1 == '0.0/0.0'){ 
                                        $ratarata1 = '&nbsp;';
                                    }else{
                                        $ratarata1 = $ratarata1;    
                                    }
                                }

                                

                            $html .= '<td align="center">'.$ratarata1.'</td>';



							$html .= '<td align="center">';
							if(is_array($arr_presi[$subplan][$tgl][$jam][$kiln])) {
	                            foreach ($arr_presi[$subplan][$tgl][$jam][$kiln] as $presiid => $a_presiVal) {
                                    $html .= '<span onclick="lihatData(\''.$presiid.'\')">'.$a_presiVal.'</span> ';
	                            }
	                        }else{
	                            $html .= '&nbsp;';
	                        }

							$html .= '</td>';

							$html .= '<td>';
							if(is_array($arr_wa[$subplan][$tgl][$jam][$kiln])) {
	                            foreach ($arr_wa[$subplan][$tgl][$jam][$kiln] as $waid => $a_waVal) {
                                    $html .= '<span onclick="lihatData(\''.$waid.'\')">'.$a_waVal.'</span> ';
	                            }
	                        }else{
	                            $html .= '&nbsp;';
	                        }

							$html .= '</td>';

							$html .= '<td>';
							if(is_array($arr_ac[$subplan][$tgl][$jam][$kiln])) {
	                            foreach ($arr_ac[$subplan][$tgl][$jam][$kiln] as $acid => $a_acVal) {
                                    $html .= '<span onclick="lihatData(\''.$acid.'\')">'.$a_acVal.'</span> ';
	                            }
	                        }else{
	                            $html .= '&nbsp;';
	                        }

							$html .= '</td>';

							$html .= '<td>';
							if(is_array($arr_wm[$subplan][$tgl][$jam][$kiln])) {
	                            foreach ($arr_wm[$subplan][$tgl][$jam][$kiln] as $wmid => $a_wmVal) {
                                    $html .= '<span onclick="lihatData(\''.$wmid.'\')">'.$a_wmVal.'</span> ';
	                            }
	                        }else{
	                            $html .= '&nbsp;';
	                        }

							$html .= '</td>';
								
							$html .= '<td>';
							if(is_array($arr_desc[$subplan][$tgl][$jam][$kiln])) {
	                            foreach ($arr_desc[$subplan][$tgl][$jam][$kiln] as $descid => $a_descVal) {
                                    $html .= '<span onclick="lihatData(\''.$descid.'\')">'.$a_descVal.'</span> ';
	                            }
	                        }else{
	                            $html .= '&nbsp;';
	                        }

							$html .= '</td>';


						$html .= '</tr>';					
					}
					$nojam++;
				}

				$html .= '<tr><th colspan="'.(11+$jmlposisi).'">&nbsp;</th></tr>';

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

	$subplan = $_GET['subplan'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
	    $whdua .= "and a.kb_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.kb_sub_plant as subplan, a.kb_date, a.kb_kiln, a.kb_temp, a.kb_speed, a.kb_presi, a.kb_desc, a.kb_wa, a.kb_ac, a.kb_wm, a.kb_tt, b.kbd_posisi, b.kbd_kg, b.kbd_cm, a.kb_id
	        from qc.qc_fg_kiln_bending_header a 
	        left join qc.qc_fg_kiln_bending_detail b on a.kb_id = b.kb_id
	        where a.kb_status = 'N' $whdua and a.kb_date >= '{$tglfrom}' and a.kb_date <= '{$tglto}'
	        ORDER BY a.kb_sub_plant, b.kbd_posisi, a.kb_date, a.kb_kiln, a.kb_id ASC";
	$responce->sql = $sql; 
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)) {
	    foreach($qry as $r){
	        $datetime = explode(' ',$r[kb_date]);
	        $r[tgl] = cgx_dmy2ymd($datetime[0]);
	        $r[jam] = substr($datetime[1],0,2);
	        

	        $arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kbd_posisi]"]["$r[kb_id]"] = $r[kbd_kg].'##'.$r[kbd_cm];
	        $arr_posisi["$r[subplan]"]["$r[tgl]"]["$r[kbd_posisi]"] = $r[kbd_posisi];

	        $arr_sumkg["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"] += $r[kbd_kg];
	        $arr_sumcm["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"] += $r[kbd_cm];


	        $arr_temp["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_temp];
	        $arr_speed["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_speed];
	        $arr_presi["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_presi];
	        $arr_desc["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_desc];
	        $arr_wa["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_wa];
	        $arr_ac["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_ac];
	        $arr_wm["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_wm];
	        $arr_tt["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_tt];
	        $arr_kolom["$r[kbd_posisi]"] = $r[kbd_posisi];
	    }
	}
	
	if(is_array($arr_nilai)) {

		$jmlposisi = count($arr_kolom);
		$jmlposisiall = 8+$jmlposisi;


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
		$coltitleSyJam = new PHPExcel_Style();
		$coltitleSyJam->applyFromArray(array(
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
		$si->mergeCells($icell[0].$baris.':'.$icell[$jmlposisiall].$baris);
		$si->setCellValue($icell[0].$baris,'No : F.1003.QC.06');
		$si->setSharedStyle($colkanan, $icell[0].$baris.':'.$icell[$jmlposisiall].$baris);
		$baris++;

		$si->mergeCells($icell[0].$baris.':'.$icell[$jmlposisiall].$baris);
		$si->setCellValue($icell[0].$baris,'DATA HASIL PENGAMATAN');
		$si->setSharedStyle($coljudul, $icell[0].$baris.':'.$icell[$jmlposisiall].$baris);
		$baris++;

		$si->mergeCells($icell[0].$baris.':'.$icell[$jmlposisiall].$baris);
		$si->setCellValue($icell[0].$baris,'SPEED KILN VS BENDING STRENGTH');
		$si->setSharedStyle($coljudul, $icell[0].$baris.':'.$icell[$jmlposisiall].$baris);
		$baris++;

		$si->mergeCells($icell[0].$baris.':'.$icell[$jmlposisiall].$baris);
		$si->setCellValue($icell[0].$baris,'TGL : '.$tgljudul);
		$si->setSharedStyle($coljudul, $icell[0].$baris.':'.$icell[$jmlposisiall].$baris);
		$baris +=3;


		foreach ($arr_nilai as $subplan => $a_tgl) {
            foreach ($a_tgl as $tgl => $a_jam) {
            	$jmlposisi = count($arr_posisi[$subplan][$tgl]);
                $jmlklm = $jmlposisi+4+4;

                $si->mergeCells($icell[0].$baris.':'.$icell[2].$baris);
				$si->setCellValue($icell[0].$baris,'SUBPLANT '.$subplan);
				$si->mergeCells($icell[3].$baris.':'.$icell[$jmlklm].$baris);
				$si->setCellValue($icell[3].$baris, $tgl);
				$si->setSharedStyle($colboldaja, $icell[0].$baris.':'.$icell[$jmlklm].$baris);
				$baris++;

                $si->mergeCells($icell[0].$baris.':'.$icell[0].($baris+1));
				$si->setCellValue($icell[0].$baris,'NO');
				$si->mergeCells($icell[1].$baris.':'.$icell[1].($baris+1));
				$si->setCellValue($icell[1].$baris,'JAM');
				$si->mergeCells($icell[2].$baris.':'.$icell[2].($baris+1));
				$si->setCellValue($icell[2].$baris,'KILN');
				$si->setCellValue($icell[3].$baris,'TEMP');
				$si->setCellValue($icell[4].$baris,'SPEED');

				$jmlcolh = $jmlposisi+4;
				$si->mergeCells($icell[5].$baris.':'.$icell[$jmlcolh].$baris);
				$si->setCellValue($icell[5].$baris,'BENDING STRENGTH / BREAKING STRENGTH');

				$jmlcolh++;
				$si->mergeCells($icell[$jmlcolh].$baris.':'.$icell[$jmlcolh].($baris+1));
				$si->setCellValue($icell[$jmlcolh].$baris,'B.S RATA - RATA');
				$jmlcolh++;
				$si->mergeCells($icell[$jmlcolh].$baris.':'.$icell[$jmlcolh].($baris+1));
				$si->setCellValue($icell[$jmlcolh].$baris,'PRESI');
				$jmlcolh++;
				$si->mergeCells($icell[$jmlcolh].$baris.':'.$icell[$jmlcolh].($baris+1));
				$si->setCellValue($icell[$jmlcolh].$baris,'WATER ABORTION');
				$jmlcolh++;
				$si->mergeCells($icell[$jmlcolh].$baris.':'.$icell[$jmlcolh].($baris+1));
				$si->setCellValue($icell[$jmlcolh].$baris,'AUTOCLAVE');
				$jmlcolh++;
				$si->mergeCells($icell[$jmlcolh].$baris.':'.$icell[$jmlcolh].($baris+1));
				$si->setCellValue($icell[$jmlcolh].$baris,'WATERMARK');
				$jmlcolh++;
				$si->mergeCells($icell[$jmlcolh].$baris.':'.$icell[$jmlcolh].($baris+1));
				$si->setCellValue($icell[$jmlcolh].$baris,'KETERANGAN');

				$si->setSharedStyle($coltitleSy, $icell[0].$baris.':'.$icell[$jmlcolh].$baris);

				$baris++;

				$si->setCellValue($icell[3].$baris,'( C )');
				$si->setCellValue($icell[4].$baris,'( Menit )');

				$cellap = 4; 
				foreach ($arr_posisi[$subplan][$tgl] as $posisi) {
					$cellap++;
                    $si->setCellValue($icell[$cellap].$baris,'POSISI '.$posisi);
                }
                    $si->setSharedStyle($coltitleSy, $icell[0].$baris.':'.$icell[$jmlcolh].$baris);
				$baris++;


				$nojam =1;
                foreach ($a_jam as $jam => $a_kiln) {
                	$si->setCellValue($icell[0].$baris,$nojam);
                	$si->setSharedStyle($coltitleSy, $icell[0].$baris.':'.$icell[0].$baris);
                	$si->setCellValue($icell[1].$baris,$jam.':00');
                	$si->setSharedStyle($coltitleSyJam, $icell[1].$baris.':'.$icell[1].$baris);
                	$si->setSharedStyle($coltitleSy, $icell[2].$baris.':'.$icell[$jmlcolh].$baris);

                	$baris++;


                	foreach ($a_kiln as $kiln => $a_posisi) {
                        $si->setCellValue($icell[2].$baris,Romawi($kiln));
                        $si->setSharedStyle($coltitleSy, $icell[0].$baris.':'.$icell[0].$baris);

                        $a_tempValz = '';
                        if(is_array($arr_temp[$subplan][$tgl][$jam][$kiln])) {
                            foreach ($arr_temp[$subplan][$tgl][$jam][$kiln] as $tempid => $a_tempVal) {
                                $a_tempValz .= $a_tempVal;
                            }
                        }else{
                            $a_tempValz .= '';
                        }
                        $si->setCellValue($icell[3].$baris, $a_tempValz);


                        $a_speedValz = '';
                        if(is_array($arr_speed[$subplan][$tgl][$jam][$kiln])) {
                            foreach ($arr_speed[$subplan][$tgl][$jam][$kiln] as $speedid => $a_speedVal) {
                                $a_speedValz .= $a_speedVal;
                            }
                        }else{
                            $a_speedValz .= '';
                        }
                        $si->setCellValue($icell[4].$baris, $a_speedValz);


                        $celpss = 4;
                        foreach ($arr_posisi[$subplan][$tgl] as $posisi) {
                        	$celpss++;
                            $nidValzz = '';
                            if(is_array($a_posisi[$posisi])) {
                                
                                foreach ($a_posisi[$posisi] as $nid => $nidVal) {
                                    $nidVal = explode( '##', $nidVal);
                                    $nidValzz .= $nidVal[0].'/'.$nidVal[1];
                                }
                                
                            }else{
                                $nidValzz .= '';
                            }
                            $si->setCellValue($icell[$celpss].$baris, $nidValzz);
                        }

                        $jmlposisikiln = count($a_posisi);
                        $TTLnkg = $arr_sumkg[$subplan][$tgl][$jam][$kiln];
                        $TTLncm = $arr_sumcm[$subplan][$tgl][$jam][$kiln];

                        

                        if($jmlposisikiln == 0){
                            $ratarata1 = '';
                        }else{
                            $rtkg = ($TTLnkg/$jmlposisikiln);
                            $rtcm = ($TTLncm/$jmlposisikiln);

                            $ratarata1 = number_format($rtkg,1).'/'.number_format($rtcm,1); 
                            if($ratarata1 == '0.0/0.0'){ 
                                $ratarata1 = '';
                            }else{
                                $ratarata1 = $ratarata1;    
                            }
                        }
                        $celpss++;
                        $si->setCellValue($icell[$celpss].$baris, $ratarata1);


                        $a_presiValz = '';
                        if(is_array($arr_presi[$subplan][$tgl][$jam][$kiln])) {
                            foreach ($arr_presi[$subplan][$tgl][$jam][$kiln] as $presiid => $a_presiVal) {
                                $a_presiValz .= $a_presiVal;
                            }
                        }else{
                            $a_presiValz .= '';
                        }

                        $celpss++;
                        $si->setCellValue($icell[$celpss].$baris, $a_presiValz);


                        $a_waValz = '';
                        if(is_array($arr_wa[$subplan][$tgl][$jam][$kiln])) {
                            foreach ($arr_wa[$subplan][$tgl][$jam][$kiln] as $waid => $a_waVal) {
                                $a_waValz .= $a_waVal;
                            }
                        }else{
                            $a_waValz .= '';
                        }

                        $a_acValz = '';
                        if(is_array($arr_ac[$subplan][$tgl][$jam][$kiln])) {
                            foreach ($arr_ac[$subplan][$tgl][$jam][$kiln] as $acid => $a_acVal) {
                                $a_acValz .= $a_acVal;
                            }
                        }else{
                            $a_acValz .= '';
                        }

                        $a_wmValz = '';
                        if(is_array($arr_wm[$subplan][$tgl][$jam][$kiln])) {
                            foreach ($arr_wm[$subplan][$tgl][$jam][$kiln] as $wmid => $a_wmVal) {
                                $a_wmValz .= $a_wmVal;
                            }
                        }else{
                            $a_wmValz .= '';
                        }

                        $a_descValz = '';
                        if(is_array($arr_desc[$subplan][$tgl][$jam][$kiln])) {
                            foreach ($arr_desc[$subplan][$tgl][$jam][$kiln] as $descid => $a_descVal) {
                                $a_descValz .= $a_descVal;
                            }
                        }else{
                            $a_descValz .= '';
                        }

                        $celpss++;
                        $si->setCellValue($icell[$celpss].$baris, $a_waValz);
						$celpss++;
                        $si->setCellValue($icell[$celpss].$baris, $a_acValz);
						$celpss++;
                        $si->setCellValue($icell[$celpss].$baris, $a_wmValz);
						$celpss++;
                        $si->setCellValue($icell[$celpss].$baris, $a_descValz);


                        $si->setSharedStyle($coltitleSy, $icell[1].$baris.':'.$icell[1].$baris);
                        $si->setSharedStyle($colborder, $icell[2].$baris.':'.$icell[$celpss].$baris);
                        $baris++;
                	}
                $nojam++;
               	}

               	$baris+=3;
            }
        }

		

		$si->setTitle('Sheet1');
		$oexcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		if($_GET["tipe"] == "xlsx") {
			header('Content-Disposition: attachment;filename=Pengamatan_speed_kiln_vs_bending.xlsx');
		} else if($_GET["tipe"] == "xls") {
			header('Content-Disposition: attachment;filename=Pengamatan_speed_kiln_vs_bending.xls');
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
	$kb_id = $_POST['qsm_id'];
	$sql = "SELECT * from qc_fg_kiln_bending_header WHERE kb_id = '{$kb_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$datetime = explode(' ',$rh[kb_date]);
	$rh[tgl] = cgx_dmy2ymd($datetime[0]);
	$rh[jam] = substr($datetime[1],0,5);
			
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[kb_id].'</td><td>Di-input Oleh : '.$rh[kb_user_create].'</td></tr><tr><td>Subplant : '.$rh[kb_sub_plant].'</td><td>Tanggal Input : '.$rh[kb_date_create].'</td></tr><tr><td>Tanggal : '.$rh[tgl].'</td><td>Di-edit Oleh : '.$rh[kb_user_modify].'</td></tr><tr><td>Jam : '.$rh[jam].'</td><td>Tanggal Edit : '.$rh[kb_date_modify].'</td></tr></table>';
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