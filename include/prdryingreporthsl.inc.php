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



function urai() {
	global $app_plan_id;
	$subplan = $_GET['subplan'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and qpdh_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT COUNT(*) AS jmldata FROM qc_pd_hsl_header WHERE qpdh_status = 'N' and qpdh_date >= '{$tglfrom}' and qpdh_date <= '{$tglto}' $whdua ";
	$qry = dbselect_plan($app_plan_id, $sql);
	
	if($qry[jmldata] <= 0){
		$html = 'TIDAKADA';
	}else{

		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
		$html .= '<div style="text-align:center;font-size:20px;font-weight:bold;">LAPORAN KAREGU PRESS</div>
  				  <div style="text-align:center;font-size:20px;font-weight:bold;">TANGGAL : '.$tgljudul.'</div>';
		

		if($subplan <> 'All') {
			$html .='<br>'.urai1($subplan,$tglfrom,$tglto);
		} else {
				$sql0 = "SELECT distinct qpdh_sub_plant FROM qc_pd_hsl_header 
						 WHERE qpdh_status = 'N' and qpdh_date >= '{$tglfrom}' AND qpdh_date <= '{$tglto}'
						 ORDER BY qpdh_sub_plant";
				$qry0 = dbselect_plan_all($app_plan_id, $sql0);
				foreach($qry0 as $r0){
					$html .='<br>'.urai1($r0[qpdh_sub_plant],$tglfrom,$tglto);
				}
		}
		$html .='</div>';
	}


	$responce->detailtabel = $html; 
	echo json_encode($responce);
}


function urai1($subplanu1,$tglfrom,$tglto){
	global $app_plan_id;

	$sql = "SELECT a.qpdh_sub_plant as subplan,
				   a.qpdh_date
			FROM qc_pd_hsl_header a
			JOIN qc_pd_hsl_detail b on(a.qpdh_id=b.qpdh_id)
			WHERE qpdh_status = 'N' and a.qpdh_date >= '{$tglfrom}' AND a.qpdh_date <= '{$tglto}' AND a.qpdh_sub_plant = '{$subplanu1}'
			ORDER BY a.qpdh_sub_plant, a.qpdh_date";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)){
		foreach($qry as $r){
			$datetime = explode(' ',$r[qpdh_date]);
			$r[tgl]   = cgx_dmy2ymd($datetime[0]);

			$arr_nilai["$r[subplan]"]["$r[tgl]"] = $r[nilai];
		}
	}
	if(is_array($arr_nilai)) {

		foreach ($arr_nilai as $subplan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_group) {

				$html .= '<div style="font-size:16px;font-weight:bold;">SUBPLANT : '.$subplan.' &nbsp; '.$tgl.'</div>';
				$html .= urai2($subplanu1,$tgl);
			}
		}
	} else {
		$html = 'TIDAKADA';
	}
	return $html;
}




