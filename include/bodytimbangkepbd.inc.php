<?php

include_once("../libs/init.php");

$nama_plan = $_SESSION[$app_id]['user']['plan_nama'];
if($_GET["mode"]) {
	$oper = $_GET["mode"];
} else {
	$oper = $_POST["oper"];
}

switch ($oper) {
	case "urai":
		urai();
		break;
	case "eksporpbd":
		eksporpbd();
		break;
	case "cbosubplant":
		cbosubplant($_GET['withselect']);
		break;
}

function urai(){
	global $app_plan_id, $nama_plan;
	$subplan = $_GET['subplan'];
	$tglfrom = cgx_dmy2ymd($_GET['tanggal'])." 00:00:00";
	$tglto = cgx_dmy2ymd($_GET['tanggal'])." 23:59:59";
	$tgljudul = $_GET['tanggal'];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.qbh_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.qbh_sub_plant as subplan, a.qbh_body_code as kodebody, b.qbd_material_type as tipe, b.qbd_material_code as item_kode, c.item_nama as item_nama, d.qbu_kode as box, b.qbd_formula as formula, b.qbd_dw as dw, b.qbd_mc as mc, b.qbd_ww as ww, b.qbd_value as nilai, b.qbd_remark as remark, a.qbh_shift as shift, a.qbh_bm_no as balmil, a.qbh_user_create, a.qbh_date_create, a.qbh_date, a.qbh_id, c.satuan
		from qc_bm_header a
		join qc_bm_detail b on(a.qbh_id=b.qbh_id)
		join item c on(b.qbd_material_code=c.item_kode)
		left join qc_box_unit d on(a.qbh_sub_plant=d.qbu_sub_plant and b.qbd_box_unit=d.qbu_kode)
		where a.qbh_kode_pbd IS NULL and a.qbh_rec_status='N' and a.qbh_date >= '{$tglfrom}' and a.qbh_date <= '{$tglto}' $whdua
		order by subplan, kodebody, tipe, item_kode, balmil";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	if(is_array($qry)){
		foreach($qry as $r){
			$r[tipe] = $r[tipe] == "1" ? "MATERIAL" : "ADDITIVE";
			$arr_baris["$r[subplan]"]["$r[kodebody]"]["$r[tipe]"]["$r[item_kode]"] = $r[item_nama]."@@".$r[satuan];
			$arr_kolom["$r[subplan]"]["$r[kodebody]"]["$r[balmil]"] = '';
			$arr_nilai["$r[subplan]"]["$r[kodebody]"]["$r[tipe]"]["$r[item_kode]"]["$r[balmil]"] += $r[nilai];
			$arr_qbh["$r[subplan]"]["$r[kodebody]"]["$r[qbh_id]"] = '';
			$arr_mc["$r[subplan]"]["$r[kodebody]"]["$r[tipe]"]["$r[item_kode]"] = round($r[mc],2);
			$i++;
		}
	}
	if(is_array($arr_baris)) {
		foreach ($arr_baris as $subplan => $a_kodebody) {
			foreach ($a_kodebody as $kodebody => $a_tipe) {
				$arr_tot_kol[$subplan][$kodebody] += count($arr_kolom[$subplan][$kodebody]);
			}
		}
		$html = '<style>td,th{padding-left:3px;padding-right:3px;}table.adaborder{border-collapse:collapse;width:100%;}table.adaborder th,table.adaborder td{border:1px solid black;}</style>';
		$html .= '<div style="text-align:center;font-size:20px;font-weight:bold;">PENIMBANGAN MATERIAL BODY</div><table style="margin:0 auto;"><tr><td>TGL : </td><td>'.$tgljudul.'</td></tr></table>';
		$html .= '<div style="overflow-x:auto;"><table class="adaborder" id="tbl01">';
		foreach ($arr_baris as $subplan => $a_kodebody) {
			foreach ($a_kodebody as $kodebody => $a_tipe) {
				$html .= '<tr><th colspan="2">SUBPLANT : '.$subplan.'</th><th colspan="'.($arr_tot_kol[$subplan][$kodebody]+2).'">KODE BODY : '.$kodebody.'</th><th rowspan="2">TOTAL MATERIAL & ADDITIVE</th><th rowspan="2">KADAR AIR (%)</th><th rowspan="2">DRY WEIGHT</th></tr>';
	        	$html .= '<tr><th>NO.</th><th>ITEM KODE</th><th>NAMA MATERIAL</th><th>SATUAN</th>';
	        	ksort($arr_kolom[$subplan][$kodebody]);
				reset($arr_kolom[$subplan][$kodebody]);
	        	foreach ($arr_kolom[$subplan][$kodebody] as $balmil => $value) {
        			$html .= '<th>BM '.$balmil.'</th>';		
		        }
	        	$html .= '</tr>';
	        	foreach ($a_tipe as $tipe => $a_item_kode) {
	        		$no = 1;
	        		$tot_nil = array();
	        		foreach ($arr_kolom[$subplan][$kodebody] as $balmil => $value) {
						$tot_nil[$balmil] = 0;	
					}
	        		foreach ($a_item_kode as $item_kode => $nil_bar) {
        				$brs = explode("@@",$nil_bar);
        				if($brs[0] != "H2O") {
	        				$tot_bar_nil = 0;
		        			$mc = $arr_mc[$subplan][$kodebody][$tipe][$item_kode];
		        			$html .='<tr><td style="text-align:center;">'.$no.'</td><td>'.$item_kode.'</td><td style="white-space: nowrap">'.$brs[0].'</td><td style="text-align:center;">'.$brs[1].'</td>';
		        			ksort($arr_kolom[$subplan][$kodebody]);
							reset($arr_kolom[$subplan][$kodebody]);
							foreach ($arr_kolom[$subplan][$kodebody] as $balmil => $value) {
		        				$nilai = $arr_nilai[$subplan][$kodebody][$tipe][$item_kode][$balmil];
			        			if($nilai) {
			        				$html .= '<td style="text-align:right;">'.number_format($nilai,2).'</td>';
			        				if($nilai == '') {
			        					$nilai = 0;
			        				}
			        				$tot_bar_nil += round($nilai,2);
			        				$tot_nil[$balmil] += round($nilai,2);
			        			} else {
			        				$html .= '<td></td>';
			        			}	
					        }
					        $dw = $tot_bar_nil-($tot_bar_nil*$mc/100);
				        	$html .= '<td style="text-align:right;font-weight:bold;background-color:#88fcb2;">'.number_format($tot_bar_nil,2).'</td><td style="text-align:right;font-weight:bold;">'.number_format($mc,2).'</td><td style="text-align:right;font-weight:bold;">'.number_format($dw,2).'</td>';
		        			$html .='</tr>';
		        			$no++;
		        		}
	        		}
	        		if($tipe == 'MATERIAL') {
	        			$html .='<tr><td></td><td colspan="2" style="text-align:center;font-weight:bold;">TOTAL '.$tipe.'</td><td></td>';
		        		ksort($arr_kolom[$subplan][$kodebody]);
						reset($arr_kolom[$subplan][$kodebody]);
						foreach ($arr_kolom[$subplan][$kodebody] as $balmil => $value) {
		        			$nilai = $tot_nil[$balmil]; 
		        			if($nilai) {
		        				$html .= '<td style="text-align:right;font-weight:bold;background-color:#edf765;">'.number_format($nilai,2).'</td>';
		        			} else {
		        				$html .= '<td></td>';
		        			}
				        }
			        	$html .='<td style="text-align:right;font-weight:bold;background-color:#88fcb2;"></td><td style="text-align:right;font-weight:bold;"></td><td style="text-align:right;font-weight:bold;"></td></tr>';
	        		}
	        	}
	        	$html .='<tr><td colspan="'.($arr_tot_kol[$subplan][$kodebody]+7).'">&nbsp;</td></tr>';
			}
		}
		$html .='</table></div>';
	} else {
		$html = 'TIDAKADA';
	}
	$responce->detailtabel = $html; 
	echo json_encode($responce);
}

