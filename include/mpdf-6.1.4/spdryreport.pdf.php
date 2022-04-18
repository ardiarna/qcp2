<?php

include_once("../../libs/init.php");
include("mpdf.php");

$arr_kolom = array("08" => "08", "09" => "09", "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14", "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19", "20" => "20", "21" => "21", "22" => "22", "23" => "23", "00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04", "05" => "05", "06" => "06", "07" => "07");
    $sql0 = "SELECT qssd_seq from qc_sp_sett_detail where qssd_group = '01' order by qssd_seq";
    $qry0 = dbselect_plan_all($app_plan_id, $sql0);
    $arr_line = array();
    if(is_array($qry0)) {
        foreach($qry0 as $r0){
            array_push($arr_line, $r0[qssd_seq]);
        }
    }
    $subplan = $_GET['subplan'];
    $tanggal = explode('@', $_GET['tanggal']);
    $tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
    $tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
    $tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
    $whdua = "";
    if($subplan <> 'All') {
        $whdua .= "and qsm_sub_plant = '".$subplan."'";
    }
    $sql = "SELECT a.qsm_sub_plant as subplan, a.qsm_date, b.qsmd_sett_seq as line, d.qssd_monitoring_desc as item_nama, b.qsmd_sett_value as nilai, a.qsm_id from qc_sp_monitoring a join qc_sp_monitoring_detail b on(a.qsm_id=b.qsm_id) join qc_sp_sett_detail d on(b.qsmd_sett_group=d.qssd_group and b.qsmd_sett_seq=d.qssd_seq) where qsm_rec_status='N' and b.qsmd_sett_group='01' and a.qsm_date >= '{$tglfrom}' and a.qsm_date <= '{$tglto}' $whdua order by a.qsm_sub_plant, a.qsm_date, b.qsmd_sett_seq, a.qsm_id";
    $responce->sql = $sql; 
    $qry = dbselect_plan_all($app_plan_id, $sql);
    if(is_array($qry)) {
        foreach($qry as $r){
            $datetime = explode(' ',$r[qsm_date]);
            $r[tgl] = cgx_dmy2ymd($datetime[0]);
            $r[jam] = substr($datetime[1],0,2);
            $arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[line]"]["$r[jam]"]["$r[qsm_id]"] = $r[nilai];
            $arr_item["$r[line]"] = $r[item_nama];
        }
    }
    
    $whdua2 = "";
    if($subplan <> 'All') {
        $whdua2 .= " and qsms_sub_plant = '".$subplan."'";
    }
    $sql2 = "SELECT qsms_sub_plant as subplan, qsms_date, qsms_keterangan
        from qc_sp_monitoring_stop
        where qsms_rec_status='N' and qsms_date >= '{$tglfrom}' and qsms_date <= '{$tglto}' $whdua2
        order by subplan, qsms_date";
    $responce->sql2 = $sql2;
    $qry2 = dbselect_plan_all($app_plan_id, $sql2);
    if(is_array($qry2)) {
        foreach($qry2 as $r2){
            $datetime2 = explode(' ',$r2[qsms_date]);
            $r2[tgl] = cgx_dmy2ymd($datetime2[0]);
            $r2[jam] = substr($datetime2[1],0,2);
            $arr_stop["$r2[subplan]"]["$r2[tgl]"]["$r2[jam]"] = $r2[qsms_keterangan];
        }
    }
    
    if(is_array($arr_stop)) {
        foreach ($arr_stop as $subplan => $a_tgl) {
            foreach ($a_tgl as $tgl => $a_jam) {
                if(!$arr_nilai[$subplan][$tgl]) {
                    foreach ($a_jam as $jam => $qsms_keterangan) {
                        foreach ($arr_line as $line) {
                            $arr_nilai["$subplan"]["$tgl"]["$line"]["$jam"]["1"] = "";
                        }
                    }
                }   
            }
        }
    }

    if(is_array($arr_nilai)) {
        ksort($arr_nilai);
        reset($arr_nilai);  
        $html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
        $html .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.1002.QC.04</div><div style="text-align:center;font-size:20px;font-weight:bold;">MONITOR SETTING SPRAY DRYER</div><table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
        $html .= '<div style="overflow-x:auto;"><table class="adaborder">';
        foreach ($arr_nilai as $subplan => $a_tgl) {
            foreach ($a_tgl as $tgl => $a_line) {
                $html .= '<tr><th colspan="2" style="text-align:left;padding-top:20px;">SUBPLANT : '.$subplan.'</th><th colspan="24" style="text-align:left;padding-top:20px;">'.$tgl.'</th></tr>';
                $html .= '<tr><th rowspan="2">NO</th><th rowspan="2">ITEM</th><th colspan="24">JAM</th></tr>';
                $html .= '<tr>';
                foreach ($arr_kolom as $kolom => $kolom_nama) {
                    $html .= '<th>'.$kolom.':00</th>';
                }
                $html .= '</tr>';
                foreach ($a_line as $line => $a_jam) {
                    $html .='<tr><td style="text-align:center;">'.$line.'</td><td style="white-space: nowrap">'.$arr_item[$line].'</td>';
                    foreach ($arr_kolom as $kolom => $kolom_nama) {
                        if($line == '1') {
                            if($arr_stop[$subplan][$tgl][$kolom]) {
                                $html .= '<td rowspan="'.count($a_line).'" style="background-color:lightblue">'.$arr_stop[$subplan][$tgl][$kolom].'</td>';
                            } else {
                                $html .= '<td style="text-align:right;">';
                                if(is_array($a_jam[$kolom])) {
                                    foreach ($a_jam[$kolom] as $qsm_id => $nilai) {
                                        $html .= '<span onclick="lihatData(\''.$qsm_id.'\')">'.$nilai.'</span> ';   
                                    }
                                } else {
                                    $html .= '&nbsp;';
                                }   
                                $html .= '</td>';
                            }
                        } else {
                            if($arr_stop[$subplan][$tgl][$kolom]) {
                                
                            } else {
                                $html .= '<td style="text-align:right;">';
                                if(is_array($a_jam[$kolom])) {
                                    foreach ($a_jam[$kolom] as $qsm_id => $nilai) {
                                        $html .= '<span onclick="lihatData(\''.$qsm_id.'\')">'.$nilai.'</span> ';   
                                    }
                                } else {
                                    $html .= '&nbsp;';
                                }   
                                $html .= '</td>';
                            }
                        }
                    }
                    $html .= '</tr>';
                }
            }
        }
        $html .='</table></div>';
    } else {
        $html = 'TIDAKADA';
    }

$mpdf = new mPDF('','A4-L');
$mpdf->WriteHTML($html);
$mpdf->Output('Monitor_Setting_Spray_Dryer.pdf', 'D');

?>