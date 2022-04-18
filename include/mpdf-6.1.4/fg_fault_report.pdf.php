<?php

include_once("../../libs/init.php");
include("mpdf.php");

global $app_plan_id;
$subplan = $_GET['subplan'];
$tanggal = explode('@', $_GET['tanggal']);
$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
$whdua = "";
if($subplan <> 'All') {
    $whdua .= "and a.fgf_sub_plant = '".$subplan."'";
}
$sql = "SELECT a.fgf_sub_plant as subplan, a.fgf_id, a.fgf_date, a.fgf_kiln, a.fgf_quality, a.fgf_type,
               b.fapr_id, c.fapr_desc, b.eco_value, b.rj_value from qc_fg_fault_header a 
        join qc_fg_fault_detail b on a.fgf_id = b.fgf_id
        join qc_fg_fault_parameter c on b.fapr_id = c.fapr_id and a.fgf_sub_plant = c.sub_plant
        WHERE a.fgf_status='N' $whdua
        AND a.fgf_date >= '{$tglfrom}' 
        AND a.fgf_date <= '{$tglto}'
        ORDER BY a.fgf_sub_plant, a.fgf_date, a.fgf_kiln, a.fgf_id, CAST(b.fapr_id AS int) ASC";
$responce->sql = $sql; 
$qry = dbselect_plan_all($app_plan_id, $sql);
if(is_array($qry)) {
    foreach($qry as $r){
        $datetime = explode(' ',$r[fgf_date]);
        $r[tgl] = cgx_dmy2ymd($datetime[0]);
        $r[jam] = substr($datetime[1],0,2);
        
        $arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"]["$r[fapr_id]"]["$r[fgf_id]"] = $r[eco_value].'@'.$r[rj_value];

        $arr_quality["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"]["$r[fgf_id]"] = $r[fgf_quality];
        $arr_type["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"]["$r[fgf_id]"] = $r[fgf_type];


        $arr_line["$r[subplan]"]["$r[fapr_id]"] = $r[fapr_desc];

        $arr_kiln["$r[fgf_kiln]"] = $r[fgf_kiln];
        $arr_jam["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"] = $r[jam];

        $arr_sumeco["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"] += $r[eco_value];
        $arr_sumrj["$r[subplan]"]["$r[tgl]"]["$r[fgf_kiln]"]["$r[jam]"] += $r[rj_value];
    }
}

    if(is_array($arr_nilai)) {

        $html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
        $html .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.1003.QC.02</div>
                  <div style="text-align:center;font-size:20px;font-weight:bold;">FAULT ANALISIS</div>
                  <table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
        $html .= '<div style="overflow-x:auto;"><table class="adaborder">';
        foreach ($arr_nilai as $subplan => $a_tgl) {

            

            foreach ($a_tgl as $tgl => $a_kiln) {

                $jmljamall = 0;
                foreach ($a_kiln as $kiln => $a_jam) {
                    $jmljamall += count($a_jam);
                }

                $html .= '<tr><th colspan="'.(($jmljamall*2)+2).'" style="text-align:left;padding-top:20px;">SUBPLANT : '.$subplan.' | '.$tgl.'</th></tr>';
                $html .= '<tr ><th rowspan="2" style="background:#D1D1D1;">NO</th>
                              <th rowspan="2" style="background:#D1D1D1;">DEFECT</th>';
                foreach ($a_kiln as $kiln => $a_jam) {
                    $jmljam = count($a_jam);
                    $html .= '<th colspan="'.($jmljam*2).'" style="background:#D1D1D1;">KILN '.Romawi($kiln).'</th>';
                }
                $html .= '</tr>';

                $html .= '<tr>';
                foreach ($a_kiln as $kiln => $a_jam) {
                    $jmljam = count($a_jam);
                    $html .= '<th colspan="'.($jmljam*2).'" style="background:#D1D1D1;">AVERAGE DEFECT</th>';
                }
                $html .= '</tr>';
                
                $html .= '<tr>';
                $html .= '<td>&nbsp;</td>';
                $html .= '<td>Export Quality (%)</td>';

                foreach ($a_kiln as $kiln => $a_jam) {
                    foreach ($a_jam as $jam => $item) {
                        $html .= '<td colspan="2" align="center">';
                        if(is_array($arr_quality[$subplan][$tgl][$kiln][$jam])) {
                            foreach ($arr_quality[$subplan][$tgl][$kiln][$jam] as $q_id => $quality) {
                                $html .= '<span onclick="lihatData(\''.$q_id.'\')">'.$quality.'</span>';
                            }
                        }else{
                            $html .= '&nbsp;';
                        }
                        $html .= '</td>';
                    }
                }

                $html .= '</tr>';

                $html .= '<tr>';
                $html .= '<td>&nbsp;</td>';
                $html .= '<td>Motive / Type</td>';

                foreach ($a_kiln as $kiln => $a_jam) {
                    foreach ($a_jam as $jam => $item) {
                        $html .= '<td colspan="2" align="center">';
                        if(is_array($arr_type[$subplan][$tgl][$kiln][$jam])) {
                            foreach ($arr_type[$subplan][$tgl][$kiln][$jam] as $t_id => $type) {
                                $html .= '<span onclick="lihatData(\''.$t_id.'\')">'.$type.'</span>';
                            }
                        }else{
                            $html .= '&nbsp;';
                        }
                        $html .= '</td>';
                    }
                }

                $html .= '</tr>';


                $html .= '<tr>';
                $html .= '<td>&nbsp;</td>';
                $html .= '<td>Time</td>';
                foreach ($a_kiln as $kiln => $a_jam) {
                    foreach ($a_jam as $jam => $item) {
                        $html .= '<td colspan="2" align="center">'.$jam.':00</td>';
                    }
                }
                $html .= '</tr>';

                $html .= '<tr>';
                $html .= '<th>&nbsp;</th>';
                $html .= '<th style="background:#D1D1D1;">&nbsp;</th>';
                foreach ($a_kiln as $kiln => $a_jam) {
                    foreach ($a_jam as $jam => $item) {
                        $html .= '<th align="center" style="background:#D1D1D1;">ECO</th>';
                        $html .= '<th align="center" style="background:#D1D1D1;">RJ</th>';
                    }
                }

                $html .= '</tr>';

                $html .= '<tr>';
                $html .= '<td>&nbsp;</td>';
                $html .= '<td>&nbsp;</td>';
                foreach ($a_kiln as $kiln => $a_jam) {
                    foreach ($a_jam as $jam => $item) {
                        $html .= '<td>&nbsp;</td>';
                        $html .= '<td>&nbsp;</td>';
                    }
                }
                $html .= '</tr>';

                $no_line =1;
                foreach ($arr_line[$subplan] as $line_id => $lineval) {
                    $html .= '<tr>';
                    $html .= '<td align="center">'.$no_line.'</td>';
                    $html .= '<td>'.$lineval.'</td>';
                    
                    foreach ($a_kiln as $kiln => $a_jam) {
                        foreach ($a_jam as $jam => $item) {
                            if(is_array($arr_nilai[$subplan][$tgl][$kiln][$jam][$line_id])) {
                                foreach ($arr_nilai[$subplan][$tgl][$kiln][$jam][$line_id] as $fgf_id => $value) {
                                    $val = explode("@",$value);
                                    $eco_value = $val[0];
                                    $rj_value  = $val[1];

                                    if($eco_value == 0){
                                        $eco_value = '&nbsp;';
                                    }else{
                                        $eco_value = $eco_value;
                                    }

                                    if($rj_value == 0){
                                        $rj_value = '&nbsp;';
                                    }else{
                                        $rj_value = $rj_value;
                                    }

                                    $html .= '<td align="right"><span onclick="lihatData(\''.$fgf_id.'\')">'.$eco_value.'</span></td>';
                                    $html .= '<td align="right"><span onclick="lihatData(\''.$fgf_id.'\')">'.$rj_value.'</span></td>';

                                    
                                }
                            }else{
                                $html .= '<td>&nbsp;</td>';
                                $html .= '<td>&nbsp;</td>';
                            }
                        }
                    }


                    $html .= '</tr>';
                $no_line++;
                }

                $html .= '<tr>';
                $html .= '<th style="background:#D1D1D1;">&nbsp;</th>';
                $html .= '<th align="center" style="background:#D1D1D1;"> T O T A L </th>';
                foreach ($a_kiln as $kiln => $a_jam) {
                    foreach ($a_jam as $jam => $item) {
                        $ttl_sumeco = $arr_sumeco[$subplan][$tgl][$kiln][$jam];
                        $ttl_sumrj  = $arr_sumrj[$subplan][$tgl][$kiln][$jam];

                        $html .= '<th align="right" style="background:#D1D1D1;">'.$ttl_sumeco.'</th>';
                        $html .= '<th align="right" style="background:#D1D1D1;">'.$ttl_sumrj.'</th>';
                    }
                }
                $html .= '</tr>';

            }             

        }

        $html .='</table></div>';
    } else {
        $html = 'TIDAKADA';
    }

$mpdf = new mPDF('','F4-L');
$mpdf->WriteHTML($html);
$mpdf->Output('Fault_Analisis.pdf', 'D');

?>