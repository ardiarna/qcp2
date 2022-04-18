<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['108'];
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
	case "importdata":
		importdata();
		break;
	case "detailtabel":
		detailtabel($_POST['kode']);
		break;
}


function urai(){
	global $app_plan_id, $akses, $app_subplan;
	$page = $_POST['page']; 
	$rows = $_POST['rows']; 
	$sidx = $_POST['sidx']; 
	$sord = $_POST['sord'];
	$whsatu = dptKondisiWhere($_POST['_search'],$_POST['filters'],$_POST['searchField'],$_POST['searchOper'],$_POST['searchString']);
	$whdua = "";

	if($_POST['subkon_id']) {
		$whdua .= " and subkon_id ilike '%".$_POST['subkon_id']."%' ";
	}
	if($_POST['subkon_name']) {
		$whdua .= " and subkon_name ilike '%".$_POST['subkon_name']."%' ";
	}

	if($_POST['subkon_desc']) {
		$whdua .= " and subkon_desc ilike '%".$_POST['subkon_desc']."%' ";
	}

	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_md_subkon where subkon_status='N' $whsatu $whdua";
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
		$sql = "SELECT * FROM qc_md_subkon WHERE subkon_status='N' $whsatu $whdua order by $sidx $sord $limit";
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

			$btnView = '<button class="btn btn-default btn-xs" title="Detail" onClick="lihatData(\''.$ro['subkon_id'].'\')"><span class="glyphicon glyphicon-search"></span></button> ';
			
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" title="Ubah" onClick="editData(\''.$ro['subkon_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" title="Hapus" onClick="hapusData(\''.$ro['subkon_id'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['subkon_id'],$ro['subkon_name'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_id, $app_plan_id;
	$r = $_REQUEST;

	$sWid = "";
	if($stat == 'edit'){
		$sWid = "AND subkon_id <> '{$r[subkon_id]}'";
	}
	
	$sql_dup = "SELECT subkon_id from qc_md_subkon where subkon_name = '{$r[subkon_name]}' AND subkon_status='N' $sWid";
	$d_dup   = dbselect_plan($app_plan_id, $sql_dup);


	if($d_dup['subkon_id'] == "") {

		if($stat == "add") {
			$r[subkon_user_create] = $_SESSION[$app_id]['user']['user_name'];
			$r[subkon_date_create] = date("Y-m-d H:i:s");

			$sql = "SELECT max(subkon_id) as ic_id_max FROM qc_md_subkon";
			$mx = dbselect_plan($app_plan_id, $sql);
			if($mx[ic_id_max] == ''){
				$mx[ic_id_max] = 0;
			} else {
				$mx[ic_id_max] = substr($mx[ic_id_max],-3);
			}
			$urutbaru = $mx[ic_id_max]+1;
			$r[subkon_id] = "S-".str_pad($urutbaru,3,"0",STR_PAD_LEFT);
			
			
			$sql = "INSERT into qc_md_subkon( subkon_id, subkon_name, subkon_desc, subkon_status, 
						subkon_user_create, subkon_date_create) 
					VALUES ('{$r[subkon_id]}', '{$r[subkon_name]}', '{$r[subkon_desc]}', 'N', 
						'{$r[subkon_user_create]}', '{$r[subkon_date_create]}');";
			$xsql = dbsave_plan($app_plan_id, $sql); 
			$out = $xsql;
			
		} else if($stat=='edit') {
			$r[subkon_user_modify] = $_SESSION[$app_id]['user']['user_name'];
			$r[subkon_date_modify] = date("Y-m-d H:i:s");
			$sql = "UPDATE qc_md_subkon SET subkon_name = '{$r[subkon_name]}', subkon_desc = '{$r[subkon_desc]}', 
							subkon_user_modify = '{$r[subkon_user_modify]}', subkon_date_modify = '{$r[subkon_date_modify]}' 
					WHERE subkon_id = '{$r[subkon_id]}';";
			$xsql = dbsave_plan($app_plan_id, $sql); 
			$out = $xsql;
		}
	}else{
		$out = 'Duplikat Data, Nama Sub Kontraktor sudah ada!';
	}
	echo $out;
}

function hapus(){
	global $app_plan_id;
	$kode = $_POST['kode'];
	$sql = "UPDATE qc_md_subkon set subkon_status='C' where subkon_id = '{$kode}';";
	echo dbsave_plan($app_plan_id, $sql); 
}


function detailtabel($kode) {
	global $app_plan_id, $app_subplan;
	$stat = $_REQUEST[stat];

	if($stat == 'edit' || $stat == 'view'){
		$sql  = "SELECT * FROM qc_md_subkon WHERE subkon_id = '{$kode}'";
		$r    = dbselect_plan($app_plan_id, $sql);

		$responce->subkon_id   		= $r[subkon_id];
		$responce->subkon_name   	= $r[subkon_name];
		$responce->subkon_desc   	= $r[subkon_desc];
	}

	if($stat == 'view'){
		$out   = '<div class="col-sm-12" style="margin-top:3px;text-align:center;">
	                <button type="button" class="btn btn-warning btn-sm" onclick="formAwal()">Batal</button>
	              </div>';
	}else{
		$out   = '<div class="col-sm-12" style="margin-top:3px;text-align:center;">
	                <button type="button" class="btn btn-primary btn-sm" onclick="simpanData()" id="btnSimpan">Simpan</button> 
	                <button type="button" class="btn btn-warning btn-sm" onclick="formAwal()">Batal</button>
	              </div>';

	}

	$responce->detailtabel = $out; 
	echo json_encode($responce);
}


function importdata(){
	global $app_plan_id;

	$whnotnin = "AND supplier_kode NOT IN ('2. PUTRA TAMBAK UTAMA, CV','2.SIP (PENGGANTI SKM)')";

	$sql = "SELECT distinct supplier_kode, company, address from supplier 
			WHERE supplier_kode like '$app_plan_id.%' $whnotnin ";
	$qry = dbselect_all($sql);
	if($qry) {
		$out = '<table class="table table-striped table-bordered table-condensed">';
	
		$arr_data = array();
		foreach($qry as $r) {
			
			$sqlcek = "SELECT * FROM qc_md_subkon where subkon_id = '".$r['supplier_kode']."'";
			$rcek = dbselect_plan($app_plan_id, $sqlcek);

			if($rcek[subkon_id]) {
				$arr_data['ADA']["$rcek[subkon_id]"] = $rcek[subkon_name]; 
			} else {

				$r[address] = addcslashes($r[address]);
				$r[company] = addcslashes($r[company]);

				$sql3 = "INSERT INTO qc_md_subkon (subkon_id, subkon_name, subkon_desc, subkon_status) 
						 VALUES ('{$r[supplier_kode]}', '{$r[company]}', '{$r[address]}', 'N');";
				$xsql = dbsave_plan($app_plan_id, $sql3);
				
				if($xsql == "OK") {
					$arr_data['SUKSES']["$r[supplier_kode]"] = $r[company]; 
				} else {
					$arr_data['GAGAL']["$r[supplier_kode]"] = $r[company]; 
				}
			}
		}


		$arr_jenis = array('GAGAL' => 'GAGAL IMPOR', 'SUKSES' => 'SUKSES IMPOR', 'ADA' => 'SUDAH ADA');

		foreach ($arr_jenis as $jid => $jenisval) {
			
			if(is_array($arr_data[$jid])){

				$out .= '<thead>';
				$out .= '<tr>';
				$out .= '<th colspan="3" style="background-color:#59b2e5;;">SUPPLIER '.$jenisval.'</th>';
				$out .= '</tr>';

				$out .= '<tr>';
				$out .= '   <th>NO</th>
							<th>KODE</th>
							<th>NAMA</th>';
				$out .= '</tr>';
				$out .= '</thead>';

				$out .= '<tbody>';

				$no = 0;
				foreach ($arr_data[$jid] as $idsub => $nmsub) {
					$out .= '<tr>
								<td class="text-center">'.++$no.'</td>
								<td class="text-center">'.$idsub.'</td>
								<td>'.$nmsub.'</td>
							 </tr>';
				}

				$out .= '<tr>';
				$out .= '<th colspan="3">&nbsp;</th>';
				$out .= '</tr>';
			}
			
		}





		$out .= '</tbody></table>';
	} else {
		$out = "Terdapat kesalahan sistem, silahkan hubungi administrator.";
	}

	$responce->hasil = $out;
	
	echo json_encode($responce);
}
?>