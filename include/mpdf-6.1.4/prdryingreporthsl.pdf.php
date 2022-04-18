<?php

include_once("../../libs/init.php");
include("mpdf.php");

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

                $html .= '<div style="font-size:15px;font-weight:bold;">SUBPLANT : '.$subplan.' '.$tgl.'</div>';
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
            $html .= '<div style="font-size:13px;font-weight:bold;"><u>'.Romawi($r2[kodee]).'. '.$r2[qcpdm_desc].'</u></div>';
            
            $html .= '<div style="overflow-x:auto;"><table class="adaborder" style="font-size:11px;">';

            //header start
            $html .='<tr>';
            $html .='<th rowspan="2">NO</th>';
            
            $sqlcolmn = "SELECT COUNT(*) AS jmlcolmn FROM qc_pd_prep_group_detil WHERE qcpdm_group = '01' ";
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
                            $sqlnilai = "SELECT b.qpdh_pd_value
                                         FROM qc_pd_hsl_header a
                                         JOIN qc_pd_hsl_detail b on(a.qpdh_id=b.qpdh_id)
                                         WHERE qpdh_status = 'N' and a.qpdh_date = '{$tgl2}' 
                                         AND a.qpdh_sub_plant = '{$subplanu2}' 
                                         AND b.qcpdm_group = '{$r2[qcpdm_group]}'
                                         AND b.qcpdd_seq = '{$subgrup2[qcpdd_seq]}'
                                         AND a.qpdh_shift = '{$xshift3}'
                                         AND b.qpp_press_no = '{$press[qpp_press_no]}'";
                            $qrynilai = dbselect_plan($app_plan_id, $sqlnilai);

                            $html .='<td style="text-align:right;">'.$qrynilai[qpdh_pd_value].'</td>';
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

                            $html .='<td style="text-align:right;">'.$qrynilai[ttlright].'</td>';
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

                        $html .='<th style="text-align:right;">'.$qrynilai3[ttlbottom].'</th>';
                }
            } 
            $html .='</tr>';
            //total bottom end


            
            $html .='</table></div>';

        }else if($r2[qcpdm_group] == '02'){
            
            $html .= '<div style="font-size:14px;font-weight:bold;"><u>'.Romawi($r2[kodee]).'. '.$r2[qcpdm_desc].'</u></div>';
            
            $html .= '<div style="overflow-x:auto;"><table class="adaborder" style="font-size:11px;"><tr>';

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

                $html .= '<div style="overflow-x:auto;"><table class="adaborder" style="font-size:11px;"><tr>';
                for ($xshift2 = 1; $xshift2 <= 3; $xshift2++) {
                        $html .= '<th width="33%">SHIFT '.Romawi($xshift2).'</th>';
                } 
                $html .='</tr><tr>';


                for ($xshift2a = 1; $xshift2a <= 3; $xshift2a++) {
                        $sql3 = "SELECT qpdh_shift as shift, qpdh_pd_value as nilai, a.qpdh_id
                                 FROM qc_pd_hsl_header a
                                 JOIN qc_pd_hsl_detail b on(a.qpdh_id=b.qpdh_id)
                                 where qpdh_status = 'N' and a.qpdh_date = '{$tgl2}' AND a.qpdh_sub_plant = '{$subplanu2}' 
                                 AND b.qcpdm_group = '{$r2[qcpdm_group]}'
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
    $html .= '<div style="overflow-x:auto;"><table width="100%"><tr>';
    for ($ttd = 1; $ttd <= 4; $ttd++) {
        if($ttd <> 4){
            $ttdVal = 'KAREGU SHIFT - '.Romawi($ttd);
        }else{
            $ttdVal = 'KASUBSI PRESS';
        }
            $html .= '<td width="25%" align="center"><b>'.$ttdVal.'</b></td>';
    } 
    $html .='</tr>';

    $html .= '<tr>';
    for ($ttd2 = 1; $ttd2 <= 4; $ttd2++) {
            $ttd2Val = '(...................................)';
            $html .= '<td align="center" height="120px">'.$ttd2Val.'</td>';
    } 
    $html .='</tr>';

    $html .='</table></div>';
    //ttd end

    return $html;
}



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
    $html .= '<div style="text-align:left;font-size:15px;font-weight:bold;">LAPORAN KAREGU PRESS</div>
                  <div style="text-align:left;font-size:15px;font-weight:bold;">TANGGAL : '.$tgljudul.'</div>';
    

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

$mpdf = new mPDF('','A4-L');
$mpdf->WriteHTML($html);
$mpdf->Output('LAPORAN KAREGU PRESS.pdf', 'D');

?>