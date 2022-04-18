<?php

include_once("../libs/init.php");

if($_GET["mode"]) {
	$oper = $_GET["mode"];
} else {
	$oper = $_POST["oper"];
}

switch ($oper) {
	case "edit":
		simpan("edit");
		break;
	case "detailtabel":
		detailtabel($_POST['stat']);
		break;
	case "gantipwd":
		gantipwd();
		break;
}



function simpan($stat) {
	global $app_plan_id;
	$r = $_POST;
	if($stat=='edit') {
		$sql = "UPDATE app_user set user_name='{$r[user_name]}', first_name='{$r[first_name]}', last_name='{$r[last_name]}', plan_kode='{$r[plan_kode]}' where user_id={$r[user_id]};";
		$xsql = dbsave_plan($app_plan_id, $sql);
		if($xsql == "OK") {
			$sql0 = "SELECT user_name, password from app_user where user_id={$r[user_id]};";
			$rhead = dbselect_plan($app_plan_id, $sql0); 
			if(login($rhead[user_name], $rhead[password])){
				$out = "OK2";
			} else {
				$out = "OK";
			}
		} else {
			$out = $xsql;
		}
	}
	echo $out;
}

function cboplant($nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT plan_kode, plan_nama from plan order by plan_kode";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[plan_kode] == $nilai){
				$out .= "<option value='{$r[plan_kode]}' selected>$r[plan_nama]</option>";
			} else {
				$out .= "<option value='{$r[plan_kode]}'>$r[plan_nama]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;	
	}
}

function detailtabel($stat) {
	global $app_plan_id;
	if($stat == "edit") {
		$sql0 = "SELECT u.*, p.plan_nama from app_user u join plan p on(u.plan_kode=p.plan_kode) where u.user_id={$_POST[user_id]}";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$responce->user_id = $rhead[user_id];
		$responce->user_name = $rhead[user_name];
		$responce->first_name = $rhead[first_name];
		$responce->last_name = $rhead[last_name];
		$responce->password = $rhead[password];
		$responce->plan_kode = cboplant($rhead[plan_kode],true);
		$responce->plan_nama = $rhead[plan_nama];
	}
    echo json_encode($responce);
}

function gantipwd() {
	global $app_plan_id;
	$r = $_POST;
	$sql0 = "SELECT password from app_user where user_id={$r[user_id]};";
	$rhead = dbselect_plan($app_plan_id, $sql0);
	if($rhead[password] == $r[pwdlama]) {
		$sql = "UPDATE app_user set password='{$r[pwdbaru]}' where user_id={$r[user_id]};";
		$out = dbsave_plan($app_plan_id, $sql);
	} else {
		$out = "PWDSALAH";
	}
	echo $out;
}

?>