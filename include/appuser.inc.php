<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['31'];
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
	case "cbouser":
		cbouser();
		break;
}

function urai(){
	global $app_plan_id, $akses, $app_subplan;
	$page = $_POST['page'];
	$rows = $_POST['rows'];
	$sidx = $_POST['sidx'];
	$sord = $_POST['sord'];
	$whsatu = dptKondisiWhere($_POST['_search'],$_POST['filters'],$_POST['searchField'],$_POST['searchOper'],$_POST['searchString']);
	$whdua = "";
	if($app_subplan <> 'All') {
		$whdua .= " and sub_plan = '".$app_subplan."'";
	}
	if($_POST['user_id']) {
		$whdua .= " and user_id = ".$_POST['user_id'];
	}
	if($_POST['user_name']) {
		$whdua .= " and lower(user_name) like lower('%".$_POST['user_name']."%')";
	}
	if($_POST['first_name']) {
		$whdua .= " and lower(first_name) like lower('%".$_POST['first_name']."%')";
	}
	if($_POST['last_name']) {
		$whdua .= " and lower(last_name) like lower('%".$_POST['last_name']."%')";
	}
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from app_user join plan on (app_user.plan_kode = plan.plan_kode) where 1=1 $whsatu $whdua";
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
		$sql = "SELECT app_user.*, plan_nama from app_user join plan on (app_user.plan_kode = plan.plan_kode) where 1=1 $whsatu $whdua order by $sidx $sord $limit";
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
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\''.$ro['user_id'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['user_id'].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
			$btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro['user_id'].'\',\''.$ro['user_name'].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
			$ro['kontrol'] = $btnView.$btnEdit.$btnDel;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['user_id'],$ro['user_name'],$ro['first_name'],$ro['last_name'],$ro['plan_nama'],$ro['sub_plan'],$ro['kontrol']); 
			$i++;
		}
	}
	echo json_encode($responce);
}

function simpan($stat){
	global $app_plan_id;
	$r = $_POST;
	if($stat=='add'){
		$sql = "INSERT INTO app_user(user_name,first_name,last_name,password,plan_kode,sub_plan) values('{$r[user_name]}','{$r[first_name]}','{$r[last_name]}','{$r[password]}','{$r[plan_kode]}','{$r[sub_plan]}') RETURNING user_id;";
		$xsql = dbselect_plan($app_plan_id, $sql);
		if($xsql[user_id]) { 
			$k2sql = "";
			foreach ($r[ap_view] as $i => $view) {
				$k2sql .= "INSERT into app_priv(user_id,menu_id,ap_view,ap_add,ap_edit,ap_del,ap_print,ap_approve) values({$xsql[user_id]},{$i},'{$view}','{$r[ap_add][$i]}','{$r[ap_edit][$i]}','{$r[ap_del][$i]}','{$r[ap_print][$i]}','{$r[ap_approve][$i]}');";
			}
			$out = dbsave_plan($app_plan_id, $k2sql);
		} else {
			$out = $xsql;
		}
	}else if($stat=='edit'){
		$sql = "UPDATE app_user set user_name='{$r[user_name]}', first_name='{$r[first_name]}', last_name='{$r[last_name]}', password ='{$r[password]}', plan_kode='{$r[plan_kode]}', sub_plan='{$r[sub_plan]}' where user_id={$r[user_id]};";
		$xsql = dbsave_plan($app_plan_id, $sql);
		if($xsql == "OK") {
			$k1sql = "DELETE from app_priv where user_id='{$r[user_id]}';";
			$x1sql = dbsave_plan($app_plan_id, $k1sql);
			if($x1sql == "OK") {
				$k2sql = "";
				foreach ($r[ap_view] as $i => $view) {
					$k2sql .= "INSERT into app_priv(user_id,menu_id,ap_view,ap_add,ap_edit,ap_del,ap_print,ap_approve) values({$r[user_id]},{$i},'{$view}','{$r[ap_add][$i]}','{$r[ap_edit][$i]}','{$r[ap_del][$i]}','{$r[ap_print][$i]}','{$r[ap_approve][$i]}');";
				}
				$out = dbsave_plan($app_plan_id, $k2sql);	
			} else {
				$out = $x1sql;
			}
		} else {
			$out = $xsql;
		}
	}
	echo $out;
}

function hapus(){
	global $app_plan_id;
	$pkey_user_id= $_POST['user_id'];
	$sql = "DELETE from app_user where user_id='{$pkey_user_id}'; DELETE from app_priv where user_id='{$pkey_user_id}';";
	echo dbsave_plan($app_plan_id, $sql);
}

function cbouser($nilai = "TIDAKADA", $isret = false){
	global $app_plan_id;
	$sql = "SELECT user_id, user_name, first_name, last_name from app_user order by user_name";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	if(is_array($qry)) {
		foreach($qry as $r){
			if($r[user_id] == $nilai){
				$out .= "<option value='{$r[user_id]}' selected>$r[plan_nama]</option>";
			} else {
				$out .= "<option value='{$r[user_id]}'>$r[user_name] - $r[first_name] $r[last_name]</option>";
			}	
		}	
	}
	if($isret){
		return $out;
	} else {
		echo $out;	
	}
}