function eksporpbd(){
	global $app_id, $app_plan_id, $nama_plan;
	$subplan = $_GET['subplan'];
	$tglfrom = cgx_dmy2ymd($_GET['tanggal'])." 00:00:00";
	$tglto = cgx_dmy2ymd($_GET['tanggal'])." 23:59:59";
	$tgljudul = $_GET['tanggal'];
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.qbh_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.qbh_sub_plant as subplan, a.qbh_body_code as kodebody, b.qbd_material_type as tipe, b.qbd_material_code as item_kode, c.item_nama as item_nama, d.qbu_kode as box, b.qbd_formula as formula, b.qbd_dw as dw, b.qbd_mc as mc, b.qbd_ww as ww, b.qbd_value as nilai, b.qbd_remark as remark, a.qbh_shift as shift, a.qbh_bm_no as balmil, a.qbh_user_create, a.qbh_date_create, a.qbh_date, a.qbh_id, c.satuan
		from qc_bm_header a
		join qc_bm_detail b on(a.qbh_id=b.qbh_id)
		join item c on(b.qbd_material_code=c.item_kode)
		left join qc_box_unit d on(a.qbh_sub_plant=d.qbu_sub_plant and b.qbd_box_unit=d.qbu_kode) 
		where a.qbh_kode_pbd IS NULL and a.qbh_rec_status='N' and a.qbh_date >= '{$tglfrom}' and a.qbh_date <= '{$tglto}' $whdua
		order by subplan, kodebody, tipe, item_kode, balmil";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	if(is_array($qry)){
		foreach($qry as $r){
			$r[tipe] = $r[tipe] == "1" ? "MATERIAL" : "ADDITIVE";
			$arr_baris["$r[subplan]"]["$r[kodebody]"]["$r[tipe]"]["$r[item_kode]"] = $r[item_nama]."@@".$r[satuan];
			$arr_kolom["$r[subplan]"]["$r[kodebody]"]["$r[balmil]"] = '';
			$arr_nilai["$r[subplan]"]["$r[kodebody]"]["$r[tipe]"]["$r[item_kode]"]["$r[balmil]"] += $r[nilai];
			$arr_qbh["$r[subplan]"]["$r[kodebody]"]["$r[qbh_id]"] = '';
			$arr_mc["$r[subplan]"]["$r[kodebody]"]["$r[tipe]"]["$r[item_kode]"] = round($r[mc],2);
			$i++;
		}
	}
	if(is_array($arr_baris)) {
		$arr_dep_kode = array('A' => '2.1', 'B' => '2.101', 'C' => '2.194'); 
		$arr_nama_bln = array('01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec');
		$qbh_user_pbd = $_SESSION[$app_id]['user']['user_name'];
		// $qbh_date_pbd = date("Y-m-d");
		$qbh_date_pbd = cgx_dmy2ymd($_GET['tanggal']);
		$req_pbd = strtoupper($qbh_user_pbd);
		$kode_produksi = $arr_nama_bln[date("m")]." ".date("Y");
		$pbd_init = "PBD/".$app_plan_id."/".date("y")."/";
		foreach ($arr_baris as $subplan => $a_kodebody) {
			foreach ($a_kodebody as $kodebody => $a_tipe) {
				$sql = "SELECT max(bon_kode_material) as pbd_max from tbl_permintaan_material where bon_kode_material like '{$pbd_init}%'";
				$mx = dbselect($sql);
				if($mx[pbd_max] == ''){
					$mx[pbd_max] = 0;
				} else {
					$mx[pbd_max] = substr($mx[pbd_max],-5);
				}
				$urutbaru = $mx[pbd_max]+1;
				$pbd_id = "PBD/".$app_plan_id."/".date("y")."/".str_pad($urutbaru,5,"0",STR_PAD_LEFT);
				$sql = "INSERT into  tbl_permintaan_material (bon_kode_material, departemen_kode, tanggal, create_by, modiby, modidate, requester, plan_kode, komposisi_kode, kode_produksi, subplan_kode) values('{$pbd_id}', '{$arr_dep_kode[$subplan]}', '{$qbh_date_pbd}', '{$qbh_user_pbd}', '{$qbh_user_pbd}', '{$qbh_date_pbd}', '{$req_pbd}', '', '{$kodebody}', '{$kode_produksi}', '{$subplan}');";
				$xsql = dbsave($sql);
				if($xsql == "OK") {
					$hasil = 7;
					foreach ($a_tipe as $tipe => $a_item_kode) {
		        		foreach ($a_item_kode as $item_kode => $nil_bar) {
		        			$brs = explode("@@",$nil_bar);
		        			if($brs[0] != "H2O") {
		        				$tot_bar_nil = 0;
		        				$tot_bar_batch = 0;
			        			$mc = $arr_mc[$subplan][$kodebody][$tipe][$item_kode];
			        			$k2sql = "INSERT into item_permintaan_material (bon_kode_material, item_kode, vol) values('{$pbd_id}', '{$item_kode}', '{$brs[1]}');";
			        			$xk2sql = dbsave($k2sql);
								if($xk2sql == "OK") {
									$k3sql = "";
				        			foreach ($arr_kolom[$subplan][$kodebody] as $balmil => $value) {
				        				$qbh_bm = $balmil > 10 ? $balmil : substr($balmil,1);
				        				$nilai = $arr_nilai[$subplan][$kodebody][$tipe][$item_kode][$balmil];
					        			if($nilai) {
					        				$tot_bar_nil += round($nilai,2);
					        				$tot_bar_batch += 1;
					        				$k3sql .= " UPDATE item_permintaan_material set bm".$qbh_bm." = {$nilai}, bm".$qbh_bm."_batch = '1' where bon_kode_material = '{$pbd_id}' and item_kode = '{$item_kode}';";
					        			}	
							        }
							        $dw = $tot_bar_nil-($tot_bar_nil*$mc/100);
							        $k3sql .= " UPDATE item_permintaan_material set qty = {$tot_bar_nil}, outstanding = {$tot_bar_nil}, qty_batch = {$tot_bar_batch}, kadar_air = '{$mc}', tot_real = $dw where bon_kode_material = '{$pbd_id}' and item_kode = '{$item_kode}';";
							        $xk3sql = dbsave($k3sql);
							        if($xk3sql != "OK") {
							        	$out .= "- ".$xk3sql."<br>";
							        	$hasil = 0;	
							        }
					    		} else {
					    			$out .= "- ".$xk2sql."<br>";
					    			$hasil = 0;
					    		}
					    	}
		        		}
		        	}
		        	if($hasil == 7) {
		        		$k4sql = "";
		        		foreach ($arr_qbh[$subplan][$kodebody] as $qbh_id => $value) {
		        			$k4sql .= " UPDATE qc_bm_header set qbh_kode_pbd = '{$pbd_id}', qbh_user_pbd = '{$qbh_user_pbd}', qbh_date_pbd = '{$qbh_date_pbd}' where qbh_id = '{$qbh_id}';";
		        		}
		        		$xk4sql = dbsave_plan($app_plan_id, $k4sql);
						if($xk4sql != "OK") {
							$out .= "- ".$xk4sql."<br>";
						} else {
							$out .= "- Subplant ".$subplan." Kode Body ".$kodebody." SUKSES ekspor PBD dengan no. ".$pbd_id." ";
						}
		        	}
				} else {
					$out .= "- ".$xsql."<br>";
				}
			}
		}
	} else {
		$out = 'TIDAKADA';
	}
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant("A",false);
	$out .= $withselect ? "</select>" : "";
	echo $out;
}



?>