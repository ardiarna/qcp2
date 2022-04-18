<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['20'];
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
                    <input class="form-control input-sm" type="hidden" name="qch_id" id="qch_id" readonly>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qch_sub_plant" name="qch_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">TANGGAL</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="qch_date" id="qch_date" readonly>
                        </div>      
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SHIFT</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" id="qch_shift" name="qch_shift"></select>
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">NO. BALL MILL</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" id="qch_bm_no" name="qch_bm_no"></select>
                        </div>
                    </div>
                    <div class="table-responsive" id="divdetail"></div>
                </form>    
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/conbodyinput.inc.php";
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
            {label:"ID", name:'qch_id', index:'qch_id', width:80},
            {label:"SUBPLANT", name:'qch_sub_plant', index:'qch_sub_plant', width:70, stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"TANGGAL", name:'qch_date', index:'qch_date', width:80},
            {label:"SHIFT", name:'qch_shift', index:'qch_shift', width:70},
            {label:"NO. BALL MILL", name:'qch_bm_no', index:'qch_bm_no', width:80},
            {label:"DIBUAT OLEH", name:'qch_user_create', index:'qch_user_create', width:80},
            {label:"DIBUAT TGL", name:'qch_date_create', index:'qch_date_create', width:100},
            {label:"DIEDIT OLEH", name:'qch_user_modify', index:'qch_user_modify', width:80},
            {label:"DIEDIT TGL", name:'qch_date_modify', index:'qch_date_modify', width:100},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"qch_date desc,qch_sub_plant asc,qch_id",
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
        //     var vid = $self.jqGrid("getCell", parentRowKey, "qch_id");
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
        postData:{'qch_id':pId},
        datatype:"json",
        colModel:[
            {label:'GROUP', name:'qcpm_desc', width:100, hidden:true},
            {label:'NO', name:'qcd_prep_seq', width:100},
            {label:'DESKRIPSI', name:'qcpd_control_desc', width:200},
            {label:"STANDAR", name:'qpd_standart', width:80},
            {label:"SILO", name:'qcs_desc', width:80},
            {label:"NO TANK", name:'qct_desc', width:80},
            {label:"NILAI", name:'qcd_prep_value', width:80, align:'right', formatter:'number'}
        ],
        styleUI:"Bootstrap",
        loadonce:true,
        width:'auto',
        height:'auto',
        rowNum:100,
        grouping: true,
        groupingView: {
            groupField: ["qcpm_desc"],
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
    $("#qch_id").val("");
    $("#qch_date").val("");
    $("#qch_shift").html("");
    // $("#qch_sub_plant").val("");
    $("#qch_bm_no").html("");
    $("#divdetail").html("");
    $("#qch_id, #qch_date, #qch_shift, #qch_sub_plant, #qch_bm_no").attr('disabled',false);
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    $.post(frm+"?mode=cboshift", function(resp,stat){
        $("#qch_shift").html(resp);
        var subplan = $('#qch_sub_plant').val();
        $.post(frm+"?mode=detailtabel", {stat:"add",subplan:subplan}, function(resp,stat){
            var o = JSON.parse(resp);
            $("#divdetail").html(o.detailtabel);
            $("#aded").val("add");
            $("#qch_id").val("OTOMATIS");
            $("#qch_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
            $("#boxAwal").hide();
            $("#boxEdit").show();      
        });
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#qch_id").val(o.qch_id);
        $("#qch_date").val(o.qch_date);
        $("#qch_shift").html(o.qch_shift);
        $("#qch_sub_plant").val(o.qch_sub_plant);
        $("#qch_bm_no").html(o.qch_bm_no);
        $("#divdetail").html(o.detailtabel);
        $("#qch_id, #qch_date, #qch_shift, #qch_sub_plant, #qch_bm_no").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#qch_id").val(o.qch_id);
        $("#qch_date").val(o.qch_date);
        $("#qch_shift").html(o.qch_shift);
        $("#qch_sub_plant").val(o.qch_sub_plant);
        $("#qch_bm_no").html(o.qch_bm_no);
        $("#divdetail").html(o.detailtabel);
        $("#qch_sub_plant, #qch_date, #qch_shift").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function hapusData(kode){
    var r = confirm("Batalkan data penimbangan dengan id "+kode+"?");
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
            qch_sub_plant:{required:true},
            qch_date:{required:true},
            qch_shift:{required:true},
            qch_bm_no:{required:true},
        };
    $('input[id^=qcd_prep_value_]').each(function(index, el){
        var key = el.id.substr(15)
        if(el.value) {
            rulenya["qcd_silo_no["+key+"]"] = {required:true};
            rulenya["qcd_slip_no["+key+"]"] = {required:true};
        }    
    });
    
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
                alert("Perubahan data "+$("#qch_id").val()+" berhasil disimpan");
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

function isiNoSlip(noslip, nilai) {
    $(".noslip_"+noslip).val(nilai);
} 

function isiNoSilo(nosilo, nilai) {
    $(".nosilo_"+nosilo).val(nilai);
}

function hanyanumerik(id, value) {
    if (/([^.0123456789]|)/g.test(value)) { 
        $("#"+id).val(value.replace(/([^.0123456789])/g, ''));
    }
}

function hitungTotalStokPow() {
    var total = 0;
    $('input.stokpow').each(function(index, el){
        if(el.value) {
            total += parseFloat(el.value);
        }    
    });
    $('#tot_stokpow').val(total);
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

    $("#qch_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        // startDate:'-1d'
    });

    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    
    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#qch_sub_plant").html(resp);
        var subplan = $('#qch_sub_plant').val();
        $.post(frm+"?mode=cbobalmil", {subplan:subplan}, function(resp,stat){
            $("#qch_bm_no").html(resp);  
        });
    });

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });

    $('#qch_sub_plant').change(function(){
        var subplan = this.value;
        $.post(frm+"?mode=cbobalmil", {subplan:subplan}, function(resp,stat){
            $("#qch_bm_no").html(resp);  
        });
        if($("#aded").val() == "add") {
            $.post(frm+"?mode=detailtabel", {stat:"add",subplan:subplan}, function(resp,stat){
                var o = JSON.parse(resp);
                $("#divdetail").html(o.detailtabel);      
            });
        }
    });
});
</script>