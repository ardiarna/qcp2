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
	global $app_plan_id, $nama_plan;
	$subplan = $_GET['subplan'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.qgh_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.qgh_sub_plant as subplan, c.qggm_desc as grup, b.qgd_prep_seq as line, d.qgdm_control_desc as item_nama, f.qgu_code as unit, a.qgh_glaze_code as kodeglaze, a.qgh_bmg_no as balmil, b.qgd_prep_value as nilai, a.qgh_id 
		from qc_gp_header a
		join qc_gp_detail b on(a.qgh_id=b.qgh_id)
		join qc_gp_group_master c on(b.qgd_prep_group=c.qggm_group) 
		join qc_gp_detail_master d on(b.qgd_prep_group=d.qgdm_group and b.qgd_prep_seq=d.qgdm_seq)
		left join qc_gen_um f on(d.qgdm_um_id=f.qgu_id) where a.qgh_rec_stat='N' and b.qgd_prep_group='01' and a.qgh_date >= '{$tglfrom}' and a.qgh_date <= '{$tglto}' $whdua
		order by subplan, grup, line, kodeglaze, balmil, qgh_id";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	if(is_array($qry)){
		foreach($qry as $r){
			$arr_baris["$r[subplan]"]["$r[line]"] = $r[item_nama]."@@".$r[unit];
			$arr_kolom["$r[subplan]"]["$r[kodeglaze]"]["$r[balmil]"]["$r[qgh_id]"] = '';
			$arr_nilai["$r[subplan]"]["$r[line]"]["$r[kodeglaze]"]["$r[balmil]"]["$r[qgh_id]"] = $r[qgh_id]."@@".$r[nilai]; 
			$i++;
		}
	}
	if(is_array($arr_baris)) {
		foreach ($arr_baris as $subplan => $a_line) {
			foreach ($arr_kolom[$subplan] as $kodeglaze => $a_balmil) {
				foreach ($a_balmil as $balmil => $a_qgh_id) {
					$arr_kol_kodeglaze[$subplan][$kodeglaze] += count($a_qgh_id);
					$arr_tot_kol_kodeglaze[$subplan] += count($a_qgh_id);
				}
        	}
		}
		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
		$html .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.1002.QC.03</div><div style="text-align:center;font-size:20px;font-weight:bold;">GLAZE PREPARATION</div><table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
		$html .= '<div style="overflow-x:auto;"><table class="adaborder" id="tbl01">';
		foreach ($arr_baris as $subplan => $a_line) {
			$html .= '<tr><th colspan="3">SUBPLANT : '.$subplan.'</th><th colspan="'.$arr_tot_kol_kodeglaze[$subplan].'">NO. BALLMILL / TYPE GLAZE</th></tr>';
        	$html .= '<tr><th rowspan="2">NO.</th><th rowspan="2">DESKRIPSI</th><th rowspan="2">UNIT</th>';
        	ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $kodeglaze => $a_balmil) {
        		$html .= '<th colspan="'.$arr_kol_kodeglaze[$subplan][$kodeglaze].'">'.$kodeglaze.'</th>';
        	}
        	$html .= '</tr><tr>';
        	foreach ($arr_kolom[$subplan] as $kodeglaze => $a_balmil) {
        		ksort($a_balmil);
        		reset($a_balmil);
        		foreach ($a_balmil as $balmil => $a_qgh_id) {
        			foreach ($a_qgh_id as $qgh_id => $value) {
        				$html .= '<th>'.$balmil.'</th>';
        			}		
        		}
        	}
        	$html .= '</tr>';
        	$no = 1;
    		foreach ($a_line as $line => $nil_bar) {
    			$brs = explode("@@",$nil_bar);
    			$html .='<tr><td style="text-align:center;">'.$no.'</td><td style="white-space: nowrap">'.$brs[0].'</td><td style="text-align:center;">'.$brs[1].'</td>';
    			ksort($arr_kolom[$subplan]);
				reset($arr_kolom[$subplan]);
    			foreach ($arr_kolom[$subplan] as $kodeglaze => $a_balmil) {
	        		ksort($a_balmil);
	        		reset($a_balmil);
	        		foreach ($a_balmil as $balmil => $a_qgh_id) {
	        			foreach ($a_qgh_id as $qgh_id => $value) {
    						$nilai = $arr_nilai[$subplan][$line][$kodeglaze][$balmil][$qgh_id];
		        			if($nilai) {
		        				$nil = explode("@@",$nilai);
		        				if($line == '1' || $line == '2' || $line == '8' || $line == '9') {
		        					$html .= '<td style="text-align:right;" onclick="lihatData(\''.$qgh_id.'\')">'.number_format($nil[1]).'</td>';
		        				} else if($line == '5' || $line == '6' || $line == '7') {
		        					$html .= '<td style="text-align:center;" onclick="lihatData(\''.$qgh_id.'\')">'.$nil[1].'</td>';
		        				} else {
		        					$html .= '<td style="text-align:right;" onclick="lihatData(\''.$qgh_id.'\')">'.number_format($nil[1],2).'</td>';
		        				}
		        			} else {
		        				$html .= '<td></td>';
		        			}
    					}			
	        		}
	        	}
	        	$html .='</tr>';
    			$no++;
    		}
        	$html .='<tr><td colspan="'.($arr_tot_kol_kodeglaze[$subplan]+3).'">&nbsp;</td></tr>';
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
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.qgh_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.qgh_sub_plant as subplan, c.qggm_desc as grup, b.qgd_prep_seq as line, d.qgdm_control_desc as item_nama, f.qgu_code as unit, a.qgh_glaze_code as kodeglaze, a.qgh_bmg_no as balmil, b.qgd_prep_value as nilai, a.qgh_id 
		from qc_gp_header a
		join qc_gp_detail b on(a.qgh_id=b.qgh_id)
		join qc_gp_group_master c on(b.qgd_prep_group=c.qggm_group) 
		join qc_gp_detail_master d on(b.qgd_prep_group=d.qgdm_group and b.qgd_prep_seq=d.qgdm_seq)
		left join qc_gen_um f on(d.qgdm_um_id=f.qgu_id) where a.qgh_rec_stat='N' and b.qgd_prep_group='01' and a.qgh_date >= '{$tglfrom}' and a.qgh_date <= '{$tglto}' $whdua
		order by subplan, grup, line, kodeglaze, balmil, qgh_id";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	if(is_array($qry)){
		foreach($qry as $r){
			$arr_baris["$r[subplan]"]["$r[line]"] = $r[item_nama]."@@".$r[unit];
			$arr_kolom["$r[subplan]"]["$r[kodeglaze]"]["$r[balmil]"]["$r[qgh_id]"] = '';
			$arr_nilai["$r[subplan]"]["$r[line]"]["$r[kodeglaze]"]["$r[balmil]"]["$r[qgh_id]"] = $r[qgh_id]."@@".$r[nilai]; 
			$i++;
		}
	}
	if(is_array($arr_baris)) {
		foreach ($arr_baris as $subplan => $a_line) {
			foreach ($arr_kolom[$subplan] as $kodeglaze => $a_balmil) {
				foreach ($a_balmil as $balmil => $a_qgh_id) {
					$arr_kol_kodeglaze[$subplan][$kodeglaze] += count($a_qgh_id);
					$arr_tot_kol_kodeglaze[$subplan] += count($a_qgh_id);
				}
        	}
		}
		$tot_kolom_max = 1;
		foreach ($arr_tot_kol_kodeglaze as $subplan => $value) {
			if($value > $tot_kolom_max) {
				$tot_kolom_max = $value;
			}
		}
		$tot_kolom_max += 2;
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
		$si->setCellValue($icell[0].$baris,'No : F.1002.QC.03');
		$si->setSharedStyle($colkanan, $icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
		$baris++;
		$si->mergeCells($icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
		$si->setCellValue($icell[0].$baris,'GLAZE PREPARATION');
		$baris++;
		$si->mergeCells($icell[0].$baris.':'.$icell[$tot_kolom_max].$baris);
		$si->setCellValue($icell[0].$baris,$tgljudul);
		$si->setSharedStyle($coltengah, $icell[0].($baris-1).':'.$icell[$tot_kolom_max].$baris);
		$baris +=3;
		$zcell = 0;
		foreach ($arr_baris as $subplan => $a_line) {
			$si->mergeCells($icell[0].$baris.':'.$icell[2].$baris);
			$si->setCellValue($icell[0].$baris,'SUBPLANT : '.$subplan);
			$akolspan = $arr_tot_kol_kodeglaze[$subplan]+2;
			$si->mergeCells($icell[3].$baris.':'.$icell[$akolspan].$baris);
			$si->setCellValue($icell[3].$baris,'NO. BALLMILL / TYPE GLAZE');
			$zcell = $akolspan > $zcell ? $akolspan : $zcell;
			$baris++;
			$si->mergeCells($icell[0].$baris.':'.$icell[0].($baris+1));
			$si->setCellValue($icell[0].$baris,'NO.');
			$si->mergeCells($icell[1].$baris.':'.$icell[1].($baris+1));
			$si->setCellValue($icell[1].$baris,'DESKRIPSI');
			$si->mergeCells($icell[2].$baris.':'.$icell[2].($baris+1));
			$si->setCellValue($icell[2].$baris,'UNIT');
			$nexcel = 3;
			ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $kodeglaze => $a_balmil) {
        		$akolspan = $nexcel+$arr_kol_kodeglaze[$subplan][$kodeglaze]-1;
        		$si->mergeCells($icell[$nexcel].$baris.':'.$icell[$akolspan].$baris);
				$si->setCellValue($icell[$nexcel].$baris,$kodeglaze);
        		$nexcel = $akolspan+1;
        	}
        	$baris++;
        	$nexcel = 3;
        	foreach ($arr_kolom[$subplan] as $kodeglaze => $a_balmil) {
        		ksort($a_balmil);
        		reset($a_balmil);
        		foreach ($a_balmil as $balmil => $a_qgh_id) {
        			foreach ($a_qgh_id as $qgh_id => $value) {
        				$si->setCellValue($icell[$nexcel].$baris,'BM '.$balmil);
		        		$nexcel++;
        			}		
        		}
        	}
        	$si->setSharedStyle($coltitleSy, $icell[0].($baris-2).':'.$icell[($nexcel-1)].$baris);
	        $baris++;
        	$no = 1;
        	$a_brs = $baris;
    		foreach ($a_line as $line => $nil_bar) {
    			$brs = explode("@@",$nil_bar);
    			$si->setCellValue($icell[0].$baris,$no);
				$si->setCellValue($icell[1].$baris,$brs[0]);
				$si->setCellValue($icell[2].$baris,$brs[1]);
				$nexcel = 3;
    			ksort($arr_kolom[$subplan]);
				reset($arr_kolom[$subplan]);
    			foreach ($arr_kolom[$subplan] as $kodeglaze => $a_balmil) {
	        		ksort($a_balmil);
	        		reset($a_balmil);
	        		foreach ($a_balmil as $balmil => $a_qgh_id) {
	        			foreach ($a_qgh_id as $qgh_id => $value) {
    						$nilai = $arr_nilai[$subplan][$line][$kodeglaze][$balmil][$qgh_id];
		        			if($nilai) {
		        				$nil = explode("@@",$nilai);
		        				$si->setCellValue($icell[$nexcel].$baris,$nil[1]);
		        				$nexcel++;
		        			} else {
		        				$si->setCellValue($icell[$nexcel].$baris,'');
				        		$nexcel++;
		        			}
    					}			
	        		}
	        	}
	        	$baris++;
    			$no++;
    		}
  			$si->setSharedStyle($colborder, $icell[0].$a_brs.':'.$icell[($nexcel-1)].($baris-1));
        	$baris++;
		}
		$si->getStyle('D4:'.$icell[$zcell].$baris)->getNumberFormat()->setFormatCode('#,##0.00');
		$si->setTitle('Sheet1');
		$oexcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		if($_GET["tipe"] == "xlsx") {
			header('Content-Disposition: attachment;filename=Glaze_Preparation.xlsx');
		} else if($_GET["tipe"] == "xls") {
			header('Content-Disposition: attachment;filename=Glaze_Preparation.xls');
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
		from qc_gp_header a
		where a.qgh_id = '{$qgh_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$rh[qgh_category] = $rh[qgh_category] == "1" ? "Engobe" : "Glazur"; 
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[qgh_id].'</td></tr><tr><td>Subplant : '.$rh[qgh_sub_plant].'</td></tr><tr><td>Tanggal : '.cgx_dmy2ymd(substr($rh[qgh_date],0,10)).'</td></tr><tr><td>Shift : '.$rh[qgh_shift].'</td><td>Di-input Oleh : '.$rh[qgd_user_create].'</td></tr><tr><td>Nomor Ballmill : '.$rh[qgh_bmg_no].'</td><td>Tanggal Input : '.$rh[qgd_date_create].'</td></tr><tr><td>Kategori : '.$rh[qgh_category].'</td><td>Di-edit Oleh : '.$rh[qgd_user_mofify].'</td></tr><tr><td>Kode Formula : '.$rh[qgh_glaze_code].'</td><td>Tanggal Edit : '.$rh[qgd_date_modify].'</td></tr></table>';
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