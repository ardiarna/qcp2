<?php

include_once("../../libs/init.php");
include("mpdf.php");

global $app_plan_id;
$subplan = $_GET['subplan'];
$shift   = $_GET['hph_shift'];
$press   = $_GET['hph_press'];
$line    = $_GET['hph_line'];

$tanggal = explode('@', $_GET['tanggal']);
$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
$tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0].' s/d '.$tanggal[1];

$whdua = "";
if($subplan <> 'All') {
    $whdua .= "and hph_sub_plant = '".$subplan."'";
}

if($shift <> 'All') {
    $whdua .= "and hph_shift = '".$shift."'";
}

if($press <> 'All') {
    $whdua .= "and hph_press = '".$press."'";
}

if($line <> 'All') {
    $whdua .= "and hph_line = '".$line."'";
}

$sql = "SELECT hph_sub_plant as subplan, hph_date, hph_shift , hph_press, hph_line, hph_id
        from qc_pd_hp_header
        where hph_status = 'N' $whdua and hph_date >= '{$tglfrom}' and hph_date <= '{$tglto}'
        ORDER BY hph_sub_plant, hph_date, hph_shift, hph_press, hph_line, hph_id ASC";

$responce->sql = $sql; 
$qry = dbselect_plan_all($app_plan_id, $sql);
if(is_array($qry)) {
    foreach($qry as $r){
        $datetime = explode(' ',$r[hph_date]);
        $r[tgl] = cgx_dmy2ymd($datetime[0]);
        $r[jam] = substr($datetime[1],0,2);

        $arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[hph_shift]"]["$r[hph_press]"]["$r[hph_line]"]["$r[hph_id]"] = $r[hph_id];
       
    }
}


if(is_array($arr_nilai)) {
    $html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
    $html .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.901.PP.14</div>
              <div style="text-align:center;font-size:20px;font-weight:bold;">LAPORAN HAMBATAN PRESS</div>
              <table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';

    foreach ($arr_nilai as $subplan => $a_tgl) {
        foreach ($a_tgl as $tgl => $a_shift) {
            foreach ($a_shift as $shift => $a_press) {
                foreach ($a_press as $press => $a_line) {
                    foreach ($a_line as $line => $a_id) {
                        $html .= '<div style="overflow-x:auto;"><table class="adaborder">';
                        $html .= '<tr>';
                        $html .= '   <th align="left" colspan="4">SUB PLANT : '.$subplan.' | TANGGAL : '.$tgl.'</th>';
                        $html .= '</tr>';
                        $html .= '<tr>';
                        $html .= '   <th align="left" colspan="4">SHIFT : '.$shift.' | PRESS : '.$press.' | LINE : '.$line.'</th>';
                        $html .= '</tr>';
                        $html .= '<tr>';
                        $html .= '   <th style="background:#D1D1D1;">JAM START</th>
                                     <th style="background:#D1D1D1;">JAM STOP</th>
                                     <th style="background:#D1D1D1;">JML MENIT</th>
                                     <th style="background:#D1D1D1;">KET. HAMBATAN</th>';
                        $html .= '</tr>';

                        foreach ($a_id as $iddata => $id) {
                                
                            $sql2 = "SELECT to_char(hpd_date_start, 'HH:MI') AS starttime, to_char(hpd_date_stop, 'HH:MI') AS stoptime, hpd_value
                                    from qc_pd_hp_detail where hph_id = '{$id}' ORDER BY hpd_date_start ASC";

                            $responce->sql = $sql2; 
                            $qry2 = dbselect_plan_all($app_plan_id, $sql2);
                            if(is_array($qry2)) {
                                foreach($qry2 as $r2){
                                    $jml = strtotime($r2[stoptime])-strtotime($r2[starttime]);
                                    $jmlmenit = $jml/60;

                                    $html .= '<tr>';
                                    $html .= '   <td style="text-align:center;"><span onclick="lihatData(\''.$id.'\')">'.$r2[starttime].'</span></td>
                                                 <td style="text-align:center;"><span onclick="lihatData(\''.$id.'\')">'.$r2[stoptime].'</span></td>
                                                 <td style="text-align:center;">'.$jmlmenit.'</td>
                                                 <td><span onclick="lihatData(\''.$id.'\')">'.nl2br(htmlspecialchars($r2[hpd_value])).'</span></td>';
                                    $html .= '</tr>';
                                }
                            }else{
                                    $html .= '<tr><td colspan="4">&nbsp;</td></tr>';
                            }
                        }

                        $html .= '</table></div>';
                        $html .= '<br><br>';
                    }
                }
            }
        }
    }
}else{
   $html = 'TIDAK ADA DATA';
}

$mpdf = new mPDF('','A4');
$mpdf->WriteHTML($html);
$mpdf->Output('LAPORAN HAMBATAN PRESS.pdf', 'D');

?>