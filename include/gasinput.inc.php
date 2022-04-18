<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['33'];
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
		$whdua .= " and qgh_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and qgh_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['qgh_id']) {
		$whdua .= " and qgh_id = '".$_POST['qgh_id']."'";
	}
	if($_POST['qgh_sub_plant']) {
		$whdua .= " and qgh_sub_plant = '".$_POST['qgh_sub_plant']."'";
	}
	if($_POST['qgh_date']) {
		$whdua .= " and qgh_date = '".$_POST['qgh_date']."'";
	}
	if($_POST['first_name']) {
		$whdua .= " and lower(first_name) like '%".strtolower($_POST['first_name'])."%'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_gas_header left join app_user on(qc_gas_header.qgd_user_create=app_user.user_name) where qgh_rec_stat='N' and qgh_date >= '{$tglfrom}' and qgh_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT qc_gas_header.*, app_user.first_name from qc_gas_header
			left join app_user on(qc_gas_header.qgd_user_create=app_user.user_name) 
			where qgh_rec_stat='N' and qgh_date >= '{$tglfrom}' and qgh_date <= '{$tglto}' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['qgh_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qgh_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qgh_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['qgh_date']);
			$ro['date'] = $datetime[0];
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qgh_id'],$ro['qgh_sub_plant'],$ro['date'],$ro['first_name'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function suburai(){
	global $app_plan_id;
	$qgh_id = $_POST['qgh_id'];
	$sql = "SELECT b.qmu_desc, c.qgpd_desc, a.qgd_line, a.qgd_value from qc_gas_detail a join qc_mesin_unit b on(a.qgd_mesin=b.qmu_code) join qc_gas_prep_detail c on(a.qgd_mesin=c.qgpd_mesin_code and a.qgd_seq=c.qgpd_seq) where a.qgh_id = '{$qgh_id}' order by b.qmu_seq, a.qgd_line, a.qgd_seq";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	foreach($qry as $ro){
		$responce->rows[$i]['cell']=array($ro['qmu_desc'],$ro['qgd_line'],$ro['qgpd_desc'],$ro['qgd_value']);
		$i++;
	}		
	echo json_encode($responce);	
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[qgh_date] = cgx_dmy2ymd($r[qgh_date]);
	$r[qgh_shift] = cgx_angka($r[qgh_shift]);
	if($stat == "add") {
		$sqlcek = "SELECT qgh_id from qc_gas_header where qgh_rec_stat = 'N' and qgh_sub_plant = '{$r[qgh_sub_plant]}' and qgh_date = '{$r[qgh_date]}' limit 1";
		$rcek = dbselect_plan($app_plan_id, $sqlcek);
		if($rcek[qgh_id]) {
			echo "Input data untuk tanggal ".$r[qgh_date]." sudah ada";
			exit();
		}
		$r[qgd_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[qgd_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(qgh_id) as qgh_id_max from qc_gas_header where qgh_sub_plant = '{$r[qgh_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[qgh_id_max] == ''){
			$mx[qgh_id_max] = 0;
		} else {
			$mx[qgh_id_max] = substr($mx[qgh_id_max],-7);
		}
		$urutbaru = $mx[qgh_id_max]+1;
		$r[qgh_id] = $app_plan_id.$r[qgh_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		$sql = "INSERT into qc_gas_header(qgh_sub_plant, qgh_id, qgh_date, qgh_shift, qgh_rec_stat, qgd_user_create, qgd_date_create) values('{$r[qgh_sub_plant]}', '{$r[qgh_id]}', '{$r[qgh_date]}', {$r[qgh_shift]}, 'N', '{$r[qgd_user_create]}', '{$r[qgd_date_create]}');";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k2sql = "";
			foreach ($r[qgd_seq] as $i => $value) {
				$r[qgd_seq][$i] = cgx_angka($r[qgd_seq][$i]);
				$k2sql .= "INSERT into qc_gas_detail(qgh_id, qgd_mesin, qgd_seq, qgd_line, qgd_value) values('{$r[qgh_id]}', '{$r[qgd_mesin][$i]}', {$r[qgd_seq][$i]}, '{$r[qgd_line][$i]}', '{$r[qgd_value][$i]}');";
			}
			$out = dbsave_plan($app_plan_id, $k2sql);
		} else {
			$out = $xsql;
		}
	} else if($stat=='edit') {
		$r[qgd_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[qgd_date_modify] = date("Y-m-d H:i:s");
		$sql = "UPDATE qc_gas_header set qgd_user_modify = '{$r[qgd_user_modify]}', qgd_date_modify = '{$r[qgd_date_modify]}' where qgh_id = '{$r[qgh_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_gas_detail where qgh_id = '{$r[qgh_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			if($x1sql == "OK") {
				$k2sql = "";
				foreach ($r[qgd_seq] as $i => $value) {
					$r[qgd_seq][$i] = cgx_angka($r[qgd_seq][$i]);
					$k2sql .= "INSERT into qc_gas_detail(qgh_id, qgd_mesin, qgd_seq, qgd_line, qgd_value) values('{$r[qgh_id]}', '{$r[qgd_mesin][$i]}', {$r[qgd_seq][$i]}, '{$r[qgd_line][$i]}', '{$r[qgd_value][$i]}');";
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
	$qgh_id = $_POST['kode'];
	$sql = "UPDATE qc_gas_header set qgh_rec_stat = 'C' where qgh_id = '{$qgh_id}';";
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
		$qgh_id = $_POST['kode'];
		$sql0 = "SELECT * from qc_gas_header where qgh_id = '{$qgh_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$tglharini = $rhead['qgh_date'];
		$sql = "SELECT distinct a.qgd_mesin, b.qmu_desc, b.qmu_seq from qc_gas_detail a join qc_mesin_unit b on(a.qgd_mesin=b.qmu_code) where a.qgh_id = '{$qgh_id}' order by b.qmu_seq";
		$sql3 = "SELECT distinct a.qgd_line from qc_gas_detail a where a.qgd_line is not null and a.qgd_line <> '' and a.qgh_id = '{$qgh_id}' order by a.qgd_line";
	} else {
		$rhead[qgh_sub_plant] = $_POST['subplan'] ? $_POST['subplan'] : ($app_subplan <> 'All' ? $app_subplan : 'A');
		$tglharini = cgx_dmy2ymd($_POST['tanggal']);
		$sql = "SELECT distinct a.qgp_mesin_code as qgd_mesin, b.qmu_desc, b.qmu_seq from qc_gas_prep a join qc_mesin_unit b on(a.qgp_mesin_code=b.qmu_code) where a.qgp_sub_plant = '{$rhead[qgh_sub_plant]}' order by b.qmu_seq";
		$sql3 = "SELECT distinct a.qgp_line as qgd_line from qc_gas_prep a where a.qgp_line is not null and a.qgp_line <> '' and a.qgp_sub_plant = '{$rhead[qgh_sub_plant]}' order by a.qgp_line";
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
    		$sql2 = "SELECT distinct a.qgd_mesin, a.qgd_seq, qgpd_desc, c.qgu_code from qc_gas_detail a join qc_gas_prep_detail b on(a.qgd_mesin=b.qgpd_mesin_code and a.qgd_seq=b.qgpd_seq) left join qc_gen_um c on (b.qgpd_um_id=c.qgu_id) where a.qgh_id = '{$qgh_id}' and a.qgd_mesin = '{$r[qgd_mesin]}' order by a.qgd_seq";
    		$sql3 = "SELECT distinct a.qgd_line from qc_gas_detail a where a.qgh_id = '{$qgh_id}' and a.qgd_mesin = '{$r[qgd_mesin]}' order by a.qgd_line";
    		$qry3 = dbselect_plan_all($app_plan_id, $sql3);
    		$sql4 = "SELECT * from qc_gas_detail where qgh_id = '{$qgh_id}' and qgd_mesin = '{$r[qgd_mesin]}' order by qgd_seq, qgd_line";
    		$qry4 = dbselect_plan_all($app_plan_id, $sql4);
			foreach($qry4 as $r4) { 
				$nilainya["$r4[qgd_mesin]"]["$r4[qgd_seq]"]["$r4[qgd_line]"] = $r4[qgd_value];
			}
    	} else {
    		$sql2 = "SELECT a.qgpd_mesin_code as qgd_mesin, a.qgpd_seq as qgd_seq, a.qgpd_desc, b.qgu_code from qc_gas_prep_detail a left join qc_gen_um b on (a.qgpd_um_id=b.qgu_id) where a.qgpd_mesin_code = '{$r[qgd_mesin]}' order by a.qgpd_seq";
    		$sql3 = "SELECT distinct a.qgp_line as qgd_line from qc_gas_prep a where a.qgp_sub_plant = '{$rhead[qgh_sub_plant]}' and a.qgp_mesin_code='{$r[qgd_mesin]}' order by a.qgp_line";
    		$qry3 = dbselect_plan_all($app_plan_id, $sql3);		
    	}
    	$sql5 = "SELECT qgh_id as idlalu from qc_gas_header where qgh_sub_plant = '{$rhead[qgh_sub_plant]}' and qgh_date < '{$tglharini}' order by qgh_date desc limit 1";
		$r5 = dbselect_plan($app_plan_id, $sql5);
		if($r5[idlalu]) {
			$sql6 = "SELECT * from qc_gas_detail where qgh_id = '{$r5[idlalu]}' and qgd_mesin = '{$r[qgd_mesin]}' order by qgd_seq, qgd_line";
	    	$qry6 = dbselect_plan_all($app_plan_id, $sql6);
			foreach($qry6 as $r6) { 
				$nilama["$r6[qgd_mesin]"]["$r6[qgd_seq]"]["$r6[qgd_line]"] = $r6[qgd_value];
			}
		}
		$qry2 = dbselect_plan_all($app_plan_id, $sql2);
		$out .= '<tr><th><span onClick="hideGrup('.$k.')">'.$r[qmu_desc].'</span></th>';
        foreach($qry3 as $r3) {
        	$r3[label_line] = $r3[qgd_line] ? 'Line '.$r3[qgd_line] : '';
        	$out .= '<th>'.$r3[label_line].'</th>';
        }
        $out .= '</tr>';
        foreach($qry2 as $r2) {
        	$r2[satuan] = $r2[qgu_code] ? ' ('.$r2[qgu_code].') ' : '';
			$out .= '<tr id="trdet_ke_'.$i.'" class="trgrup_ke_'.$k.'">
	        	<td>'.$r2[qgpd_desc].$r2[satuan].'</td>';
	        foreach($qry3 as $r3) {
	        	$out .= '<td><input type="hidden" name="qgd_mesin['.$i.']" value="'.$r2[qgd_mesin].'"><input type="hidden" name="qgd_seq['.$i.']" id="qgd_seq_'.$i.'" value="'.$r2[qgd_seq].'"><input type="hidden" name="qgd_line['.$i.']" id="qgd_line_'.$i.'" value="'.$r3[qgd_line].'">';
	        	if($r2[qgd_seq] == '1') {
	        		$out .= '<select class="form-control input-sm" name="qgd_value['.$i.']" id="qgd_value_'.$r2[qgd_mesin].'_'.$r2[qgd_seq].'_'.$r3[qgd_line].'">'.cboukuran($nilainya[$r2[qgd_mesin]][$r2[qgd_seq]][$r3[qgd_line]],true).'</select>';
	        	} else if($r2[qgd_seq] == '2') {
	        		$out .= '<input class="form-control input-sm" type="text" name="qgd_value['.$i.']" id="qgd_value_'.$r2[qgd_mesin].'_'.$r2[qgd_seq].'_'.$r3[qgd_line].'" value="'.$nilainya[$r2[qgd_mesin]][$r2[qgd_seq]][$r3[qgd_line]].'" style="text-align:right;" onkeyup="hitungVolume(\''.$r2[qgd_mesin].'\',\''.$r2[qgd_seq].'\',\''.$r3[qgd_line].'\',\''.$nilama[$r2[qgd_mesin]][$r2[qgd_seq]][$r3[qgd_line]].'\',this.value)">';
	        	} else if($r2[qgd_seq] == '3') {
	        		$out .= '<input class="form-control input-sm" type="text" name="qgd_value['.$i.']" id="qgd_value_'.$r2[qgd_mesin].'_'.$r2[qgd_seq].'_'.$r3[qgd_line].'" value="'.$nilainya[$r2[qgd_mesin]][$r2[qgd_seq]][$r3[qgd_line]].'" style="text-align:right;" onkeyup="hanyanumerik(this.id,this.value)" readonly>';  
	        	} else {
	        		$out .= '<input class="form-control input-sm" type="text" name="qgd_value['.$i.']" id="qgd_value_'.$r2[qgd_mesin].'_'.$r2[qgd_seq].'_'.$r3[qgd_line].'" value="'.$nilainya[$r2[qgd_mesin]][$r2[qgd_seq]][$r3[qgd_line]].'" style="text-align:right;" onkeyup="hanyanumerik(this.id,this.value)">';
	        	}
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
		$datetime = explode(' ',$rhead['qgh_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$responce->qgh_id = $rhead[qgh_id];
		$responce->qgh_date = $rhead[date];
		$responce->qgh_shift =  cbo_shift($rhead[qgh_shift]);
		$responce->qgh_sub_plant = $rhead[qgh_sub_plant];
	}
    $responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>