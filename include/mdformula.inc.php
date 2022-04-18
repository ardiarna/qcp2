<?php

include_once("../libs/init.php");

if($_GET["mode"]) {
	$oper = $_GET["mode"];
} else {
	$oper = $_POST["oper"];
}

switch ($oper) {
	case "urai":
		urai();
		break;
	case "cbosubplant":
		cbosubplant();
		break;	
	case "sub2urai":
		sub2urai();
		break;
	case "sinkitem":
		sinkitem();
		break;
}

function urai(){
	global $app_plan_id;
	$page = $_POST['page']; 
	$rows = $_POST['rows']; 
	$sidx = $_POST['sidx']; 
	$sord = $_POST['sord'];
	$jenis = $_GET['jenis'];
	$plan_kode = $app_plan_id;
	$subplan_kode = $_GET['subplan'];
	$tanggal = explode('-', $_GET['tanggal']);
	$bulan = $tanggal[0];
	$tahun = $tanggal[1];
	$whsatu = dptKondisiWhere($_POST['_search'],$_POST['filters'],$_POST['searchField'],$_POST['searchOper'],$_POST['searchString']);
	$whdua = "";
	if($subplan_kode <> 'All') {
		$whdua .= " and sub_plan = '".$subplan_kode."'";
	}
	if($bulan <> 'All') {
		$whdua .= " and date_part('month',tanggal)='".$bulan."'";
	}
	if($tahun <> 'All') {
		$whdua .= " and date_part('year',tanggal)='".$tahun."'";
	}
	if($_POST['sub_plan']) {
		$whdua .= " and sub_plan = '".$_POST['sub_plan']."'";
	}
	if($_POST['komposisi_kode']) {
		$whdua .= " and lower(komposisi_kode) like '%".strtolower($_POST['komposisi_kode'])."%'";
	}
	if($_POST['tanggal']) {
		$whdua .= " and tanggal = '".$_POST['tanggal']."'";
	}
	if($_POST['keterangan']) {
		$whdua .= " and lower(keterangan) like '%".strtolower($_POST['keterangan'])."%'";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from tbl_komposisi_produksi where jenis='$jenis' and plan_kode='$plan_kode' $whsatu $whdua";
	$r = dbselect($sql);
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
		$sql = "SELECT * from tbl_komposisi_produksi 
			where jenis='$jenis' and plan_kode='$plan_kode' $whsatu $whdua
			order by $sidx $sord $limit";
		$qry = dbselect_all($sql);
		$i = 0;
	} else { 
		$total_pages = 1; 
	}
	if($page > $total_pages) $page = $total_pages; 
	$responce->sql = $sql; 
	$responce->page = $page; 
	$responce->total = $total_pages; 
	$responce->records = $count; 
	if($count > 0) {
		foreach($qry as $ro){
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['id_komposisi'],$ro['sub_plan'],$ro['komposisi_kode'],$ro['tanggal'],$ro['volume'],$ro['keterangan']);
			$i++;
		}
	}
	echo json_encode($responce);
}

function cbosubplant(){
	$out = "<select><option value=''></option>";
	$out .= "<option value='A'>A</option>
		<option value='B'>B</option>
		<option value='C'>C</option>";	
	$out .= "</select>";	
	echo $out;
}

function sub2urai(){
	global $app_plan_id;
	$plan_kode = $app_plan_id;
	$jenis = $_POST['jenis'];
	$komposisi_kode = $_POST['komposisi_kode'];
	$id_komposisi = $_POST['id_komposisi'];
	$volume = $_POST['volume'];
	$grand = dbselect("SELECT sum(formula) as formula from qry_detail_komposisi where komposisi_kode='{$komposisi_kode}' and plan_kode='{$plan_kode}' and kelompok not in ('ADDITIVE')");
	$sql = "SELECT * from qry_detail_komposisi where komposisi_kode='{$komposisi_kode}' and plan_kode='{$plan_kode}' and id_komposisi = '{$id_komposisi}' order by kelompok desc, item_kode asc";
	$qry = dbselect_all($sql);
	$i = 0;
	$responce->sql=$sql;
	foreach($qry as $ro){
		$ro['dw'] = $volume/$grand['formula']*$ro['formula'];
		$ro['ww'] = $ro['dw']/((100-$ro['mc'])/100);
		$responce->rows[$i]['cell']=array($ro['kelompok'],$ro['item_kode'],$ro['item_nama'],$ro['company'],$ro['formula'],$ro['dw'],$ro['mc'],$ro['ww']);
		$i++;
	}		
	echo json_encode($responce);	
}

