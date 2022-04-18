<?php

include_once("../../libs/init.php");
include("mpdf.php");

$subplan = $_GET['subplan'];
$tanggal = explode('@', $_GET['tanggal']);
$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
$whdua = "";
if($subplan <> 'All') {
    $whdua .= " and a.qlh_sub_plant = '".$subplan."'";
}
$sql = "SELECT a.qlh_sub_plant as subplan, a.qlh_date, qlh_cap_bank_1, qlh_cap_bank_2, qlh_cap_bank_3, b.qld_group as grup, b.qld_r, b.qld_s, b.qld_t, b.qld_v, b.qld_watt_hour, a.qlh_id
    from qc_listrik_header a 
    join qc_listrik_detail b on(a.qlh_id=b.qlh_id) 
    where a.qlh_rec_status = 'N' and a.qlh_date >= '{$tglfrom}' and a.qlh_date <= '{$tglto}' $whdua 
    order by qlh_sub_plant, qlh_date, qld_group, qlh_id";
$qry = dbselect_plan_all($app_plan_id, $sql);
$i = 0;
if(is_array($qry)){
    foreach($qry as $r){
        $datetime = explode(' ',$r[qlh_date]);
        $r[tgl] = cgx_dmy2ymd($datetime[0]);
        $r[jam] = substr($datetime[1],0,5);
        $arr_baris["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[qlh_id]"] = $r[qlh_id]."@@".$r[qlh_cap_bank_1]."@@".$r[qlh_cap_bank_2]."@@".$r[qlh_cap_bank_3];
        $arr_kolom["$r[subplan]"]["$r[grup]"] = '';
        $arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[qlh_id]"]["$r[grup]"] = $r[qld_r]."@@".$r[qld_s]."@@".$r[qld_t]."@@".$r[qld_v]."@@".$r[qld_watt_hour];
        $i++;
    }
}
if(is_array($arr_baris)) {
    foreach ($arr_baris as $subplan => $a_tgl) {
        foreach ($arr_kolom[$subplan] as $grup => $value) {
            $arr_tot_kol[$subplan] += 5;
        }
    }
    $html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
    $html .= '<div style="text-align:center;font-size:20px;font-weight:bold;">PEMAKAIAN LISTRIK</div><table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
    $html .= '<div style="overflow-x:auto;"><table class="adaborder">';
    foreach ($arr_baris as $subplan => $a_tgl) {
        $html .= '<tr><th colspan="'.($arr_tot_kol[$subplan]+6).'" style="text-align:left;padding-top:20px;">SUBPLANT : '.$subplan.'</th></tr>';
        $html .= '<tr><th rowspan="2">No</th><th rowspan="2">Tanggal</th><th rowspan="2">Jam</th>';
        ksort($arr_kolom[$subplan]);
        reset($arr_kolom[$subplan]);
        foreach ($arr_kolom[$subplan] as $grup => $value) {
            $html .= '<th colspan="5">'.$grup.'</th>';
        }
        $html .= '<th rowspan="2">Cap Bank-1 Cos Q</th><th rowspan="2">Cap Bank-2 Cos Q</th><th rowspan="2">Cap Bank-3 Cos Q</th></tr><tr>';
        ksort($arr_kolom[$subplan]);
        reset($arr_kolom[$subplan]);
        foreach ($arr_kolom[$subplan] as $grup => $value) {
            $html .= '<th>R</th><th>S</th><th>T</th><th>V</th><th>Watt Hour Met</th>';
        }
        $html .= '</tr>';
        foreach ($a_tgl as $tgl => $a_jam) {
            $html .='<tr><td></td><td style="text-align:center;font-weight:bold;"><u>'.$tgl.'</u></td><td></td>';
            foreach ($arr_kolom[$subplan] as $grup => $value) {
                $html .= '<td></td><td></td><td></td><td></td><td></td>';
            }
            $html .= '<td></td><td></td><td></td></tr>';
            $no = 1;
            foreach ($a_jam as $jam => $a_qlh_id) {
                foreach ($a_qlh_id as $qlh_id => $nil_bar) {
                    $brs = explode("@@",$nil_bar);
                    $html .='<tr><td style="text-align:center;">'.$no.'</td><td style="text-align:center;">'.$tgl.'</td><td style="text-align:center;">'.$jam.'</td>';
                    ksort($arr_kolom[$subplan]);
                    reset($arr_kolom[$subplan]);
                    foreach ($arr_kolom[$subplan] as $grup => $value) {
                        $nilai = $arr_nilai[$subplan][$tgl][$jam][$qlh_id][$grup];
                        if($nilai) {
                            $nil = explode("@@",$nilai);
                            $html .= '<td style="text-align:right;" onclick="lihatData(\''.$qlh_id.'\')">'.$nil[0].'</td>';
                            $html .= '<td style="text-align:right;" onclick="lihatData(\''.$qlh_id.'\')">'.$nil[1].'</td>';
                            $html .= '<td style="text-align:right;" onclick="lihatData(\''.$qlh_id.'\')">'.$nil[2].'</td>';
                            $html .= '<td style="text-align:right;" onclick="lihatData(\''.$qlh_id.'\')">'.$nil[3].'</td>';
                            $html .= '<td style="text-align:right;" onclick="lihatData(\''.$qlh_id.'\')">'.$nil[4].'</td>';
                        } else {
                            $html .= '<td></td><td></td><td></td><td></td><td></td>';
                        }
                    }
                    $html .= '<td style="text-align:right;" onclick="lihatData(\''.$qlh_id.'\')">'.$brs[1].'</td>';
                    $html .= '<td style="text-align:right;" onclick="lihatData(\''.$qlh_id.'\')">'.$brs[2].'</td>';
                    $html .= '<td style="text-align:right;" onclick="lihatData(\''.$qlh_id.'\')">'.$brs[3].'</td>';
                    $html .='</tr>';
                    $no++;
                }
            }
            $html .='<tr><td></td><td>&nbsp;</td><td></td>';
            foreach ($arr_kolom[$subplan] as $grup => $value) {
                $html .= '<td></td><td></td><td></td><td></td><td></td>';
            }
            $html .= '<td></td><td></td><td></td></tr>';    
        }
    }
    $html .='</table></div>';
} else {
    $html = 'TIDAKADA';
}

$mpdf = new mPDF('','A4-L');
$mpdf->WriteHTML($html);
$mpdf->Output('Pemakaian_Listrik.pdf', 'D');

?>