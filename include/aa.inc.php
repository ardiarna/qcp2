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
	$arrga = array();
	$sql = "SELECT upper(ca.fcg_name) as fcg_name, coalesce(ga.fga_deff_massa_fisc,0) as umur, ga.fga_kd_group, ga.fga_metd_dep_fisc, ga.fga_deff_massa_fisc, ga.fga_deff_trf_thn_fisc, ga.fga_metd_dep_comm, ga.fga_deff_massa_comm, ga.fga_deff_trf_thn_comm
		from fa_group_asset ga 
		join fa_category_group ca on(ga.fcg_kode=ca.fcg_kode)
		order by fcg_name, fga_deff_massa_fisc";
	$qry = dbselect_all($sql);
	foreach($qry as $r) { 
		$arrga["$r[fcg_name]"]["$r[umur]"]["fga_kd_group"] = $r[fga_kd_group];
		$arrga["$r[fcg_name]"]["$r[umur]"]["fga_metd_dep_fisc"] = $r[fga_metd_dep_fisc];
		$arrga["$r[fcg_name]"]["$r[umur]"]["fga_deff_massa_fisc"] = $r[fga_deff_massa_fisc];
		$arrga["$r[fcg_name]"]["$r[umur]"]["fga_deff_trf_thn_fisc"] = $r[fga_deff_trf_thn_fisc];
		$arrga["$r[fcg_name]"]["$r[umur]"]["fga_metd_dep_comm"] = $r[fga_metd_dep_comm];
		$arrga["$r[fcg_name]"]["$r[umur]"]["fga_deff_massa_comm"] = $r[fga_deff_massa_comm];
		$arrga["$r[fcg_name]"]["$r[umur]"]["fga_deff_trf_thn_comm"] = $r[fga_deff_trf_thn_comm];		
	}
	
	$i = 0;
	$kd_group = "AWAL";
	$sqlinsert = "";
	foreach($data as $r) {
		$fga_kd_group = $arrga[$r[jenis_aktifa]][$r[umur]]["fga_kd_group"];
		$fga_metd_dep_fisc = $arrga[$r[jenis_aktifa]][$r[umur]]["fga_metd_dep_fisc"];
		$fga_deff_massa_fisc = $arrga[$r[jenis_aktifa]][$r[umur]]["fga_deff_massa_fisc"];
		$fga_deff_trf_thn_fisc = $arrga[$r[jenis_aktifa]][$r[umur]]["fga_deff_trf_thn_fisc"];
		$fga_metd_dep_comm = $arrga[$r[jenis_aktifa]][$r[umur]]["fga_metd_dep_comm"];
		$fga_deff_massa_comm = $arrga[$r[jenis_aktifa]][$r[umur]]["fga_deff_massa_comm"];
		$fga_deff_trf_thn_comm = $arrga[$r[jenis_aktifa]][$r[umur]]["fga_deff_trf_thn_comm"];
		if($fga_kd_group <> $kd_group) {
			$no = 1;
			$kd_group = $fga_kd_group; 
		}
		$fam_asset_code = $plan.".".$fga_kd_group.".".str_pad($no,5,"0",STR_PAD_LEFT);
		$r[nama_unit] = filter_var($r[nama_unit], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$fam_name = $r[nama_unit];
		$fam_doc_reff = $r[no_bukti];
		$fam_qty = $r[qty];
		$fam_amount = $r[harga_peroleh];
		$fam_live_time_fisc = $fga_deff_massa_fisc ? $fga_deff_massa_fisc : "null";
		$fam_trf_fisc = $fga_deff_trf_thn_fisc ? $fga_deff_trf_thn_fisc : "null";
		$fam_live_time_comm =  $fga_deff_massa_comm ? $fga_deff_massa_comm : "null";
		$fam_trf_comm = $fga_deff_trf_thn_comm ? $fga_deff_trf_thn_comm : "null";
		$fam_accum_depp = $r[akumulasi_penyusutan];
		$fam_book_value = $r[nilai_buku];
		$fam_obtained_date = ExcelDateToDate($r[tanggal_peroleh]);

		$sqlinsert .= "INSERT INTO fa_asset_master(fam_asset_group, fam_asset_code, fam_name, fam_desc, fam_doc_reff, fam_qty, fam_amount, fam_gen_user, fam_gen_date, fam_meth_dep_fisc, fam_live_time_fisc, fam_trf_fisc, fam_meth_dep_comm, fam_live_time_comm, fam_trf_comm, fam_accum_depp, fam_book_value, fam_obtained_date, fam_last_date_dep) VALUES ('$fga_kd_group', '$fam_asset_code', '$fam_name', '$fam_name', '$fam_doc_reff', $fam_qty, $fam_amount, 'ardi', now(), '$fga_metd_dep_fisc', $fam_live_time_fisc, $fam_trf_fisc, '$fga_metd_dep_comm', $fam_live_time_comm, $fam_trf_comm, $fam_accum_depp, $fam_book_value, '$fam_obtained_date', '2018-04-30');";
		$i++;
		$no++;
	}
	// $hasil = dbsave($sqlinsert);
	$hasil = "gagal";
	if($hasil <> "OK") {
		$responce->sql = $sqlinsert;
	}
	$responce->hasil = $hasil;
	echo json_encode($responce);
}

?>