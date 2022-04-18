<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['75'];
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
	case "cboklin":
		cboklin($_POST['subplan']);
		break;
	case "cboshift":
		cboshift();
		break;
	case "loadscp":
		loadscp();
		break;
	case "cbosubplant":
		cbosubplant($_GET['withselect']);
		break;
	case "detailtabel":
		detailtabel($_POST['stat'],$_POST['subplan']);
		break;
}


function loadscp(){
	global $app_plan_id;

	$tglrr      = cgx_dmy2ymd($_POST[kl_date])." 00:00:00";
	$planrr     = $_POST[kl_sub_plant];
	$id_kilnr = $_POST[id_kiln];


	if(!empty($_POST[id_kiln])){

		$qq = " SELECT kl_speed, kl_code, kl_presure, kl_time
				from qc_kiln_header where kl_date = '{$tglrr}' and kl_sub_plant = '{$planrr}' and id_kiln = '{$id_kilnr}'
				order by kl_time asc limit 1";
		$rr = dbselect_plan($app_plan_id, $qq);

		$responce->kl_speedrr 	= $rr[kl_speed];
	    $responce->kl_coderr 	= $rr[kl_code];
	    $responce->kl_presurerr = $rr[kl_presure];
		echo json_encode($responce);
	}
}

function cboklin($subplan, $nilai = "TIDAKADA", $irrest =  false) {
	global $app_plan_id;
	$sql  = "SELECT * FROM qc_kiln_mesin WHERE sub_plant = '{$subplan}' ORDER BY id_kiln ASC";
	$qry  = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option value=''></option>";
	foreach($qry as $r){
		if($r[id_kiln] == $nilai) {
			$out .= "<option value='{$r[id_kiln]}' selected>$r[desc_kiln]</option>";
		} else {
			$out .= "<option value='{$r[id_kiln]}'>$r[desc_kiln]</option>";
		}	
	}

	if($irrest){
		return $out;
	}else{
		echo $out;
	}
	
}

