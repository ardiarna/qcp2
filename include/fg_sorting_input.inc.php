<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['88'];
$app_subplan = $_SESSION[$app_id]['user']['sub_plan'];

if($_GET["mode"]) {
	$oper = $_GET["mode"];
} else {
	$oper = $_POST["oper"];
}

switch ($oper) {
	case "urai":
		urai();
		break;
	case "add":
		simpan("add");
		break;
	case "edit":
		simpan("edit");
		break;
	case "hapus":
		hapus();
		break;	
	case "cbosubplant":
		cbosubplant($_GET['withselect']);
		break;
	case "cbomesin":
		cbomesin($_POST['subplan']);
		// cbopress($rhead[hph_sub_plant],$rhead[hph_press],true);
		break;
	case "detailtabel":
		detailtabel($_POST['stat']);
		break;
	case "pilihmotif":
		pilihmotif();
		break;
}


function cbomesin($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT mesin_id, mesin_desc from qc_fg_sorting_mesin where sub_plant = 'A' and mesin_status = 'N' order by mesin_id";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if(trim($r[mesin_id]) == $nilai){
				$out .= "<option value='$r[mesin_id]' selected>$r[mesin_desc]</option>";
			} else {
				$out .= "<option value='$r[mesin_id]'>$r[mesin_desc]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;
	}
}


