<link rel="stylesheet" href="../ss/bootstrap.min.css">
<?php

include_once("../../libs/init.php");
include("mpdf.php");


global $app_plan_id;
$subplan = $_GET['subplan'];
$arr_kolom = array("08" => "08", "11" => "11", "14" => "14", "17" => "17", "20" => "20", "22" => "22", "24" => "24", "03" => "03", "06" => "06");
$subplan = $_GET['subplan'];
$tglfrom = cgx_dmy2ymd($_GET['tanggal'])." 00:00:00";
$tgljudul  = $_GET['tanggal'];
$cmh_press = $_GET['cmh_press'];

$qcek = "SELECT COUNT(*) AS jmldata from qc_pd_cm_header WHERE cmh_status = 'N' AND cmh_sub_plant = '{$subplan}' AND cmh_date >= '{$tglfrom}' AND cmh_press = '{$cmh_press}' ";
$dcek = dbselect_plan($app_plan_id, $qcek);

if($dcek[jmldata] <= 0){
    $html = 'TIDAK ADA DATA';
}else{

    $html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
    $html .= '<table style="margin:0 auto;font-size:11px;" width="100%">
                <tr>
                    <td align="left">
                        <table>
                            <tr>
                                <th colspan="3">&nbsp;</th>
                            </tr>
                            <tr>
                                <th colspan="3">&nbsp;</th>
                            </tr>
                            <tr>
                                <th align="left">LAPORAN HARIAN</th>
                                <th>:</th>
                                <th></th>
                            </tr>
                            <tr>
                                <th align="left">MESIN PRESS NOMOR</th>
                                <th>:</th>
                                <th align="left">'.$cmh_press.'</th>
                            </tr>
                            <tr>
                                <th align="left">TANGGAL</th>
                                <th>:</th>
                                <th align="left">'.$tgljudul.'</th>
                            </tr>
                        </table>
                    </td>
                    <td class="text-right">
                        <table align="right">
                            <tr>
                                <th align="left">NO.</th>
                                <th align="left">:</th>
                                <th align="left">F.901.00.12</th>
                            </tr>
                            <tr>
                                <th colspan="3">&nbsp;</th>
                            </tr>
                            <tr>
                                <th align="left">OPERATOR SHIFT</th>
                                <th>:</th>
                                <th align="left">SHIFT I</th>
                                <th>:</th>
                                <th>. . . . . . . . . .</th>
                            </tr>
                            <tr>
                                <th align="left"></th>
                                <th>:</th>
                                <th align="left">SHIFT II</th>
                                <th>:</th>
                                <th>. . . . . . . . . .</th>
                            </tr>
                            <tr>
                                <th align="left"></th>
                                <th>:</th>
                                <th align="left">SHIFT III</th>
                                <th>:</th>
                                <th>. . . . . . . . . .</th>
                            </tr>
                        </table>
                    </td>
                </tr>
              </table>';
    $html .= '<div style="overflow-x:auto;"><table class="adaborder" style="font-size:11px;">';
    $html .= '<tr><th>JAM PEMERIKSAAN</th>';
                foreach ($arr_kolom as $kolom => $kolom_nama) {
                    $html .= '<th>'.$kolom.':00</th>';
                }
    $html .= '</tr>';
    $html .= '<tr><th colspan="10" style="font-size:16px;">PENGONTROLAN FUNGSI MESIN</th></tr>';

    $sql = "SELECT cm_group, cm_desc from qc_pd_cm_group order by CAST(cm_group AS int) ASC";
    $qry = dbselect_plan_all($app_plan_id, $sql);
    foreach($qry as $r) {
        $html .= '<tr><th colspan="10" style="font-size:16px;">'.$r[cm_desc].'</th></tr>';
            
            $sql2 = "SELECT * from qc_pd_cm_group_d1 WHERE cm_group = '{$r[cm_group]}' order by CAST(cd1_id AS int) ASC";
            $qry2 = dbselect_plan_all($app_plan_id, $sql2);

            $no_d1 = 'A';
            foreach($qry2 as $r2) {
                $html .= '<tr><th colspan="10" style="font-size:14px;">'.$no_d1.'. '.$r2[cd1_desc].'</th></tr>';


                $sql3 = "SELECT * from qc_pd_cm_group_d2 WHERE sub_plant = '{$subplan}' and cm_group = '{$r[cm_group]}' and cd1_id = '{$r2[cd1_id]}' order by CAST(cd2_id AS int) ASC";
                $qry3 = dbselect_plan_all($app_plan_id, $sql3);
                foreach($qry3 as $r3) {
                    $html .= '<tr>';
                    $html .= '<td><b>'.$r3[cd2_desc].'</b></td>';
                    foreach ($arr_kolom as $kolom => $kolom_nama) { 

                        $tglfrom2 = cgx_dmy2ymd($_GET['tanggal']).' '.$kolom.":00:00";

                        $sql4 = "SELECT a.cmh_id, b.cmd_value 
                                 FROM qc_pd_cm_header a left join qc_pd_cm_detail b on a.cmh_id = b.cmh_id
                                 WHERE a.cmh_sub_plant = '{$subplan}' and b.cm_group = '{$r[cm_group]}' 
                                 and b.cd1_id = '{$r2[cd1_id]}' and b.cd2_id = '{$r3[cd2_id]}' 
                                 and a.cmh_date = '{$tglfrom2}' ";
                        $qry4 = dbselect_plan($app_plan_id, $sql4);


                        $html .= '<td align="center">';
                        if($r3[cd2_type] == 'option'){
                            if($qry4[cmd_value] == "Y"){
                                $html .= '<img src="../../css/images/check.png">';
                            }else if($qry4[cmd_value] == "N"){
                                $html .= '<img src="../../css/images/remove.png">';
                            }else{
                                $html .= $qry4[cmd_value];
                            }
                        }else{
                            $html .= $qry4[cmd_value];
                        }
                        $html .= '</td>';

                    }
                    $html .= '</tr>';
                }


            $no_d1++;
            }
    }
    $html .= '</table></div>';

}

$mpdf = new mPDF('','A4');
$mpdf->WriteHTML($html);
$mpdf->Output('PENGONTROLAN FUNGSI MESIN.pdf', 'D');

?>