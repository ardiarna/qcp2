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
	$arr_kolom = array("08" => "08", "11" => "11", "14" => "14", "17" => "17", "20" => "20", "22" => "22", "24" => "24", "03" => "03", "06" => "06");
	$subplan = $_GET['subplan'];
	$tglfrom = cgx_dmy2ymd($_GET['tanggal'])." 00:00:00";
	$tgljudul  = $_GET['tanggal'];
	$cmh_press = $_GET['cmh_press'];

	$subplan = $_GET['subplan'];
	$cmh_press = $_GET['cmh_press'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
	    $whdua .= "and a.cmh_sub_plant = '".$subplan."'";
	}

	if($cmh_press <> 'All') {
	    $whdua .= "and a.cmh_press = '".$cmh_press."'";
	}

	$sql = "SELECT a.cmh_sub_plant as subplan, a.cmh_date, a.cmh_press, 
			       b.cm_group, c.cm_desc, b.cd1_id, d.cd1_desc, b.cd2_id, e.cd2_desc, b.cmd_value, a.cmh_id 
			from qc.qc_pd_cm_header a 
			left join qc.qc_pd_cm_detail b on a.cmh_id = b.cmh_id
			left join qc.qc_pd_cm_group c on b.cm_group = c.cm_group
			left join qc.qc_pd_cm_group_d1 d on b.cm_group = d.cm_group and b.cd1_id = d.cd1_id
			left join qc.qc_pd_cm_group_d2 e on b.cm_group = d.cm_group and b.cd1_id = e.cd1_id and b.cd2_id = e.cd2_id
			where a.cmh_status = 'N' $whdua and a.cmh_date >= '{$tglfrom}' and a.cmh_date <= '{$tglto}'
			ORDER BY a.cmh_sub_plant, to_char(a.cmh_date, 'DD-MM-YYYY'), CAST(a.cmh_press AS int), CAST(b.cm_group AS int), CAST(b.cd1_id AS int), CAST(b.cd2_id AS int), a.cmh_id ASC";
	$responce->sql = $sql; 
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)) {
	    foreach($qry as $r){
	        $datetime = explode(' ',$r[cmh_date]);
	        $r[tgl] = cgx_dmy2ymd($datetime[0]);
	        $r[jam] = substr($datetime[1],0,2);
	        

	        $arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[cmh_press]"]["$r[cm_group]"]["$r[cd1_id]"]["$r[cd2_id]"]["$r[jam]"]["$r[cmh_id]"] = $r[cmd_value];
	        $arr_group["$r[cm_group]"] = $r[cm_desc];
	        $arr_gd1["$r[cm_group]"]["$r[cd1_id]"] = $r[cd1_desc];
	        $arr_gd2["$r[cm_group]"]["$r[cd1_id]"]["$r[cd2_id]"] = $r[cd2_desc];
	       
	    }
	}


	if(is_array($arr_nilai)) {
		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
		$html .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.901.00.12</div>
				  <div style="text-align:center;font-size:20px;font-weight:bold;">LAPORAN PENGONTROLAN FUNGSI MESIN</div>
				  <table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
		foreach ($arr_nilai as $subplan => $a_tgl) {
			foreach ($a_tgl as $tgl => $a_press) {
				foreach ($a_press as $press => $a_group) {
					$html .= '<div style="overflow-x:auto;"><table class="adaborder">';
					
					$html .= '<tr>';
					$html .= '<th class="text-left">SUB PLANT</th><th class="text-left" colspan="4" >: '.$subplan.'</th>';
					$html .= '<th class="text-left" colspan="2">Operator</th><th class="text-left" colspan="3" >: Shift I : ..........</th>';
					$html .= '</tr>';

					$html .= '<tr>';
					$html .= '<th class="text-left">MESIN PRESS NOMOR</th><th class="text-left" colspan="4" >: '.$press.'</th>';
					$html .= '<th class="text-left" colspan="2">&nbsp;</th><th class="text-left" colspan="3" >: Shift II : ..........</th>';
					$html .= '</tr>';

					$html .= '<tr>';
					$html .= '<th class="text-left">TANGGAL</th><th class="text-left" colspan="4" >: '.$tgl.'</th>';
					$html .= '<th class="text-left" colspan="2">&nbsp;</th><th class="text-left" colspan="3" >: Shift II : ..........</th>';
					$html .= '</tr>';


					$html .= '<tr><th>JAM PEMERIKSAAN</th>';
					foreach ($arr_kolom as $kolom => $kolom_nama) {
						$html .= '<th>'.$kolom.':00</th>';
					}
					$html .= '</tr>';
					$html .= '<tr><th colspan="10" style="font-size:16px;">PENGONTROLAN FUNGSI MESIN</th></tr>';
					foreach ($a_group as $group => $a_gd1) {
						
						$html .= '<tr><th colspan="10" style="font-size:16px;"><u>'.$arr_group[$group].'</u></th></tr>';

						$no_d1 = 'A';
						foreach ($a_gd1 as $gd1 => $a_gd2) {
							$html .= '<tr><th colspan="10" style="font-size:16px;"><u>'.$no_d1.'. '.$arr_gd1[$group][$gd1].'</u></th></tr>';
						$no_d1++;

							foreach ($a_gd2 as $gd2 => $a_jam) {
								$html .= '<tr>';
								$html .= '<td colspan="1">'.$arr_gd2[$group][$gd1][$gd2].'</td>';
								foreach ($arr_kolom as $kolom => $kolom_nama){
									if($kolom == '24'){
										$kolomVal = '00';
									}else{
										$kolomVal = $kolom;
									}

									$html .= '<td class="text-center">';

									if(is_array($a_jam[$kolomVal])) {
										foreach ($a_jam[$kolomVal] as $cmh_idd => $cmh_iddVal) {

											$html .= '<span onclick="lihatData(\''.$cmh_idd.'\')">';

											if($cmh_iddVal == 'Y'){
												$html .= '<i class="fa fa-check"></i></span> ';
											}else if($cmh_iddVal == 'N'){
												$html .= '<i class="fa fa-remove"></i></span> ';
											}else{
												$html .= $cmh_iddVal.' ';
											}

		                                    $html .= '</span> ';
			                            }
									}else{
										$html .= '&nbsp;';
									}

									$html .= '</td>';
								}
								$html .= '</tr>';
							}
						}
					}
					$html .='</table></div>';

					$html .= '<br>';
					$html .= '<br>';
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
	$html = 'Dalam Maintenance';
	echo $html;
}

function lihatdata(){
	global $app_plan_id;
	$cmh_id = $_POST['cmh_id'];
	$sql = "SELECT * from qc_pd_cm_header where cmh_id = '{$cmh_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$datetime = explode(' ',$rh[cmh_date]);
	$rh[tgl] = cgx_dmy2ymd($datetime[0]);
	$rh[jam] = substr($datetime[1],0,5);
			
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[cmh_id].'</td><td>Di-input Oleh : '.$rh[cmh_user_create].'</td></tr><tr><td>Subplant : '.$rh[cmh_sub_plant].'</td><td>Tanggal Input : '.$rh[cmh_date_create].'</td></tr><tr><td>Tanggal : '.$rh[tgl].'</td><td>Di-edit Oleh : '.$rh[cmh_user_modify].'</td></tr><tr><td>Jam : '.$rh[jam].'</td><td>Tanggal Edit : '.$rh[cmh_date_modify].'</td></tr></table>';
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