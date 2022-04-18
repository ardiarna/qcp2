<?php

include_once("../libs/init.php");

if($_GET["mode"]) {
	$oper = $_GET["mode"];
} else {
	$oper = $_POST["oper"];
}

switch($oper){
	case "signin":
		signin();
		break;
	case "signout":
		signout();
		break;
}

function signin(){
	$r = $_REQUEST;
	if(login($r['uname'], $r['pwd'])){
		echo "OK";
	}else{
		echo "Silahkan isi username dan kata sandi dengan benar.";
	}
}

function signout(){
	logout();
	header("Location: ../");
}

?>