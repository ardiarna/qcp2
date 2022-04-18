<?php

include_once("../libs/init.php");

$nama_plan = $_SESSION[$app_id]['user']['plan_nama'];
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
	global $app_plan_id, $nama_plan;
	$subplan = $_GET['subplan'];
	$shift = $_GET['shift'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.qbh_sub_plant = '".$subplan."'";
	}
	if($shift <> 'All') {
		$whdua .= " and a.qbh_shift = '".$shift."'";
	}
	$sql = "SELECT a.qbh_sub_plant as subplan, a.qbh_body_code as kodebody, b.qbd_material_type as tipe, b.qbd_material_code as item_kode, c.item_nama as item_nama, d.qbu_kode as box, b.qbd_formula as formula, b.qbd_dw as dw, b.qbd_mc as mc, b.qbd_ww as ww, b.qbd_value as nilai, b.qbd_remark as remark, a.qbh_shift as shift, a.qbh_bm_no as balmil, a.qbh_user_create, a.qbh_date_create, a.qbh_date, a.qbh_id
		from qc_bm_header a
		join qc_bm_detail b on(a.qbh_id=b.qbh_id)
		join item c on(b.qbd_material_code=c.item_kode)
		left join qc_box_unit d on(a.qbh_sub_plant=d.qbu_sub_plant and b.qbd_box_unit=d.qbu_kode) where a.qbh_rec_status='N' and a.qbh_date >= '{$tglfrom}' and a.qbh_date <= '{$tglto}' $whdua
		order by subplan, kodebody, tipe, item_kode, qbh_date, shift, balmil, qbh_id, box";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	if(is_array($qry)){
		foreach($qry as $r){
			$r[tipe] = $r[tipe] == "1" ? "MATERIAL" : "ADDITIVE";
			$arr_baris["$r[subplan]"]["$r[kodebody]"]["$r[tipe]"]["$r[item_kode]"]["$r[box]"] = $r[item_nama]."@@".$r[box]."@@".$r[formula]."@@".$r[dw]."@@".$r[mc]."@@".$r[ww];
			$arr_kolom["$r[subplan]"]["$r[kodebody]"]["$r[qbh_date]"]["$r[shift]"]["$r[balmil]"]["$r[qbh_id]"] = '';
			$arr_nilai["$r[subplan]"]["$r[kodebody]"]["$r[tipe]"]["$r[item_kode]"]["$r[box]"]["$r[qbh_date]"]["$r[shift]"]["$r[balmil]"]["$r[qbh_id]"] = $r[qbh_id]."@@".$r[nilai]."@@".$r[box]."@@".$r[formula]."@@".$r[dw]."@@".$r[mc]."@@".$r[ww]; 
			$i++;
		}
	}
	if(is_array($arr_baris)) {
		foreach ($arr_baris as $subplan => $a_kodebody) {
			foreach ($a_kodebody as $kodebody => $a_tipe) {
				foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
					foreach ($a_shift as $shift => $a_balmil) {
						foreach ($a_balmil as $balmil => $a_qbh_id) {
							$arr_kol_shift[$subplan][$kodebody][$qbh_date][$shift] += count($a_qbh_id);
							$arr_kol_date[$subplan][$kodebody][$qbh_date] += count($a_qbh_id);
							$arr_tot_kol[$subplan][$kodebody] += count($a_qbh_id);
						}
		        	}
				}
			}
		}
		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
		$html .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.1002.QC.01</div><div style="text-align:center;font-size:20px;font-weight:bold;">PENIMBANGAN MATERIAL BODY</div><table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
		$html .= '<div style="overflow-x:auto;"><table class="adaborder" id="tbl01">';
		foreach ($arr_baris as $subplan => $a_kodebody) {
			foreach ($a_kodebody as $kodebody => $a_tipe) {
				$html .= '<tr><th colspan="2">SUBPLANT : '.$subplan.'</th><th colspan="6">KODE BODY : '.$kodebody.'</th>';
				ksort($arr_kolom[$subplan][$kodebody]);
				reset($arr_kolom[$subplan][$kodebody]);
	        	foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
	        		$html .= '<th colspan="'.$arr_kol_date[$subplan][$kodebody][$qbh_date].'">'.$qbh_date.'</th>';
	        	}
	        	$html .= '<th rowspan="3">TOTAL MATERIAL & ADDITIVE</th></tr>';
	        	$html .= '<tr><th rowspan="2">NO.</th><th rowspan="2">ITEM KODE</th><th rowspan="2">NAMA MATERIAL</th><th rowspan="2">NO. BOX</th><th rowspan="2">FORMULA (%)</th><th rowspan="2">DW (kg)</th><th rowspan="2">MC (%)</th><th rowspan="2">WW (kg)</th>';
	        	ksort($arr_kolom[$subplan][$kodebody]);
				reset($arr_kolom[$subplan][$kodebody]);
	        	foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
	        		ksort($a_shift);
		        	reset($a_shift);	
	        		foreach ($a_shift as $shift => $a_balmil) {
	        			$html .= '<th colspan="'.$arr_kol_shift[$subplan][$kodebody][$qbh_date][$shift].'">SHIFT-'.$shift.'</th>';
	        		}
	        	}
	        	$html .= '</tr><tr>';
	        	ksort($arr_kolom[$subplan][$kodebody]);
				reset($arr_kolom[$subplan][$kodebody]);
	        	foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
	        		ksort($a_shift);
		        	reset($a_shift);
		        	foreach ($a_shift as $shift => $a_balmil) {
		        		ksort($a_balmil);
		        		reset($a_balmil);
		        		foreach ($a_balmil as $balmil => $a_qbh_id) {
		        			foreach ($a_qbh_id as $qbh_id => $value) {
		        				$html .= '<th>'.$balmil.'</th>';
		        			}		
		        		}
		        	}
		        }
	        	$html .= '</tr>';
	        	foreach ($a_tipe as $tipe => $a_item_kode) {
	        		$no = 1;
	        		$tot_nil = array();
	        		foreach ($a_item_kode as $item_kode => $a_box) {
	        			foreach ($a_box as $box => $nil_bar) {
	        				$tot_bar_nil = 0;
		        			$brs = explode("@@",$nil_bar);
		        			$html .='<tr><td style="text-align:center;">'.$no.'</td><td>'.$item_kode.'</td><td style="white-space: nowrap">'.$brs[0].'</td><td style="text-align:center;">'.$brs[1].'</td><td style="text-align:right;">'.number_format($brs[2],2).'</td><td style="text-align:right;">'.number_format($brs[3],2).'</td><td style="text-align:right;">'.number_format($brs[4],2).'</td><td style="text-align:right;">'.number_format($brs[5],2).'</td>';
		        			ksort($arr_kolom[$subplan][$kodebody]);
							reset($arr_kolom[$subplan][$kodebody]);
							foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
		        				ksort($a_shift);
			        			reset($a_shift);
			        			foreach ($a_shift as $shift => $a_balmil) {
					        		ksort($a_balmil);
					        		reset($a_balmil);
					        		foreach ($a_balmil as $balmil => $a_qbh_id) {
					        			foreach ($a_qbh_id as $qbh_id => $value) {
			        						$nilai = $arr_nilai[$subplan][$kodebody][$tipe][$item_kode][$box][$qbh_date][$shift][$balmil][$qbh_id];
						        			if($nilai) {
						        				$nil = explode("@@",$nilai);
						        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$qbh_id.'\')">'.number_format($nil[1],2).'</td>';
						        				$tot_bar_nil += round($nil[1],2);
						        				$tot_nil[$qbh_date][$shift][$balmil][$qbh_id] += round($nil[1],2);
						        			} else {
						        				$html .= '<td></td>';
						        			}
			        					}			
					        		}
					        	}
					        }
				        	$html .= '<td style="text-align:right;font-weight:bold;background-color:#88fcb2;">'.number_format($tot_bar_nil,2).'</td>';
		        			$html .='</tr>';
		        			$no++;
	        			}
	        		}
	        		if($tipe == 'MATERIAL') {
	        			$html .='<tr><td></td><td colspan="2" style="text-align:center;font-weight:bold;">TOTAL '.$tipe.'</td><td></td><td></td><td></td><td></td><td></td>';
		        		ksort($arr_kolom[$subplan][$kodebody]);
						reset($arr_kolom[$subplan][$kodebody]);
						foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
	        				ksort($a_shift);
		        			reset($a_shift);
			    			foreach ($a_shift as $shift => $a_balmil) {
				        		ksort($a_balmil);
				        		reset($a_balmil);
				        		foreach ($a_balmil as $balmil => $a_qbh_id) {
				        			foreach ($a_qbh_id as $qbh_id => $value) {
				        				$nilai = $tot_nil[$qbh_date][$shift][$balmil][$qbh_id];
					        			if($nilai) {
					        				$html .= '<td style="text-align:right;font-weight:bold;background-color:#edf765;">'.number_format($nilai,2).'</td>';
					        			} else {
					        				$html .= '<td></td>';
					        			}	
				        			}		
				        		}
				        	}
				        }
			        	$html .='<td style="text-align:right;font-weight:bold;background-color:#88fcb2;"></td></tr>';
	        		}
	        	}
	        	$html .='<tr><td colspan="'.($arr_tot_kol[$subplan][$kodebody]+9).'">&nbsp;</td></tr>';
			}
		}
		$html .='</table></div>';
	} else {
		$html = 'TIDAKADA';
	}
	$responce->detailtabel = $html; 
	echo json_encode($responce);
}

