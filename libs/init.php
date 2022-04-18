<?php

$root_path = dirname(__FILE__);
require_once ("konfigurasi.php");

session_start();


if($app_dbtype=="postgres"){
	$app_conn = pg_connect( "host=$app_host port=$app_port dbname=$app_dbname user=$app_user password=$app_pass");
	if(!$app_conn){
		print("Connection FAILED");
	}
}else if($app_dbtype=="mysql"){
	$app_conn = mysql_pconnect("{$app_host}:{$app_port}", $app_user, $app_pass);
	if(!$app_conn){
		print("Connection FAILED");
	}else{
		if(!mysql_select_db($app_dbname, $app_conn)){
			die(mysql_error($app_conn));
		}
	}
}

function dbselect_all($sql = null){
	global $app_conn,$app_dbtype;
	if($app_dbtype=="postgres"){
		$res = pg_query($app_conn,$sql);
		if(pg_num_rows($res)==0){
			$ret = null;
		}else{
			$ret = pg_fetch_all($res); // pg_free_result($res); // pg_close($srv_conn);	
		}
	}else if($app_dbtype=="mysql"){
		$res = mysql_query($sql,$app_conn);
		if(mysql_num_rows($res)==0){
			$ret = null;
		}else{
			while(($d1 = mysql_fetch_array($res,MYSQL_ASSOC)) != FALSE){
				$ret[] = $d1;
			}
		}
		mysql_free_result($res);
	}
	return $ret;
}

function dbselect($sql = null){
	global $app_conn,$app_dbtype;
	if($app_dbtype=="postgres"){
		$res = pg_query($app_conn,$sql);
		if(pg_num_rows($res)==0){
			$ret = null;
		}else{
			$ret = pg_fetch_array($res); // pg_free_result($res); // pg_close($srv_conn);	
		}
	}else if($app_dbtype=="mysql"){
		$res = mysql_query($sql,$app_conn);
		if(mysql_num_rows($res)==0){
			$ret = null;
		}else{
			if(($d1 = mysql_fetch_array($res)) != FALSE) {
				$ret = $d1;
			} else {
				$ret = NULL;
			}
		}
		mysql_free_result($res);
	}
	return $ret;
}

function dbsave($sql = null){
	global $app_conn,$app_dbtype;
	if($app_dbtype=="postgres"){
		$res = pg_query($app_conn,$sql);
		if($res){
			$ret = "OK";
		}else{
			$ret = pg_errormessage($app_conn);
		}
		// pg_free_result($res); // pg_close($app_conn);
	}else if($app_dbtype=="mysql"){
		$res = mysql_query($sql,$app_conn);
		if($res){
			$ret = "OK";
		}else{
			$ret = mysql_error($app_conn);
		}
		mysql_free_result($res);	
	}
	return $ret;	
}

function dbselect_plan_all($app_plan, $sql = null){
	switch ($app_plan) {
		case '1':
			$app_host_plan		= "db.p1.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '2':
			$app_host_plan		= "db.p2.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '3':
			$app_host_plan		= "db.p3.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '4':
			$app_host_plan		= "db.p4.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '5':
			$app_host_plan		= "db.p5.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '6':
			$app_host_plan		= "localhost";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '7':
			$app_host_plan		= "192.168.111.57";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi";
			$app_user_plan		= "armasi";
			$app_pass_plan		= "4rm4s1p455";
		break;
	}
	$app_conn_plan = pg_connect( "host=$app_host_plan port=$app_port_plan dbname=$app_dbname_plan user=$app_user_plan password=$app_pass_plan");
	if(!$app_conn_plan){
		return "Connection FAILED";
		exit;
	}
	$res = pg_query($app_conn_plan,$sql);
	if(pg_num_rows($res)==0){
		$ret = null;
	}else{
		$ret = pg_fetch_all($res);
	}
	return $ret;
}

function dbselect_plan($app_plan, $sql = null){
	switch ($app_plan) {
		case '1':
			$app_host_plan		= "db.p1.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '2':
			$app_host_plan		= "db.p2.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '3':
			$app_host_plan		= "db.p3.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '4':
			$app_host_plan		= "db.p4.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '5':
			$app_host_plan		= "db.p5.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '6':
			$app_host_plan		= "localhost";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '7':
			$app_host_plan		= "192.168.111.57";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi";
			$app_user_plan		= "armasi";
			$app_pass_plan		= "4rm4s1p455";
		break;
	}
	$app_conn_plan = pg_connect( "host=$app_host_plan port=$app_port_plan dbname=$app_dbname_plan user=$app_user_plan password=$app_pass_plan");
	if(!$app_conn_plan){
		return "Connection FAILED";
		exit;
	}
	$res = pg_query($app_conn_plan,$sql);
	if(pg_num_rows($res)==0){
		$ret = null;
	}else{
		$ret = pg_fetch_array($res);
	}
	return $ret;
}

