<?php

include_once("../libs/init.php");
global $app_id;
$nama_plan = $_SESSION[$app_id]['user']['plan_nama'];
if ($_GET["mode"]) {
    $oper = $_GET["mode"];
} else {
    $oper = $_POST["oper"];
}

switch ($oper) {
    case "urai":
        urai();
        break;
    case "excel":
        excel();
        break;
    case "lihatdata":
        lihatdata();
        break;
    case "cbosubplant":
        cbosubplant($_GET['withselect']);
        break;
}

function urai()
{
    global $app_plan_id, $nama_plan;
    $subplan = $_GET['subplan'];
    $tanggal = explode('@', $_GET['tanggal']);
    $tglfrom = cgx_dmy2ymd($tanggal[0]) . " 00:00:00";
    $tglto = cgx_dmy2ymd($tanggal[1]) . " 23:59:59";
    $tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0] . ' s/d ' . $tanggal[1];
    $whdua = "";
    $_htmlTableHead1 = '';
    $_htmlTableHead2 = '';
    if ($subplan <> 'All') {
        $whdua .= " and a.qgh_sub_plant = '" . $subplan . "'";
        $_row = 18;
        $_htmlTableHead1 = '<th colspan="' . ($_row / 1) . '">RUNHOUR GENSET ' . $app_plan_id . $subplan . '</th>';
        for ($j = 1; $j <= 3; $j++) {
            $_htmlTableHead2 .= '<th>Run Hour ' . $subplan . $j . '</td>'
                . '<th>Rata<sup>2</sup> Pemakaian</th>'
                . '<th>Meter Solar ' . $subplan . $j . '</td>'
                . '<th>Rata<sup>2</sup> Pemakaian</th>'
                . '<th>Waktu Pemanasan ' . $subplan . $j . '</td>'
                . '<th>Rata<sup>2</sup> Pemakaian</th>';
        }
    } else {
        $_row = 54;
        for ($i = 'A'; $i <= 'C'; $i++) {
            $_htmlTableHead1 .= '<th colspan="' . ($_row / 3) . '">RUNHOUR GENSET ' . $app_plan_id . $i . '</th>';
            for ($j = 1; $j <= 3; $j++) {
                $_htmlTableHead2 .= '<th>Run Hour ' . $app_plan_id . $i . '-' . $j . '</td>'
                    . '<th>Rata<sup>2</sup> Pemakaian</th>'
                    . '<th>Meter Solar ' . $app_plan_id . $i . '-' . $j . '</td>'
                    . '<th>Rata<sup>2</sup> Pemakaian</th>'
                    . '<th>Waktu Pemanasan ' . $app_plan_id . $i . '-' . $j . '</td>'
                    . '<th>Rata<sup>2</sup> Pemakaian</th>';
            }
        }
    }

    $html = '
            <style>
                table {
                    border-collapse: collapse;
                    width: 100%;
                }
                .adaborder td ,
                .adaborder th{
                    border:1px solid #d1d1d1;
                }
                table.adaborder th,
                table.adaborder th{
                    background: #fff;
                    padding: 8px 16px;
                }
                table.adaborder tr th,
                table.adaborder tr th[colspan="18"],
                table.adaborder tr td.fixed-table
                {
                    z-index: 20;
                    position: sticky;   
                    background: white;             
                }
                table.adaborder tr th{
                    top: 30px;
                }
                table.adaborder tr td.fixed-table{
                    left: 0;
                }
                table.adaborder tr th[colspan="18"],
                table.adaborder thead tr th[rowspan="2"]{
                    top: 0;
                    left: 0;
                    z-index: 21;
                    position: sticky;   
                    background: white;     
                }
            </style>
            <div style="text-align:center;font-size:20px;font-weight:bold;">RUNHOUR GENSET</div>
            <div style="text-align:center;font-weight:bold;"> TGL : ' . $tgljudul . '</div>
          ';
    $html .= '
        <div style="overflow: auto;height: 450px">
            <table class="adaborder">
                <thead>
                    <tr align="center">
                        <th rowspan="2" class="fixed-table">Tanggal</th>'
        . $_htmlTableHead1
        . '</tr>'
        . '<tr align="center">'
        . $_htmlTableHead2
        . '</tr>
                </thead>';

    $sql = "SELECT 
                to_char(a.qgh_date::date,'yyyy-mm-dd') AS dates,
                a.qgh_date::time AS hours,
                a.qgh_sub_plant AS subplan, 
                a.qgh_date, 
                b.qgh_run_hour,
                b.qgh_warming,
                b.qgh_meter_solar, 
                a.qgh_id 
		FROM qc_genset_runhour_header_test a 
		JOIN qc_genset_runhour_detail_test b ON(a.qgh_id=b.qgh_id) 
		WHERE a.qgh_rec_status IS NULL AND a.qgh_date >= '{$tglfrom}' AND a.qgh_date <= '{$tglto}' $whdua 
		GROUP BY a.qgh_sub_plant,a.qgh_date,b.qgh_run_hour,qgh_meter_solar,qgh_warming, a.qgh_id
		ORDER BY dates, qgh_sub_plant, qgh_id";
    $qry = dbselect_plan_all($app_plan_id, $sql);
    $_flag = 0;
    $ranges = array();
    $_tempDateStart = date("Y-m-d", strtotime(substr($tglfrom, 0, 10)));
    $_tempDateEnd = date("Y-m-d", strtotime(substr($tglto, 0, 10)));
    $total = array();
    while (strtotime($_tempDateStart) <= strtotime($_tempDateEnd)) {
        if ($subplan == 'All') {
            for ($i = 'A'; $i <= 'C'; $i++) {
                for ($j = 0; $j < 3; $j++) {
                    $ranges[$_tempDateStart][$i]['qgh_meter_solar'][$j] = 0;
                    $ranges[$_tempDateStart][$i]['avg_meter_solar'][$j] = 0;
                    $ranges[$_tempDateStart][$i]['qgh_run_hour'][$j] = '-';
                    $ranges[$_tempDateStart][$i]['avg_run_hour'][$j] = '-';
                    $ranges[$_tempDateStart][$i]['qgh_warming'][$j] = '-';
                    $ranges[$_tempDateStart][$i]['avg_warming'][$j] = '-';
                }
            }
        } else {
            for ($j = 0; $j < 3; $j++) {
                $ranges[$_tempDateStart][$subplan]['qgh_meter_solar'][$j] = 0;
                $ranges[$_tempDateStart][$subplan]['avg_meter_solar'][$j] = 0;
                $ranges[$_tempDateStart][$subplan]['qgh_run_hour'][$j] = '-';
                $ranges[$_tempDateStart][$subplan]['avg_run_hour'][$j] = '-';
                $ranges[$_tempDateStart][$subplan]['qgh_warming'][$j] = '-';
                $ranges[$_tempDateStart][$subplan]['avg_warming'][$j] = '-';
            }
        }
        foreach ($qry as $keys => $values) // loop untuk setiap data record
        {
            if ($qry[$keys]['dates'] === $_tempDateStart) // jika ditemukan data dengan tanggal yang sama
            {
                $_flag = 1;
                $qry[$keys]['qgh_meter_solar'] = array_map("intval", explode(',', $qry[$keys]['qgh_meter_solar'])); // pecah dan ubah data genset menjadi integer
                $qry[$keys]['qgh_run_hour'] = explode(',', $qry[$keys]['qgh_run_hour']);
                $qry[$keys]['qgh_warming'] = explode(',', $qry[$keys]['qgh_warming']);
                $ranges[$_tempDateStart][$qry[$keys]['subplan']] = $qry[$keys];// push data kedalam array key tanggal
            }
        }
        $_tempDateStart = date("Y-m-d", strtotime("+1 days", strtotime($_tempDateStart)));
    }

    // Start print records
    $_flag = array();
    foreach ($ranges as $keys => $values) // loop dates
    {
        $html .= '<tr><td class="fixed-table">' . date('d-m-Y', strtotime($keys)) . '</td>';
        foreach ($values as $key => $value) // loop subplant
        {
            if (array_key_exists('qgh_run_hour', $value)) {
                foreach ($value['qgh_run_hour'] as $k => $val) // loop
                {
                    if ($val !== '-') {
                        $_exp = explode(' - ', $val);
                        $_exp = array_map(function ($x) {
                            return DateTime::createFromFormat("d/m/Y H:i", $x);
                        }, $_exp);
                        $v = date_diff($_exp[0], $_exp[1]);
                        $html .= '<td>' . ((((($v->d) * 24) + $v->h) * 60) + $v->i) . ' menit</td>';

                    } else {
                        $html .= '<td>-</td>';
                    }
                    if (is_null($_flag[$key][$k])) {
                        $_flag[$key][$k] = 0;
//                        $html .= '<td bgcolor="#faebd7">'.$_flag[$key][$k].'</td>';
                        $html .= '<td bgcolor="#faf0e6">-</td>';
                    } else {
//                        $html .= '<td bgcolor="#faebd7">'.($val-$_flag[$key][$k]).'</td>';
                        $html .= '<td bgcolor="#f0ffff">-</td>';
                        $_flag[$key][$k] = $val;
                    }
                }
            }
            if (array_key_exists('qgh_meter_solar', $value)) {
                foreach ($value['qgh_meter_solar'] as $k => $val) {
                    $html .= '<td>' . $val . '</td>';
                    if (is_null($_flag[$key][$k])) {
                        $_flag[$key][$k] = 0;
//                        $html .= '<td bgcolor="#faebd7">'.$_flag[$key][$k].'</td>';
                        $html .= '<td bgcolor="#faf0e6">-</td>';
                    } else {
//                        $html .= '<td bgcolor="#faebd7">'.($val-$_flag[$key][$k]).'</td>';
                        $html .= '<td bgcolor="#f0ffff">-</td>';
                        $_flag[$key][$k] = $val;
                    }
                }
            }
            if (array_key_exists('qgh_warming', $value)) {
                foreach ($value['qgh_warming'] as $k => $val) {
                    if ($val !== '-') {
                        $_exp = explode(' - ', $val);
                        $_exp = array_map(function ($x) {
                            return DateTime::createFromFormat("d/m/Y H:i", $x);
                        }, $_exp);
                        $v = date_diff($_exp[0], $_exp[1]);
                        $html .= '<td>' . ((($v->d) * 24) + $v->h) . ' jam ' . $v->i . ' menit</td>';
                    } else {
                        $html .= '<td>-</td>';
                    }
                    if (is_null($_flag[$key][$k])) {
                        $_flag[$key][$k] = 0;
//                        $html .= '<td bgcolor="#faebd7">'.$_flag[$key][$k].'</td>';
                        $html .= '<td bgcolor="#faf0e6">-</td>';
                    } else {
//                        $html .= '<td bgcolor="#faebd7">'.($val-$_flag[$key][$k]).'</td>';
                        $html .= '<td bgcolor="#f0ffff">-</td>';
                        $_flag[$key][$k] = $val;
                    }
                }
            }
        }
        $html .= '</tr>';
    }
    $responce->flag = $_flag;
    $responce->data = $ranges;
    // End print records

    $html .= '</table>'; // end of html table tags
    $responce->detailtabel = $html; // tampung dan tampilkan data kedalam html
    echo json_encode($responce);
}