function display_node($am_parent, $padding, $user_id = 0) {
	global $app_plan_id;
	if($am_parent > 0) {
		$whparent = "a.am_parent = ".$am_parent;
	} else {
		$whparent = "a.am_parent is null";
	}
	$sql = "SELECT a.am_id, a.am_label, a.am_link, a.am_parent, a.am_class, a.am_sort, b.am_count, c.ap_view, c.ap_add, c.ap_edit, c.ap_del, c.ap_print, c.ap_approve
		from app_menu a 
		left join (SELECT am_parent, count(*) as am_count from app_menu group by am_parent) b on(a.am_id=b.am_parent)
		left join app_priv c on(a.am_id=c.menu_id and c.user_id=$user_id)
		where a.am_stats = 'Y' AND $whparent order by a.am_sort";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	foreach($qry as $ro){
		
		if ($ro[am_count] > 0) {
			if($ro[ap_view] == "Y") {
        		$ro[ap_view] = "checked";
        	}

    		$out .= '<tr>
	    		<td style="padding-left:'.$padding.'px;background-color:#63bbbf;"><i class="'.$ro[am_class].'"></i> '.$ro[am_label].'</td>
	    		<td class="text-center" style="background-color:#63bbbf;"><input type="checkbox" name="ap_view['.$ro[am_id].']" id="ap_view_'.$ro[am_id].'" value="Y" onClick="aktifCekbox(\''.$ro['am_id'].'\')" '.$ro[ap_view].'></td>
	    		<td class="text-center" style="background-color:#63bbbf;"><input type="hidden" name="ap_add['.$ro[am_id].']" id="ap_add_'.$ro[am_id].'" value=""></td>
	    		<td class="text-center" style="background-color:#63bbbf;"><input type="hidden" name="ap_edit['.$ro[am_id].']" id="ap_edit_'.$ro[am_id].'" value=""></td>
	    		<td class="text-center" style="background-color:#63bbbf;"><input type="hidden" name="ap_del['.$ro[am_id].']" id="ap_del_'.$ro[am_id].'" value=""></td>
	    		<td class="text-center" style="background-color:#63bbbf;"><input type="hidden" name="ap_print['.$ro[am_id].']" id="ap_print_'.$ro[am_id].'" value=""></td>
	    		<td class="text-center" style="background-color:#63bbbf;"><input type="hidden" name="ap_approve['.$ro[am_id].']" id="ap_approve_'.$ro[am_id].'" value=""></td>
	    		</tr>';


			$out .= display_node($ro[am_id], $padding+20, $user_id);
        } else {
        	if($ro[ap_view] == "Y") {
        		$ro[ap_view] = "checked";
        		$ro[ap_add] = $ro[ap_add] == "Y" ? "checked" : "";
	        	$ro[ap_edit] = $ro[ap_edit] == "Y" ? "checked" : "";
	        	$ro[ap_del] = $ro[ap_del] == "Y" ? "checked" : "";
	        	$ro[ap_print] = $ro[ap_print] == "Y" ? "checked" : "";
	        	$ro[ap_approve] = $ro[ap_approve] == "Y" ? "checked" : "";
        	} else {
        		$ro[ap_add] = "disabled";
	        	$ro[ap_edit] = "disabled";
	        	$ro[ap_del] = "disabled";
	        	$ro[ap_print] = "disabled";
	        	$ro[ap_approve] = "disabled";
        	}
        	$out .= '<tr>
    		<td style="padding-left:'.$padding.'px;"><i class="'.$ro[am_class].'"></i> '.$ro[am_label].'</td>
    		<td class="text-center"><input type="checkbox" name="ap_view['.$ro[am_id].']" id="ap_view_'.$ro[am_id].'" value="Y" onClick="aktifCekbox(\''.$ro['am_id'].'\')" '.$ro[ap_view].'></td>
    		<td class="text-center"><input type="checkbox" name="ap_add['.$ro[am_id].']" id="ap_add_'.$ro[am_id].'" value="Y" '.$ro[ap_add].'></td>
    		<td class="text-center"><input type="checkbox" name="ap_edit['.$ro[am_id].']" id="ap_edit_'.$ro[am_id].'" value="Y" '.$ro[ap_edit].'></td>
    		<td class="text-center"><input type="checkbox" name="ap_del['.$ro[am_id].']" id="ap_del_'.$ro[am_id].'" value="Y" '.$ro[ap_del].'></td>
    		<td class="text-center"><input type="checkbox" name="ap_print['.$ro[am_id].']" id="ap_print_'.$ro[am_id].'" value="Y" '.$ro[ap_print].'></td>
    		<td class="text-center"><input type="checkbox" name="ap_approve['.$ro[am_id].']" id="ap_approve_'.$ro[am_id].'" value="Y" '.$ro[ap_approve].'></td>
    		</tr>';
        }
	}
	return $out;
}

function detailtabel($stat) {
	global $app_plan_id;
	$_POST[user_id] = $_POST[user_id] ? $_POST[user_id] : 0;
	$out = '<table id="tabeldetail" class="table table-bordered table-striped table-condensed"><tr>
        <th>MENU</th><th width="50">VIEW</th><th width="50">ADD</th><th width="50">EDIT</th><th width="50">DEL</th><th width="50">PRINT</th><th width="50">APPR</th>
        </tr>';
	$out .= display_node(0, 10, $_POST[user_id]);
	$out .= "</table>";

	if($stat == "edit" || $stat == "view") {
		$sql0 = "SELECT * from app_user where user_id={$_POST[user_id]}";
		$rhead = dbselect_plan($app_plan_id, $sql0);
		$responce->user_id = $rhead[user_id];
		$responce->user_name = $rhead[user_name];
		$responce->first_name = $rhead[first_name];
		$responce->last_name = $rhead[last_name];
		$responce->password = $rhead[password];
		$responce->plan_kode = cbo_plant($rhead[plan_kode]);
		$responce->sub_plan = cbo_subplant($rhead[sub_plan],true);
	} else if($stat == "add"){
		$responce->plan_kode = cbo_plant();
		$responce->sub_plan = cbo_subplant("TIDAKADA",true);
	}
	$responce->detailtabel = $out;
    echo json_encode($responce);
}

?>