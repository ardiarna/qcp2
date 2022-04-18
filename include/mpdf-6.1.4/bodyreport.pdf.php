<?php

include_once("../../libs/init.php");
include("mpdf.php");

$subplan = $_GET['subplan'];
$shift = $_GET['shift'];
$tanggal = explode('@', $_GET['tanggal']);
$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
$whdua = "";
if($subplan <> 'All') {
    $whdua .= " and a.qbh_sub_plant = '".$subplan."'";
}
if($shift <> 'All') {
    $whdua .= " and a.qbh_shift = '".$shift."'";
}
$sql = "SELECT a.qbh_sub_plant as subplan, a.qbh_body_code as kodebody, b.qbd_material_type as tipe, b.qbd_material_code as item_kode, c.item_nama as item_nama, d.qbu_kode as box, b.qbd_formula as formula, b.qbd_dw as dw, b.qbd_mc as mc, b.qbd_ww as ww, b.qbd_value as nilai, b.qbd_remark as remark, a.qbh_shift as shift, a.qbh_bm_no as balmil, a.qbh_user_create, a.qbh_date_create, a.qbh_date, a.qbh_id
    from qc_bm_header a
    join qc_bm_detail b on(a.qbh_id=b.qbh_id)
    join item c on(b.qbd_material_code=c.item_kode)
    left join qc_box_unit d on(a.qbh_sub_plant=d.qbu_sub_plant and b.qbd_box_unit=d.qbu_kode) where a.qbh_rec_status='N' and a.qbh_date >= '{$tglfrom}' and a.qbh_date <= '{$tglto}' $whdua
    order by subplan, kodebody, tipe, item_kode, qbh_date, shift, balmil, qbh_id, box";
