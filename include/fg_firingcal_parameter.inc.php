
<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['93'];
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
}


function urai(){
	global $app_plan_id, $akses, $app_subplan;
	$page = $_POST['page']; 
	$rows = $_POST['rows']; 
	$sidx = $_POST['sidx']; 
	$sord = $_POST['sord'];
	$subplan_kode = $_GET['subplan'];
	$whsatu = dptKondisiWhere($_POST['_search'],$_POST['filters'],$_POST['searchField'],$_POST['searchOper'],$_POST['searchString']);
	$whdua = "";
	if($app_subplan <> 'All') {
		$whdua .= " and sub_plan = '".$app_subplan."'";
	}
	if($subplan_kode <> 'All') {
		$whdua .= " and sub_plan = '".$subplan_kode."'";
	}
	
	if(!$sidx) $sidx = 1;
	$sql = "SELECT count(*) as count from qc_subplan where 1=1 $whsatu $whdua";
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
		$sql = "SELECT sub_plan from qc_subplan where 1=1 $whsatu $whdua order by $sidx $sord $limit";
		$qry = dbselect_plan_all($app_plan_id, $sql);
		$i = 0;
	} else { 
		$total_pages = 1; 
	}
	if($page > $total_pages) $page = $total_pages; 
	$responce->page = $page; 
	$responce->total = $total_pages; 
	$responce->records = $count; 
	if($count > 0) {
		foreach($qry as $ro){
			$btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\''.$ro['sub_plan'].'\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
			$ro['kontrol'] = $btnView;
			$responce->rows[$i]['id']=$i; 
			$responce->rows[$i]['cell']=array($ro['sub_plan'],$ro['kontrol']);
			$i++;
		}
	}
	echo json_encode($responce);
}



function simpan($stat){
	global $app_plan_id;
	$subplanid  = $_POST['subplanid'];
	$groupid    = $_POST['groupid'];
	$myid  		= $_POST['myid'];
	$myparrent  = $_POST['myparrent'];
	$fc_gddesc  = $_POST['fc_gddesc'];
	$fc_gdunit  = $_POST['fc_gdunit'];



	$sqlmx = "SELECT MAX(CAST(fc_gdid AS integer)) AS maxiid FROM qc_fg_firing_group_detail";
	$mx    = dbselect_plan($app_plan_id, $sqlmx);

	if($mx[maxiid] == ''){
		$mx[maxiid] = 0;
	} else {
		$mx[maxiid] = $mx[maxiid];
	}
	$urutbaru = $mx[maxiid]+1;
	
	if($stat=='add'){
		$sql = "INSERT INTO qc_fg_firing_group_detail(fc_sub_plant, fc_group, fc_gdid, fc_gdparrent, fc_gddesc, fc_gdunit, fc_gdstatus) values('{$subplanid}','{$groupid}','{$urutbaru}','{$myparrent}','{$fc_gddesc}','{$fc_gdunit}','N')";
	}else if($stat=='edit'){
		$sql = "UPDATE qc_fg_firing_group_detail SET fc_gddesc='{$fc_gddesc}', fc_gdunit='{$fc_gdunit}' WHERE fc_sub_plant='{$subplanid}' and fc_group='{$groupid}' and fc_gdid='{$myid}'";
	}

	$hsl = dbsave_plan($app_plan_id, $sql);
	if($hsl == 'OK'){
		$out = "Data Berhasil disimpan";
	}else{
		$out = $hsl;
	}

	echo $out;
}

function hapus(){
	global $app_plan_id;
	$subplanid = $_POST['subplanid'];
	$groupid = $_POST['groupid'];
	$myid   = $_POST['myid'];

	$sql = "UPDATE qc_fg_firing_group_detail SET fc_gdstatus = 'C' WHERE fc_sub_plant = '{$subplanid}' AND fc_group = '{$groupid}' AND fc_gdid = '{$myid}'";

	$hsl = dbsave_plan($app_plan_id, $sql);
	if($hsl == 'OK'){
		$out = "Berhasil hapus data.";
	}else{
		$out = "Gagal hapus data.";
	}
	echo $out;
}

