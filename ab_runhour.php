<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['49'];
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
                        <label class="col-sm-1 control-label" style="text-align:left;">From</label>
                        <div class="col-sm-2" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="tglFrom" id="tglFrom">
                        </div>
                        <label class="col-sm-1 control-label" style="text-align:left;margin-top:3px;">To :</label>
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
            <div class="box-body" id="boxEdit" style="display:none;">
                <form class="form-horizontal" id="frEdit">
                    <input type="hidden" name="aded" id="aded">
                    <input class="form-control input-sm" type="hidden" name="qar_id" id="qar_id" readonly>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">TANGGAL</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="qar_date" id="qar_date" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SHIFT</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" id="qar_shift" name="qar_shift"></select>
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">AWAL</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="qar_awal" id="qar_awal" style="text-align:right;" onkeyup="hanyanumerik(this.id,this.value)">   
                        </div> 
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">NAMA MESIN</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qar_ab_nama" name="qar_ab_nama"></select>       
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">AKHIR</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="qar_akhir" id="qar_akhir" style="text-align:right;" onkeyup="hanyanumerik(this.id,this.value)">   
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">NOMOR MESIN</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qar_ab_nomor" name="qar_ab_nomor"></select>       
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">KETERANGAN</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="qar_remark" id="qar_remark">   
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12" style="margin-top:3px;text-align:center;">
                            <button type="button" class="btn btn-primary btn-sm" onClick="simpanData()" id="btnSimpan" style="display:none;">Simpan</button>
                            <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button>
                        </div>
                    </div>
                </form>    
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/ab_runhour.inc.php";
var vdropmenu = false;
var validator = "";

function tampilTabel(tblnya, pgrnya, pTanggal){
    var topnya = tblnya+"_toppager";
    var vshrinktofit = true;
    var vpanjanglayar = 150;
    if($(window).height() >= 520){vpanjanglayar = $(window).height()-(250+$("#frCari").height()+$(".content-header").height());}
    if($(window).width() <= 470){vshrinktofit = false;}
    jQuery(tblnya).jqGrid({
        url:frm + "?mode=urai&tanggal="+pTanggal,
        mtype:"POST",
        datatype:"json",
        colModel:[
            {label:"ID", name:'qar_id', index:'qar_id', width:80},
            {label:"TANGGAL", name:'qar_date', index:'qar_date', width:80},
            {label:"SHIFT", name:'qar_shift', index:'qar_shift', width:70},
            {label:'NAMA MESIN', name:'qar_ab_nama', width:200},
            {label:'NOMOR MESIN', name:'qar_ab_nomor', width:100},
            {label:"AWAL", name:'qar_awal', index:'qar_awal', width:100, sorttype:"int", align:'right', formatter:'integer'},
            {label:"AKHIR", name:'qar_akhir', index:'qar_akhir', width:100, sorttype:"int", align:'right', formatter:'integer'},
            {label:'KETERANGAN', name:'qar_remark', width:100},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:111, align:'center'}
        ],
        sortname:"qar_date desc,qar_shift desc,qar_id",
        sortorder:'desc', 
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

function formAwal(){
    $("#aded").val("");
    $("#qar_shift, #qar_ab_nomor").html("");
    $("#qar_id, #qar_date, #qar_ab_nama, #qar_awal, #qar_akhir, #qar_remark").val("");
    $("#qar_id, #qar_date, #qar_shift, #qar_ab_nama, #qar_ab_nomor, #qar_awal, #qar_akhir, #qar_remark").attr('disabled',false);
    $("#boxEdit, #btnSimpan").hide();
    $("#boxAwal").show();
}

function tambahData(){
    $.post(frm+"?mode=cboshift", function(resp,stat){
        $("#aded").val("add");
        $("#qar_id").val("OTOMATIS");
        $("#qar_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
        $("#qar_shift").html(resp);
        $("#boxAwal").hide();
        $("#boxEdit, #btnSimpan").show();  
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        console.log(o);
        $("#qar_id").val(o.qar_id);
        $("#qar_date").val(o.qar_date);
        $("#qar_shift").html(o.qar_shift);
        $("#qar_ab_nama").val(o.qar_ab_nama);
        $("#qar_ab_nomor").html(o.qar_ab_nomor);
        $("#qar_awal").val(o.qar_awal);
        $("#qar_akhir").val(o.qar_akhir);
        $("#qar_remark").val(o.qar_remark);
        $("#qar_id, #qar_date, #qar_shift, #qar_ab_nama, #qar_ab_nomor, #qar_awal, #qar_akhir, #qar_remark").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#qar_id").val(o.qar_id);
        $("#qar_date").val(o.qar_date);
        $("#qar_shift").html(o.qar_shift);
        $("#qar_ab_nama").val(o.qar_ab_nama);
        $("#qar_ab_nomor").html(o.qar_ab_nomor);
        $("#qar_awal").val(o.qar_awal);
        $("#qar_akhir").val(o.qar_akhir);
        $("#qar_remark").val(o.qar_remark);
        // $("#qar_date, #qar_shift").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit, #btnSimpan").show();
    });
}

function hapusData(kode){
    var r = confirm("Batalkan data pemakaian listrik dengan id "+kode+"?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {kode:kode}, function(resp,stat){
            if (resp=="OK") {
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
            qar_date:{required:true},
            qlh_time:{required:true}
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
                alert("Data berhasil disimpan");
            } else {
                alert("Perubahan data "+$("#qar_id").val()+" berhasil disimpan");
            }
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

function hideGrup(grupke){
    $(".trgrup_ke_"+grupke).toggle();
}
    
$(document).ready(function () {
    var ubahUkuranJqGrid = function(){
        var vukur = $('#kontensm').width(); 
        if(vukur <= 470){
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
    $('#frCari').resize(ubahTinggiJqGrid);
    
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
            alert('Tanggal From tidak boleh lebih cepat dari tanggal To, mohon ubah tanggal To.');
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
    
    $("#qar_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-1d'
    });

    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });

    $.post(frm+"?mode=cboabnama", function(resp,stat){
        $("#qar_ab_nama").html(resp); 
    });
    
    $('#qar_ab_nama').change(function(){
        var qab_nama = this.value;
        $("#qar_ab_nomor").html('');
        $.post(frm+"?mode=cboabnomor", {qab_nama:qab_nama}, function(resp,stat){
            $("#qar_ab_nomor").html(resp);  
        });
    });

});

function hanyanumerik(id, value) {
    if (/([^.0123456789]|)/g.test(value)) { 
        $("#"+id).val(value.replace(/([^.0123456789])/g, ''));
    }
}
</script>