<?php

include_once("../../libs/init.php");
include("mpdf.php");


function get_nilai_g1($looptgl,$g,$s){
    global $app_plan_id;    
    //GET NILAI START
    $sql_hasil = "  SELECT a.qch_id, b.qcd_group, b.qcd_seq, b.qcd_value, b.qcd_id
                    FROM qc_cb_header a LEFT JOIN (SELECT * FROM qc_cb_detail where qcd_status = 'N') b ON a.qch_id = b.qch_id
                    where a.qch_rec_stat = 'N' and qch_sub_plant = 'A' AND to_char(a.qch_date, 'YYYY-MM-DD') = '{$looptgl}' ";
    $qry_hasil = dbselect_plan_all($app_plan_id, $sql_hasil);
    if(is_array($qry_hasil)){
        foreach($qry_hasil as $r_hasil){
            $arr_hasil["$r_hasil[qcd_group]"]["$r_hasil[qcd_seq]"]["$r_hasil[qcd_id]"] = $r_hasil[qcd_value]; 
        }
    }
    //GET NILAI END


    if(is_array($arr_hasil[$g][$s])){
        $jmdata = count($arr_hasil[$g][$s]);
        $data = 0;
        foreach ($arr_hasil[$g][$s] as $id => $value) {
            $data += $value;
        }
        $out = $data/$jmdata;
    }else{
        $out = '&nbsp;';
    }

    return $out;
}


function show_parameter($iddept,$iddiv,$cat,$parent,$looptgl){
    global $app_plan_id;
    $sql = "SELECT * FROM qc_kpi_parameter WHERE kpi_dept = '$iddept' AND kpi_divisi = '$iddiv' AND kpi_cat = '$cat' AND kpi_parent = '$parent' ORDER BY kpi_id ASC";
    $qry = dbselect_plan_all($app_plan_id, $sql);
    $out = '';
    if(is_array($qry)){
        foreach($qry as $r){
            $arr_nilai["$r[kpi_id]"] = $r[kpi_desc].'@@'.$r[kpi_bobot].'@@'.$r[kpi_satuan].'@@'.$r[kpi_target].'@@'.$r[kpi_periode]; 
        }
    }

    if(is_array($arr_nilai)){
        $no = 'A';
        foreach ($arr_nilai as $id => $value) {
            
            $sql_count = "SELECT count(*) AS jml FROM qc_kpi_parameter WHERE kpi_dept = '$iddept' AND kpi_divisi = '$iddiv' AND kpi_cat = '$cat' AND kpi_parent = '$id'";
            $qry_count = dbselect_plan($app_plan_id, $sql_count);
            $jml_sub   = $qry_count['jml'];

            $val = explode('@@', $value);



            //DEPARTEMEN 1
            if($id == 2){
                $hasil = '<td style="text-align:right;">'.get_nilai_g1($looptgl,1,2).'</td>';
            }
            elseif($id == 3){
                $hasil = '<td style="text-align:right;">'.get_nilai_g1($looptgl,1,3).'</td>';
            }elseif($id == 4){
                $hasil = '<td style="text-align:right;">'.get_nilai_g1($looptgl,1,4).'</td>';
            }elseif($id == 5){
                $hasil = '<td>&nbsp;</td>';
            }elseif($id == 6){
                $hasil = '<td style="text-align:right;">'.get_nilai_g1($looptgl,1,5).'</td>';
            }elseif($id == 8){
                $hasil = '<td style="text-align:right;">'.get_nilai_g1($looptgl,2,2).'</td>';
            }elseif($id == 9){
                $hasil = '<td style="text-align:right;">'.get_nilai_g1($looptgl,2,3).'</td>';
            }elseif($id == 10){
                $hasil = '<td style="text-align:right;">'.get_nilai_g1($looptgl,2,4).'</td>';
            }elseif($id == 12){
                $hasil = '<td style="text-align:right;">'.get_nilai_g1($looptgl,3,1).'</td>';
            }elseif($id == 13){
                $hasil = '<td style="text-align:right;">'.get_nilai_g1($looptgl,3,2).'</td>';
            }

            //DEPARTEMEN 2
            elseif($id == 34){
                $hasil = '<td style="text-align:right;">'.get_nilai_g1($looptgl,3,2).'</td>';
            }












            else{
                $hasil = '<td>&nbsp;</td>';
            }








            if($parent == 0){
                $out .= '<tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <th>'.$no.'</th>
                            <th style="text-align:left;">'.$val[0].'</th>
                            <th>'.$val[1].'</th>
                            <th>'.$val[2].'</th>
                            <th>'.$val[3].'</th>
                            <th>'.$val[4].'</th>
                            '.$hasil.'
                            <td>&nbsp;</td>
                          </tr>';
             $no++;
            }else{
                $out .= '<tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>- '.$val[0].'</th>
                            <td style="text-align:center;">'.$val[1].'</td>
                            <td style="text-align:center;">'.$val[2].'</td>
                            <td style="text-align:center;">'.$val[3].'</td>
                            <td style="text-align:center;">'.$val[4].'</td>
                            '.$hasil.'
                            <td>&nbsp;</td>
                          </tr>';
            }

            if($jml_sub > 0){
                $out .= show_parameter($iddept,$iddiv,$cat,$id,$looptgl);
            }
        }
    }else{
        $out .= '<tr><td colspan="10">&nbsp;</td></tr>';
    }

    return $out;
}