function dbselect_plan_numrows($app_plan, $sql = null){
	switch ($app_plan) {
		case '1':
			$app_host_plan		= "db.p1.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '2':
			$app_host_plan		= "db.p2.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '3':
			$app_host_plan		= "db.p3.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '4':
			$app_host_plan		= "db.p4.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '5':
			$app_host_plan		= "db.p5.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '6':
			$app_host_plan		= "localhost";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '7':
			$app_host_plan		= "192.168.111.57";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi";
			$app_user_plan		= "armasi";
			$app_pass_plan		= "4rm4s1p455";
		break;
	}
	$app_conn_plan = pg_connect( "host=$app_host_plan port=$app_port_plan dbname=$app_dbname_plan user=$app_user_plan password=$app_pass_plan");
	if(!$app_conn_plan){
		return "Connection FAILED";
		exit;
	}
	$res = pg_query($app_conn_plan, $sql);
	return pg_num_rows($res);
}

function dbsave_plan($app_plan, $sql = null){
	switch ($app_plan) {
		case '1':
			$app_host_plan		= "db.p1.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '2':
			$app_host_plan		= "db.p2.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '3':
			$app_host_plan		= "db.p3.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '4':
			$app_host_plan		= "db.p4.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '5':
			$app_host_plan		= "db.p5.arwanacitra.com";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '6':
			$app_host_plan		= "localhost";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi_local";
			$app_user_plan		= "armasi_qc";
			$app_pass_plan		= "armasi";
		break;
		case '7':
			$app_host_plan		= "192.168.111.56";
			$app_port_plan		= "5432";
			$app_dbname_plan	= "armasi";
			$app_user_plan		= "armasi";
			$app_pass_plan		= "4rm4s1p455";
		break;
	}
	$app_conn_plan = pg_connect( "host=$app_host_plan port=$app_port_plan dbname=$app_dbname_plan user=$app_user_plan password=$app_pass_plan");
	if(!$app_conn_plan){
		return "Connection FAILED";
		exit;
	}
	$res = pg_query($app_conn_plan,$sql);
	if($res){
		$ret = "OK";
	}else{
		$ret = pg_errormessage($app_conn);
	}
	return $ret;	
}

