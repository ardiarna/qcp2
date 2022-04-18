
<?php

include_once("../libs/init.php");

$akses = $_SESSION[$app_id]['app_priv']['69'];
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
	$groupd1id  = $_POST['groupd1id'];
	$groupd2id  = $_POST['groupd2id'];
	$groupd2Val = $_POST['groupd2Val'];
	$cd_type    = $_POST['cd_type'];

	$sqlmx = "SELECT MAX(CAST(cd2_id AS integer)) AS maxiid FROM qc_pd_cm_group_d2 WHERE sub_plant = '{$subplanid}' AND cm_group = '{$groupid}' AND cd1_id = '{$groupd1id}'";
	$mx    = dbselect_plan($app_plan_id, $sqlmx);

	if($mx[maxiid] == ''){
		$mx[maxiid] = 0;
	} else {
		$mx[maxiid] = $mx[maxiid];
	}
	$urutbaru = $mx[maxiid]+1;
	
	if($stat=='add'){
		$sql = "INSERT INTO qc_pd_cm_group_d2(sub_plant,cm_group,cd1_id,cd2_id,cd2_desc,cd2_status,cd2_type) values('{$subplanid}','{$groupid}','{$groupd1id}','{$urutbaru}','{$groupd2Val}','N','{$cd_type}')";
	}else if($stat=='edit'){
		$sql = "UPDATE qc_pd_cm_group_d2 SET cd2_desc='{$groupd2Val}', cd2_type='{$cd_type}' WHERE sub_plant='{$subplanid}' and cm_group='{$groupid}' and cd1_id='{$groupd1id}' 
				and cd2_id='{$groupd2id}'";
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
	$id1  = $_POST['id1'];
	$id2  = $_POST['id2'];

	$sql = "UPDATE qc_pd_cm_group_d2 SET cd2_status = 'C' WHERE sub_plant = '{$plan}' AND cm_group = '{$grup}' AND cd1_id = '{$id1}' AND cd2_id = '{$id2}' ";

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

	$sql = "SELECT cm_group, cm_desc from qc_pd_cm_group order by cm_group";
	$qry = dbselect_plan_all($app_plan_id, $sql);
	

	
	$ngrp =1;
	$i = 0;
	$out .= '<table class="table table-bordered table-condensed table-hover">';
	foreach($qry as $r) {
		$out .= '<tr>
		        	<th colspan="4" class="text-left" style="background-color:#00c0ef">'.romawi($ngrp).'. '.$r[cm_desc].'</th>
	        	 </tr>';

			$sql2 = "SELECT * from qc_pd_cm_group_d1 WHERE cm_group = '{$r[cm_group]}' ORDER BY CAST(cd1_id AS int) ASC";
			$qry2 = dbselect_plan_all($app_plan_id, $sql2);

			$no_d1 = 'A';
			foreach($qry2 as $r2) {

				$dtlgrup2 = romawi($ngrp).' - '.$no_d1.'. '.$r2[cd1_desc];

				$btnAdd = $akses[add] == 'Y' ? '<button type="button" class="btn btn-default btn-xs" onClick="tambahData(\''.$plan.'\',\''.$r[cm_group].'\',\''.$r2[cd1_id].'\',\''.$dtlgrup2.'\')"><span class="glyphicon glyphicon-plus"></span></button>	 ' : '';


				$out .= '<tr>
				        	<th colspan="4" class="text-left">
				        		'.$dtlgrup2.'
				        	</th>
			        	 </tr>';
			    $out .= '<tr>
				        	<th colspan="4" class="text-left">'.$btnAdd.'&nbsp;</th>
			        	 </tr>';

			   
			    $sqlcek = "SELECT COUNT(*) AS jmldata from qc_pd_cm_group_d2 
			    		   WHERE sub_plant = '{$plan}' AND cm_group = '{$r[cm_group]}' AND cd1_id = '{$r2[cd1_id]}' AND cd2_status = 'N'";
				$qrycek = dbselect_plan($app_plan_id, $sqlcek);

				if($qrycek[jmldata] == 0){
					$out .= '<tr><td colspan="4">Tidak ada data..</td></tr>';
				}else{


				    $sql3 = "SELECT * from qc_pd_cm_group_d2 
				    		 WHERE sub_plant = '{$plan}' AND cm_group = '{$r[cm_group]}' AND cd1_id = '{$r2[cd1_id]}' AND cd2_status = 'N' ORDER BY CAST(cd2_id AS int) ASC";
					$qry3 = dbselect_plan_all($app_plan_id, $sql3);


					$out .= '<tr>
					        	<th>KODE</th>
					        	<th>PARAMETER</th>
					        	<th>TYPE</th>
					        	<th>KONTROL</th>
				        	 </tr>';



					foreach($qry3 as $r3) {


						$btnEdit = $akses[edit] == 'Y' ? '<button type="button" class="btn btn-default btn-xs" onClick="editData2(\''.$plan.'\',\''.$r[cm_group].'\',\''.$r2[cd1_id].'\',\''.$r3[cd2_id].'\',\''.$r3[cd2_desc].'\',\''.$dtlgrup2.'\',\''.$r3[cd2_type].'\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';


						$btnDel = $akses[del] == 'Y' ? '<button type="button" class="btn btn-default btn-xs" onClick="hapusData(\''.$plan.'\',\''.$r[cm_group].'\',\''.$r2[cd1_id].'\',\''.$r3[cd2_id].'\',\''.$r3[cd2_desc].'\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';

						$kontrol = $btnEdit.$btnDel;

						$out .= '<tr>
						        	<td class="text-center">'.$r3[cd2_id].'</td>
						        	<td class="text-left">'.$r3[cd2_desc].'</td>
						        	<td class="text-left">'.$r3[cd2_type].'</td>
						        	<td class="text-center">'.$kontrol.'</td>
					        	 </tr>';
					}

					    $out .= '<tr><td colspan="4">&nbsp;</td></tr>';
				}
			    
			$no_d1++;
			}

	$ngrp++;
	}

	$out .= '</table>';

	$responce->detailtabel = $out; 
	echo json_encode($responce);
}

?>