function excel()
{
    require_once("../libs/PHPExcel.php");
    global $app_plan_id, $nama_plan;
    $subplan = $_GET['subplan'];
    $tanggal = explode('@', $_GET['tanggal']);
    $tglfrom = cgx_dmy2ymd($tanggal[0]) . " 00:00:00";
    $tglto = cgx_dmy2ymd($tanggal[1]) . " 23:59:59";
    $tgljudul = $tanggal[0] == $tanggal[1] ? $tanggal[0] : $tanggal[0] . ' s/d ' . $tanggal[1];
    $whdua = "";
    if ($subplan <> 'All') {
        $whdua .= " and a.qgh_sub_plant = '" . $subplan . "'";
    }
    $sql = "SELECT a.qgh_sub_plant as subplan, a.qgh_date, qid_deep_wheel2, qid_deep_wheel3, qid_data_mushola, qid_kolam, qid_glazing_line, a.qgh_id,qid_pdam
		from qc_air_header a 
		join qc_air_detail b on(a.qgh_id=b.qgh_id) 
		where a.qgh_rec_status is null and a.qgh_date >= '{$tglfrom}' and a.qgh_date <= '{$tglto}' $whdua 
		order by qgh_sub_plant, qgh_date, qgh_id";
    $qry = dbselect_plan_all($app_plan_id, $sql);
    $i = 0;
    if (is_array($qry)) {
        foreach ($qry as $r) {
            $datetime = explode(' ', $r[qlh_date]);
            $r[tgl] = cgx_dmy2ymd($datetime[0]);
            $r[jam] = substr($datetime[1], 0, 5);
            $arr_baris["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[qlh_id]"] = $r[qlh_id] . "@@" . $r[qlh_cap_bank_1] . "@@" . $r[qlh_cap_bank_2] . "@@" . $r[qlh_cap_bank_3];
            $arr_kolom["$r[subplan]"]["$r[grup]"] = '';
            $arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[qlh_id]"]["$r[grup]"] = $r[qld_r] . "@@" . $r[qld_s] . "@@" . $r[qld_t] . "@@" . $r[qld_v] . "@@" . $r[qld_watt_hour];
            $arr_data_air["$r[qgh_id]"] = $r[qgh_id] . "@@" . $r[subplan] . "@@" . $r[qid_deep_wheel2];
            $i++;
        }
    }
    if (is_array($arr_baris)) {
        foreach ($arr_baris as $subplan => $a_tgl) {
            foreach ($arr_kolom[$subplan] as $grup => $value) {
                $arr_tot_kol[$subplan] += 3;
            }
        }
        $icell = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "AA", "AB", "AC", "AD", "AE", "AF", "AG", "AH", "AI", "AJ", "AK", "AL", "AM", "AN", "AO", "AP", "AQ", "AR", "AS", "AT", "AU", "AV", "AW", "AX", "AY", "AZ", "BA", "BB", "BC", "BD", "BE", "BF", "BG", "BH", "BI", "BJ", "BK", "BL", "BM", "BN", "BO", "BP", "BQ", "BR", "BS", "BT", "BU", "BV", "BW", "BX", "BY", "BZ", "CA", "CB", "CC", "CD", "CE", "CF", "CG", "CH", "CI", "CJ", "CK", "CL", "CM", "CN", "CO", "CP", "CQ", "CR", "CS", "CT", "CU", "CV", "CW", "CX", "CY", "CZ", "DA", "DB", "DC", "DD", "DE", "DF", "DG", "DH", "DI", "DJ", "DK", "DL", "DM", "DN", "DO", "DP", "DQ", "DR", "DS", "DT", "DU", "DV", "DW", "DX", "DY", "DZ");
        $nama_kolam = array('Kolam_1', 'Kolam_2', 'Kolam_3', 'Kolam_4', 'Kolam_5a', 'Kolam_6a', 'Kolam_6b', 'Kolam_6c');
        $oexcel = new PHPExcel();
        $coltitleSy = new PHPExcel_Style();
        $coltitleSy->applyFromArray(array(
            'font' => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        ));
        $colborder = new PHPExcel_Style();
        $colborder->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        ));
        $colboldunder = new PHPExcel_Style();
        $colboldunder->applyFromArray(array(
            'font' => array(
                'bold' => true,
                'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        ));
        $coltengah = new PHPExcel_Style();
        $coltengah->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        ));
        $colboldaja = new PHPExcel_Style();
        $colboldaja->applyFromArray(array(
            'font' => array(
                'bold' => true
            )
        ));
        $oexcel->getProperties()->setCreator("Dimas")
            ->setLastModifiedBy("Dimas");
        $si = $oexcel->setActiveSheetIndex(0);

        $baris = 1;
        $si->mergeCells($icell[0] . $baris . ':' . $icell[7] . $baris);
        $si->setCellValue($icell[0] . $baris, 'PEMAKAIAN Air');
        $baris++;
        $si->mergeCells($icell[0] . $baris . ':' . $icell[7] . $baris);
        $si->setCellValue($icell[0] . $baris, 'TGL : ' . $tgljudul);
        $si->setSharedStyle($coltitleSy, $icell[0] . ($baris - 1) . ':' . $icell[7] . $baris);
        $baris += 4;
        $zcell = 0;
        $akolspan = $arr_tot_kol[$subplan];
        $baris++;
        $si->mergeCells($icell[0] . $baris . ':' . $icell[0] . ($baris + 2));
        $si->setCellValue($icell[0] . $baris, 'TANGGAL');
        $si->mergeCells($icell[1] . $baris . ':' . $icell[8] . $baris);
        $si->setCellValue($icell[1] . $baris, 'FLOW METER');
        $si->mergeCells($icell[9] . $baris . ':' . $icell[16] . $baris);
        $si->setCellValue($icell[9] . $baris, 'Daftar Level Kolam');

        $nexcel = 2;
        $baris++;
        $nexcel = 3;
        ksort($arr_kolom[$subplan]);
        reset($arr_kolom[$subplan]);
        $si->mergeCells($icell[1] . $baris . ':' . $icell[1] . ($baris + 1));
        $si->setCellValue($icell[1] . $baris, 'Deep Wheel 2 (5c)');
        $nexcel++;
        $si->mergeCells($icell[2] . $baris . ':' . $icell[2] . ($baris + 1));
        $si->setCellValue($icell[2] . $baris, 'Deep Wheel 3 (5a)');
        $nexcel++;
        $si->mergeCells($icell[3] . $baris . ':' . $icell[3] . ($baris + 1));
        $si->setCellValue($icell[3] . $baris, 'Mushola,Kantin,Mess	');
        $nexcel++;
        $si->mergeCells($icell[4] . $baris . ':' . $icell[4] . ($baris + 1));
        $si->setCellValue($icell[4] . $baris, 'Galzing Line');
        $nexcel++;
        $si->mergeCells($icell[5] . $baris . ':' . $icell[7] . $baris);
        $si->setCellValue($icell[5] . $baris, 'Water Tank');
        $nexcel++;
        $si->mergeCells($icell[8] . $baris . ':' . $icell[8] . ($baris + 1));
        $si->setCellValue($icell[8] . $baris, 'PDAM');
        $nexcel++;
        $si->mergeCells($icell[9] . $baris . ':' . $icell[9] . ($baris + 1));
        $si->setCellValue($icell[9] . $baris, 'Kolam 1');
        $nexcel++;
        $si->mergeCells($icell[10] . $baris . ':' . $icell[10] . ($baris + 1));
        $si->setCellValue($icell[10] . $baris, 'Kolam 2');
        $nexcel++;
        $si->mergeCells($icell[11] . $baris . ':' . $icell[11] . ($baris + 1));
        $si->setCellValue($icell[11] . $baris, 'Kolam 3');
        $nexcel++;
        $si->mergeCells($icell[12] . $baris . ':' . $icell[12] . ($baris + 1));
        $si->setCellValue($icell[12] . $baris, 'Kolam 4');
        $nexcel++;
        $si->mergeCells($icell[13] . $baris . ':' . $icell[13] . ($baris + 1));
        $si->setCellValue($icell[13] . $baris, 'Kolam 5');
        $nexcel++;
        $si->mergeCells($icell[14] . $baris . ':' . $icell[14] . ($baris + 1));
        $si->setCellValue($icell[14] . $baris, 'Kolam 6a');
        $nexcel++;
        $si->mergeCells($icell[15] . $baris . ':' . $icell[15] . ($baris + 1));
        $si->setCellValue($icell[15] . $baris, 'Kolam 6b');
        $nexcel++;
        $si->mergeCells($icell[16] . $baris . ':' . $icell[16] . ($baris + 1));
        $si->setCellValue($icell[16] . $baris, 'Kolam 6c');
        $nexcel++;
        $si->mergeCells('R' . '7' . ':' . 'R' . ('7' + 2));
        $si->setCellValue('R' . '7', 'PARAF TTD PETUGAS');
        $baris = 9;
        $si->mergeCells($icell[5] . $baris . ':' . $icell[5] . $baris);
        $si->setCellValue($icell[5] . $baris, 'Plan A');
        $si->mergeCells($icell[6] . $baris . ':' . $icell[6] . $baris);
        $si->setCellValue($icell[6] . $baris, 'Plan B');
        $si->mergeCells($icell[7] . $baris . ':' . $icell[7] . $baris);
        $si->setCellValue($icell[7] . $baris, 'Plan C');


        $si->setSharedStyle($coltitleSy, $icell[0] . ($baris - 2) . ':' . $icell[($nexcel)] . $baris);
        $baris = 10;
        foreach ($qry as $data_air) {
            $subplana = $data_air[subplan] == 'A' ? $data_air[subplan] : '-';
            $subplanb = $data_air[subplan] == 'B' ? $data_air[subplan] : '-';
            $subplanc = $data_air[subplan] == 'C' ? $data_air[subplan] : '-';
            $tanggal = explode(" ", $data_air[qgh_date]);
            $data_kolam = explode(",", $data_air[qid_kolam]);
            $si->setCellValue($icell[0] . $baris, $tanggal[0]);
            $si->setCellValue($icell[1] . $baris, $data_air[qid_deep_wheel2]);
            $si->setCellValue($icell[2] . $baris, $data_air[qid_deep_wheel3]);
            $si->setCellValue($icell[3] . $baris, $data_air[qid_data_mushola]);
            $si->setCellValue($icell[4] . $baris, $data_air[qid_glazing_line]);
            $si->setCellValue($icell[5] . $baris, $subplana);
            $si->setCellValue($icell[6] . $baris, $subplanb);
            $si->setCellValue($icell[7] . $baris, $subplanc);
            $si->setCellValue($icell[8] . $baris, $data_air[qid_pdam]);
            $bariss = 9;
            foreach ($data_kolam as $no_kolam => $d) {
                $si->setCellValue($icell[$bariss] . $baris, $data_kolam[$no_kolam]);
                $bariss++;
            }
            $baris++;
        }


        $html .= '</table></div>';
        $si->setTitle('Sheet1');
        $oexcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if ($_GET["tipe"] == "xlsx") {
            header('Content-Disposition: attachment;filename=Pemakaian_Air.xlsx');
        } else {
            header('Content-Disposition: attachment;filename=Pemakaian_Air.xls');
        }
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $objWriter = PHPExcel_IOFactory::createWriter($oexcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    } else {
        $html = 'Tidak Ada Data';
    }
    // echo $html;
    $responce->detailtabel = $html;
    echo json_encode($responce);
}

