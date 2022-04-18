<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['35'];
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
                    <input type="hidden" name="pkey_qlu_plant_code" id="pkey_qlu_plant_code">
                    <input type="hidden" name="pkey_qlu_kode" id="pkey_qlu_kode">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qlu_plant_code" name="qlu_plant_code"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">NOMOR</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="qlu_kode" id="qlu_kode" maxlength="2">
                        </div>    
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">KETERANGAN</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="qlu_desc" name="qlu_desc">       
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
var frm = "include/mdline.inc.php";
var vdropmenu = false;
var validator = "";

function tampilTabel(tblnya, pgrnya, pSubPlan = 'All') {
    var topnya = tblnya+"_toppager";
    var vshrinktofit = true;
    var vpanjanglayar = 150;
    if($(window).height() >= 520){vpanjanglayar = $(window).height()-(230+$(".content-header").height());}
    if($(window).width() <= 550){vshrinktofit = false;}
    jQuery(tblnya).jqGrid({
        url:frm + "?mode=urai",
        mtype:"POST",
        datatype:"json",
        colModel:[
            {label:"SUBPLANT", name:'qlu_plant_code', index:'qlu_plant_code', width:80, editable:true, editrules:{required:true}},
            {label:"NOMOR", name:'qlu_kode', index:'qlu_kode', width:80, editable:true, editrules:{required:true}},
            {label:"KETERANGAN", name:'qlu_desc', index:'qlu_desc', width:400, editable:true},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:50, align:'center'}
        ],
        sortname:'qlu_plant_code asc,qlu_kode', 
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
    $("#pkey_qlu_plant_code").val("");
    $("#pkey_qlu_kode").val("");
    $("#qlu_plant_code").html("");
    $("#qlu_kode").val("");
    $("#qlu_desc").val("");
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
     $.post(frm+"?mode=detailtabel", {stat:"add"}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("add");
        $("#qlu_plant_code").html(o.sub_plan);
        $("#boxAwal").hide();
        $("#boxEdit").show(); 
    });
}

function editData(qlu_plant_code, qlu_kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",qlu_plant_code:qlu_plant_code,qlu_kode:qlu_kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#pkey_qlu_plant_code").val(o.qlu_plant_code);
        $("#pkey_qlu_kode").val(o.qlu_kode);
        $("#qlu_plant_code").html(o.sub_plan);
        $("#qlu_kode").val(o.qlu_kode);
        $("#qlu_desc").val(o.qlu_desc);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function hapusData(qlu_plant_code, qlu_kode){
    var r = confirm("Hapus data box plant "+qlu_plant_code+" nomor "+qlu_kode+" ?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {qlu_plant_code:qlu_plant_code,qlu_kode:qlu_kode}, function(resp,stat){
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
                alert("Perubahan data "+$("#qlu_kode").val()+" berhasil disimpan");
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

    $("#qlu_kode").afDigitOnly();
    $("#qlu_desc").afInputVal();

    var rulenya = {
            qlu_plant_code:{required:true},
            qlu_kode:{required:true,maxlength:2},
            qlu_desc:{required:true}
        };
    $("#frEdit").validate({rules:rulenya});
});
</script>