function urai(){
	global $app_plan_id, $akses, $app_subplan;
	$page = $_POST['page']; 
	$rows = $_POST['rows']; 
	$sidx = $_POST['sidx']; 
	$sord = $_POST['sord'];
	$subplan_kode = $_GET['subplan'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$whsatu = dptKondisiWhere($_POST['_search'],$_POST['filters'],$_POST['searchField'],$_POST['searchOper'],$_POST['searchString']);
	$whdua = "";
	if($app_subplan <> 'All') {
		$whdua .= " and sp_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and sp_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['sp_id']) {
		$whdua .= " and sp_id = '".$_POST['sp_id']."'";
	}
	if($_POST['sp_sub_plant']) {
		$whdua .= " and sp_sub_plant = '".$_POST['sp_sub_plant']."'";
	}
	if($_POST['sp_date']) {
		$whdua .= " and to_char(sp_date, 'DD-MM-YYYY')  = '".$_POST['sp_date']."'";
	}
	if($_POST['sp_shift']) {
		$whdua .= " and sp_shift = '".$_POST['sp_shift']."'";
	}

	if($_POST['sp_line']) {
		$whdua .= " and sp_line = '".$_POST['sp_line']."'";
	}

	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) AS count FROM qc_fg_sorting_header WHERE sp_status = 'N' AND sp_date >= '{$tglfrom}' AND sp_date <= '{$tglto}' $whsatu $whdua";
	$r = dbselect_plan($app_plan_id, $sql);
	$count = $r['count'];
	if($count > 0) { 
		if($rows == -1){
			$total_pages = 1;
			$limit = "";
		} else {
			$total_pages = ceil($count / $rows);
			$start = $rows * $page - $rows;
			$limit = "limit ".$rows." offset ".$start;
		}
		$sql = "SELECT * FROM qc_fg_sorting_header WHERE sp_status = 'N' and sp_date >= '{$tglfrom}' and sp_date <= '{$tglto}' $whsatu $whdua
				ORDER BY $sidx $sord $limit";
		$qry = dbselect_plan_all($app_plan_id, $sql);
		$i = 0;
	} else { 
		$total_pages = 1; 
	}
	if($page > $total_pages) $page = $total_pages; 
	$responce->page = $page; 
	$responce->total = $total_pages; 
	$responce->records = $count; 
	if($count > 0) {
		foreach($qry as $ro){
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['sp_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['sp_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['sp_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['sp_date']);
			$ro['date'] = cgx_dmy2ymd($datetime[0]);
			$ro['time'] = substr($datetime[1],0,5);
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['sp_sub_plant'],$ro['sp_id'],$ro['date'],$ro['sp_shift'],$ro['sp_line'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}



function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[sp_date]  = cgx_dmy2ymd($r[sp_date])." 00:00:00";
	if($stat == "add") {
		$r[sp_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[sp_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(sp_id) as sp_id_max from qc_fg_sorting_header where sp_sub_plant = '{$r[sp_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[sp_id_max] == ''){
			$mx[sp_id_max] = 0;
		} else {
			$mx[sp_id_max] = substr($mx[sp_id_max],-7);
		}
		$urutbaru = $mx[sp_id_max]+1;
		$r[sp_id] = $app_plan_id.$r[sp_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		
		//cek jika duplikat
		$qdup = "SELECT COUNT(*) AS jmldup from qc_fg_sorting_header 
				 WHERE sp_status = 'N' 
				 AND sp_sub_plant = '{$r[sp_sub_plant]}' 
				 AND sp_date = '{$r[sp_date]}'
				 AND sp_line = '{$r[sp_line]}'
				 AND sp_shift = '{$r[sp_shift]}'";
		$ddup = dbselect_plan($app_plan_id, $qdup);

		if($ddup[jmldup] > 0){
			$out = "Terjadi Duplikat Data";
		}else{

			if(is_array($r[code])) {
					$sql = "INSERT into qc_fg_sorting_header(sp_sub_plant, sp_id, sp_date, sp_line, sp_shift, sp_status, sp_user_create, sp_date_create) values 
					       ('{$r[sp_sub_plant]}', '{$r[sp_id]}', '{$r[sp_date]}', {$r[sp_line]}, '{$r[sp_shift]}', 'N', '{$r[sp_user_create]}', '{$r[sp_date_create]}');";
					$xsql = dbsave_plan($app_plan_id, $sql); 
					if($xsql == "OK") {
						$k2sql = "";
							foreach ($r[code] as $i => $value) {
								if($r[export][$i] == ''){ $r[export][$i] = 0;}else{$r[export][$i] = $r[export][$i];}
								if($r[ekonomi][$i] == ''){ $r[ekonomi][$i] = 0;}else{$r[ekonomi][$i] = $r[ekonomi][$i];}
								if($r[reject][$i] == ''){ $r[reject][$i] = 0;}else{$r[reject][$i] = $r[reject][$i];}
								if($r[rijek_palet][$i] == ''){ $r[rijek_palet][$i] = 0;}else{$r[rijek_palet][$i] = $r[rijek_palet][$i];}
								if($r[rijek_buang][$i] == ''){ $r[rijek_buang][$i] = 0;}else{$r[rijek_buang][$i] = $r[rijek_buang][$i];}
								$r[keterangan][$i] = addslashes($r[keterangan][$i]);

								if($r[code][$i] == '' && $r[code][$i] == '' && $r[code][$i] == '' && $r[code][$i] == '' && $r[code][$i] == '' && $r[code][$i] == ''){
									
								}else{
									$k2sql .= "INSERT into qc_fg_sorting_detail(sp_id, code, size, export, ekonomi, reject, keterangan, rijek_palet, rijek_buang) 
										   values('{$r[sp_id]}', '{$r[code][$i]}', '{$r[size][$i]}', '{$r[export][$i]}', '{$r[ekonomi][$i]}', '{$r[reject][$i]}', '{$r[keterangan][$i]}', '{$r[rijek_palet][$i]}', '{$r[rijek_buang][$i]}');";
								}

								
							}
							$out = dbsave_plan($app_plan_id, $k2sql);
					} else {
						$out = $xsql;
					}
			}else{
				$out = 'Silahkan tambahkan detail data.';
			}
		}
	} else if($stat=='edit') {
		if(is_array($r[code])) {
				$r[sp_user_modify] = $_SESSION[$app_id]['user']['user_name'];
				$r[sp_date_modify] = date("Y-m-d H:i:s");

				$sql = "UPDATE qc_fg_sorting_header SET sp_user_modify = '{$r[sp_user_modify]}', sp_date_modify = '{$r[sp_date_modify]}' WHERE sp_id = '{$r[sp_id]}';";
				$xsql = dbsave_plan($app_plan_id, $sql); 
				if($xsql == "OK") {
					$k1sql = "DELETE from qc_fg_sorting_detail where sp_id = '{$r[sp_id]}';";
					$x1sql = dbsave_plan($app_plan_id, $k1sql);

					$k2sql = "";
					if(is_array($r[code])) {
						foreach ($r[code] as $i => $value) {
							if($r[export][$i] == ''){ $r[export][$i] = 0;}else{$r[export][$i] = $r[export][$i];}
							if($r[ekonomi][$i] == ''){ $r[ekonomi][$i] = 0;}else{$r[ekonomi][$i] = $r[ekonomi][$i];}
							if($r[reject][$i] == ''){ $r[reject][$i] = 0;}else{$r[reject][$i] = $r[reject][$i];}
							if($r[rijek_palet][$i] == ''){ $r[rijek_palet][$i] = 0;}else{$r[rijek_palet][$i] = $r[rijek_palet][$i];}
							if($r[rijek_buang][$i] == ''){ $r[rijek_buang][$i] = 0;}else{$r[rijek_buang][$i] = $r[rijek_buang][$i];}
							$r[keterangan][$i] = addslashes($r[keterangan][$i]);

							if($r[code][$i] == '' && $r[code][$i] == '' && $r[code][$i] == '' && $r[code][$i] == '' && $r[code][$i] == '' && $r[code][$i] == ''){
									
							}else{
								$k2sql .= "INSERT into qc_fg_sorting_detail(sp_id, code, size, export, ekonomi, reject, keterangan, rijek_palet, rijek_buang) 
									   values('{$r[sp_id]}', '{$r[code][$i]}', '{$r[size][$i]}', '{$r[export][$i]}', '{$r[ekonomi][$i]}', '{$r[reject][$i]}', '{$r[keterangan][$i]}', '{$r[rijek_palet][$i]}', '{$r[rijek_buang][$i]}');";
							}
							
							
						}
						$out = dbsave_plan($app_plan_id, $k2sql);
					} 
				} else {
					$out = $xsql;
				}
		}else{
			$out = 'Silahkan tambahkan detail data.';
		}
	}
	echo $out;
}

function hapus(){
	global $app_plan_id;
	$sp_id = $_POST['kode'];

	$sql = "UPDATE qc_fg_sorting_header SET sp_status = 'C' WHERE sp_id = '{$sp_id}';";
	echo dbsave_plan($app_plan_id, $sql); 
}


function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant();
	$out .= $withselect ? "</select>" : "";
	echo $out;
}

function pilihmotif() {
	global $app_plan_id;
	if($_POST['txt_cari']) {
		$txt_cari = strtoupper($_POST['txt_cari']);
		$whsatu = "and upper(qmm_nama) like '%{$txt_cari}%'";
	}
	$sql = "SELECT qmm_nama, qmm_size from qc_md_motif where 1=1 $whsatu order by qmm_nama";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out = '<table class="table table-bordered table-striped table-condensed"><tbody><tr><th></th><th>CODE MOTIF</th><th>SIZE</th></tr>';
	if(is_array($qry)) {
		foreach($qry as $r) {
			$qmm_nama = str_replace('"', '', $r[qmm_nama]);
			$out .= '<tr><td><span class="glyphicon glyphicon-ok" onClick="setMotif(\''.$qmm_nama.'\',\''.$r[qmm_size].'\');"></span></td><td>'.$qmm_nama.'</td><td>'.$r[qmm_size].'</td></tr>';
		}
	} else {
		$out .= '<tr><td colspan="2">Motif dengan nama : '.$txt_cari.' tidak ditemukan...</td></tr>';
	}	
	$out .= '</tbody></table>';

	$responce->out = $out;
	echo json_encode($responce);
}

function detailtabel($stat) {
	global $app_plan_id, $app_subplan;
	if($stat == "edit" || $stat == "view") {
		$sp_id = $_POST['kode'];
		$sql0 = "SELECT * FROM qc_fg_sorting_header WHERE sp_id = '{$sp_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['sp_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$rhead['time'] = substr($datetime[1],0,5);
	} else {
		if($_POST['subplan']) {
			$rhead[sp_sub_plant] = $_POST['subplan'];
		} else {
			if($app_subplan <> 'All') {
				$rhead[sp_sub_plant] = $app_subplan;
			} else {
				$rhead[sp_sub_plant] = 'A';
			}
		}
	}

	$i = 1;
	$out .= '<table id="tabeldetail" class="table table-bordered table-condensed table-hover">';

	$out .=	'<tr>
        	<th width="10" rowspan="2">';
        if($stat == "add" || $stat == "edit") {
        	$out .= '<a href="javascript:void(0)" class="btn btn-default btn-xs" onClick="tambahItem()"><span class="glyphicon glyphicon-plus"></span></a>';
        }else{
        	$out .= '&nbsp';
        }

    $out .= '   </th>
	    		<th style="vertical-align:middle;" rowspan="2">CODE</th>
	        	<th style="vertical-align:middle;" rowspan="2">SIZE</th>
	        	<th colspan="5">HASIL OUTPUT SORTIR</th>';

	$out .= '	<th style="vertical-align:middle;" rowspan="2">KETERANGAN</th>
	        </tr>';
    $out .= '<tr>
	        	<th>EXPORT <br> ( M2 )</th>
	        	<th>EKONOMI <br> ( M2 )</th>
	        	<th>REJECT <br> SORTIR ( M2 )</th>
	        	<th>REJECT <br> PALET ( M2 )</th>
	        	<th>REJECT <br> BUANG ( M2 )</th>
	        </tr>';

    if($stat == "edit" || $stat == "view") {
    		$sql2a = "SELECT COUNT(*) AS jml FROM qc_fg_sorting_detail WHERE sp_id = '{$sp_id}'";
			$qry2a = dbselect_plan($app_plan_id, $sql2a);


			$sql2 = "SELECT * FROM qc_fg_sorting_detail WHERE sp_id = '{$sp_id}' ORDER BY code";
			$qry2 = dbselect_plan_all($app_plan_id, $sql2);

			foreach($qry2 as $r2) {

				if($r2[export] == 0){ $r2[export] = '';}else{$r2[export] = $r2[export];}
				if($r2[ekonomi] == 0){ $r2[ekonomi] = '';}else{$r2[ekonomi] = $r2[ekonomi];}
				if($r2[reject] == 0){ $r2[reject] = '';}else{$r2[reject] = $r2[reject];}
				if($r2[rijek_palet] == 0){ $r2[rijek_palet] = '';}else{$r2[rijek_palet] = $r2[rijek_palet];}
				if($r2[rijek_buang] == 0){ $r2[rijek_buang] = '';}else{$r2[rijek_buang] = $r2[rijek_buang];}
				$out .= '<tr id="trdet_ke_'.$i.'">
		        	<td class="text-center">';

		        if($stat == "edit") {
		        	if($i == $qry2a[jml]){
		        		$styleremove = '';
		        	}else{
		        		$styleremove = 'style="display:none"';
		        	}

		        	$out .= '<div id="linkremove'.$i.'" '.$styleremove.'><a href="javascript:void(0)" class="btn btn-default btn-xs" onclick="hapusItem('.$i.')"><span class="glyphicon glyphicon-remove"></span></a></div>';
		        }else{
		        	$out .= '&nbsp';
		        }
		        $out .=	'</td>
		        	<td width="400">
		        		<div class="input-group">
		        			<input class="form-control input-sm" name="code['.$i.']" id="code_'.$i.'" type="text" value="'.$r2[code].'" readonly>
		        			<div class="input-group-addon" title="Pilih code"><span class="glyphicon glyphicon-option-horizontal" onClick="tampilMotif(\''.$i.'\');"></span></div>
		        		</div>
		        	</td>
		        	<td width="90">
		        		<input class="form-control input-sm text-right" name="size['.$i.']" id="size_'.$i.'" type="text" value="'.$r2[size].'">
		        	</td>
		        	<td width="90">
		        		<input class="form-control input-sm text-right" name="export['.$i.']" id="export_'.$i.'" value="'.$r2[export].'" onkeyup="hanyanumerik(this.id,this.value);">
		        	</td>
		        	<td width="90">
		        		<input class="form-control input-sm text-right" name="ekonomi['.$i.']" id="ekonomi_'.$i.'" value="'.$r2[ekonomi].'" onkeyup="hanyanumerik(this.id,this.value);">
		        	</td>
		        	<td width="90">
		        		<input class="form-control input-sm text-right" name="reject['.$i.']" id="reject_'.$i.'" value="'.$r2[reject].'" onkeyup="hanyanumerik(this.id,this.value);">
		        	</td>
		        	<td width="90">
		        		<input class="form-control input-sm text-right" name="rijek_palet['.$i.']" id="rijek_palet_'.$i.'" value="'.$r2[rijek_palet].'" onkeyup="hanyanumerik(this.id,this.value);">
		        	</td>
		        	<td width="90">
		        		<input class="form-control input-sm text-right" name="rijek_buang['.$i.']" id="rijek_buang_'.$i.'" value="'.$r2[rijek_buang].'" onkeyup="hanyanumerik(this.id,this.value);">
		        	</td>
		        	<td width="120">
		        		<input class="form-control input-sm text-right" name="keterangan['.$i.']" id="keterangan_'.$i.'" value="'.$r2[keterangan].'">
		        	</td>
		        	</tr>';
				$i++;
			}
	}

    if($stat == "edit" || $stat == "add") {
		$out .= '<tr>
		    <td colspan="11" class="text-center">
		    	<input type="hidden" id="barisLast" value="'.$i.'">
		    	<button type="button" class="btn btn-primary btn-sm" onClick="simpanData(\''.$stat.'\')">Simpan</button> 
		    	<button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button></td>
		    </tr>';
	} else {
		$out .= '<tr>
		    <td colspan="11" class="text-center">
		    	<button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button></td>
		    </tr>';
	}

    $out .= '</table>';




    if($stat == "edit" || $stat == "view") {
    	$responce->sp_id = $rhead[sp_id];
    	$responce->sp_date = $rhead[date];
    	$responce->sp_shift = $rhead[sp_shift];
    	$responce->sp_sub_plant = $rhead[sp_sub_plant];
    	$responce->sp_line = $rhead[sp_line];
    }
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>