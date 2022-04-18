<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['65'];
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
            <div class="modal fade" id="modalMotif" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header alert-info">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Daftar Motif</h4>
                        </div>
                        <input type="hidden" name="idx_motif" id="idx_motif">
                        <div class="modal-body table-responsive">
                            <div class="input-group">
                                <input class="form-control input-sm" id="txt_cari" type="text" placeholder="Masukkan code motif">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-sm btn-info" onclick="pilihMotif();">Go</button>
                                </span>
                            </div>
                            <br>
                            <div id="isiMdlMotif" style="display:block;max-height:500px;overflow-y:auto;-ms-overflow-style:-ms-autohiding-scrollbar;">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="myModal" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header alert-warning">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title" id="jdlModal">Pesan</h4>
                        </div>
                        <div class="modal-body table-responsive" id="isiModal">...Sedang Memuat...</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body" id="boxAwal">
                <form class="form-horizontal" id="frCari">
                    <div class="form-group">
                        <label class="col-sm-1 control-label" style="text-align:left;">Dari : </label>
                        <div class="col-sm-2" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="tglFrom" id="tglFrom">
                        </div>
                        <label class="col-sm-1 control-label" style="text-align:left;margin-top:3px;">s/d : </label>
                        <div class="col-sm-2" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="tglTo" id="tglTo">
                        </div>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <button type="button" id="btnCari" class="btn btn-primary btn-sm"><span>GO</span></button>
                        </div>
                    </div>
                </form>
                <div id="kontensm">
                    <table id="tblsm"></table>
                    <div id="pgrsm"></div>        
                </div>
            </div>
            <div class="box-body" id="boxEdit" style="display: none;">
                <form class="form-horizontal" id="frEdit">
                    <input type="hidden" name="aded" id="aded">
                    <input type="hidden" name="qgh_id" id="qgh_id" readonly>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="col-sm-4 control-label" style="text-align:left;">SUBPLANT</label>
                            <div class="col-sm-8" style="margin-top:3px;">
                                <select class="form-control input-sm" name="qgh_subplant" id="qgh_subplant"></select>   
                            </div>  
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label" style="text-align:left;">SHIFT</label>
                            <div class="col-sm-8" style="margin-top:3px;">  
                                <select class="form-control input-sm" name="qgh_shift" id="qgh_shift"></select>
                            </div>
                        </div>   
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="col-sm-4 control-label" style="text-align:left;">TANGGAL</label>
                            <div class="col-sm-8" style="margin-top:3px;">  
                                <input class="form-control input-sm" type="text" name="qgh_date" id="qgh_date" readonly>
                            </div>    
                        </div>
                    </div>
                    <label class="col-sm-12 control-label" style="text-align:left;">I. HASIL PRODUKSI</label>
                    <div class="col-sm-12 table-responsive" id="dvitem"></div>
                    <div class="col-sm-12 text-right" style="margin-top:-10px;">
                        <input type="hidden" id="jmlbarisitem">
                        <input type="hidden" id="lastbarisitem">
                        <button type="button" class="btn btn-success btn-sm" onClick="tambahItem()" id="btnItem" style="display:none;">Tambah Code Motif</button>
                    </div>
                    <label class="col-sm-12 control-label" style="margin-top:3px;text-align:left;">II. ABSENSI</label>
                    <div class="col-sm-12">
                        <textarea class="form-control input-sm" name="qgh_absensi" id="qgh_absensi"></textarea>
                    </div>
                    <label class="col-sm-12 control-label" style="margin-top:3px;text-align:left;">III. KETERANGAN</label>
                    <div class="col-sm-12">
                        <textarea class="form-control input-sm" name="qgh_keterangan" id="qgh_keterangan"></textarea>
                    </div> 
                    <div class="col-sm-12" style="margin-top:10px;text-align:center;">
                        <button type="button" class="btn btn-primary btn-sm" onClick="simpanData()" id="btnSimpan" style="display:none;">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button>
                    </div>
                </form>    
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var frm = "include/glalineinput.inc.php";
var vdropmenu = false;
var validator = "";
var vcboHambatan = "";

