<?php

include_once("../../libs/init.php");
include("mpdf.php");

$tanggal = explode('@', $_GET['tanggal']);
$tgl_from = cgx_dmy2ymd($tanggal[0])." 00:00:00";
$tgl_to = cgx_dmy2ymd($tanggal[1])." 23:59:59";
$tgl_judul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
$whdua = "";
$sql = "SELECT qar_ab_nama as nama_msn, qar_ab_nomor as nomor_msn, qar_date, qar_shift as shift, qar_id, qar_awal, qar_akhir
    from qc_alber_runhour
    where qar_rec_stat = 'N' and qar_date >= '{$tgl_from}' and qar_date <= '{$tgl_to}'
    order by qar_ab_nama, qar_ab_nomor, qar_date, qar_shift";
$qry = dbselect_plan_all($app_plan_id, $sql);
$i = 0;
if(is_array($qry)){
    foreach($qry as $r){
        $datetime = explode(' ',$r[qar_date]);
        $r[tgl] = cgx_dmy2ymd($datetime[0]);
        $r[jam] = substr($datetime[1],0,5);
        $arr_kolom["$r[nama_msn]"]["$r[nomor_msn]"]["$r[shift]"] = '';
        $arr_nilai["$r[nama_msn]"]["$r[nomor_msn]"]["$r[tgl]"]["$r[shift]"] = $r[qar_id]."@@".$r[qar_awal]."@@".$r[qar_akhir];
        $i++;
    }
}
if(is_array($arr_nilai)) {
    $html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
    $html .= '<div style="text-align:center;font-size:20px;font-weight:bold;">DATA RUNNING HOUR ALAT BERAT</div><table style="margin:0 auto;"><tr><td>Tanggal : </td><td>'.$tgl_judul.'</td></tr></table>';
    $html .= '<div style="overflow-x:auto;"><table class="adaborder">';
    foreach ($arr_nilai as $nama_msn => $a_nomor_msn) {
        foreach ($a_nomor_msn as $nomor_msn => $a_tgl) {
            $html .= '<tr><th style="text-align:left;">Nama Mesin</th><th style="text-align:left;" colspan="7">'.$nama_msn.'</th></tr><tr><th style="text-align:left;">Nomor Mesin</th><th style="text-align:left;" colspan="7">'.$nomor_msn.'</th></tr>';
            $html .= '<tr><th rowspan="3">TANGGAL</th><th colspan="6">RUNNING HOUR</th><th rowspan="3">TOTAL</th></tr>';
            $html .= '<tr><th colspan="2">SHIFT 1</th><th colspan="2">SHIFT 2</th><th colspan="2">SHIFT 3</th></tr>';
            $html .= '<tr><th>AWAL</th><th>AKHIR</th><th>AWAL</th><th>AKHIR</th><th>AWAL</th><th>AKHIR</th></tr>';
            $tg_a = 1;
            $nil_awal_tgl = 0;
            $nil_ahir_tgl = 0;
            foreach ($a_tgl as $tgl => $a_shift) {
                $nil_1 = array();
                $nil_2 = array();
                $nil_3 = array();
                $nil_awal = 0;
                $nil_ahir = 0;
                $html .='<tr><td style="text-align:center;">'.$tgl.'</td>';
                $nilai_1 = $a_shift[1];
                if($nilai_1) {
                    $nil_1 = explode("@@",$nilai_1);
                    $html .= '<td style="text-align:right;" onclick="lihatData(\''.$nil_1[0].'\')">'.number_format($nil_1[1]).'</td>';
                    $html .= '<td style="text-align:right;" onclick="lihatData(\''.$nil_1[0].'\')">'.number_format($nil_1[2]).'</td>';
                } else {
                    $html .= '<td></td><td></td>';
                }
                $nilai_2 = $a_shift[2];
                if($nilai_2) {
                    $nil_2 = explode("@@",$nilai_2);
                    $html .= '<td style="text-align:right;" onclick="lihatData(\''.$nil_2[0].'\')">'.number_format($nil_2[1]).'</td>';
                    $html .= '<td style="text-align:right;" onclick="lihatData(\''.$nil_2[0].'\')">'.number_format($nil_2[2]).'</td>';
                } else {
                    $html .= '<td></td><td></td>';
                }
                $nilai_3 = $a_shift[3];
                if($nilai_3) {
                    $nil_3 = explode("@@",$nilai_3);
                    $html .= '<td style="text-align:right;" onclick="lihatData(\''.$nil_3[0].'\')">'.number_format($nil_3[1]).'</td>';
                    $html .= '<td style="text-align:right;" onclick="lihatData(\''.$nil_3[0].'\')">'.number_format($nil_3[2]).'</td>';
                } else {
                    $html .= '<td></td><td></td>';
                }

                if($nil_1[1]){
                    $nil_awal = $nil_1[1]; 
                } else if($nil_1[2]){
                    $nil_awal = $nil_1[2];
                } else if($nil_2[1]){
                    $nil_awal = $nil_2[1];
                } else if($nil_2[2]){
                    $nil_awal = $nil_2[2];
                } else if($nil_3[1]){
                    $nil_awal = $nil_3[1];
                } else if($nil_3[2]){
                    $nil_awal = $nil_3[2];
                }

                if($nil_3[2]){
                    $nil_ahir = $nil_3[2];
                } else if($nil_3[1]){
                    $nil_ahir = $nil_3[1];
                } else if($nil_2[2]){
                    $nil_ahir = $nil_2[2];
                } else if($nil_2[1]){
                    $nil_ahir = $nil_2[1];
                } else if($nil_1[2]){
                    $nil_ahir = $nil_1[2];
                } else if($nil_1[1]){
                    $nil_ahir = $nil_1[1];
                }

                $nil_total = $nil_ahir - $nil_awal;
                $html .= '<td style="text-align:right;">'.number_format($nil_total).'</td>';
                $html .='</tr>';

                if($tg_a == 1) {
                    $nil_awal_tgl = $nil_awal;
                    $tg_a = 2;
                }
                $nil_ahir_tgl = $nil_ahir;
            }
            $nil_total_tgl = $nil_ahir_tgl - $nil_awal_tgl;
            $html .='<tr><td style="text-align:center;font-weight:bold;">TOTAL</td><td style="text-align:right;font-weight:bold;">'.number_format($nil_awal_tgl).'</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td style="text-align:right;font-weight:bold;">'.number_format($nil_ahir_tgl).'</td><td style="text-align:right;font-weight:bold;">'.number_format($nil_total_tgl).'</td></tr><tr><td colspan="8">&nbsp;</td></tr>';
                
        }
    }
    $html .='</table></div>';
} else {
    $html = 'TIDAKADA';
}
    
$mpdf = new mPDF('','A4-L');
$mpdf->WriteHTML($html);
$mpdf->Output('Running_Hour_Alat_Berat.pdf', 'D');

?>