function createMyExcel($vfilename,$vquery,$vcolumn,$vcoltitle){
	require_once("PHPExcel.php");
	$icell = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
	$icolumn = explode(",", $vcolumn);
	$icoltitle = explode(",", $vcoltitle);
	// Create new PHPExcel object
	$oexcel = new PHPExcel();
	// Create style for column title
	$coltitleSy = new PHPExcel_Style();
	$coltitleSy->applyFromArray(
		array('fill' 	=> array(
			'type'    	=> PHPExcel_Style_Fill::FILL_SOLID,
			'color'		=> array('argb' => '000000')),
			'font'		=> array(
				'bold' 	=> true,
				'color' => array('rgb' => 'FFFFFF')
			),
	        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
		)
	);
	// Set document properties
	$oexcel->getProperties()->setCreator("Ardi Fianto")
							->setLastModifiedBy("Ardi Fianto");
	$si = $oexcel->setActiveSheetIndex(0);
	$baris = 1;
	$row = dbselect_all($vquery);
	// Set Title of Column
	for ($i=0; $i<count($icolumn); $i++) {
		if($icoltitle[$i]){
			$si->setCellValue($icell[$i].$baris,$icoltitle[$i]);	
		}else{
			$si->setCellValue($icell[$i].$baris,$icolumn[$i]);
		}	
	}
	// Set style of colum title 
	$si->setSharedStyle($coltitleSy, $icell[0].$baris.':'.$icell[--$i].$baris);
	
	//Set contain colums and rows
	foreach($row as $r){
		$baris++;
		for ($i=0; $i<count($icolumn); $i++) {
			$si->setCellValue($icell[$i].$baris,$r[$icolumn[$i]]);
		}
	}
	// Rename worksheet
	$si->setTitle('Sheet1');
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$oexcel->setActiveSheetIndex(0);
	// Redirect output to a client’s web browser (Excel2007)
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename='.$vfilename.'.xlsx');
	header('Cache-Control: max-age=0');
	// If you're serving to IE 9, then the following may be needed
	header('Cache-Control: max-age=1');
	// If you're serving to IE over SSL, then the following may be needed
	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	header ('Pragma: public'); // HTTP/1.0
	$objWriter = PHPExcel_IOFactory::createWriter($oexcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
}

function dptKondisiWhere($_search,$filters,$searchField,$searchOper,$searchString){
    $qwery = "";
	if($_search == "true"){
		$ops = array(
			'eq'=>"=",
			'ne'=>"<>",
			'lt'=>"<",
			'le'=>"<=",
			'gt'=>">",
			'ge'=>">=",
			'bw'=>"LIKE",
			'bn'=>"NOT LIKE",
			'in'=>"IN",
			'ni'=>"NOT IN",
			'ew'=>"LIKE",
			'en'=>"NOT LIKE",
			'cn'=>"LIKE" ,
			'nc'=>"NOT LIKE",
			'nu'=>"IS NULL",
			'nn'=>"IS NOT NULL" 
		);
		if($filters){
	        $jsona = json_decode($filters,true);
	        if(is_array($jsona)){
				$groupOp = $jsona['groupOp'];
				$rules = $jsona['rules'];
	            $i = 0;
	            foreach($rules as $key => $val) {
	                $i++;
	                $field = $val['field'];
	                $op = $val['op'];
	                $data = $val['data'];
					$data = toValueSql($op,$data);
					if($i == 1) $qwery = " AND ";
					else $qwery .= " ".$groupOp." ";
					$qwery .= $field." ".$ops[$op]." ".$data;
	            }
	        }
	    }else if($searchString){
	    	$searchString = toValueSql($searchOper,$searchString);
			$qwery = " AND ".$searchField." ".$ops[$searchOper]." ".$searchString;	
	    }
	}
    return $qwery;
}

function toValueSql ($oper, $val) {
	if($oper=='bw' || $oper=='bn') return "'" . addslashes($val) . "%'";
	else if ($oper=='ew' || $oper=='en') return "'%" . addslashes($val) . "'";
	else if ($oper=='cn' || $oper=='nc') return "'%" . addslashes($val) . "%'";
	else if ($oper=='in' || $oper=='ni') return "(" . $val . ")";
	else if ($oper=='nu' || $oper=='nn') return "";
	else return "'" . addslashes($val) . "'";
}

function cgx_emptydate($date) {
    return empty($date) || $date == '0000-00-00';
}

function cgx_dmy2ymd($dmy) {
    $arr = explode("-", $dmy);
    $out = $arr[2] . '-' . $arr[1] . '-' . $arr[0];
    $out = cgx_emptydate($out) || $out == '--' ? '0000-00-00' : $out;
    return $out;
}

function cgx_mmm2m($mmm) {
	$mmm = strtolower($mmm);
	switch ($mmm) {
		case 'jan':
			$out = '1';
			break;
		case 'feb':
			$out = '2';
			break;
		case 'mar':
			$out = '3';
			break;
		case 'apr':
			$out = '4';
			break;
		case 'may':
			$out = '5';
			break;
		case 'jun':
			$out = '6';
			break;
		case 'jul':
			$out = '7';
			break;
		case 'aug':
			$out = '8';
			break;
		case 'sep':
			$out = '9';
			break;
		case 'oct':
			$out = '10';
			break;
		case 'nov':
			$out = '11';
			break;
		case 'dec':
			$out = '12';
			break;
	}
	return $out;
}

function cgx_angka($angka){
	if (is_numeric($angka)) {
		$out = $angka;
	} else {
		$out = 0;
	}
	return $out;
}

function cgx_null($par) {
	if($par) {
		$out = $par;
	} else {
		$out = 'NULL';
	}
	return $out;
}

function login($id, $password) {
    global $app_plan_id, $app_id;
    $sql = "SELECT u.*, p.plan_nama from app_user u join plan p on(u.plan_kode=p.plan_kode) where u.user_name ='{$id}' and u.password='{$password}'"; 
    //echo "TESSSSSS";
	$r = dbselect_plan($app_plan_id, $sql);
	
    if($r == "Connection FAILED") {
    	return FALSE;
    } else {
	    if($r){
	    	$_SESSION[$app_id]['authenticated'] = 1;
	        $_SESSION[$app_id]['user'] = $r;
	         $sql = "SELECT a.*
					from app_priv a 
					left join app_menu b on a.menu_id = b.am_id
					where user_id = '{$r[user_id]}' and b.am_stats = 'Y' order by menu_id";
			$qry = dbselect_plan_all($app_plan_id, $sql);
			$i = 0;
			if(is_array($qry)) {
				foreach($qry as $ro) {
					$_SESSION[$app_id]['daftarmenu'][$i] = $ro[menu_id];
					$_SESSION[$app_id]['app_priv']["$ro[menu_id]"]['view'] = $ro[ap_view];
					$_SESSION[$app_id]['app_priv']["$ro[menu_id]"]['add'] = $ro[ap_add];
					$_SESSION[$app_id]['app_priv']["$ro[menu_id]"]['edit'] = $ro[ap_edit];
					$_SESSION[$app_id]['app_priv']["$ro[menu_id]"]['del'] = $ro[ap_del];
					$_SESSION[$app_id]['app_priv']["$ro[menu_id]"]['print'] = $ro[ap_print];
					$_SESSION[$app_id]['app_priv']["$ro[menu_id]"]['approve'] = $ro[ap_approve];
					$i++;
				}	
			}
	        return TRUE;
	    }else{
	        return FALSE;
	    }
	}
}

function logout(){
	global $app_id;
	unset($_SESSION[$app_id]);       
}

function authenticated() {
    global $app_id;
    return $_SESSION[$app_id]['authenticated'] == 1;
}

function cek_menuprivilege($daftar_menu, $parent_id) {
	global $app_plan_id;
	$priv = false;
	$sql = "SELECT a.am_id, b.am_count 
		from app_menu a
		left join (SELECT am_parent, count(*) as am_count from app_menu group by am_parent) b ON(a.am_id=b.am_parent) 
		where a.am_parent = {$parent_id} order by am_id";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	foreach($qry as $r) {
		if ($r[am_count] > 0) {
			$priv = cek_menuprivilege($daftar_menu, $r[am_id]);
		} else {
			if(is_array($daftar_menu)) {
				foreach ($daftar_menu as $val) {
					if($r[am_id] == $val) {
						$priv = true;
						break;
					}	
				}	
			}
		}
	}
	return $priv;
}

