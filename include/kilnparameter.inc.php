
<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['77'];
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
	$groupd2id  = $_POST['groupd2id'];
	$groupd2Val = $_POST['groupd2Val'];

	$sqlmx = "SELECT MAX(CAST(kld_id AS integer)) AS maxiid FROM qc_kiln_group_detail WHERE sub_plant = '{$subplanid}' AND kl_group = '{$groupid}'";
	$mx    = dbselect_plan($app_plan_id, $sqlmx);

	if($mx[maxiid] == ''){
		$mx[maxiid] = 0;
	} else {
		$mx[maxiid] = $mx[maxiid];
	}
	$urutbaru = $mx[maxiid]+1;
	
	if($stat=='add'){
		$sql = "INSERT INTO qc_kiln_group_detail(sub_plant,kl_group,kld_id,kld_desc,kld_status) values('{$subplanid}','{$groupid}','{$urutbaru}','{$groupd2Val}','N')";
	}else if($stat=='edit'){
		$sql = "UPDATE qc_kiln_group_detail SET kld_desc='{$groupd2Val}' WHERE sub_plant='{$subplanid}' and kl_group='{$groupid}' and kld_id='{$groupd2id}'";
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
	$plan = $_POST['plan'];
	$grup = $_POST['grup'];
	$id   = $_POST['id'];

	$sql = "UPDATE qc_kiln_group_detail SET kld_status = 'C' WHERE sub_plant = '{$plan}' AND kl_group = '{$grup}' AND kld_id = '{$id}'";

	$hsl = dbsave_plan($app_plan_id, $sql);
	if($hsl == 'OK'){
		$out = "Berhasil hapus data.";
	}else{
		$out = "Gagal hapus data.";
	}
	echo $out;
}

function detailtabel() {
	global $app_plan_id, $akses, $app_subplan;
	$plan = $_POST['plan'];

	$sql = "SELECT kl_group, kl_desc from qc_kiln_group ORDER BY CAST(kl_group AS int) ASC";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	
	$ngrp =1;
	$i = 0;
	$out .= '<table class="table table-bordered table-condensed table-hover">';
	foreach($qry as $r) {

		$dtlgrup = romawi($ngrp).'. '.$r[kl_desc];
		$out .= '<tr>
		        	<th colspan="3" class="text-left" style="background-color:#00c0ef">'.$dtlgrup.'</th>
	        	 </tr>';

		$btnAdd = $akses[add] == 'Y' ? '<button type="button" class="btn btn-default btn-xs" onClick="tambahData(\''.$plan.'\',\''.$r[kl_group].'\',\''.$dtlgrup.'\')"><span class="glyphicon glyphicon-plus"></span></button>	 ' : '';

		$out .= '<tr><th colspan="3" class="text-left">'.$btnAdd.'&nbsp;</th></tr>';

		$out .= '<tr>
		        	<th>KODE</th>
		        	<th>PARAMETER</th>
		        	<th>KONTROL</th>
	        	 </tr>';


	    $sqlcek = "SELECT COUNT(*) AS jmldata from qc_kiln_group_detail 
	    		   WHERE sub_plant = '{$plan}' AND kl_group = '{$r[kl_group]}' AND kld_status = 'N'";
		$qrycek = dbselect_plan($app_plan_id, $sqlcek);

		if($qrycek[jmldata] == 0){
			$out .= '<tr><td colspan="3">Tidak ada data..</td></tr>';
		}else{

		    $sql3 = "SELECT * from qc_kiln_group_detail 
		    		 WHERE sub_plant = '{$plan}' AND kl_group = '{$r[kl_group]}' AND kld_status = 'N' ORDER BY CAST(kld_id AS int) ASC";
			$qry3 = dbselect_plan_all($app_plan_id, $sql3);




			foreach($qry3 as $r3) {


				$btnEdit = $akses[edit] == 'Y' ? '<button type="button" class="btn btn-default btn-xs" onClick="editData2(\''.$plan.'\',\''.$r[kl_group].'\',\''.$r3[kld_id].'\',\''.$r3[kld_desc].'\',\''.$dtlgrup.'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';


				$btnDel = $akses[del] == 'Y' ? '<button type="button" class="btn btn-default btn-xs" onClick="hapusData(\''.$plan.'\',\''.$r[kl_group].'\',\''.$r3[kld_id].'\',\''.$r3[kld_desc].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';

				$kontrol = $btnEdit.$btnDel;

				$out .= '<tr>
				        	<td class="text-center">'.$r3[kld_id].'</td>
				        	<td class="text-left">'.$r3[kld_desc].'</td>
				        	<td class="text-center">'.$kontrol.'</td>
			        	 </tr>';
			}

			    $out .= '<tr><td colspan="3">&nbsp;</td></tr>';
		}

	$ngrp++;
	}

	$out .= '</table>';

	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>
