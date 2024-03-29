﻿<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['22'];
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
            <div class="box-body" id="boxEdit" style="display: none;">
                <form class="form-horizontal" id="frEdit">
                    <input type="hidden" name="aded" id="aded">
                    <input class="form-control input-sm" type="hidden" name="qgh_id" id="qgh_id" readonly>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qgh_sub_plant" name="qgh_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">TANGGAL</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="qgh_date" id="qgh_date" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SHIFT</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" id="qgh_shift" name="qgh_shift"></select>
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">KATEGORI</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qgh_category" name="qgh_category">
                                <option></option>
                                <option value="1">Engobe</option>
                                <option value="2">Glazur</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">NOMOR BALL MILL</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qgh_bmg_no" name="qgh_bmg_no"></select>       
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">KODE FORMULA</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qgh_glaze_code" name="qgh_glaze_code"></select>   
                        </div>
                    </div>
                    <div class="table-responsive" id="divdetail"></div>
                </form>    
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/glazeinput.inc.php";
var vdropmenu = false;
var validator = "";

function tampilTabel(tblnya, pgrnya, pTanggal, pSubPlan = 'All'){
    var topnya = tblnya+"_toppager";
    var vshrinktofit = true;
    var vpanjanglayar = 150;
    if($(window).height() >= 520){vpanjanglayar = $(window).height()-(250+$("#frCari").height()+$(".content-header").height());}
    if($(window).width() <= 800){vshrinktofit = false;}
    jQuery(tblnya).jqGrid({
        url:frm + "?mode=urai&tanggal="+pTanggal+"&subplan="+pSubPlan,
        mtype:"POST",
        datatype:"json",
        colModel:[
            {label:"ID", name:'qgh_id', index:'qgh_id', width:80},
            {label:"SUBPLANT", name:'qgh_sub_plant', index:'qgh_sub_plant', width:70, stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"TANGGAL", name:'qgh_date', index:'qgh_date', width:80},
            {label:"SHIFT", name:'qgh_shift', index:'qgh_shift', width:70},
            {label:"KATEGORI", name:'qgh_category', index:'qgh_category', width:80},
            {label:"NOMOR BALL MILL", name:'qgh_bmg_no', index:'qgh_bmg_no', width:70},
            {label:"KODE FORMULA", name:'qgh_glaze_code', index:'qgh_glaze_code', width:100},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'},
        ],
        sortname:"qgh_date desc,qgh_sub_plant asc,qgh_id",
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
        // subGrid:true,
        // subGridRowExpanded:function(parentRowID, parentRowKey) {
        //     var $self = $(this);
        //     var vid = $self.jqGrid("getCell", parentRowKey, "qgh_id");
        //     tampilSubTabel(parentRowID, parentRowKey, vid);
        // }
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

function tampilSubTabel(parentRowID, parentRowKey, pId) {
    var childGridID = parentRowID + "_table";
    $('#' + parentRowID).append('<table id=' + childGridID + '></table>');
    jQuery("#" + childGridID).jqGrid({
        url:frm + "?mode=suburai",
        mtype:"POST",
        postData:{'qgh_id':pId},
        datatype:"json",
        colModel:[
            {label:'GROUP', name:'qggm_desc', width:100, hidden:true},
            {label:'NO', name:'qgd_prep_seq', width:100},
            {label:'DESKRIPSI', name:'qgdm_control_desc', width:100},
            {label:'STANDAR', name:'qgd_standard_id', width:200},
            {label:'UNIT', name:'qgu_code', width:200},
            {label:'NILAI', name:'qgd_prep_value', width:80, align:'right', formatter:'number'},
            {label:'REMARK', name:'qgd_prep_remark', width:100}
        ],
        styleUI:"Bootstrap",
        loadonce:true,
        width:'auto',
        height:'auto',
        grouping: true,
        groupingView: {
            groupField: ["qggm_desc"],
            groupColumnShow: [false],
            groupText: ["<b>{0}</b>"],
            groupOrder: ["desc"],
            groupSummary: [true],
            groupCollapse: false  
        }
   });
}

function formAwal(){
    $("#aded").val("");
    $("#qgh_id").val("");
    $("#qgh_date").val("");
    $("#qgh_shift").html("");
    $("#qgh_sub_plant").val("");
    $("#qgh_category").val("");
    $("#qgh_bmg_no").html("");
    $("#qgh_glaze_code").html("");
    $("#divdetail").html("");
    $("#qgh_id, #qgh_date, #qgh_shift, #qgh_sub_plant, #qgh_category, #qgh_bmg_no, #qgh_glaze_code").attr('disabled',false);
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
     $.post(frm+"?mode=detailtabel", {stat:"add"}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#divdetail").html(o.detailtabel);
        $.post(frm+"?mode=cboshift", function(resp,stat){
            $("#qgh_shift").html(resp);
            $("#aded").val("add");
            $("#qgh_id").val("OTOMATIS");
            $("#qgh_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
            $("#boxAwal").hide();
            $("#boxEdit").show(); 
        }); 
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#qgh_id").val(o.qgh_id);
        $("#qgh_date").val(o.qgh_date);
        $("#qgh_shift").html(o.qgh_shift);
        $("#qgh_sub_plant").val(o.qgh_sub_plant);
        $("#qgh_category").val(o.qgh_category);
        $("#qgh_bmg_no").html(o.qgh_bmg_no);
        $("#qgh_glaze_code").html(o.qgh_glaze_code);
        $("#divdetail").html(o.detailtabel);
        $("#qgh_id, #qgh_date, #qgh_shift, #qgh_sub_plant, #qgh_category, #qgh_bmg_no, #qgh_glaze_code").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#qgh_id").val(o.qgh_id);
        $("#qgh_date").val(o.qgh_date);
        $("#qgh_shift").html(o.qgh_shift);
        $("#qgh_sub_plant").val(o.qgh_sub_plant);
        $("#qgh_category").val(o.qgh_category);
        $("#qgh_bmg_no").html(o.qgh_bmg_no);
        $("#qgh_glaze_code").html(o.qgh_glaze_code);
        $("#divdetail").html(o.detailtabel);
        $("#qgh_sub_plant, #qgh_date, #qgh_shift").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function hapusData(kode){
    var r = confirm("Batalkan data input glaze dengan id "+kode+"?");
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

function simpanData(mode) {
    var rulenya = {
            qgh_sub_plant:{required:true},
            qgh_date:{required:true},
            qgh_shift:{required:true},
            qgh_category:{required:true},
            qgh_bmg_no:{required:true},
            qgh_glaze_code:{required:true}
        };
    if(validator != "") {
        validator.destroy();
    }
    
    validator = $("#frEdit").validate({rules:rulenya});
    
    if($("#frEdit").valid()) {
        $.post(frm+"?mode="+mode, $("#frEdit").serialize(), function(resp,stat){
          if (resp=="OK") {
            if (mode == "add") {
                alert("Data berhasil disimpan");
            } else {
                alert("Perubahan data "+$("#qgh_id").val()+" berhasil disimpan");
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
    
    $("#qgh_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-1d'
    });

    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    
    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#qgh_sub_plant").html(resp);
        var subplan = $('#qgh_sub_plant').val();
        $.post(frm+"?mode=cboballmill", {subplan:subplan}, function(resp,stat){
            $("#qgh_bmg_no").html(resp);  
        });
    });

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });

    $('#qgh_sub_plant').change(function(){
        var subplan = this.value;
        $.post(frm+"?mode=cboballmill", {subplan:subplan}, function(resp,stat){
            $("#qgh_bmg_no").html(resp);  
        });
    });

    $('#qgh_category').change(function(){
        var kategori = this.value;
        $.post(frm+"?mode=cbokodeglaze", {kategori:kategori}, function(resp,stat){
            $("#qgh_glaze_code").html(resp);  
        });
    });
});
</script>