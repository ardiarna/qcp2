<?php

include_once("../../libs/init.php");
include("mpdf.php");

global $app_plan_id;
$subplan  = $_GET['subplan'];
$no_line = $_GET['qph_no_line'];

$tanggal = explode('@', $_GET['tanggal']);
$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
$whdua = "";
if($subplan <> 'All') {
    $whdua .= "and qph_sub_plant = '".$subplan."'";
}

if($no_line <> 'All') {
    $whdua .= "and qph_no_line = '".$no_line."'";
}


$sql = "SELECT distinct qph_sub_plant, qph_no_line from qc_pd_header WHERE qph_rec_stat='N' and qph_date >= '{$tglfrom}' and qph_date <= '{$tglto}' $whdua order by qph_sub_plant, qph_no_line";
$responce->sqla = $sql; 
$qry = dbselect_plan_all($app_plan_id, $sql);
if(is_array($qry)) {
    foreach($qry as $r){
        
        $arr_plan["$r[qph_sub_plant]"]["$r[qph_no_line]"] = $r[qph_no_line];
    }
}

if(is_array($arr_plan)) {

    
    $html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
    $html .= '<div style="text-align:center;font-size:20px;font-weight:bold;">PRESS & DRYING REPORT</div>
              <table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
    
    foreach ($arr_plan as $splan => $a_press) {
        foreach ($a_press as $press) {
            $html .= '<div style="text-align:left;font-size:16px;font-weight:bold;">SUBPLAN '.$splan.' - PRESS '.$press.'</div>';
            $sWhere = "and a.qph_sub_plant = '".$splan."' and a.qph_no_line = '".$press."'";
            for ($i=1; $i <=3; $i++) { 
                $grup = '0'.$i;
                if($grup == "01") {

                    $html .= '<div style="overflow-x:auto;"><table class="adaborder">';

                        $sql1 = "SELECT a.qph_id, a.qph_date, a.qph_no_line, a.qph_shift as shift,  
                                        b.qpd_pd_seq as line, c.qpgd_control_desc as item_name, '' as std, d.qgu_code as unit, 
                                        b.qpd_pd_value as nilai, b.qpd_pd_remark as remark
                                from qc_pd_header a
                                join qc_pd_detail b on(a.qph_id=b.qph_id)
                                join qc_pd_group_detail c on(b.qpd_pd_group=c.qpgd_group and b.qpd_pd_seq=c.qpgd_seq)
                                join qc_gen_um d on(c.qpgd_um_id=d.qgu_id)
                                where a.qph_rec_stat='N' and b.qpd_pd_group='{$grup}' and a.qph_date >= '{$tglfrom}' and a.qph_date <= '{$tglto}' $sWhere
                                order by a.qph_date, shift, line";
                        $responce->sql[$splan][$press][$grup] = $sql1; 
                        $qry1 = dbselect_plan_all($app_plan_id, $sql1);
                        if(is_array($qry1)) {
                            $arr_kolom = array();
                            $arr_nilai = array();
                            $arr_remark = array();
                            $arr_item = array();
                            foreach($qry1 as $r1){
                                $datetime = explode(' ',$r1[qph_date]);
                                $r1[tgl] = cgx_dmy2ymd($datetime[0]);
                                $arr_kolom["$r1[tgl]"]["$r1[qph_no_line]"]["$r1[shift]"] = $r1[shift];
                                $arr_nilai["$r1[tgl]"]["$r1[qph_no_line]"]["$r1[shift]"]["$r1[line]"]["$r1[qph_id]"] = $r1[nilai];
                                $arr_remark["$r1[tgl]"]["$r1[qph_no_line]"]["$r1[shift]"]["$r1[qph_no_line]"]["$r1[line]"]["$r1[qph_id]"] = $r1[remark];
                                $arr_item["$r1[line]"] = $r1[item_name].'@'.$r1[unit];
                            }

                            


                                $html .= '<tr>';
                                $html .= '<th rowspan = "3">NO</th>';
                                $html .= '<th rowspan = "3">POWDER PAKAI</th>';
                                $html .= '<th rowspan = "3">STD</th>';
                                $html .= '<th rowspan = "3">UNIT</th>';
                                foreach($arr_kolom as $tgl => $a_shift){
                                    $colshift = count($a_shift[$press])*2;
                                    $html .= '<th colspan="'.$colshift.'">'.$tgl.'</th>';
                                }
                                $html .= '</tr>';


                                $html .= '<tr>';
                                foreach($arr_kolom as $tgl => $a_shift){
                                    foreach($a_shift[$press] as $shift){
                                        $html .= '<th colspan="2">SHIFT '.Romawi($shift).'</th>';
                                    }
                                }
                                $html .= '</tr>';

                                $html .= '<tr>';
                                foreach($arr_kolom as $tgl => $a_shift){
                                    foreach($a_shift[$press] as $shift){
                                        $html .= '<th>NILAI</th>';
                                        $html .= '<th>REMAKS</th>';
                                    }
                                }
                                $html .= '</tr>';
                                $no=1;
                                foreach($arr_item as $item => $itemVal1){
                                    $itemVal = explode('@', $itemVal1);

                                    $html .= '<tr>';
                                    $html .= '<th>'.$no.'</th>';
                                    $html .= '<td>'.$itemVal[0].'</td>';
                                    $html .= '<td></td>';
                                    $html .= '<td align="center">'.$itemVal[1].'</td>';

                                    foreach($arr_kolom as $tgl => $a_shift){
                                        foreach($a_shift[$press] as $shift){

                                            $html .= '<td align="right">';
                                            if(is_array($arr_nilai[$tgl][$press][$shift][$item])){
                                                foreach($arr_nilai[$tgl][$press][$shift][$item] as $nilai_id => $nilaiVal){
                                                    $html .= ' <span onclick="lihatData(\''.$nilai_id.'\')">'.$nilaiVal.'</span>';
                                                }
                                            }else{
                                                $html .='&nbsp;';
                                            }
                                            $html .= '</td>';


                                            $html .= '<td align="right">';
                                            if(is_array($arr_remaks[$tgl][$press][$shift][$item])){
                                                foreach($arr_remaks[$tgl][$press][$shift][$item] as $remaks_id => $remaksVal){
                                                    $html .= ' <span onclick="lihatData(\''.$remaks_id.'\')">'.$remaksVal.'</span>';
                                                }
                                            }else{
                                                $html .='&nbsp;';
                                            }
                                            $html .= '</td>';


                                            
                                            
                                        }
                                    }

                                    $html .= '</tr>';
                                $no++;
                                }
                        }

                    $html .= '</table></div>';

                }else if($grup == "02") {

                    $html .= '<div style="overflow-x:auto;"><table class="adaborder">';
                        $sql2 = "SELECT a.qph_id, a.qph_sub_plant, a.qph_date, a.qph_no_line, a.qph_shift as shift,
                                b.qpd_pd_seq as line, c.qpgd_control_desc as item_name, '' as std, d.qgu_code as unit, e.qpm_desc as mp,
                                b.qpd_pd_value, b.qpd_pd_valmax
                                from qc_pd_header a
                                join qc_pd_detail b on(a.qph_id=b.qph_id)
                                join qc_pd_group_detail c on(b.qpd_pd_group=c.qpgd_group and b.qpd_pd_seq=c.qpgd_seq)
                                join qc_gen_um d on(c.qpgd_um_id=d.qgu_id)
                                left join qc_pd_mouldset e on(a.qph_sub_plant=e.qpm_sub_plant and a.qph_no_line=e.qpm_press_code 
                                                              and b.qpd_mould_no=e.qpm_code)
                                where a.qph_rec_stat='N' 
                                and b.qpd_pd_group='{$grup}' and a.qph_date >= '{$tglfrom}' 
                                and a.qph_date <= '{$tglto}' $sWhere
                                order by a.qph_date, a.qph_no_line, a.qph_shift, line, mp";
                        $responce->sql[$splan][$press][$grup] = $sql2; 
                        $qry2 = dbselect_plan_all($app_plan_id, $sql2);
                        if(is_array($qry2)) {
                            $arr_kolom2 = array();
                            $arr_nilai2 = array();
                            $arr_item2 = array();
                            foreach($qry2 as $r2){
                                $datetime2 = explode(' ',$r2[qph_date]);
                                $r2[tgl] = cgx_dmy2ymd($datetime2[0]);
                                $arr_kolom2["$r2[tgl]"]["$r2[qph_no_line]"]["$r2[shift]"]["$r2[mp]"] = $r2[mp];
                                $arr_nilai2["$r2[tgl]"]["$r2[qph_no_line]"]["$r2[shift]"]["$r2[line]"]["$r2[mp]"]["$r2[qph_id]"] = $r2[qpd_pd_value].'@@'.$r2[qpd_pd_valmax];
                                $arr_item2["$r2[line]"] = $r2[item_name].'@'.$r2[unit];
                            }

                                $html .= '<tr>';
                                $html .= '<th rowspan = "4">NO</th>';
                                $html .= '<th rowspan = "4">GREEN BODY</th>';
                                $html .= '<th rowspan = "4">STD</th>';
                                $html .= '<th rowspan = "4">UNIT</th>';
                                foreach($arr_kolom2 as $tgl2 => $a_shift2){
                                    $colshift2 = 0;
                                    foreach ($a_shift2[$press] as $shift2 => $a_mp2) {
                                        foreach ($a_mp2 as $mp2) {

                                            $colshift2++;
                                        }
                                    }

                                    $colshift2a = $colshift2*2;

                                    $html .= '<th colspan="'.$colshift2a.'">'.$tgl2.'</th>';
                                }
                                $html .= '</tr>';


                                $html .= '<tr>';
                                foreach($arr_kolom2 as $tgl2 => $a_shift2){
                                    $colshift22 = 0;
                                    foreach($a_shift2[$press] as $shift2 => $a_mp2){
                                        $colshift22 = count($a_mp2)*2;
                                        $html .= '<th colspan="'.$colshift22.'">SHIFT '.Romawi($shift2).'</th>';
                                    }
                                }
                                $html .= '</tr>';

                                $html .= '<tr>';
                                foreach($arr_kolom2 as $tgl2 => $a_shift2){
                                    foreach($a_shift2[$press] as $shift2 => $a_mp2){
                                        foreach($a_mp2 as $mp2){
                                            if($mp2 == ''){$mp2 = '&nbsp;';}
                                            $html .= '<th colspan="2">'.$mp2.'</th>';
                                        }
                                    }
                                }
                                $html .= '</tr>';


                                $html .= '<tr>';
                                foreach($arr_kolom2 as $tgl2 => $a_shift2){
                                    foreach($a_shift2[$press] as $shift2 => $a_mp2){
                                        foreach($a_mp2 as $mp2){
                                            $html .= '<th>MIN</th>';
                                            $html .= '<th>MAX</th>';
                                        }
                                    }
                                }
                                $html .= '</tr>';


                                $no=1;
                                foreach($arr_item2 as $item2 => $itemVal21){
                                    $itemVal2 = explode('@', $itemVal21);

                                    $html .= '<tr>';
                                    $html .= '<th>'.$no.'</th>';
                                    $html .= '<td>'.$itemVal2[0].'</td>';
                                    $html .= '<td></td>';
                                    $html .= '<td align="center">'.$itemVal2[1].'</td>';

                                    foreach($arr_kolom2 as $tgl2 => $a_shift2){
                                        foreach($a_shift2[$press] as $shift2 => $a_mp2){
                                            foreach($a_mp2 as $mp2){
                                                $min = '';
                                                $max = '';
                                                if(is_array($arr_nilai2[$tgl2][$press][$shift2][$item2][$mp2])){
                                                    foreach($arr_nilai2[$tgl2][$press][$shift2][$item2][$mp2] as $nilai2_id => $nilai2Val){
                                                        $Vval = explode('@@', $nilai2Val);
                                                        $nmin = $Vval[0]; 
                                                        $nmax = $Vval[1]; 
                                                        
                                                        $min .= ' <span onclick="lihatData(\''.$nilai2_id.'\')">'.$nmin.'</span>';
                                                        $max .= ' <span onclick="lihatData(\''.$nilai2_id.'\')">'.$nmax.'</span>';
                                                    }
                                                }else{
                                                    $min .='&nbsp;';
                                                    $max .='&nbsp;';
                                                }

                                                $html .= '<td align="right">'.$min.'</td>';
                                                $html .= '<td align="right">'.$max.'</td>';
                                            }
                                        }
                                    }

                                    $html .= '</tr>';
                                $no++;
                                }
                        } 
                    $html .= '</table></div>';
                }else if($grup == "03") {
                    
                    $html .= '<div style="overflow-x:auto;"><table class="adaborder">';
                        $sql3 = "SELECT a.qph_id,a.qph_sub_plant, a.qph_no_line, a.qph_date, a.qph_shift as shift,
                                    b.qpd_pd_seq as line, c.qpgd_control_desc as item_name, '' as std, 
                                    d.qgu_code as unit,  e.qph_desc as hd, b.qpd_pd_value, b.qpd_pd_valmax
                                from qc_pd_header a
                                join qc_pd_detail b on(a.qph_id=b.qph_id)
                                join qc_pd_group_detail c on(b.qpd_pd_group=c.qpgd_group and b.qpd_pd_seq=c.qpgd_seq)
                                join qc_gen_um d on(c.qpgd_um_id=d.qgu_id)
                                left join qc_pd_hd e on(a.qph_sub_plant=e.qph_sub_plant and b.qpd_hd_no=e.qph_code)
                                where a.qph_rec_stat='N' and b.qpd_pd_group='{$grup}' 
                                and a.qph_date >= '{$tglfrom}' 
                                and a.qph_date <= '{$tglto}' $sWhere
                                order by a.qph_date, a.qph_no_line, a.qph_shift, line, hd";
                        $responce->sql[$splan][$press][$grup] = $sql3; 
                        $qry3 = dbselect_plan_all($app_plan_id, $sql3);
                        if(is_array($qry3)) {
                            $arr_kolom3 = array();
                            $arr_nilai3 = array();
                            $arr_item3 = array();
                            foreach($qry3 as $r3){
                                $datetime3 = explode(' ',$r3[qph_date]);
                                $r3[tgl] = cgx_dmy2ymd($datetime3[0]);
                                $arr_kolom3["$r3[tgl]"]["$r3[qph_no_line]"]["$r3[shift]"]["$r3[hd]"] = $r3[hd];
                                $arr_nilai3["$r3[tgl]"]["$r3[qph_no_line]"]["$r3[shift]"]["$r3[line]"]["$r3[hd]"]["$r3[qph_id]"] = $r3[qpd_pd_value].'@@'.$r3[qpd_pd_valmax];
                                $arr_item3["$r3[line]"] = $r3[item_name].'@'.$r3[unit];
                            }

                                $html .= '<tr>';
                                $html .= '<th rowspan = "4">NO</th>';
                                $html .= '<th rowspan = "4">HORIZONTAL DRYER</th>';
                                $html .= '<th rowspan = "4">STD</th>';
                                $html .= '<th rowspan = "4">UNIT</th>';
                                foreach($arr_kolom3 as $tgl3 => $a_shift3){
                                    
                                    $colshift3 = 0;
                                    foreach ($a_shift3[$press] as $shift3 => $a_mp3) {
                                        foreach ($a_mp3 as $mp3) {

                                            $colshift3++;
                                        }
                                    }

                                    $colshift3a = $colshift3*2;

                                    $html .= '<th colspan="'.$colshift3a.'">'.$tgl3.'</th>';
                                }
                                $html .= '</tr>';


                                $html .= '<tr>';
                                foreach($arr_kolom3 as $tgl3 => $a_shift3){
                                    $colshift33 = 0;
                                    foreach($a_shift3[$press] as $shift3 => $a_mp3){
                                        $colshift33 = count($a_mp3)*2;
                                        $html .= '<th colspan="'.$colshift33.'">SHIFT '.Romawi($shift3).'</th>';
                                    }
                                }
                                $html .= '</tr>';


                                $html .= '<tr>';
                                foreach($arr_kolom3 as $tgl3 => $a_shift3){
                                    foreach($a_shift3[$press] as $shift3 => $a_mp3){
                                        foreach($a_mp3 as $mp3){
                                            if($mp3 == ''){$mp3 = '&nbsp;';}
                                            $html .= '<th colspan="2">'.$mp3.'</th>';
                                        }
                                    }
                                }
                                $html .= '</tr>';

                                $html .= '<tr>';
                                foreach($arr_kolom3 as $tgl3 => $a_shift3){
                                    foreach($a_shift3[$press] as $shift3 => $a_mp3){
                                        foreach($a_mp3 as $mp3){
                                            $html .= '<th>MIN</th>';
                                            $html .= '<th>MAX</th>';
                                        }
                                    }
                                }
                                $html .= '</tr>';


                                $no=1;
                                foreach($arr_item3 as $item3 => $itemVal31){
                                    $itemVal3 = explode('@', $itemVal31);

                                    $html .= '<tr>';
                                    $html .= '<th>'.$no.'</th>';
                                    $html .= '<td>'.$itemVal3[0].'</td>';
                                    $html .= '<td></td>';
                                    $html .= '<td align="center">'.$itemVal3[1].'</td>';

                                    foreach($arr_kolom3 as $tgl3 => $a_shift3){
                                        foreach($a_shift3[$press] as $shift3 => $a_mp3){
                                            foreach($a_mp3 as $mp3){
                                                $min3 = '';
                                                $max3 = '';
                                                if(is_array($arr_nilai3[$tgl3][$press][$shift3][$item3][$mp3])){
                                                    foreach($arr_nilai3[$tgl3][$press][$shift3][$item3][$mp3] as $nilai3_id => $nilai3Val){
                                                        $Vval3 = explode('@@', $nilai3Val);
                                                        $nmin3 = $Vval3[0]; 
                                                        $nmax3 = $Vval3[1]; 
                                                        
                                                        $min3 .= ' <span onclick="lihatData(\''.$nilai3_id.'\')">'.$nmin3.'</span>';
                                                        $max3 .= ' <span onclick="lihatData(\''.$nilai3_id.'\')">'.$nmax3.'</span>';
                                                    }
                                                }else{
                                                    $min3 .='&nbsp;';
                                                    $max3 .='&nbsp;';
                                                }

                                                $html .= '<td align="right">'.$min3.'</td>';
                                                $html .= '<td align="right">'.$max3.'</td>';

                                            }
                                        }
                                    }

                                    $html .= '</tr>';
                                $no++;
                                }
                        } 
                    $html .= '</table></div>';

                }else{
                    $html .= '';
                }

                $html .= '<br><br>';


            }

        }
        
    }

} else {
    $html = 'TIDAKADA';
}
$mpdf = new mPDF('','A4-L');
$mpdf->WriteHTML($html);
$mpdf->Output('PRESS & DRYING.pdf', 'D');

?>