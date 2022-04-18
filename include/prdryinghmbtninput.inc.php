<?php

include_once("../libs/init.php");

$sTable_h = "qc_pd_cm_header";
$sTable_d = "qc_pd_cm_detail";

$akses = $_SESSION[$app_id]['app_priv']['67'];
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
	case "cboline":
		cboline($_POST['subplan']);
		break;
	case "detailtabel":
		detailtabel($_POST['stat']);
		break;
	case "cboshift":
		cboshift();
		break;
	case "htgmenit":
		htgmenit();
		break;
}


function htgmenit(){
	$start = $_POST['start'];
	$stop  = $_POST['stop'];

	$jml = strtotime($stop)-strtotime($start);
	$jmlmenit = $jml/60;

	echo $jmlmenit;

}


function cboline($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	switch ($subplan) {
		case 'All':
			$qry = array("","1","2","3");
			break;
		case 'A':
			$qry = array("","1","2","3");
			break;
		case 'B':
			$qry = array("","1","2","3");
			break;
		case 'C':
			$qry = array("","1","2","3");
			break;
	}

	if(is_array($qry)) {
		foreach($qry as $r) {
			if($r == $nilai) {
				$out .= "<option value='{$r}' selected>$r</option>";
			} else {
				$out .= "<option value='{$r}'>$r</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;
	}
}


function cbopress($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qpp_code, qpp_desc from qc_pd_press where qpp_sub_plant = '{$subplan}' order by qpp_code";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if(trim($r[qpp_code]) == $nilai){
				$out .= "<option value='$r[qpp_code]' selected>$r[qpp_code]</option>";
			} else {
				$out .= "<option value='$r[qpp_code]'>$r[qpp_code]</option>";
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
		$whdua .= " and hph_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and hph_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['hph_id']) {
		$whdua .= " and hph_id = '".$_POST['hph_id']."'";
	}
	if($_POST['hph_sub_plant']) {
		$whdua .= " and hph_sub_plant = '".$_POST['hph_sub_plant']."'";
	}
	if($_POST['hph_date']) {
		$whdua .= " and to_char(hph_date, 'DD-MM-YYYY')  = '".$_POST['hph_date']."'";
	}
	if($_POST['hph_press']) {
		$whdua .= " and hph_press = '".$_POST['hph_press']."'";
	}
	if($_POST['hph_shift']) {
		$whdua .= " and hph_shift = '".$_POST['hph_shift']."'";
	}
	if($_POST['hph_line']) {
		$whdua .= " and hph_line = '".$_POST['hph_line']."'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) AS count FROM qc_pd_hp_header WHERE hph_status = 'N' AND hph_date >= '{$tglfrom}' AND hph_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT hph_id, hph_sub_plant, hph_date, hph_press, hph_line, hph_shift FROM qc_pd_hp_header 
			    WHERE hph_status = 'N' and hph_date >= '{$tglfrom}' and hph_date <= '{$tglto}' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['hph_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['hph_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['hph_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['hph_date']);
			$ro['date'] = cgx_dmy2ymd($datetime[0]);
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['hph_sub_plant'],$ro['hph_id'],$ro['date'],$ro['hph_shift'],$ro['hph_line'],$ro['hph_press'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}



function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[hph_date]  = cgx_dmy2ymd($r[hph_date])." 00:00:00";
	$r[hph_press] = cgx_angka($r[hph_press]);
	$r[hph_line]  = cgx_angka($r[hph_line]);
	$r[hph_shift] = cgx_angka($r[hph_shift]);
	if($stat == "add") {
		$r[hph_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[hph_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(hph_id) as hph_id_max from qc_pd_hp_header where hph_sub_plant = '{$r[hph_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[hph_id_max] == ''){
			$mx[hph_id_max] = 0;
		} else {
			$mx[hph_id_max] = substr($mx[hph_id_max],-7);
		}
		$urutbaru = $mx[hph_id_max]+1;
		$r[hph_id] = $app_plan_id.$r[hph_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		
		//cek jika duplikat
		$qdup = "SELECT COUNT(*) AS jmldup from qc_pd_hp_header 
				 WHERE hph_status = 'N' 
				 AND hph_sub_plant = '{$r[hph_sub_plant]}' 
				 AND hph_date = '{$r[hph_date]}'
				 AND hph_shift = '{$r[hph_shift]}'
				 AND hph_line = '{$r[hph_line]}'
				 AND hph_press = '{$r[hph_press]}' ";
		$ddup = dbselect_plan($app_plan_id, $qdup);

		if($ddup[jmldup] > 0){
			$out = "Terjadi Duplikat Data";
		}else{

			$sql = "INSERT into qc_pd_hp_header(hph_sub_plant, hph_id, hph_date, hph_press, hph_line, hph_shift, hph_user_create, hph_date_create, hph_status) values 
			       ('{$r[hph_sub_plant]}', '{$r[hph_id]}', '{$r[hph_date]}', {$r[hph_press]}, '{$r[hph_line]}', '{$r[hph_shift]}', '{$r[hph_user_create]}', '{$r[hph_date_create]}','N');";
			$xsql = dbsave_plan($app_plan_id, $sql); 
			if($xsql == "OK") {
				$k2sql = "";
				$tglidx = explode(" ",$r[hph_date]);
				foreach ($r[hpd_date_start] as $i => $value) {

					if($r[hpd_date_start] = ''){
						$r[hpd_date_start] = '00:00';
					}else{
						$r[hpd_date_start] = $r[hpd_date_start];
					}

					if($r[hpd_date_stop] = ''){
						$r[hpd_date_stop] = '00:00';
					}else{
						$r[hpd_date_stop] = $r[hpd_date_stop];
					}

					$start = $tglidx[0].' '.$r[hpd_date_start][$i].':00';
					$stop  = $tglidx[0].' '.$r[hpd_date_stop][$i].':00';

					$k2sql .= "INSERT into qc_pd_hp_detail(hph_id, hpd_date_start, hpd_date_stop, hpd_value) 
							   values('{$r[hph_id]}', '{$start}', '{$stop}', '{$r[hpd_value][$i]}');";
					
				}
				$out = dbsave_plan($app_plan_id, $k2sql);
			} else {
				$out = $xsql;
			}
		}
	} else if($stat=='edit') {
		$r[hph_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[hph_date_modify] = date("Y-m-d H:i:s");

		$sql = "UPDATE qc_pd_hp_header set hph_user_modify = '{$r[hph_user_modify]}', hph_date_modify = '{$r[hph_date_modify]}' where hph_id = '{$r[hph_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_pd_hp_detail where hph_id = '{$r[hph_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);

			$k2sql = "";


			//cek tgl
			$qtgl = "SELECT to_char(hph_date, 'YYYY-MM-DD') tgld from qc_pd_hp_header WHERE hph_id = '{$r[hph_id]}'";
			$dtgl = dbselect_plan($app_plan_id, $qtgl);

			foreach ($r[hpd_date_start] as $i => $value) {
				
				if($r[hpd_date_start] = ''){
					$r[hpd_date_start] = '00:00';
				}else{
					$r[hpd_date_start] = $r[hpd_date_start];
				}

				if($r[hpd_date_stop] = ''){
					$r[hpd_date_stop] = '00:00';
				}else{
					$r[hpd_date_stop] = $r[hpd_date_stop];
				}

				$start = $dtgl[tgld].' '.$r[hpd_date_start][$i].':00';
				$stop  = $dtgl[tgld].' '.$r[hpd_date_stop][$i].':00';

				$k2sql .= "INSERT into qc_pd_hp_detail(hph_id, hpd_date_start, hpd_date_stop, hpd_value) 
						   values('{$r[hph_id]}', '{$start}', '{$stop}', '{$r[hpd_value][$i]}');";
				
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
	$hph_id = $_POST['kode'];

	$sql = "UPDATE qc_pd_hp_header SET hph_status = 'C' WHERE hph_id = '{$hph_id}';";
	echo dbsave_plan($app_plan_id, $sql); 
}


function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant();
	$out .= $withselect ? "</select>" : "";
	echo $out;
}

function cboshift($nilai = "TIDAKADA"){
	$out = cbo_shift($nilai);
	echo $out;
}

function detailtabel($stat) {
	global $app_plan_id, $app_subplan;
	if($stat == "edit" || $stat == "view") {
		$hph_id = $_POST['kode'];
		$sql0 = "SELECT * FROM qc_pd_hp_header WHERE hph_id = '{$hph_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['hph_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
	} else {
		if($_POST['subplan']) {
			$rhead[hph_sub_plant] = $_POST['subplan'];
		} else {
			if($app_subplan <> 'All') {
				$rhead[hph_sub_plant] = $app_subplan;
			} else {
				$rhead[hph_sub_plant] = 'A';
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

    $out .= '</th><th>JAM START</th>
        	<th>JAM STOP</th>
        	<th>JUMLAH MENIT</th>
        	<th>KETERANGAN HAMBATAN</th>
        </tr>';

    if($stat == "edit" || $stat == "view") {
			$sql2 = "SELECT a.hph_id, to_char(b.hpd_date_start, 'HH:MI') AS starttime, to_char(b.hpd_date_stop, 'HH:MI') AS stoptime, b.hpd_value 
					 FROM qc_pd_hp_header a join qc_pd_hp_detail b on a.hph_id = b.hph_id
					 WHERE a.hph_id = '{$hph_id}' ORDER BY hpd_date_start";
			$qry2 = dbselect_plan_all($app_plan_id, $sql2);

			foreach($qry2 as $r2) {
				$start2 = $r2['starttime'];
				$stop2  = $r2['stoptime'];

				$jml2 = strtotime($stop2)-strtotime($start2);
				$jmlmenit2 = $jml2/60;

				$out .= '<tr id="trdet_ke_'.$i.'">
		        	<td class="text-center">';

		        if($stat == "edit") {
		        	$out .= '<a href="javascript:void(0)" class="btn btn-default btn-xs" onclick="hapusItem('.$i.')"><span class="glyphicon glyphicon-remove"></span></a>';
		        }else{
		        	$out .= '&nbsp';
		        }
		        $out .=	'</td>
		        	<td>
		        		<input class="form-control input-sm" name="hpd_date_start['.$i.']" id="hpd_date_start_'.$i.'" type="time" value="'.$r2[starttime].'">
		        	</td>
		        	<td>
		        		<input class="form-control input-sm" name="hpd_date_stop['.$i.']" id="hpd_date_stop_'.$i.'" type="time" value="'.$r2[stoptime].'" onchange="hitungDwWw('.$i.');">
		        	</td>
		        	<td>
		        		<input class="form-control input-sm" name="hpd_jml_menit['.$i.']" id="hpd_jml_menit_'.$i.'" value="'.$jmlmenit2.'" readonly>
		        	</td>
		        	<td>
		        		<textarea class="form-control" rows="3" name="hpd_value['.$i.']" id="hpd_value_['.$i.']">'.$r2[hpd_value].'</textarea>
		        	</td>
		        	</tr>';
				$i++;
			}
	}

    $out .= '<tr><td colspan="5">&nbsp;</td></tr>';

    if($stat == "edit" || $stat == "add") {
		$out .= '<tr>
		    <td colspan="5" class="text-center">
		    	<input type="hidden" id="barisLast" value="'.$i.'">
		    	<button type="button" class="btn btn-primary btn-sm" onClick="simpanData(\''.$stat.'\')">Simpan</button> 
		    	<button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button></td>
		    </tr>';
	} else {
		$out .= '<tr>
		    <td colspan="5" class="text-center">

		    	<button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button></td>
		    </tr>';
	}

    $out .= '</table>';




    if($stat == "edit" || $stat == "view") {
    	$responce->hph_id = $rhead[hph_id];
    	$responce->hph_date = $rhead[date];
    	$responce->hph_line = cboline($rhead[hph_sub_plant],$rhead[hph_line],true);
    	$responce->hph_shift = cbo_shift($rhead[hph_shift]);
    	$responce->hph_press = cbopress($rhead[hph_sub_plant],$rhead[hph_press],true);
    	$responce->hph_sub_plant = $rhead[hph_sub_plant];
    }
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>