function display_node($plan, $grup, $parent, $padding) {
	global $app_plan_id, $akses;

	$sqlcek = "SELECT COUNT(*) AS jmldata from qc_fg_firing_group_detail where fc_gdparrent = '$parent' and fc_sub_plant = '{$plan}' and fc_group = '{$grup}'";
	$qrycek = dbselect_plan($app_plan_id, $sqlcek);

	if($qrycek[jmldata] <= 0){
		$out .= '<tr><td colspan="3">Tidak ada data..</td></tr>';
	}else{

			$sql = "SELECT a.fc_gdid, a.fc_group, a.fc_sub_plant, a.fc_gdparrent, a.fc_gdunit, a.fc_gddesc, b.jml
					from qc_fg_firing_group_detail a 
					left join (select fc_gdparrent, count(fc_gdparrent) as jml 
								from qc_fg_firing_group_detail 
								where fc_sub_plant = '{$plan}' and fc_group = '{$grup}' and fc_gdstatus = 'N'
								group by fc_gdparrent) b
					on a.fc_gdid = b.fc_gdparrent
					where a.fc_gdparrent = '$parent' and a.fc_sub_plant = '{$plan}' and a.fc_group = '{$grup}' and a.fc_gdstatus = 'N'
					ORDER BY CAST(a.fc_gdid AS int) ASC";
			$qry = dbselect_plan_all($app_plan_id, $sql);
			foreach($qry as $ro){
				if($ro[fc_gdparrent] == 0){
					$btn_tambah = $akses[add] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="tambahData(\''.$ro[fc_sub_plant].'\',\''.$ro[fc_group].'\',\''.$ro[fc_gddesc].'\',\''.$ro[fc_gdid].'\')"><span class="glyphicon glyphicon-plus"></span></button> ' : '';
				}			
				$btn_edit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData2(\''.$ro[fc_sub_plant].'\',\''.$ro[fc_group].'\',\''.$ro[fc_gddesc].'\',\''.$ro[fc_gdunit].'\',\''.$ro[fc_gdid].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
				$btn_hapus = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\''.$ro[fc_sub_plant].'\',\''.$ro[fc_group].'\',\''.$ro[fc_gdid].'\',\''.$ro[fc_gddesc].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
				$out .= '<tr>
		    		<td style="padding-left:'.$padding.'px;">'.$ro[fc_gddesc].'</td>
		    		<td class="text-center">'.$ro[fc_gdunit].'</td>
		    		<td class="text-center">'.$btn_tambah.$btn_edit.$btn_hapus.'</td>
		    		</tr>';
				if ($ro[jml] > 0) {
					$out .= display_node($ro[fc_sub_plant],$ro[fc_group],$ro[fc_gdid], $padding+20);
		        }
			}
	}
	return $out;
}

function detailtabel() {
	global $app_plan_id, $akses, $app_subplan;
	$plan = $_POST['plan'];

	$sql = "SELECT fc_group, fc_desc from qc_fg_firing_group ORDER BY CAST(fc_group AS int) ASC";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	
	$ngrp =1;
	$i = 0;
	$out .= '<table class="table table-bordered table-condensed table-hover table-striped">';
	foreach($qry as $r) {

		$dtlgrup = romawi($ngrp).'. '.$r[fc_desc];
		$out .= '<tr>
		        	<th colspan="3" class="text-left" style="background-color:#00c0ef">'.$dtlgrup.'</th>
	        	 </tr>';

		$btnAdd = $akses[add] == 'Y' ? '<button type="button" class="btn btn-default btn-xs" onClick="tambahData(\''.$plan.'\',\''.$r[fc_group].'\',\''.$dtlgrup.'\',0)"><span class="glyphicon glyphicon-plus"></span></button>	 ' : '';

		$out .= '<tr><th colspan="3" class="text-left">'.$btnAdd.'&nbsp;</th></tr>';

		$out .= '<tr>
		        	<th>DESCRIPSI</th>
		        	<th>UNIT</th>
		        	<th>KONTROL</th>
	        	 </tr>';

	    $out .= display_node($plan, $r[fc_group], 0, 10);
	$ngrp++;
	}

	$out .= '</table>';

	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>
