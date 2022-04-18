<?php

include_once("../../libs/init.php");
include("mpdf.php");

global $app_plan_id;
$arr_qly = array('2' => 'EKONOMI', '4' => 'KW IV');
    $subplan = $_GET['subplan'];
    $shift   = $_GET['shift'];
    $tanggal = explode('@', $_GET['tanggal']);
    $tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
    $tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
    $tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
    $whdua = "";
    if($subplan <> 'All') {
        $whdua .= "and a.rg_sub_plant = '".$subplan."'";
    }

    if($shift <> 'All') {
        $whdua .= "and a.rg_shift = '".$shift."'";
    }

    $sql = "SELECT a.rg_sub_plant AS subplan, a.rg_id, a.rg_date, a.rg_shift, a.rg_line, b.rg_qly, b.rg_per_2h, c.qmd_nama as defect_nama, a.rg_motif
        FROM qc_fg_rg_header a 
        JOIN qc_fg_rg_detail b on a.rg_id = b.rg_id
        LEFT JOIN qc_md_defect c on b.rg_defect_kode = c.qmd_kode
        WHERE a.rg_status='N' AND a.rg_date >= '{$tglfrom}' AND a.rg_date <= '{$tglto}' AND b.rg_qly IN(2,4,5) $whdua
        ORDER BY a.rg_sub_plant, a.rg_date, a.rg_line, a.rg_shift, c.qmd_nama, b.rg_qly, a.rg_motif, a.rg_id ASC";
    $responce->sql = $sql; 
    $qry = dbselect_plan_all($app_plan_id, $sql);
    $arr_n_rg_per_2h  = array();
    if(is_array($qry)) {
        foreach($qry as $r){
            $datetime = explode(' ',$r[rg_date]);
            $r[tgl] = cgx_dmy2ymd($datetime[0]);
            $r[jam] = substr($datetime[1],0,2);
            if($r[rg_qly] == '5') {
                $r[rg_qly] = '4';
            }
            if($r[rg_motif] == '') {
                $r[rg_motif] = '[...]';
            }
            $arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[rg_line]"]["$r[rg_shift]"]["$r[defect_nama]"]["$r[rg_qly]"]["$r[rg_motif]"]["$r[rg_id]"] += $r[rg_per_2h];
            $arr_motif["$r[subplan]"]["$r[tgl]"]["$r[rg_line]"]["$r[rg_qly]"]["$r[rg_motif]"] = '';
        }
    }

    if(is_array($arr_nilai)) {
        $html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
        $html .= '<div style="text-align:right;font-size:13px;font-weight:bold;"></div>
                  <div style="text-align:center;font-size:20px;font-weight:bold;">DATA ANALISA DEFECT</div>
                  <table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
        foreach ($arr_nilai as $subplan => $a_tgl) {
            foreach ($a_tgl as $tgl => $a_line) {
                foreach ($a_line as $line => $a_shift) {
                    $html .= '<table><tr><td>Plant </td><td> : '.$app_plan_id.$subplan.'</td></tr><tr><td>Tanggal </td><td> : '.$tgl.'</td></tr><tr><td>Line </td><td> : '.$line.'</td></tr></table>';
                    $html .= '<div style="overflow-x:auto;"><table class="adaborder">';
                    $html .= '<tr><th rowspan="3" style="text-align:center;background: #D1D1D1;">SHIFT</th>
                        <th rowspan="3" style="text-align:center;background: #D1D1D1;">DEFECT</th>
                        <th colspan="'.count($arr_motif[$subplan][$tgl][$line]['4']).'" style="text-align:center;background: #D1D1D1;">'.$arr_qly['4'].'</th>
                        <th rowspan="3" style="text-align:center;background: #D1D1D1;">KETERANGAN</th>
                        <th colspan="'.count($arr_motif[$subplan][$tgl][$line]['2']).'" style="text-align:center;background: #D1D1D1;">'.$arr_qly['2'].'</th>
                        <th rowspan="3" style="text-align:center;background: #D1D1D1;">KETERANGAN</th>
                    </tr>
                    <tr>
                        <th colspan="'.count($arr_motif[$subplan][$tgl][$line]['4']).'" style="text-align:center;background: #D1D1D1;">Motive</th>
                        <th colspan="'.count($arr_motif[$subplan][$tgl][$line]['2']).'" style="text-align:center;background: #D1D1D1;">Motive</th>
                    </tr>
                    <tr>';
                    foreach ($arr_motif[$subplan][$tgl][$line]['4'] as $motif => $nol) {
                        $html .= '<th style="text-align:center;background: #D1D1D1;">'.$motif.'</th>';
                    }
                    foreach ($arr_motif[$subplan][$tgl][$line]['2'] as $motif => $nol) {
                        $html .= '<th style="text-align:center;background: #D1D1D1;">'.$motif.'</th>';
                    }
                    $html .= '</tr>';
                    foreach ($a_shift as $shift => $a_defect_nama) {
                        $v_a = count($a_defect_nama) + 1;
                        $html .= '<tr><td style="text-align:center;" rowspan="'.$v_a.'">'.Romawi($shift).'</td></tr>';
                        foreach ($a_defect_nama as $defect_nama => $a_rg_qly) {
                            $html .= '<tr>
                                <td>'.$defect_nama.'</td>';
                            foreach ($arr_motif[$subplan][$tgl][$line]['4'] as $motif => $nol) {
                                $html .= '<td style="text-align:right;">';
                                if(is_array($a_rg_qly['4'][$motif])) {
                                    $qty = 0;
                                    foreach ($a_rg_qly['4'][$motif] as $rg_id => $rg_per_2h) {
                                        $qty += $rg_per_2h; 
                                    }
                                    $arr_qty[$subplan][$tgl][$line][$shift]['4'][$motif] += $qty;
                                    $qtyStr = number_format($qty);
                                } else {
                                    $qtyStr = '';
                                }
                                $html .= $qtyStr.'</td>';
                            }
                            $html .= '<td></td>';
                            foreach ($arr_motif[$subplan][$tgl][$line]['2'] as $motif => $nol) {
                                $html .= '<td style="text-align:right;">';
                                if(is_array($a_rg_qly['2'][$motif])) {
                                    $qty = 0;
                                    foreach ($a_rg_qly['2'][$motif] as $rg_id => $rg_per_2h) {
                                        $qty += $rg_per_2h; 
                                    }
                                    $arr_qty[$subplan][$tgl][$line][$shift]['2'][$motif] += $qty;
                                    $qtyStr = number_format($qty);
                                } else {
                                    $qtyStr = '';
                                }
                                $html .= $qtyStr.'</td>';
                            }
                            $html .= '<td></td>';
                            $html .= '</tr>';   
                        }
                        $html .= '<tr><td></td><td style="text-align:center;font-weight:bold;">Total</td>';
                        foreach ($arr_motif[$subplan][$tgl][$line]['4'] as $motif => $nol) {
                            $html .= '<td style="text-align:right;font-weight:bold;">'.number_format($arr_qty[$subplan][$tgl][$line][$shift]['4'][$motif]).'</td>';
                        }
                        $html .= '<td></td>';
                        foreach ($arr_motif[$subplan][$tgl][$line]['2'] as $motif => $nol) {
                            $html .= '<td style="text-align:right;font-weight:bold;">'.number_format($arr_qty[$subplan][$tgl][$line][$shift]['2'][$motif]).'</td>';
                        }
                        $html .= '<td></td>';
                        $html .= '</tr>';
                    }
                    $html .='</table></div><br>';
                }
            }
        }
    } else {
        $html = 'TIDAKADA';
    }

$mpdf = new mPDF('','F4');
$mpdf->WriteHTML($html);
$mpdf->Output('Data_rendement_group.pdf', 'D');

?>