<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['83'];
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
		$whdua .= " and kb_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and kb_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['kb_id']) {
		$whdua .= " and kb_id = '".$_POST['kb_id']."'";
	}
	if($_POST['kb_sub_plant']) {
		$whdua .= " and kb_sub_plant = '".$_POST['kb_sub_plant']."'";
	}
	if($_POST['kb_date']) {
		$whdua .= " and to_char(kb_date, 'DD-MM-YYYY')  = '".$_POST['kb_date']."'";
	}
	if($_POST['kb_kiln']) {
		$whdua .= " and kb_kiln = '".$_POST['kb_kiln']."'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) AS count FROM qc_fg_kiln_bending_header WHERE kb_status = 'N' AND kb_date >= '{$tglfrom}' AND kb_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT kb_id, kb_sub_plant, kb_date, kb_kiln, kb_temp, kb_speed, kb_presi, kb_desc, kb_wa, kb_ac, kb_wm, kb_tt FROM qc_fg_kiln_bending_header 
			    WHERE kb_status = 'N' and kb_date >= '{$tglfrom}' and kb_date <= '{$tglto}' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['kb_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['kb_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['kb_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['kb_date']);
			$ro['date'] = cgx_dmy2ymd($datetime[0]);
			$ro['time'] = substr($datetime[1],0,5);
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['kb_sub_plant'],$ro['kb_id'],$ro['date'],$ro['time'],$ro['kb_kiln'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}



function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[kb_date]  = cgx_dmy2ymd($r[kb_date])." ".$r[kb_jam].":00";
	if($stat == "add") {
		$r[kb_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[kb_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(kb_id) as kb_id_max from qc_fg_kiln_bending_header where kb_sub_plant = '{$r[kb_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[kb_id_max] == ''){
			$mx[kb_id_max] = 0;
		} else {
			$mx[kb_id_max] = substr($mx[kb_id_max],-7);
		}
		$urutbaru = $mx[kb_id_max]+1;
		$r[kb_id] = $app_plan_id.$r[kb_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		
		//cek jika duplikat
		$qdup = "SELECT COUNT(*) AS jmldup from qc_fg_kiln_bending_header 
				 WHERE kb_status = 'N' 
				 AND kb_sub_plant = '{$r[kb_sub_plant]}' 
				 AND kb_date = '{$r[kb_date]}'
				 AND kb_kiln = '{$r[kb_kiln]}'";
		$ddup = dbselect_plan($app_plan_id, $qdup);

		if($ddup[jmldup] > 0){
			$out = "Terjadi Duplikat Data";
		}else{

			if(is_array($r[kbd_posisi])) {
					$sql = "INSERT into qc_fg_kiln_bending_header(kb_sub_plant, kb_id, kb_date, kb_kiln, kb_temp, kb_speed, kb_presi, kb_desc, kb_status, kb_user_create, kb_date_create, kb_wa, kb_ac, kb_wm, kb_tt) values 
					       ('{$r[kb_sub_plant]}', '{$r[kb_id]}', '{$r[kb_date]}', {$r[kb_kiln]}, '{$r[kb_temp]}', '{$r[kb_speed]}', '{$r[kb_presi]}', '{$r[kb_desc]}','N', '{$r[kb_user_create]}', '{$r[kb_date_create]}', '{$r[kb_wa]}', '{$r[kb_ac]}', '{$r[kb_wm]}', '{$r[kb_tt]}');";
					$xsql = dbsave_plan($app_plan_id, $sql); 
					if($xsql == "OK") {
						$k2sql = "";
						
							foreach ($r[kbd_posisi] as $i => $value) {
								$k2sql .= "INSERT into qc_fg_kiln_bending_detail(kb_id, kbd_posisi, kbd_kg, kbd_cm) 
										   values('{$r[kb_id]}', '{$r[kbd_posisi][$i]}', '{$r[kbd_kg][$i]}', '{$r[kbd_cm][$i]}');";
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
		if(is_array($r[kbd_posisi])) {
				$r[kb_user_modify] = $_SESSION[$app_id]['user']['user_name'];
				$r[kb_date_modify] = date("Y-m-d H:i:s");

				$sql = "UPDATE qc_fg_kiln_bending_header SET kb_user_modify = '{$r[kb_user_modify]}', kb_date_modify = '{$r[kb_date_modify]}', 
							   kb_temp = '{$r[kb_temp]}', 
							   kb_speed = '{$r[kb_speed]}', 
							   kb_presi = '{$r[kb_presi]}', 
							   kb_desc = '{$r[kb_desc]}',
							   kb_wa = '{$r[kb_wa]}',
							   kb_ac = '{$r[kb_ac]}',
							   kb_wm = '{$r[kb_wm]}',
							   kb_tt = '{$r[kb_tt]}' 
						WHERE kb_id = '{$r[kb_id]}';";
				$xsql = dbsave_plan($app_plan_id, $sql); 
				if($xsql == "OK") {
					$k1sql = "DELETE from qc_fg_kiln_bending_detail where kb_id = '{$r[kb_id]}';";
					$x1sql = dbsave_plan($app_plan_id, $k1sql);

					$k2sql = "";
					if(is_array($r[kbd_posisi])) {
						foreach ($r[kbd_posisi] as $i => $value) {
							$k2sql .= "INSERT into qc_fg_kiln_bending_detail(kb_id, kbd_posisi, kbd_kg, kbd_cm) 
									   values('{$r[kb_id]}', '{$r[kbd_posisi][$i]}', '{$r[kbd_kg][$i]}', '{$r[kbd_cm][$i]}');";
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
	$kb_id = $_POST['kode'];

	$sql = "UPDATE qc_fg_kiln_bending_header SET kb_status = 'C' WHERE kb_id = '{$kb_id}';";
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
		$kb_id = $_POST['kode'];
		$sql0 = "SELECT * FROM qc_fg_kiln_bending_header WHERE kb_id = '{$kb_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['kb_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$rhead['time'] = substr($datetime[1],0,5);
	} else {
		if($_POST['subplan']) {
			$rhead[kb_sub_plant] = $_POST['subplan'];
		} else {
			if($app_subplan <> 'All') {
				$rhead[kb_sub_plant] = $app_subplan;
			} else {
				$rhead[kb_sub_plant] = 'A';
			}
		}
	}

	$i = 0;
	$out .= '<table id="tabeldetail" class="table table-bordered table-condensed table-hover">';

	$out .=	'<tr>
        	<th width="10">';
        if($stat == "add" || $stat == "edit") {
        	$out .= '<a href="javascript:void(0)" class="btn btn-default btn-xs" onClick="tambahItem()"><span class="glyphicon glyphicon-plus"></span></a>';
        }else{
        	$out .= '&nbsp';
        }

    $out .= '</th><th>POSISI</th>
        	<th>BENDING STRENGTH</th>
        	<th>BREAKING STRENGTH</th>
        </tr>';

    if($stat == "edit" || $stat == "view") {
    		$sql2a = "SELECT COUNT(*) AS jml FROM qc_fg_kiln_bending_detail WHERE kb_id = '{$kb_id}'";
			$qry2a = dbselect_plan($app_plan_id, $sql2a);


			$sql2 = "SELECT * FROM qc_fg_kiln_bending_detail WHERE kb_id = '{$kb_id}' ORDER BY kbd_posisi";
			$qry2 = dbselect_plan_all($app_plan_id, $sql2);

			foreach($qry2 as $r2) {
				$out .= '<tr id="trdet_ke_'.$i.'">
		        	<td class="text-center">';

		        if($stat == "edit") {
		        	if($i+1 == $qry2a[jml]){
		        		$styleremove = '';
		        	}else{
		        		$styleremove = 'style="display:none"';
		        	}

		        	$out .= '<div id="linkremove'.$i.'" '.$styleremove.'><a href="javascript:void(0)" class="btn btn-default btn-xs" onclick="hapusItem('.$i.')"><span class="glyphicon glyphicon-remove"></span></a></div>';
		        }else{
		        	$out .= '&nbsp';
		        }
		        $out .=	'</td>
		        	<td>
		        		<input class="form-control input-sm" name="kbd_posisi['.$i.']" id="kbd_posisi_'.$i.'" type="number" value="'.$r2[kbd_posisi].'">
		        	</td>
		        	<td>
		        		<input class="form-control input-sm text-right" name="kbd_kg['.$i.']" id="kbd_kg_'.$i.'" type="text" value="'.$r2[kbd_kg].'" onkeyup="hanyanumerik(this.id,this.value);hitungKg('.$i.');">
		        	</td>
		        	<td>
		        		<input class="form-control input-sm text-right" name="kbd_cm['.$i.']" id="kbd_cm_'.$i.'" value="'.$r2[kbd_cm].'" onkeyup="hanyanumerik(this.id,this.value);hitungKg('.$i.');">
		        	</td>
		        	</tr>';
				$i++;
			}
	}

    if($stat == "edit" || $stat == "add") {
		$out .= '<tr>
		    <td colspan="4" class="text-center">
		    	<input type="hidden" id="barisLast" value="'.$i.'">
		    </td>
		    </tr>';
		$tombol = '<button type="button" class="btn btn-primary btn-sm" onClick="simpanData(\''.$stat.'\')">Simpan</button> 
		    	<button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button>';
	} else {
		$tombol = '<button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button>';
	}

    $out .= '</table>';




    if($stat == "edit" || $stat == "view") {
    	$responce->kb_id = $rhead[kb_id];
    	$responce->kb_date = $rhead[date];
    	$responce->kb_jam = $rhead[time];
    	$responce->kb_kiln = $rhead[kb_kiln];
    	$responce->kb_temp = $rhead[kb_temp];
    	$responce->kb_speed = $rhead[kb_speed];
    	$responce->kb_presi = $rhead[kb_presi];
    	$responce->kb_desc = $rhead[kb_desc];
    	$responce->kb_sub_plant = $rhead[kb_sub_plant];
    	$responce->kb_wa = $rhead[kb_wa];
    	$responce->kb_ac = $rhead[kb_ac];
    	$responce->kb_wm = $rhead[kb_wm];
    	$responce->kb_tt = $rhead[kb_tt];
    }
	$responce->detailtabel = $out;
	$responce->tombol = $tombol; 
	echo json_encode($responce);
}

?>