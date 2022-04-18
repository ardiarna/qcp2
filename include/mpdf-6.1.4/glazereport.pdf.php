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
    $whdua .= " and a.qgh_sub_plant = '".$subplan."'";
}
$sql = "SELECT a.qgh_sub_plant as subplan, c.qggm_desc as grup, b.qgd_prep_seq as line, d.qgdm_control_desc as item_nama, f.qgu_code as unit, a.qgh_glaze_code as kodeglaze, a.qgh_bmg_no as balmil, b.qgd_prep_value as nilai, a.qgh_id 
    from qc_gp_header a
    join qc_gp_detail b on(a.qgh_id=b.qgh_id)
    join qc_gp_group_master c on(b.qgd_prep_group=c.qggm_group) 
    join qc_gp_detail_master d on(b.qgd_prep_group=d.qgdm_group and b.qgd_prep_seq=d.qgdm_seq)
    left join qc_gen_um f on(d.qgdm_um_id=f.qgu_id) where a.qgh_rec_stat='N' and b.qgd_prep_group='01' and a.qgh_date >= '{$tglfrom}' and a.qgh_date <= '{$tglto}' $whdua
    order by subplan, grup, line, kodeglaze, balmil, qgh_id";
$qry = dbselect_plan_all($app_plan_id, $sql);
$i = 0;
if(is_array($qry)){
    foreach($qry as $r){
        $arr_baris["$r[subplan]"]["$r[line]"] = $r[item_nama]."@@".$r[unit];
        $arr_kolom["$r[subplan]"]["$r[kodeglaze]"]["$r[balmil]"]["$r[qgh_id]"] = '';
        $arr_nilai["$r[subplan]"]["$r[line]"]["$r[kodeglaze]"]["$r[balmil]"]["$r[qgh_id]"] = $r[qgh_id]."@@".$r[nilai]; 
        $i++;
    }
}
if(is_array($arr_baris)) {
    foreach ($arr_baris as $subplan => $a_line) {
        foreach ($arr_kolom[$subplan] as $kodeglaze => $a_balmil) {
            foreach ($a_balmil as $balmil => $a_qgh_id) {
                $arr_kol_kodeglaze[$subplan][$kodeglaze] += count($a_qgh_id);
                $arr_tot_kol_kodeglaze[$subplan] += count($a_qgh_id);
            }
        }
    }
    $html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
    $html .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.1002.QC.03</div><div style="text-align:center;font-size:20px;font-weight:bold;">GLAZE PREPARATION</div><table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
    $html .= '<div style="overflow-x:auto;"><table class="adaborder" id="tbl01">';
    foreach ($arr_baris as $subplan => $a_line) {
        $html .= '<tr><th colspan="3">SUBPLANT : '.$subplan.'</th><th colspan="'.$arr_tot_kol_kodeglaze[$subplan].'">NO. BALLMILL / TYPE GLAZE</th></tr>';
        $html .= '<tr><th rowspan="2">NO.</th><th rowspan="2">DESKRIPSI</th><th rowspan="2">UNIT</th>';
        ksort($arr_kolom[$subplan]);
        reset($arr_kolom[$subplan]);
        foreach ($arr_kolom[$subplan] as $kodeglaze => $a_balmil) {
            $html .= '<th colspan="'.$arr_kol_kodeglaze[$subplan][$kodeglaze].'">'.$kodeglaze.'</th>';
        }
        $html .= '</tr><tr>';
        foreach ($arr_kolom[$subplan] as $kodeglaze => $a_balmil) {
            ksort($a_balmil);
            reset($a_balmil);
            foreach ($a_balmil as $balmil => $a_qgh_id) {
                foreach ($a_qgh_id as $qgh_id => $value) {
                    $html .= '<th>'.$balmil.'</th>';
                }       
            }
        }
        $html .= '</tr>';
        $no = 1;
        foreach ($a_line as $line => $nil_bar) {
            $brs = explode("@@",$nil_bar);
            $html .='<tr><td style="text-align:center;">'.$no.'</td><td style="white-space: nowrap">'.$brs[0].'</td><td style="text-align:center;">'.$brs[1].'</td>';
            ksort($arr_kolom[$subplan]);
            reset($arr_kolom[$subplan]);
            foreach ($arr_kolom[$subplan] as $kodeglaze => $a_balmil) {
                ksort($a_balmil);
                reset($a_balmil);
                foreach ($a_balmil as $balmil => $a_qgh_id) {
                    foreach ($a_qgh_id as $qgh_id => $value) {
                        $nilai = $arr_nilai[$subplan][$line][$kodeglaze][$balmil][$qgh_id];
                        if($nilai) {
                            $nil = explode("@@",$nilai);
                            if($line == '1' || $line == '2' || $line == '8' || $line == '9') {
                                $html .= '<td style="text-align:right;" onclick="lihatData(\''.$qgh_id.'\')">'.number_format($nil[1]).'</td>';
                            } else if($line == '5' || $line == '6' || $line == '7') {
                                $html .= '<td style="text-align:center;" onclick="lihatData(\''.$qgh_id.'\')">'.$nil[1].'</td>';
                            } else {
                                $html .= '<td style="text-align:right;" onclick="lihatData(\''.$qgh_id.'\')">'.number_format($nil[1],2).'</td>';
                            }
                        } else {
                            $html .= '<td></td>';
                        }
                    }           
                }
            }
            $html .='</tr>';
            $no++;
        }
        $html .='<tr><td colspan="'.($arr_tot_kol_kodeglaze[$subplan]+3).'">&nbsp;</td></tr>';
    }
    $html .='</table></div>';
} else {
    $html = 'Tidak Ada Data';
}

$mpdf = new mPDF('','A4-L');
$mpdf->WriteHTML($html);
$mpdf->Output('Glaze_Preparation.pdf', 'D');

?>