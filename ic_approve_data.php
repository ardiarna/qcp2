<?php
include_once("libs/init.php");

session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['121'];

$user  = $_SESSION[$app_id]['user']['user_name'];

//CEK AKSES APPROVE : 1 PM, 2 : KABAG
$sql_apr = " SELECT * FROM qc_ic_in_appr WHERE appr_uname = '$user' ";
$r_apr   = dbselect_plan($app_plan_id, $sql_apr);
$kdjab   = $r_apr['appr_jab'];
$nmjab   = level_jab($kdjab);



$arr_jab = array(1,2); 

?>
<style type="text/css">
    #tblsm,
    .ui-jqgrid-htable {
        font-size:11px;
   }
    th {
      text-align:center;
   }     
</style>


<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="modal fade" id="Mdl" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header alert-info">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Approval Data</h4>
                        </div>
                        <div class="modal-body table-responsive">
                            <div id="isiMdl"></div>
                        </div>
                        <div class="modal-footer" id="footer">
                        </div>
                    </div>
                </div>
            </div>

            <div class="box-body" id="boxAwal">
                <form class="form-horizontal" id="frCari">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">Dari : </label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="tglFrom" id="tglFrom">
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">Sampai : </label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="tglTo" id="tglTo">
                        </div>
                    </div>

                    <div class="form-group">
                        <?php 
                            if (in_array($kdjab, $arr_jab)){
                                echo '<label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">Status : </label>
                                        <div class="col-sm-3" style="margin-top:3px;">
                                            <select class="form-control input-sm" name="cmbstatus" id="cmbstatus">
                                                <option value="">Belum Approve</option>
                                                <option value="Y">Sudah Approve</option>
                                                <option value="N">Tidak Approve</option>
                                            </select>
                                        </div>';
                            }else{
                                echo '<label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">Apr. Kabag : </label>
                                        <div class="col-sm-3" style="margin-top:3px;">
                                            <select class="form-control input-sm" name="cmbkabag" id="cmbkabag">
                                                <option value="">Belum Approve</option>
                                                <option value="Y">Sudah Approve</option>
                                                <option value="N">Tidak Approve</option>
                                            </select>
                                        </div>

                                        <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">Apr. Pm : </label>
                                        <div class="col-sm-3" style="margin-top:3px;">
                                            <select class="form-control input-sm" name="cmbpm" id="cmbpm">
                                                <option value="">Belum Approve</option>
                                                <option value="Y">Sudah Approve</option>
                                                <option value="N">Tidak Approve</option>
                                            </select>
                                        </div>';
                            }
                        ?>
                       

                    
                        <div class="col-sm-1" style="margin-top:3px;">
                            <button type="button" id="btnCari" class="btn btn-primary btn-sm"><span>GO</span></button>
                        </div>

                        <?php
                            if($kdjab == 1){
                                echo '<div class="col-sm-5" style="margin-top:3px;">
                                        <span style="color:red;">*Data yang tampil adalah data yang sudah di cek Kabag</span>
                                      </div>';
                            }
                        ?>
                    </div>
                </form>
                <div id="kontensm">
                    <table id="tblsm"></table>
                    <div id="pgrsm"></div>        
                </div>
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/ic_approve_data.inc.php";
var vdropmenu = false;
var validator = "";

function tampilTabel(tblnya, pgrnya, pTanggal, stsKabag = 'All', stsPm = 'All', stsUser = 'All', pSubPlan = 'All'){
    var topnya = tblnya+"_toppager";
    var vshrinktofit = true;
    var vpanjanglayar = 150;
    if($(window).height() >= 520){vpanjanglayar = $(window).height()-(250+$("#frCari").height()+$(".content-header").height());}
    if($(window).width() <= 800){vshrinktofit = false;}
    jQuery(tblnya).jqGrid({
        url:frm + "?mode=urai&tanggal="+pTanggal+"&stsKabag="+stsKabag+"&stsPm="+stsPm+"&stsUser="+stsUser+"&subplan="+pSubPlan,
        mtype:"POST",
        datatype:"json",
        colModel:[
            {label:"VIEW", name:'btn_view', index:'btn_view', width:70, align:'center'},
            {label:"ID", name:'ic_id', index:'ic_id', width:70, align:'center'},
            {label:"TANGGAL", name:'ic_date', index:'ic_date', width:70, align:'center'},
            {label:"MATERIAL", name:'ic_nm_material', index:'ic_nm_material', width:130, align:'left'},
            {label:"LW", name:'ic_lw', index:'ic_lw', width:70, align:'center'},
            {label:"VISCO", name:'ic_visco', index:'ic_visco', width:70, align:'center'},
            {label:"KADAR AIR", name:'ic_kadar_air', index:'ic_kadar_air', width:70, align:'center'},
            {label:"HASIL", name:'ic_hasil', index:'ic_hasil', width:70, align:'center'},
            {label:"KABAG", name:'ic_ap_kabag_sts', index:'ic_ap_kabag_sts', width:50, align:'center', stype:'select', searchoptions:{value:"ALL:ALL;Y:Y;N:N"}},
            {label:"PM", name:'ic_ap_pm_sts', index:'ic_ap_pm_sts', width:50, align:'center', stype:'select', searchoptions:{value:"ALL:ALL;Y:Y;N:N"}},
        ],
        sortname:"ic_id",
        sortorder:'desc', 
        styleUI:"Bootstrap",
        hoverrows:false,
        loadonce:false,
        height:vpanjanglayar,
        rowNum:-1,
        rowList:[5,10,15,20,"-1:All"],
        rownumbers:true,
        pager:pgrnya,
        editurl:frm,
        altRows:true,
        viewrecords:true,
        autowidth:true,
        shrinkToFit:vshrinktofit,
        toppager:true,
        multiselect: true,
    });

    jQuery(tblnya).jqGrid('navGrid', topnya,
        {
            add:false,
            edit:false,
            del:false,
            view:false,
            search:false,
            refresh:false,
            alertwidth:250,
            dropmenu:vdropmenu
       }, //navbar
        {}, //edit
        {}, //new
        {}, //del
        {}, //serch
        {}, //view
    );
    jQuery(tblnya).jqGrid('filterToolbar');
    $('.ui-search-toolbar').hide();
    $(topnya+"_center").hide();
    $(topnya+"_right").hide();
    $(topnya+"_left").attr("colspan", "3");

    jQuery(tblnya).jqGrid('navButtonAdd', topnya+"_left", {
        caption:"", buttonicon:'glyphicon-search', title:"Tampilkan baris pencarian",
        onClickButton:function () {
            this.toggleToolbar();
       }
    });

    

    <?php if (in_array($kdjab, $arr_jab)) { ?>
        jQuery(tblnya).jqGrid('navButtonAdd', topnya+"_left", {
            caption:"", buttonicon:'glyphicon-check', title:"Set Approval",
            onClickButton:function () {
                Approval(tblnya);
           }
        });
    <?php } ?>

    $(pgrnya+"_center").hide();

}



