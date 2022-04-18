<?php

include_once("../libs/init.php");

global $app_id;

$akses = $_SESSION[$app_id]['app_priv']['40'];
$app_subplan = $_SESSION[$app_id]['user']['sub_plan'];
$users = $_SESSION[$app_id]['user']['user_name'];

if ($_GET["mode"]) {
    $oper = $_GET["mode"];
} else {
    $oper = $_POST["oper"];
}

switch ($oper) {
    case "urai":
        urai();
        break;
    case "suburai":
        suburai();
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
    case "cbosubplant":
        cbosubplant($_GET['withselect']);
        break;
    case "detailtabel":
        detailtabel($_POST['stat']);
        break;
    case "cbogenset":
        cbogenset($_POST['withselect']);
        break;
}

function urai()
{
    global $app_plan_id, $akses, $app_subplan;
    $page = $_POST['page'];
    $rows = $_POST['rows'];
    $sidx = $_POST['sidx'];
    $sord = $_POST['sord'];
    $subplan_kode = $_GET['subplan'];
    $tanggal = explode('@', $_GET['tanggal']);
    $tglfrom = cgx_dmy2ymd($tanggal[0]) . " 00:00:00";
    $tglto = cgx_dmy2ymd($tanggal[1]) . " 23:59:59";
    $whsatu = dptKondisiWhere($_POST['_search'], $_POST['filters'], $_POST['searchField'], $_POST['searchOper'], $_POST['searchString']);
    $whdua = "";
    if ($app_subplan <> 'All') {
        $whdua .= " and qgh_sub_plant = '" . $app_subplan . "'";
    }

    if ($_POST['qgh_id']) {
        $whdua .= " and qgh_id = '" . $_POST['qgh_id'] . "'";
    }
    if ($_POST['qgh_sub_plant']) {
        $whdua .= " and qgh_sub_plant = '" . $_POST['qgh_sub_plant'] . "'";
    }
    if ($_POST['qgh_date']) {
        $whdua .= " and qgh_date = '" . $_POST['qgh_date'] . "'";
    }
    if (!$sidx) $sidx = 1;

    $start = $rows * $page - $rows;
    $limit = "limit " . $rows . " offset " . $start;
    $sql = "SELECT qgh_id, qgh_sub_plant, qgh_date from qc_genset_header_test where qgh_rec_status is null and qgh_date >= '{$tglfrom}' and qgh_date <= '{$tglto}' $whsatu $whdua
			order by qgh_id ";
    $qry = dbselect_plan_all($app_plan_id, $sql);
    $i = 0;

    if ($page > $total_pages) $page = $total_pages;
    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    foreach ($qry as $ro) {
        $btnView = $akses[view] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="lihatData(\'' . $ro['qgh_id'] . '\')"><span class="glyphicon glyphicon-zoom-in"></span></button> ' : '';
        $btnEdit = $akses[edit] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="editData(\'' . $ro['qgh_id'] . '\')"><span class="glyphicon glyphicon-pencil"></span></button> ' : '';
        $btnDel = $akses[del] == 'Y' ? '<button class="btn btn-default btn-xs" onClick="hapusData(\'' . $ro['qgh_id'] . '\')"><span class="glyphicon glyphicon-trash"></span></button> ' : '';
        $ro['kontrol'] = $btnView . "" . $btnEdit . "" . $btnDel;
        $datetime = explode(' ', $ro['qgh_date']);
        $ro['date'] = $datetime[0];
        $ro['time'] = substr($datetime[1], 0, 5);
        $responce->rows[$i]['id'] = $i;
        $responce->rows[$i]['cell'] = array($ro['qgh_id'], $ro['qgh_sub_plant'], $ro['date'], $ro['time'], $ro['kontrol']);
        $i++;
    }
    echo json_encode($responce);
}

