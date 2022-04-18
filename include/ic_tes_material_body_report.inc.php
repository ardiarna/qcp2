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

function excel() {
	header("Content-type: application/x-msexcel"); 
	header('Content-Disposition: attachment; filename="HASIL_TEST_MATERIAL_BODY.xls"');
	echo urai(true);
}

function urai($excel = false){
	if($excel){
		$border = "border='1'";
	}else{
		$border = "";
	}

	global $app_plan_id, $nama_plan;
	$subplan  = $_GET['subplan'];
	$tanggal  = explode('@', $_GET['tanggal']);
	$tglfrom  = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto 	  = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.ic_sub_plant = '".$subplan."'";
	}

	$sql = "SELECT a.ic_sub_plant, a.ic_id, a.ic_date, c.ic_kd_material, c.ic_nm_material, d.subkon_name, 
				   b.icd_group, e.pm_groupname, b.icd_seq, e.pm_desc, e.pm_std, e.pm_sat, b.icd_value
			FROM qc_ic_mb_header a 
			LEFT JOIN qc_ic_mb_detail b ON a.ic_id = b.ic_id
			LEFT JOIN qc_ic_kebasahan_data c ON a.ic_idmasuk = c.ic_id
			LEFT JOIN qc_md_subkon d ON c.ic_sub_kontraktor = d.subkon_name
			LEFT JOIN qc_ic_parameter e ON b.icd_group = e.pm_groupid AND b.icd_seq = e.pm_seq
			WHERE a.ic_rec_stat = 'N' AND a.ic_date >= '{$tglfrom}' and a.ic_date <= '{$tglto}'
			ORDER BY a.ic_sub_plant, a.ic_date, b.icd_group, e.pm_urut ASC";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)){
		foreach($qry as $r){

			$datetime = explode(' ',$r[ic_date]);
			$r[tgl] = cgx_dmy2ymd($datetime[0]);
			$r[jam] = substr($datetime[1],0,5);

			$arr_nilai["$r[ic_sub_plant]"]["$r[ic_kd_material]"]["$r[tgl]"]["$r[ic_id]"]["$r[icd_group]"]["$r[icd_seq]"] = $r[icd_value]; 
			$arr_mat["$r[ic_kd_material]"] = $r[ic_nm_material];	
			$arr_seq["$r[icd_group]"]["$r[icd_seq]"] = $r[pm_desc].'@@'.$r[pm_std].'@@'.$r[pm_sat];
			$arr_grup["$r[icd_group]"] = $r[pm_groupname];		
		}
	}

	if(is_array($arr_nilai)) {
		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;}table.adaborder th,table.adaborder td{border:1px solid black;} .str{ mso-number-format:\@; } </style>';
		$html .= '
				  <div style="text-align:center;font-size:20px;font-weight:bold;">HASIL TEST MATERIAL BODY</div>
				  <div style="text-align:center;font-size:14px;font-weight:bold;">TGL : '.$tgljudul.'</div><br>';
		
		foreach ($arr_nilai as $subplan => $a_item) {

			foreach ($a_item as $kditem => $a_tgl) {
				$jmlcol = 0;
				foreach ($a_tgl as $tgl => $a_id) {
					$jmlcol += count($a_id);
				}

				$html .= '<div style="overflow-x:auto;"><table class="adaborder" id="tbl01" '.$border.'>';
				$html .= '<tr><th colspan="'.($jmlcol+4).'" style="background: #D1D1D1;text-align:left;">SUBPLANT : '.$subplan.' | '.$kditem.' - '.$arr_mat[$kditem].'</th></tr>';
				$html .= '<tr>
							<th style="width:50px;min-width:50px;">NO</th>
							<th style="width:150px;min-width:150px;">DESKRIPSI</th>
							<th style="width:150px;min-width:150px;">STD</th>
							<th style="width:70px;min-width:70px;">SATUAN</th>';		
				foreach ($a_tgl as $tgl => $a_id) {
					$jml = count($a_id);
					$html .= '<th width="100px;" colspan="'.($jml).'">'.$tgl.'</th>';
				}			
				$html .= '</tr>';

				$ng = 'A';
				foreach ($arr_seq as $group => $a_idseq) {
					$groupval = $arr_grup[$group];	

					$html .= '<tr>
						    	<th style="background: #D1D1D1;">'.$ng.'</th>
						    	<th style="text-align:left;">'.$groupval.'</th>
						    	<th style="text-align:left;">&nbsp;</th>
						    	<th style="text-align:left;">&nbsp;</th>';
					for ($i=1; $i<=$jmlcol; $i++) { 
					 	$html .='<th>&nbsp;</th>';
					} 
					$html .= '</tr>';

					$no = 1;
					foreach ($a_idseq as $seq => $itemval) {
						$val = explode('@@', $itemval);
						$html .= '<tr>
							    	<td align="center">'.$no.'</td>    
							    	<td nowrap>'.$val[0].'</td>    
							    	<td align="center" nowrap>'.$val[1].'</td>    
							    	<td align="center">'.$val[2].'</td>';
						
						foreach ($a_tgl as $tgl => $a_id) {
							foreach ($a_id as $id => $nilaival) {
							$isi = $nilaival[$group][$seq];

								$html .= '<td align="center"><span onclick="lihatData(\''.$id.'\')">'.$isi.'</span></td>';
							}
						}
						$html .='</tr>';
					$no++;
					}
				$ng++;
				}

				$html .='</table></div><br>';
				
			}
				

				
		}
	} else {
		$html = 'TIDAKADA';
	}
	if($excel){
		return $html;
	}else{
		$responce->detailtabel = $html; 
		$responce->sql = $sql; 
		echo json_encode($responce);
	}
}


function lihatdata(){
	global $app_plan_id;
	$kode = $_POST['kode'];
	$sql = "SELECT * FROM qc_ic_mb_header WHERE ic_id = '{$kode}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$datetime = explode(' ',$rh[ic_date]);
	$rh[tgl] = cgx_dmy2ymd($datetime[0]);

	$out = '<table class="table table-striped table-bordered table-condensed">
				<tr>
					<td width="50%">ID : '.$rh[ic_id].'</td>
					<td width="50%">Subplant : '.$rh[ic_sub_plant].'</td>
				</tr>
				<tr>
					<td>Tanggal : '.$rh[tgl].'</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>User Input : '.$rh[ic_user_create].'</td>
					<td>User Edit : '.$rh[ic_user_modify].'</td>
				</tr>
				<tr>
					<td>tanggal Input : '.$rh[ic_date_create].'</td>
					<td>tanggal Edit : '.$rh[ic_date_modify].'</td>
				</tr>
			</table>';
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