<?php

include_once("../../libs/init.php");
include("mpdf.php");

$subplan = $_GET['subplan'];
    $tanggal = $_GET['tanggal'];
    $tglfrom = cgx_dmy2ymd($tanggal)." 00:00:00";
    $tglto = cgx_dmy2ymd($tanggal)." 23:59:59";
    $tgljudul = $tanggal;
    $whdua = "";
    if($subplan <> 'All') {
        $whdua .= " and a.qgh_subplant = '".$subplan."'";
    }
    $sql = "SELECT a.qgh_subplant as subplan, b.qgd_motif as motif, a.qgh_shift as shift, a.qgh_id, b.qgd_hasil, b.qgd_reject, b.qgd_hambatan, qgh_absensi, qgh_keterangan
        from qc_gl_header a
        join qc_gl_detail b on(a.qgh_id=b.qgh_id)
        where qgh_rec_stat = 'N' and a.qgh_date >= '{$tglfrom}' and a.qgh_date <= '{$tglto}' $whdua
        order by subplan, motif, shift, qgh_id";
    $responce->sql = $sql;
    $qry = dbselect_plan_all($app_plan_id, $sql);
    if(is_array($qry)){
        foreach($qry as $r){
            $arr_kolom["$r[subplan]"]["$r[shift]"] = '';
            $arr_nilai["$r[subplan]"]["$r[motif]"]["$r[shift]"]["$r[qgh_id]"] = $r[qgd_hasil].'@@'.$r[qgd_reject].'@@'.$r[qgd_hambatan];
            $arr_absensi["$r[subplan]"]["$r[shift]"]["$r[qgh_id]"] = $r[qgh_absensi];
            $arr_keterangan["$r[subplan]"]["$r[shift]"]["$r[qgh_id]"] = $r[qgh_keterangan];
        }
    }
    if(is_array($arr_nilai)) {
        $sqlham = "SELECT qmh_code, qmh_nama from qc_md_hambatan order by qmh_code";
        $qryham = dbselect_plan_all($app_plan_id, $sqlham);
        if(is_array($qryham)) {
            $jml_hambatan = count($qryham);
            $jml_kolom = 4; 
            $jml_baris = ceil($jml_hambatan/$jml_kolom); 
            $idx = 1; 
            foreach($qryham as $rham) {
                $row_id = $idx % $jml_baris;
                $rows[$row_id] .= '<td style="font-size:80%;">'.$rham[qmh_code].' : '.$rham[qmh_nama].'</td>';
                $idx++;
            }
            $ham_html ='<table style="width:100%"><tbody><tr><td colspan="4" style="font-size:80%;">Kode Hambatan : <td></tr>'; 
            foreach ($rows as $cur_row) { 
                $ham_html .= '<tr>'.$cur_row.'</tr>'; 
            } 
            $ham_html .='</tbody></table>';
        }
        $out = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
        $out .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : </div><div style="text-align:center;font-size:20px;font-weight:bold;">LAPORAN GLAZE LINE</div><table style="margin:0 auto;"><tr><td>TANGGAL : </td><td>'.$tgljudul.'</td></tr></table><div style="overflow-x:auto;">';
        foreach ($arr_nilai as $subplan => $a_motif) {
            $out .= '<div style="font-size:18px;font-weight:bold;">SUBPLANT : '.$subplan.'</div>';
            $out .= '<div style="font-size:13px;font-weight:bold;margin-top:10px;">I. HASIL PRODUKSI</div>';
            $out .= '<table class="adaborder"><tbody>';
            $out .= '<tr><th rowspan="2" style="width:35px;max-width:35px;">NO</th><th rowspan="2">CODE</th>';
            ksort($arr_kolom[$subplan]);
            reset($arr_kolom[$subplan]);
            foreach ($arr_kolom[$subplan] as $shift => $value) {
                $out .= '<th colspan="3">SHIFT - '.$shift.'</th>';
            }
            $out .= '<th colspan="3">TOTAL</th></tr><tr>';
            ksort($arr_kolom[$subplan]);
            reset($arr_kolom[$subplan]);
            foreach ($arr_kolom[$subplan] as $shift => $value) {
                $out .= '<th style="width:80px;max-width:80px;">HASIL, M2</th><th style="width:80px;max-width:80px;">REJECT, M2</th><th style="width:60px;max-width:60px;">HAMBATAN</th>';
            }
            $out .= '<th style="width:90px;max-width:90px;">HASIL, M2</th><th style="width:90px;max-width:90px;">REJECT, M2</th><th style="width:100px;max-width:100px;">TOTAL</th></tr>';
            $i = 1;
            foreach ($a_motif as $motif => $a_shift) {
                $kol_hasil = 0;
                $kol_reject = 0;
                $out .='<tr><td style="text-align:center;">'.$i.'</td><td style="white-space:nowrap">'.$motif.'</td>';
                ksort($arr_kolom[$subplan]);
                reset($arr_kolom[$subplan]);
                foreach ($arr_kolom[$subplan] as $shift => $value) {
                    if(is_array($a_shift[$shift])) {
                        $hasil = '';
                        $reject = '';
                        $hambatan = '';
                        foreach ($a_shift[$shift] as $qgh_id => $nilai) {
                            $r = explode("@@",$nilai);
                            if($r[0]){
                                $hasil .= '<span onclick="lihatData(\''.$qgh_id.'\')">'.number_format($r[0]).'</span> ';
                                $kol_hasil += $r[0];
                                $row_hasil[$subplan][$shift] += $r[0];
                                $tot_hasil[$subplan] += $r[0];
                            }
                            if($r[1]){
                                $reject .= '<span onclick="lihatData(\''.$qgh_id.'\')">'.number_format($r[1]).'</span> ';
                                $kol_reject += $r[1];
                                $row_reject[$subplan][$shift] += $r[1];
                                $tot_reject[$subplan] += $r[1];
                            }
                            if($r[2]){
                                $hambatan .= '<span onclick="lihatData(\''.$qgh_id.'\')">'.$r[2].'</span> ';
                            }
                        }
                        $out .= '<td style="text-align:right;">'.$hasil.'</td><td style="text-align:right;">'.$reject.'</td><td style="text-align:center;">'.$hambatan.'</td>';
                    } else {
                        $out .= '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                    }   
                }
                $kol_hare = $kol_hasil+$kol_reject;
                $out .= '<td style="text-align:right;">'.number_format($kol_hasil).'</td><td style="text-align:right;">'.number_format($kol_reject).'</td><td style="text-align:right;">'.number_format($kol_hare).'</td>';
                $out .= '</tr>';
                $i++;
            }
            $out .='<tr style="background-color:#cadbf7;"><td colspan="2" style="font-weight:bold;">TOTAL</td>';
            ksort($arr_kolom[$subplan]);
            reset($arr_kolom[$subplan]);
            foreach ($arr_kolom[$subplan] as $shift => $value) {
                $out .= '<td style="text-align:right;">'.number_format($row_hasil[$subplan][$shift]).'</td><td style="text-align:right;">'.number_format($row_reject[$subplan][$shift]).'</td><td>&nbsp;</td>'; 
            }
            $tot_hare[$subplan] = $tot_hasil[$subplan] + $tot_reject[$subplan];
            $out .= '<td style="text-align:right;">'.number_format($tot_hasil[$subplan]).'</td><td style="text-align:right;">'.number_format($tot_reject[$subplan]).'</td><td style="text-align:right;">'.number_format($tot_hare[$subplan]).'</td>';
            $out .= '</tr>';
            $out .='</tbody></table>';

            $out .= '<div style="font-size:13px;font-weight:bold;margin-top:5px;">II. ABSENSI</div>';
            $out .= '<table style="width:100%"><tbody><tr><td style="width:48%;vertical-align:top;"><table class="adaborder"><tbody><tr style="background-color:#cadbf7;">';
            ksort($arr_absensi[$subplan]);
            reset($arr_absensi[$subplan]);
            foreach ($arr_absensi[$subplan] as $shift => $a_qgh_id) {   
                $out .= '<th>SHIFT - '.$shift.'</th>';
            }
            $out .= '</tr><tr style="height:100%">';
            ksort($arr_absensi[$subplan]);
            reset($arr_absensi[$subplan]);
            foreach ($arr_absensi[$subplan] as $shift => $a_qgh_id) {
                $out .= '<td>';
                if(count($a_qgh_id) > 1) {
                    $out .= '<ul style="margin-left:-30px;">';
                    foreach ($a_qgh_id as $qgh_id => $absensi) {
                        $out .= '<li>'.$absensi.'</li>';    
                    }   
                    $out .= '</ul>';
                } else {
                    foreach ($a_qgh_id as $qgh_id => $absensi) {
                        $out .= $absensi; 
                    }
                }
                $out .= '</td>';
            }
            $out .='</tr></tbody></table></td><td style="width:52%;vertical-align:top;">'.$ham_html.'</td></tr></tbody></table>';

            $out .= '<div style="font-size:13px;font-weight:bold;margin-top:5px;">III. KETERANGAN</div>';
            $out .= '<table class="adaborder" style="margin-bottom:20px;"><tbody><tr style="height:130px;">';
            ksort($arr_keterangan[$subplan]);
            reset($arr_keterangan[$subplan]);
            foreach ($arr_keterangan[$subplan] as $shift => $a_qgh_id) {
                $out .= '<td style="border-bottom:0px;">';
                if(count($a_qgh_id) > 1) {
                    $out .= '<ul style="margin-left:-30px;">';
                    foreach ($a_qgh_id as $qgh_id => $keterangan) {
                        $out .= '<li>'.$keterangan.'</li>'; 
                    }   
                    $out .= '</ul>';
                } else {
                    foreach ($a_qgh_id as $qgh_id => $keterangan) {
                        $out .= $keterangan;  
                    }
                }
                $out .= '</td>';    
            }
            $out .='</tr><tr>';
            ksort($arr_keterangan[$subplan]);
            reset($arr_keterangan[$subplan]);
            foreach ($arr_keterangan[$subplan] as $shift => $a_qgh_id) {    
                $out .= '<th style="border-top:0px;">KAREGU SHIFT - '.$shift.' '.$subplan.'</th>';
            }
            $out .='</tr></tbody></table>';
        }
        $out .='</div>';
    } else {
        $out = 'TIDAKADA';
    }
// echo $out;
$mpdf = new mPDF('','A4-L');
$mpdf->WriteHTML($out);
$mpdf->Output('Glaze_Line.pdf', 'D'); // D, I

?>