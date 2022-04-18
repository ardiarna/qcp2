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
$sql = "SELECT a.qbh_sub_plant as subplan, a.qbh_body_code as kodebody, b.qbd_material_type as tipe, b.qbd_material_code as item_kode, c.item_nama, sum(b.qbd_value) as nilai
    from qc_bm_header a
    join qc_bm_detail b on(a.qbh_id=b.qbh_id)
    join item c on(b.qbd_material_code=c.item_kode)
    left join qc_box_unit d on(a.qbh_sub_plant=d.qbu_sub_plant and b.qbd_box_unit=d.qbu_kode) where a.qbh_rec_status='N' and a.qbh_date >= '{$tglfrom}' and a.qbh_date <= '{$tglto}' $whdua 
    group by a.qbh_sub_plant, a.qbh_body_code, b.qbd_material_type, b.qbd_material_code, c.item_nama
    order by subplan, kodebody, tipe, item_kode";
$qry = dbselect_plan_all($app_plan_id, $sql);
if(is_array($qry)){
    foreach($qry as $r){
        $r[tipe] = $r[tipe] == "1" ? "MATERIAL" : "ADDITIVE";
        $arr_baris["$r[subplan]"]["$r[kodebody]"]["$r[tipe]"]["$r[item_kode]"] = $r[item_nama]."@@".$r[nilai];
    }
}
if(is_array($arr_baris)) {
    $html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
    $html .= '<div style="text-align:center;font-size:20px;font-weight:bold;">PENIMBANGAN MATERIAL BODY (SUMMARY)</div><table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.',</td><td>Shift : </td><td>'.$shift.'</td></tr></table>';
    $html .= '<div style="overflow-x:auto;"><table class="adaborder">';
    foreach ($arr_baris as $subplan => $a_kodebody) {
        foreach ($a_kodebody as $kodebody => $a_tipe) {
            $html .= '<tr><th colspan="2">SUBPLANT : '.$subplan.'</th><th colspan="2">KODE BODY : '.$kodebody.'</th></tr>';
            $html .= '<tr><th>NO.</th><th>ITEM KODE</th><th>NAMA MATERIAL</th><th>TOTAL MATERIAL & ADDITIVE</th></tr>';
            foreach ($a_tipe as $tipe => $a_item_kode) {
                $no = 1;
                foreach ($a_item_kode as $item_kode => $nil_bar) {
                    $brs = explode("@@",$nil_bar);
                    $html .='<tr><td style="text-align:center;">'.$no.'</td><td>'.$item_kode.'</td><td style="white-space: nowrap">'.$brs[0].'</td><td style="text-align:right;">'.number_format($brs[1],2).'</td>';
                    $no++;
                }
                if($tipe == 'MATERIAL') {
                    $html .='<tr><td colspan="2" style="text-align:center;font-weight:bold;">ADDITIVE</td><td></td><td></td></tr>';
                }
            }
            $html .='<tr><td colspan="4">&nbsp;</td></tr>';
        }
    }
    $html .='</table></div>';
} else {
    $html = 'TIDAKADA';
}
    
$mpdf = new mPDF('','A4-L');
$mpdf->WriteHTML($html);
$mpdf->Output('Penimbangan_Material_Body_Summ.pdf', 'D');

?>