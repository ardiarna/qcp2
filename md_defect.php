<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['53'];
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
                    <input type="hidden" name="pkey_qmd_kode" id="pkey_qmd_kode">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">KODE</label>
                        <div class="col-sm-3" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="qmd_kode" id="qmd_kode" maxlength="3">
                        </div>    
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">KETERANGAN</label>
                        <div class="col-sm-10" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="qmd_nama" name="qmd_nama">       
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
var frm = "include/md_defect.inc.php";
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
            {label:"KODE", name:'qmd_kode', index:'qmd_kode', width:80},
            {label:"KETERANGAN", name:'qmd_nama', index:'qmd_nama', width:400},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:50, align:'center'}
        ],
        sortname:'qmd_nama', 
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
    $("#pkey_qmd_kode").val("");
    $("#qmd_kode").val("");
    $("#qmd_nama").val("");
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    $("#aded").val("add");
    $("#boxAwal").hide();
    $("#boxEdit").show();
}

function editData(qmd_kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",qmd_kode:qmd_kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#pkey_qmd_kode").val(o.qmd_kode);
        $("#qmd_kode").val(o.qmd_kode);
        $("#qmd_nama").val(o.qmd_nama);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function hapusData(qmd_kode){
    var r = confirm("Hapus master data defect dengan kode "+qmd_kode+" ?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {qmd_kode:qmd_kode}, function(resp,stat){
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
                alert("Perubahan data "+$("#qmd_kode").val()+" berhasil disimpan");
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

    $("#qmd_kode").afInputVal();
    $("#qmd_nama").afInputVal();

    var rulenya = {
            qmd_kode:{required:true,maxlength:3},
            qmd_nama:{required:true}
        };
    $("#frEdit").validate({rules:rulenya});
});
</script>