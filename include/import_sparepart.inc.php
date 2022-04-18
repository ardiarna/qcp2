<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['106'];
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
	case "prosesimport":
		prosesimport();
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
	
	if($_POST['kdasset']) {
		$whdua .= " and kdasset = '".$_POST['kdasset']."'";
	}
	if($_POST['item_kode']) {
		$whdua .= " and item_kode = '".$_POST['item_kode']."'";
	}
	
	if($_POST['item_nama']) {
		$whdua .= " and item_nama = '".$_POST['item_nama']."'";
	}
	if($_POST['item_satuan']) {
		$whdua .= " and item_satuan = '".$_POST['item_satuan']."'";
	}

	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) AS count FROM qc_master_sparepart WHERE 1=1 $whsatu $whdua";
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
		$sql = "SELECT * FROM qc_master_sparepart WHERE 1=1 $whsatu $whdua ORDER BY $sidx $sord $limit";
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
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['kdasset'],$ro['item_kode'],$ro['item_nama'],$ro['item_satuan']);
			$i++;
		}
	}
	echo json_encode($responce);
}



function prosesimport(){
	global $app_id, $app_plan_id;

	$r = $_REQUEST;
	$kdasset = $r[kdasset];
	$sheet  = $r[sheet];
	$awal   = $r[awal];
	$akhir  = $r[akhir];

	$nama 	  = $_FILES['file']['name'];
	$ukuran	  = $_FILES['file']['size'];
	$file_tmp = $_FILES['file']['tmp_name'];	

	move_uploaded_file($file_tmp, '../excelfile/'.$nama);
	// if(){
	// 	echo 'Data diupload';
	// }else{
	// 	echo 'Gagal diupload';
	// }


	// $r[fgf_date] = cgx_dmy2ymd($r[fgf_date])." ".$r[fgf_time].":00";
	// $r[fgf_kiln] = cgx_angka($r[fgf_kiln]);

	// $r[fgf_quality] = cgx_angka($r[fgf_quality]);
	// if($r[fgf_quality] > 100){
	// 	$r[fgf_quality] = 100;
	// }else{
	// 	$r[fgf_quality] = $r[fgf_quality];
	// }

	// if($stat == "add") {
	// 	$r[fgf_user_create] = $_SESSION[$app_id]['user']['user_name'];
	// 	$r[fgf_date_create] = date("Y-m-d H:i:s");
	// 	$sql = "SELECT max(fgf_id) as fgf_id_max from qc_fg_fault_header where fgf_sub_plant = '{$r[fgf_sub_plant]}'";
	// 	$mx = dbselect_plan($app_plan_id, $sql);
	// 	if($mx[fgf_id_max] == ''){
	// 		$mx[fgf_id_max] = 0;
	// 	} else {
	// 		$mx[fgf_id_max] = substr($mx[fgf_id_max],-7);
	// 	}
	// 	$urutbaru = $mx[fgf_id_max]+1;
	// 	$r[fgf_id] = $app_plan_id.$r[fgf_sub_plant]."/".str_pad($urutbaru,7,"0",STR_PAD_LEFT);
		
	// 	//cek jika duplikat
	// 	$qdup = "SELECT COUNT(*) AS jmldup from qc_fg_fault_header 
	// 			 WHERE fgf_status = 'N' 
	// 			 AND fgf_sub_plant = '{$r[fgf_sub_plant]}' 
	// 			 AND fgf_date = '{$r[fgf_date]}'
	// 			 AND fgf_kiln = '{$r[fgf_kiln]}' ";
	// 	$ddup = dbselect_plan($app_plan_id, $qdup);

	// 	if($ddup[jmldup] > 0){
	// 		$out = "Terjadi Duplikat Data";
	// 	}else{

	// 		$sql = "INSERT into qc_fg_fault_header(fgf_sub_plant, fgf_id, fgf_date, fgf_kiln, fgf_quality, fgf_type, fgf_user_create, fgf_date_create, fgf_status) values('{$r[fgf_sub_plant]}','{$r[fgf_id]}',  '{$r[fgf_date]}', '{$r[fgf_kiln]}', '{$r[fgf_quality]}','{$r[fgf_type]}','{$r[fgf_user_create]}', '{$r[fgf_date_create]}', 'N');";
	// 		$xsql = dbsave_plan($app_plan_id, $sql); 
	// 		$out = $xsql;
	// 		if($xsql == "OK") {
	// 			$k2sql = "";
	// 			foreach ($r[fapr_id] as $i => $value) {
	// 				if($r[eco_value][$i] == ''){
	// 					$r[eco_value][$i] = 0;
	// 				}else{
	// 					$r[eco_value][$i] = $r[eco_value][$i];
	// 				}

	// 				if($r[rj_value][$i] == ''){
	// 					$r[rj_value][$i] = 0;
	// 				}else{
	// 					$r[rj_value][$i] = $r[rj_value][$i];
	// 				}


	// 				$k2sql .= "INSERT into qc_fg_fault_detail(fgf_id, fapr_id, eco_value, rj_value) 
	// 							values('{$r[fgf_id]}', '{$r[fapr_id][$i]}', '{$r[eco_value][$i]}', '{$r[rj_value][$i]}');";
	// 			}
	// 			$out = dbsave_plan($app_plan_id, $k2sql);
	// 		} else {
	// 			$out = $xsql;
	// 		}
	// 	}
	// } else if($stat=='edit') {
	// 	$r[fgf_user_modify] = $_SESSION[$app_id]['user']['user_name'];
	// 	$r[fgf_date_modify] = date("Y-m-d H:i:s");

	// 	$sql = "UPDATE qc_fg_fault_header set fgf_quality = '{$r[fgf_quality]}', fgf_type = '{$r[fgf_type]}', fgf_user_modify = '{$r[fgf_user_modify]}', fgf_date_modify = '{$r[fgf_date_modify]}' 
	// 			where fgf_id = '{$r[fgf_id]}';";
	// 	$xsql = dbsave_plan($app_plan_id, $sql); 
	// 	if($xsql == "OK") {
	// 		$k1sql = "DELETE from qc_fg_fault_detail where fgf_id = '{$r[fgf_id]}';";
	// 		$x1sql = dbsave_plan($app_plan_id, $k1sql);

	// 		$k2sql = "";
	// 		foreach ($r[fapr_id] as $i => $value) {
	// 			if($r[eco_value][$i] == ''){
	// 				$r[eco_value][$i] = 0;
	// 			}else{
	// 				$r[eco_value][$i] = $r[eco_value][$i];
	// 			}

	// 			if($r[rj_value][$i] == ''){
	// 				$r[rj_value][$i] = 0;
	// 			}else{
	// 				$r[rj_value][$i] = $r[rj_value][$i];
	// 			}


	// 			$k2sql .= "INSERT into qc_fg_fault_detail(fgf_id, fapr_id, eco_value, rj_value) 
	// 						values('{$r[fgf_id]}', '{$r[fapr_id][$i]}', '{$r[eco_value][$i]}', '{$r[rj_value][$i]}');";
	// 		}
	// 		$out = dbsave_plan($app_plan_id, $k2sql);
	// 	} else {
	// 		$out = $xsql;
	// 	}
	// }
	// echo $out;
}

?>