function lihatdata()
{
    global $app_plan_id;
    $qbh_id = $_POST['qbh_id'];
    $sql = "SELECT a.*
		FROM qc_bm_header a
		WHERE a.qbh_id = '{$qbh_id}'";
    $rh = dbselect_plan($app_plan_id, $sql);
    $out = '<table class="table table-striped table-bordered table-condensed"><tr><td>ID : ' . $rh[qbh_id] . '</td></tr><tr><td>Subplant : ' . $rh[qid_sub_plant] . '</td></tr><tr><td>Tanggal : ' . cgx_dmy2ymd($rh[qid_date]) . '</td></tr><tr><td>Shift : ' . $rh[qbh_shift] . '</td><td>Di-input Oleh : ' . $rh[qbh_user_create] . '</td></tr><tr><td>Nomor Ballmill : ' . $rh[qbh_bm_no] . '</td><td>Tanggal Input : ' . $rh[qid_date_create] . '</td></tr><tr><td>Kapasitas : ' . number_format($rh[qbh_volume]) . '</td><td>Di-edit Oleh : ' . $rh[qbh_user_modify] . '</td></tr><tr><td>Kode Body : ' . $rh[qbh_body_code] . '</td><td>Tanggal Edit : ' . $rh[qid_date_modify] . '</td></tr></table>';
    $responce->hasil = $out;
    echo json_encode($responce);

}

function cbosubplant($withselect = false)
{
    $out = $withselect ? "<select>" : "";
    $out .= cbo_subplant("TIDAKADA", true);
    $out .= $withselect ? "</select>" : "";
    echo $out;
}

function formatDateDiff(DateInterval $interval)
{
    $format = array();
    if ($interval->y !== 0) {
        array_push($format, "%y tahun");
    }
    if ($interval->m !== 0) {
        array_push($format, "%m bulan");
    }
    if ($interval->d !== 0) {
        array_push($format, "%d hari");
    }
    if ($interval->h !== 0) {
        array_push($format, "%h jam");
    }
    if ($interval->i !== 0) {
        array_push($format, "%i menit");
    }
    print_r(var_dump($interval->format($format)));

}

?>