function cbodepartemen(){
    global $app_plan_id;
    $sql = "SELECT * FROM qc_kpi_dept ORDER BY iddept ASC";
    $qry = dbselect_plan_all($app_plan_id, $sql);
    $out = '';
    if(is_array($qry)){
        foreach($qry as $r){
            $out .= '<option value="'.$r[iddept].'">'.strtoupper($r[nmdept]).'</option>';
        }
    }
    echo $out;
}


global $app_plan_id, $nama_plan;
$dept     = $_GET['dept'];

$tanggal  = explode('@', $_GET['tanggal']);

$tglfrom  = cgx_dmy2ymd($tanggal[0]);
$tglfrom2 = cgx_dmy2ymd($tanggal[0])." 00:00:00";

$tglto    = cgx_dmy2ymd($tanggal[1]);
$tglto2   = cgx_dmy2ymd($tanggal[1])." 23:59:59";


$thn_awal  = substr($tglfrom,0,4);
$thn_akhir = substr($tglto,0,4);





$whdua = "";
if($dept <> 'All') {
    $whdua .= " and a.iddept = '".$dept."'";
}

$sql = "SELECT a.iddept, a.nmdept, b.iddivisi, b.nmdivisi FROM qc_kpi_dept a LEFT JOIN qc_kpi_divisi b ON a.iddept = b.iddept WHERE 1=1 $whdua ";
$qry = dbselect_plan_all($app_plan_id, $sql);
if(is_array($qry)){
    foreach($qry as $r){
        $arr_dept["$r[iddept]"] = $r[nmdept]; 
        $arr_div["$r[iddept]"]["$r[iddivisi]"] = $r[nmdivisi]; 
    }
}

if(is_array($arr_div)) {
    $html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;} .str{ mso-number-format:\@; } </style>';


    for ($looptgl=$tglfrom; $looptgl <= $tglto; $looptgl++) { 
        $tgl1 = explode('-', $looptgl);
        $fulltgl = $tgl1[2].'-'.$tgl1[1].'-'.$tgl1[0];

        $namahari = date('D', strtotime($looptgl));

        $html .='
                  <div style="text-align:center;font-size:14px;font-weight:bold;">PT ARWANA CITRAMULIA TBK & ANAK PERUSAHAAN</div>
                  <div style="text-align:center;font-size:14px;font-weight:bold;">KEY PERFORMANCE INDICATOR (KPI) '.$tgl1[0].'</div>
                  <div style="text-align:center;font-size:14px;font-weight:bold;">UNTUK PENILAIAN INSENTIF PRODUKSI</div>
                ';  

        foreach ($arr_div as $iddept => $a_divisi) {
            foreach ($a_divisi as $iddiv => $nmdiv) {
                $html .='
                          <div style="text-align:left;font-size:12px;font-weight:bold;">DEPARTEMEN : '.strtoupper($arr_dept[$iddept]).'</div>
                          <div style="text-align:left;font-size:12px;font-weight:bold;">DIVISI : '.strtoupper($nmdiv).'</div>
                          <div style="text-align:left;font-size:12px;font-weight:bold;">HARI / TANGGAL : '.strtoupper(conversi_hari($namahari)).' / '.$fulltgl.'</div>
                        ';
                
                $html .= '<div style="overflow-x:auto;"><table class="adaborder" id="tbl01" '.$border.'>';
                $html .= '<tr>
                            <th colspan="6">&nbsp;</th>
                            <th colspan="2">SASARAN</th>
                            <th colspan="2">REALISASI</th>
                          </tr>';

                $html .= '<tr>
                            <th>NO</th>
                            <th>CATEGORY</th>
                            <th colspan="2">DESKRIPSI</th>
                            <th>BOBOT (%)</th>
                            <th>SATUAN PENGUKURAN</th>
                            <th>TARGET</th>
                            <th>PERIODE / WAKTU</th>
                            <th>HASIL</th>
                            <th>PERIODE / WAKTU</th>
                          </tr>';

                $sql2 = "SELECT distinct kpi_cat FROM qc_kpi_parameter WHERE kpi_dept = '$iddept' AND kpi_divisi = '$iddiv' ORDER BY kpi_cat ASC";
                $qry2 = dbselect_plan_all($app_plan_id, $sql2);
                if(is_array($qry2)){
                    $no = '1';
                    foreach($qry2 as $r2){
                        if($r2['kpi_cat'] == 1){
                            $catval = 'QUALITY';
                        }else{
                            $catval = 'QUANTITY';
                        }


                        $html .= '<tr>
                                    <th>'.$no.'</th>
                                    <th>'.$catval.'</th>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                  </tr>';
                        $html .= show_parameter($iddept,$iddiv,$r2['kpi_cat'],0,$looptgl);
                    $no++;
                    }
                }   

                $html .='</table></div>';
                $html .='<br><br><br>';
            }
        }

        $tglfrom = date('Y-m-d', strtotime('+1 days', strtotime($looptgl)));
    }
    
} else {
    $html = 'TIDAKADA';
}

$mpdf = new mPDF('','A4-L');
$mpdf->WriteHTML($html);
$mpdf->Output('KPI_REPORT_'.$arr_dept[$iddept].'.pdf', 'D');

?>