<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['37'];
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
	case "suburai":
		suburai();
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
		$whdua .= " and qgp_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and qgp_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['qgp_id']) {
		$whdua .= " and qgp_id = '".$_POST['qgp_id']."'";
	}
	if($_POST['qgp_sub_plant']) {
		$whdua .= " and qgp_sub_plant = '".$_POST['qgp_sub_plant']."'";
	}
	if($_POST['qgp_date']) {
		$whdua .= " and qgp_date = '".$_POST['qgp_date']."'";
	}
	if($_POST['first_name']) {
		$whdua .= " and lower(first_name) like '%".strtolower($_POST['first_name'])."%'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_gas_produksi left join app_user on(qc_gas_produksi.qgp_user_create=app_user.user_name) where qgp_rec_stat='N' and qgp_date >= '{$tglfrom}' and qgp_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT qc_gas_produksi.*, app_user.first_name from qc_gas_produksi
			left join app_user on(qc_gas_produksi.qgp_user_create=app_user.user_name) 
			where qgp_rec_stat='N' and qgp_date >= '{$tglfrom}' and qgp_date <= '{$tglto}' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['qgp_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qgp_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qgp_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['qgp_date']);
			$ro['date'] = $datetime[0];
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qgp_id'],$ro['qgp_sub_plant'],$ro['date'],$ro['first_name'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function suburai(){
	global $app_plan_id;
	$qgp_id = $_POST['qgp_id'];
	$sql = "SELECT b.qmu_desc, c.qgpd2_desc, a.qgdp_line, a.qgdp_value from qc_gas_detail_produksi a join qc_mesin_unit b on(a.qgdp_mesin=b.qmu_code) join qc_gas_prep_detail_2 c on(a.qgdp_mesin=c.qgpd2_mesin_code and a.qgdp_seq=c.qgpd2_seq) where a.qgp_id = '{$qgp_id}' order by b.qmu_seq, a.qgdp_line, a.qgdp_seq";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	foreach($qry as $ro){
		$responce->rows[$i]['cell']=array($ro['qmu_desc'],$ro['qgdp_line'],$ro['qgpd2_desc'],$ro['qgdp_value']);
		$i++;
	}		
	echo json_encode($responce);	
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[qgp_date] = cgx_dmy2ymd($r[qgp_date]);
	$r[qgp_shift] = cgx_angka($r[qgp_shift]);
	if($stat == "add") {
		$sqlcek = "SELECT qgp_id from qc_gas_produksi where qgp_rec_stat = 'N' and qgp_sub_plant = '{$r[qgp_sub_plant]}' and qgp_date = '{$r[qgp_date]}' limit 1";
		$rcek = dbselect_plan($app_plan_id, $sqlcek);
		if($rcek[qgp_id]) {
			echo "Input data untuk tanggal ".$r[qgp_date]." sudah ada";
			exit();
		}
		$r[qgp_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[qgp_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(qgp_id) as qgp_id_max from qc_gas_produksi where qgp_sub_plant = '{$r[qgp_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[qgp_id_max] == ''){
			$mx[qgp_id_max] = 0;
		} else {
			$mx[qgp_id_max] = substr($mx[qgp_id_max],-7);
		}
		$urutbaru = $mx[qgp_id_max]+1;
		$r[qgp_id] = $app_plan_id.$r[qgp_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		$sql = "INSERT into qc_gas_produksi(qgp_sub_plant, qgp_id, qgp_date, qgp_shift, qgp_rec_stat, qgp_user_create, qgp_date_create) values('{$r[qgp_sub_plant]}', '{$r[qgp_id]}', '{$r[qgp_date]}', {$r[qgp_shift]}, 'N', '{$r[qgp_user_create]}', '{$r[qgp_date_create]}');";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k2sql = "";
			foreach ($r[qgdp_seq] as $i => $value) {
				$r[qgdp_seq][$i] = cgx_angka($r[qgdp_seq][$i]);
				$k2sql .= "INSERT into qc_gas_detail_produksi(qgp_id, qgdp_mesin, qgdp_seq, qgdp_line, qgdp_value) values('{$r[qgp_id]}', '{$r[qgdp_mesin][$i]}', {$r[qgdp_seq][$i]}, '{$r[qgdp_line][$i]}', '{$r[qgdp_value][$i]}');";
			}
			$out = dbsave_plan($app_plan_id, $k2sql);
		} else {
			$out = $xsql;
		}
	} else if($stat=='edit') {
		$r[qgp_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[qgp_date_modify] = date("Y-m-d H:i:s");
		$sql = "UPDATE qc_gas_produksi set qgp_user_modify = '{$r[qgp_user_modify]}', qgp_date_modify = '{$r[qgp_date_modify]}' where qgp_id = '{$r[qgp_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_gas_detail_produksi where qgp_id = '{$r[qgp_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			if($x1sql == "OK") {
				$k2sql = "";
				foreach ($r[qgdp_seq] as $i => $value) {
					$r[qgdp_seq][$i] = cgx_angka($r[qgdp_seq][$i]);
					$k2sql .= "INSERT into qc_gas_detail_produksi(qgp_id, qgdp_mesin, qgdp_seq, qgdp_line, qgdp_value) values('{$r[qgp_id]}', '{$r[qgdp_mesin][$i]}', {$r[qgdp_seq][$i]}, '{$r[qgdp_line][$i]}', '{$r[qgdp_value][$i]}');";
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
	$qgp_id = $_POST['kode'];
	$sql = "UPDATE qc_gas_produksi set qgp_rec_stat = 'C' where qgp_id = '{$qgp_id}';";
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

function cboukuran($nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT category_nama from category where length(category_kode) = 2 and jenis_kode = '2' order by category_kode";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[category_nama] == $nilai) {
				$out .= "<option value='$r[category_nama]' selected>$r[category_nama]</option>";
			} else {
				$out .= "<option value='$r[category_nama]'>$r[category_nama]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;
	}
}

function detailtabel($stat) {
	global $app_plan_id, $app_subplan;
	if($stat == "edit" || $stat == "view") { 
		$qgp_id = $_POST['kode'];
		$sql0 = "SELECT * from qc_gas_produksi where qgp_id = '{$qgp_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$tglharini = $rhead['qgp_date'];
		$sql = "SELECT distinct a.qgdp_mesin, b.qmu_desc, b.qmu_seq from qc_gas_detail_produksi a join qc_mesin_unit b on(a.qgdp_mesin=b.qmu_code) where a.qgp_id = '{$qgp_id}' order by b.qmu_seq";
		$sql3 = "SELECT distinct a.qgdp_line from qc_gas_detail_produksi a where a.qgdp_line is not null and a.qgdp_line <> '' and a.qgp_id = '{$qgp_id}' order by a.qgdp_line";
	} else {
		$rhead[qgp_sub_plant] = $_POST['subplan'] ? $_POST['subplan'] : ($app_subplan <> 'All' ? $app_subplan : 'A');
		$tglharini = cgx_dmy2ymd($_POST['tanggal']);
		$sql = "SELECT distinct a.qgp_mesin_code as qgdp_mesin, b.qmu_desc, b.qmu_seq from qc_gas_prep a join qc_mesin_unit b on(a.qgp_mesin_code=b.qmu_code) where a.qgp_sub_plant = '{$rhead[qgp_sub_plant]}' order by b.qmu_seq";
		$sql3 = "SELECT distinct a.qgp_line as qgdp_line from qc_gas_prep a where a.qgp_line is not null and a.qgp_line <> '' and a.qgp_sub_plant = '{$rhead[qgp_sub_plant]}' order by a.qgp_line";
	}
	$k = 0;
	$i = 0;
	$jmlkolom = 2;
	$out  = '<table class="table table-bordered table-condensed">';
	$qry3 = dbselect_plan_all($app_plan_id, $sql3);
	foreach($qry3 as $r3) {
        $jmlkolom +=1;
	}
	$qry = dbselect_plan_all($app_plan_id, $sql);
	foreach($qry as $r) {
		if($stat == "edit" || $stat == "view") {
    		$sql2 = "SELECT distinct a.qgdp_mesin, a.qgdp_seq, qgpd2_desc, c.qgu_code from qc_gas_detail_produksi a join qc_gas_prep_detail_2 b on(a.qgdp_mesin=b.qgpd2_mesin_code and a.qgdp_seq=b.qgpd2_seq) left join qc_gen_um c on (b.qgpd2_um_id=c.qgu_id) where a.qgp_id = '{$qgp_id}' and a.qgdp_mesin = '{$r[qgdp_mesin]}' order by a.qgdp_seq";
    		$sql3 = "SELECT distinct a.qgdp_line from qc_gas_detail_produksi a where a.qgp_id = '{$qgp_id}' and a.qgdp_mesin = '{$r[qgdp_mesin]}'";
    		$qry3 = dbselect_plan_all($app_plan_id, $sql3);
    		$sql4 = "SELECT * from qc_gas_detail_produksi where qgp_id = '{$qgp_id}' and qgdp_mesin = '{$r[qgdp_mesin]}' order by qgdp_seq, qgdp_line";
    		$qry4 = dbselect_plan_all($app_plan_id, $sql4);
			foreach($qry4 as $r4) { 
				$nilainya["$r4[qgdp_mesin]"]["$r4[qgdp_seq]"]["$r4[qgdp_line]"] = $r4[qgdp_value];
			}
    	} else {
    		$sql2 = "SELECT a.qgpd2_mesin_code as qgdp_mesin, a.qgpd2_seq as qgdp_seq, a.qgpd2_desc, b.qgu_code from qc_gas_prep_detail_2 a left join qc_gen_um b on (a.qgpd2_um_id=b.qgu_id) where a.qgpd2_mesin_code = '{$r[qgdp_mesin]}' order by a.qgpd2_seq";
    		$sql3 = "SELECT distinct a.qgp_line as qgdp_line from qc_gas_prep a where a.qgp_sub_plant = '{$rhead[qgp_sub_plant]}' and a.qgp_mesin_code='{$r[qgdp_mesin]}' order by a.qgp_line";
    		$qry3 = dbselect_plan_all($app_plan_id, $sql3);		
    	}
    	$sql5 = "SELECT qgp_id as idlalu from qc_gas_produksi where qgp_sub_plant = '{$rhead[qgp_sub_plant]}' and qgp_date < '{$tglharini}' order by qgp_date desc limit 1";
		$r5 = dbselect_plan($app_plan_id, $sql5);
		if($r5[idlalu]) {
			$sql6 = "SELECT * from qc_gas_detail_produksi where qgp_id = '{$r5[idlalu]}' and qgdp_mesin = '{$r[qgdp_mesin]}' order by qgdp_seq, qgdp_line";
	    	$qry6 = dbselect_plan_all($app_plan_id, $sql6);
			foreach($qry6 as $r6) { 
				$nilama["$r6[qgdp_mesin]"]["$r6[qgdp_seq]"]["$r6[qgdp_line]"] = $r6[qgdp_value];
			}
		}
		$qry2 = dbselect_plan_all($app_plan_id, $sql2);
		$out .= '<tr><th><span onClick="hideGrup('.$k.')">'.$r[qmu_desc].'</span></th>';
        foreach($qry3 as $r3) {
        	$r3[label_line] = $r3[qgdp_line] ? 'Line '.$r3[qgdp_line] : '';
        	$out .= '<th>'.$r3[label_line].'</th>';
        }
        $out .= '</tr>';
        foreach($qry2 as $r2) {
        	$r2[satuan] = $r2[qgu_code] ? ' ('.$r2[qgu_code].') ' : '';
			$out .= '<tr id="trdet_ke_'.$i.'" class="trgrup_ke_'.$k.'">
	        	<td>'.$r2[qgpd2_desc].$r2[satuan].'</td>';
	        foreach($qry3 as $r3) {
	        	$out .= '<td><input type="hidden" name="qgdp_mesin['.$i.']" value="'.$r2[qgdp_mesin].'"><input type="hidden" name="qgdp_seq['.$i.']" id="qgdp_seq_'.$i.'" value="'.$r2[qgdp_seq].'"><input type="hidden" name="qgdp_line['.$i.']" id="qgdp_line_'.$i.'" value="'.$r3[qgdp_line].'">';
	        	$out .= '<input class="form-control input-sm" type="text" name="qgdp_value['.$i.']" id="qgdp_value_'.$r2[qgdp_mesin].'_'.$r2[qgdp_seq].'_'.$r3[qgdp_line].'" value="'.$nilainya[$r2[qgdp_mesin]][$r2[qgdp_seq]][$r3[qgdp_line]].'" style="text-align:right;" onkeyup="hanyanumerik(this.id,this.value)">';
	        	$out .= '</td>';  
	        	$i++;
	        }
	        $out .= '</tr>';
		}
        $k++;
	}
	if($stat == "edit" || $stat == "add") {
		$out .= '<tr>
		    <td colspan="'.$jmlkolom.'" class="text-center"><button type="button" class="btn btn-primary btn-sm" onClick="simpanData(\''.$stat.'\')">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button></td>
		    </tr>';
		} else {
			$out .= '<tr>
		    <td colspan="'.$jmlkolom.'" class="text-center"><button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button></td>
		    </tr>';
		}
	$out .= '</table>';

	if($stat == "edit" || $stat == "view") {
		$datetime = explode(' ',$rhead['qgp_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$responce->qgp_id = $rhead[qgp_id];
		$responce->qgp_date = $rhead[date];
		$responce->qgp_shift =  cbo_shift($rhead[qgp_shift]);
		$responce->qgp_sub_plant = $rhead[qgp_sub_plant];
	}
    $responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>