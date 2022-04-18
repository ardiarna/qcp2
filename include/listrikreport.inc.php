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
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.qlh_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.qlh_sub_plant as subplan, a.qlh_date, qlh_cap_bank_1, qlh_cap_bank_2, qlh_cap_bank_3, b.qld_group as grup, b.qld_r, b.qld_s, b.qld_t, b.qld_v, b.qld_watt_hour, a.qlh_id
		from qc_listrik_header a 
		join qc_listrik_detail b on(a.qlh_id=b.qlh_id) 
		where a.qlh_rec_status = 'N' and a.qlh_date >= '{$tglfrom}' and a.qlh_date <= '{$tglto}' $whdua 
		order by qlh_sub_plant, qlh_date, qld_group, qlh_id";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	if(is_array($qry)){
		foreach($qry as $r){
			$datetime = explode(' ',$r[qlh_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			$r[jam] = substr($datetime[1],0,5);
			$arr_baris["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[qlh_id]"] = $r[qlh_id]."@@".$r[qlh_cap_bank_1]."@@".$r[qlh_cap_bank_2]."@@".$r[qlh_cap_bank_3];
			$arr_kolom["$r[subplan]"]["$r[grup]"] = '';
			$arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[qlh_id]"]["$r[grup]"] = $r[qld_r]."@@".$r[qld_s]."@@".$r[qld_t]."@@".$r[qld_v]."@@".$r[qld_watt_hour];
			$i++;
		}
	}
	if(is_array($arr_baris)) {
		foreach ($arr_baris as $subplan => $a_tgl) {
			foreach ($arr_kolom[$subplan] as $grup => $value) {
				$arr_tot_kol[$subplan] += 5;
        	}
		}
		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
		$html .= '<div style="text-align:center;font-size:20px;font-weight:bold;">PEMAKAIAN LISTRIK</div><table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
		$html .= '<div style="overflow-x:auto;"><table class="adaborder">';
		foreach ($arr_baris as $subplan => $a_tgl) {
			$html .= '<tr><th colspan="'.($arr_tot_kol[$subplan]+6).'" style="text-align:left;padding-top:20px;">SUBPLANT : '.$subplan.'</th></tr>';
        	$html .= '<tr><th rowspan="2">No</th><th rowspan="2">Tanggal</th><th rowspan="2">Jam</th>';
        	ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $grup => $value) {
        		$html .= '<th colspan="5">'.$grup.'</th>';
        	}
        	$html .= '<th rowspan="2">Cap Bank-1 Cos Q</th><th rowspan="2">Cap Bank-2 Cos Q</th><th rowspan="2">Cap Bank-3 Cos Q</th></tr><tr>';
        	ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $grup => $value) {
        		$html .= '<th>R</th><th>S</th><th>T</th><th>V</th><th>Watt Hour Met</th>';
        	}
        	$html .= '</tr>';
        	foreach ($a_tgl as $tgl => $a_jam) {
        		$html .='<tr><td></td><td style="text-align:center;font-weight:bold;"><u>'.$tgl.'</u></td><td></td>';
        		foreach ($arr_kolom[$subplan] as $grup => $value) {
	        		$html .= '<td></td><td></td><td></td><td></td><td></td>';
	        	}
	        	$html .= '<td></td><td></td><td></td></tr>';
	        	$no = 1;
        		foreach ($a_jam as $jam => $a_qlh_id) {
        			foreach ($a_qlh_id as $qlh_id => $nil_bar) {
        				$brs = explode("@@",$nil_bar);
        				$html .='<tr><td style="text-align:center;">'.$no.'</td><td style="text-align:center;">'.$tgl.'</td><td style="text-align:center;">'.$jam.'</td>';
	        			ksort($arr_kolom[$subplan]);
						reset($arr_kolom[$subplan]);
	        			foreach ($arr_kolom[$subplan] as $grup => $value) {
			        		$nilai = $arr_nilai[$subplan][$tgl][$jam][$qlh_id][$grup];
		        			if($nilai) {
		        				$nil = explode("@@",$nilai);
		        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$qlh_id.'\')">'.$nil[0].'</td>';
		        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$qlh_id.'\')">'.$nil[1].'</td>';
		        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$qlh_id.'\')">'.$nil[2].'</td>';
		        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$qlh_id.'\')">'.$nil[3].'</td>';
		        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$qlh_id.'\')">'.$nil[4].'</td>';
		        			} else {
		        				$html .= '<td></td><td></td><td></td><td></td><td></td>';
		        			}
			        	}
			        	$html .= '<td style="text-align:right;" onclick="lihatData(\''.$qlh_id.'\')">'.$brs[1].'</td>';
        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$qlh_id.'\')">'.$brs[2].'</td>';
        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$qlh_id.'\')">'.$brs[3].'</td>';
			        	$html .='</tr>';
        				$no++;
        			}
        		}
        		$html .='<tr><td></td><td>&nbsp;</td><td></td>';
        		foreach ($arr_kolom[$subplan] as $grup => $value) {
	        		$html .= '<td></td><td></td><td></td><td></td><td></td>';
	        	}
	        	$html .= '<td></td><td></td><td></td></tr>';	
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
		$whdua .= " and a.qlh_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.qlh_sub_plant as subplan, a.qlh_date, qlh_cap_bank_1, qlh_cap_bank_2, qlh_cap_bank_3, b.qld_group as grup, b.qld_r, b.qld_s, b.qld_t, b.qld_v, b.qld_watt_hour, a.qlh_id
		from qc_listrik_header a 
		join qc_listrik_detail b on(a.qlh_id=b.qlh_id) 
		where a.qlh_rec_status = 'N' and a.qlh_date >= '{$tglfrom}' and a.qlh_date <= '{$tglto}' $whdua 
		order by qlh_sub_plant, qlh_date, qld_group, qlh_id";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	if(is_array($qry)){
		foreach($qry as $r){
			$datetime = explode(' ',$r[qlh_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			$r[jam] = substr($datetime[1],0,5);
			$arr_baris["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[qlh_id]"] = $r[qlh_id]."@@".$r[qlh_cap_bank_1]."@@".$r[qlh_cap_bank_2]."@@".$r[qlh_cap_bank_3];
			$arr_kolom["$r[subplan]"]["$r[grup]"] = '';
			$arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[qlh_id]"]["$r[grup]"] = $r[qld_r]."@@".$r[qld_s]."@@".$r[qld_t]."@@".$r[qld_v]."@@".$r[qld_watt_hour];
			$i++;
		}
	}
	if(is_array($arr_baris)) {
		foreach ($arr_baris as $subplan => $a_tgl) {
			foreach ($arr_kolom[$subplan] as $grup => $value) {
				$arr_tot_kol[$subplan] += 5;
        	}
		}
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
		$oexcel->getProperties()->setCreator("Ardi Fianto")
								->setLastModifiedBy("Ardi Fianto");
		$si = $oexcel->setActiveSheetIndex(0);
		
		$baris = 1;
		$si->mergeCells($icell[0].$baris.':'.$icell[7].$baris);
		$si->setCellValue($icell[0].$baris,'PEMAKAIAN LISTRIK');
		$baris++;
		$si->mergeCells($icell[0].$baris.':'.$icell[7].$baris);
		$si->setCellValue($icell[0].$baris,'TGL : '.$tgljudul);
		$si->setSharedStyle($coltitleSy, $icell[0].($baris-1).':'.$icell[7].$baris);
		$baris +=3;
		$zcell = 0;
		foreach ($arr_baris as $subplan => $a_tgl) {
			$akolspan = $arr_tot_kol[$subplan]+5;
			$si->mergeCells($icell[0].$baris.':'.$icell[$akolspan].$baris);
			$si->setCellValue($icell[0].$baris,'SUBPLANT : '.$subplan);
			$si->setSharedStyle($colboldaja, $icell[0].$baris);	
			$baris++;
			$si->mergeCells($icell[0].$baris.':'.$icell[0].($baris+1));
			$si->setCellValue($icell[0].$baris,'No');
			$si->mergeCells($icell[1].$baris.':'.$icell[1].($baris+1));
			$si->setCellValue($icell[1].$baris,'Tanggal');
			$si->mergeCells($icell[2].$baris.':'.$icell[2].($baris+1));
			$si->setCellValue($icell[2].$baris,'Jam');
			$nexcel = 3;
        	ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $grup => $value) {
        		$akolspan = $nexcel+4;
	        	$si->mergeCells($icell[$nexcel].$baris.':'.$icell[$akolspan].$baris);
				$si->setCellValue($icell[$nexcel].$baris,$grup);
	        	$nexcel = $akolspan+1;
        	}
        	$si->mergeCells($icell[$nexcel].$baris.':'.$icell[$nexcel].($baris+1));
			$si->setCellValue($icell[$nexcel].$baris,'Cap Bank-1 Cos Q');
        	$nexcel++;
        	$si->mergeCells($icell[$nexcel].$baris.':'.$icell[$nexcel].($baris+1));
			$si->setCellValue($icell[$nexcel].$baris,'Cap Bank-2 Cos Q');
        	$nexcel++;
        	$si->mergeCells($icell[$nexcel].$baris.':'.$icell[$nexcel].($baris+1));
			$si->setCellValue($icell[$nexcel].$baris,'Cap Bank-3 Cos Q');
        	$baris++;
        	$nexcel = 3;
        	ksort($arr_kolom[$subplan]);
			reset($arr_kolom[$subplan]);
        	foreach ($arr_kolom[$subplan] as $grup => $value) {
        		$si->setCellValue($icell[$nexcel].$baris,'R');
		        $nexcel++;
		        $si->setCellValue($icell[$nexcel].$baris,'S');
		        $nexcel++;
		        $si->setCellValue($icell[$nexcel].$baris,'T');
		        $nexcel++;
		        $si->setCellValue($icell[$nexcel].$baris,'V');
		        $nexcel++;
		        $si->setCellValue($icell[$nexcel].$baris,'Watt Hour Met');
		        $nexcel++;
        	}
        	$si->setSharedStyle($coltitleSy, $icell[0].($baris-1).':'.$icell[($nexcel+2)].$baris);
        	$baris++;
        	foreach ($a_tgl as $tgl => $a_jam) {
        		$a_brs = $baris;
        		$si->setCellValue($icell[1].$baris,$tgl);
        		foreach ($arr_kolom[$subplan] as $grup => $value) {
	        		
	        	}
	        	$baris++;
	        	$no = 1;
        		foreach ($a_jam as $jam => $a_qlh_id) {
        			foreach ($a_qlh_id as $qlh_id => $nil_bar) {
        				$brs = explode("@@",$nil_bar);
        				$si->setCellValue($icell[0].$baris,$no);
        				$si->setCellValue($icell[1].$baris,$tgl);
        				$si->setCellValue($icell[2].$baris,$jam);
        				$nexcel = 3;
        				ksort($arr_kolom[$subplan]);
						reset($arr_kolom[$subplan]);
	        			foreach ($arr_kolom[$subplan] as $grup => $value) {
			        		$nilai = $arr_nilai[$subplan][$tgl][$jam][$qlh_id][$grup];
		        			if($nilai) {
		        				$nil = explode("@@",$nilai);
		        				$si->setCellValue($icell[$nexcel].$baris,$nil[0]);
						        $nexcel++;
						        $si->setCellValue($icell[$nexcel].$baris,$nil[1]);
						        $nexcel++;
						        $si->setCellValue($icell[$nexcel].$baris,$nil[2]);
						        $nexcel++;
						        $si->setCellValue($icell[$nexcel].$baris,$nil[3]);
						        $nexcel++;
						        $si->setCellValue($icell[$nexcel].$baris,$nil[4]);
						        $nexcel++;
		        			} else {
		        				$nexcel++;
						        $nexcel++;
						        $nexcel++;
						        $nexcel++;
						        $nexcel++;
		        			}
			        	}
			        	$si->setCellValue($icell[$nexcel].$baris,$brs[1]);
				        $nexcel++;
				        $si->setCellValue($icell[$nexcel].$baris,$brs[2]);
				        $nexcel++;
				        $si->setCellValue($icell[$nexcel].$baris,$brs[3]);
				        $no++;
        				$baris++;
        			}
        		}
        		$baris++;
        		$si->setSharedStyle($colboldunder, $icell[0].$a_brs.':'.$icell[$nexcel].$a_brs);
        		$si->setSharedStyle($coltengah, $icell[0].($a_brs+1).':'.$icell[0].($baris-1));
        		$si->setSharedStyle($colborder, $icell[1].($a_brs+1).':'.$icell[$nexcel].($baris-1));
        	}
        	$baris++;
		}
		$si->setTitle('Sheet1');
		$oexcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		if($_GET["tipe"] == "xlsx") {
			header('Content-Disposition: attachment;filename=Pemakaian_Listrik.xlsx');
		} else if($_GET["tipe"] == "xls") {
			header('Content-Disposition: attachment;filename=Pemakaian_Listrik.xls');
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
		$html = 'TIDAKADA';
		echo $html;
	}
}

function lihatdata(){
	global $app_plan_id;
	$qlh_id = $_POST['qlh_id'];
	$sql = "SELECT a.*
		from qc_listrik_header a
		where a.qlh_id = '{$qlh_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$datetime = explode(' ',$rh[qlh_date]);
	$rh[tgl] = cgx_dmy2ymd($datetime[0]);
	$rh[jam] = substr($datetime[1],0,5);
			
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[qlh_id].'</td><td>Di-input Oleh : '.$rh[qlh_user_create].'</td></tr><tr><td>Subplant : '.$rh[qlh_sub_plant].'</td><td>Tanggal Input : '.$rh[qlh_date_create].'</td></tr><tr><td>Tanggal : '.$rh[tgl].'</td><td>Di-edit Oleh : '.$rh[qlh_user_modify].'</td></tr><tr><td>Jam : '.$rh[jam].'</td><td>Tanggal Edit : '.$rh[qlh_date_modify].'</td></tr></table>';
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