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
	$sql = "SELECT a.qbh_sub_plant as subplan, a.qbh_body_code as kodebody, b.qbd_material_type as tipe, b.qbd_material_code as item_kode, c.item_nama, sum(b.qbd_value) as nilai
		from qc_bm_header a
		join qc_bm_detail b on(a.qbh_id=b.qbh_id)
		join item c on(b.qbd_material_code=c.item_kode)
		left join qc_box_unit d on(a.qbh_sub_plant=d.qbu_sub_plant and b.qbd_box_unit=d.qbu_kode) where a.qbh_rec_status='N' and a.qbh_date >= '{$tglfrom}' and a.qbh_date <= '{$tglto}' $whdua 
		group by a.qbh_sub_plant, a.qbh_body_code, b.qbd_material_type, b.qbd_material_code, c.item_nama
		order by subplan, kodebody, tipe, item_kode";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)){
		foreach($qry as $r){
			$r[tipe] = $r[tipe] == "1" ? "MATERIAL" : "ADDITIVE";
			$arr_baris["$r[subplan]"]["$r[kodebody]"]["$r[tipe]"]["$r[item_kode]"] = $r[item_nama]."@@".$r[nilai];
		}
	}
	if(is_array($arr_baris)) {
		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
		$html .= '<div style="text-align:center;font-size:20px;font-weight:bold;">PENIMBANGAN MATERIAL BODY (SUMMARY)</div><table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.',</td><td>Shift : </td><td>'.$shift.'</td></tr></table>';
		$html .= '<div style="overflow-x:auto;"><table class="adaborder">';
		foreach ($arr_baris as $subplan => $a_kodebody) {
			foreach ($a_kodebody as $kodebody => $a_tipe) {
				$html .= '<tr><th colspan="2">SUBPLANT : '.$subplan.'</th><th colspan="2">KODE BODY : '.$kodebody.'</th></tr>';
	        	$html .= '<tr><th>NO.</th><th>ITEM KODE</th><th>NAMA MATERIAL</th><th>TOTAL MATERIAL & ADDITIVE</th></tr>';
	        	foreach ($a_tipe as $tipe => $a_item_kode) {
	        		$no = 1;
	        		foreach ($a_item_kode as $item_kode => $nil_bar) {
	        			$brs = explode("@@",$nil_bar);
	        			$html .='<tr><td style="text-align:center;">'.$no.'</td><td>'.$item_kode.'</td><td style="white-space: nowrap">'.$brs[0].'</td><td style="text-align:right;">'.number_format($brs[1],2).'</td>';
	        			$no++;
	        		}
	        		if($tipe == 'MATERIAL') {
	        			$html .='<tr><td colspan="2" style="text-align:center;font-weight:bold;">ADDITIVE</td><td></td><td></td></tr>';
	        		}
	        	}
	        	$html .='<tr><td colspan="4">&nbsp;</td></tr>';
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
	$sql = "SELECT a.qbh_sub_plant as subplan, a.qbh_body_code as kodebody, b.qbd_material_type as tipe, b.qbd_material_code as item_kode, c.item_nama, sum(b.qbd_value) as nilai
		from qc_bm_header a
		join qc_bm_detail b on(a.qbh_id=b.qbh_id)
		join item c on(b.qbd_material_code=c.item_kode)
		left join qc_box_unit d on(a.qbh_sub_plant=d.qbu_sub_plant and b.qbd_box_unit=d.qbu_kode) where a.qbh_rec_status='N' and a.qbh_date >= '{$tglfrom}' and a.qbh_date <= '{$tglto}' $whdua 
		group by a.qbh_sub_plant, a.qbh_body_code, b.qbd_material_type, b.qbd_material_code, c.item_nama
		order by subplan, kodebody, tipe, item_kode";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)){
		foreach($qry as $r){
			$r[tipe] = $r[tipe] == "1" ? "MATERIAL" : "ADDITIVE";
			$arr_baris["$r[subplan]"]["$r[kodebody]"]["$r[tipe]"]["$r[item_kode]"] = $r[item_nama]."@@".$r[nilai];
		}
	}
	if(is_array($arr_baris)) {
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

		$oexcel->getProperties()->setCreator("Ardi Fianto")
								->setLastModifiedBy("Ardi Fianto");
		$si = $oexcel->setActiveSheetIndex(0);
		
		$baris = 1;
		$si->mergeCells($icell[0].$baris.':'.$icell[3].$baris);
		$si->setCellValue($icell[0].$baris,'PENIMBANGAN MATERIAL BODY (SUMMARY)');
		$baris++;
		$si->mergeCells($icell[0].$baris.':'.$icell[3].$baris);
		$si->setCellValue($icell[0].$baris,'TGL : '.$tgljudul.', Shift : '.$shift);
		$si->setSharedStyle($coltitleSy, $icell[0].($baris-1).':'.$icell[3].$baris);
		$baris +=3;
		foreach ($arr_baris as $subplan => $a_kodebody) {
			foreach ($a_kodebody as $kodebody => $a_tipe) {
				$si->mergeCells($icell[0].$baris.':'.$icell[1].$baris);
				$si->setCellValue($icell[0].$baris,'SUBPLANT : '.$subplan);
				$si->mergeCells($icell[2].$baris.':'.$icell[3].$baris);
				$si->setCellValue($icell[2].$baris,'KODE BODY : '.$kodebody);
				$baris++;
				$si->setCellValue($icell[0].$baris,'NO.');
				$si->setCellValue($icell[1].$baris,'ITEM KODE');
				$si->setCellValue($icell[2].$baris,'NAMA MATERIAL');
				$si->setCellValue($icell[3].$baris,'TOTAL MATERIAL & ADDITIVE');
	        	$si->setSharedStyle($coltitleSy, $icell[0].($baris-1).':'.$icell[2].$baris);
	        	$si->setSharedStyle($colwrap, $icell[3].($baris-1).':'.$icell[3].$baris);
	        	$baris++;
	        	foreach ($a_tipe as $tipe => $a_item_kode) {
	        		$no = 1;
	        		foreach ($a_item_kode as $item_kode => $nil_bar) {
	        			$brs = explode("@@",$nil_bar);
	        			$si->setCellValue($icell[0].$baris,$no);
        				$si->setCellValue($icell[1].$baris,$item_kode);
        				$si->setCellValue($icell[2].$baris,$brs[0]);
        				$si->setCellValue($icell[3].$baris,$brs[1]);
        				$si->setSharedStyle($colborder, $icell[0].$baris.':'.$icell[3].$baris);
        				$no++;
	        			$baris++;
	        		}
	        		if($tipe == 'MATERIAL') {
	        			// $si->mergeCells($icell[0].$baris.':'.$icell[1].$baris);
						$si->setCellValue($icell[1].$baris,'ADDITIVE' );
			        	$si->setSharedStyle($coltitleSy, $icell[0].$baris.':'.$icell[3].$baris);
			        	// $si->setSharedStyle($colkolorgreen, $icell[3].$baris);	
	        		}
	        		$baris++;
	        	}
	        	$si->mergeCells($icell[0].$baris.':'.$icell[3].$baris);
				$si->setCellValue($icell[0].$baris,'');
	        	$baris ++;
			}
		}
		$baris -=2;
		$si->getStyle('D4:D'.$baris)->getNumberFormat()->setFormatCode('#,##0.00');
		$si->setTitle('Sheet1');
		$oexcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		if($_GET["tipe"] == "xlsx") {
			header('Content-Disposition: attachment;filename=Penimbangan_Material_Body_Summ.xlsx');
		} else if($_GET["tipe"] == "xls") {
			header('Content-Disposition: attachment;filename=Penimbangan_Material_Body_Summ.xls');
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