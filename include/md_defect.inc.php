<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['53'];
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
	case "detailtabel":
		detailtabel($_POST['stat']);
		break;
}

function urai(){
	global $app_plan_id, $akses, $app_subplan;
	$page = $_POST['page']; // get the requested page 
	$rows = $_POST['rows']; // get how many rows we want to have into the grid 
	$sidx = $_POST['sidx']; // get index row - i.e. user click to sort 
	$sord = $_POST['sord']; // get the direction
	$whsatu = dptKondisiWhere($_POST['_search'],$_POST['filters'],$_POST['searchField'],$_POST['searchOper'],$_POST['searchString']);
	$whdua = "";
	if($_POST['qmd_kode']) {
		$whdua .= " and qmd_kode = '".$_POST['qmd_kode']."'";
	}
	if($_POST['qmd_nama']) {
		$whdua .= " and lower(qmd_nama) like lower('%".$_POST['qmd_nama']."%')";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_md_defect where 1=1 $whsatu $whdua";
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
		$sql = "SELECT * from qc_md_defect where 1=1 $whsatu $whdua order by $sidx $sord $limit";
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
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qmd_kode'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qmd_kode'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qmd_kode'],$ro['qmd_nama'],$ro['kontrol']); 
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_plan_id;
	$pkey_qmd_kode = $_POST['pkey_qmd_kode'];
	$qmd_kode = $_POST['qmd_kode'];
	$qmd_nama = $_POST['qmd_nama'];
	if($stat=='add'){
		$sql = "INSERT INTO qc_md_defect(qmd_kode,qmd_nama) values('{$qmd_kode}','{$qmd_nama}')";
	}else if($stat=='edit'){
		$sql = "UPDATE qc_md_defect set qmd_kode='{$qmd_kode}', qmd_nama='{$qmd_nama}' where qmd_kode='{$pkey_qmd_kode}'";
	}
	echo dbsave_plan($app_plan_id, $sql);
}

function hapus(){
	global $app_plan_id;
	$pkey_qmd_kode = $_POST['qmd_kode'];
	$sql = "DELETE from qc_md_defect where qmd_kode='{$pkey_qmd_kode}'";
	echo dbsave_plan($app_plan_id, $sql);
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit") {
		$pkey_qmd_kode = $_POST['qmd_kode'];
		$sql0 = "SELECT * from qc_md_defect where qmd_kode='{$pkey_qmd_kode}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$responce->qmd_kode = $rhead[qmd_kode];
		$responce->qmd_nama = $rhead[qmd_nama];
	}
    echo json_encode($responce);
}

?>