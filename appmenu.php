<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['30'];
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
                <div id="kontensm" class="table-responsive"></div>
            </div>
            <div class="box-body" id="boxEdit" style="display: none;">
                <form class="form-horizontal" id="frEdit">
                    <input type="hidden" name="aded" id="aded">
                    <input type="hidden" name="am_id" id="am_id">
                    <input type="hidden" name="am_parent" id="am_parent">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">LABEL</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input type="text" class="form-control input-sm" id="am_label" name="am_label">
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">PARENT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="parent_label" name="parent_label" readonly>
                        </div>    
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SORT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="am_sort" name="am_sort">       
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">LINK</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="am_link" name="am_link">       
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">ICON</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="am_class" name="am_class">
                                <option></option>
                                <option>fa fa-building</option>
                                <option>fa fa-circle-o</option>
                                <option>fa fa-dashboard</option>
                                <option>fa fa-file-text</option>
                                <option>fa fa-folder</option>
                                <option>fa fa-gear</option>
                                <option>fa fa-user</option>
                            </select>       
                        </div>

                        <label class="col-sm-2 control-label" style="text-align:left;">ACTIVE</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="am_stats" name="am_stats">
                                <option value="Y">Y</option>
                                <option value="N">N</option>
                            </select>       
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-12" style="margin-top:3px;text-align:center;">
                            <button type="button" class="btn btn-primary btn-sm" onClick="simpanData()">Save</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Cancel</button>
                        </div>
                    </div>
                </form>    
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var frm = "include/appmenu.inc.php";
var vdropmenu = false;
var validator = "";

function tampilTabel() {
    $.post(frm+"?mode=urai", function(resp,stat){
        var o = JSON.parse(resp);
        $("#kontensm").html(o.detailtabel);    
    });
}

function formAwal(){
    $("#aded").val("");
    $("#am_id").val("");
    $("#am_label").val("");
    $("#am_parent").html("");
    $("#am_sort").val("");
    $("#am_link").val("");
    $("#am_class").val("");
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(am_id){
    $.post(frm+"?mode=detailtabel", {stat:"add",am_id:am_id}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("add");
        $("#am_id").val("OTOMATIS");
        $("#am_parent").val(o.am_id);
        $("#parent_label").val(o.am_label);
        $("#am_sort").val(o.am_sort);
        $("#am_stats").val('Y');
        $("#boxAwal").hide();
        $("#boxEdit").show(); 
    });
}

function editData(am_id){
    $.post(frm+"?mode=detailtabel", {stat:"edit",am_id:am_id}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#am_id").val(o.am_id);
        $("#am_parent").val(o.am_parent);
        $("#am_label").val(o.am_label);
        $("#am_sort").val(o.am_sort);
        $("#am_link").val(o.am_link);
        $("#am_class").val(o.am_class);
        $("#am_stats").val(o.am_stats);
        $("#parent_label").val(o.parent_label);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function hapusData(am_id, am_label){
    var r = confirm("Hapus data menu "+am_id+". "+am_label+" ?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {am_id:am_id}, function(resp,stat){
            if (resp=="OK") {
                tampilTabel();
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
                alert("Menu berhasil disimpan");
            } else {
                alert("Perubahan menu "+$("#am_label").val()+" berhasil disimpan");
            }
            formAwal();
            tampilTabel();
          }else{
            alert(resp);
          }
        });
    }
}

function naikSort(am_id, am_sort, am_parent) {
    $.post(frm+"?mode=naiksort", {am_id:am_id,am_sort:am_sort,am_parent:am_parent}, function(resp,stat){
        if (resp=="OK") {
            tampilTabel();
        } else {
            alert(resp);
        }  
    });
}

function turunSort(am_id, am_sort, am_parent) {
    $.post(frm+"?mode=turunsort", {am_id:am_id,am_sort:am_sort,am_parent:am_parent}, function(resp,stat){
        if (resp=="OK") {
            tampilTabel();
        } else {
            alert(resp);
        }  
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

    tampilTabel();

    var rulenya = {
            am_label:{required:true},
            am_sort:{required:true,digits:true},
            am_class:{required:true},
        };
    $("#frEdit").validate({rules:rulenya});
    
    

});
</script>