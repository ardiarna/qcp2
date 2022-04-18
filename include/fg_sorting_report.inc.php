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
	$subplan  = $_GET['subplan'];
	$sp_shift = $_GET['sp_shift'];
	$sp_line  = $_GET['sp_line'];
	$tanggal  = explode('@', $_GET['tanggal']);
	$tglfrom  = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto    = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
	$whdua = "";
	if($subplan <> 'All') {
	    $whdua .= "and sp_sub_plant = '".$subplan."'";
	}

	if($sp_shift <> 'All') {
	    $whdua .= "and sp_shift = '".$sp_shift."'";
	}

	if($sp_line <> 'All') {
	    $whdua .= "and sp_line = '".$sp_line."'";
	}

	$sql = "SELECT a.sp_id, sp_sub_plant as subplan, sp_date, sp_line, sp_shift, 
	    b.export, b.ekonomi, b.reject, b.rijek_palet, b.rijek_buang
	    FROM qc_fg_sorting_header a LEFT JOIN qc_fg_sorting_detail b ON a.sp_id = b.sp_id
	    WHERE sp_status = 'N' $whdua 
	    AND sp_date >= '{$tglfrom}' AND sp_date <= '{$tglto}'
	    ORDER BY sp_sub_plant, sp_date, sp_line, sp_shift, a.sp_id ASC";
	$responce->sql = $sql; 
	$qry = dbselect_plan_all($app_plan_id, $sql);

	$arr_export = array();
	$arr_ekonomi = array();
	$arr_reject = array();
	$arr_rijek_palet = array();
	$arr_rijek_buang = array();
	if(is_array($qry)) {
	    foreach($qry as $r){
	        $datetime = explode(' ',$r[sp_date]);
	        $r[tgl] = cgx_dmy2ymd($datetime[0]);

	        $arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[sp_line]"]["$r[sp_shift]"]["$r[sp_id]"] = $r[sp_id];
	        $arr_export["$r[subplan]"]["$r[tgl]"]["$r[sp_line]"]  += $r[export];
	        $arr_ekonomi["$r[subplan]"]["$r[tgl]"]["$r[sp_line]"] += $r[ekonomi];
	        $arr_reject["$r[subplan]"]["$r[tgl]"]["$r[sp_line]"]  += $r[reject];
	        $arr_rijek_palet["$r[subplan]"]["$r[tgl]"]["$r[sp_line]"]  += $r[rijek_palet];
	        $arr_rijek_buang["$r[subplan]"]["$r[tgl]"]["$r[sp_line]"]  += $r[rijek_buang];
	    }
	}


	if(is_array($arr_nilai)) {

	    $html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
	    $html .= '<div style="text-align:center;font-size:20px;font-weight:bold;"><u>LAPORAN HARIAN SORTING PACKING</u></div>';
	    $html .= '<table align="center"><tr><td>TANGGAL : '.$tgljudul.'</td></tr></table>';
	    
	    foreach ($arr_nilai as $subplan => $a_tgl) {
	        foreach ($a_tgl as $tgl => $a_line) {
	            $Grandsumexport    = 0;
	            $Grandsumekonomi   = 0;
	            $Grandsumreject    = 0;
	            $Grandrightttl_all = 0;

	            foreach ($a_line as $line => $a_shift) {

	                $html .= '<div style="overflow-x:auto;"><table class="adaborder">';
	                $html .= '<tr><th colspan="15" style="text-align:left;">SUBPLANT : '.$subplan.' | '.$tgl.'</th></tr>';
	                $html .= '<tr><th colspan="15" style="text-align:left;">LINE : '.$line.'</th></tr>';

	                $html .= '<tr>
	                            <th style="vertical-align:middle;width:60px;" rowspan="2">SHIFT</th>
	                            <th style="vertical-align:middle;width:120px;" rowspan="2">CODE</th>
	                            <th style="vertical-align:middle;width:80px;" rowspan="2">SIZE</th>
	                            <th colspan="10">HASIL OUTPUT SORTIR</th>';
	                $html .= '<th style="vertical-align:middle;width:70px;" rowspan="2">TOTAL <br> ( M2 )</th>
	                         <th style="vertical-align:middle;" rowspan="2">KETERANGAN</th>
	                         </tr>';
	                $html .= '<tr>
	                            <th style="width:70px;">EXPORT <br> ( M2 )</th>
	                            <th style="vertical-align:middle;width:50px;">%</th>
	                            <th style="width:70px;">EKONOMI <br> ( M2 )</th>
	                            <th style="vertical-align:middle;width:50px;">%</th>
	                            <th style="width:75px;">REJECT <br> SORTIR ( M2 )</th>
	                            <th style="vertical-align:middle;width:50px;">%</th>
	                            <th style="width:70px;">REJECT <br> PALET ( M2 )</th>
	                            <th style="vertical-align:middle;width:50px;">%</th>
	                            <th style="width:70px;">REJECT <br> BUANG ( M2 )</th>
	                            <th style="vertical-align:middle;width:50px;">%</th>
	                        </tr>';

	                foreach ($a_shift as $shift => $a_id) {
	                    $html .= '<tr>';        
	                    $html .= '<th style="text-align:center;">'.Romawi($shift).'</th>';  
	                    $html .= '<td colspan="14"></td>';
	                    $html .= '</tr>';       

	                    foreach ($a_id as $idd => $id) {
	                        $sql2 = "SELECT * FROM qc_fg_sorting_detail WHERE sp_id = '{$id}' ORDER BY code ASC";
	                        $responce->sql = $sql2; 
	                        $qry2 = dbselect_plan_all($app_plan_id, $sql2);
	                        if(is_array($qry2)) {
	                            $sumexport  = 0;
	                            $sumekonomi = 0;
	                            $sumreject  = 0;
	                            $sumrijek_palet = 0;
	                            $sumrijek_buang = 0;
	                            $sql3 = "SELECT sum(export) as sumexport, sum(ekonomi) as sumekonomi, sum(reject) as sumreject, sum(rijek_palet) as sumrijek_palet, sum(rijek_buang) as sumrijek_buang FROM qc_fg_sorting_detail WHERE sp_id = '{$id}'";
	                            $responce->sql = $sql3; 
	                            $qry3 = dbselect_plan($app_plan_id, $sql3);

	                            $sumexport  = $qry3[sumexport];
	                            $sumekonomi = $qry3[sumekonomi];
	                            $sumreject  = $qry3[sumreject];
	                            $sumrijek_palet  = $qry3[sumrijek_palet];
	                            $sumrijek_buang  = $qry3[sumrijek_buang];
	                            foreach($qry2 as $r2) {

	                                $rightttl = $r2[export]+$r2[ekonomi]+$r2[reject]+$r2[rijek_palet]+$r2[rijek_buang]; 

	                                $r2[exportpersen] = ($r2[export]/$rightttl)*100;
	                                if($r2[exportpersen] == 100){
	                                    $exportpersen = 100;
	                                }else{
	                                    $exportpersen = number_format($r2[exportpersen],2); 
	                                }

	                                $r2[ekonomipersen] = ($r2[ekonomi]/$rightttl)*100;
	                                if($r2[ekonomipersen] == 100){
	                                    $ekonomipersen = 100;
	                                }else{
	                                    $ekonomipersen = number_format($r2[ekonomipersen],2);   
	                                }

	                                $r2[rejectpersen] = ($r2[reject]/$rightttl)*100;
	                                if($r2[rejectpersen] == 100){
	                                    $rejectpersen = 100;
	                                }else{
	                                    $rejectpersen = number_format($r2[rejectpersen],2); 
	                                }

	                                $r2[rijek_paletpersen] = ($r2[rijek_palet]/$rightttl)*100;
	                                if($r2[rijek_paletpersen] == 100){
	                                    $rijek_paletpersen = 100;
	                                }else{
	                                    $rijek_paletpersen = number_format($r2[rijek_paletpersen],2);   
	                                }

	                                $r2[rijek_buangpersen] = ($r2[rijek_buang]/$rightttl)*100;
	                                if($r2[rijek_buangpersen] == 100){
	                                    $rijek_buangpersen = 100;
	                                }else{
	                                    $rijek_buangpersen = number_format($r2[rijek_buangpersen],2);   
	                                }

	                                $html .= '<tr>';
	                                $html .= '<td>&nbsp;</td>'; 
	                                $html .= '<td style="text-align:center;"><span onclick="lihatData(\''.$idd.'\')">'.$r2[code].'</span></td>';    
	                                $html .= '<td style="text-align:center;"><span onclick="lihatData(\''.$idd.'\')">'.$r2[size].'</span></td>';    
	                                $html .= '<td style="text-align:right;"><span onclick="lihatData(\''.$idd.'\')">'.number_format($r2[export]).'</span></td>';   
	                                $html .= '<td style="text-align:right;">'.$exportpersen.'</td>';    
	                                $html .= '<td style="text-align:right;"><span onclick="lihatData(\''.$idd.'\')">'.number_format($r2[ekonomi]).'</span></td>';  
	                                $html .= '<td style="text-align:right;">'.$ekonomipersen.'</td>';
	                                $html .= '<td style="text-align:right;"><span onclick="lihatData(\''.$idd.'\')">'.number_format($r2[reject]).'</span></td>';   
	                                $html .= '<td style="text-align:right;">'.$rejectpersen.'</td>';
	                                $html .= '<td style="text-align:right;"><span onclick="lihatData(\''.$idd.'\')">'.number_format($r2[rijek_palet]).'</span></td>';  
	                                $html .= '<td style="text-align:right;">'.$rijek_paletpersen.'</td>';
	                                $html .= '<td style="text-align:right;"><span onclick="lihatData(\''.$idd.'\')">'.number_format($r2[rijek_buang]).'</span></td>';  
	                                $html .= '<td style="text-align:right;">'.$rijek_buangpersen.'</td>';
	                                $html .= '<td style="text-align:right;"><b>'.number_format($rightttl).'</b></td>';
	                                $html .= '<td><span onclick="lihatData(\''.$idd.'\')">'.$r2[keterangan].'</span></td>';
	                                $html .= '</tr>';
	                            }

	                            $rightttl_all = $sumexport+$sumekonomi+$sumreject+$sumrijek_palet+$sumrijek_buang;

	                            $html .= '<tr>';
	                            $html .= '<th>TOTAL</th>';  
	                            $html .= '<td>&nbsp;</td>'; 
	                            $html .= '<td>&nbsp;</td>'; 
	                            $html .= '<td style="text-align:right;"><b>'.number_format($sumexport).'</b></td>';    
	                            $html .= '<td>&nbsp;</td>';     
	                            $html .= '<td style="text-align:right;"><b>'.number_format($sumekonomi).'</b></td>';
	                            $html .= '<td>&nbsp;</td>'; 
	                            $html .= '<td style="text-align:right;"><b>'.number_format($sumreject).'</b></td>';
	                            $html .= '<td>&nbsp;</td>'; 
	                            $html .= '<td style="text-align:right;"><b>'.number_format($sumrijek_palet).'</b></td>';
	                            $html .= '<td>&nbsp;</td>'; 
	                            $html .= '<td style="text-align:right;"><b>'.number_format($sumrijek_buang).'</b></td>';
	                            $html .= '<td>&nbsp;</td>'; 
	                            $html .= '<td style="text-align:right;"><b>'.number_format($rightttl_all).'</b></td>'; 
	                            $html .= '<td>&nbsp;</td>'; 
	                            $html .= '</tr>';
	                        }
	                    }       
	                }

	                $Grandsumexport  = $arr_export[$subplan][$tgl][$line];
	                $Grandsumekonomi = $arr_ekonomi[$subplan][$tgl][$line];
	                $Grandsumreject  = $arr_reject[$subplan][$tgl][$line];
	                $Grandsumrijek_palet  = $arr_rijek_palet[$subplan][$tgl][$line];
	                $Grandsumrijek_buang  = $arr_rijek_buang[$subplan][$tgl][$line];
	                $Grandrightttl_all = $Grandsumexport+$Grandsumekonomi+$Grandsumreject+$Grandsumrijek_palet+$Grandsumrijek_buang;

	                $html .= '<tr>';
	                $html .= '<th colspan="3">GRAND TOTAL</th>';    
	                $html .= '<td style="text-align:right;"><b>'.number_format($Grandsumexport).'</b></td>';   
	                $html .= '<td style="text-align:right;">&nbsp;</td>';   
	                $html .= '<td style="text-align:right;"><b>'.number_format($Grandsumekonomi).'</b></td>';
	                $html .= '<td style="text-align:right;">&nbsp;</td>';   
	                $html .= '<td style="text-align:right;"><b>'.number_format($Grandsumreject).'</b></td>';
	                $html .= '<td style="text-align:right;">&nbsp;</td>';   
	                $html .= '<td style="text-align:right;"><b>'.number_format($Grandsumrijek_palet).'</b></td>';
	                $html .= '<td style="text-align:right;">&nbsp;</td>';   
	                $html .= '<td style="text-align:right;"><b>'.number_format($Grandsumrijek_buang).'</b></td>';
	                $html .= '<td style="text-align:right;">&nbsp;</td>';   
	                $html .= '<td style="text-align:right;"><b>'.number_format($Grandrightttl_all).'</b></td>';    
	                $html .= '<td>&nbsp;</td>'; 
	                $html .= '</tr>';

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
	$html = 'Belum tersedia';
	echo $html;
}

function lihatdata(){
	global $app_plan_id;
	$sp_id = $_POST['sp_id'];
	$sql = "SELECT * from qc_fg_sorting_header WHERE sp_id = '{$sp_id}'";
	$rh = dbselect_plan($app_plan_id, $sql);
	$datetime = explode(' ',$rh[sp_date]);
	$rh[tgl] = cgx_dmy2ymd($datetime[0]);
	$rh[jam] = '';
			
	$out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : '.$rh[sp_id].'</td><td>Di-input Oleh : '.$rh[sp_user_create].'</td></tr><tr><td>Subplant : '.$rh[sp_sub_plant].'</td><td>Tanggal Input : '.$rh[sp_date_create].'</td></tr><tr><td>Tanggal : '.$rh[tgl].'</td><td>Di-edit Oleh : '.$rh[sp_user_modify].'</td></tr><tr><td>Jam : '.$rh[jam].'</td><td>Tanggal Edit : '.$rh[sp_date_modify].'</td></tr></table>';
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