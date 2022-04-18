<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['91'];
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
	case "cboshift":
		cboshift();
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
		$whdua .= " and fh_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and fh_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['fh_id']) {
		$whdua .= " and fh_id = '".$_POST['fh_id']."'";
	}
	if($_POST['fh_sub_plant']) {
		$whdua .= " and fh_sub_plant = '".$_POST['fh_sub_plant']."'";
	}
	if($_POST['fh_date']) {
		$whdua .= " and to_char(fh_date, 'DD-MM-YYYY')  = '".$_POST['fh_date']."'";
	}
	if($_POST['fh_shift']) {
		$whdua .= " and fh_shift = '".$_POST['fh_shift']."'";
	}
	if($_POST['fh_kiln']) {
		$whdua .= " and fh_kiln = '".$_POST['fh_kiln']."'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_fg_firing_header where fh_status='N' and fh_date >= '{$tglfrom}' and fh_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT fh_sub_plant, fh_id, fh_date, fh_kiln, fh_shift from qc_fg_firing_header where fh_status='N' and fh_date >= '{$tglfrom}' and fh_date <= '{$tglto}' $whsatu $whdua
			order by $sidx $sord $limit";
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['fh_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['fh_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['fh_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['fh_date']);
			$ro['date'] = cgx_dmy2ymd($datetime[0]);
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['fh_sub_plant'],$ro['fh_id'],$ro['date'],$ro['fh_kiln'],$ro['fh_shift'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

	

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[fh_shift] = cgx_angka($r[fh_shift]);
	$r[fh_date] = cgx_dmy2ymd($r[fh_date]);
	if($stat == "add") {
		$r[fh_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[fh_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(fh_id) as fh_id_max from qc_fg_firing_header where fh_sub_plant = '{$r[fh_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[fh_id_max] == ''){
			$mx[fh_id_max] = 0;
		} else {
			$mx[fh_id_max] = substr($mx[fh_id_max],-7);
		}
		$urutbaru = $mx[fh_id_max]+1;
		$r[fh_id] = $app_plan_id.$r[fh_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);

		//cek jika duplikat
		$qdup = "SELECT COUNT(*) AS jmldup from qc_fg_firing_header WHERE fh_status = 'N' AND fh_sub_plant = '{$r[fh_sub_plant]}' AND fh_date = '{$r[fh_date]}' AND fh_shift = '{$r[fh_shift]}' AND fh_kiln = '{$r[fh_kiln]}'";
		$ddup = dbselect_plan($app_plan_id, $qdup);

		if($ddup[jmldup] > 0){
			$out = "Terjadi Duplikat Data";
		}else{
				$sql = "INSERT into qc_fg_firing_header(fh_sub_plant, fh_id, fh_date, fh_kiln, fh_shift, fh_status, fh_user_create, fh_date_create) values('{$r[fh_sub_plant]}', '{$r[fh_id]}', '{$r[fh_date]}', '{$r[fh_kiln]}', '{$r[fh_shift]}', 'N', '{$r[fh_user_create]}', '{$r[fh_date_create]}');";
				$xsql = dbsave_plan($app_plan_id, $sql); 
				if($xsql == "OK") {
					$k2sql = "";
					foreach ($r[fc_gdid] as $i => $value) {
						$k2sql .= "INSERT into qc_fg_firing_detail(fh_id, fc_group, fc_gdid, fhd_value) values('{$r[fh_id]}', '{$r[fc_group][$i]}', '{$r[fc_gdid][$i]}', '{$r[fhd_value][$i]}' );";
					}
					$out = dbsave_plan($app_plan_id, $k2sql);
				} else {
					$out = $xsql;
				}
		}
	} else if($stat=='edit') {
		$r[fh_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[fh_date_modify] = date("Y-m-d H:i:s");
		$sql = "UPDATE qc_fg_firing_header set fh_user_modify = '{$r[fh_user_modify]}', fh_date_modify = '{$r[fh_date_modify]}' where fh_id = '{$r[fh_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_fg_firing_detail where fh_id = '{$r[fh_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			if($x1sql == "OK") {
				$k2sql = "";
				foreach ($r[fc_gdid] as $i => $value) {
					$k2sql .= "INSERT into qc_fg_firing_detail(fh_id, fc_group, fc_gdid, fhd_value) values('{$r[fh_id]}', '{$r[fc_group][$i]}', '{$r[fc_gdid][$i]}', '{$r[fhd_value][$i]}' );";
				}
				$out = dbsave_plan($app_plan_id, $k2sql);	
			} else {
				$out = $x1sql;
			}
		} else {
			$out = $xsql;
		}
	}
	echo $out;
}

function hapus(){
	global $app_plan_id;
	$qph_id = $_POST['kode'];
	$sql = "UPDATE qc_pd_header set qph_rec_stat='C' where qph_id = '{$qph_id}';";
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


function display_node($plan, $grup, $parent, $stat, $fh_id) {
	global $app_plan_id, $akses;
	$sqlcek = "SELECT COUNT(*) AS jmldata from qc_fg_firing_group_detail where fc_gdparrent = '$parent' and fc_sub_plant = '{$plan}' and fc_group = '{$grup}'";
	$qrycek = dbselect_plan($app_plan_id, $sqlcek);

	if($qrycek[jmldata] <= 0){
		$out .= '<tr><td colspan="3">Tidak ada data..'.$sqlcek.'</td></tr>';
	}else{
			if($stat == 'add'){
				$sql = "SELECT a.fc_gdid, a.fc_group, a.fc_sub_plant, a.fc_gdparrent, a.fc_gdunit, a.fc_gddesc, b.jml
						from qc_fg_firing_group_detail a 
						left join (select fc_gdparrent, count(fc_gdparrent) as jml 
									from qc_fg_firing_group_detail 
									where fc_sub_plant = '{$plan}' and fc_group = '{$grup}' and fc_gdstatus = 'N'
									group by fc_gdparrent) b
						on a.fc_gdid = b.fc_gdparrent
						where a.fc_gdparrent = '{$parent}' and a.fc_sub_plant = '{$plan}' and a.fc_group = '{$grup}' and a.fc_gdstatus = 'N'
						ORDER BY CAST(a.fc_gdid AS int) ASC";
			}else{
				$sql = "SELECT a.fh_sub_plant as fc_sub_plant, a.fh_id, 
								b.fc_group, b.fc_gdid, '' as fhd_std, b.fhd_value,
								c.fc_gddesc, c.fc_gdparrent, c.fc_gdunit, d.jml
						from qc_fg_firing_header a 
						left join qc_fg_firing_detail b on a.fh_id = b.fh_id
						left join (select fc_gdid, fc_gdunit, fc_gddesc, fc_gdparrent from qc_fg_firing_group_detail where fc_sub_plant = '{$plan}' and fc_group = '{$grup}') c on b.fc_gdid = c.fc_gdid
						left join (select fc_gdparrent, count(fc_gdparrent) as jml from qc_fg_firing_group_detail where fc_sub_plant = '{$plan}' and fc_group = '{$grup}' group by fc_gdparrent) d
						on b.fc_gdid = d.fc_gdparrent
						where a.fh_id = '{$fh_id}' and c.fc_gdparrent = '{$parent}' and a.fh_sub_plant = '{$plan}' and b.fc_group = '{$grup}'
						ORDER BY CAST(b.fc_gdid AS int) ASC";
			}

			$qry = dbselect_plan_all($app_plan_id, $sql);
			$no=1;
			foreach($qry as $ro){
				if($parent == 0){
					$no = $no;
					$styleshow = 'style="display:none"';
				}else{
					$no	= "";
					$styleshow = '';
				}

				if($ro[jml] > 0){
					$styleshow = 'style="display:none"';
				}else{
					$styleshow = '';
				}

				$out .= '<tr>
		    		<td class="text-center">'.$no.'</td>
		    		<td>'.$ro[fc_gddesc].'</td>
		    		<td>
		    			<input type="hidden" name="fc_group['.$ro[fc_gdid].']" id="fc_group_'.$ro[fc_gdid].'" value="'.$ro[fc_group].'" readonly>
		    			<input type="hidden" name="fc_gdid['.$ro[fc_gdid].']" id="fc_gdid_'.$ro[fc_gdid].'" value="'.$ro[fc_gdid].'" readonly>
		    			<input class="form-control input-sm" '.$styleshow.' type="text" name="fhd_std['.$ro[fc_gdid].']" id="fhd_std_'.$ro[fc_gdid].'" value="'.$ro[fhd_std].'" readonly>
		    		</td>
		    		<td class="text-center">'.$ro[fc_gdunit].'</td>
		    		<td>
		    			<input class="form-control input-sm" '.$styleshow.' type="text" name="fhd_value['.$ro[fc_gdid].']" id="fhd_value_'.$ro[fc_gdid].'" value="'.$ro[fhd_value].'">
		    		</td>
		    		</tr>';
				if ($ro[jml] > 0) {
					$out .= display_node($ro[fc_sub_plant],$ro[fc_group],$ro[fc_gdid], $stat, $ro[fh_id]);
		        }
			$no++;
			}
	}
	return $out;
}


function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit" || $stat == "view") {
		$fh_id = $_POST['kode'];
		$sql0 = "SELECT * from qc_fg_firing_header where fh_id = '{$fh_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['fh_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
	} else {
		$rhead[fh_sub_plant] = $_POST['subplan'];
		$stylehid = 'style="display:none;"';
	}
	
	$sql = "SELECT fc_group, fc_desc from qc_fg_firing_group ORDER BY CAST(fc_group AS int) ASC";
	$k = 0;
	$i = 0;
	$out = '<table class="table">';
	$qry = dbselect_plan_all($app_plan_id, $sql);
	foreach($qry as $r) {
		$out .= '<tr><td><button type="button" class="btn btn-default btn-sm btn-block" style="text-align:left;" onClick="hideGrup(\''.$k.'\')">'.$r[fc_group].'. INPUT '.$r[fc_desc].'</button></td></tr>
				<tr id="trgrup_ke_'.$k.'" '.$stylehid.'><td><table class="table table-bordered table-condensed table-striped">
					<tr>
		        	<th width="50">NO</th>    
		        	<th>DESCRIPSI</th>
		        	<th width="120">STANDAR</th>
		        	<th width="90">UNIT</th>
		        	<th>NILAI</th>';
        $out .= '</tr>';

        $out .= display_node($rhead[fh_sub_plant], $r[fc_group], 0, $stat, $fh_id);
        $k++;
        $out .= '</table></td></tr>';
	}
	if($stat == "edit" || $stat == "add") {
		$out .= '<tr>
		    <td class="text-center"><button type="button" class="btn btn-primary btn-sm" onClick="simpanData(\''.$stat.'\')">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button></td>
		    </tr>';
	} else {
		$out .= '<tr>
		    <td class="text-center"><button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button></td>
		    </tr>';
	}
	$out .= '</table>';
	
    if($stat == "edit" || $stat == "view") {
    	$responce->fh_id = $rhead[fh_id];
    	$responce->fh_sub_plant = $rhead[fh_sub_plant];
    	$responce->fh_date  = $rhead[date];
    	$responce->fh_kiln = $rhead[fh_kiln];
    	$responce->fh_shift = cbo_shift($rhead[fh_shift]);
    }
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>