function urai2($subplanu2,$tgl){
	global $app_plan_id;

	$tgl2 = date('Y-m-d', strtotime($tgl));

	$sql2 = "SELECT distinct qcpdm_group, qcpdm_desc, CAST(qcpdm_group AS integer) as kodee from qc_pd_prep_group order by qcpdm_group";
	$qry2 = dbselect_plan_all($app_plan_id, $sql2);
	foreach($qry2 as $r2) {
		if($r2[qcpdm_group] == "01") {

			$html .= '<div style="font-size:14px;font-weight:bold;"><u>'.Romawi($r2[kodee]).'. '.$r2[qcpdm_desc].'</u></div>';
			
			$html .= '<div style="overflow-x:auto;"><table class="adaborder">';

			//header start
			$html .='<tr>';
			$html .='<th rowspan="2">NO</th>';
			
			$sqlcolmn = "SELECT COUNT(*) AS jmlcolmn FROM qc_pd_prep_group_detil WHERE qcpdm_group = '$r2[qcpdm_group]' ";
			$qrycolmn = dbselect_plan($app_plan_id, $sqlcolmn);
			$jmlcolmn = $qrycolmn[jmlcolmn]; 
			

			for ($xshift = 1; $xshift <= 4; $xshift++) {
				if($xshift != 4){
					$html .='<th colspan="'.$jmlcolmn.'">SHIFT '.Romawi($xshift).'</th>';
				}else{
					$html .='<th colspan="'.$jmlcolmn.'">TOTAL</th>';
				} 
			} 

			$html .='</tr>';

			$html .='<tr>';

			for ($xshift2 = 1; $xshift2 <= 4; $xshift2++) {
				$sqlsubgrup = "SELECT * FROM qc_pd_prep_group_detil WHERE qcpdm_group = '{$r2[qcpdm_group]}' ";
				$qrysubgrup = dbselect_plan_all($app_plan_id, $sqlsubgrup);
				foreach($qrysubgrup as $subgrup) {

					$html .='<th>'.$subgrup[qcpdd_control_desc].'</th>';
				}
			} 
			$html .='</tr>';

			//header end

			$sqlpress = "SELECT b.qpp_press_no
						 FROM qc_pd_hsl_header a
						 JOIN qc_pd_hsl_detail b on(a.qpdh_id=b.qpdh_id)
						 WHERE qpdh_status = 'N' and a.qpdh_date = '{$tgl2}' AND a.qpdh_sub_plant = '{$subplanu2}' AND b.qcpdm_group = '{$r2[qcpdm_group]}'
						 GROUP BY b.qpp_press_no 
						 ORDER BY b.qpp_press_no";
			$qrypress = dbselect_plan_all($app_plan_id, $sqlpress);
			foreach($qrypress as $press) {

				$html .='<tr>';
				$html .='<th class="text-center">'.$press[qpp_press_no].'</th>';
				// shift
				for ($xshift3 = 1; $xshift3 <= 4; $xshift3++) {
					$sqlsubgrup2 = "SELECT * FROM qc_pd_prep_group_detil WHERE qcpdm_group = '{$r2[qcpdm_group]}' ";
					$qrysubgrup2 = dbselect_plan_all($app_plan_id, $sqlsubgrup2);
					foreach($qrysubgrup2 as $subgrup2) {
						if($xshift3 <> 4){
							$sqlnilai = "SELECT b.qpdh_pd_value, b.qpdh_id
										 FROM qc_pd_hsl_header a
										 JOIN qc_pd_hsl_detail b on(a.qpdh_id=b.qpdh_id)
										 WHERE qpdh_status = 'N' and a.qpdh_date = '{$tgl2}' 
										 AND a.qpdh_sub_plant = '{$subplanu2}' 
										 AND b.qcpdm_group = '{$r2[qcpdm_group]}'
										 AND b.qcpdd_seq = '{$subgrup2[qcpdd_seq]}'
										 AND a.qpdh_shift = '{$xshift3}'
										 AND b.qpp_press_no = '{$press[qpp_press_no]}'";
							$qrynilai = dbselect_plan($app_plan_id, $sqlnilai);

							$html .='<td class="text-right"><span onclick="lihatData(\''.$qrynilai[qpdh_id].'\')">'.$qrynilai[qpdh_pd_value].'</span></td>';
						}else{
							$sqlnilai = "SELECT SUM(CAST(b.qpdh_pd_value AS decimal)) AS ttlright
										 FROM qc_pd_hsl_header a
										 JOIN qc_pd_hsl_detail b on(a.qpdh_id=b.qpdh_id)
										 WHERE qpdh_status = 'N' and a.qpdh_date = '{$tgl2}' 
										 AND a.qpdh_sub_plant = '{$subplanu2}' 
										 AND b.qcpdm_group = '{$r2[qcpdm_group]}'
										 AND b.qcpdd_seq = '{$subgrup2[qcpdd_seq]}'
										 AND b.qpp_press_no = '{$press[qpp_press_no]}'";
							$qrynilai = dbselect_plan($app_plan_id, $sqlnilai);

							$html .='<td class="text-right">'.$qrynilai[ttlright].'</td>';
						}
					}
				} 

				$html .='</tr>';

			}


			//total bottom start
			$html .='<tr><th class="text-center">TOTAL</th>';

			for ($xshift4 = 1; $xshift4 <= 4; $xshift4++) {
				$sqlsubgrup4 = "SELECT * FROM qc_pd_prep_group_detil WHERE qcpdm_group = '{$r2[qcpdm_group]}' ";
				$qrysubgrup4 = dbselect_plan_all($app_plan_id, $sqlsubgrup4);
				foreach($qrysubgrup4 as $subgrup4) {

					if($xshift4 <> 4){
						$wshift = "AND a.qpdh_shift = '{$xshift4}'";
					}else{
						$wshift = "";
					}
						$sqlnilai3 = "SELECT SUM(CAST(b.qpdh_pd_value AS decimal)) AS ttlbottom
									 FROM qc_pd_hsl_header a
									 JOIN qc_pd_hsl_detail b on(a.qpdh_id=b.qpdh_id)
									 WHERE qpdh_status = 'N' and a.qpdh_date = '{$tgl2}' 
									 AND a.qpdh_sub_plant = '{$subplanu2}' 
									 AND b.qcpdm_group = '{$r2[qcpdm_group]}'
									 AND b.qcpdd_seq = '{$subgrup4[qcpdd_seq]}'
									 $wshift ";
						$qrynilai3 = dbselect_plan($app_plan_id, $sqlnilai3);

						$html .='<th class="text-right">'.$qrynilai3[ttlbottom].'</th>';
				}
			} 
			$html .='</tr>';
			//total bottom end


			
			$html .='</table></div>';

		}else if($r2[qcpdm_group] == '02'){
			
			$html .= '<div style="font-size:14px;font-weight:bold;"><u>'.Romawi($r2[kodee]).'. '.$r2[qcpdm_desc].'</u></div>';
			
			$html .= '<div style="overflow-x:auto;"><table class="adaborder"><tr>';

			for ($xshift2 = 1; $xshift2 <= 3; $xshift2++) {
					$html .= '<th height="50" width="7%">SHIFT '.Romawi($xshift2).'</th>';


					$sql3 = "SELECT qpdh_pd_value as nilai, a.qpdh_id
							FROM qc_pd_hsl_header a
							JOIN qc_pd_hsl_detail b on(a.qpdh_id=b.qpdh_id)
							where qpdh_status = 'N' and a.qpdh_date = '{$tgl2}' AND a.qpdh_sub_plant = '{$subplanu2}' 
							AND b.qcpdm_group = '{$r2[qcpdm_group]}'
							AND a.qpdh_shift = '{$xshift2}'
							ORDER BY b.qcpdd_seq";
					$qry3 = dbselect_plan($app_plan_id, $sql3);

					$html .= '<td width="25%"><span onclick="lihatData(\''.$qry3[qpdh_id].'\')">'.nl2br(htmlspecialchars($qry3[nilai])).'</span></td>';
			} 

			$html .='</tr></table></div>';
				
		}else{
				$html .= '<div style="font-size:14px;font-weight:bold;"><u>'.Romawi($r2[kodee]).'. '.$r2[qcpdm_desc].'</u></div>';

				$html .= '<div style="overflow-x:auto;"><table class="adaborder"><tr>';
				for ($xshift2 = 1; $xshift2 <= 3; $xshift2++) {
						$html .= '<th width="33%">SHIFT '.Romawi($xshift2).'</th>';
				} 
				$html .='</tr><tr>';


				for ($xshift2a = 1; $xshift2a <= 3; $xshift2a++) {
						$sql3 = "SELECT qpdh_shift as shift, qpdh_pd_value as nilai, a.qpdh_id
								 FROM qc_pd_hsl_header a
								 JOIN qc_pd_hsl_detail b on(a.qpdh_id=b.qpdh_id)
								 where qpdh_status = 'N' and a.qpdh_date = '{$tgl2}' AND a.qpdh_sub_plant = '{$subplanu2}' AND b.qcpdm_group = '{$r2[qcpdm_group]}'
								 AND a.qpdh_shift = '{$xshift2a}'
								 ORDER BY b.qcpdd_seq";
						$qry3 = dbselect_plan($app_plan_id, $sql3);

						$html .= '<td style="vertical-align: top"><span onclick="lihatData(\''.$qry3[qpdh_id].'\')">'.nl2br(htmlspecialchars($qry3[nilai])).'</span></td>';
				} 
				$html .='</tr>';

				$html .='</table></div>';
		}
		$html .='<br>';
	}

	$html .='<br>';



	//ttd start
	// $html .= '<div style="overflow-x:auto;"><table width="100%"><tr>';
	// for ($ttd = 1; $ttd <= 4; $ttd++) {
	// 	if($ttd <> 4){
	// 		$ttdVal = 'KAREGU SHIFT - '.Romawi($ttd);
	// 	}else{
	// 		$ttdVal = 'KASUBSI PRESS';
	// 	}
	// 		$html .= '<td width="25%" class="text-center"><b>'.$ttdVal.'</b></td>';
	// } 
	// $html .='</tr>';

	// $html .= '<tr>';
	// for ($ttd2 = 1; $ttd2 <= 4; $ttd2++) {
	// 		$ttd2Val = '(...................................)';
	// 		$html .= '<td class="text-center" height="120px">'.$ttd2Val.'</td>';
	// } 
	// $html .='</tr>';

	// $html .='</table></div>';
	//ttd end

	return $html;
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
		$whdua .= " and qpdh_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT COUNT(*) AS jmldata FROM qc_pd_hsl_header WHERE qpdh_status = 'N' and qpdh_date >= '{$tglfrom}' and qpdh_date <= '{$tglto}' $whdua ";
	$qry = dbselect_plan($app_plan_id, $sql);
	
	if($qry[jmldata] > 0){

	
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
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
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
		$colborderabsen = new PHPExcel_Style();
		$colborderabsen->applyFromArray(array(
		    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    ),
		    'alignment' => array(
		    	'horizontal'	=> PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
		    	'vertical'		=> PHPExcel_Style_Alignment::VERTICAL_CENTER,
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
		$oexcel->getProperties()->setCreator("Ardi Fianto")
								->setLastModifiedBy("Ardi Fianto");
		$si = $oexcel->setActiveSheetIndex(0);

		//jml kolom
		$qcolm = "SELECT COUNT(*) AS jmlkolom FROM qc_pd_prep_group_detil WHERE qcpdm_group = '01'";
		$dcolm = dbselect_plan($app_plan_id, $qcolm);
		$shiftjmlkolom = $dcolm[jmlkolom];
		$jmlkolom = $dcolm[jmlkolom]*4;
		
		$baris = 1;
		$si->mergeCells($icell[0].$baris.':'.$icell[$jmlkolom].$baris);
		$si->setCellValue($icell[0].$baris,'LAPORAN KAREGU PRESS');
		$baris++;
		$si->mergeCells($icell[0].$baris.':'.$icell[$jmlkolom].$baris);
		$si->setCellValue($icell[0].$baris,'TANGGAL : '.$tgljudul);
		$si->setSharedStyle($colboldaja, $icell[0].($baris-1).':'.$icell[$jmlkolom].$baris);
		$baris +=3;


		$whdua2 = "";
		if($subplan <> 'All') {
			$whdua2 .= " and a.qpdh_sub_plant = '".$subplan."'";
		}
		$sql = "SELECT distinct a.qpdh_sub_plant as subplan, a.qpdh_date
				FROM qc_pd_hsl_header a
				JOIN qc_pd_hsl_detail b on(a.qpdh_id=b.qpdh_id)
				WHERE qpdh_status = 'N' and a.qpdh_date >= '{$tglfrom}' AND a.qpdh_date <= '{$tglto}' $whdua2
				ORDER BY a.qpdh_sub_plant, a.qpdh_date";

		$qry = dbselect_plan_all($app_plan_id, $sql);
		
		$ndata = 1;
		foreach ($qry as $plan2) {
			$tglitem2  =  date('d-m-Y', strtotime($plan2[qpdh_date]));
			$tglitem   =  date('Y-m-d', strtotime($plan2[qpdh_date]));
			$subplanitem = $plan2[subplan];

			if($ndata > 1){
				$baris+=5;
			}


			$si->mergeCells($icell[0].$baris.':'.$icell[1].$baris);
			$si->setCellValue($icell[0].$baris,'SUBPLANT : '.$plan2[subplan]);
			$si->mergeCells($icell[2].$baris.':'.$icell[$jmlkolom].$baris);
			$si->setCellValue($icell[2].$baris,$tglitem2);
			$si->setSharedStyle($colboldaja, $icell[0].$baris.':'.$icell[2].$baris);	
			$baris++;


			$sql22 = "SELECT distinct qcpdm_group, qcpdm_desc, CAST(qcpdm_group AS integer) as kodee from qc_pd_prep_group order by qcpdm_group";
			$qry22 = dbselect_plan_all($app_plan_id, $sql22);
			foreach($qry22 as $r22) {
				if($r22[qcpdm_group] == '01'){
					$si->mergeCells($icell[0].$baris.':'.$icell[$jmlkolom].$baris);
					$si->setCellValue($icell[0].$baris,Romawi($r22[kodee]).'. '.$r22[qcpdm_desc]);
					$si->setSharedStyle($colboldunderline, $icell[0].$baris.':'.$icell[2].$baris);
					$baris++;


					$si->mergeCells($icell[0].$baris.':'.$icell[0].($baris+1));
					$si->setCellValue($icell[0].$baris,' NO ');
					$si->setSharedStyle($coltitleSy, $icell[0].$baris.':'.$icell[0].($baris+1));

					$cell_a = 1;
					$cell_b = $shiftjmlkolom;
					for ($xshift2 = 1; $xshift2 <= 4; $xshift2++) {
						if($xshift2 == 4){
							$xshift2Val = 'TOTAL';
						}else{
							$xshift2Val = 'SHIFT '.Romawi($xshift2);
						}

						$si->mergeCells($icell[$cell_a].$baris.':'.$icell[$cell_b].$baris);
						$si->setCellValue($icell[$cell_a].$baris,$xshift2Val);

						$cell_a = $cell_a+$shiftjmlkolom;
						$cell_b = $cell_b+$shiftjmlkolom;
					} 
					$si->setSharedStyle($coltitleSy, $icell[0].$baris.':'.$icell[$jmlkolom].$baris);
					$baris++;


					//header 2
					$cell_sub_a = 1;
					for ($xshift33 = 1; $xshift33 <= 4; $xshift33++) {
						$sqlsubgrup22 = "SELECT * FROM qc_pd_prep_group_detil WHERE qcpdm_group = '{$r22[qcpdm_group]}' ";
						$qrysubgrup22 = dbselect_plan_all($app_plan_id, $sqlsubgrup22);
						foreach($qrysubgrup22 as $subgrup22) {
							$si->setCellValue($icell[$cell_sub_a].$baris,$subgrup22[qcpdd_control_desc]);
						 $cell_sub_a++;
						}
					}
					$si->setSharedStyle($coltitleSy, $icell[1].$baris.':'.$icell[$jmlkolom].$baris);
					$baris++;


					$sqlpress2 = "SELECT b.qpp_press_no
								 FROM qc_pd_hsl_header a
								 JOIN qc_pd_hsl_detail b on(a.qpdh_id=b.qpdh_id)
								 WHERE qpdh_status = 'N' and a.qpdh_date = '{$tglitem}' AND a.qpdh_sub_plant = '{$subplanitem}' AND b.qcpdm_group = '{$r22[qcpdm_group]}'
								 GROUP BY b.qpp_press_no 
								 ORDER BY b.qpp_press_no";
					$qrypress2 = dbselect_plan_all($app_plan_id, $sqlpress2);
					foreach($qrypress2 as $press2) {
						$si->setCellValue($icell[0].$baris,$press2[qpp_press_no]);
						$si->setSharedStyle($coltitleSy, $icell[0].$baris.':'.$icell[$jmlkolom].$baris);

						$cell_sub_a2 = 1;
						for ($xshift34 = 1; $xshift34 <= 4; $xshift34++) {
							$sqlsubgrup34 = "SELECT * FROM qc_pd_prep_group_detil WHERE qcpdm_group = '{$r22[qcpdm_group]}' ";
							$qrysubgrup34 = dbselect_plan_all($app_plan_id, $sqlsubgrup34);
							foreach($qrysubgrup34 as $subgrup34) {
									if($xshift34 <> 4){
										$sqlnilai2 = "SELECT b.qpdh_pd_value, b.qpdh_id
													 FROM qc_pd_hsl_header a
													 JOIN qc_pd_hsl_detail b on(a.qpdh_id=b.qpdh_id)
													 WHERE qpdh_status = 'N' and a.qpdh_date = '{$tglitem}' 
													 AND a.qpdh_sub_plant = '{$subplanitem}' 
													 AND b.qcpdm_group = '{$r22[qcpdm_group]}'
													 AND b.qcpdd_seq = '{$subgrup34[qcpdd_seq]}'
													 AND a.qpdh_shift = '{$xshift34}'
													 AND b.qpp_press_no = '{$press2[qpp_press_no]}'";
										$qrynilai2 = dbselect_plan($app_plan_id, $sqlnilai2);

										$si->setCellValue($icell[$cell_sub_a2].$baris,$qrynilai2[qpdh_pd_value]);
									}else{
										$sqlnilai2 = "SELECT SUM(CAST(b.qpdh_pd_value AS decimal)) AS ttlright
													 FROM qc_pd_hsl_header a
													 JOIN qc_pd_hsl_detail b on(a.qpdh_id=b.qpdh_id)
													 WHERE qpdh_status = 'N' and a.qpdh_date = '{$tglitem}' 
													 AND a.qpdh_sub_plant = '{$subplanitem}' 
													 AND b.qcpdm_group = '{$r22[qcpdm_group]}'
													 AND b.qcpdd_seq = '{$subgrup34[qcpdd_seq]}'
													 AND b.qpp_press_no = '{$press2[qpp_press_no]}'";
										$qrynilai2 = dbselect_plan($app_plan_id, $sqlnilai2);

										$si->setCellValue($icell[$cell_sub_a2].$baris,$qrynilai2[ttlright]);
									}
							 $cell_sub_a2++;
							}
						}

						$si->setSharedStyle($colborder, $icell[1].$baris.':'.$icell[$jmlkolom].$baris);
							
						$baris++;
					}
					//total bottom start

						$si->setCellValue($icell[0].$baris,' TOTAL ');
						$si->setSharedStyle($coltitleSy, $icell[0].$baris.':'.$icell[0].$baris);

						$cell_sub_a3 = 1;
						for ($xshift35 = 1; $xshift35 <= 4; $xshift35++) {
							$sqlsubgrup35 = "SELECT * FROM qc_pd_prep_group_detil WHERE qcpdm_group = '{$r22[qcpdm_group]}' ";
							$qrysubgrup35 = dbselect_plan_all($app_plan_id, $sqlsubgrup35);
							foreach($qrysubgrup35 as $subgrup35){
								if($xshift35 <> 4){
									$wshift35 = "AND a.qpdh_shift = '{$xshift35}'";
								}else{
									$wshift35 = "";
								}
									$sqlnilai35 = "SELECT SUM(CAST(b.qpdh_pd_value AS decimal)) AS ttlbottom
												  FROM qc_pd_hsl_header a
												  JOIN qc_pd_hsl_detail b on(a.qpdh_id=b.qpdh_id)
												  WHERE qpdh_status = 'N' and a.qpdh_date = '{$tglitem}' 
												  AND a.qpdh_sub_plant = '{$subplanitem}' 
												  AND b.qcpdm_group = '{$r22[qcpdm_group]}'
												  AND b.qcpdd_seq = '{$subgrup35[qcpdd_seq]}'
												  $wshift35 ";
									$qrynilai35 = dbselect_plan($app_plan_id, $sqlnilai35);

									$si->setCellValue($icell[$cell_sub_a3].$baris,$qrynilai35[ttlbottom]);
						 		$cell_sub_a3++;
							}
						}
						$si->setSharedStyle($coltitleSy2, $icell[1].$baris.':'.$icell[$jmlkolom].$baris);

					$baris++;

				}else if($r22[qcpdm_group] == '02'){
					$baris++;
					$si->mergeCells($icell[0].$baris.':'.$icell[$jmlkolom].$baris);
					$si->setCellValue($icell[0].$baris,Romawi($r22[kodee]).'. '.$r22[qcpdm_desc]);
					$si->setSharedStyle($colboldunderline, $icell[0].$baris.':'.$icell[2].$baris);
					$baris++;


					for ($xshift40 = 1; $xshift40 <= 3; $xshift40++) {
						if($xshift40 == 1){
							$a1 = 0;
							$a2 = 1;
							$a3 = 3;
						}else if($xshift40 == 2){
							$a1 = 4;
							$a2 = 5;
							$a3 = 7;
						}else{
							$a1 = 8;
							$a2 = 9;
							$a3 = 12;
						}

						$si->mergeCells($icell[$a1].$baris.':'.$icell[$a1].($baris+2));
						$si->setCellValue($icell[$a1].$baris,'SHIFT '.Romawi($xshift40));
						$si->setSharedStyle($coltitleSy, $icell[$a1].$baris.':'.$icell[$a1].($baris+2));

						$sqlabsen = "SELECT qpdh_pd_value
									  FROM qc_pd_hsl_header a
									  JOIN qc_pd_hsl_detail b on(a.qpdh_id=b.qpdh_id)
									  WHERE qpdh_status = 'N' and a.qpdh_date = '{$tglitem}' 
									  AND a.qpdh_sub_plant = '{$subplanitem}' 
									  AND b.qcpdm_group = '{$r22[qcpdm_group]}'
									  AND b.qcpdd_seq = '1'
									  AND a.qpdh_shift = '{$xshift40}' ";
						$qryabsen = dbselect_plan($app_plan_id, $sqlabsen);

						$si->mergeCells($icell[$a2].$baris.':'.$icell[$a3].($baris+2));
						$si->setCellValue($icell[$a2].$baris,$qryabsen[qpdh_pd_value]);
						$si->setSharedStyle($colborderabsen, $icell[$a2].$baris.':'.$icell[$a3].($baris+2));
						
					}
					$baris++;
				}else{
					$baris+=3;
					$si->mergeCells($icell[0].$baris.':'.$icell[$jmlkolom].$baris);
					$si->setCellValue($icell[0].$baris,Romawi($r22[kodee]).'. '.$r22[qcpdm_desc]);
					
					$si->setSharedStyle($colboldunderline, $icell[0].$baris.':'.$icell[2].$baris);
					$baris++;


					for ($xshift36 = 1; $xshift36 <= 3; $xshift36++) {
						if($xshift36 == 1){
							$celketa = 0;
							$celketb = 3;
						}else if($xshift36 == 2){
							$celketa = 4;
							$celketb = 7;
						}else{
							$celketa = 8;
							$celketb = 12;
						}

						$si->mergeCells($icell[$celketa].$baris.':'.$icell[$celketb].$baris);
						$si->setCellValue($icell[$celketa].$baris,'SHIFT '.Romawi($xshift36));
					}

					$si->setSharedStyle($coltitleSy, $icell[0].$baris.':'.$icell[12].$baris);
					$baris++;


					for ($xshift37 = 1; $xshift37 <= 3; $xshift37++) {
						if($xshift37 == 1){
							$celketa2 = 0;
							$celketb2 = 3;
						}else if($xshift37 == 2){
							$celketa2 = 4;
							$celketb2 = 7;
						}else{
							$celketa2 = 8;
							$celketb2 = 12;
						}

						$sqlket = "SELECT qpdh_pd_value
									  FROM qc_pd_hsl_header a
									  JOIN qc_pd_hsl_detail b on(a.qpdh_id=b.qpdh_id)
									  WHERE qpdh_status = 'N' and a.qpdh_date = '{$tglitem}' 
									  AND a.qpdh_sub_plant = '{$subplanitem}' 
									  AND b.qcpdm_group = '{$r22[qcpdm_group]}'
									  AND b.qcpdd_seq = '1'
									  AND a.qpdh_shift = '{$xshift37}' ";
						$qryket = dbselect_plan($app_plan_id, $sqlket);


						$si->mergeCells($icell[$celketa2].$baris.':'.$icell[$celketb2].($baris+10));
						$si->setCellValue($icell[$celketa2].$baris,$qryket[qpdh_pd_value]);
					}

					$si->setSharedStyle($colborderket, $icell[0].$baris.':'.$icell[12].($baris+10));
					$baris++;			
				}
			}

			$baris+=11;
			//ttd start
			for ($ttda = 1; $ttda <= 4; $ttda++) {
				if($ttda == 1){
					$celketa22 = 0;
					$celketb22 = 3;
				}else if($ttda == 2){
					$celketa22 = 4;
					$celketb22 = 6;
				}else if($ttda == 3){
					$celketa22 = 7;
					$celketb22 = 9;
				}else{
					$celketa22 = 10;
					$celketb22 = 12;
				}

				if($ttda <> 4){
					$ttdaVal = 'KAREGU SHIFT - '.Romawi($ttda);
				}else{
					$ttdaVal = 'KASUBSI PRESS';
				}

				$si->mergeCells($icell[$celketa22].$baris.':'.$icell[$celketb22].$baris);
				$si->setCellValue($icell[$celketa22].$baris,$ttdaVal);
				$si->setSharedStyle($coltengah, $icell[0].$baris.':'.$icell[12].$baris);	
			} 

			$baris++;

			for ($ttda = 1; $ttda <= 4; $ttda++) {
				if($ttda == 1){
					$celketa222 = 0;
					$celketb222 = 3;
				}else if($ttda == 2){
					$celketa222 = 4;
					$celketb222 = 6;
				}else if($ttda == 3){
					$celketa222 = 7;
					$celketb222 = 9;
				}else{
					$celketa222 = 10;
					$celketb222 = 12;
				}

				$si->mergeCells($icell[$celketa222].$baris.':'.$icell[$celketb222].($baris+3));
				$si->setCellValue($icell[$celketa222].$baris,'');
				$si->setSharedStyle($coltengah, $icell[0].$baris.':'.$icell[12].($baris+3));
				
			} 
			$baris+=4;

			for ($ttda = 1; $ttda <= 4; $ttda++) {
				if($ttda == 1){
					$celketa222 = 0;
					$celketb222 = 3;
				}else if($ttda == 2){
					$celketa222 = 4;
					$celketb222 = 6;
				}else if($ttda == 3){
					$celketa222 = 7;
					$celketb222 = 9;
				}else{
					$celketa222 = 10;
					$celketb222 = 12;
				}

				$si->mergeCells($icell[$celketa222].$baris.':'.$icell[$celketb222].$baris);
				$si->setCellValue($icell[$celketa222].$baris,'(...................................)');
				$si->setSharedStyle($coltengah, $icell[0].$baris.':'.$icell[12].$baris);
				
			} 
			$baris++;

			$ndata++;
		}


		
		$si->setTitle('Sheet1');
		$oexcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		if($_GET["tipe"] == "xlsx") {
			header('Content-Disposition: attachment;filename=Laporan_Karegu_Press.xlsx');
		} else if($_GET["tipe"] == "xls") {
			header('Content-Disposition: attachment;filename=Laporan_Karegu_Press.xls');
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
	$qpdh_id = $_POST['qpdh_id'];
	$sql = "SELECT * from qc_pd_hsl_header where qpdh_id = '{$qpdh_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$datetime = explode(' ',$rh[qpdh_date]);
	$rh[tgl] = cgx_dmy2ymd($datetime[0]);
	$rh[jam] = substr($datetime[1],0,5);
			
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[qpdh_id].'</td><td>Di-input Oleh : '.$rh[qpdh_user_create].'</td></tr><tr><td>Subplant : '.$rh[qpdh_sub_plant].'</td><td>Tanggal Input : '.$rh[qpdh_date_create].'</td></tr><tr><td>Tanggal : '.$rh[tgl].'</td><td>Di-edit Oleh : '.$rh[qpdh_user_modify].'</td></tr><tr><td>Jam : '.$rh[jam].'</td><td>Tanggal Edit : '.$rh[qpdh_date_modify].'</td></tr></table>';
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