function tampilTabel(tblnya, pgrnya, pTanggal, pSubPlan = 'All') {
    var topnya = tblnya+"_toppager";
    var vshrinktofit = true;
    var vpanjanglayar = 150;
    if($(window).height() >= 520){vpanjanglayar = $(window).height()-(230+$(".content-header").height());}
    if($(window).width() <= 550){vshrinktofit = false;}
    jQuery(tblnya).jqGrid({
        url:frm + "?mode=urai&tanggal="+pTanggal+"&subplan="+pSubPlan,
        mtype:"POST",
        datatype:"json",
        colModel:[
            {label:"ID", name:'qgh_id', index:'qgh_id', width:80},
            {label:"SUBPLANT", name:'qgh_subplant', index:'qgh_subplant', width:70, stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"TANGGAL", name:'qgh_date', index:'qgh_date', width:80},
            {label:"SHIFT", name:'qgh_shift', index:'qgh_shift', width:70},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'},
        ],
        sortname:'qgh_date desc,qgh_subplant asc,qgh_id', 
        sortorder:'desc', 
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
    $("#aded, #qgh_id, #qgh_subplant, #qgh_date, #qgh_absensi, #qgh_keterangan").val("");
    $("#qgh_shift, #dvitem").html("");
    $("#qgh_id, #qgh_subplant, #qgh_shift, #qgh_date, #qgh_absensi, #qgh_keterangan").attr('disabled',false);
    $("#boxEdit, #btnSimpan, #btnItem").hide();
    $("#boxAwal").show();
}

function tambahData(){
    $.post(frm+"?mode=cboshift", function(resp,stat){
        $("#qgh_shift").html(resp);
        $("#aded").val("add");
        $("#qgh_id").val("OTOMATIS");
        $("#qgh_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
        $("#dvitem").html('<table id="tblitem" class="table table-bordered table-condensed table-hover table-striped"><tr><th colspan="2">CODE</th><th>HASIL, M2</th><th>REJECT, M2</th><th>HAMBATAN</th></tr></table>');
        $("#jmlbarisitem").val(1);
        $("#lastbarisitem").val(0);
        $("#boxAwal").hide();
        $("#boxEdit, #btnSimpan, #btnItem").show();
        tambahItem();
        tambahItem();
        tambahItem();
        tambahItem();
    });
}

function lihatData(qgh_id){
    $.post(frm+"?mode=detailtabel", {stat:"view",qgh_id:qgh_id}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#qgh_id").val(o.qgh_id);
        $("#qgh_subplant").val(o.qgh_subplant);
        $("#qgh_date").val(o.qgh_date);
        $("#qgh_shift").html(o.qgh_shift);
        $("#qgh_absensi").val(o.qgh_absensi);
        $("#qgh_keterangan").val(o.qgh_keterangan);
        $("#dvitem").html(o.detailtabel);
        $("#jmlbarisitem").val(o.jmlbarisitem);
        $("#lastbarisitem").val(o.lastbarisitem);
        $("#qgh_subplant, #qgh_date, #qgh_shift, #qgh_absensi, #qgh_keterangan").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function editData(qgh_id){
    $.post(frm+"?mode=detailtabel", {stat:"edit",qgh_id:qgh_id}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#qgh_id").val(o.qgh_id);
        $("#qgh_subplant").val(o.qgh_subplant);
        $("#qgh_date").val(o.qgh_date);
        $("#qgh_shift").html(o.qgh_shift);
        $("#qgh_absensi").val(o.qgh_absensi);
        $("#qgh_keterangan").val(o.qgh_keterangan);
        $("#dvitem").html(o.detailtabel);
        $("#jmlbarisitem").val(o.jmlbarisitem);
        $("#lastbarisitem").val(o.lastbarisitem);
        $("#qgh_subplant, #qgh_date").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit, #btnSimpan, #btnItem").show();  
    });
}

function hapusData(qgh_id){
    var r = confirm("Hapus data input glaze line dengan id : "+qgh_id+" ?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {qgh_id:qgh_id}, function(resp,stat){
            if (resp=="OK") {
                $("#jdlModal").html("Hapus Data");
                $("#isiModal").html("Data berhasil dihapus");
                $('#myModal').modal('show');
                $.jgrid.gridUnload("#tblsm");
                tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
            } else {
                $("#jdlModal").html("Hapus Data Gagal");
                $("#isiModal").html(resp);
                $('#myModal').modal('show');
            }  
        });
    } else {
        return false;
    }
}

function simpanData() {
    var rulenya = {
            qgh_subplant:{required:true},
            qgh_date:{required:true},
            qgh_shift:{required:true},
            qgh_absensi:{required:true},
            qgh_keterangan:{required:true}
        };
    if(validator != "") {
        validator.destroy();
    }
    
    validator = $("#frEdit").validate({rules:rulenya});

    if($("#frEdit").valid()) {
        var mode = $("#aded").val();
        $.post(frm+"?mode="+mode, $("#frEdit").serialize(), function(resp,stat){
          if (resp=="OK") {
            if (mode == "add") {
                $("#jdlModal").html("Tambah Data");
                $("#isiModal").html("Data berhasil disimpan");
            } else {
                $("#jdlModal").html("Ubah Data");
                $("#isiModal").html("Perubahan data "+$("#qgh_id").val()+" berhasil disimpan");
            }
            $('#myModal').modal('show');
            formAwal();
            $.jgrid.gridUnload("#tblsm");
            tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
          }else{
            $("#jdlModal").html("Input Data Gagal");
            $("#isiModal").html(resp);
            $('#myModal').modal('show');
          }
        });
    }
}

function tambahItem() {
    var jmlbaris =  $("#jmlbarisitem").val();
    var lastbaris = $("#lastbarisitem").val();
    var table = document.getElementById("tblitem");
    var row = table.insertRow(jmlbaris);
    row.setAttribute("id", "tritem_ke_"+lastbaris); 
    var kolhapus = row.insertCell(0);
    var kolMotif = row.insertCell(1);
    var kolHasil = row.insertCell(2);
    var kolReject = row.insertCell(3);
    var kolHambatan = row.insertCell(4);
    kolhapus.style.width = "25px";
    kolHasil.style.width = "100px";
    kolReject.style.width = "100px";
    
    kolhapus.innerHTML = '<span class="glyphicon glyphicon-remove" onclick="hapusItem('+lastbaris+')"></span>';
    kolMotif.innerHTML = '<div class="input-group"><input class="form-control input-sm" name="qgd_motif['+lastbaris+']" id="qgd_motif_'+lastbaris+'" type="text" readonly><div class="input-group-addon" title="Pilih nama item"><span class="glyphicon glyphicon-option-horizontal" onClick="tampilMotif(\''+lastbaris+'\');"></span></div></div>';
    kolHasil.innerHTML = '<input class="form-control input-sm text-right" name="qgd_hasil['+lastbaris+']" id="qgd_hasil_'+lastbaris+'" type="text" onkeyup="hanyanumerik(this.id,this.value);">';
    kolReject.innerHTML = '<input class="form-control input-sm text-right" name="qgd_reject['+lastbaris+']" id="qgd_reject_'+lastbaris+'" type="text" onkeyup="hanyanumerik(this.id,this.value);">';
    kolHambatan.innerHTML = '<select class="form-control input-sm" name="qgd_hambatan['+lastbaris+']" id="qgd_hambatan_'+lastbaris+'">'+vcboHambatan+'</select>';
    $("#jmlbarisitem").val(parseInt(jmlbaris)+1);
    $("#lastbarisitem").val(parseInt(lastbaris)+1);
}

function hapusItem(baris) {
    $("#jmlbarisitem").val(parseInt($("#jmlbarisitem").val())-1);
    $("#tritem_ke_"+baris).remove();
}

function tampilMotif(idx) {
    $("#idx_motif").val(idx);
    $("#modalMotif").modal('show');    
}

function pilihMotif() {
    $.post(frm+"?mode=pilihmotif", {txt_cari:$("#txt_cari").val()}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#isiMdlMotif").html(o.out);
    });
}

function setMotif(motif) {
    var idx = $("#idx_motif").val();
    $("#qgd_motif_"+idx).val(motif);
    $("#modalMotif").modal('hide');
    $("#qgd_hasil_"+idx).focus();
}

function hanyanumerik(id, value) {
    if (/([^.0123456789]|)/g.test(value)) { 
        $("#"+id).val(value.replace(/([^.0123456789])/g, ''));
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

    $("#tglFrom").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        todayHighlight:true,
        endDate:'date'
    }).on('changeDate', function(e) {
        var tglTo = $("#tglTo").val().split("-");
        var tglb = new Date(tglTo[2], parseInt(tglTo[1])-1, tglTo[0]);
        var tgla = new Date(e.date.getFullYear(), e.date.getMonth(), e.date.getDate());
        $("#tglTo").datepicker('setStartDate', tgla);
        if(tgla > tglb) {
            alert('Tanggal Dari tidak boleh lebih cepat dari Tanggal s/d, mohon ubah Tanggal s/d.');
            $("#tglTo").datepicker('show');
        }
    }).val(moment().format("01-MM-YYYY"));

    $("#tglTo").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        todayHighlight:true,
        endDate:'date',
        startDate:'date'
    }).val(moment().format("DD-MM-YYYY"));

    $("#qgh_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-1d'
    });

    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());

    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#qgh_subplant").html(resp);
    });

    $("#txt_cari").keyup(function(e){
        if(e.keyCode == 13) {
            pilihMotif();
        }
    });

    $.post(frm+"?mode=pilihmotif", function(resp,stat){
        var o = JSON.parse(resp);
        $("#isiMdlMotif").html(o.out);
    });
    
    $.post(frm+"?mode=cbohambatan", function(resp,stat){
        vcboHambatan = resp;
    });
});
</script>