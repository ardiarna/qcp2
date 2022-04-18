<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['17'];
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
            <div class="box-body" id="boxAwal">
                <div id="kontensm">
                    <table id="tblsm"></table>
                    <div id="pgrsm"></div>        
                </div>
            </div>
            <div class="box-body" id="boxEdit" style="display: none;">
                <form class="form-horizontal" id="frEdit">
                    <input type="hidden" name="aded" id="aded">
                    <input type="hidden" name="pkey_qps_sub_plant" id="pkey_qps_sub_plant">
                    <input type="hidden" name="pkey_qps_code" id="pkey_qps_code">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qps_sub_plant" name="qps_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">NOMOR</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="qps_code" id="qps_code" maxlength="2">
                        </div>    
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">KAPASITAS</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="qps_cap" name="qps_cap">
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">KETERANGAN</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="qps_desc" name="qps_desc">       
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12" style="margin-top:3px;text-align:center;">
                            <button type="button" class="btn btn-primary btn-sm" onClick="simpanData()">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button>
                        </div>
                    </div>
                </form>    
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var frm = "include/mdsd.inc.php";
var vdropmenu = false;
var validator = "";

function tampilTabel(tblnya, pgrnya, pSubPlan = 'All') {
    var topnya = tblnya+"_toppager";
    var vshrinktofit = true;
    var vpanjanglayar = 150;
    if($(window).height() >= 520){vpanjanglayar = $(window).height()-(230+$(".content-header").height());}
    if($(window).width() <= 550){vshrinktofit = false;}
    jQuery(tblnya).jqGrid({
        url:frm + "?mode=urai&subplan="+pSubPlan,
        mtype:"POST",
        datatype:"json",
        colModel:[
            {label:"PLANT", name:'qps_sub_plant', index:'qps_sub_plant', width:80, stype:'select', searchoptions:{value:":;A:A;B:B;C:C"}},
            {label:"NOMOR", name:'qps_code', index:'qps_code', width:80},
            {label:"KAPASITAS", name:'qps_cap', index:'qps_cap', width:90, sorttype:"int", formatter:'integer', align:'right'},
            {label:"KETERANGAN", name:'qps_desc', index:'qps_desc', width:400},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:50, align:'center'}
        ],
        sortname:'qps_sub_plant asc,qps_code', 
        sortorder:'asc', 
        styleUI:"Bootstrap",
        height:vpanjanglayar,
        rowNum:-1,
        rowList:[5,10,15,20,'-1:All'],
        rownumbers:true,
        pager:pgrnya,
        editurl:frm,
        altRows:true,
        viewrecords:true,
        autowidth:true,
        shrinkToFit:vshrinktofit,
        toppager:true
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
    $(pgrnya+"_center").hide();

    <?php if ($akses[add]=='Y') { ?>
    jQuery(tblnya).jqGrid('navButtonAdd', topnya+"_left", {caption:"", buttonicon:'glyphicon-plus-sign', title:"Tambah data",onClickButton:tambahData});
    <?php } ?>

    jQuery(tblnya).jqGrid('navButtonAdd', topnya+"_left", {
        caption:"", buttonicon:'glyphicon-search', title:"Tampilkan baris pencarian",
        onClickButton:function (){
            this.toggleToolbar();
        }
    });
}

function formAwal(){
    $("#aded").val("");
    $("#pkey_qps_sub_plant").val("");
    $("#pkey_qps_code").val("");
    $("#qps_sub_plant").html("");
    $("#qps_code").val("");
    $("#qps_cap").val("");
    $("#qps_desc").val("");
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    $.post(frm+"?mode=detailtabel", {stat:"add"}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("add");
        $("#qps_sub_plant").html(o.sub_plan);
        $("#boxAwal").hide();
        $("#boxEdit").show(); 
    });
}

function editData(qps_sub_plant, qps_code){
    $.post(frm+"?mode=detailtabel", {stat:"edit",qps_sub_plant:qps_sub_plant,qps_code:qps_code}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#pkey_qps_sub_plant").val(o.qps_sub_plant);
        $("#pkey_qps_code").val(o.qps_code);
        $("#qps_sub_plant").html(o.sub_plan);
        $("#qps_code").val(o.qps_code);
        $("#qps_cap").val(o.qps_cap);
        $("#qps_desc").val(o.qps_desc);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function hapusData(qps_sub_plant, qps_code){
    var r = confirm("Hapus data horizontal dryer plant "+qps_sub_plant+" nomor "+qps_code+" ?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {qps_sub_plant:qps_sub_plant,qps_code:qps_code}, function(resp,stat){
            if (resp=="OK") {
                $.jgrid.gridUnload("#tblsm");
                tampilTabel("#tblsm","#pgrsm");
            } else {
                alert(resp);
            }  
        });
    } else {
        return false;
    }
}

function simpanData() {
    if($("#frEdit").valid()) {
        var mode = $("#aded").val();
        $.post(frm+"?mode="+mode, $("#frEdit").serialize(), function(resp,stat){
          if (resp=="OK") {
            if (mode == "add") {
                alert("Data berhasil disimpan");
            } else {
                alert("Perubahan data "+$("#qps_code").val()+" berhasil disimpan");
            }
            formAwal();
            $.jgrid.gridUnload("#tblsm");
            tampilTabel("#tblsm","#pgrsm");
          }else{
            alert(resp);
          }
        });
    }
}

$(document).ready(function (){
    var ubahUkuranJqGrid = function(){
        var vukur = $('#kontensm').width(); 
        if(vukur <= 550){
            $("#tblsm").setGridWidth(vukur, false);    
        } else {
            $("#tblsm").setGridWidth(vukur, true);    
        }
    };
    $('#kontensm').resize(ubahUkuranJqGrid);
    var ubahTinggiJqGrid = function(){
        var vpanjanglayar = 150;
        if($(window).height() >= 520){
            vpanjanglayar = $(window).height()-(230+$(".content-header").height());
        }
        $("#tblsm").setGridHeight(vpanjanglayar);
    };
    $('.content-header').resize(ubahTinggiJqGrid);
    
    tampilTabel("#tblsm","#pgrsm");

    $("#qps_code").afDigitOnly();
    $("#qps_cap").afNumericOnly();
    $("#qps_desc").afInputVal();

    var rulenya = {
            qps_sub_plant:{required:true},
            qps_code:{required:true,maxlength:2},
            qps_cap:{required:true,number: true},
            qps_desc:{required:true}
        };
    $("#frEdit").validate({rules:rulenya});
    
    
});
</script>