function sinkitem(){
	global $app_plan_id;
	$sql = "SELECT DISTINCT a.item_kode
		FROM 
		(
			SELECT distinct item_kode 
			from qry_detail_komposisi 
			where plan_kode = '{$app_plan_id}' 
			UNION
			SELECT item_kode
			from item
			where substring(item_kode from 1 for 2) in ('00','01')
		) AS a
		ORDER BY a.item_kode";
	$qry = dbselect_all($sql);
	if($qry) {
		$out = '<table class="table table-striped table-bordered table-condensed"><thead><tr><th>NO</th><th>KODE MATERIAL</th><th>NAMA MATERIAL</th><th>STATUS</th></tr></thead><tbody>';
		$no = 1;
		$ada = 0;
		$sukses = 0;
		$gagal = 0;
		foreach($qry as $r) {
			$out .= '<tr><td>'.$no.'</td><td>'.$r[item_kode].'</td>';
			$sqlcek = "SELECT item_kode, item_nama from item where item_kode = '".$r['item_kode']."'";
			$rcek = dbselect_plan($app_plan_id, $sqlcek);
			if($rcek[item_kode]) {
				$out .= '<td>'.$rcek[item_nama].'</td><td>Sudah Ada</td>';
				$ada++;
			} else {
				$sql2 = "SELECT * from item where item_kode = '".$r['item_kode']."'";
				$r2 = dbselect($sql2);
				$r2[plant_kode] = $r2[plant_kode] ? $r2[plant_kode] : 'NULL';
				$r2[nilai_konversi] = $r2[nilai_konversi] ? $r2[nilai_konversi] : 'NULL';
				$sql3 = "INSERT into item(item_kode, category_kode, item_nama, spesification, gl_account, satuan, color, quality, inactive, modiby, modidate, ipc, gambar, kode_lama, group_nama, item_nama_baru, plant_kode, sub_plant, item_nama_lama, konversi, nilai_konversi, jenis_barang) values('{$r2[item_kode]}', '{$r2[category_kode]}', '{$r2[item_nama]}', '{$r2[spesification]}', '{$r2[gl_account]}', '{$r2[satuan]}', '{$r2[color]}', '{$r2[quality]}', '{$r2[inactive]}', '{$r2[modiby]}', '{$r2[modidate]}', '{$r2[ipc]}', '{$r2[gambar]}', '{$r2[kode_lama]}', '{$r2[group_nama]}', '{$r2[item_nama_baru]}', {$r2[plant_kode]}, '{$r2[sub_plant]}', '{$r2[item_nama_lama]}', '{$r2[konversi]}', {$r2[nilai_konversi]}, '{$r2[jenis_barang]}');";
				$xsql = dbsave_plan($app_plan_id, $sql3);
				if($xsql == "OK") {
					$out .= '<td>'.$r2[item_nama].'</td><td>Berhasil diimpor</td>';
					$sukses++;
				} else {
					$sql4 = "SELECT * from category where category_kode = '".$r2['category_kode']."'";
					$r4 = dbselect($sql4);
					$r4[jumlah_m2] = $r4[jumlah_m2] ? $r4[jumlah_m2] : 'NULL';
					$sql5 = "INSERT INTO category(category_kode, jenis_kode, category_nama, inactive, modiby, modidate, gl_account, kelompok_mesin, kelompok_barang, jumlah_m2) VALUES ('{$r4[category_kode]}', '{$r4[jenis_kode]}', '{$r4[category_nama]}', '{$r4[inactive]}', '{$r4[modiby]}', '{$r4[modidate]}', '{$r4[gl_account]}', '{$r4[kelompok_mesin]}', '{$r4[kelompok_barang]}', {$r4[jumlah_m2]});";
					$xsql2 = dbsave_plan($app_plan_id, $sql5);
					if($xsql2 == "OK") {
						$xsql3 = dbsave_plan($app_plan_id, $sql3);
						if($xsql3 == "OK") {
							$out .= '<td>'.$r2[item_nama].'</td><td>Berhasil diimpor</td>';
							$sukses++;
						} else {
							$out .= '<td>'.$r2[item_nama].'</td><td>Gagal diimpor</td>';
							$gagal++;
						}
					} else {
						$out .= '<td>'.$r2[item_nama].'</td><td>Gagal diimpor</td>';
						$gagal++;
					}
				}
			}
			$out .= '</tr>';
			$no++;
		}
		$out .= '</tbody></table><br>Sudah Ada : '.$ada.' , Berhasil : '.$sukses.' , Gagal : '.$gagal;
	} else {
		$out = "Terdapat kesalahan sistem, silahkan hubungi administrator.";
	}

	$responce->hasil = $out;
	echo json_encode($responce);
}

?>