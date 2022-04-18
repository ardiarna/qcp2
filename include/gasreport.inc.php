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
	case "test":
		test();
		break;
	case "cbosubplant":
		cbosubplant($_GET['withselect']);
		break;
}

function urai(){
	global $app_plan_id;
	$subplan = $_GET['subplan'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.qgh_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.qgh_sub_plant as subplan, a.qgh_date as tanggal, c.qmu_desc as mesin, b.qgd_line as line, qgpd_desc as seq, b.qgd_value as nilai, a.qgd_user_create, a.qgd_date_create  
		from qc_gas_header a
		join qc_gas_detail b on(a.qgh_id=b.qgh_id)
		join qc_mesin_unit c on(b.qgd_mesin=c.qmu_code)
		join qc_gas_prep_detail d on(b.qgd_mesin=d.qgpd_mesin_code and b.qgd_seq=d.qgpd_seq)
		where a.qgh_rec_stat = 'N'
		and a.qgh_date >= '{$tglfrom}' and a.qgh_date <= '{$tglto}' $whdua
		order by subplan, tanggal, c.qmu_seq, line, b.qgd_seq";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	foreach($qry as $ro){
		$datetime = explode(' ',$ro['tanggal']);
		$ro['line'] = $ro['line'] ? "Line ".$ro['line'] : '-';
		$responce->rows[$i]['subplan']=$ro['subplan'];
		$responce->rows[$i]['tanggal']=$datetime[0];
		$responce->rows[$i]['mesin']=$ro['mesin'];
		$responce->rows[$i]['line']=$ro['line'];
		$responce->rows[$i]['seq']=$ro['seq'];
		$responce->rows[$i]['nilai']=$ro['nilai'];
		$responce->rows[$i]['usercreate']=$ro['qgd_user_create'];
		$responce->rows[$i]['datecreate']=$ro['qgd_date_create'];
		$i++;
	}
	echo json_encode($responce);
}

function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant("TIDAKADA",true);
	$out .= $withselect ? "</select>" : "";
	echo $out;
}



?>