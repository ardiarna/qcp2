<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['14'];
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
                    <input type="hidden" name="pkey_qct_sub_plant" id="pkey_qct_sub_plant">
                    <input type="hidden" name="pkey_qct_code" id="pkey_qct_code">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qct_sub_plant" name="qct_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">NOMOR</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="qct_code" id="qct_code" maxlength="2">
                        </div>    
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">KAPASITAS</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="qct_cap" name="qct_cap">
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">KETERANGAN</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="qct_desc" name="qct_desc">       
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
var frm = "include/mdslip.inc.php";
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
            {label:"PLANT", name:'qct_sub_plant', index:'qct_sub_plant', width:80, stype:'select', searchoptions:{value:":;A:A;B:B;C:C"}},
            {label:"NOMOR", name:'qct_code', index:'qct_code', width:80},
            {label:"KAPASITAS", name:'qct_cap', index:'qct_cap', width:90, sorttype:"int", formatter:'integer', align:'right'},
            {label:"KETERANGAN", name:'qct_desc', index:'qct_desc', width:400},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:50, align:'center'}
        ],
        sortname:'qct_sub_plant asc,qct_code', 
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
    $("#pkey_qct_sub_plant").val("");
    $("#pkey_qct_code").val("");
    $("#qct_sub_plant").html("");
    $("#qct_code").val("");
    $("#qct_cap").val("");
    $("#qct_desc").val("");
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
     $.post(frm+"?mode=detailtabel", {stat:"add"}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("add");
        $("#qct_sub_plant").html(o.sub_plan);
        $("#boxAwal").hide();
        $("#boxEdit").show(); 
    });
}

function editData(qct_sub_plant, qct_code){
    $.post(frm+"?mode=detailtabel", {stat:"edit",qct_sub_plant:qct_sub_plant,qct_code:qct_code}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#pkey_qct_sub_plant").val(o.qct_sub_plant);
        $("#pkey_qct_code").val(o.qct_code);
        $("#qct_sub_plant").html(o.sub_plan);
        $("#qct_code").val(o.qct_code);
        $("#qct_cap").val(o.qct_cap);
        $("#qct_desc").val(o.qct_desc);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function hapusData(qct_sub_plant, qct_code){
    var r = confirm("Hapus data slip tank plant "+qct_sub_plant+" nomor "+qct_code+" ?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {qct_sub_plant:qct_sub_plant,qct_code:qct_code}, function(resp,stat){
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
                alert("Perubahan data "+$("#qct_code").val()+" berhasil disimpan");
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

    $("#qct_code").afDigitOnly();
    $("#qct_cap").afNumericOnly();
    $("#qct_desc").afInputVal();

    var rulenya = {
            qct_sub_plant:{required:true},
            qct_code:{required:true,maxlength:2},
            qct_cap:{required:true,number: true},
            qct_desc:{required:true}
        };
    $("#frEdit").validate({rules:rulenya});
    
    
});
</script>