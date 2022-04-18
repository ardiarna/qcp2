<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['48'];
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
            <div class="box-body" id="boxEdit" style="display:none;">
                <form class="form-horizontal" id="frEdit">
                    <input type="hidden" name="aded" id="aded">
                    <input type="hidden" name="pkey_qab_nama" id="pkey_qab_nama">
                    <input type="hidden" name="pkey_qab_nomor" id="pkey_qab_nomor">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">Nama Mesin</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <div class="input-group" id="dvqab_nama">
                                <input class="form-control input-sm" type="text" name="qab_nama" id="qab_nama">
                                <div class="input-group-addon" title="Pilih nama mesin yang sudah ada">
                                    <span class=" glyphicon glyphicon-chevron-down" onclick="ubahQabNama('sel')"></span>
                                </div>
                            </div>
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">Nomor Mesin</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="qab_nomor" name="qab_nomor">       
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
var frm = "include/ab_daftar.inc.php";
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
            {label:"Nama Mesin", name:'qab_nama', index:'qab_nama', width:80},
            {label:"Nomor Mesin", name:'qab_nomor', index:'qab_nomor', width:400},
            {label:"Kontrol", name:'kontrol', index:'kontrol', width:50, align:'center'}
        ],
        sortname:'qab_nama asc,qab_nomor', 
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
    $("#pkey_qab_nama").val("");
    $("#pkey_qab_nomor").val("");
    $("#qab_nama").val("");
    $("#qab_nomor").val("");
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    $.post(frm+"?mode=detailtabel", {stat:"add"}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("add");
        $("#boxAwal").hide();
        $("#boxEdit").show(); 
    });
}

function editData(qab_nama, qab_nomor){
    $.post(frm+"?mode=detailtabel", {stat:"edit",qab_nama:qab_nama,qab_nomor:qab_nomor}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#pkey_qab_nama").val(o.qab_nama);
        $("#pkey_qab_nomor").val(o.qab_nomor);
        $("#qab_nama").val(o.qab_nama);
        $("#qab_nomor").val(o.qab_nomor);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function hapusData(qab_nama, qab_nomor){
    var r = confirm("Hapus data alat berat "+qab_nama+" nomor "+qab_nomor+" ?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {qab_nama:qab_nama,qab_nomor:qab_nomor}, function(resp,stat){
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
                alert("Perubahan data "+$("#qab_nama").val()+" berhasil disimpan");
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

function ubahQabNama(tipe) {
    if(tipe == 'sel') {
        $("#dvqab_nama").html('<select class="form-control input-sm" id="qab_nama" name="qab_nama"></select><div class="input-group-addon" title="Input nama mesin baru"><span class=" glyphicon glyphicon-chevron-up" onclick="ubahQabNama(\'inp\')"></span></div>');
        $.post(frm+"?mode=cboabnama", function(resp,stat){
            $("#qab_nama").html(resp);  
        });
    } else {
        $("#dvqab_nama").html('<input class="form-control input-sm" type="text" name="qab_nama" id="qab_nama"><div class="input-group-addon" title="Pilih nama mesin yang sudah ada"><span class=" glyphicon glyphicon-chevron-down" onclick="ubahQabNama(\'sel\')"></span></div>');
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

    $("#qab_nama").afInputVal();
    $("#qab_nomor").afDigitOnly();
    
    var rulenya = {
            qab_nama:{required:true},
            qab_nomor:{required:true}
            
        };
    $("#frEdit").validate({rules:rulenya});
});
</script>