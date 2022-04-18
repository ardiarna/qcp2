<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['16'];
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
                    <input type="hidden" name="pkey_qpm_sub_plant" id="pkey_qpm_sub_plant">
                    <input type="hidden" name="pkey_qpm_press_code" id="pkey_qpm_press_code">
                    <input type="hidden" name="pkey_qpm_code" id="pkey_qpm_code">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qpm_sub_plant" name="qpm_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">PRESS NO.</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" name="qpm_press_code" id="qpm_press_code"></select>
                        </div>    
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">MOULDSET NO.</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="qpm_code" name="qpm_code" maxlength="2">
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">KETERANGAN</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="qpm_desc" name="qpm_desc">       
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
var frm = "include/mdmp.inc.php";
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
            {label:"PLANT", name:'qpm_sub_plant', index:'qpm_sub_plant', width:80, stype:'select', searchoptions:{value:":;A:A;B:B;C:C"}},
            {label:"PRESS NO.", name:'qpm_press_code', index:'qpm_press_code', width:80},
            {label:"MOULDSET NO.", name:'qpm_code', index:'qpm_code', width:90},
            {label:"KETERANGAN", name:'qpm_desc', index:'qpm_desc', width:400},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:50, align:'center'}
        ],
        sortname:'qpm_sub_plant asc,qpm_press_code', 
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
    $("#pkey_qpm_sub_plant").val("");
    $("#pkey_qpm_press_code").val("");
    $("#pkey_qpm_code").val("");
    $("#qpm_sub_plant").html("");
    $("#qpm_press_code").html("");
    $("#qpm_code").val("");
    $("#qpm_desc").val("");
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    $.post(frm+"?mode=detailtabel", {stat:"add"}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("add");
        $("#qpm_sub_plant").html(o.sub_plan);
        $("#boxAwal").hide();
        $("#boxEdit").show(); 
    });
}

function editData(qpm_sub_plant, qpm_press_code, qpm_code){
    $.post(frm+"?mode=detailtabel", {stat:"edit",qpm_sub_plant:qpm_sub_plant,qpm_press_code:qpm_press_code,qpm_code:qpm_code}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#pkey_qpm_sub_plant").val(o.qpm_sub_plant);
        $("#pkey_qpm_press_code").val(o.qpm_press_code);
        $("#pkey_qpm_code").val(o.qpm_code);
        $("#qpm_sub_plant").html(o.sub_plan);
        $("#qpm_press_code").html(o.qpm_press_codehtml);
        $("#qpm_code").val(o.qpm_code);
        $("#qpm_desc").val(o.qpm_desc);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function hapusData(qpm_sub_plant, qpm_press_code, qpm_code){
    var r = confirm("Hapus data mouldset plant "+qpm_sub_plant+" nomor press "+qpm_press_code+" nomor "+qpm_code+" ?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {qpm_sub_plant:qpm_sub_plant,qpm_press_code:qpm_press_code,qpm_code:qpm_code}, function(resp,stat){
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
                alert("Perubahan data "+$("#qpm_press_code").val()+" berhasil disimpan");
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

    $('#qpm_sub_plant').change(function(){
        var subplan = this.value;
        $.post(frm+"?mode=cbopress", {subplan:subplan}, function(resp,stat){
            $("#qpm_press_code").html(resp);  
        });
    });

    $("#qpm_code").afDigitOnly();
    $("#qpm_desc").afInputVal();

    var rulenya = {
            qpm_sub_plant:{required:true},
            qpm_press_code:{required:true},
            qpm_code:{required:true,maxlength:2},
            qpm_desc:{required:true}
        };
    $("#frEdit").validate({rules:rulenya});
    
    
});
</script>