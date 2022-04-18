﻿<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['122'];
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
                <form class="form-horizontal" id="frCari">
                </form>
                <div id="kontensm">
                    <table id="tblsm"></table>
                    <div id="pgrsm"></div>        
                </div>
            </div>
            <div class="box-body" id="boxEdit" style="display: none;">
                <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()" title="Kembali">
                    <i class="fa fa-reply"></i>
                </button>
                <button type="button" class="btn btn-success btn-sm" onClick="copyData()" title="Copy Data">
                    <i class="fa fa-copy"></i>
                </button>
                <br>
                <br>
                <form id="frm1" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-1 control-label" style="text-align:left;">KODE</label>
                        <div class="col-sm-5" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="ic_kd_material" id="ic_kd_material" readonly> 
                        </div>
                        <label class="col-sm-1 control-label" style="text-align:left;">NAMA</label>
                        <div class="col-sm-5" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="ic_nm_material" id="ic_nm_material" readonly>
                        </div>    
                    </div>
                    <div class="form-group" id="divCopymat" style="display:none;">
                        <div class="col-sm-6" style="margin-top:3px;">
                            <select class="form-control input-sm" name="kodefrom" id="kodefrom"></select>    
                        </div>
                        <div class="col-sm-1" style="margin-top:3px;">
                            <button type="button" class="btn btn-success btn-sm" onClick="salinStd()">OK</button>
                        </div>
                    </div>
                    <div class="table-responsive" id="divdetail"></div>
                </form>    
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var frm = "include/ic_md_spesifikasimutu.inc.php";
var vdropmenu = false;
var validator = "";


function tampilTabel(tblnya, pgrnya, pTanggal, pSubPlan = 'All'){
    var topnya = tblnya+"_toppager";
    var vshrinktofit = true;
    var vpanjanglayar = 150;
    if($(window).height() >= 520){vpanjanglayar = $(window).height()-(250+$("#frCari").height()+$(".content-header").height());}
    if($(window).width() <= 800){vshrinktofit = false;}
    jQuery(tblnya).jqGrid({
        url:frm + "?mode=urai&subplan="+pSubPlan,
        mtype:"POST",
        datatype:"json",
        colModel:[
            {label:"KODE MATERIAL", name:'item_kode', index:'item_kode', width:70, align:'center'},
            {label:"NAMA MATERIAL", name:'item_nama', index:'item_nama'},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"item_kode",
        sortorder:'asc', 
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


    // <?php if ($akses[add]=='Y') { ?>
    // jQuery(tblnya).jqGrid('navButtonAdd', topnya+"_left", {caption:"", buttonicon:'glyphicon-plus-sign', title:"Tambah data", onClickButton:tambahData});
    // <?php } ?>

    jQuery(tblnya).jqGrid('navButtonAdd', topnya+"_left", {
        caption:"", buttonicon:'glyphicon-search', title:"Tampilkan baris pencarian",
        onClickButton:function () {
            this.toggleToolbar();
       }
    });

    jQuery(tblnya).jqGrid('navButtonAdd', topnya+"_left", {caption:"", buttonicon:'glyphicon-refresh', title:"Reload data", onClickButton:reloadData});


    $(pgrnya+"_center").hide();
}


function formAwal(){
    $("#boxAwal").show();
    $("#boxEdit").hide();
    $("#divCopymat").hide();  
}

function tambahData(){
    $("#ic_id").val("OTOMATIS");
    $("#boxAwal").hide();
    $("#boxEdit").show(); 
}

function copyData(){
    var kode = $("#ic_kd_material").val();
    $.post(frm+"?mode=listcopy", {kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#kodefrom").html(o.list);
        $("#divCopymat").show();  
    });
}

function salinStd() {
    var kodefrom = $("#kodefrom").val();
    $.post(frm+"?mode=detailtabel", {kode:kodefrom}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#divdetail").html(o.detailtabel);
        $("#divCopymat").hide();  
    });   
}

function DetilData(kd,nm){
    $.post(frm+"?mode=detailtabel", {kode:kd}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#boxAwal").hide();
        $("#boxEdit").show();
        $("#ic_kd_material").val(kd);
        $("#ic_nm_material").val(nm);
        $("#divdetail").html(o.detailtabel);
    });
}

function simpanData() {
    var rulenya = {
            ic_kd_material:{required:true},
            ic_nm_material:{required:true},
        };
    
    if(validator != ""){
        validator.destroy();
    }
    
    validator = $("#frm1").validate({rules:rulenya});

    if($("#frm1").valid()) { 
        $.post(frm+"?mode=simpan", $("#frm1").serialize(), function(resp,stat){
            alert(resp);
            $('#frmmdl').modal('hide');
        });
    }   
}


$(document).ready(function (){
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
    $('.content-header').resize(ubahTinggiJqGrid);

    tampilTabel("#tblsm","#pgrsm");
    
    $('.select2').select2();


});


function reloadData(){
    $.jgrid.gridUnload("#tblsm");
    tampilTabel("#tblsm","#pgrsm");
}
</script>

