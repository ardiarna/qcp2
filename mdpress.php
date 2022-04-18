<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['15'];
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
                    <input type="hidden" name="pkey_qpp_sub_plant" id="pkey_qpp_sub_plant">
                    <input type="hidden" name="pkey_qpp_code" id="pkey_qpp_code">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qpp_sub_plant" name="qpp_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">NOMOR</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="qpp_code" id="qpp_code" maxlength="2">
                        </div>    
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">KAPASITAS</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="qpp_cap" name="qpp_cap">
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">KETERANGAN</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="qpp_desc" name="qpp_desc">       
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
var frm = "include/mdpress.inc.php";
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
            {label:"PLANT", name:'qpp_sub_plant', index:'qpp_sub_plant', width:80, stype:'select', searchoptions:{value:":;A:A;B:B;C:C"}},
            {label:"NOMOR", name:'qpp_code', index:'qpp_code', width:80},
            {label:"KAPASITAS", name:'qpp_cap', index:'qpp_cap', width:90, sorttype:"int", formatter:'integer', align:'right'},
            {label:"KETERANGAN", name:'qpp_desc', index:'qpp_desc', width:400},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:50, align:'center'}
        ],
        sortname:'qpp_sub_plant asc,qpp_code', 
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
        toppager:true,
        subGrid:true,
        subGridRowExpanded:function(parentRowID, parentRowKey) {
            var $self = $(this);
            var vsubplan = $self.jqGrid("getCell", parentRowKey, "qpp_sub_plant");
            var vkode = $self.jqGrid("getCell", parentRowKey, "qpp_code");
            tampilSubTabel(parentRowID, parentRowKey, vsubplan, vkode);
        }
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

function tampilSubTabel(parentRowID, parentRowKey, pSubPlan, pKode) {
    var childGridID = parentRowID + "_table";
    $('#' + parentRowID).append('<table id=' + childGridID + '></table>');
    jQuery("#" + childGridID).jqGrid({
        url:frm + "?mode=suburai",
        mtype:"POST",
        postData:{'qpp_sub_plant':pSubPlan,'qpp_code':pKode},
        datatype:"json",
        colModel:[
            {label:'PLANT', name:'qpm_sub_plant', width:100, hidden:true},
            {label:'PRESS NO.', name:'qpm_press_code', width:100, hidden:true},
            {label:'MOULDSET NO.', name:'qpm_code', width:200},
            {label:'KETERANGAN', name:'qpm_desc', width:200}
        ],
        styleUI:"Bootstrap",
        loadonce:true,
        width:'auto',
        height:'auto',
        rowNum:-1,
   });
}

function formAwal(){
    $("#aded").val("");
    $("#pkey_qpp_sub_plant").val("");
    $("#pkey_qpp_code").val("");
    $("#qpp_sub_plant").html("");
    $("#qpp_code").val("");
    $("#qpp_cap").val("");
    $("#qpp_desc").val("");
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
     $.post(frm+"?mode=detailtabel", {stat:"add"}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("add");
        $("#qpp_sub_plant").html(o.sub_plan);
        $("#boxAwal").hide();
        $("#boxEdit").show(); 
    });
}

function editData(qpp_sub_plant, qpp_code){
    $.post(frm+"?mode=detailtabel", {stat:"edit",qpp_sub_plant:qpp_sub_plant,qpp_code:qpp_code}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#pkey_qpp_sub_plant").val(o.qpp_sub_plant);
        $("#pkey_qpp_code").val(o.qpp_code);
        $("#qpp_sub_plant").html(o.sub_plan);
        $("#qpp_code").val(o.qpp_code);
        $("#qpp_cap").val(o.qpp_cap);
        $("#qpp_desc").val(o.qpp_desc);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function hapusData(qpp_sub_plant, qpp_code){
    var r = confirm("Hapus data press plant "+qpp_sub_plant+" nomor "+qpp_code+" ?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {qpp_sub_plant:qpp_sub_plant,qpp_code:qpp_code}, function(resp,stat){
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
                alert("Perubahan data "+$("#qpp_code").val()+" berhasil disimpan");
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

    $("#qpp_code").afDigitOnly();
    $("#qpp_cap").afNumericOnly();
    $("#qpp_desc").afInputVal();

    var rulenya = {
            qpp_sub_plant:{required:true},
            qpp_code:{required:true,maxlength:2},
            qpp_cap:{required:true,number: true},
            qpp_desc:{required:true}
        };
    $("#frEdit").validate({rules:rulenya});
    
    
});
</script>