function lihatData(kode) {
    $.post(frm+"?mode=detailtabel", {kode:kode,jenis:'single'}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#isiMdl").html(o.out);
        $("#footer").html(o.footer);
        $("#Mdl").modal('show');    
    });
}


$(document).ready(function () {

    var ubahUkuranJqGrid = function(){
        var vukur = $('#kontensm').width(); 
        if(vukur <= 800){
            $("#tblsm").setGridWidth(vukur, false); 
       } else {
            $("#tblsm").setGridWidth(vukur, true);
       }
    };
    $('#kontensm').resize(ubahUkuranJqGrid);
    var ubahTinggiJqGrid = function(){
        var vpanjanglayar = 150;
        if($(window).height() >= 520){
            vpanjanglayar = $(window).height()-(250+$("#frCari").height()+$(".content-header").height());
        }
        $("#tblsm").setGridHeight(vpanjanglayar);
    };
    $('#frCari').resize(ubahTinggiJqGrid);
    
    $("#tglFrom").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        todayHighlight:true,
        endDate:'date'
    }).on('changeDate', function(e) {
        var tglTo = $("#tglTo").val().split("-");
        var tglb = new Date(tglTo[2], parseInt(tglTo[1])-1, tglTo[0]);
        var tgla = new Date(e.date.getFullYear(), e.date.getMonth(), e.date.getDate());
        $("#tglTo").datepicker('setStartDate', tgla);
        if(tgla > tglb) {
            alert('Tanggal Dari tidak boleh lebih cepat dari Tanggal s/d, mohon ubah Tanggal s/d.');
            $("#tglTo").datepicker('show');
        }
    }).val(moment().format("01-MM-YYYY"));

    $("#tglTo").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        todayHighlight:true,
        endDate:'date',
        startDate:'date'
    }).val(moment().format("DD-MM-YYYY"));


    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val(),$("#cmbkabag").val(),$("#cmbpm").val(),$("#cmbstatus").val());
   

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val(),$("#cmbkabag").val(),$("#cmbpm").val(),$("#cmbstatus").val());
    });
});

function Approval(tblnya) {
    var arr_id;
    arr_id = jQuery(tblnya).jqGrid('getGridParam','selarrrow');
    
    if(arr_id != ''){

        $.post(frm+"?mode=detaillist", {kode:arr_id}, function(resp,stat){
            var o = JSON.parse(resp);
            $("#isiMdl").html(o.out);
            $("#footer").html(o.footer);
            $("#Mdl").modal('show');    
        });
    }else{
        swal("ID belum di pilih!", {
          icon: "error",
        });
    }

    
}

function apporve_data($jenis,$id,$hasil = null){
    var $sts = $("#ap_status").val(); 
    var $ket = $("#ap_keterangan").val(); 
    var $kirim; 
    var $alert; 
   
    if($sts == ''){
        $kirim = 'NO';
        $alert = "Status belum di pilih!";
    }else{
        if($jenis == 'single'){
            if($hasil == 'N' && $sts == 'Y' && $ket == ''){
                $kirim = 'NO';
                $alert = "Keterangan perlu di isi!";
            }else{
                $kirim = 'YES';
            }
        }else{
            if($ket == ''){
                $kirim = 'NO';
                $alert = "Keterangan perlu di isi!";
            }else{
                $kirim = 'YES';
            }
        }
    }

    if($kirim == 'YES'){
        $.post(frm+"?mode=setapproval", {id:$id,sts:$sts,ket:$ket,jenis:$jenis}, function(resp,stat){
            var o = JSON.parse(resp);
            if(o.respons == 'OK'){
                swal("Berhasil!", {
                  icon: "success",
                });

                $.jgrid.gridUnload("#tblsm");
                tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val(),$("#cmbkabag").val(),$("#cmbpm").val(),$("#cmbstatus").val());
                $("#Mdl").modal('hide');
            }else{
                swal(o.respons, {
                  icon: "error",
                });
            }
        });
    }else{
        swal($alert, {
          icon: "error",
        });
    }
}
</script>