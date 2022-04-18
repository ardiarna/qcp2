<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['26'];
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
                    <input class="form-control input-sm" type="hidden" name="qph_id" id="qph_id" readonly>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qph_sub_plant" name="qph_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">TANGGAL</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="qph_date" id="qph_date" readonly>
                        </div>      
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SHIFT</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" id="qph_shift" name="qph_shift"></select>
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">NO. LINE</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" id="qph_no_line" name="qph_no_line"></select>
                        </div>
                    </div>
                    <div class="table-responsive" id="divdetail"></div>
                </form>    
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/prdryinginput.inc.php";
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
            {label:"ID", name:'qph_id', index:'qph_id', width:80},
            {label:"SUBPLANT", name:'qph_sub_plant', index:'qph_sub_plant', width:70, stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"TANGGAL", name:'qph_date', index:'qph_date', width:80},
            {label:"SHIFT", name:'qph_shift', index:'qph_shift', width:70},
            {label:"NO. LINE", name:'qph_no_line', index:'qph_no_line', width:80},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"qph_date desc,qph_sub_plant asc,qph_shift asc,qph_no_line asc,qph_id",
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
        //     var vid = $self.jqGrid("getCell", parentRowKey, "qph_id");
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
        postData:{'qph_id':pId},
        datatype:"json",
        colModel:[
            {label:'GROUP', name:'qpg_desc', width:100, hidden:true},
            {label:'NO', name:'qpd_pd_seq', width:100},
            {label:'DESKRIPSI', name:'qpgd_control_desc', width:200},
            {label:"STD", name:'qpd_standart', width:80},
            {label:"UNIT", name:'qgu_code', width:70},
            {label:"MP", name:'qpd_mould_no', width:80},
            {label:"HD", name:'qpd_hd_no', width:80},
            {label:"REMARK", name:'qpd_pd_remark', width:80},
            {label:"NILAI", name:'qpd_pd_value', width:80, align:'right', formatter:'number'}
        ],
        styleUI:"Bootstrap",
        loadonce:true,
        width:'auto',
        height:'auto',
        grouping: true,
        groupingView: {
            groupField: ["qpg_desc"],
            groupColumnShow: [false],
            groupText: ["<b>{0}</b>"],
            groupOrder: ["desc"],
            groupSummary: [false],
            groupCollapse: false  
        }
   });
}

function formAwal(){
    $("#aded").val("");
    $("#qph_id").val("");
    $("#qph_date").val("");
    $("#qph_shift").html("");
    $("#qph_sub_plant").val("");
    $("#qph_no_line").val("");
    $("#divdetail").html("");
    $("#qph_id, #qph_date, #qph_shift, #qph_sub_plant, #qph_no_line").attr('disabled',false);
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    $.post(frm+"?mode=cboshift", function(resp,stat){
        $("#aded").val("add");
        $("#qph_id").val("OTOMATIS");
        $("#qph_shift").html(resp);
        $("#qph_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
        $("#boxAwal").hide();
        $("#boxEdit").show();
        $("#divdetail").html("<table align='center'><tr><td class='text-center'><button type='button' class='btn btn-warning btn-sm' onClick='formAwal()'>Kembali</button></td></tr></table>");   
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#qph_id").val(o.qph_id);
        $("#qph_date").val(o.qph_date);
        $("#qph_shift").html(o.qph_shift);
        $("#qph_sub_plant").val(o.qph_sub_plant);
        $("#qph_no_line").html(o.qph_no_line);
        $("#divdetail").html(o.detailtabel);
        $("#qph_id, #qph_date, #qph_shift, #qph_sub_plant, #qph_no_line").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#qph_id").val(o.qph_id);
        $("#qph_date").val(o.qph_date);
        $("#qph_shift").html(o.qph_shift);
        $("#qph_sub_plant").val(o.qph_sub_plant);
        $("#qph_no_line").html(o.qph_no_line);
        $("#divdetail").html(o.detailtabel);
        $("#qph_sub_plant, #qph_date, #qph_shift, #qph_no_line").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function hapusData(kode){
    var r = confirm("Batalkan data input press & drying dengan id "+kode+"?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {kode:kode}, function(resp,stat){
            if(resp=="OK") {
                formAwal();
                $.jgrid.gridUnload("#tblsm");
                tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
            }else{
                alert(resp);
            }  
        });
    } else {
        return false;
    }
}

function simpanData(mode) {
    var rulenya = {
            qph_sub_plant:{required:true},
            qph_date:{required:true},
            qph_shift:{required:true},
            qph_no_line:{required:true}
        };
    $('input[id^=qpd_pd_value_]').each(function(index, el){
        var key = el.id.substr(13)
        if(el.value) {
            rulenya["qpd_mould_no["+key+"]"] = {required:true};
            rulenya["qpd_hd_no["+key+"]"] = {required:true};
        }    
    });
    
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
                alert("Perubahan data "+$("#qph_id").val()+" berhasil disimpan");
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

function hideGrup(grupke) {
    $("#trgrup_ke_"+grupke).toggle();
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

    $("#qph_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-1d'
    });

    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    
    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#qph_sub_plant").html(resp);
    });

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });

    $('#qph_sub_plant').change(function(){
        $.post(frm+"?mode=cboline", {}, function(resp,stat){
            $("#qph_no_line").html(resp);  
        });
    });

    $('#qph_no_line').change(function(){
        var subplan = $("#qph_sub_plant").val();
        var no_line = this.value;
        if($("#aded").val() == "add") {
            var press = this.value;
            $.post(frm+"?mode=detailtabel", {stat:"add",subplan:subplan,no_line:no_line}, function(resp,stat){
                var o = JSON.parse(resp);
                $("#divdetail").html(o.detailtabel);      
            });
        }
    });
    
});
</script>