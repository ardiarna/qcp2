<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['114'];
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
	case "hapus":
		hapus();
		break;
	case "pilihuser":
		pilihuser();
		break;
}


function pilihuser() {
	global $app_plan_id;
	if($_POST['txt_cari']) {
		$txt_cari = strtoupper($_POST['txt_cari']);
		$whsatu = "AND (user_name ilike '%{$txt_cari}%' OR 
						first_name ilike '%{$txt_cari}%' OR 
						last_name ilike '%{$txt_cari}%'
				       )";
	}
	$sql = "SELECT * FROM app_user WHERE 1=1 $whsatu ORDER BY user_name ASC";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out = '<table class="table table-bordered table-striped table-condensed">
				<tbody>
					<tr>
						<th>#</th>
						<th>USERNAME</th>
						<th>FISRTNAME</th>
						<th>LASTNAME</th>
					</tr>';
	if(is_array($qry)) {
		foreach($qry as $r) {
			$out .= '<tr>
						<td class="text-center">
							<span class="glyphicon glyphicon-ok" onClick="setUser(\''.$r[user_name].'\');"></span>
						</td>
						<td>'.$r[user_name].'</td>
						<td>'.$r[first_name].'</td>
						<td>'.$r[last_name].'</td>
					</tr>';
		}
	} else {
		$out .= '<tr><td colspan="4">Pencarian : '.$txt_cari.' tidak ditemukan...</td></tr>';
	}	
	$out .= '</tbody></table>';

	$responce->out = $out;
	echo json_encode($responce);
}


function urai(){
	global $app_plan_id, $akses, $app_subplan;
	$page = $_POST['page']; 
	$rows = $_POST['rows']; 
	$sidx = $_POST['sidx']; 
	$sord = $_POST['sord'];
	$whsatu = dptKondisiWhere($_POST['_search'],$_POST['filters'],$_POST['searchField'],$_POST['searchOper'],$_POST['searchString']);
	$whdua = "";

	if($_POST['user_name']) {
		$whdua .= " and b.user_name ilike '%".$_POST['user_name']."%' ";
	}

	if($_POST['appr_jab']) {
		$whdua .= " and appr_jab = '".$_POST['appr_jab']."' ";
	}

	if($_POST['first_name']) {
		$whdua .= " and b.first_name ilike '%".$_POST['first_name']."%' ";
	}

	if($_POST['last_name']) {
		$whdua .= " and b.last_name ilike '%".$_POST['last_name']."%' ";
	}


	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count 
			FROM qc_ic_in_appr a 
			LEFT JOIN app_user b ON a.appr_uname = b.user_name 
			WHERE 1=1 $whsatu $whdua";
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
		$sql = "SELECT a.*, b.user_name, b.first_name, b.last_name 
				FROM qc_ic_in_appr a 
				LEFT JOIN app_user b ON a.appr_uname = b.user_name 
				WHERE 1=1 $whsatu $whdua order by $sidx $sord $limit";
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

			$ro['appr_jab'] = $ro['appr_jab'] == '1' ? 'PM' : 'KABAG';
			
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" title="Hapus" onClick="hapusData(\''.$ro['appr_uname'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';

			$ro['kontrol'] = $btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['appr_jab'],$ro['user_name'],$ro['first_name'],$ro['last_name'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;

	$sql_dup = "SELECT appr_uname FROM qc_ic_in_appr where appr_uname = '{$r[appr_uname]}'";
	$d_dup   = dbselect_plan($app_plan_id, $sql_dup);


	if($d_dup['appr_uname'] == "") {

		$r[appr_user_create] = $_SESSION[$app_id]['user']['user_name'];
		$r[appr_date_create] = date("Y-m-d H:i:s");

		$sql = "SELECT max(subkon_id) as ic_id_max FROM qc_md_subkon";
		$mx = dbselect_plan($app_plan_id, $sql);
		if($mx[ic_id_max] == ''){
			$mx[ic_id_max] = 0;
		} else {
			$mx[ic_id_max] = substr($mx[ic_id_max],-3);
		}
		$urutbaru = $mx[ic_id_max]+1;
		$r[subkon_id] = "S-".str_pad($urutbaru,3,"0",STR_PAD_LEFT);
		
		
		$sql = "INSERT INTO qc_ic_in_appr( appr_uname, appr_jab, appr_user_create, appr_date_create) 
				VALUES ('{$r[appr_uname]}', '{$r[appr_jab]}', '{$r[appr_user_create]}', '{$r[appr_date_create]}');";
		$xsql = dbsave_plan($app_plan_id, $sql); 
		$out = $xsql;
	}else{
		$out = 'Duplikat Data, '.$r[appr_uname].' sudah ada!';
	}
	echo $out;
}

function hapus(){
	global $app_plan_id;
	$kode = $_POST['kode'];
	$sql = "DELETE FROM qc_ic_in_appr WHERE appr_uname = '{$kode}';";
	echo dbsave_plan($app_plan_id, $sql); 
}

?>