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
        $whdua .= " and a.qih_sub_plant = '".$subplan."'";
    }
    $sql = "SELECT a.qih_sub_plant as subplan, a.qih_date, qid_deep_wheel2, qid_deep_wheel3, qid_data_mushola, qid_kolam, qid_glazing_line, a.qih_id,qid_pdam
        from qc_air_header a 
        join qc_air_detail b on(a.qih_id=b.qih_id) 
        where a.qih_rec_status is null and a.qih_date >= '{$tglfrom}' and a.qih_date <= '{$tglto}' $whdua 
        order by qih_sub_plant, qih_date, qih_id";
    $qry = dbselect_plan_all($app_plan_id, $sql);
    $i = 0;
    
    
        $html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
        $html .= '<div style="text-align:center;font-size:20px;font-weight:bold;">PEMAKAIAN AIR</div><table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
        $html .= '<div style="overflow-x:auto;"><table class="adaborder">';
        $html .= '<tr align=center>';
        $html .= '<td rowspan=3 align="center">Tanggal</td><td colspan=8 align="center">FLOW METER</td><td colspan=8 align="center">DAFTAR LEVEL KOLAM</td><td rowspan=3>PARAF TTD PETUGAS</td>';
        $html .= '</tr>';
        $html .= '<tr align="center">';
        $html .= '<td rowspan=2>Deep Wheel 3<br>(5a)</td><td rowspan=2>Deep Wheel 2<br>(5c)</td><td rowspan=2>Mushola,Kantin,Mess</td><td rowspan=2>Glazing Line 2A</td><td colspan=3 >Water Tank</td><td rowspan=2>PDAM</td><td rowspan=2>Kolam 1</td><td rowspan=2>Kolam 2</td><td rowspan=2>Kolam 3</td><td rowspan=2>Kolam 4</td><td rowspan=2>Kolam 5a</td><td rowspan=2>Kolam 6a</td><td rowspan=2>Kolam 6b</td><td rowspan=2>Kolam 6c</td>';
        $html .= '</tr>';
        $html .= '<tr align="center">';
        $html .= '<td >Plan a</td><td >Plan b</td><td >Plan c</td>';
        $html .= '</tr>';
    if(is_array($qry)){
        foreach($qry as $r){
            $datetime = explode(' ',$r[qih_date]);
            $Kolam = explode(',',$r[qid_kolam]);
            $r[tgl] = cgx_dmy2ymd($datetime[0]);
            $r[jam] = substr($datetime[1],0,5);
            $tanggal=$r[qih_date];
            if($r[subplan]=='A'){$spa= "A";}else{$spa= "-";}
            if($r[subplan]=='B'){$spb= "B";}else{$spb= "-";}
            if($r[subplan]=='C'){$spc= "C";}else{$spc= "-";}
        $html .= '<tr align="center">';
        $html .= '<td >'.$r[tgl].'</td><td >'.$r[qid_deep_wheel3].'</td><td >'.$r[qid_deep_wheel2].'</td><td >'.$r[qid_data_mushola].'</td><td >'.$r[qid_glazing_line].'</td><td >'.$spa.'</td><td >'.$spb.'</td><td >'.$spc.'</td><td >'.$r[pid_pdam].'</td><td >'.$Kolam[0].'</td><td >'.$Kolam[1].'</td><td >'.$Kolam[2].'</td><td >'.$Kolam[3].'</td><td >'.$Kolam[4].'</td><td >'.$Kolam[5].'</td><td >'.$Kolam[6].'</td><td >'.$Kolam[7].'</td><td></td>';
        $html .= '</tr>';
        
        }
    }
    $html .= '</table>';

$mpdf = new mPDF('','A4-L');
$mpdf->WriteHTML($html);
$mpdf->Output('Pemakaian_Listrik.pdf', 'D');

?>