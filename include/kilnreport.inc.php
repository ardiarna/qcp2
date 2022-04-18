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
	case "cboklin":
		cboklin($_POST['subplan']);
		break;
}


function cboklin($subplan) {
	global $app_plan_id;
	$sql  = "SELECT * FROM qc_kiln_mesin WHERE sub_plant = '{$subplan}' ORDER BY id_kiln ASC";
	$qry  = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option value='All'>All</option>";
	foreach($qry as $r){
		$out .= "<option value='{$r[id_kiln]}'>$r[desc_kiln]</option>";
	}

	if($irrest){
		return $out;
	}else{
		echo $out;
	}
	
}


function jamshift($shift){
	if($shift == '1'){
		$qryjam = array("08","09","10","11","12","13","14","15");	
	}else if($shift == '2'){
		$qryjam = array("16","17","18","19","20","21","22","23");	
	}else{
		$qryjam = array("24","01","02","03","04","05","06","07");
	} 	

	return $qryjam;
}

function urai(){
	global $app_plan_id;
	$subplan = $_GET['subplan'];
	$id_kiln = $_GET['id_kiln'];


	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];


	if($subplan <> 'All') {
		$whdua .= "and kl_sub_plant = '".$subplan."'";
	}

	if($id_kiln <> 'All') {
		$whdua .= " and a.id_kiln = '".$id_kiln."'";
	}

	$sql = "SELECT distinct kl_sub_plant, kl_date, a.id_kiln, b.desc_kiln 
			FROM qc_kiln_header a left join qc_kiln_mesin b ON a.kl_sub_plant = b.sub_plant and a.id_kiln = b.id_kiln 
			WHERE kl_status='N' and kl_date >= '{$tglfrom}' and kl_date <= '{$tglto}' $whdua order by kl_sub_plant, kl_date, id_kiln";
	$responce->sqla = $sql; 
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)) {
		foreach($qry as $r){
			$datetime = explode(' ',$r[kl_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			$arr_plan["$r[kl_sub_plant]"]["$r[tgl]"]["$r[id_kiln]"] = $r[desc_kiln];
		}
	}


	if(is_array($arr_plan)) {
			$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
			$html .= '<div style="text-align:right;font-size:14px;font-weight:bold;">NO. F.901.PP.21</div>';
		foreach ($arr_plan as $plan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_kiln) {
				$html .= '<div style="text-align:left;font-size:16px;font-weight:bold;">SUBPLAN : '.$plan.' | '.$tgl.' </div>';
				foreach ($a_kiln as $kiln => $kilnVal) {

					$html .= '<div style="text-align:center;font-size:16px;font-weight:bold;">DATA TEMPERATUR KILN '.strtoupper($kilnVal).'</div>';

					$sql1 = "SELECT a.kl_id, a.kl_time, a.kl_speed, a.kl_code, a.kl_presure, b.kl_id, b.kl_group, b.kld_id, b.kl_d_value, c.kl_desc, d.kld_desc
							 FROM qc_kiln_header a join qc.qc_kiln_detail b on a.kl_id = b.kl_id 
							 join qc_kiln_group c on b.kl_group = c.kl_group 
							 join (SELECT * FROM qc_kiln_group_detail WHERE sub_plant = '{$plan}') AS d on b.kl_group = d.kl_group and b.kld_id = d.kld_id
							 WHERE a.kl_status = 'N' AND a.kl_sub_plant = '{$plan}' AND to_char(a.kl_date, 'DD-MM-YYYY') = '{$tgl}' AND a.id_kiln = '{$kiln}'";
					$qry1 = dbselect_plan_all($app_plan_id, $sql1);
					if(is_array($qry1)) {
						foreach($qry1 as $r1){
							$datetime2 = explode(' ',$r1[kl_time]);
							$r1[tgl] = cgx_dmy2ymd($datetime2[0]);
							$r1[jam] = substr($datetime2[1],0,2);
							$arr_nilai["$r1[kl_group]"]["$r1[kld_id]"]["$r1[jam]"]["$r1[kl_id]"] = $r1[kl_d_value];
							$arr_grup["$r1[kl_group]"] = $r1[kl_desc];
							$arr_item["$r1[kl_group]"]["$r1[kld_id]"] = $r1[kld_desc];
						}
					}

						$html .='<div class="table-responsive">
								  <table  class="adaborder">
									<tr>
									  	<th colspan="2" rowspan="2">NO. MODUL</th>';
													

										$jmlshift = 3;
										$jmlcoljam = 0;
										for($i=1;$i<=$jmlshift;$i++){
											$jmlcoljam = count(jamshift($i));
											$html .=' <th colspan="'.$jmlcoljam.'">SHIFT '.Romawi($i).'</th>';
										}
						$html .='</tr>';	
						$html .='<tr>';	
						for($i=1;$i<=$jmlshift;$i++){
							$arrayjam = jamshift($i);

							foreach($arrayjam as $r) {
								$html .='<th>'.$r.':00</th>';
							}
						}
						$html .='</tr>';	

						foreach ($arr_nilai as $grup => $a_grup) {

							$jmlrowspan = count($arr_item[$grup])+1;
							$html .='<tr>';
							$html .='<th rowspan="'.$jmlrowspan.'">'.$arr_grup[$grup].'</th>';
							$html .='</tr>';

							foreach ($a_grup as $item => $a_item) {
								$html .='<tr>';
								$html .='<th>'.$arr_item[$grup][$item].'</th>';

								for($i=1;$i<=$jmlshift;$i++){
									$arrayjam = jamshift($i);

									foreach($arrayjam as $r) {
										if(is_array($a_item[$r])) {
											foreach ($a_item[$r] as $kl_id => $nilai) {
												$htmlVall = '<span onclick="lihatData(\''.$kl_id.'\')">'.$nilai.'</span>';	
											}
										} else {
											$htmlVall = '&nbsp;';
										}	

										$html .= '<td align="center">'.$htmlVall.'</td>';
									}
								}
								
								$html .='</tr>';


							}	

							$html .='<tr>';
							$html .='<th colspan="26">&nbsp;</th>';
							$html .='</tr>';
						}	


						$html .='<tr>';
						for($i=1;$i<=$jmlshift;$i++){
							if($i == 1){
								$wjam = "AND to_char(kl_time, 'HH:MI') IN ('08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00') ";
								$colpan3 = "10";
							}else if($i == 2){
								$wjam = "AND to_char(kl_time, 'HH:MI') IN ('16:00','17:00','18:00','19:00','20:00','21:00','22:00','23:00') ";
								$colpan3 = "8";
							}else{
								$wjam = "AND to_char(kl_time, 'HH:MI') IN ('24:00','01:00','02:00','03:00','04:00','05:00','06:00','07:00') ";
								$colpan3 = "8";
							} 

							$sql21 = "SELECT distinct kl_speed, kl_code, kl_presure, kl_id, kl_time, kl_id FROM qc_kiln_header
								 	  WHERE kl_status = 'N' AND kl_sub_plant = '{$plan}' AND to_char(kl_date, 'DD-MM-YYYY') = '{$tgl}' AND id_kiln = '{$kiln}'
								 	  $wjam order by kl_time desc limit 1 ";
							$qry21 = dbselect_plan($app_plan_id, $sql21);

							$html .='<td colspan="'.$colpan3.'" height="80px">';
								$html .='<span class="jdlbwh" style="margin-left:15px;">Speed</span>';
								$html .='<span class="jdlbwh"> : </span>';
								$html .='<span class="jdlbwh" onclick="lihatData(\''.$qry21[kl_id].'\')">'.$qry21[kl_speed].'</span>';

								$html .='<br>';

								$html .='<span class="jdlbwh" style="margin-left:15px;">Code</span>';
								$html .='<span class="jdlbwh"> : </span>';
								$html .='<span class="jdlbwh" onclick="lihatData(\''.$qry21[kl_id].'\')">'.$qry21[kl_code].'</span>';

								$html .='<br>';

								$html .='<span class="jdlbwh" style="margin-left:15px;">Presure</span>';
								$html .='<span class="jdlbwh"> : </span>';
								$html .='<span class="jdlbwh" onclick="lihatData(\''.$qry21[kl_id].'\')">'.$qry21[kl_presure].'</span>';

								$html .='<br>';
								$html .='<br>';
								$html .='<br>';
								$html .='<br>';
								$html .='<br>';

								$html .='<span class="jdlbwh" style="margin-left:15px;">-----------------</span>';
								$html .='<br>';
								$html .='<span class="jdlbwh" style="margin-left:15px;">Kepala Regu</span>';

							$html .='</td>';
						}



						$html .='</table></div>';
						$html .='<br><br>';

				}
			}
		}

	} else {
		$html = 'TIDAKADA';
	}
	$responce->detailtabel = $html; 
	echo json_encode($responce);
}

function excel() {
	$html = 'Belum tersedia...';
	echo $html;
}

function lihatdata(){
	global $app_plan_id;
	$kl_id = $_POST['kl_id'];
	$sql = "SELECT * from qc_kiln_header where kl_id = '{$kl_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$datetime = explode(' ',$rh[kl_date]);
	$rh[tgl] = cgx_dmy2ymd($datetime[0]);

	$datetime2 = explode(' ',$rh[kl_time]);
	$rh[jam] = substr($datetime2[1],0,5);	
			
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[kl_id].'</td><td>Di-input Oleh : '.$rh[kl_user_create].'</td></tr><tr><td>Subplant : '.$rh[kl_sub_plant].'</td><td>Tanggal Input : '.$rh[kl_date_create].'</td></tr><tr><td>Tanggal : '.$rh[tgl].'</td><td>Di-edit Oleh : '.$rh[kl_user_modify].'</td></tr><tr><td>Jam : '.$rh[jam].'</td><td>Tanggal Edit : '.$rh[kl_date_modify].'</td></tr></table>';
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