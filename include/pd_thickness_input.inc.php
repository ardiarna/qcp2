<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['130'];
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
	case "cbopress":
		cbopress($_POST['subplan']);
		break;
	case "cbobalmil":
		cbobalmil($_POST['subplan']);
		break;
	case "detailtabel":
		detailtabel($_POST['stat']);
		break;
	case "cbosubplant":
		cbosubplant($_GET['withselect']);
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
		$whdua .= " and op_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and op_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['op_id']) {
		$whdua .= " and op_id = '".$_POST['op_id']."'";
	}
	if($_POST['op_sub_plant']) {
		$whdua .= " and op_sub_plant = '".$_POST['op_sub_plant']."'";
	}
	if($_POST['op_date']) {
		$whdua .= " and op_date = '".$_POST['op_date']."'";
	}
	if($_POST['op_press']) {
		$whdua .= " and op_press = '".$_POST['op_press']."'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_pd_thickness_header where op_rec_stat = 'N' and op_date >= '{$tglfrom}' and op_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT * from qc_pd_thickness_header where op_rec_stat = 'N' and op_date >= '{$tglfrom}' and op_date <= '{$tglto}' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['op_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['op_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['op_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$datetime = explode(' ',$ro['op_date']);
			$ro['date'] = $datetime[0];
			$ro['time'] = substr($datetime[1],0,5);
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['op_sub_plant'],$ro['op_id'],$ro['date'],$ro['time'],$ro['op_press'],$ro['op_shift'],$ro['op_ukuran'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}



function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[op_date]  = cgx_dmy2ymd($r[op_date])." ".$r[op_jam].":00";
	$r[op_press] = cgx_angka($r[op_press]);
	$r[op_shift] = check_shift($r[op_jam]);

	if($stat == "add") {
		$r[op_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[op_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(op_id) as op_id_max from qc_pd_thickness_header where op_sub_plant = '{$r[op_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[op_id_max] == ''){
			$mx[op_id_max] = 0;
		} else {
			$mx[op_id_max] = substr($mx[op_id_max],-7);
		}
		$urutbaru = $mx[op_id_max]+1;
		$r[op_id] = $app_plan_id.$r[op_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		
		$sql = "INSERT into qc_pd_thickness_header(op_sub_plant, op_id, op_date, op_shift, op_press, op_rec_stat, op_tekanan, op_format, op_ukuran, op_user_create, op_date_create) 
		        values('{$r[op_sub_plant]}', '{$r[op_id]}', '{$r[op_date]}', '{$r[op_shift]}', '{$r[op_press]}', 'N', '{$r[op_tekanan]}', '{$r[op_format]}', '{$r[op_ukuran]}', '{$r[op_user_create]}', '{$r[op_date_create]}');";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k2sql = "";
			foreach ($r[op_mould] as $i => $value) {
				foreach ($r[op_value.$i] as $isi => $nilai) {
					$k2sql .= "INSERT into qc_pd_thickness_detail(op_id, op_mould, op_urut, op_value) 
								values('{$r[op_id]}', '{$value}', '{$isi}', '{$nilai}');";
				}
			}
			$out = dbsave_plan($app_plan_id, $k2sql);
		} else {
			$out = $xsql;
		}
	} else if($stat=='edit') {
		$r[op_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[op_date_modify] = date("Y-m-d H:i:s");

		$sql = "UPDATE qc_pd_thickness_header set op_user_modify = '{$r[op_user_modify]}', op_date_modify = '{$r[op_date_modify]}', op_tekanan = '{$r[op_tekanan]}', op_ukuran = '{$r[op_ukuran]}' where op_id = '{$r[op_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_pd_thickness_detail where op_id = '{$r[op_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			if($x1sql == "OK") {
				$k2sql = "";
				foreach ($r[op_mould] as $i => $value) {
					foreach ($r[op_value.$i] as $isi => $nilai) {
						if(!empty($nilai)){
							$k2sql .= "INSERT into qc_pd_thickness_detail(op_id, op_mould, op_urut, op_value) values('{$r[op_id]}', '{$value}', '{$isi}', '{$nilai}');";
						}
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
	// echo $sql;
}

function hapus(){
	global $app_plan_id;
	$op_id = $_POST['kode'];

	$sql = "UPDATE qc_pd_thickness_header SET op_rec_stat = 'C' WHERE op_id = '{$op_id}';";
	echo dbsave_plan($app_plan_id, $sql); 
}

function cbopress($subplan, $nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT qpp_code, qpp_desc from qc_pd_press where qpp_sub_plant = '{$subplan}'";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[qpp_code] == $nilai){
				$out .= "<option value='{$r[qpp_code]}' selected>$r[qpp_code]</option>";
			} else {
				$out .= "<option value='{$r[qpp_code]}'>$r[qpp_code]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;	
	}
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
		$op_id = $_POST['kode'];
		$sql0 = "SELECT * from qc_pd_thickness_header where op_id = '{$op_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);

		$datetime = explode(' ',$rhead['op_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);
		$rhead['time'] = substr($datetime[1],0,5);
		
		$tblformat = explode('x',$rhead['op_format']);

		$rhead[rows] = $tblformat[0];
		$rhead[cols] = $tblformat[1];

		$sql = "SELECT distinct op_mould as qpm_code from qc_pd_thickness_detail where op_id = '{$op_id}' order by op_mould";


	} else {
		if($_POST['subplan']) {
			$rhead[op_sub_plant] = $_POST['subplan'];
		} else {
			if($app_subplan <> 'All') {
				$rhead[op_sub_plant] = $app_subplan;
			} else {
				$rhead[op_sub_plant] = 'A';
			}
		}
		$rhead[op_press] = $_POST['press'];

		$sql = "SELECT * from qc_pd_mouldset where qpm_sub_plant='{$rhead[op_sub_plant]}' and qpm_press_code='{$rhead[op_press]}' order by qpm_code";

		$rhead[rows] = 3;
		$rhead[cols] = 3;
	}

	$op_formatnew = $rhead[rows]."x".$rhead[cols];
	$k = 0;
	$out = '<table class="table"><input type="hidden" name="op_format" id="op_format" value="'.$op_formatnew.'" readonly>';
	$qry = dbselect_plan_all($app_plan_id, $sql);

	foreach($qry as $r) {
		$out .= '<tr><td><button type="button" class="btn btn-default btn-sm btn-block" style="text-align:left;" onClick="hideGrup(\''.$k.'\')">CAVITTY '.$r[qpm_code].'</button></td></tr>
			<tr id="trgrup_ke_'.$k.'"><td>';
		


		$out .= '<table border ="1" style="border-collapse: collapse">';

		

		if($stat == "edit" || $stat == "view") {
			$sql2 = "SELECT * FROM qc_pd_thickness_detail WHERE op_id = '{$op_id}' order by op_mould, op_urut";
			$responce->sql = $sql2; 
			$qry2 = dbselect_plan_all($app_plan_id, $sql2);
			if(is_array($qry2)) {
				foreach($qry2 as $r2){
					$arr_nilai["$r2[op_mould]"]["$r2[op_urut]"] = $r2[op_value];
				}
			}
		} 

		$urutm =1;
		for ($row=1; $row <= $rhead[rows]; $row++) { 
			$out .= '<tr>';
			for ($col=1; $col <= $rhead[cols]; $col++) { 
			   	$p = $col * $row;
			   	$k_new = $k+1;


			   	if($stat == "edit" || $stat == "view"){
			   		$r2[op_value] = $arr_nilai[$r[qpm_code]][$urutm];
			   	}else{
			   		$r2[op_value] = "";
			   	}
			   		if($urutm == 2 || $urutm == 4 || $urutm == 6 || $urutm == 8) {
			   			$out .= '<td>
			   					<input type="hidden" name="op_mould['.$k_new.']" id="op_mould_'.$k_new.'" value="'.$r[qpm_code].'" readonly>
			   					<input class="form-control input-sm text-center" name="op_value'.$k_new.'['.$urutm.']" id="op_value'.$k_new.'_'.$urutm.'" type="text" value="'.$r2[op_value].'" onkeyup="hanyanumerik(this.id,this.value);">
			   				</td>';
			   		} else {
			   			$out .= '<td style="background-color:black;">&nbsp;</td>';
			   		}
			   		
			   	 $urutm++;
			   	}
		  	    $out .= '</tr>';
		}


		$out .= '</table>';

        $out .= '</td></tr>';
        $k++;
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
    	$responce->op_id = $rhead[op_id];
    	$responce->op_sub_plant = $rhead[op_sub_plant];
    	$responce->op_date = $rhead[date];
    	$responce->op_jam = $rhead[time];
    	$responce->op_tekanan = $rhead[op_tekanan];
    	$responce->op_ukuran = $rhead[op_ukuran];
    	$responce->op_press = cbopress($rhead[op_sub_plant], $rhead[op_press], true);
    }
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant();
	$out .= $withselect ? "</select>" : "";
	echo $out;
}

?>