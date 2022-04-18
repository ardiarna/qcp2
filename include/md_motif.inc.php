<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['54'];
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
	case "impor":
		impor();
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
	if($_POST['qmm_nama']) {
		$whdua .= " and lower(qmm_nama) like lower('%".$_POST['qmm_nama']."%')";
	}
	if($_POST['qmm_size']) {
		$whdua .= " and qmm_size = '".$_POST['qmm_size']."'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_md_motif where 1=1 $whsatu $whdua";
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
		$sql = "SELECT * from qc_md_motif where 1=1 $whsatu $whdua order by $sidx $sord $limit";
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
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['qmm_nama'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['qmm_nama'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['qmm_nama'],$ro['qmm_size'],$ro['kontrol']); 
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_plan_id;
	$pkey_qmm_nama = $_POST['pkey_qmm_nama'];
	$qmm_nama = $_POST['qmm_nama'];
	$qmm_size = $_POST['qmm_size'];
	if($stat=='add'){
		$sql = "INSERT INTO qc_md_motif(qmm_nama, qmm_size) values('{$qmm_nama}', '{$qmm_size}')";
	}else if($stat=='edit'){
		$sql = "UPDATE qc_md_motif set qmm_nama='{$qmm_nama}', qmm_size='{$qmm_size}' where qmm_nama='{$pkey_qmm_nama}'";
	}
	echo dbsave_plan($app_plan_id, $sql);
}

function hapus(){
	global $app_plan_id;
	$pkey_qmm_nama = $_POST['qmm_nama'];
	$sql = "DELETE from qc_md_motif where qmm_nama='{$pkey_qmm_nama}'";
	echo dbsave_plan($app_plan_id, $sql);
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit") {
		$pkey_qmm_nama = $_POST['qmm_nama'];
		$sql0 = "SELECT * from qc_md_motif where qmm_nama='{$pkey_qmm_nama}'";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$responce->qmm_nama = $rhead[qmm_nama];
		$responce->qmm_size = $rhead[qmm_size];
	}
    echo json_encode($responce);
}

function impor() {
	global $app_plan_id;
	$arr_size = array("05" => "20 X 20", "06" => "30 X 30", "07" => "40 X 40", "08" => "20 X 25", "09" => "25 X 40", "10" => "25 X 25", "11" => "50 X 50", "12" => "25 X 50");

	$sql = "SELECT distinct substring(category_kode from 1 for 2) as cat_kode, trim(spesification) as spesification from item where left(item_kode,2) in('05','06','07','08','09','10','11','12') and spesification is not null and spesification <> '' and spesification <> '-' order by trim(spesification)";
	$qry = dbselect_all($sql);
	if(is_array($qry)) {
		$out = '<table class="table table-striped table-bordered table-condensed"><thead><tr><th>NO</th><th>NAMA MOTIF</th><th>UKURAN</th><th>STATUS</th></tr></thead><tbody>';
		$no = 1;
		$ada = 0;
		$sukses = 0;
		$gagal = 0;
		foreach($qry as $r){
			$size = $arr_size[$r[cat_kode]];
			$out .= '<tr><td>'.$no.'</td><td>'.$r[spesification].'</td><td>'.$size.'</td>';			
			$sql0 = "SELECT * from qc_md_motif where qmm_nama='{$r[spesification]}'";
			$rhead = dbselect_plan($app_plan_id, $sql0);
			if($rhead[qmm_nama]){
				if(!$rhead[qmm_size]){
					$sql = "UPDATE qc_md_motif set qmm_size = '{$size}' where qmm_nama = '{$rhead[qmm_nama]}';";
					$xsql = dbsave_plan($app_plan_id, $sql);
					if($xsql == "OK") {
						$out .= '<td>Berhasil Update Ukuran</td>';
						$sukses++;
					} else {
						$out .= '<td>Gagal Update Ukuran</td>';
						$gagal++;
					}
				} else {
					$out .= '<td>Sudah Ada</td>';
				}
				$ada++;
			} else {
				$sql = "INSERT INTO qc_md_motif(qmm_nama, qmm_size) values('{$r[spesification]}','{$size}')";
				$xsql = dbsave_plan($app_plan_id, $sql);
				if($xsql == "OK") {
					$out .= '<td>Berhasil diimpor</td>';
					$sukses++;
				} else {
					$out .= '<td>Gagal diimpor</td>';
					$gagal++;
				}
			}
			$out .= '</tr>';
			$no++;
		}
		$out .= '</tbody></table><br>Sudah Ada : '.$ada.' , Berhasil : '.$sukses.' , Gagal : '.$gagal;		
	} else {
		$out = "Terdapat kesalahan sistem, silahkan hubungi administrator.";
	}
	$responce->hasil=$out;
    echo json_encode($responce);
}

?>