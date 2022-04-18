<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['108'];
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
            <div class="box-body" id="boxForm" style="display: none;">
                <form class="form-horizontal" id="frEdit">
                    <input type="hidden" name="aded" id="aded" readonly>
                <div class="form-group">
                    <label class="col-sm-2 control-label" style="text-align:left;">KODE SUB KONTRAKTOR</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <input class="form-control input-sm" type="text" id="subkon_id" name="subkon_id" readonly>
                    </div>
                    <label class="col-sm-2 control-label" style="text-align:left;">NAMA SUB KONTRAKTOR</label>
                    <div class="col-sm-4" style="margin-top:3px;">  
                        <input class="form-control input-sm" type="text" name="subkon_name" id="subkon_name">
                    </div>      
                </div>

                <div class="form-group" style="display: none;">
                    <label class="col-sm-2 control-label" style="text-align:left;">KETERANGAN</label>
                    <div class="col-sm-10" style="margin-top:3px;">
                        <textarea class="form-control input-sm" name="subkon_desc" id="subkon_desc"></textarea>
                    </div>
                </div>
                <div class="form-group" id="buttonform"></div>
                </form>
            </div>
        </div>
    </div>
</div> 


<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Impor Supplier dari Armasi</h4>
            </div>
            <div class="modal-body table-responsive" id="isiModal" style="display:block;max-height:500px;overflow-y:auto;-ms-overflow-style:-ms-autohiding-scrollbar;">
                <div class="overlay" id="divloading" style="display: none;">
                    <i class="fa fa-refresh fa-spin"></i> Mohon tunggu...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="btnclose" style="display: none;">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var frm = "include/md_subkon.inc.php";
var vdropmenu = false;
var validator = "";

function tampilTabel(tblnya, pgrnya, pTanggal, pSubPlan = 'All'){
    var topnya = tblnya+"_toppager";
    var vshrinktofit = true;
    var vpanjanglayar = 150;
    if($(window).height() >= 520){vpanjanglayar = $(window).height()-(230+$(".content-header").height());}
    if($(window).width() <= 800){vshrinktofit = false;}
    jQuery(tblnya).jqGrid({
        url:frm + "?mode=urai",
        mtype:"POST",
        datatype:"json",
        colModel:[
            {label:"KODE", name:'subkon_id', index:'subkon_id', align:'center' , width:50},
            {label:"NAMA", name:'subkon_name', index:'subkon_name', width:150, align:'left'},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"subkon_id",
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

    <?php if ($akses[add]=='Y') { ?>
    jQuery(tblnya).jqGrid('navButtonAdd', topnya+"_left", {caption:"", buttonicon:'glyphicon-plus-sign', title:"Tambah data", onClickButton:tambahData});

    // jQuery(tblnya).jqGrid('navButtonAdd', topnya+"_left", {caption:"", buttonicon:'glyphicon-import', title:"Impor Supplier dari Armasi",onClickButton:imporData});
    
    <?php } ?>

    jQuery(tblnya).jqGrid('navButtonAdd', topnya+"_left", {
        caption:"", buttonicon:'glyphicon-search', title:"Tampilkan baris pencarian",
        onClickButton:function () {
            this.toggleToolbar();
       }
    });

    $(pgrnya+"_center").hide();
}




function simpanData() {
    var mode = $("#aded").val();

    var rulenya = {
            subkon_id:{required:true},
            subkon_name:{required:true},
        };
    if(validator != ""){
        validator.destroy();
    }
    
    validator = $("#frEdit").validate({rules:rulenya});
    
    if($("#frEdit").valid()) { 
        $.post(frm+"?mode="+mode, $("#frEdit").serialize(), function(resp,stat){
          if (resp=="OK") {
            if (mode == "add") {
                alert("Data berhasil disimpan");
            } else {
                alert("Perubahan data "+$("#subkon_id").val()+" berhasil disimpan");
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

function formAwal(){
    $("#aded").val("");
    $("#subkon_id").val("");
    $("#subkon_name").val("");
    $("#subkon_desc").val("");
    $("#boxAwal").show();
    $("#boxForm").hide();
    $("#subkon_name, #subkon_desc").attr('readonly',false);
}




function imporData() {
    $("#btnclose").hide(); 
    swal({
          title: "Confirm!",
          text: "Impor Data dari Armasi ?",
          icon: "warning",
          buttons: true,
          dangerMode: true,
        })
        .then((willOk) => {
          if (willOk) {
            $("#myModal").modal('show'); 
            $("#divloading").show(); 

            $.post(frm+"?mode=importdata", {}, function(resp,stat){
                var o = JSON.parse(resp);

                $("#divloading").hide(); 
                $("#btnclose").show(); 
                $("#isiModal").html(o.hasil);

                $.jgrid.gridUnload("#tblsm");
                tampilTabel("#tblsm","#pgrsm");
            });
          }
    });
}

function tambahData() {
    $("#aded").val("add");
    $("#subkon_id").val("OTOMATIS");
    $("#boxForm").show(); 
    $("#boxAwal").hide(); 
    $.post(frm+"?mode=detailtabel", {stat:"add"}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#buttonform").html(o.detailtabel);
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#subkon_id").val(o.subkon_id);
        $("#subkon_name").val(o.subkon_name);
        $("#subkon_desc").val(o.subkon_desc);
        $("#buttonform").html(o.detailtabel);
        $("#boxForm").show(); 
        $("#boxAwal").hide(); 
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("view");
        $("#subkon_id").val(o.subkon_id);
        $("#subkon_name").val(o.subkon_name);
        $("#subkon_desc").val(o.subkon_desc);
        $("#buttonform").html(o.detailtabel);
        $("#boxForm").show(); 
        $("#boxAwal").hide(); 
        $("#subkon_id, #subkon_name, #subkon_desc").attr('readonly',true);
    });
}

function hapusData(kode){
    var r = confirm("Hapus data id "+kode+"?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {kode:kode}, function(resp,stat){
            if(resp=="OK") {
                formAwal();
                $.jgrid.gridUnload("#tblsm");
                tampilTabel("#tblsm","#pgrsm");
            }else{
                alert(resp);
            }  
        });
    } else {
        return false;
    }
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
            vpanjanglayar = $(window).height()-(210+$(".content-header").height());
        }
        if($(window).width() <= 800){vshrinktofit = false;}
        $("#tblsm").setGridHeight(vpanjanglayar);
    };
    $('#boxAwal').resize(ubahTinggiJqGrid);
    

    tampilTabel("#tblsm","#pgrsm");

});

</script>