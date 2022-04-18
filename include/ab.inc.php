<?php

include_once("../libs/init.php");

if($_GET["mode"]) {
	$oper = $_GET["mode"];
} else {
	$oper = $_POST["oper"];
}

switch ($oper) {
	case "simpan":
		simpan();
		break;
}

function ExcelDateToDate($dateValue) {    
	$unixDate = ($dateValue - 25569) * 86400;
	return gmdate("Y-m-d", $unixDate);
}	

function simpan(){
	$plan = $_GET["plan"];
	$data = $_POST["data"];
	$kd_group = "AWAL";
	$sqlinsert = "";
	foreach($data as $r) {
		if($r[harga] > 0) {
			$sql = "SELECT item_kode from item where quality = 'EXPORT' and spesification = '{$r[spesification]}'";
			$rcek = dbselect($sql);
			if(!$rcek[item_kode]) {
				$sql = "SELECT item_kode from item where quality = 'EXPORT' and spesification like '%{$r[spesification]}%'";
				$rcek = dbselect($sql);
				if(!$rcek[item_kode]) {
					$spesification = str_replace(' ', '', $r[spesification]);
					$sql = "SELECT item_kode from item where quality = 'EXPORT' and replace(spesification,' ','') like '%{$spesification}%'";
					$rcek = dbselect($sql);
					if(!$rcek[item_kode]) {
						$rcek[item_kode] = $r[spesification];
					}
				}
			} 
			$sqlinsert .= "INSERT INTO item_harga_jual(item_kode,plant_kode,harga,tanggal) VALUES ('{$rcek[item_kode]}','{$plan}',{$r[harga]},'2018-04-01');";
		}
	}
	$hasil = dbsave($sqlinsert);
	// $hasil = "gagal";
	if($hasil <> "OK") {
		$responce->sql = $sqlinsert;
	}
	$responce->hasil = $hasil;
	echo json_encode($responce);
}

?>