function tampilkan_menu($parent, $level, $daftar_menu) {
	global $app_plan_id;
	
	$sql = "SELECT a.am_id, a.am_label, a.am_link, a.am_class, b.am_count, c.am_label as lblparent 
			from app_menu a 
			left join (SELECT am_parent, count(*) as am_count from app_menu group by am_parent) b ON(a.am_id=b.am_parent) 
			left join app_menu c on(a.am_parent=c.am_id)
			WHERE COALESCE(a.am_parent, 0) = '{$parent}' AND a.am_stats = 'Y' order by a.am_sort";

	$qry = dbselect_plan_all($app_plan_id, $sql);
	if($level == 0) {
		$out .= "<ul class='sidebar-menu' data-widget='tree'>";	
	} else {
		$out .= "<ul class='treeview-menu'>";
	}
	foreach($qry as $row){
		if ($row['am_count'] > 0) {
			if(in_array($row['am_id'],$daftar_menu)) {
				$out .= "<li class='treeview'><a href='#'><i class='".$row['am_class']."'></i><span>".$row['am_label']."</span><span class='pull-right-container'><i class='fa fa-angle-left pull-right'></i></span></a>";
				$out .= tampilkan_menu($row['am_id'], $level + 1, $daftar_menu);
				$out .= "</li>";
			}
        } else {
        	if(in_array($row['am_id'],$daftar_menu)) {
				$row['lblparent'] = $row['lblparent'] ? $row['lblparent']." - ".$row['am_label'] : $row['am_label'];
            	$out .= "<li class='treeview'><a href='#' class='menua' lk='".$row['am_link']."' judul='".$row['lblparent']."'><i class='".$row['am_class']."'></i><span>" .$row['am_label']."</span></a></li>";
			}
        }
	}
    $out .= "</ul>";
    return $out;
}

function cbo_plant($nilai = "TIDAKADA"){
	global $app_plan_id;
	$sql = "SELECT plan_kode, plan_nama from plan order by plan_kode";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	$out .= "<option></option>";
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[plan_kode] == $nilai){
				$out .= "<option value='{$r[plan_kode]}' selected>$r[plan_nama]</option>";
			} else {
				$out .= "<option value='{$r[plan_kode]}'>$r[plan_nama]</option>";
			}	
		}	
	}
	return $out;
}

