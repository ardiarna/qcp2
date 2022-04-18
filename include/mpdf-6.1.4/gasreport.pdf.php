<?php

include_once("../../libs/init.php");
include("mpdf.php");
require('Pivot.php');

function simpleHtmlTable($data)
{
    echo "<table border='1'>";
    echo "<thead>";
    foreach (array_keys($data[0]) as $item) {
        echo "<td><b>{$item}<b></td>";
    }
    echo "</thead>";
    foreach ($data as $row) {
        echo "<tr>";
        foreach ($row as $item) {
            echo "<td>{$item}</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

$grup = $_GET['grup'];
$subplan = $_GET['subplan'];
$tanggal = explode('@', $_GET['tanggal']);
$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
if($subplan <> 'All') {
    $arrsubplan = array($subplan);
} else {
    $arrsubplan = array('A','B','C');
}
$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];
$html .= $_SESSION[$app_id]['user']['plan_nama'].'<div style="text-align:center;font-size:20px;font-weight:bold;">PEMAKAIAN GAS</div><table style="margin:0 auto;"><tr><td>Tanggal : </td><td>'.$tgljudul.'</td></tr></table><br>';    
foreach ($arrsubplan as $subplannya) {
    $sql0 = "SELECT distinct b.qgd_mesin, c.qmu_seq, c.qmu_desc  
        from qc_gas_header a
        join qc_gas_detail b on(a.qgh_id=b.qgh_id)
        join qc_mesin_unit c on(b.qgd_mesin=c.qmu_code)
        where a.qgh_rec_stat = 'N'
        and a.qgh_date >= '{$tglfrom}' and a.qgh_date <= '{$tglto}' and a.qgh_sub_plant = '{$subplannya}'
        order by c.qmu_seq";
    $qry0 = dbselect_plan_all($app_plan_id, $sql0);
    if($qry0) {
    $html .= '<table><tr><td style="font-size:17px;font-weight:bold;">Subplant</td><td style="font-size:17px;font-weight:bold;">:</td><td style="font-size:17px;font-weight:bold;">'.$subplannya.'</td></tr></table>'; 
    foreach ($qry0 as $mesinya) {
        $vrline = array();
        $vrkolom = array();
        $nilai = array();
        $sql = "SELECT a.qgh_sub_plant as subplan, a.qgh_date as tanggal, c.qmu_desc as mesin, b.qgd_line as line, qgpd_desc as seq, b.qgd_value as nilai, a.qgd_user_create, a.qgd_date_create  
            from qc_gas_header a
            join qc_gas_detail b on(a.qgh_id=b.qgh_id)
            join qc_mesin_unit c on(b.qgd_mesin=c.qmu_code)
            join qc_gas_prep_detail d on(b.qgd_mesin=d.qgpd_mesin_code and b.qgd_seq=d.qgpd_seq)
            where a.qgh_rec_stat = 'N'
            and a.qgh_date >= '{$tglfrom}' and a.qgh_date <= '{$tglto}' and a.qgh_sub_plant = '{$subplannya}' and b.qgd_mesin = '{$mesinya[qgd_mesin]}'
            order by subplan, tanggal, c.qmu_seq, line, b.qgd_seq";
        $qry = dbselect_plan_all($app_plan_id, $sql);
        if($qry) {
            foreach ($qry as $r) { 
                $nilai["$r[tanggal]"]["$r[seq]"]["$r[line]"] .= " ".$r[nilai]; 
            }
            $data = Pivot::factory($qry)
                ->pivotOn(array('tanggal'))
                ->addColumn(array('line','seq'), array('nilai'))
                ->fetch();
            $html .= '<table><tr><td>Nama Mesin</td><td>:</td><td>'.$mesinya[qmu_desc].'</td></tr></table>';
            $html .= '<table class="adaborder" style="margin-bottom:15px;">';
            $judul = array('tanggal' => 'Tanggal');
            $jmlkolom = intval(count($data[0]))-4;
            $html .= '<tr>';
            foreach ($data[0] as $key => $val) {
                if($key <> '_id') {
                    if($judul[$key]) {
                        $html .= '<th rowspan="2">'.$judul[$key].'</th>';
                    } else {
                        $judul2 = explode('_', $key);
                        if($judul2[0]) {
                            $vrline[$judul2[0]] += 1;
                        }
                    }
                }
            }
            foreach ($vrline as $key => $val) {
                $html .= '<th colspan="'.($val+3).'">Line '.$key.'</th>';    
            }   
            $html .= '</tr>';
            $html .= '<tr>';
            foreach ($data[0] as $key => $val) {
                if($key <> '_id') {
                    if($judul[$key]) {
                    } else {
                        $judul2 = explode('_', $key);
                        if($judul2[1]) {
                            $vrkolom[$judul2[0]][$judul2[1]] += 1;    
                        }
                    }
                }
            }
            foreach ($vrkolom as $line => $vrseq) {
                foreach ($vrseq as $seq => $val) {
                    $html .= '<th>'.$seq.'</th>';
                }
                $html .= '<th>Koreksi</th>';
                $html .= '<th>NM<sup>3</sup></th>';
                $html .= '<th>Kcal</th>';    
            }
            $html .= '</tr>';
            $totvolume = array();
            $totnm3 = array();
            $totkcal = array();  
            foreach ($data as $r) {
                $html .= '<tr>';
                $val1 = explode(' ', $r[tanggal]);
                $html .= '<td style="text-align:center;">'.$val1[0].'</td>';
                foreach ($vrkolom as $line => $vrseq) {
                    foreach ($vrseq as $seq => $val) {
                        if($seq == 'Ukuran') {
                            $html .= '<td style="text-align:right;">'.$nilai[$r[tanggal]][$seq][$line].'</td>';
                        } else if($seq == 'Volume') {
                            $html .= '<td style="text-align:right;">'.number_format($r[$line."_".$seq."_nilai"],2).'</td>';
                            $totvolume[$line] += $r[$line."_".$seq."_nilai"];
                        } else {
                            $html .= '<td style="text-align:right;">'.number_format($r[$line."_".$seq."_nilai"],2).'</td>';
                        }
                    }
                    $koreksi = ((1.01325+$r[$line."_Tekanan_nilai"])/1.01325)*(300/(273+$r[$line."_Temperatur_nilai"]))*(1+(0.002*$r[$line."_Tekanan_nilai"]));
                    $nm3 = $koreksi*$r[$line."_Volume_nilai"];
                    $kcal = $nm3*8802; 
                    $html .= '<td style="text-align:right;">'.number_format($koreksi,6).'</td>';
                    $html .= '<td style="text-align:right;">'.number_format($nm3,2).'</td>';
                    $html .= '<td style="text-align:right;">'.number_format($kcal,2).'</td>';  
                    $totnm3[$line] += $nm3;
                    $totkcal[$line] += $kcal;   
                }
                
                $html .= '</tr>';
            }
            $html .= '<tr>';
            $html .= '<td style="text-align:center;"><strong>TOTAL<strong></td>';
            foreach ($vrkolom as $line => $vrseq) {
                foreach ($vrseq as $seq => $val) {
                    if($seq == 'Volume') {
                        $html .= '<td style="text-align:right;">'.number_format($totvolume[$line],2).'</td>';
                    } else {
                        $html .= '<td style="text-align:right;"></td>';
                    }
                }
                $html .= '<td style="text-align:right;"></td>';
                $html .= '<td style="text-align:right;">'.number_format($totnm3[$line],2).'</td>';
                $html .= '<td style="text-align:right;">'.number_format($totkcal[$line],2).'</td>';     
            }
            $html .= '</tr>';
            $html .= '</table>';
        }
    }
    }
}
// echo $html;

$mpdf = new mPDF('','A4-L');

$mpdf->WriteHTML($html);
$mpdf->Output();

?>