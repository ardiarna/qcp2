<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['65'];
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
	case "cbohambatan":
		cbohambatan($_POST['nilai']);
		break;
	case "detailtabel":
		detailtabel($_POST['stat']);
		break;
	case "pilihmotif":
		pilihmotif();
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
		$whdua .= " and qgh_subplant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and qgh_subplant = '".$subplan_kode."'";
	}
	if($_POST['qgh_id']) {
		$whdua .= " and qgh_id = '".$_POST['qgh_id']."'";
	}
	if($_POST['qgh_subplant']) {
		$whdua .= " and qgh_subplant = '".$_POST['qgh_subplant']."'";
	}
	if($_POST['qgh_date']) {
		$whdua .= " and qgh_date = '".$_POST['qgh_date']."'";
	}
	if($_POST['qgh_shift']) {
		$whdua .= " and qgh_shift = ".$_POST['qgh_shift']."";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_gl_header where qgh_rec_stat = 'N' and qgh_date >= '{$tglfrom}' and qgh_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT * from qc_gl_header where qgh_rec_stat = 'N' and qgh_date >= '{$tglfrom}' and qgh_date <= '{$tglto}' $whsatu $whdua order by $sidx $sord $limit";
		$qry = dbselect_plan_all($app_plan_id, $sql);
		$i = 0;
	} else { 
		$total_pages = 1; 
	}
	if($page > $total_pages) $page = $total_pages; 
	$responce->page = $page; 
	$responce->total = $total_pages; 
	$responce->records = $count; 
	if( $count > 0 ) {
		foreach($qry as $ro){
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['qgh_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qgh_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qgh_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qgh_id'],$ro['qgh_subplant'],$ro['qgh_date'],$ro['qgh_shift'],$ro['kontrol']); 
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[qgh_date] = cgx_dmy2ymd($r[qgh_date]);
	$r[qgh_shift] = cgx_angka($r[qgh_shift]);
	if($stat == "add") {
		$r[qgh_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[qgh_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(qgh_id) as qgh_id_max from qc_gl_header where qgh_subplant = '{$r[qgh_subplant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[qgh_id_max] == ''){
			$mx[qgh_id_max] = 0;
		} else {
			$mx[qgh_id_max] = substr($mx[qgh_id_max],-7);
		}
		$urutbaru = $mx[qgh_id_max]+1;
		$r[qgh_id] = $app_plan_id.$r[qgh_subplant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		$r[qgh_absensi]    = addslashes($r[qgh_absensi]);
		$r[qgh_keterangan] = addslashes($r[qgh_keterangan]);
		$sql = "INSERT into qc_gl_header(qgh_subplant, qgh_id, qgh_date, qgh_shift, qgh_absensi, qgh_keterangan, qgh_rec_stat, qgh_user_create, qgh_date_create) values('{$r[qgh_subplant]}', '{$r[qgh_id]}', '{$r[qgh_date]}', {$r[qgh_shift]}, '{$r[qgh_absensi]}', '{$r[qgh_keterangan]}', 'N', '{$r[qgh_user_create]}', '{$r[qgh_date_create]}');";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k2sql = "";
			foreach ($r[qgd_motif] as $i => $value) {
				if($r[qgd_motif][$i]) {
					$r[qgd_hasil][$i] = cgx_angka($r[qgd_hasil][$i]);
					$r[qgd_reject][$i] = cgx_angka($r[qgd_reject][$i]);
					$k2sql .= "INSERT into qc_gl_detail(qgh_id, qgd_motif, qgd_hasil, qgd_reject, qgd_hambatan) values('{$r[qgh_id]}', '{$r[qgd_motif][$i]}', {$r[qgd_hasil][$i]}, {$r[qgd_reject][$i]}, '{$r[qgd_hambatan][$i]}');";
				}
			}
			$out = dbsave_plan($app_plan_id, $k2sql);
		} else {
			$out = $xsql;
		}
	} else if($stat=='edit') {
		$r[qgh_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[qgh_date_modify] = date("Y-m-d H:i:s");
		$r[qgh_absensi]    = addslashes($r[qgh_absensi]);
		$r[qgh_keterangan] = addslashes($r[qgh_keterangan]);
		$sql = "UPDATE qc_gl_header set qgh_shift = '{$r[qgh_shift]}', qgh_absensi = '{$r[qgh_absensi]}', qgh_keterangan = '{$r[qgh_keterangan]}', qgh_user_modify = '{$r[qgh_user_modify]}', qgh_date_modify = '{$r[qgh_date_modify]}' where qgh_id = '{$r[qgh_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_gl_detail where qgh_id = '{$r[qgh_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			if($x1sql == "OK") {
				$k2sql = "";
				foreach ($r[qgd_motif] as $i => $value) {
					if($r[qgd_motif][$i]) {
						$r[qgd_hasil][$i] = cgx_angka($r[qgd_hasil][$i]);
						$r[qgd_reject][$i] = cgx_angka($r[qgd_reject][$i]);
						$k2sql .= "INSERT into qc_gl_detail(qgh_id, qgd_motif, qgd_hasil, qgd_reject, qgd_hambatan) values('{$r[qgh_id]}', '{$r[qgd_motif][$i]}', {$r[qgd_hasil][$i]}, {$r[qgd_reject][$i]}, '{$r[qgd_hambatan][$i]}');";
					}
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
	$qgh_id = $_POST['qgh_id'];
	$sql = "UPDATE qc_gl_header set qgh_rec_stat = 'C' where qgh_id = '{$qgh_id}';";
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

function cbohambatan($nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qmh_code, qmh_nama from qc_md_hambatan order by qmh_code";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[qmh_code] == $nilai){
				$out .= "<option value='$r[qmh_code]' selected>$r[qmh_code] - $r[qmh_nama]</option>";
			} else {
				$out .= "<option value='$r[qmh_code]'>$r[qmh_code] - $r[qmh_nama]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;	
	}
}

function pilihmotif() {
	global $app_plan_id;
	if($_POST['txt_cari']) {
		$txt_cari = strtoupper($_POST['txt_cari']);
		$whsatu = "and upper(qmm_nama) like '%{$txt_cari}%'";
	}
	$sql = "SELECT qmm_nama from qc_md_motif where 1=1 $whsatu order by qmm_nama";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out = '<table class="table table-bordered table-striped table-condensed"><tbody><tr><th></th><th>CODE MOTIF</th></tr>';
	if(is_array($qry)) {
		foreach($qry as $r) {
			$qmm_nama = str_replace('"', '', $r[qmm_nama]);
			$out .= '<tr><td><span class="glyphicon glyphicon-ok" onClick="setMotif(\''.$qmm_nama.'\');"></span></td><td>'.$qmm_nama.'</td></tr>';
		}
	} else {
		$out .= '<tr><td colspan="2">Motif dengan nama : '.$txt_cari.' tidak ditemukan...</td></tr>';
	}	
	$out .= '</tbody></table>';

	$responce->out = $out;
	echo json_encode($responce);
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit" || $stat == "view") {
		$qgh_id = $_POST['qgh_id'];
		$sql0 = "SELECT * from qc_gl_header where qgh_id='{$qgh_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$sql = "SELECT * from qc_gl_detail where qgh_id = '{$qgh_id}' order by qgd_motif";
		$qry = dbselect_plan_all($app_plan_id, $sql);
		$i = 0;
		if(is_array($qry)) {
			$out = '<table id="tblitem" class="table table-bordered table-condensed table-hover table-striped"><tbody><tr><th colspan="2">CODE</th><th>HASIL, M2</th><th>REJECT, M2</th><th>HAMBATAN</th></tr>';
			foreach($qry as $r) {
				$out .= '<tr id="tritem_ke_'.$i.'"><td>';
				if($stat == "edit") {
					$out .= '<span class="glyphicon glyphicon-remove" onclick="hapusItem(\''.$i.'\')"></span>';
				}
				$out .= '</td><td><div class="input-group"><input class="form-control input-sm" name="qgd_motif['.$i.']" id="qgd_motif_'.$i.'" type="text" value="'.$r[qgd_motif].'" readonly><div class="input-group-addon" title="Pilih nama item"><span class="glyphicon glyphicon-option-horizontal" onClick="tampilMotif(\''.$i.'\');"></span></div></div></td><td><input class="form-control input-sm text-right" name="qgd_hasil['.$i.']" id="qgd_hasil_'.$i.'" value="'.$r[qgd_hasil].'" type="text" onkeyup="hanyanumerik(this.id,this.value);"></td><td><input class="form-control input-sm text-right" name="qgd_reject['.$i.']" id="qgd_reject_'.$i.'" value="'.$r[qgd_reject].'" type="text" onkeyup="hanyanumerik(this.id,this.value);"></td><td><select class="form-control input-sm" name="qgd_hambatan['.$i.']" id="qgd_hambatan_'.$i.'">'.cbohambatan($r[qgd_hambatan],true).'</select></td></tr>';
				$i++;
			}
			$out .= '</tbody></table>';
		}
		$datetime = explode(' ',$rhead[qgh_date]);
		$rhead[date] = cgx_dmy2ymd($datetime[0]);
		$responce->qgh_id = $rhead[qgh_id];
		$responce->qgh_subplant = $rhead[qgh_subplant];
		$responce->qgh_date = $rhead[date];
		$responce->qgh_shift = cbo_shift($rhead[qgh_shift]);
		$responce->qgh_absensi = $rhead[qgh_absensi];
		$responce->qgh_keterangan = $rhead[qgh_keterangan];
		$responce->detailtabel = $out;
		$responce->lastbarisitem = $i;
		$responce->jmlbarisitem = ($i+1);
	}
    echo json_encode($responce);
}

?>