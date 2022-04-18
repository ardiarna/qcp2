<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['28'];
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
                <form class="form-horizontal">
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
            <div class="box-body" id="boxEdit" style="display: none;">
                <form class="form-horizontal" id="frEdit">
                    <input type="hidden" name="aded" id="aded">
                    <input class="form-control input-sm" type="hidden" name="qfh_id" id="qfh_id" readonly>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qfh_sub_plant" name="qfh_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">TANGGAL</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="qfh_date" id="qfh_date" readonly>
                        </div>    
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">DILAPORKAN KEPADA</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="qfh_reported_to" name="qfh_reported_to">
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">DIKERJAKAN OLEH</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="qfh_done_by" name="qfh_done_by">       
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">TEMUAN</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <textarea class="form-control input-sm" id="qfh_findings" name="qfh_findings"></textarea>
                        </div>
                    </div>
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
var frm = "include/ftaginput.inc.php";
var vdropmenu = false;
var validator = "";

function tampilTabel(tblnya, pgrnya, pTanggal, pSubPlan = 'All'){
    var topnya = tblnya+"_toppager";
    var vshrinktofit = true;
    if($(window).width() <= 800){vshrinktofit = false;}
    jQuery(tblnya).jqGrid({
        url:frm + "?mode=urai&tanggal="+pTanggal+"&subplan="+pSubPlan,
        mtype:"POST",
        datatype:"json",
        colModel:[
            {label:"ID", name:'qfh_id', index:'qfh_id', width:80},
            {label:"SUBPLANT", name:'qfh_sub_plant', index:'qfh_sub_plant', width:70, stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"TANGGAL", name:'qfh_date', index:'qfh_date', width:80},
            {label:"TEMUAN", name:'qfh_findings', index:'qfh_findings', width:70},
            {label:"STATUS", name:'qfh_rec_stat', index:'qfh_rec_stat', width:80},
            {label:"DILAPORKAN KEPADA", name:'qfh_reported_to', index:'qfh_reported_to', width:80},
            {label:"DIKERJAKAN OLEH", name:'qfh_done_by', index:'qfh_done_by', width:70},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'},
        ],
        sortname:"qfh_date desc,qfh_sub_plant asc,qfh_id",
        sortorder:'desc', 
        styleUI:"Bootstrap",
        hoverrows:false,
        loadonce:false,
        height:"auto",
        rowNum:-1,
        rowList:[5,10,15,20,"-1:All"],
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
    $("#qfh_id").val("");
    $("#qfh_date").val("");
    $("#qfh_findings").val("");
    $("#qfh_sub_plant").val("");
    $("#qfh_reported_to").val("");
    $("#qfh_done_by").val("");
    $("#qfh_id, #qfh_date, #qfh_findings, #qfh_sub_plant, #qfh_reported_to, #qfh_done_by").attr('disabled',false);
    $("#dvTombol").html('<button type="button" class="btn btn-primary btn-sm" onClick="simpanData()">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button>');
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    $("#aded").val("add");
    $("#qfh_id").val("OTOMATIS");
    $("#qfh_date").val(moment().format("DD-MM-YYYY"));
    $("#boxAwal").hide();
    $("#boxEdit").show();
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#qfh_id").val(o.qfh_id);
        $("#qfh_date").val(o.qfh_date);
        $("#qfh_findings").val(o.qfh_findings);
        $("#qfh_sub_plant").val(o.qfh_sub_plant);
        $("#qfh_reported_to").val(o.qfh_reported_to);
        $("#qfh_done_by").val(o.qfh_done_by);
        $("#qfh_id, #qfh_date, #qfh_findings, #qfh_sub_plant, #qfh_reported_to, #qfh_done_by").attr('disabled',true);
        $("#dvTombol").html('<button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button>');
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#qfh_id").val(o.qfh_id);
        $("#qfh_date").val(o.qfh_date);
        $("#qfh_findings").val(o.qfh_findings);
        $("#qfh_sub_plant").val(o.qfh_sub_plant);
        $("#qfh_reported_to").val(o.qfh_reported_to);
        $("#qfh_done_by").val(o.qfh_done_by);
        $("#qfh_sub_plant").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function hapusData(kode){
    var r = confirm("Hapus data dengan id "+kode+"?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {kode:kode}, function(resp,stat){
            if (resp=="OK") {
                $.jgrid.gridUnload("#tblsm");
                tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
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
                alert("Perubahan data "+$("#qfh_id").val()+" berhasil disimpan");
            }
            formAwal();
            $.jgrid.gridUnload("#tblsm");
            tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
          }else{
            alert(resp);
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
        if(vukur <= 800){
            $("#tblsm").setGridWidth(vukur, false); 
       } else {
            $("#tblsm").setGridWidth(vukur, true);
       }
    };
    $('#kontensm').resize(ubahUkuranJqGrid);

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

    // $("#qfh_date").datepicker({
    //     autoclose:true,
    //     format:'dd-mm-yyyy'
    // });

    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    
    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#qfh_sub_plant").html(resp);
    });

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });

    $("#qfh_reported_to").afInputVal();
    $("#qfh_done_by").afInputVal();
    $("#qfh_findings").afInputVal();

    var rulenya = {
            qfh_sub_plant:{required:true},
            qfh_date:{required:true},
            qfh_reported_to:{required:true},
            qfh_done_by:{required:true},
            qfh_findings:{required:true}
        };
    $("#frEdit").validate({rules:rulenya});
    
    
});
</script>