<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['62'];
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
	case "cbobalmil":
		cbobalmil($_POST['subplan']);
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
		$whdua .= " and qpdh_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and qpdh_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['qpdh_id']) {
		$whdua .= " and qpdh_id = '".$_POST['qpdh_id']."'";
	}
	if($_POST['qpdh_sub_plant']) {
		$whdua .= " and qpdh_sub_plant = '".$_POST['qpdh_sub_plant']."'";
	}
	if($_POST['qpdh_date']) {
		$whdua .= " and qpdh_date = '".$_POST['qpdh_date']."'";
	}
	if($_POST['qpdh_shift']) {
		$whdua .= " and qpdh_shift = '".$_POST['qpdh_shift']."'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_pd_hsl_header where qpdh_status = 'N' and qpdh_date >= '{$tglfrom}' and qpdh_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT qpdh_id, qpdh_sub_plant, qpdh_date, qpdh_shift from qc_pd_hsl_header where qpdh_status = 'N' and qpdh_date >= '{$tglfrom}' and qpdh_date <= '{$tglto}' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['qpdh_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qpdh_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qpdh_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['qpdh_date']);
			$ro['date'] = $datetime[0];
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qpdh_id'],$ro['qpdh_sub_plant'],$ro['date'],$ro['qpdh_shift'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}



function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[qpdh_date] = cgx_dmy2ymd($r[qpdh_date]);
	$r[qpdh_shift] = cgx_angka($r[qpdh_shift]);
	if($stat == "add") {
		$r[qpdh_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[qpdh_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(qpdh_id) as qpdh_id_max from qc_pd_hsl_header where qpdh_sub_plant = '{$r[qpdh_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[qpdh_id_max] == ''){
			$mx[qpdh_id_max] = 0;
		} else {
			$mx[qpdh_id_max] = substr($mx[qpdh_id_max],-7);
		}
		$urutbaru = $mx[qpdh_id_max]+1;
		$r[qpdh_id] = $app_plan_id.$r[qpdh_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		
		//cek jika duplikat
		$qdup = "SELECT COUNT(*) AS jmldup from qc_pd_hsl_header where qpdh_status = 'N' and qpdh_sub_plant = '{$r[qpdh_sub_plant]}' 
				 AND qpdh_date = '{$r[qpdh_date]}'
				 AND qpdh_shift = '{$r[qpdh_shift]}' ";
		$ddup = dbselect_plan($app_plan_id, $qdup);

		if($ddup[jmldup] > 0){
			$out = "DUPLIKAT";
		}else{

			$sql = "INSERT into qc_pd_hsl_header(qpdh_id, qpdh_sub_plant, qpdh_date, qpdh_user_create, qpdh_date_create, qpdh_shift, qpdh_status) values('{$r[qpdh_id]}', '{$r[qpdh_sub_plant]}', '{$r[qpdh_date]}', '{$r[qpdh_user_create]}', '{$r[qpdh_date_create]}', {$r[qpdh_shift]}, 'N');";
			$xsql = dbsave_plan($app_plan_id, $sql); 
			if($xsql == "OK") {
				$k2sql = "";
				foreach ($r[qcpdd_seq] as $i => $value) {
					if ($r[qcpdm_group][$i] == '01') {
						if($r[qpdh_pd_value][$i] == ''){
							$valdata = 0;
						}else{
							$valdata = addslashes($r[qpdh_pd_value][$i]);
						}
						$k2sql .= "INSERT into qc_pd_hsl_detail(qpdh_id, qcpdm_group, qcpdd_seq, qpp_press_no, qpdh_pd_value) 
								values('{$r[qpdh_id]}', '{$r[qcpdm_group][$i]}', '{$r[qcpdd_seq][$i]}', '{$r[qpp_press_no][$i]}', '{$valdata}');";
					} else {
						$valdata = addslashes($r[qpdh_pd_value][$i]);
						$k2sql .= "INSERT into qc_pd_hsl_detail(qpdh_id, qcpdm_group, qcpdd_seq, qpdh_pd_value) 
								values('{$r[qpdh_id]}', '{$r[qcpdm_group][$i]}', '{$r[qcpdd_seq][$i]}', '{$valdata}');";
					}
				}
				$out = dbsave_plan($app_plan_id, $k2sql);
			} else {
				$out = $xsql;
			}
		}
	} else if($stat=='edit') {
		$r[qpdh_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[qpdh_date_modify] = date("Y-m-d H:i:s");

		$sql = "UPDATE qc_pd_hsl_header set qpdh_user_modify = '{$r[qpdh_user_modify]}', qpdh_date_modify = '{$r[qpdh_date_modify]}' where qpdh_id = '{$r[qpdh_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_pd_hsl_detail where qpdh_id = '{$r[qpdh_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			if($x1sql == "OK") {
				$k2sql = "";
				foreach ($r[qcpdd_seq] as $i => $value) {
					if ($r[qcpdm_group][$i] == '01') {
						if($r[qpdh_pd_value][$i] == ''){
							$valdata = 0;
						}else{
							$valdata = addslashes($r[qpdh_pd_value][$i]);
						}
						$k2sql .= "INSERT into qc_pd_hsl_detail(qpdh_id, qcpdm_group, qcpdd_seq, qpp_press_no, qpdh_pd_value) 
								values('{$r[qpdh_id]}', '{$r[qcpdm_group][$i]}', '{$r[qcpdd_seq][$i]}', '{$r[qpp_press_no][$i]}', '{$valdata}');";
					} else {
						$valdata = addslashes($r[qpdh_pd_value][$i]);
						$k2sql .= "INSERT into qc_pd_hsl_detail(qpdh_id, qcpdm_group, qcpdd_seq, qpdh_pd_value) 
								values('{$r[qpdh_id]}', '{$r[qcpdm_group][$i]}', '{$r[qcpdd_seq][$i]}', '{$valdata}');";
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
	$qpdh_id = $_POST['kode'];

	$sql = "UPDATE qc_pd_hsl_header SET qpdh_status = 'C' WHERE qpdh_id = '{$qpdh_id}';";
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

function cbosilo($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qpp_code from qc_pd_press where qpp_sub_plant = '{$subplan}'";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[qpp_code] == $nilai) {
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


function detailtabel($stat) {
	global $app_plan_id, $app_subplan;
	if($stat == "edit" || $stat == "view") {
		$qpdh_id = $_POST['kode'];
		$sql0 = "SELECT * from qc_pd_hsl_header where qpdh_id = '{$qpdh_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['qpdh_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$sql = "SELECT distinct qc_pd_hsl_detail.qcpdm_group, qcpdm_desc from qc_pd_hsl_detail join qc_pd_prep_group on(qc_pd_hsl_detail.qcpdm_group=qc_pd_prep_group.qcpdm_group) where qpdh_id = '{$qpdh_id}' order by qc_pd_hsl_detail.qcpdm_group";
	} else {
		if($_POST['subplan']) {
			$rhead[qpdh_sub_plant] = $_POST['subplan'];
		} else {
			if($app_subplan <> 'All') {
				$rhead[qpdh_sub_plant] = $app_subplan;
			} else {
				$rhead[qpdh_sub_plant] = 'A';
			}
		}
		$sql = "SELECT qcpdm_group, qcpdm_desc from qc_pd_prep_group order by qcpdm_group";
		$stylehid = 'style="display:none;"';
	}
	$k = 0;
	$i = 0;
	$out = '<table class="table">';
	$qry = dbselect_plan_all($app_plan_id, $sql);
	foreach($qry as $r) {
		$out .= '<tr><td><button type="button" class="btn btn-default btn-sm btn-block" style="text-align:left;" onClick="hideGrup(\''.$k.'\')">'.$r[qcpdm_group].'. INPUT '.$r[qcpdm_desc].'</button></td></tr>
			<tr id="trgrup_ke_'.$k.'" '.$stylehid.'><td>';
		if($r[qcpdm_group] == "01") {
			$out .= '<table class="table table-bordered table-condensed table-striped">
				<tr>
	        	<th width="90">NO. PRESS</th>';
	        $sql2 = "SELECT qcpdm_group as qcpdm_group, qcpdd_seq as qcpdd_seq, qcpdd_control_desc from qc_pd_prep_group_detil
	        		where qcpdm_group = '{$r[qcpdm_group]}' order by qcpdd_seq";
			$qry2 = dbselect_plan_all($app_plan_id, $sql2);
			foreach($qry2 as $r2) {
		        $out .= '<th>'.$r2[qcpdd_control_desc].'</th>';
			}
			if($stat == "edit" || $stat == "view") {
				// $sql3 = "SELECT distinct qpp_press_no from qc_pd_hsl_detail where qpdh_id = '{$qpdh_id}' and qcpdm_group = '{$r[qcpdm_group]}'";
				$sql3 = "SELECT qpp_code as qpp_press_no from qc_pd_press where qpp_sub_plant = '{$rhead[qpdh_sub_plant]}' order by qpp_code";
				$qry3 = dbselect_plan_all($app_plan_id, $sql3);
			} else {
				$sql3 = "SELECT qpp_code as qpp_press_no from qc_pd_press where qpp_sub_plant = '{$rhead[qpdh_sub_plant]}' order by qpp_code";
				$qry3 = dbselect_plan_all($app_plan_id, $sql3);
			}
			$idxsilo = 1;
			foreach($qry3 as $r3) {
				$out .= '<tr>
							<td>
								<input class="form-control input-sm text-center" type="text" value="'.$r3[qpp_press_no].'" readonly>
							</td>';	

		        foreach($qry2 as $r2) {
		        	$out .= '<td>
		        				<input type="hidden" name="qcpdm_group['.$i.']" id="qcpdm_group_'.$i.'" value="'.$r2[qcpdm_group].'">
		        				<input type="hidden" name="qcpdd_seq['.$i.']" id="qcpdd_seq_'.$i.'" value="'.$r2[qcpdd_seq].'">';
		        	if($stat == "edit" || $stat == "view") {
		        		$sql4 = "SELECT qpdh_pd_value from qc_pd_hsl_detail where qpdh_id = '{$qpdh_id}' and qcpdm_group = '{$r2[qcpdm_group]}' and qcpdd_seq = '{$r2[qcpdd_seq]}' and qpp_press_no = '{$r3[qpp_press_no]}'";
						$r4 = dbselect_plan($app_plan_id, $sql4); 
		        		$out .= '<input class="nosilo_'.$idxsilo.'" type="hidden" name="qpp_press_no['.$i.']" id="qpp_press_no_'.$i.'" value="'.$r3[qpp_press_no].'">
		        			<input class="form-control input-sm text-right stokpow'.$r2[qcpdd_seq].'" type="text" name="qpdh_pd_value['.$i.']" id="qpdh_pd_value_'.$i.'" onkeyup="hanyanumerik(this.id,this.value);hitungTotalStokPow('.$r2[qcpdd_seq].');" value="'.$r4[qpdh_pd_value].'"></td>';
		        	} else {
		        		$out .= '<input class="nosilo_'.$idxsilo.'" type="hidden" name="qpp_press_no['.$i.']" id="qpp_press_no_'.$i.'" value="'.$r3[qpp_press_no].'">
		        				<input class="form-control input-sm text-right stokpow'.$r2[qcpdd_seq].'" type="text" name="qpdh_pd_value['.$i.']" id="qpdh_pd_value_'.$i.'" value="" onkeyup="hanyanumerik(this.id,this.value);hitungTotalStokPow('.$r2[qcpdd_seq].');"></td>';
		        	}
					$i++;
				}
				$out .= '</tr>';
				$idxsilo++;
			}
			//total
			$out .= '<tr>
					  <td class="text-center">TOTAL</td>
					  ';
			//looping total
			$sqlttl = "SELECT qcpdd_seq from qc_pd_prep_group_detil where qcpdm_group = '{$r[qcpdm_group]}' order by qcpdd_seq";
			$qryttl = dbselect_plan_all($app_plan_id, $sqlttl);
			foreach($qryttl as $rttl) {
				if($stat == "edit" || $stat == "view") {
	        		$sql_sum = "SELECT SUM(CAST(qpdh_pd_value AS decimal)) AS ttl_val from qc_pd_hsl_detail where qpdh_id = '{$qpdh_id}' and qcpdm_group = '{$r2[qcpdm_group]}' and qcpdd_seq = '{$rttl[qcpdd_seq]}'";
					$r_sum = dbselect_plan($app_plan_id, $sql_sum); 

					$out .= '<td>
						<input class="form-control input-sm text-right" type="text" name="tot_stokpow'.$rttl[qcpdd_seq].'" id="tot_stokpow'.$rttl[qcpdd_seq].'" value="'.$r_sum[ttl_val].'" readonly>
					  	</td>';
				}else{

					$out .= '<td>
						<input class="form-control input-sm text-right" type="text" name="tot_stokpow'.$rttl[qcpdd_seq].'" id="tot_stokpow'.$rttl[qcpdd_seq].'" value="" readonly>
					  	</td>';
				}
			}
	        $k++;
	        $out .= '</tr></table>';
    	} else {
    		$out .= '<table class="table table-bordered table-condensed table-striped">
				<tr>
					<th class="text-left">'.$r[qcpdm_desc].'</th>';
	        $out .= '</tr>';
	        if($stat == "edit" || $stat == "view") {
				$sql2 = "SELECT qc_pd_hsl_detail.*, qcpdd_control_desc from qc_pd_hsl_detail join qc_pd_prep_group_detil on(qc_pd_hsl_detail.qcpdm_group=qc_pd_prep_group_detil.qcpdm_group and qc_pd_hsl_detail.qcpdd_seq=qc_pd_prep_group_detil.qcpdd_seq)
						where qpdh_id = '{$qpdh_id}' and qc_pd_hsl_detail.qcpdm_group = '{$r[qcpdm_group]}' order by qcpdd_seq";
			} else {
				$sql2 = "SELECT qcpdm_group as qcpdm_group, qcpdd_seq as qcpdd_seq, qcpdd_control_desc from qc_pd_prep_group_detil 
						 where qcpdm_group = '{$r[qcpdm_group]}' order by qcpdd_seq";
			}
			$qry2 = dbselect_plan_all($app_plan_id, $sql2);
			foreach($qry2 as $r2) {
				$out .= '<tr>
		        	<td class="text-left">
		        		<input type="hidden" name="qcpdm_group['.$i.']" id="qcpdm_group_'.$i.'" value="'.$r2[qcpdm_group].'">
		        		<input type="hidden" name="qcpdd_seq['.$i.']" id="qcpdd_seq_'.$i.'" value="'.$r2[qcpdd_seq].'">
		        		<textarea rows="4" cols="100" name="qpdh_pd_value['.$i.']" id="qpdh_pd_value_'.$i.'">'.$r2[qpdh_pd_value].'</textarea>
		        	</td>';
		        $out .= '</tr>';
				$i++;
			}
	        $k++;
	        $out .= '</table>';
    	}
        $out .= '</td></tr>';
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
    	$responce->qpdh_id = $rhead[qpdh_id];
    	$responce->qpdh_sub_plant = $rhead[qpdh_sub_plant];
    	$responce->qpdh_date = $rhead[date];
    	$responce->qpdh_shift = cbo_shift($rhead[qpdh_shift]);
    }
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>