function suburai()
{
    global $app_plan_id;
    $qgh_id = $_POST['qgh_id'];
    $sql = "SELECT qc_genset_detail_test.*, qssd_monitoring_desc, qss_desc from qc_genset_detail_test join qc_sp_sett_master on(qc_genset_detail_test.qid_group=qc_sp_sett_master.qss_group) join qc_sp_sett_detail on(qc_genset_detail_test.qid_r=qc_sp_sett_detail.qssd_seq) where qgh_id = '{$qgh_id}' order by qid_group, qid_r";
    $qry = dbselect_plan_all($app_plan_id, $sql);
    $i = 0;
    foreach ($qry as $ro) {
        $responce->rows[$i]['cell'] = array($ro['qss_desc'], $ro['qid_r'], $ro['qssd_monitoring_desc'], $ro['qid_s'], $ro['qid_t']);
        $i++;
    }
    echo json_encode($responce);
}

function simpan($stat)
{
    global $app_id, $app_plan_id, $users;
    $r = $_REQUEST;
    $r[qgh_date] = cgx_dmy2ymd($r[qgh_date]) . " " . $r[qgh_time] . ":00";
    $r[qgh_user_create] = $users;
    $r[qgh_date_create] = date("Y-m-d H:i:s");
    if ($stat == "add") {
        $sql = "SELECT max(qgh_id) AS qgh_id_max FROM qc_genset_header_test WHERE qgh_sub_plant = '{$r[qgh_sub_plant]}'";
        $mx = dbselect_plan($app_plan_id, $sql);
        if ($mx[qgh_id_max] == '') {
            $mx[qgh_id_max] = 0;
        } else {
            $mx[qgh_id_max] = substr($mx[qgh_id_max], -6);
        }
        $r[qgh_id] = $app_plan_id . $r[qgh_sub_plant] . "/" . str_pad(++$mx[qgh_id_max], 6, "0", STR_PAD_LEFT); // format id

        $sql = "INSERT INTO qc_genset_header_test(qgh_id, qgh_sub_plant, qgh_date, qgh_user_create, qgh_date_create) 
                VALUES('{$r[qgh_id]}', '{$r[qgh_sub_plant]}', '{$r[qgh_date]}', '{$r[qgh_user_create]}', '{$r[qgh_date_create]}');";
        $xsql = dbsave_plan($app_plan_id, $sql); // insert data qc_genset
        if ($xsql == "OK") {
            $k2sql = "";
            $genset=implode(',',$r[qgh_genset]);
            $no_urut=implode(',',$r[qgh_no_urut]);
            $k2sql .= "INSERT into qc_genset_detail_test (qgh_id, qgh_no_urut, qgh_genset) values('{$r[qgh_id]}', '{$no_urut}', '{$genset}');";
            $out = dbsave_plan($app_plan_id, $k2sql);
        } else {
            $out = $xsql;
        }
    } else if ($stat == 'edit') {
        $r[qgh_user_modify] = $users;
        $r[qgh_date_modify] = date("Y-m-d H:i:s");
        $sql = "UPDATE qc_genset_header_test SET qgh_user_modify = 'f', qgh_date_modify = '{$r[qgh_date_modify]}' WHERE qgh_id = '{$r[qgh_id]}';";
        $xsql = dbsave_plan($app_plan_id, $sql);
        if ($xsql == "OK") {
            $k1sql = "DELETE FROM qc_genset_detail_test WHERE qgh_id = '{$r[qgh_id]}';";
            $x1sql = dbsave_plan($app_plan_id, $k1sql);
            if ($x1sql == "OK") {
                $k2sql = "";
                $genset = implode(',',$r[qgh_genset]);
                $no_urut = implode(',',$r[qgh_no_urut]);
                $k2sql .= "INSERT INTO qc_genset_detail_test (qgh_id, qgh_no_urut, qgh_genset) VALUES('{$r[qgh_id]}', '{$no_urut}', '{$genset}');";
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

function hapus()
{
    global $app_plan_id;
    $qgh_id = $_POST['kode'];
    $sql = "UPDATE qc_genset_header_test set qgh_rec_status='1' where qgh_id = '{$qgh_id}';";
    echo dbsave_plan($app_plan_id, $sql);
}

function cbosubplant($ws = false)
{
    echo ($ws) ? "<select>" . cbo_subplant() . "</select>" : cbo_subplant();
}


function cbogenset($ws = false)
{
    echo ($ws) ? "<select>" . cbo_genset() . "</select>" : cbo_genset();
}

function cbo_genset()
{
    global $app_plan_id;
    $sql = "SELECT DISTINCT(qgh_no_urut) FROM qc_genset_detail_test";
    $qry = dbselect_plan_all($app_plan_id, $sql);
    $_res = array();
    foreach ($qry as $key=>$val)
    {
        $_temp = explode(',',$val['qgh_no_urut']);
        foreach ($_temp as $keys=>$values)
        {
            if(!in_array($values,$_res))
            {
                echo "<option value='".$values."'>".$values."</option>";
                array_push($_res,$values);
            }
        }
    }
}

function detailtabel($stat)
{
    global $app_plan_id, $app_subplan;
    if ($stat == "edit" || $stat == "view") {
        $qgh_id = $_POST['kode'];
        $sql0 = "SELECT * from qc_genset_header_test where qgh_id = '{$qgh_id}'";
        $rhead = dbselect_plan($app_plan_id, $sql0);
        $datetime = explode(' ', $rhead['qgh_date']);
        $rhead['date'] = cgx_dmy2ymd($datetime[0]);
        $rhead['time'] = substr($datetime[1], 0, 5);
        $sql = "SELECT * from qc_genset_detail_test where qgh_id = '{$qgh_id}'";
        $qry = dbselect_plan_all($app_plan_id, $sql);
        $arr_qld_group = array();
        foreach ($qry as $r) {
            $val_genset = explode(',', $r[qgh_genset]);
            $val_no_urut = explode(',', $r[qgh_no_urut]);
        }
        $responce->qgh_sub_plant = substr($qgh_id, 1, 1);
        $responce->qgh_id = $qgh_id;
        $responce->qgh_date = $rhead['date'];
        $responce->qgh_time = $rhead['time'];
    }
    $out = "
	    <div class='col col-md-12' id='genset_isi' style='margin-top:30px;background-color:#e3f3fc' >
	        <h3>
	            <i class='fa fa-info-circle' ></i> Data Genset
	        </h3>
	";
    $jml = ( count($val_genset) > 0 ) ? count($val_genset) : 3 ;
    for ($i = 0; $i < $jml; $i++)
    {
        // $label=str_replace('', replace, subject)
        $out .= "
                <div class='row genset-row'>
                    <div class='col col-sm-4'>
                        <input class='form-control' type='text' name='qgh_no_urut[".$i."]' id='qgh_no_urut" . $i . "' value='".( isset($val_no_urut[$i]) ? $val_no_urut[$i] : 'Genset-'.($i+1) ). "' style='text-align:right;' placeholder='Genset-".($i+1)."' required>
                    </div>
                    <div class='col col-sm-4'>
                        <input class='form-control' type='text' name='qgh_genset[".$i."]' id='qgh_genset" . $i . "' value='" . $val_genset[$i] . "' style='text-align:right;' onkeyup='hanyanumerik(this.id,this.value)' required>
                    </div>
                    <div class='col col-sm-2'>
                        <button class='btn btn-sm btn-icon btn-danger' onclick='dropItems(this)'><i class='fa fa-trash'></i></button>
                    </div>
                </div>
			";
    }
    if( $stat !== 'view') {
        $out .= '
            <div class="row">
                <hr/>
                <div class="col-sm-12 text-right">
                    <button type="button" class="btn btn-success btn-sm" onClick="addItems()" id="btnItem"><i class="fa fa-plus-circle"></i> Tambah Genset</button>
                </div>
            </div>
            ';
    }
    if ($stat == 'add') {
        $text = "Simpan";
    } else {
        $text = "Update";
    }
    if( $stat !== 'view'){
        $out .= "<div class='row'>
                    <div class='col col-md-12' style='margin-top:30px;background-color:white;'>
                    <br>
                    <button  class='btn btn-primary btn-sm' onClick=\"simpanData('" . $stat . "')\">" . $text . "</button> 
                    <button type='button' class='btn btn-warning btn-sm' onClick=\"formAwal()\">Batal</button>";
    }
    else
    {
        $out .= "<div class='row'><div class='col col-md-12 mt-5 bg-white'><button type='button' class='btn btn-warning btn-sm pull-right' onClick=\"formAwal()\">Kembali</button></div></div>";
    }
    $out .= "</div>
		</div>
	</div>";
    $responce->detailtabel = $out;
    echo json_encode($responce);
}

?>
