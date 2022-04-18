<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['114'];
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
            <div class="modal fade" id="modalUser" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header alert-info">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Daftar User</h4>
                        </div>
                        <div class="modal-body table-responsive">
                            <div class="input-group">
                                <input class="form-control input-sm" id="txt_cari" type="text" placeholder="Pencarian">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-sm btn-info" onclick="pilihUser();">Go</button>
                                </span>
                            </div>
                            <br>
                            <div id="isiMdlUser" style="display:block;max-height:500px;overflow-y:auto;-ms-overflow-style:-ms-autohiding-scrollbar;">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

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
                    <label class="col-sm-2 control-label" style="text-align:left;">USERNAME</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" name="appr_uname" id="appr_uname" readonly>
                            <div class="input-group-addon" title="Pilih">
                                <span class="glyphicon glyphicon-option-horizontal" onClick="tampilUser();"></span>
                            </div>
                        </div>
                    </div>

                    <label class="col-sm-2 control-label" style="text-align:left;">LVL JABATAN</label>
                    <div class="col-sm-4" style="margin-top:3px;">  
                        <select class="form-control input-sm" name="appr_jab" id="appr_jab">
                            <option></option>
                            <option value="1">PM</option>
                            <option value="2">KABAG</option>
                        </select>
                    </div>      
                </div>
                <div class="form-group">
                    <div class="col-sm-12" style="margin-top:3px;text-align:center;">
                    <button type="button" class="btn btn-primary btn-sm" onclick="simpanData()" id="btnSimpan">Simpan</button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="formAwal()">Batal</button>
                  </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/ic_settings_appr.inc.php";
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
            {label:"LVL JABATAN", name:'appr_jab', index:'appr_jab', width:150, align:'left', stype:'select', searchoptions:{value:":;1:PM;2:KABAG"}},
            {label:"USERNAME", name:'user_name', index:'user_name', width:150, align:'left'},
            {label:"FIRSTNAME", name:'first_name', index:'first_name', width:150, align:'left'},
            {label:"LASTNAME", name:'last_name', index:'last_name', width:150, align:'left'},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"appr_jab, user_name",
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
            appr_uname:{required:true},
            appr_jab:{required:true},
        };
    if(validator != ""){
        validator.destroy();
    }
    
    validator = $("#frEdit").validate({rules:rulenya});
    
    if($("#frEdit").valid()) { 
        $.post(frm+"?mode="+mode, $("#frEdit").serialize(), function(resp,stat){
          if (resp=="OK") {
            alert("Data berhasil disimpan");
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
    $("#appr_uname").val("");
    $("#appr_jab").val("");
    $("#boxAwal").show();
    $("#boxForm").hide();
}


function tambahData() {
    $("#aded").val("add");
    $("#boxForm").show(); 
    $("#boxAwal").hide(); 
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



function tampilUser() {
    $("#txt_cari").val('');
    pilihUser();
    $("#modalUser").modal('show');    
}

function pilihUser() {
    $.post(frm+"?mode=pilihuser", {txt_cari:$("#txt_cari").val()}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#isiMdlUser").html(o.out);
    });
}

function setUser(nmm) {
    $("#appr_uname").val(nmm);
    $("#modalUser").modal('hide');
}

</script>