function cboshift($nilai = "TIDAKADA") {
	$qry = array("","1","2","3");	
	if(is_array($qry)) {
		foreach($qry as $r) {
			if($r == $nilai) {
				$out .= "<option value='{$r}' selected>$r</option>";
			} else {
				$out .= "<option value='{$r}'>$r</option>";
			}	
		}	
	}
	echo $out;
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
		$whdua .= " and kl_sub_plant = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and kl_sub_plant = '".$subplan_kode."'";
	}
	if($_POST['kl_id']) {
		$whdua .= " and kl_id = '".$_POST['kl_id']."'";
	}
	if($_POST['kl_sub_plant']) {
		$whdua .= " and kl_sub_plant = '".$_POST['kl_sub_plant']."'";
	}
	if($_POST['kl_date']) {
		$whdua .= " and kl_date = '".$_POST['kl_date']."'";
	}
	if($_POST['id_kiln']) {
		$whdua .= " and id_kiln = '".$_POST['id_kiln']."'";
	}
	if($_POST['kl_shift']) {
		$whdua .= " and kl_shift = '".$_POST['kl_shift']."'";
	}

	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_kiln_header where kl_status='N' and kl_date >= '{$tglfrom}' and kl_date <= '{$tglto}' $whsatu $whdua";
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
		$sql = "SELECT a.*, b.desc_kiln from qc_kiln_header a
				left join qc_kiln_mesin b ON a.kl_sub_plant = b.sub_plant and a.id_kiln = b.id_kiln
				where kl_status='N' and kl_date >= '{$tglfrom}' and kl_date <= '{$tglto}' $whsatu $whdua
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['kl_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['kl_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['kl_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;

			$datetime = explode(' ',$ro['kl_date']);
			$ro['date'] = $datetime[0];

			$datetime2  = explode(' ',$ro['kl_time']);
			$ro['time'] = substr($datetime2[1],0,5);
			
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['kl_id'],$ro['kl_sub_plant'],$ro['date'],$ro['desc_kiln'],$ro['time'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;
	$r[kl_date1] = $r[kl_date];
	$r[kl_date] = cgx_dmy2ymd($r[kl_date1])." 00:00:00";
	$r[kl_time] = cgx_dmy2ymd($r[kl_date1])." ".$r[kl_time].":00";
	if($stat == "add") {
		$r[kl_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[kl_date_create] = date("Y-m-d H:i:s");
		$sql = "SELECT max(kl_id) as kl_id_max from qc_kiln_header where kl_sub_plant = '{$r[kl_sub_plant]}'";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[kl_id_max] == ''){
			$mx[kl_id_max] = 0;
		} else {
			$mx[kl_id_max] = substr($mx[kl_id_max],-7);
		}
		$urutbaru = $mx[kl_id_max]+1;
		$r[kl_id] = $app_plan_id.$r[kl_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);

		//cek duplikat
		$qdup = "SELECT count(*) as count from qc_kiln_header where kl_sub_plant='{$r[kl_sub_plant]}' and id_kiln = '{$r[id_kiln]}' and kl_date = '{$r[kl_date]}' 
				 and kl_time = '{$r[kl_time]}'";
		$rdup = dbselect_plan($app_plan_id, $qdup);

		if($rdup[count] > 0){
			$out = "Terjadi duplikat data.";
		}else{
			$sql = "INSERT into qc_kiln_header(kl_sub_plant, kl_id, id_kiln, kl_date, kl_time, kl_speed, kl_code, kl_presure, kl_user_create, kl_date_create, kl_status) 
					values('{$r[kl_sub_plant]}', '{$r[kl_id]}', '{$r[id_kiln]}', '{$r[kl_date]}', '{$r[kl_time]}', '{$r[kl_speed]}', '{$r[kl_code]}', '{$r[kl_presure]}', '{$r[kl_user_create]}', '{$r[kl_date_create]}', 'N');";
			$xsql = dbsave_plan($app_plan_id, $sql); 
			if($xsql == "OK") {
				$k2sql = "";
				foreach ($r[kl_group] as $i => $value) {
				$k2sql .= "INSERT into qc_kiln_detail(kl_id, kl_group, kld_id, kl_d_value) values('{$r[kl_id]}', '{$r[kl_group][$i]}', '{$r[kld_id][$i]}', '{$r[kl_d_value][$i]}');";
				}
				$out = dbsave_plan($app_plan_id, $k2sql);
			} else {
				$out = $xsql;
			}
		}
	} else if($stat=='edit') {
		$r[kl_user_modify] = $_SESSION[$app_id]['user']['user_name'];
		$r[kl_date_modify] = date("Y-m-d H:i:s");
		$sql = "UPDATE qc_kiln_header set kl_user_modify = '{$r[kl_user_modify]}', kl_date_modify = '{$r[kl_date_modify]}', kl_speed = '{$r[kl_speed]}', kl_code = '{$r[kl_code]}', kl_presure = '{$r[kl_presure]}' where kl_id = '{$r[kl_id]}';";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		if($xsql == "OK") {
			$k1sql = "DELETE from qc_kiln_detail where kl_id = '{$r[kl_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			if($x1sql == "OK") {
				$k2sql = "";
				foreach ($r[kl_group] as $i => $value) {
					$k2sql .= "INSERT into qc_kiln_detail(kl_id, kl_group, kld_id, kl_d_value) values('{$r[kl_id]}', '{$r[kl_group][$i]}', '{$r[kld_id][$i]}', '{$r[kl_d_value][$i]}');";
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
	$kl_id = $_POST['kode'];
	$sql = "UPDATE qc_kiln_header set kl_status='C' where kl_id = '{$kl_id}';";
	echo dbsave_plan($app_plan_id, $sql); 
}

function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant();
	$out .= $withselect ? "</select>" : "";
	echo $out;
}

function detailtabel($stat,$subplan) {
	global $app_plan_id;
	if($stat == "edit" || $stat == "view") {
		$kl_id = $_POST['kode'];
		$sql0 = "SELECT * from qc_kiln_header where kl_id = '{$kl_id}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$datetime = explode(' ',$rhead['kl_date']);
		$rhead['date'] = cgx_dmy2ymd($datetime[0]);

		$datetime2 = explode(' ',$rhead['kl_time']);
		$rhead['time'] = substr($datetime2[1],0,5);

		$sqlgrup = "SELECT distinct a.kl_group, b.kl_desc from qc_kiln_detail a join qc_kiln_group b on a.kl_group = b.kl_group where a.kl_id = '{$kl_id}' order by a.kl_group ASC";
	} else {
		$sqlgrup = "SELECT kl_group, kl_desc from qc_kiln_group order by kl_group";
	}

	$k = 0;
	$i = 0;



	$qrygrup = dbselect_plan_all($app_plan_id, $sqlgrup);
	$nogrup = 1;
	$i = 1;


	$out = '<table class="table">';
	foreach($qrygrup as $rgrup) {
		$out .= '<tr>
					<td>
						<button type="button" class="btn btn-default btn-sm btn-block" style="text-align:left;font-weight:bold;" onClick="hideGrup(\''.$nogrup.'\')">'.ROmawi($nogrup).'. INPUT '.$rgrup[kl_desc].'</button>
					</td>
				</tr>
			<tr id="trgrup_ke_'.$nogrup.'"><td>';



			$out .= '<table class="table table-bordered table-condensed table-hover">
					 <tr>
		        	   <th width="100px">NO. MODUL</th>
		        	   <th>INPUT NILAI</th>
		        	 </tr>';


			if($stat == "edit" || $stat == "view") {
				$sqlitem = "SELECT a.kld_id, b.kld_desc 
							from qc.qc_kiln_detail a left join (SELECT * FROM qc.qc_kiln_group_detail WHERE sub_plant = '{$rhead[kl_sub_plant]}') as b 
							on a.kld_id = b.kld_id and a.kl_group = b.kl_group
							WHERE a.kl_id = '{$rhead[kl_id]}' and a.kl_group = '{$rgrup[kl_group]}'
							order by cast(a.kld_id as integer) ASC";

				$sqljmlitem = "SELECT COUNT(*) as jmlitem 
							from qc.qc_kiln_detail a left join (SELECT * FROM qc.qc_kiln_group_detail WHERE sub_plant = '{$rhead[kl_sub_plant]}') as b 
							on a.kld_id = b.kld_id and a.kl_group = b.kl_group
							WHERE a.kl_id = '{$rhead[kl_id]}' and a.kl_group = '{$rgrup[kl_group]}'";

			}else{
				$sqlitem = "SELECT * from qc_kiln_group_detail 
							WHERE sub_plant = '{$subplan}' and kl_group = '{$rgrup[kl_group]}' and kld_status = 'N' 
							order by cast(kld_id as integer) ASC";

				$sqljmlitem = "SELECT COUNT(*) as jmlitem from qc_kiln_group_detail 
							WHERE sub_plant = '{$subplan}' and kl_group = '{$rgrup[kl_group]}' and kld_status = 'N' ";
				
			}

			$jmlitem   = dbselect_plan($app_plan_id, $sqljmlitem);

			if($jmlitem[jmlitem] <= 0){
				$out .=	'<tr><td colspan="2" class="text-center">Parameter belum ada...</td></tr>';
			}else{

				$qryitem   = dbselect_plan_all($app_plan_id, $sqlitem);
			
				$noitem = 1;
				foreach($qryitem as $ritem) {
					$out .=	'<tr>';
					$out .=	'<td><input class="form-control input-sm text-center" type="text" name="ritem['.$i.']" id="ritem_'.$i.'" value="'.$ritem[kld_desc].'" readonly></td>';

					$out .= '<td>
		        				<input type="hidden" name="kl_group['.$i.']" id="kl_group_'.$i.'" value="'.$rgrup[kl_group].'">
		        				<input type="hidden" name="kld_id['.$i.']" id="kld_id_'.$i.'" value="'.$ritem[kld_id].'">';
			        	
			        	if($stat == "edit" || $stat == "view") {

			        		if($stat == "view"){
			        			$readonly = "readonly";
			        		}else{
			        			$readonly = "";
			        		}

			        		$sql4 = "SELECT kl_d_value from qc_kiln_detail where kl_id = '{$rhead[kl_id]}' and kl_group = '{$rgrup[kl_group]}' and kld_id = '{$ritem[kld_id]}'";
							$r4 = dbselect_plan($app_plan_id, $sql4); 

			        		$out .= '<input class="form-control input-sm text-left" '.$readonly.' type="text" name="kl_d_value['.$i.']" id="kl_d_value_'.$i.'" onkeyup="hanyanumerik(this.id,this.value);" value="'.$r4[kl_d_value].'"></td>';
			        	} else {
			        		$out .= '<input class="form-control input-sm text-left" type="text" name="kl_d_value['.$i.']" id="kl_d_value_'.$i.'" value="" onkeyup="hanyanumerik(this.id,this.value);"></td>';
			        	}
					

					$out .=	'</tr>';

					$i++;
					$noitem++;
				}
			}
		$out .= '</table>';
		$out .= '</td></tr>';
	$nogrup++;
	}

	$out .= '</table>';
	$out .= '<table class="table">';

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
    	$responce->kl_id 		= $rhead[kl_id];
	    $responce->kl_date 		= $rhead[date];
	    $responce->id_kiln 	= cboklin($rhead[kl_sub_plant], $rhead[id_kiln], true);
	    $responce->kl_sub_plant = $rhead[kl_sub_plant];
	    $responce->kl_code 		= $rhead[kl_code];
	    $responce->kl_presure 	= $rhead[kl_presure];
	    $responce->kl_speed 	= $rhead[kl_speed];
	    $responce->kl_time      = '<option value="'.$rhead[time].'">'.$rhead[time].'</option>';
    }
	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>