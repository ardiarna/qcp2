<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['31'];
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
                    <input type="hidden" id="user_id" name="user_id">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">USERNAME</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="user_name" id="user_name" maxlength="20">
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">PLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" name="plan_kode" id="plan_kode"></select>
                        </div>    
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">NAMA DEPAN</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="first_name" name="first_name" maxlength="20">
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" name="sub_plan" id="sub_plan"></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">NAMA BELAKANG</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="last_name" name="last_name" maxlength="20">    
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">PASSWORD</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="password" class="form-control input-sm" id="password" name="password" maxlength="20">    
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-6" style="margin-top:3px;">
                            <button type="button" class="btn btn-default btn-sm" id="btnUserLain">
                                Samakan Hak Akses Dengan User Lain
                            </button>    
                        </div>
                    </div>
                    <div class="form-group" id="divuserlain" style="display:none;">
                        <div class="col-sm-6" style="margin-top:3px;">
                            <select class="form-control input-sm" name="userlain" id="userlain"></select>    
                        </div>
                        <div class="col-sm-1" style="margin-top:3px;">
                            <button type="button" class="btn btn-success btn-sm" onClick="salinPrivilege()">OK</button>
                        </div>
                    </div>
                    <div class="table-responsive" id="divdetail"></div>
                    <div class="form-group">
                        <div class="col-sm-12" style="margin-top:3px;text-align:center;" id="dvTombol">
                            <button type="button" class="btn btn-primary btn-sm" onClick="simpanData()">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button>
                        </div>
                    </div>
                </form>    
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var frm = "include/appuser.inc.php";
var vdropmenu = false;
var validator = "";

function tampilTabel(tblnya, pgrnya, pSubPlan = 'All') {
    var topnya = tblnya+"_toppager";
    var vshrinktofit = true;
    if($(window).width() <= 550){vshrinktofit = false;}
    jQuery(tblnya).jqGrid({
        url:frm + "?mode=urai&subplan="+pSubPlan,
        mtype:"POST",
        datatype:"json",
        colModel:[
            {label:"ID", name:'user_id', index:'user_id', width:40},
            {label:"USERNAME", name:'user_name', index:'user_name', width:150},
            {label:"NAMA DEPAN", name:'first_name', index:'first_name', width:170},
            {label:"NAMA BELAKANG", name:'last_name', index:'last_name', width:170},
            {label:"PLANT", name:'plan_nama', index:'plan_nama', width:170},
            {label:"SUBPLANT", name:'sub_plan', index:'sub_plan', width:60},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:'user_name', 
        sortorder:'asc', 
        styleUI:"Bootstrap",
        height:"auto",
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
    jQuery(tblnya).jqGrid('navButtonAdd', topnya+"_left", {caption:"", buttonicon:'glyphicon-plus-sign', title:"Tambah data", onClickButton:tambahData});
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
    $("#user_id").val("");
    $("#user_name").val("");
    $("#first_name").val("");
    $("#last_name").val("");
    $("#password").val("");
    $("#plan_kode").html("");
    $("#sub_plan").html("");
    $("#divdetail").html("");
    $("#user_id, #user_name, #first_name, #last_name, #password, #plan_kode, #sub_plan, #btnUserLain").attr('disabled',false);
    $("#dvTombol").html('<button type="button" class="btn btn-primary btn-sm" onClick="simpanData()">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button>');
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    $.post(frm+"?mode=detailtabel", {stat:"add"}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("add");
        $("#user_id").val("OTOMATIS");
        $("#plan_kode").html(o.plan_kode);
        $("#sub_plan").html(o.sub_plan);
        $("#divdetail").html(o.detailtabel);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function lihatData(user_id){
    $.post(frm+"?mode=detailtabel", {stat:"view",user_id:user_id}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#user_id").val(o.user_id);
        $("#user_name").val(o.user_name);
        $("#first_name").val(o.first_name);
        $("#last_name").val(o.last_name);
        $("#password").val(o.password);
        $("#plan_kode").html(o.plan_kode);
        $("#sub_plan").html(o.sub_plan);
        $("#divdetail").html(o.detailtabel);
        $("#user_id, #user_name, #first_name, #last_name, #password, #plan_kode, #sub_plan, #btnUserLain").attr('disabled',true);
        $("#dvTombol").html('<button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button>')
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function editData(user_id){
    $.post(frm+"?mode=detailtabel", {stat:"edit",user_id:user_id}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#user_id").val(o.user_id);
        $("#user_name").val(o.user_name);
        $("#first_name").val(o.first_name);
        $("#last_name").val(o.last_name);
        $("#password").val(o.password);
        $("#plan_kode").html(o.plan_kode);
        $("#sub_plan").html(o.sub_plan);
        $("#divdetail").html(o.detailtabel);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function hapusData(user_id, user_name){
    var r = confirm("Hapus data "+user_name+" ?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {user_id:user_id}, function(resp,stat){
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
                alert("Perubahan data "+$("#user_name").val()+" berhasil disimpan");
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

function aktifCekbox(am_id) {
    if ($("#ap_view_"+am_id).is(":checked")) {
        $("#ap_add_"+am_id+",#ap_edit_"+am_id+",#ap_del_"+am_id+",#ap_print_"+am_id+",#ap_approve_"+am_id).prop("disabled", false);
    } else {
        $("#ap_add_"+am_id+",#ap_edit_"+am_id+",#ap_del_"+am_id+",#ap_print_"+am_id+",#ap_approve_"+am_id).prop("disabled", true);
    }
}

function salinPrivilege() {
    var userlain = $("#userlain").val();
    $.post(frm+"?mode=detailtabel", {stat:"userlain",user_id:userlain}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#divdetail").html(o.detailtabel);
        $("#divuserlain").hide();  
    });   
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

    tampilTabel("#tblsm","#pgrsm");

    $("#user_name").afInputVal();
    $("#first_name").afInputVal();
    $("#last_name").afInputVal();
    $("#password").afInputVal();

    var rulenya = {
            user_name:{required:true,maxlength:20},
            first_name:{required:true,maxlength:20},
            last_name:{maxlength:20},
            password:{required:true,maxlength:20},
            plan_kode:{required:true},
            sub_plan:{required:true}
        };
    $("#frEdit").validate({rules:rulenya});

    $("#btnUserLain").click(function(){
        $.post(frm+"?mode=cbouser", function(resp,stat){
            $("#userlain").html(resp);
            $("#divuserlain").show();  
        });
    });
});
</script>