function excel(){
	require_once("../libs/PHPExcel.php");
	global $app_plan_id, $nama_plan;
	$subplan = $_GET['subplan'];
	$shift = $_GET['shift'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.qbh_sub_plant = '".$subplan."'";
	}
	if($shift <> 'All') {
		$whdua .= " and a.qbh_shift = '".$shift."'";
	}
	$sql = "SELECT a.qbh_sub_plant as subplan, a.qbh_body_code as kodebody, b.qbd_material_type as tipe, b.qbd_material_code as item_kode, c.item_nama as item_nama, d.qbu_kode as box, b.qbd_formula as formula, b.qbd_dw as dw, b.qbd_mc as mc, b.qbd_ww as ww, b.qbd_value as nilai, b.qbd_remark as remark, a.qbh_shift as shift, a.qbh_bm_no as balmil, a.qbh_user_create, a.qbh_date_create, a.qbh_date, a.qbh_id
		from qc_bm_header a
		join qc_bm_detail b on(a.qbh_id=b.qbh_id)
		join item c on(b.qbd_material_code=c.item_kode)
		left join qc_box_unit d on(a.qbh_sub_plant=d.qbu_sub_plant and b.qbd_box_unit=d.qbu_kode) where a.qbh_rec_status='N' and a.qbh_date >= '{$tglfrom}' and a.qbh_date <= '{$tglto}' $whdua
		order by subplan, kodebody, tipe, item_kode, qbh_date, shift, balmil, qbh_id, box";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	if(is_array($qry)){
		foreach($qry as $r){
			$r[tipe] = $r[tipe] == "1" ? "MATERIAL" : "ADDITIVE";
			$arr_baris["$r[subplan]"]["$r[kodebody]"]["$r[tipe]"]["$r[item_kode]"]["$r[box]"] = $r[item_nama]."@@".$r[box]."@@".$r[formula]."@@".$r[dw]."@@".$r[mc]."@@".$r[ww];
			$arr_kolom["$r[subplan]"]["$r[kodebody]"]["$r[qbh_date]"]["$r[shift]"]["$r[balmil]"]["$r[qbh_id]"] = '';
			$arr_nilai["$r[subplan]"]["$r[kodebody]"]["$r[tipe]"]["$r[item_kode]"]["$r[box]"]["$r[qbh_date]"]["$r[shift]"]["$r[balmil]"]["$r[qbh_id]"] = $r[qbh_id]."@@".$r[nilai]."@@".$r[box]."@@".$r[formula]."@@".$r[dw]."@@".$r[mc]."@@".$r[ww]; 
			$i++;
		}
	}
	if(is_array($arr_baris)) {
		foreach ($arr_baris as $subplan => $a_kodebody) {
			foreach ($a_kodebody as $kodebody => $a_tipe) {
				foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
					foreach ($a_shift as $shift => $a_balmil) {
						foreach ($a_balmil as $balmil => $a_qbh_id) {
							$arr_kol_shift[$subplan][$kodebody][$qbh_date][$shift] += count($a_qbh_id);
							$arr_kol_date[$subplan][$kodebody][$qbh_date] += count($a_qbh_id);
							$arr_tot_kol[$subplan][$kodebody] += count($a_qbh_id);
						}
		        	}
				}
			}
		}
		$tot_kolom_max = 1;
		foreach ($arr_tot_kol as $subplan => $a_kodebody) {
			foreach ($a_kodebody as $kodebody => $value) {
				if($value > $tot_kolom_max) {
					$tot_kolom_max = $value;
				}
			}
		}
		$tot_kolom_max += 8;
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
			'font'		=> array(
				'bold' 	=> true
			),
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
		$oexcel->getProperties()->setCreator("Ardi Fianto")
								->setLastModifiedBy("Ardi Fianto");
		$si = $oexcel->setActiveSheetIndex(0);
		
		$baris = 1;
		$si->mergeCells($icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
		$si->setCellValue($icell[0].$baris,'No : F.1002.QC.01');
		$si->setSharedStyle($colkanan, $icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
		$baris++;
		$si->mergeCells($icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
		$si->setCellValue($icell[0].$baris,'PENIMBANGAN MATERIAL BODY');
		$baris++;
		$si->mergeCells($icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
		$si->setCellValue($icell[0].$baris,$tgljudul);
		$si->setSharedStyle($coltengah, $icell[0].($baris-1).':'.$icell[$tot_kolom_max].$baris);
		$baris +=3;
		$zcell = 0;
		foreach ($arr_baris as $subplan => $a_kodebody) {
			foreach ($a_kodebody as $kodebody => $a_tipe) {
				$si->mergeCells($icell[0].$baris.':'.$icell[1].$baris);
				$si->setCellValue($icell[0].$baris,'SUBPLANT : '.$subplan);
				$si->mergeCells($icell[2].$baris.':'.$icell[7].$baris);
				$si->setCellValue($icell[2].$baris,'KODE BODY : '.$kodebody);
				$nexcel = 8;
				ksort($arr_kolom[$subplan][$kodebody]);
				reset($arr_kolom[$subplan][$kodebody]);
	        	foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
	        		$akolspan = $nexcel+$arr_kol_date[$subplan][$kodebody][$qbh_date]-1;
					$si->mergeCells($icell[$nexcel].$baris.':'.$icell[$akolspan].$baris);
					$si->setCellValue($icell[$nexcel].$baris,$qbh_date);
					$nexcel = $akolspan+1;
	        	}
				$si->mergeCells($icell[$nexcel].$baris.':'.$icell[$nexcel].($baris+2));
				$si->setCellValue($icell[$nexcel].$baris,'TOTAL MATERIAL & ADDITIVE');
				$zcell = $nexcel > $zcell ? $nexcel : $zcell;
				$baris++;
				$si->mergeCells($icell[0].$baris.':'.$icell[0].($baris+1));
				$si->setCellValue($icell[0].$baris,'NO.');
				$si->mergeCells($icell[1].$baris.':'.$icell[1].($baris+1));
				$si->setCellValue($icell[1].$baris,'ITEM KODE');
				$si->mergeCells($icell[2].$baris.':'.$icell[2].($baris+1));
				$si->setCellValue($icell[2].$baris,'NAMA MATERIAL');
				$si->mergeCells($icell[3].$baris.':'.$icell[3].($baris+1));
				$si->setCellValue($icell[3].$baris,'NO. BOX');
				$si->mergeCells($icell[4].$baris.':'.$icell[4].($baris+1));
				$si->setCellValue($icell[4].$baris,'FORMULA (%)');
				$si->mergeCells($icell[5].$baris.':'.$icell[5].($baris+1));
				$si->setCellValue($icell[5].$baris,'DW (kg)');
				$si->mergeCells($icell[6].$baris.':'.$icell[6].($baris+1));
				$si->setCellValue($icell[6].$baris,'MC (%)');
				$si->mergeCells($icell[7].$baris.':'.$icell[7].($baris+1));
				$si->setCellValue($icell[7].$baris,'WW (kg)');
				$nexcel = 8;
				ksort($arr_kolom[$subplan][$kodebody]);
				reset($arr_kolom[$subplan][$kodebody]);
	        	foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
	        		ksort($a_shift);
		        	reset($a_shift);	
	        		foreach ($a_shift as $shift => $a_balmil) {
	        			$akolspan = $nexcel+$arr_kol_shift[$subplan][$kodebody][$qbh_date][$shift]-1;
		        		$si->mergeCells($icell[$nexcel].$baris.':'.$icell[$akolspan].$baris);
						$si->setCellValue($icell[$nexcel].$baris,'SHIFT-'.$shift);
		        		$nexcel = $akolspan+1;
	        		}
	        	}
	        	$baris++;
				$nexcel = 8;
				ksort($arr_kolom[$subplan][$kodebody]);
				reset($arr_kolom[$subplan][$kodebody]);
	        	foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
	        		ksort($a_shift);
		        	reset($a_shift);
		        	foreach ($a_shift as $shift => $a_balmil) {
		        		ksort($a_balmil);
		        		reset($a_balmil);
		        		foreach ($a_balmil as $balmil => $a_qbh_id) {
		        			foreach ($a_qbh_id as $qbh_id => $value) {
		        				$si->setCellValue($icell[$nexcel].$baris,'BM '.$balmil);
		        				$nexcel++;
		        			}		
		        		}
		        	}
		        }
	        	$si->setSharedStyle($coltitleSy, $icell[0].($baris-2).':'.$icell[($nexcel-1)].$baris);
	        	$si->setSharedStyle($colwrap, $icell[$nexcel].($baris-2).':'.$icell[$nexcel].$baris);
	        	$baris++;
	        	foreach ($a_tipe as $tipe => $a_item_kode) {
	        		$no = 1;
	        		$tot_nil = array();
	        		foreach ($a_item_kode as $item_kode => $a_box) {
	        			foreach ($a_box as $box => $nil_bar) {
	        				$tot_bar_nil = 0;
		        			$brs = explode("@@",$nil_bar);
		        			$si->setCellValue($icell[0].$baris,$no);
	        				$si->setCellValue($icell[1].$baris,$item_kode);
	        				$si->setCellValue($icell[2].$baris,$brs[0]);
	        				$si->setCellValue($icell[3].$baris,$brs[1]);
	        				$si->setCellValue($icell[4].$baris,$brs[2]);
	        				$si->setCellValue($icell[5].$baris,$brs[3]);
	        				$si->setCellValue($icell[6].$baris,$brs[4]);
	        				$si->setCellValue($icell[7].$baris,$brs[5]);
	        				$nexcel = 8;
	        				ksort($arr_kolom[$subplan][$kodebody]);
							reset($arr_kolom[$subplan][$kodebody]);
							foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
		        				ksort($a_shift);
			        			reset($a_shift);
			        			foreach ($a_shift as $shift => $a_balmil) {
					        		ksort($a_balmil);
					        		reset($a_balmil);
					        		foreach ($a_balmil as $balmil => $a_qbh_id) {
					        			foreach ($a_qbh_id as $qbh_id => $value) {
			        						$nilai = $arr_nilai[$subplan][$kodebody][$tipe][$item_kode][$box][$qbh_date][$shift][$balmil][$qbh_id];
						        			if($nilai) {
						        				$nil = explode("@@",$nilai);
						        				$si->setCellValue($icell[$nexcel].$baris,$nil[1]);
						        				$tot_bar_nil += round($nil[1],2);
						        				$tot_nil[$qbh_date][$shift][$balmil][$qbh_id] += round($nil[1],2);
						        				$nexcel++;
						        			} else {
						        				$si->setCellValue($icell[$nexcel].$baris,'');
					        					$nexcel++;
						        			}
			        					}			
					        		}
					        	}
					        }
				        	$si->setCellValue($icell[$nexcel].$baris,$tot_bar_nil);
				        	$z = $nexcel-1;
				        	$si->setSharedStyle($colborder, $icell[0].$baris.':'.$icell[$z].$baris);
		        			$si->setSharedStyle($colkolorgreen, $icell[$nexcel].$baris);
		        			$no++;
		        			$baris++;
	        			}	
	        		}
	        		if($tipe == 'MATERIAL') {
	        			$si->mergeCells($icell[0].$baris.':'.$icell[2].$baris);
						$si->setCellValue($icell[0].$baris,'TOTAL '.$tipe);
						$nexcel = 8;
						ksort($arr_kolom[$subplan][$kodebody]);
						reset($arr_kolom[$subplan][$kodebody]);
						foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
	        				ksort($a_shift);
		        			reset($a_shift);
			    			foreach ($a_shift as $shift => $a_balmil) {
				        		ksort($a_balmil);
				        		reset($a_balmil);
				        		foreach ($a_balmil as $balmil => $a_qbh_id) {
				        			foreach ($a_qbh_id as $qbh_id => $value) {
				        				$nilai = $tot_nil[$qbh_date][$shift][$balmil][$qbh_id];
					        			if($nilai) {
					        				$si->setCellValue($icell[$nexcel].$baris,$nilai);
					        				$nexcel++;
					        			} else {
					        				$si->setCellValue($icell[$nexcel].$baris,'');
						        			$nexcel++;
					        			}	
				        			}		
				        		}
				        	}
				        }
			        	$si->setSharedStyle($coltitleSy, $icell[0].$baris.':'.$icell[7].$baris);
			        	$z = $nexcel-1;
			        	$si->setSharedStyle($colkoloryellow, $icell[8].$baris.':'.$icell[$z].$baris);
			        	$si->setSharedStyle($colkolorgreen, $icell[$nexcel].$baris);	
	        		}
	        		$baris++;
	        	}
	        	$si->mergeCells($icell[0].$baris.':'.$icell[$nexcel].$baris);
				$si->setCellValue($icell[0].$baris,'');
	        	$baris ++;
			}
		}
		$baris -=2;
		$si->getStyle('D4:'.$icell[$zcell].$baris)->getNumberFormat()->setFormatCode('#,##0.00');
		$si->setTitle('Sheet1');
		$oexcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		if($_GET["tipe"] == "xlsx") {
			header('Content-Disposition: attachment;filename=Penimbangan_Material_Body.xlsx');
		} else if($_GET["tipe"] == "xls") {
			header('Content-Disposition: attachment;filename=Penimbangan_Material_Body.xls');
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
	$qbh_id = $_POST['qbh_id'];
	$sql = "SELECT a.*
		from qc_bm_header a
		where a.qbh_id = '{$qbh_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[qbh_id].'</td></tr><tr><td>Subplant : '.$rh[qbh_sub_plant].'</td></tr><tr><td>Tanggal : '.cgx_dmy2ymd($rh[qbh_date]).'</td></tr><tr><td>Shift : '.$rh[qbh_shift].'</td><td>Di-input Oleh : '.$rh[qbh_user_create].'</td></tr><tr><td>Nomor Ballmill : '.$rh[qbh_bm_no].'</td><td>Tanggal Input : '.$rh[qbh_date_create].'</td></tr><tr><td>Kapasitas : '.number_format($rh[qbh_volume]).'</td><td>Di-edit Oleh : '.$rh[qbh_user_modify].'</td></tr><tr><td>Kode Body : '.$rh[qbh_body_code].'</td><td>Tanggal Edit : '.$rh[qbh_date_modify].'</td></tr></table>';
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