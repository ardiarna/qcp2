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
	case "cbonama_msnt":
		cbonama_msnt($_GET['withselect']);
		break;
}

function urai(){
	global $app_plan_id, $nama_plan;
	$tanggal = explode('@', $_GET['tanggal']);
	$tgl_from = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tgl_to = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgl_judul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	$sql = "SELECT qar_ab_nama as nama_msn, qar_ab_nomor as nomor_msn, qar_date, qar_shift as shift, qar_id, qar_awal, qar_akhir
		from qc_alber_runhour
		where qar_rec_stat = 'N' and qar_date >= '{$tgl_from}' and qar_date <= '{$tgl_to}'
		order by qar_ab_nama, qar_ab_nomor, qar_date, qar_shift";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	if(is_array($qry)){
		foreach($qry as $r){
			$datetime = explode(' ',$r[qar_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			$r[jam] = substr($datetime[1],0,5);
			$arr_kolom["$r[nama_msn]"]["$r[nomor_msn]"]["$r[shift]"] = '';
			$arr_nilai["$r[nama_msn]"]["$r[nomor_msn]"]["$r[tgl]"]["$r[shift]"] = $r[qar_id]."@@".$r[qar_awal]."@@".$r[qar_akhir];
			$i++;
		}
	}
	if(is_array($arr_nilai)) {
		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
		$html .= '<div style="text-align:center;font-size:20px;font-weight:bold;">DATA RUNNING HOUR ALAT BERAT</div><table style="margin:0 auto;"><tr><td>Tanggal : </td><td>'.$tgl_judul.'</td></tr></table>';
		$html .= '<div style="overflow-x:auto;"><table class="adaborder">';
		foreach ($arr_nilai as $nama_msn => $a_nomor_msn) {
			foreach ($a_nomor_msn as $nomor_msn => $a_tgl) {
				$html .= '<tr><th style="text-align:left;">Nama Mesin</th><th style="text-align:left;" colspan="7">'.$nama_msn.'</th></tr><tr><th style="text-align:left;">Nomor Mesin</th><th style="text-align:left;" colspan="7">'.$nomor_msn.'</th></tr>';
				$html .= '<tr><th rowspan="3">TANGGAL</th><th colspan="6">RUNNING HOUR</th><th rowspan="3">TOTAL</th></tr>';
	        	$html .= '<tr><th colspan="2">SHIFT 1</th><th colspan="2">SHIFT 2</th><th colspan="2">SHIFT 3</th></tr>';
	        	$html .= '<tr><th>AWAL</th><th>AKHIR</th><th>AWAL</th><th>AKHIR</th><th>AWAL</th><th>AKHIR</th></tr>';
	        	$tg_a = 1;
	        	$nil_awal_tgl = 0;
	        	$nil_ahir_tgl = 0;
	        	foreach ($a_tgl as $tgl => $a_shift) {
	        		$nil_1 = array();
	        		$nil_2 = array();
	        		$nil_3 = array();
	        		$nil_awal = 0;
	        		$nil_ahir = 0;
	        		$html .='<tr><td style="text-align:center;">'.$tgl.'</td>';
	        		$nilai_1 = $a_shift[1];
        			if($nilai_1) {
        				$nil_1 = explode("@@",$nilai_1);
        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$nil_1[0].'\')">'.number_format($nil_1[1]).'</td>';
        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$nil_1[0].'\')">'.number_format($nil_1[2]).'</td>';
        			} else {
        				$html .= '<td></td><td></td>';
        			}
        			$nilai_2 = $a_shift[2];
        			if($nilai_2) {
        				$nil_2 = explode("@@",$nilai_2);
        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$nil_2[0].'\')">'.number_format($nil_2[1]).'</td>';
        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$nil_2[0].'\')">'.number_format($nil_2[2]).'</td>';
        			} else {
        				$html .= '<td></td><td></td>';
        			}
        			$nilai_3 = $a_shift[3];
        			if($nilai_3) {
        				$nil_3 = explode("@@",$nilai_3);
        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$nil_3[0].'\')">'.number_format($nil_3[1]).'</td>';
        				$html .= '<td style="text-align:right;" onclick="lihatData(\''.$nil_3[0].'\')">'.number_format($nil_3[2]).'</td>';
        			} else {
        				$html .= '<td></td><td></td>';
        			}

        			if($nil_1[1]){
        				$nil_awal = $nil_1[1]; 
        			} else if($nil_1[2]){
        				$nil_awal = $nil_1[2];
        			} else if($nil_2[1]){
        				$nil_awal = $nil_2[1];
        			} else if($nil_2[2]){
        				$nil_awal = $nil_2[2];
        			} else if($nil_3[1]){
        				$nil_awal = $nil_3[1];
        			} else if($nil_3[2]){
        				$nil_awal = $nil_3[2];
        			}

        			if($nil_3[2]){
        				$nil_ahir = $nil_3[2];
        			} else if($nil_3[1]){
        				$nil_ahir = $nil_3[1];
        			} else if($nil_2[2]){
        				$nil_ahir = $nil_2[2];
        			} else if($nil_2[1]){
        				$nil_ahir = $nil_2[1];
        			} else if($nil_1[2]){
        				$nil_ahir = $nil_1[2];
        			} else if($nil_1[1]){
        				$nil_ahir = $nil_1[1];
        			}

        			$nil_total = $nil_ahir - $nil_awal;
        			$html .= '<td style="text-align:right;">'.number_format($nil_total).'</td>';
	        		$html .='</tr>';

	        		if($tg_a == 1) {
	        			$nil_awal_tgl = $nil_awal;
	        			$tg_a = 2;
	        		}
	        		$nil_ahir_tgl = $nil_ahir;
        		}
        		$nil_total_tgl = $nil_ahir_tgl - $nil_awal_tgl;
        		$html .='<tr><td style="text-align:center;font-weight:bold;">TOTAL</td><td style="text-align:right;font-weight:bold;">'.number_format($nil_awal_tgl).'</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td style="text-align:right;font-weight:bold;">'.number_format($nil_ahir_tgl).'</td><td style="text-align:right;font-weight:bold;">'.number_format($nil_total_tgl).'</td></tr><tr><td colspan="8">&nbsp;</td></tr>';
	        		
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
	$tanggal = explode('@', $_GET['tanggal']);
	$tgl_from = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tgl_to = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgl_judul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	$sql = "SELECT qar_ab_nama as nama_msn, qar_ab_nomor as nomor_msn, qar_date, qar_shift as shift, qar_id, qar_awal, qar_akhir
		from qc_alber_runhour
		where qar_rec_stat = 'N' and qar_date >= '{$tgl_from}' and qar_date <= '{$tgl_to}'
		order by qar_ab_nama, qar_ab_nomor, qar_date, qar_shift";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	if(is_array($qry)){
		foreach($qry as $r){
			$datetime = explode(' ',$r[qar_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			$r[jam] = substr($datetime[1],0,5);
			$arr_kolom["$r[nama_msn]"]["$r[nomor_msn]"]["$r[shift]"] = '';
			$arr_nilai["$r[nama_msn]"]["$r[nomor_msn]"]["$r[tgl]"]["$r[shift]"] = $r[qar_id]."@@".$r[qar_awal]."@@".$r[qar_akhir];
			$i++;
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
		$colboldborder = new PHPExcel_Style();
		$colboldborder->applyFromArray(array(
		    'font'		=> array(
				'bold' 	=> true
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
		$si->setCellValue($icell[0].$baris,'DATA RUNNING HOUR ALAT BERAT');
		$si->setSharedStyle($coltitleSy, $icell[0].$baris.':'.$icell[7].$baris);
		$baris +=2;
		$zcell = 0;
		foreach ($arr_nilai as $nama_msn => $a_nomor_msn) {
			foreach ($a_nomor_msn as $nomor_msn => $a_tgl) {
				$si->setCellValue($icell[0].$baris,'Nama Mesin');
				$si->mergeCells($icell[1].$baris.':'.$icell[7].$baris);
				$si->setCellValue($icell[1].$baris,$nama_msn);
				$baris++;
				$si->setCellValue($icell[0].$baris,'Nomor Mesin');
				$si->mergeCells($icell[1].$baris.':'.$icell[7].$baris);
				$si->setCellValue($icell[1].$baris,$nomor_msn);
				$si->setSharedStyle($colboldaja, $icell[0].($baris-1).':'.$icell[7].$baris);	
				$baris++;
				$si->mergeCells($icell[0].$baris.':'.$icell[0].($baris+2));
				$si->setCellValue($icell[0].$baris,'TANGGAL');
				$si->mergeCells($icell[1].$baris.':'.$icell[6].$baris);
				$si->setCellValue($icell[1].$baris,'RUNNING HOUR');
				$si->mergeCells($icell[7].$baris.':'.$icell[7].($baris+2));
				$si->setCellValue($icell[7].$baris,'TOTAL');
				$baris++;
				$si->mergeCells($icell[1].$baris.':'.$icell[2].$baris);
				$si->setCellValue($icell[1].$baris,'SHIFT 1');
				$si->mergeCells($icell[3].$baris.':'.$icell[4].$baris);
				$si->setCellValue($icell[3].$baris,'SHIFT 2');
				$si->mergeCells($icell[5].$baris.':'.$icell[6].$baris);
				$si->setCellValue($icell[5].$baris,'SHIFT 3');
				$baris++;
				$si->setCellValue($icell[1].$baris,'AWAL');
				$si->setCellValue($icell[2].$baris,'AKHIR');
				$si->setCellValue($icell[3].$baris,'AWAL');
				$si->setCellValue($icell[4].$baris,'AKHIR');
				$si->setCellValue($icell[5].$baris,'AWAL');
				$si->setCellValue($icell[6].$baris,'AKHIR');
				$si->setSharedStyle($coltitleSy, $icell[0].($baris-2).':'.$icell[7].$baris);
				$baris++;
				$tg_a = 1;
	        	$nil_awal_tgl = 0;
	        	$nil_ahir_tgl = 0;
	        	$a_brs = $baris;
	        	foreach ($a_tgl as $tgl => $a_shift) {
	        		$nil_1 = array();
	        		$nil_2 = array();
	        		$nil_3 = array();
	        		$nil_awal = 0;
	        		$nil_ahir = 0;
	        		$si->setCellValue($icell[0].$baris,$tgl);
	        		$nilai_1 = $a_shift[1];
        			if($nilai_1) {
        				$nil_1 = explode("@@",$nilai_1);
        				$si->setCellValue($icell[1].$baris,$nil_1[1]);
	        			$si->setCellValue($icell[2].$baris,$nil_1[2]);
        			}
        			$nilai_2 = $a_shift[2];
        			if($nilai_2) {
        				$nil_2 = explode("@@",$nilai_2);
        				$si->setCellValue($icell[3].$baris,$nil_2[1]);
	        			$si->setCellValue($icell[4].$baris,$nil_2[2]);
        			}
        			$nilai_3 = $a_shift[3];
        			if($nilai_3) {
        				$nil_3 = explode("@@",$nilai_3);
        				$si->setCellValue($icell[5].$baris,$nil_3[1]);
	        			$si->setCellValue($icell[6].$baris,$nil_3[2]);
        			}

        			if($nil_1[1]){
        				$nil_awal = $nil_1[1]; 
        			} else if($nil_1[2]){
        				$nil_awal = $nil_1[2];
        			} else if($nil_2[1]){
        				$nil_awal = $nil_2[1];
        			} else if($nil_2[2]){
        				$nil_awal = $nil_2[2];
        			} else if($nil_3[1]){
        				$nil_awal = $nil_3[1];
        			} else if($nil_3[2]){
        				$nil_awal = $nil_3[2];
        			}

        			if($nil_3[2]){
        				$nil_ahir = $nil_3[2];
        			} else if($nil_3[1]){
        				$nil_ahir = $nil_3[1];
        			} else if($nil_2[2]){
        				$nil_ahir = $nil_2[2];
        			} else if($nil_2[1]){
        				$nil_ahir = $nil_2[1];
        			} else if($nil_1[2]){
        				$nil_ahir = $nil_1[2];
        			} else if($nil_1[1]){
        				$nil_ahir = $nil_1[1];
        			}

        			$nil_total = $nil_ahir - $nil_awal;
        			$si->setCellValue($icell[7].$baris,$nil_total);
	  				$baris++;
	        		if($tg_a == 1) {
	        			$nil_awal_tgl = $nil_awal;
	        			$tg_a = 2;
	        		}
	        		$nil_ahir_tgl = $nil_ahir;
        		}
        		$nil_total_tgl = $nil_ahir_tgl - $nil_awal_tgl;
        		$si->setCellValue($icell[0].$baris,'TOTAL');
	        	$si->setCellValue($icell[1].$baris,$nil_awal_tgl);
	        	$si->setCellValue($icell[6].$baris,$nil_ahir_tgl);
	        	$si->setCellValue($icell[7].$baris,$nil_total_tgl);
	        	$si->setSharedStyle($coltitleSy, $icell[0].$baris);	
	        	$si->setSharedStyle($colboldborder, $icell[1].$baris.':'.$icell[7].$baris);	
	        	$si->setSharedStyle($colborder, $icell[0].($a_brs).':'.$icell[7].($baris-1));
	    		$baris += 2;    	
			}
		}
		$si->getStyle('B8:'.$icell[7].$baris)->getNumberFormat()->setFormatCode('#,##0.00');
		$si->setTitle('Sheet1');
		$oexcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		if($_GET["tipe"] == "xlsx") {
			header('Content-Disposition: attachment;filename=Running_Hour_Alat_Berat.xlsx');
		} else if($_GET["tipe"] == "xls") {
			header('Content-Disposition: attachment;filename=Running_Hour_Alat_Berat.xls');
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
	$qar_id = $_POST['qar_id'];
	$sql = "SELECT a.*
		from qc_alber_runhour a
		where a.qar_id = '{$qar_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[qar_id].'</td></tr><tr><td>Tanggal : '.cgx_dmy2ymd(substr($rh[qar_date],0,10)).'</td></tr><tr><td>Shift : '.$rh[qar_shift].'</td><td>Di-input Oleh : '.$rh[qar_user_create].'</td></tr><tr><td>Nama Mesin : '.$rh[qar_ab_nama].'</td><td>Tanggal Input : '.$rh[qar_date_create].'</td></tr><tr><td>Nomor Mesin : '.$rh[qar_ab_nomor].'</td><td>Di-edit Oleh : '.$rh[qar_user_modify].'</td></tr><tr><td>Keterangan : '.$rh[qar_remark].'</td><td>Tanggal Edit : '.$rh[qar_date_modify].'</td></tr></table>';
	$responce->hasil=$out;
    echo json_encode($responce);

}

function cbonama_msnt($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_nama_msnt("TIDAKADA",true);
	$out .= $withselect ? "</select>" : "";
	echo $out;
}



?>