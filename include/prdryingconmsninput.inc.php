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
	case "detailtabel":
		detailtabel($_POST['stat']);
		break;
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
		$whdua .= " and cmh_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and cmh_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['cmh_id']) {
		$whdua .= " and cmh_id = '".$_POST['cmh_id']."'";
	}
	if($_POST['cmh_sub_plant']) {
		$whdua .= " and cmh_sub_plant = '".$_POST['cmh_sub_plant']."'";
	}
	if($_POST['cmh_date']) {
		$whdua .= " and to_char(cmh_date, 'YYYY-MM-DD')  = '".$_POST['cmh_date']."'";
	}
	if($_POST['time']) {
		$whdua .= " and to_char(cmh_date, 'HH:MI')  = '".$_POST['time']."'";
	}
	if($_POST['cmh_press']) {
		$whdua .= " and cmh_press = '".$_POST['cmh_press']."'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) AS count FROM qc_pd_cm_header WHERE cmh_status = 'N' AND cmh_date >= '{$tglfrom}' AND cmh_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT cmh_id, cmh_sub_plant, cmh_date, cmh_press FROM qc_pd_cm_header 
			    WHERE cmh_status = 'N' and cmh_date >= '{$tglfrom}' and cmh_date <= '{$tglto}' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['cmh_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['cmh_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['cmh_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['cmh_date']);
			$ro['date'] = $datetime[0];
			$ro['time'] = substr($datetime[1],0,5);
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['cmh_sub_plant'],$ro['cmh_id'],$ro['date'],$ro['time'],$ro['cmh_press'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}



function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[cmh_date] = cgx_dmy2ymd($r[cmh_date])." ".$r[cmh_time].":00";
	$r[cmh_press] = cgx_angka($r[cmh_press]);
	if($stat == "add") {
		$r[cmh_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[cmh_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(cmh_id) as cmh_id_max from qc_pd_cm_header where cmh_sub_plant = '{$r[cmh_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[cmh_id_max] == ''){
			$mx[cmh_id_max] = 0;
		} else {
			$mx[cmh_id_max] = substr($mx[cmh_id_max],-7);
		}
		$urutbaru = $mx[cmh_id_max]+1;
		$r[cmh_id] = $app_plan_id.$r[cmh_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		
		//cek jika duplikat
		$qdup = "SELECT COUNT(*) AS jmldup from qc_pd_cm_header 
				 WHERE cmh_status = 'N' 
				 AND cmh_sub_plant = '{$r[cmh_sub_plant]}' 
				 AND cmh_date = '{$r[cmh_date]}'
				 AND cmh_press = '{$r[cmh_press]}' ";
		$ddup = dbselect_plan($app_plan_id, $qdup);

		if($ddup[jmldup] > 0){
			$out = "Terjadi Duplikat Data";
		}else{

			$sql = "INSERT into qc_pd_cm_header(cmh_sub_plant, cmh_id, cmh_date, cmh_press, cmh_user_create, cmh_date_create, cmh_status) values('{$r[cmh_sub_plant]}','{$r[cmh_id]}',  '{$r[cmh_date]}', '{$r[cmh_press]}', '{$r[cmh_user_create]}', '{$r[cmh_date_create]}', 'N');";
			$xsql = dbsave_plan($app_plan_id, $sql); 
			if($xsql == "OK") {
				$k2sql = "";
				foreach ($r[cd2_id] as $i => $value) {
					$k2sql .= "INSERT into qc_pd_cm_detail(cmh_id, cm_group, cd1_id, cd2_id, cmd_value) 
								values('{$r[cmh_id]}', '{$r[cm_group][$i]}', '{$r[cd1_id][$i]}', '{$r[cd2_id][$i]}', '{$r[cmd_value][$i]}');";
				}
				$out = dbsave_plan($app_plan_id, $k2sql);
			} else {
				$out = $xsql;
			}
		}
	} else if($stat=='edit') {
		$r[cmh_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[cmh_date_modify] = date("Y-m-d H:i:s");

		$sql = "UPDATE qc_pd_cm_header set cmh_user_modify = '{$r[cmh_user_modify]}', cmh_date_modify = '{$r[cmh_date_modify]}' where cmh_id = '{$r[cmh_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_pd_cm_detail where cmh_id = '{$r[cmh_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);

			$k2sql = "";
			foreach ($r[cd2_id] as $i => $value) {
				$k2sql .= "INSERT into qc_pd_cm_detail(cmh_id, cm_group, cd1_id, cd2_id, cmd_value) 
							values('{$r[cmh_id]}', '{$r[cm_group][$i]}', '{$r[cd1_id][$i]}', '{$r[cd2_id][$i]}', '{$r[cmd_value][$i]}');";
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
	$cmh_id = $_POST['kode'];

	$sql = "UPDATE qc_pd_cm_header SET cmh_status = 'C' WHERE cmh_id = '{$cmh_id}';";
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
		$cmh_id = $_POST['kode'];
		$sql0 = "SELECT * FROM qc_pd_cm_header WHERE cmh_id = '{$cmh_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['cmh_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$rhead['time'] = substr($datetime[1],0,5);

		$sql = "SELECT distinct a.cm_group, cm_desc FROM qc_pd_cm_detail a JOIN qc_pd_cm_group b on a.cm_group = b.cm_group
				WHERE cmh_id = '{$cmh_id}' order by a.cm_group";
	} else {
		if($_POST['subplan']) {
			$rhead[cmh_sub_plant] = $_POST['subplan'];
		} else {
			if($app_subplan <> 'All') {
				$rhead[cmh_sub_plant] = $app_subplan;
			} else {
				$rhead[cmh_sub_plant] = 'A';
			}
		}
		$sql = "SELECT cm_group, cm_desc from qc_pd_cm_group order by cm_group";
		$stylehid = 'style="display:none;"';
	}
	$k = 0;
	$i = 0;
	$ngrp = 1;
	$out = '<table class="table">';
	$qry = dbselect_plan_all($app_plan_id, $sql);
	foreach($qry as $r) {
		$out .= '<tr><td><button type="button" class="btn btn-default btn-sm btn-block" style="text-align:left;" onClick="hideGrup(\''.$k.'\')"><b>'.Romawi($ngrp).'. '.$r[cm_desc].'</b></button></td></tr>
			<tr id="trgrup_ke_'.$k.'" '.$stylehid.'><td>';
			$sql2 = "SELECT * from qc_pd_cm_group_d1 WHERE cm_group = '{$r[cm_group]}' order by cd1_id";
			$qry2 = dbselect_plan_all($app_plan_id, $sql2);
			$no_d1 = 'A';

			$out .= '<table class="table table-bordered table-condensed table-striped">';

			foreach($qry2 as $r2) {
			$out .= '<tr><td class="text-center" colspan="3"><b>'.$no_d1.'. '.$r2[cd1_desc].'</b></td></tr>';

				$sqlcek1 = "SELECT COUNT(*) AS jmldata1 FROM qc_pd_cm_group_d2 
						 WHERE sub_plant = '{$rhead[cmh_sub_plant]}' AND cm_group = '{$r[cm_group]}' AND cd1_id = '{$r2[cd1_id]}'";
				$qrycek1 = dbselect_plan($app_plan_id, $sqlcek1);

				if($qrycek1[jmldata1] > 0){
					
					if($stat == "edit" || $stat == "view") {
						$sql3 = "SELECT a.*, b.cd2_desc, b.cd2_type
								 FROM qc_pd_cm_detail a JOIN qc_pd_cm_group_d2 b ON a.cm_group=b.cm_group AND a.cd1_id=b.cd1_id AND a.cd2_id=b.cd2_id
								 WHERE a.cmh_id = '{$cmh_id}' AND b.sub_plant = '{$rhead[cmh_sub_plant]}' 
								 AND a.cm_group = '{$r2[cm_group]}' 
								 AND a.cd1_id = '{$r2[cd1_id]}' 
								 ORDER BY CAST(b.cd2_id AS int) ASC";
					} else {
						$sql3 = "SELECT * from qc_pd_cm_group_d2 
						 	WHERE sub_plant = '{$rhead[cmh_sub_plant]}' AND cm_group = '{$r[cm_group]}' AND cd1_id = '{$r2[cd1_id]}' AND cd2_status = 'N'
						 	ORDER BY CAST(cd2_id AS int) ASC";
					}
					$qry3 = dbselect_plan_all($app_plan_id, $sql3);

					$no_d2 = '1';
					foreach($qry3 as $r3) {
						$out .= '<tr>
							<td class="text-center" width="50" style="vertical-align:middle">'.$no_d2.'</td>
							<td  style="vertical-align:middle" width="40%">'.$r3[cd2_desc].'</td>
				        	<td>
				        		<input type="hidden" name="cm_group['.$i.']" id="qcpdm_group_'.$i.'" value="'.$r3[cm_group].'">
				        		<input type="hidden" name="cd1_id['.$i.']" id="cd1_id_'.$i.'" value="'.$r3[cd1_id].'">
				        		<input type="hidden" name="cd2_id['.$i.']" id="cd2_id_'.$i.'" value="'.$r3[cd2_id].'">';






				       

				        if($r3[cd2_type] == 'number'){
				        	$out .= '<input class="form-control input-sm text-right" type="text" name="cmd_value['.$i.']" id="cmd_value'.$i.'" onkeyup="hanyanumerik(this.id,this.value);" value="'.$r3[cmd_value].'">';
				        }else if($r3[cd2_type] == 'option'){
				        	
				        	if($r3[cmd_value] == 'Y'){
		     					$selectedY = 'selected';
		     				}else if($r3[cmd_value] == 'N'){
		     					$selectedN = 'selected';
		     				}else{
		     					$selected1 = 'selected';
		     				}
	     				
		     				$out .= '<select class="form-control input-sm" name="cmd_value['.$i.']" id="cmd_value'.$i.'">
		                                <option value="" '.$selected1.'></option>
		                                <option value="Y" '.$selectedY.'>&#xf00c;</option>
		                                <option value="N" '.$selectedN.'>&#xf00d;</option>
	                                </select>';
				        }else{
				        	$out .= '<input class="form-control input-sm" type="text" name="cmd_value['.$i.']" id="cmd_value'.$i.'" value="'.$r3[cmd_value].'">';
				        }	

				        $out .= '</td>';
				        $out .= '</tr>';
					$i++;
					$no_d2++;

					$selected1 ="";
					$selectedY ="";
					$selectedN ="";
					}	
				}else{
					$out .= '<tr><td class="text-center" colspan="3">Tidak ada data...</td></tr>';
				}

			$no_d1++;
				
			}

			$out .= '</table>';


        $out .= '</td></tr>';
        $k++;
	$ngrp++;
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
    	$responce->cmh_id = $rhead[cmh_id];
    	$responce->cmh_sub_plant = $rhead[cmh_sub_plant];
    	$responce->cmh_date = $rhead[date];
    	$responce->cmh_time = $rhead[time];
    	$responce->cmh_press = cbopress($rhead[cmh_sub_plant],$rhead[cmh_press],true);
    }
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>