$qry = dbselect_plan_all($app_plan_id, $sql);
$i = 0;
if(is_array($qry)){
    foreach($qry as $r){
        $r[tipe] = $r[tipe] == "1" ? "MATERIAL" : "ADDITIVE";
        $arr_baris["$r[subplan]"]["$r[kodebody]"]["$r[tipe]"]["$r[item_kode]"]["$r[box]"] = $r[item_nama]."@@".$r[box]."@@".$r[formula]."@@".$r[dw]."@@".$r[mc]."@@".$r[ww];
        $arr_kolom["$r[subplan]"]["$r[kodebody]"]["$r[qbh_date]"]["$r[shift]"]["$r[balmil]"]["$r[qbh_id]"] = '';
        $arr_nilai["$r[subplan]"]["$r[kodebody]"]["$r[tipe]"]["$r[item_kode]"]["$r[box]"]["$r[qbh_date]"]["$r[shift]"]["$r[balmil]"]["$r[qbh_id]"] = $r[qbh_id]."@@".$r[nilai]."@@".$r[box]."@@".$r[formula]."@@".$r[dw]."@@".$r[mc]."@@".$r[ww]; 
        $i++;
    }
}
if(is_array($arr_baris)) {
    foreach ($arr_baris as $subplan => $a_kodebody) {
        foreach ($a_kodebody as $kodebody => $a_tipe) {
            foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
                foreach ($a_shift as $shift => $a_balmil) {
                    foreach ($a_balmil as $balmil => $a_qbh_id) {
                        $arr_kol_shift[$subplan][$kodebody][$qbh_date][$shift] += count($a_qbh_id);
                        $arr_kol_date[$subplan][$kodebody][$qbh_date] += count($a_qbh_id);
                        $arr_tot_kol[$subplan][$kodebody] += count($a_qbh_id);
                    }
                }
            }
        }
    }
    $html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
    $html .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.1002.QC.01</div><div style="text-align:center;font-size:20px;font-weight:bold;">PENIMBANGAN MATERIAL BODY</div><table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
    $html .= '<div style="overflow-x:auto;"><table class="adaborder" id="tbl01">';
    foreach ($arr_baris as $subplan => $a_kodebody) {
        foreach ($a_kodebody as $kodebody => $a_tipe) {
            $html .= '<tr><th colspan="2">SUBPLANT : '.$subplan.'</th><th colspan="6">KODE BODY : '.$kodebody.'</th>';
            ksort($arr_kolom[$subplan][$kodebody]);
            reset($arr_kolom[$subplan][$kodebody]);
            foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
                $html .= '<th colspan="'.$arr_kol_date[$subplan][$kodebody][$qbh_date].'">'.$qbh_date.'</th>';
            }
            $html .= '<th rowspan="3">TOTAL MATERIAL & ADDITIVE</th></tr>';
            $html .= '<tr><th rowspan="2">NO.</th><th rowspan="2">ITEM KODE</th><th rowspan="2">NAMA MATERIAL</th><th rowspan="2">NO. BOX</th><th rowspan="2">FORMULA (%)</th><th rowspan="2">DW (kg)</th><th rowspan="2">MC (%)</th><th rowspan="2">WW (kg)</th>';
            ksort($arr_kolom[$subplan][$kodebody]);
            reset($arr_kolom[$subplan][$kodebody]);
            foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
                ksort($a_shift);
                reset($a_shift);    
                foreach ($a_shift as $shift => $a_balmil) {
                    $html .= '<th colspan="'.$arr_kol_shift[$subplan][$kodebody][$qbh_date][$shift].'">SHIFT-'.$shift.'</th>';
                }
            }
            $html .= '</tr><tr>';
            ksort($arr_kolom[$subplan][$kodebody]);
            reset($arr_kolom[$subplan][$kodebody]);
            foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
                ksort($a_shift);
                reset($a_shift);
                foreach ($a_shift as $shift => $a_balmil) {
                    ksort($a_balmil);
                    reset($a_balmil);
                    foreach ($a_balmil as $balmil => $a_qbh_id) {
                        foreach ($a_qbh_id as $qbh_id => $value) {
                            $html .= '<th>'.$balmil.'</th>';
                        }       
                    }
                }
            }
            $html .= '</tr>';
            foreach ($a_tipe as $tipe => $a_item_kode) {
                $no = 1;
                $tot_nil = array();
                foreach ($a_item_kode as $item_kode => $a_box) {
                    foreach ($a_box as $box => $nil_bar) {
                        $tot_bar_nil = 0;
                        $brs = explode("@@",$nil_bar);
                        $html .='<tr><td style="text-align:center;">'.$no.'</td><td>'.$item_kode.'</td><td style="white-space: nowrap">'.$brs[0].'</td><td style="text-align:center;">'.$brs[1].'</td><td style="text-align:right;">'.number_format($brs[2],2).'</td><td style="text-align:right;">'.number_format($brs[3],2).'</td><td style="text-align:right;">'.number_format($brs[4],2).'</td><td style="text-align:right;">'.number_format($brs[5],2).'</td>';
                        ksort($arr_kolom[$subplan][$kodebody]);
                        reset($arr_kolom[$subplan][$kodebody]);
                        foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
                            ksort($a_shift);
                            reset($a_shift);
                            foreach ($a_shift as $shift => $a_balmil) {
                                ksort($a_balmil);
                                reset($a_balmil);
                                foreach ($a_balmil as $balmil => $a_qbh_id) {
                                    foreach ($a_qbh_id as $qbh_id => $value) {
                                        $nilai = $arr_nilai[$subplan][$kodebody][$tipe][$item_kode][$box][$qbh_date][$shift][$balmil][$qbh_id];
                                        if($nilai) {
                                            $nil = explode("@@",$nilai);
                                            $html .= '<td style="text-align:right;" onclick="lihatData(\''.$qbh_id.'\')">'.number_format($nil[1],2).'</td>';
                                            $tot_bar_nil += round($nil[1],2);
                                            $tot_nil[$qbh_date][$shift][$balmil][$qbh_id] += round($nil[1],2);
                                        } else {
                                            $html .= '<td></td>';
                                        }
                                    }           
                                }
                            }
                        }
                        $html .= '<td style="text-align:right;font-weight:bold;background-color:#88fcb2;">'.number_format($tot_bar_nil,2).'</td>';
                        $html .='</tr>';
                        $no++;
                    }
                }
                if($tipe == 'MATERIAL') {
                    $html .='<tr><td></td><td colspan="2" style="text-align:center;font-weight:bold;">TOTAL '.$tipe.'</td><td></td><td></td><td></td><td></td><td></td>';
                    ksort($arr_kolom[$subplan][$kodebody]);
                    reset($arr_kolom[$subplan][$kodebody]);
                    foreach ($arr_kolom[$subplan][$kodebody] as $qbh_date => $a_shift) {
                        ksort($a_shift);
                        reset($a_shift);
                        foreach ($a_shift as $shift => $a_balmil) {
                            ksort($a_balmil);
                            reset($a_balmil);
                            foreach ($a_balmil as $balmil => $a_qbh_id) {
                                foreach ($a_qbh_id as $qbh_id => $value) {
                                    $nilai = $tot_nil[$qbh_date][$shift][$balmil][$qbh_id];
                                    if($nilai) {
                                        $html .= '<td style="text-align:right;font-weight:bold;background-color:#edf765;">'.number_format($nilai,2).'</td>';
                                    } else {
                                        $html .= '<td></td>';
                                    }   
                                }       
                            }
                        }
                    }
                    $html .='<td style="text-align:right;font-weight:bold;background-color:#88fcb2;"></td></tr>';
                }
            }
            $html .='<tr><td colspan="'.($arr_tot_kol[$subplan][$kodebody]+9).'">&nbsp;</td></tr>';
        }
    }
    $html .='</table></div>';
} else {
    $html = 'TIDAKADA';
}
    
$mpdf = new mPDF('','A4-L');
$mpdf->WriteHTML($html);
$mpdf->Output('Penimbangan_Material_Body.pdf', 'D');

?>