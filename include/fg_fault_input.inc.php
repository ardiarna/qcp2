<?php

include_once("../libs/init.php");

$sTable_h = "qc_pd_cm_header";
$sTable_d = "qc_pd_cm_detail";

$akses = $_SESSION[$app_id]['app_priv']['85'];
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
	case "cbopress":
		cbopress($_POST['subplan']);
		break;
	case "detailtabel":
		detailtabel($_POST['stat']);
		break;
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
		$whdua .= " and fgf_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and fgf_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['fgf_id']) {
		$whdua .= " and fgf_id = '".$_POST['fgf_id']."'";
	}
	if($_POST['fgf_sub_plant']) {
		$whdua .= " and fgf_sub_plant = '".$_POST['fgf_sub_plant']."'";
	}
	if($_POST['date']) {
		$whdua .= " and to_char(fgf_date, 'YYYY-MM-DD')  = '".$_POST['date']."'";
	}
	if($_POST['fgf_kiln']) {
		$whdua .= " and fgf_kiln = '".$_POST['fgf_kiln']."'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) AS count FROM qc_fg_fault_header WHERE fgf_status = 'N' AND fgf_date >= '{$tglfrom}' AND fgf_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT fgf_id, fgf_sub_plant, fgf_date, fgf_kiln FROM qc_fg_fault_header 
			    WHERE fgf_status = 'N' and fgf_date >= '{$tglfrom}' and fgf_date <= '{$tglto}' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['fgf_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['fgf_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['fgf_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['fgf_date']);
			$ro['date'] = $datetime[0];
			$ro['time'] = substr($datetime[1],0,5);
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['fgf_sub_plant'],$ro['fgf_id'],$ro['date'],$ro['time'],$ro['fgf_kiln'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}



function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[fgf_date] = cgx_dmy2ymd($r[fgf_date])." ".$r[fgf_time].":00";
	$r[fgf_kiln] = cgx_angka($r[fgf_kiln]);

	$r[fgf_quality] = cgx_angka($r[fgf_quality]);
	if($r[fgf_quality] > 100){
		$r[fgf_quality] = 100;
	}else{
		$r[fgf_quality] = $r[fgf_quality];
	}

	if($stat == "add") {
		$r[fgf_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[fgf_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(fgf_id) as fgf_id_max from qc_fg_fault_header where fgf_sub_plant = '{$r[fgf_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[fgf_id_max] == ''){
			$mx[fgf_id_max] = 0;
		} else {
			$mx[fgf_id_max] = substr($mx[fgf_id_max],-7);
		}
		$urutbaru = $mx[fgf_id_max]+1;
		$r[fgf_id] = $app_plan_id.$r[fgf_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		
		//cek jika duplikat
		$qdup = "SELECT COUNT(*) AS jmldup from qc_fg_fault_header 
				 WHERE fgf_status = 'N' 
				 AND fgf_sub_plant = '{$r[fgf_sub_plant]}' 
				 AND fgf_date = '{$r[fgf_date]}'
				 AND fgf_kiln = '{$r[fgf_kiln]}' ";
		$ddup = dbselect_plan($app_plan_id, $qdup);

		if($ddup[jmldup] > 0){
			$out = "Terjadi Duplikat Data";
		}else{

			$sql = "INSERT into qc_fg_fault_header(fgf_sub_plant, fgf_id, fgf_date, fgf_kiln, fgf_quality, fgf_type, fgf_user_create, fgf_date_create, fgf_status) values('{$r[fgf_sub_plant]}','{$r[fgf_id]}',  '{$r[fgf_date]}', '{$r[fgf_kiln]}', '{$r[fgf_quality]}','{$r[fgf_type]}','{$r[fgf_user_create]}', '{$r[fgf_date_create]}', 'N');";
			$xsql = dbsave_plan($app_plan_id, $sql); 
			$out = $xsql;
			if($xsql == "OK") {
				$k2sql = "";
				foreach ($r[fapr_id] as $i => $value) {
					if($r[eco_value][$i] == ''){
						$r[eco_value][$i] = 0;
					}else{
						$r[eco_value][$i] = $r[eco_value][$i];
					}

					if($r[rj_value][$i] == ''){
						$r[rj_value][$i] = 0;
					}else{
						$r[rj_value][$i] = $r[rj_value][$i];
					}


					$k2sql .= "INSERT into qc_fg_fault_detail(fgf_id, fapr_id, eco_value, rj_value) 
								values('{$r[fgf_id]}', '{$r[fapr_id][$i]}', '{$r[eco_value][$i]}', '{$r[rj_value][$i]}');";
				}
				$out = dbsave_plan($app_plan_id, $k2sql);
			} else {
				$out = $xsql;
			}
		}
	} else if($stat=='edit') {
		$r[fgf_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[fgf_date_modify] = date("Y-m-d H:i:s");

		$sql = "UPDATE qc_fg_fault_header set fgf_quality = '{$r[fgf_quality]}', fgf_type = '{$r[fgf_type]}', fgf_user_modify = '{$r[fgf_user_modify]}', fgf_date_modify = '{$r[fgf_date_modify]}' 
				where fgf_id = '{$r[fgf_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_fg_fault_detail where fgf_id = '{$r[fgf_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);

			$k2sql = "";
			foreach ($r[fapr_id] as $i => $value) {
				if($r[eco_value][$i] == ''){
					$r[eco_value][$i] = 0;
				}else{
					$r[eco_value][$i] = $r[eco_value][$i];
				}

				if($r[rj_value][$i] == ''){
					$r[rj_value][$i] = 0;
				}else{
					$r[rj_value][$i] = $r[rj_value][$i];
				}


				$k2sql .= "INSERT into qc_fg_fault_detail(fgf_id, fapr_id, eco_value, rj_value) 
							values('{$r[fgf_id]}', '{$r[fapr_id][$i]}', '{$r[eco_value][$i]}', '{$r[rj_value][$i]}');";
			}
			$out = dbsave_plan($app_plan_id, $k2sql);
		} else {
			$out = $xsql;
		}
	}
	echo $out;
}

function hapus(){
	global $app_plan_id;
	$fgf_id = $_POST['kode'];

	$sql = "UPDATE qc_fg_fault_header SET fgf_status = 'C' WHERE fgf_id = '{$fgf_id}';";
	echo dbsave_plan($app_plan_id, $sql); 
}


function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant();
	$out .= $withselect ? "</select>" : "";
	echo $out;
}

function detailtabel($stat) {
	global $app_plan_id, $app_subplan;
	if($stat == "edit" || $stat == "view") {
		$fgf_id = $_POST['kode'];
		$sql0 = "SELECT * FROM qc_fg_fault_header WHERE fgf_id = '{$fgf_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['fgf_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$rhead['time'] = substr($datetime[1],0,5);

		$sql = "SELECT b.fapr_id, c.fapr_desc, b.eco_value, b.rj_value from qc_fg_fault_header a 
				join qc_fg_fault_detail b on a.fgf_id = b.fgf_id
				join (SELECT * FROM qc_fg_fault_parameter WHERE sub_plant = '{$rhead[fgf_sub_plant]}') AS c on b.fapr_id = c.fapr_id
				WHERE a.fgf_id = '{$fgf_id}' 
				ORDER BY CAST(b.fapr_id AS int) ASC";
		
	} else {
		if($_POST['subplan']) {
			$rhead[fgf_sub_plant] = $_POST['subplan'];
		} else {
			if($app_subplan <> 'All') {
				$rhead[fgf_sub_plant] = $app_subplan;
			} else {
				$rhead[fgf_sub_plant] = 'A';
			}
		}
		$sql = "SELECT fapr_id, fapr_desc from qc_fg_fault_parameter WHERE sub_plant = '{$rhead[fgf_sub_plant]}' AND fapr_status = 'N' ORDER BY CAST(fapr_id AS int) ASC";
	}

	$qry = dbselect_plan_all($app_plan_id, $sql);

	$out .= '<table class="table table-bordered table-condensed table-hover">';
	$out .= '<tr>';		
		$out .= '<th rowspan="2" style="vertical-align:middle;">NO</th>';		
		$out .= '<th rowspan="2" style="vertical-align:middle;">DEFECT</th>';		
		$out .= '<th colspan="2">AVERAGE DEFECT</th>';		
	$out .= '</tr>';
	$out .= '<tr>';		
		$out .= '<th>ECO</th>';		
		$out .= '<th>RJ</th>';		
	$out .= '</tr>';

	$no=1;

	$ttleco = 0;
	$ttlrj  = 0;
	foreach($qry as $r){
		if($r[eco_value] == 0){
			$r[eco_value] = '';
		}else{
			$r[eco_value] = $r[eco_value];
		}

		if($r[rj_value] == 0){
			$r[rj_value] = '';
		}else{
			$r[rj_value] = $r[rj_value];
		}


		$out .= '<tr>';		
			$out .= '<td widht="4%" class="text-center">'.$no.'</td>';		
			$out .= '<td>'.$r[fapr_desc].'</td>';		
			$out .= '<td>';
				$out .= '<input class="form-control input-sm text-right" type="hidden" name="fapr_id['.$no.']" id="fapr_id_'.$no.'" value="'.$r[fapr_id].'" readonly>';
				$out .= '<input class="form-control input-sm text-right eco" type="text" name="eco_value['.$no.']" id="eco_value_'.$no.'" value="'.$r[eco_value].'" onkeyup="hanyanumerik(this.id,this.value);hitungTotal(\'eco\');">';
			$out .= '</td>';	

			$out .= '<td>';
				$out .= '<input class="form-control input-sm text-right rj" type="text" name="rj_value['.$no.']" id="rj_value_'.$no.'" value="'.$r[rj_value].'" onkeyup="hanyanumerik(this.id,this.value);;hitungTotal(\'rj\');">';
			$out .= '</td>';		
		$out .= '</tr>';		
	$no++;
	$ttleco += $r[eco_value];
	$ttlrj  += $r[rj_value];
	}

	$out .= '<tr>
		    <th colspan="2" class="text-center">TOTAL</th>
		    <td><input class="form-control input-sm text-right" type="text" name="tot_eco" id="tot_eco" value="'.$ttleco.'" readonly></td>
		    <td><input class="form-control input-sm text-right" type="text" name="tot_rj" id="tot_rj" value="'.$ttlrj.'" readonly></td>
		    </tr>';

	if($stat == "edit" || $stat == "add") {
		$out .= '<tr>
		    <td colspan="4" class="text-center"><button type="button" class="btn btn-primary btn-sm" onClick="simpanData(\''.$stat.'\')">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button></td>
		    </tr>';
	} else {
		$out .= '<tr>
		    <td colspan="4" class="text-center"><button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button></td>
		    </tr>';
	}
	$out .= '</table>';

    if($stat == "edit" || $stat == "view") {
    	$responce->fgf_id = $rhead[fgf_id];
    	$responce->fgf_sub_plant = $rhead[fgf_sub_plant];
    	$responce->fgf_date = $rhead[date];
    	$responce->fgf_time = $rhead[time];
    	$responce->fgf_type = $rhead[fgf_type];
    	$responce->fgf_kiln = $rhead[fgf_kiln];
    	$responce->fgf_quality = $rhead[fgf_quality];
    }
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>