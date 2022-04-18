<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['126'];
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
            <div class="modal fade" id="modalform" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header alert-info">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title" ><span id="titleform"></span></h4>
                        </div>
                        <div class="modal-body table-responsive" id="isiform"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary btn-sm" onClick="simpanForm()">Simpan</button> 
                            <button type="button" class="btn btn-warning btn-sm" data-dismiss="modal">Batal</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modalHeader" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header alert-info">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title" ><span id="titleheader"></span></h4>
                        </div>
                        <div class="modal-body table-responsive">
                           <form class="form-horizontal" id="frEdit">
                                <input type="hidden" name="aded" id="aded" readonly>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" style="text-align:left;">ID</label>
                                    <div class="col-sm-4" style="margin-top:3px;">
                                        <input class="form-control input-sm" type="text" name="idh" id="idh" value="OTOMATIS" readonly>
                                    </div>
                                    <label class="col-sm-2 control-label" style="text-align:left;">TAHUN</label>
                                    <div class="col-sm-4" style="margin-top:3px;">
                                        <input class="form-control input-sm" type="text" name="tahun" id="tahun" onkeyup="hanyanumerik(this.id, this.value)"> 
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" style="text-align:left;">DEPARTEMEN</label>
                                    <div class="col-sm-4" style="margin-top:3px;">
                                        <input class="form-control input-sm" type="text" name="departemen" id="departemen">
                                    </div>
                                    <label class="col-sm-2 control-label" style="text-align:left;">DIVISI</label>
                                    <div class="col-sm-4" style="margin-top:3px;">
                                        <input class="form-control input-sm" type="text" name="divisi" id="divisi">
                                    </div>
                                </div>
                            </form>    
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary btn-sm" onClick="simpanData()">Simpan</button> 
                            <button type="button" class="btn btn-warning btn-sm" data-dismiss="modal">Batal</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body" id="boxAwal">
                <form class="form-horizontal" id="frCari">
                    <div class="col-md-12"></div>
                </form>
                <div id="kontensm">
                    <table id="tblsm"></table>
                    <div id="pgrsm"></div>        
                </div>
            </div>
            <div class="box-body" id="boxDetail" style="display: none;">
                <div class="table-responsive" id="divdetail"></div>
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/kpi_parameter.inc.php";
var vdropmenu = false;
var validator = "";

function tampilTabel(tblnya, pgrnya){
    var topnya = tblnya+"_toppager";
    var vshrinktofit = true;
    var vpanjanglayar = 150;
    if($(window).height() >= 520){vpanjanglayar = $(window).height()-(250+$("#frCari").height()+$(".content-header").height());}
    if($(window).width() <= 800){vshrinktofit = false;}
    jQuery(tblnya).jqGrid({
        url:frm + "?mode=urai",
        mtype:"POST",
        datatype:"json",
        colModel:[
            {label:"DEPARTEMEN", name:'nmdept', index:'nmdept', width:100},
            {label:"DIVISI", name:'nmdivisi', index:'nmdivisi', width:100},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"nmdept asc, nmdivisi",
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
            tahun:{required:true},
            departemen:{required:true},
            divisi:{required:true},
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
                $('#modalHeader').modal('hide');
            } else {
                alert("Perubahan data berhasil disimpan");
                $('#modalHeader').modal('hide');
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
    $("#idh").val("OTOMATIS");
    $("#tahun").val("");
    $("#departemen").val("");
    $("#divisi").val("");
    $("#boxAwal").show();
    $("#boxDetail").hide();    
}

function tambahData() {
    $("#aded").val("add");
    $("#tahun").val("");
    $("#departemen").val("");
    $("#divisi").val("");
    $("#titleheader").html('FORM KPI PARAMETER | TAMBAH');    
    $("#modalHeader").modal('show');  
    $("#idh").val('OTOMATIS');  
}

function editData(kode){
    $.post(frm+"?mode=getdata", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#titleheader").html('FORM KPI PARAMETER | UBAH');    
        $("#modalHeader").modal('show');  
        $("#idh").val(o.idh);
        $("#tahun").val(o.tahun);
        $("#divisi").val(o.divisi);
        $("#departemen").val(o.departemen);
    });
}

function hapusData(kode){
    var r = confirm("Hapus data id : "+kode+"?\nDetail data ikut dihapus.");
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



function detailData($iddept,$iddivisi){
    $.post(frm+"?mode=detailtabel", {iddept:$iddept,iddivisi:$iddivisi}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#divdetail").html(o.detailtabel);
        $("#boxAwal").hide();
        $("#boxDetail").show();      
    });
}


function FormData($stats,$iddept,$iddivisi,$id){
    $.post(frm+"?mode=formdata", {stat:$stats,iddept:$iddept,iddivisi:$iddivisi,id:$id}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#titleform").html(o.titleform);    
        $("#isiform").html(o.isiform);    
        $("#modalform").modal('show');  
    });
}


function simpanForm() {
    var $iddept    = $("#kpi_dept").val();
    var $iddivisi  = $("#kpi_divisi").val();

    var rulenya = {
        aksidata:{required:true},
        kpi_dept:{required:true},
        kpi_divisi:{required:true},
        kpi_parent:{required:true},
        kpi_cat:{required:true},
        kpi_desc:{required:true},
    };
    if(validator != ""){
        validator.destroy();
    }
    
    validator = $("#frm1").validate({rules:rulenya});
    
    if($("#frm1").valid()) { 
        $.post(frm+"?mode=simpanform", $("#frm1").serialize(), function(resp,stat){
          if (resp=="OK") {
            detailData($iddept,$iddivisi);
            alert("Data berhasil disimpan");
            $('#modalform').modal('hide');
          }else{
            alert(resp);
          }
        });
    }   
}

function hapusData2($iddept,$iddivisi,$id){
    var r = confirm("Yakin hapus data ?\nChild Data juga dihapus!");
    if (r == true) {
        $.post(frm+"?mode=hapus2", {id:$id}, function(resp,stat){
            if(resp=="OK") {
                detailData($iddept,$iddivisi);
            }else{
                alert(resp);
            }  
        });
    } else {
        return false;
    }
}



function hideGrup(grupke) {
    $("#trgrup_ke_"+grupke).toggle();
}


function hanyanumerik(id, value) {
    if (/([^.0123456789]|)/g.test(value)) { 
        $("#"+id).val(value.replace(/([^.0123456789])/g, ''));
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
            vpanjanglayar = $(window).height()-(250+$("#frCari").height()+$(".content-header").height());
        }
        $("#tblsm").setGridHeight(vpanjanglayar);
    };

    tampilTabel("#tblsm","#pgrsm");
    
    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm");
    });

});
</script>