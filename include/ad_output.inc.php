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
		cbosubplant($_GET['withselect']);
		break;
	case "export_exc":
		export_exc();
		break;
	case "export_eco":
		export_eco();
		break;
	case "export_kw":
		export_kw();
		break;
}

function urai(){
	global $app_plan_id;
	$grup = $_GET['grup'];
	$subplan = $_GET['subplan'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and a.qbh_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT a.qbh_sub_plant as subplan, a.qbh_body_code as kodebody, b.qbd_material_type as tipe, b.qbd_material_code as line, c.item_nama as item_name, d.qbu_kode as box, b.qbd_formula as formula, b.qbd_dw as dw, b.qbd_mc as mc, b.qbd_ww as ww, 
		b.qbd_value as nilai, b.qbd_remark as remark, a.qbh_shift as shift, a.qbh_bm_no as balmil, a.qbh_user_create, a.qbh_date_create
		from qc_bm_header a
		join qc_bm_detail b on(a.qbh_id=b.qbh_id)
		join item c on(b.qbd_material_code=c.item_kode)
		left join qc_box_unit d on(b.qbd_box_unit=d.qbu_kode) where a.qbh_rec_status='N' and a.qbh_date >= '{$tglfrom}' and a.qbh_date <= '{$tglto}' $whdua
		order by subplan, kodebody, tipe, line, shift, balmil";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$i = 0;
	foreach($qry as $ro){
		$ro['tipe'] = $ro['tipe'] == "1" ? "MATERIAL" : "ADDITIVE";
		$responce->rows[$i]['subplan']=$ro['subplan'];
		$responce->rows[$i]['kodebody']=$ro['kodebody'];
		$responce->rows[$i]['tipe']=$ro['tipe'];
		$responce->rows[$i]['line']=$ro['line'];
		$responce->rows[$i]['item_name']=$ro['item_name'];
		$responce->rows[$i]['box']=$ro['box']; 
		$responce->rows[$i]['formula']=$ro['formula']; 
		$responce->rows[$i]['dw']=$ro['dw']; 
		$responce->rows[$i]['mc']=$ro['mc']; 
		$responce->rows[$i]['ww']=$ro['ww']; 
		$responce->rows[$i]['shift']=$ro['shift'];
		$responce->rows[$i]['balmil']=$ro['balmil']; 
		$responce->rows[$i]['nilai']=$ro['nilai'];
		$responce->rows[$i]['remark']=$ro['remark']; 
		$responce->rows[$i]['usercreate']=$ro['qbh_user_create'];
		$responce->rows[$i]['datecreate']=$ro['qbh_date_create'];
		$i++;
	}
	echo json_encode($responce);
}

function export_exc() {
	global $app_plan_id;
	$subplan = $_GET['subplan'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$whdua = "";
	if($subplan <> 'All') {
		$whdua .= " and qex_sub_plant = '".$subplan."'";
	}
	$sql = "SELECT * from qcdaily_exp where qex_rec_status = 'N' and qex_date >= '$tglfrom' and qex_date <= '$tglto' $whdua order by qex_id";
	createMyExcel($app_plan_id,"Analisa_Defect_Exp",$sql,"qex_id,qex_sub_plant,qex_line,qex_date,qex_motif,qex_seri,qex_shading,qex_exp,qex_eco,qex_kw,qex_user_create,qex_date_create,qex_user_modify,qex_date_modify","ID,SUBPLANT,LINE,TANGGAL,MOTIF,SERI,SHADING,EXP,ECO,KW,USERCREATE,DATECREATE,USERMODIFY,DATEMODIFY");
}

function export_eco() {
	global $app_plan_id;
	$subplan = $_GET['subplan'];
	$tanggal = explode('@', $_GET['tanggal']);
	$tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
	$tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
	$whdua = "";
	if($subplan <> 'All') {
        $whdua .= " and qec_sub_plant = '".$subplan."'";
    }
    $sql = "SELECT * from qcdaily_eco where qec_rec_status = 'N' and qec_date >= '$tglfrom' and qec_date <= '$tglto' and qec_m2 > 0 $whdua order by qec_id";
    createMyExcel($app_plan_id,"Analisa_Defect_Eco",$sql,"qec_id,qec_sub_plant,qec_line,qec_date,qec_motif,qec_seri,qec_shading,qec_defect_kode,qec_m2,qec_keterangan","ID,SUBPLANT,LINE,TANGGAL,MOTIF,SERI,SHADING,KODE DEFECT,M2,KETERANGAN");
}

function export_kw() {
    global $app_plan_id;
    $subplan = $_GET['subplan'];
    $tanggal = explode('@', $_GET['tanggal']);
    $tglfrom = cgx_dmy2ymd($tanggal[0])." 00:00:00";
    $tglto = cgx_dmy2ymd($tanggal[1])." 23:59:59";
    $whdua = "";
    if($subplan <> 'All') {
        $whdua .= " and qkw_sub_plant = '".$subplan."'";
    }
    $sql = "SELECT * from qcdaily_kw where qkw_rec_status = 'N' and qkw_date >= '$tglfrom' and qkw_date <= '$tglto' and qkw_m2 > 0 $whdua order by qkw_id";
    createMyExcel($app_plan_id,"Analisa_Defect_Kw",$sql,"qkw_id,qkw_sub_plant,qkw_line,qkw_date,qkw_motif,qkw_seri,qkw_shading,qkw_defect_kode,qkw_m2,qkw_keterangan","ID,SUBPLANT,LINE,TANGGAL,MOTIF,SERI,SHADING,KODE DEFECT,M2,KETERANGAN");
}

function cbosubplant($withselect = false){
	$out  = $withselect ? "<select>" : "";
	$out .= cbo_subplant("TIDAKADA",true);
	$out .= $withselect ? "</select>" : "";
	echo $out;
}



?>