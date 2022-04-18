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



function urai($excel = false){
	global $app_plan_id;
	$subplan = $_GET['subplan'];
	$shift   = $_GET['shift'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= "and a.op_sub_plant = '".$subplan."'";
	}
	if($shift <> 'All') {
		$whdua .= "and a.op_shift = '".$shift."'";
	}
	$sql = "SELECT a.op_id, a.op_sub_plant  as subplan, a.op_date, a.op_shift, a.op_press, a.op_tekanan, a.op_format, a.op_ukuran, b.op_mould, b.op_urut, b.op_value 
			from qc.qc_pd_size_header a left join qc.qc_pd_size_detail b on a.op_id = b.op_id
			where a.op_rec_stat = 'N' AND a.op_date >= '{$tglfrom}' AND a.op_date <= '{$tglto}' $whdua
			order by a.op_sub_plant, a.op_press, a.op_shift, a.op_date asc";
	$responce->sql = $sql; 
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)) {
		foreach($qry as $r){
			$datetime = explode(' ',$r[op_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			$r[jam] = substr($datetime[1],0,5);
			
			$arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[op_shift]"]["$r[jam]"]["$r[op_press]"]["$r[op_mould]"]["$r[op_urut]"]["$r[op_id]"] = $r[op_value];
			$arr_press["$r[op_press]"] = $r[op_press];
			$arr_shift["$r[subplan]"]["$r[tgl]"]["$r[op_press]"]["$r[op_shift]"] = $r[op_shift];
			$arr_mould["$r[op_mould]"] = $r[op_mould];
			$arr_desc["$r[subplan]"]["$r[tgl]"]["$r[op_shift]"]["$r[jam]"]["$r[op_press]"]["$r[op_id]"] = $r[op_tekanan].'@@'.$r[op_format].'@@'.$r[op_ukuran];
		}
	}

	if(is_array($arr_nilai)) {

		$html .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.1002.QC.</div>
				  <div style="text-align:center;font-size:20px;font-weight:bold;">SIZE OUT PRESS</div>
				  <table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';

		$jmlcolmould = count($arr_mould);
		$jmlcolpress = count($arr_press)*$jmlcolmould;
		ksort($arr_press);
		ksort($arr_mould);

		foreach ($arr_nilai as $subplan => $a_tgl) {
		  	foreach ($a_tgl as $tgl => $a_shift) {
		  		$html .= '<div style="overflow-x:auto;"><table class="adaborder" style="width:100%" border=1>';
				$html .= '<tr><th style="text-align:left;border:1px solid black;" colspan='.($jmlcolpress+1).'>SUBPLANT : '.$subplan.' | '.$tgl.'</th></tr>';
				
				$html .= '<tr style="border:1px solid black;">';
				$html .= '<th rowspan="2" >SHIFT</th>';
				foreach ($arr_press as $press) {
		  			$html .= '<th colspan='.$jmlcolmould.'>PRESS '.$press.'</th>';
		  		}	
				$html .= '</tr>';

				$html .= '<tr style="border:1px solid black;">';
				foreach ($arr_press as $press) {
					foreach ($arr_mould as $mould) {
			  			$html .= '<th>CAVITTY '.$mould.'</th>';
			  		}	
				}	
				$html .= '</tr>';

			foreach ($a_shift as $shift => $a_jam) {
				$html .= '<tr style="border:1px solid black;">';
				$html .= '<th style="background: #D1D1D1;">'.Romawi($shift).'</th>';
				foreach ($arr_press as $press) {
		  			$html .= '<th style="background: #D1D1D1;" colspan='.$jmlcolmould.'>&nbsp;</th>';
		  		}
				$html .= '</tr>';



				foreach ($a_jam as $jam => $a_press) {
					$html .= '<tr style="border:1px solid black;">';
					$html .= '<th>&nbsp;</th>';
					foreach ($arr_press as $press) {
						$jamdesc = "";
						$tekakan = "";
						$ukuran = "";
						if(is_array($arr_desc[$subplan][$tgl][$shift][$jam][$press])){
		  					foreach ($arr_desc[$subplan][$tgl][$shift][$jam][$press] as $iddesc  => $desc) {
		  						$descc = explode('@@', $desc);
		  						$jamdesc .= '<span onclick="lihatData(\''.$iddesc.'\')">'.$jam.'</span>';
		  						$tekakan .= '<span onclick="lihatData(\''.$iddesc.'\')">'.$descc[0].'</span>';
		  						$ukuran .= '<span onclick="lihatData(\''.$iddesc.'\')">'.$descc[2].'</span>';

		  						$format  = explode('x', $descc[1]); 
		  						$rows    = cgx_angka($format[0]);
		  						$cols    = cgx_angka($format[1]);
		  					}
		  				}else{
		  					$tekakan .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		  					$jamdesc .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		  				}


			  			$html .= '<th colspan='.$jmlcolmould.' style="text-align:left;">&nbsp; JAM CHECK : '.$jamdesc.' | TEKANAN : '.$tekakan.' | Uk : '.$ukuran.'</th>';
			  		}
					$html .= '</tr>';

					$html .= '<tr style="border:1px solid black;">';
					$html .= '<th>&nbsp;</th>';
					foreach ($arr_press as $press) {
						foreach ($arr_mould as $mould) {
				  			$html .= '<th>';
				  				$urutm =1;
				  				$html .= '<table align="center" style="width:95%;margin-top:3px;margin-bottom:3px;" border=1>';
								for ($row=1; $row <= $rows; $row++) { 
									$html .= '<tr style="border:1px solid black;">';
									for ($col=1; $col <= $cols; $col++) { 
									   	$p = $col * $row;
									   	$k_new = $k+1;
									   	$val = "";
									   	if(is_array($a_press[$press][$mould][$urutm])){
										   	foreach ($a_press[$press][$mould][$urutm] as $idd => $isi) {
										   		if($isi == ""){
										   			$val .= "&nbsp;&nbsp;";
										   		}else{
										   			$val .= $isi;
										   		}
										   	}
										}else{
											$val.= '&nbsp;&nbsp;';
										}

									   	$html .= '<td><span onclick="lihatData(\''.$idd.'\')">'.$val.'</span></td>';

									   	 $urutm++;
								   	}
								    $html .= '</tr>';
								}
								$html .= '</table>';

				  			$html  .= '</th>';
				  		}	
					}	
					$html .= '</tr>';


					
				}

			}

				$html .='</table></div>';
				$html .='<br>';
				$html .='<br>';
			}
		}


	} else {
		$html = 'TIDAKADA';
	}
	

	if($excel){
		return $html;
	}else{
		$responce->detailtabel = $html; 
		echo json_encode($responce);
	}
}


function excel() {
	header("Content-type: application/x-msexcel"); 
	header('Content-Disposition: attachment; filename="Size_out_press.xls"');
	echo urai(true);
}

function lihatdata(){
	global $app_plan_id;
	$op_id = $_POST['op_id'];
	$sql = "SELECT * from qc_pd_size_header where op_id = '{$op_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$datetime = explode(' ',$rh[op_date]);
	$rh[tgl] = cgx_dmy2ymd($datetime[0]);
	$rh[jam] = substr($datetime[1],0,5);
			
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[op_id].'</td><td>Di-input Oleh : '.$rh[op_user_create].'</td></tr><tr><td>Subplant : '.$rh[op_sub_plant].'</td><td>Tanggal Input : '.$rh[op_date_create].'</td></tr><tr><td>Tanggal : '.$rh[tgl].'</td><td>Di-edit Oleh : '.$rh[op_user_modify].'</td></tr><tr><td>Jam : '.$rh[jam].'</td><td>Tanggal Edit : '.$rh[op_date_modify].'</td></tr></table>';
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