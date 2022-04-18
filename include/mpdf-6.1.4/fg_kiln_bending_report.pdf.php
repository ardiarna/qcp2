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
        $whdua .= "and a.kb_sub_plant = '".$subplan."'";
    }
    $sql = "SELECT a.kb_sub_plant as subplan, a.kb_date, a.kb_kiln, a.kb_temp, a.kb_speed, a.kb_presi, a.kb_desc, a.kb_wa, a.kb_ac, a.kb_wm, a.kb_tt, b.kbd_posisi, b.kbd_kg, b.kbd_cm, a.kb_id
            from qc.qc_fg_kiln_bending_header a 
            left join qc.qc_fg_kiln_bending_detail b on a.kb_id = b.kb_id
            where a.kb_status = 'N' $whdua and a.kb_date >= '{$tglfrom}' and a.kb_date <= '{$tglto}'
            ORDER BY a.kb_sub_plant, b.kbd_posisi, a.kb_date, a.kb_kiln, a.kb_id ASC";
    $responce->sql = $sql; 
    $qry = dbselect_plan_all($app_plan_id, $sql);
    if(is_array($qry)) {
        foreach($qry as $r){
            $datetime = explode(' ',$r[kb_date]);
            $r[tgl] = cgx_dmy2ymd($datetime[0]);
            $r[jam] = substr($datetime[1],0,2);
            

            $arr_nilai["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kbd_posisi]"]["$r[kb_id]"] = $r[kbd_kg].'##'.$r[kbd_cm];
            $arr_posisi["$r[subplan]"]["$r[tgl]"]["$r[kbd_posisi]"] = $r[kbd_posisi];

            $arr_sumkg["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"] += $r[kbd_kg];
            $arr_sumcm["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"] += $r[kbd_cm];


            $arr_temp["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_temp];
            $arr_speed["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_speed];
            $arr_presi["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_presi];
            $arr_desc["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_desc];
            $arr_wa["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_wa];
            $arr_ac["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_ac];
            $arr_wm["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_wm];
            $arr_tt["$r[subplan]"]["$r[tgl]"]["$r[jam]"]["$r[kb_kiln]"]["$r[kb_id]"]   = $r[kb_tt];
        }
    }

    if(is_array($arr_nilai)) {

        $html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
        $html .= '<div style="text-align:right;font-size:13px;font-weight:bold;">No : F.1003.QC.06</div>
                  <div style="text-align:center;font-size:20px;font-weight:bold;">DATA HASIL PENGAMATAN</div>
                  <div style="text-align:center;font-size:20px;font-weight:bold;">SPEED KILN VS BENDING STRENGTH</div>
                  <table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
        $html .= '<div style="overflow-x:auto;"><table class="adaborder">';
        foreach ($arr_nilai as $subplan => $a_tgl) {
            foreach ($a_tgl as $tgl => $a_jam) {

                $jmlposisi = count($arr_posisi[$subplan][$tgl]);
                $jmlklm = $jmlposisi+10;
                $html .= '<tr><th colspan="3" style="text-align:left;background:#D1D1D1;">SUBPLANT : '.$subplan.'</th><th colspan="'.($jmlklm-1).'" style="text-align:left;background:#D1D1D1;">'.$tgl.'</th></tr>';
                $html .= '<tr><th rowspan="2">NO</th>
                              <th rowspan="2">JAM</th>
                              <th rowspan="2">KILN</th>
                              <th>TEMP</th>
                              <th>SPEED</th>
                              <th rowspan="2">TEBAL TILE</th>
                              <th colspan="'.$jmlposisi.'">BENDING STRENGTH / BREAKING STRENGTH</th>
                              <th rowspan="2">B . S <br> RATA - RATA</th>
                              <th rowspan="2">PRESI</th>
                              <th rowspan="2">WATER ABORTION</th>
                              <th rowspan="2">AUTOCLAVE</th>
                              <th rowspan="2">WATERMARK</th>
                              <th rowspan="2">KETERANGAN</th>
                          </tr>';
                $html .= '<tr>';
                $html .= '<th>( C )</th>';
                $html .= '<th>( Menit )</th>';
                // $html .= '<th></th>';
                foreach ($arr_posisi[$subplan][$tgl] as $posisi) {
                    $html .= '<th>POSISI '.$posisi.'</th>';
                }
                $html .= '</tr>';


                $nojam =1;
                foreach ($a_jam as $jam => $a_kiln) {
                    $html .= '<tr>';
                    $html .= '<th>'.$nojam.'</th>';
                    $html .= '<th style="background:#D1D1D1;">'.$jam.':00</th>';

                    for ($i=1; $i <= $jmlklm; $i++) { 
                        $html .= '<th>&nbsp;</th>';
                    }

                    $html .= '</tr>';

                    

                    foreach ($a_kiln as $kiln => $a_posisi) {
                        $html .= '<tr>';
                        $html .= '<th>&nbsp;</th>';
                        $html .= '<th>&nbsp;</th>';
                        $html .= '<th>'.Romawi($kiln).'</th>';

                            $html .= '<td align="center">';
                            if(is_array($arr_temp[$subplan][$tgl][$jam][$kiln])) {
                                foreach ($arr_temp[$subplan][$tgl][$jam][$kiln] as $tempid => $a_tempVal) {
                                    $html .= '<span onclick="lihatData(\''.$tempid.'\')">'.$a_tempVal.'</span> ';
                                }
                            }else{
                                $html .= '&nbsp;';
                            }

                            $html .= '</td>';   

                            $html .= '<td align="center">';
                            if(is_array($arr_speed[$subplan][$tgl][$jam][$kiln])) {
                                foreach ($arr_speed[$subplan][$tgl][$jam][$kiln] as $speedid => $a_speedVal) {
                                    $html .= '<span onclick="lihatData(\''.$speedid.'\')">'.$a_speedVal.'</span> ';
                                }
                            }else{
                                $html .= '&nbsp;';
                            }

                            $html .= '</td>';   

                            $html .= '<td align="center">';
                            if(is_array($arr_tt[$subplan][$tgl][$jam][$kiln])) {
                                foreach ($arr_tt[$subplan][$tgl][$jam][$kiln] as $ttid => $a_ttVal) {
                                    $html .= '<span onclick="lihatData(\''.$ttid.'\')">'.$a_ttVal.'</span> ';
                                }
                            }else{
                                $html .= '&nbsp;';
                            }

                            $html .= '</td>';

                            foreach ($arr_posisi[$subplan][$tgl] as $posisi) {
                                $html .= '<td align="center">';
                                if(is_array($a_posisi[$posisi])) {
                                    
                                    foreach ($a_posisi[$posisi] as $nid => $nidVal) {
                                        $nidVal = explode( '##', $nidVal);
                                        $html .= '<span onclick="lihatData(\''.$nid.'\')">'.$nidVal[0].'/'.$nidVal[1].'</span> ';
                                    }
                                    
                                }else{
                                    $html .= '&nbsp;';
                                }
                                $html .= '</td>';
                            }


                                $jmlposisikiln = count($a_posisi);
                                $TTLnkg = $arr_sumkg[$subplan][$tgl][$jam][$kiln];
                                $TTLncm = $arr_sumcm[$subplan][$tgl][$jam][$kiln];

                                

                                if($jmlposisikiln == 0){
                                    $ratarata1 = '&nbsp;';
                                }else{
                                    $rtkg = ($TTLnkg/$jmlposisikiln);
                                    $rtcm = ($TTLncm/$jmlposisikiln);

                                    $ratarata1 = number_format($rtkg,1).'/'.number_format($rtcm,1); 
                                    if($ratarata1 == '0.0/0.0'){ 
                                        $ratarata1 = '&nbsp;';
                                    }else{
                                        $ratarata1 = $ratarata1;    
                                    }
                                }

                                

                            $html .= '<td align="center">'.$ratarata1.'</td>';



                            $html .= '<td align="center">';
                            if(is_array($arr_presi[$subplan][$tgl][$jam][$kiln])) {
                                foreach ($arr_presi[$subplan][$tgl][$jam][$kiln] as $presiid => $a_presiVal) {
                                    $html .= '<span onclick="lihatData(\''.$presiid.'\')">'.$a_presiVal.'</span> ';
                                }
                            }else{
                                $html .= '&nbsp;';
                            }

                            $html .= '</td>';

                            $html .= '<td>';
                            if(is_array($arr_wa[$subplan][$tgl][$jam][$kiln])) {
                                foreach ($arr_wa[$subplan][$tgl][$jam][$kiln] as $waid => $a_waVal) {
                                    $html .= '<span onclick="lihatData(\''.$waid.'\')">'.$a_waVal.'</span> ';
                                }
                            }else{
                                $html .= '&nbsp;';
                            }

                            $html .= '</td>';

                            $html .= '<td>';
                            if(is_array($arr_ac[$subplan][$tgl][$jam][$kiln])) {
                                foreach ($arr_ac[$subplan][$tgl][$jam][$kiln] as $acid => $a_acVal) {
                                    $html .= '<span onclick="lihatData(\''.$acid.'\')">'.$a_acVal.'</span> ';
                                }
                            }else{
                                $html .= '&nbsp;';
                            }

                            $html .= '</td>';

                            $html .= '<td>';
                            if(is_array($arr_wm[$subplan][$tgl][$jam][$kiln])) {
                                foreach ($arr_wm[$subplan][$tgl][$jam][$kiln] as $wmid => $a_wmVal) {
                                    $html .= '<span onclick="lihatData(\''.$wmid.'\')">'.$a_wmVal.'</span> ';
                                }
                            }else{
                                $html .= '&nbsp;';
                            }

                            $html .= '</td>';
                                
                            $html .= '<td>';
                            if(is_array($arr_desc[$subplan][$tgl][$jam][$kiln])) {
                                foreach ($arr_desc[$subplan][$tgl][$jam][$kiln] as $descid => $a_descVal) {
                                    $html .= '<span onclick="lihatData(\''.$descid.'\')">'.$a_descVal.'</span> ';
                                }
                            }else{
                                $html .= '&nbsp;';
                            }

                            $html .= '</td>';


                        $html .= '</tr>';                   
                    }
                    $nojam++;
                }

                $html .= '<tr><th colspan="'.(12+$jmlposisi).'">&nbsp;</th></tr>';

            }
        }

        $html .='</table></div>';
        
    } else {
        $html = 'TIDAKADA';
    }

$mpdf = new mPDF('','A4-L');
$mpdf->WriteHTML($html);
$mpdf->Output('Pengamatan_Speed_vs_bending.pdf', 'D');

?>