function cbo_subplant($nilai = "TIDAKADA", $plusall = false) {
	global $app_id;
    switch ($_SESSION[$app_id]['user']['sub_plan']) {
		case 'All':
			$qry = array("","A","B","C");
			if($plusall) {
				$qry = array("All","A","B","C");	
			}
			break;
		case 'A':
			$qry = array("A");
			break;
		case 'B':
			$qry = array("B");
			break;
		case 'C':
			$qry = array("C");
			break;
	}
	if(is_array($qry)) {
		foreach($qry as $r) {
			if($r == $nilai) {
				$out .= "<option value='{$r}' selected>$r</option>";
			} else {
				$out .= "<option value='{$r}'>$r</option>";
			}	
		}	
	}
	return $out;
}

function cbo_shift($nilai = "TIDAKADA") {
	if($nilai == "TIDAKADA") {
		$jam = intval(date("H"))-1;
		$jam = $jam == -1 ? 23 : $jam;
		$jamshift = str_pad($jam,2,"0",STR_PAD_LEFT).date(":i"); 
		if($jamshift >= "08:00" && $jamshift <= "15:59"){
			$shift = 1;
		} else if($jamshift >= "16:00" && $jamshift <= "23:59"){
			$shift = 2;
		} else {
			$shift = 3;
		}	
	} else {
		$shift = $nilai;
	}
	$qry = array("1","2","3");
	foreach($qry as $r) {
		if($r == $shift) {
			$out .= "<option value='{$r}' selected>$r</option>";
		} else {
			$out .= "<option value='{$r}'>$r</option>";
		}	
	}
	return $out;
}

function Romawi($angka){
    $hsl = "";
    if($angka<1||$angka>3999){
        $hsl = "Batas Angka 1 s/d 3999";
    }else{
         while($angka>=1000){
             $hsl .= "M";
             $angka -= 1000;
         }
         if($angka>=500){
             if($angka>500){
                 if($angka>=900){
                     $hsl .= "M";
                     $angka-=900;
                 }else{
                     $hsl .= "D";
                     $angka-=500;
                 }
             }
         }
         while($angka>=100){
             if($angka>=400){
                 $hsl .= "CD";
                 $angka-=400;
             }else{
                 $angka-=100;
             }
         }
         if($angka>=50){
             if($angka>=90){
                 $hsl .= "XC";
                  $angka-=90;
             }else{
                $hsl .= "L";
                $angka-=50;
             }
         }
         while($angka>=10){
             if($angka>=40){
                $hsl .= "XL";
                $angka-=40;
             }else{
                $hsl .= "X";
                $angka-=10;
             }
         }
         if($angka>=5){
             if($angka==9){
                 $hsl .= "IX";
                 $angka-=9;
             }else{
                $hsl .= "V";
                $angka-=5;
             }
         }
         while($angka>=1){
             if($angka==4){
                $hsl .= "IV";
                $angka-=4;
             }else{
                $hsl .= "I";
                $angka-=1;
             }
         }
    }
    return ($hsl);
}



function conversi_hari($hari){
	switch($hari){
		case 'Sun':
			$hari_ini = "Minggu";
		break;
 
		case 'Mon':			
			$hari_ini = "Senin";
		break;
 
		case 'Tue':
			$hari_ini = "Selasa";
		break;
 
		case 'Wed':
			$hari_ini = "Rabu";
		break;
 
		case 'Thu':
			$hari_ini = "Kamis";
		break;
 
		case 'Fri':
			$hari_ini = "Jumat";
		break;
 
		case 'Sat':
			$hari_ini = "Sabtu";
		break;
		
		default:
			$hari_ini = "Tidak di ketahui";		
		break;
	}
 
	return $hari_ini;
}



function format_rupiah($angka){
	$hasil_rupiah = number_format($angka,0,',','.');
	return $hasil_rupiah;
}


function level_jab($kdjab){
	switch($kdjab){
		case '1':
			$jab = "PM";
		break;
 
		case '2':			
			$jab = "KABAG";
		break;
		
		default:
			$jab = "Tidak di ketahui";		
		break;
	}
 
	return $jab;
}

function check_shift($time) {
   	if($time >= "07:00" && $time <= "14:59"){
		$shift = 1;
	} else if($time >= "15:00" && $time <= "22:59"){
		$shift = 2;
	} else {
		$shift = 3;
	}
	return $shift;
}

?>
