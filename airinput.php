<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['40'];
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
                    <input class="form-control input-sm" type="hidden" name="qlh_id" id="qlh_id" readonly>
                    <div class='container col col-md-12' >
                        <div class='row'>
                            <div class='col col-md-6'>
                                <label>Tanggal / Jam Input</label>
                                <input class='form-control input-sm' type='text' name='qih_date' id='qlh_date' readonly>   
                            </div>
                            <div class='col col-md-6'>
                                <label>Subplant</label>
                                <select class="form-control input-sm" id="qlh_sub_plant" name="qih_sub_plant"></select> 
                            </div>
                            <div class="col-md-6 bootstrap-timepicker">
                                <label>Jam</label>
                                <input class="form-control input-sm" type="text" name="qih_time" id="qlh_time">   
                            </div>
                            <div class='col col-md-6'>
                                <label>Deep Wheel 3</label>
                                <input class='form-control input-sm' type='text' name='qih_wheel2' id="qlh_deep_wheel2"  style="text-align:right;" onkeyup="hanyanumerik(this.id,this.value)">
                            </div>
                            <div class='col col-md-6'>
                                <label>Deep Wheel 2</label>
                                <input class='form-control input-sm' type='text' name='qih_wheel3' id="qlh_deep_wheel3" style="text-align:right;" onkeyup="hanyanumerik(this.id,this.value)"> 
                            </div>
                            <div class='col col-md-6'>
                                <label>Mushola, Kantin, Mess</label>
                                <input class='form-control input-sm' type='text' name='qih_mus' id="qlh_mus" style="text-align:right;" onkeyup="hanyanumerik(this.id,this.value)">
                            </div>
                            <div class='col col-md-6'>
                                <label>Glazing Line 2A</label>
                                <input class='form-control input-sm' type='text' name='qih_glaze' id="qlh_glaze" style="text-align:right;" onkeyup="hanyanumerik(this.id,this.value)"> 
                            </div>
                            <div class='col col-md-6'>
                                <label>PDAM</label>
                                <input class='form-control input-sm' type='text' name='qih_pdam' id="qlh_pdam" style="text-align:right;" onkeyup="hanyanumerik(this.id,this.value)"> 
                            </div>
                    </div>
                </form>
            <div class="table-responsive" id="divdetail"></div>
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/airinput.inc.php";
var vdropmenu = false;
var validator = "";

function tampilTabel(tblnya, pgrnya, pTanggal, pSubPlan = 'All'){
    var topnya = tblnya+"_toppager";
    var vshrinktofit = true;
    var vpanjanglayar = 150;
    if($(window).height() >= 520){vpanjanglayar = $(window).height()-(250+$("#frCari").height()+$(".content-header").height());}
    if($(window).width() <= 470){vshrinktofit = false;}
    jQuery(tblnya).jqGrid({
        url:frm + "?mode=urai&tanggal="+pTanggal+"&subplan="+pSubPlan,
        mtype:"POST",
        datatype:"json",
        colModel:[
            {label:"ID", name:'qlh_id', index:'qlh_id', width:80},
            {label:"SUBPLANT", name:'qlh_sub_plant', index:'qlh_sub_plant', width:70, stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"TANGGAL", name:'qlh_date', index:'qlh_date', width:80},
            {label:"JAM", name:'qlh_time', index:'qlh_time', width:70},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"qlh_date desc,qlh_sub_plant asc,qlh_id",
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
        //     var vid = $self.jqGrid("getCell", parentRowKey, "qlh_id");
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
        postData:{'qlh_id':pId},
        datatype:"json",
        colModel:[
            {label:'GROUP', name:'qss_desc', width:100, hidden:true},
            {label:'NO', name:'qsmd_sett_seq', width:100},
            {label:'DESKRIPSI', name:'qssd_monitoring_desc', width:200},
            {label:'REMARK', name:'qsmd_sett_remark', width:100},
            {label:'NILAI', name:'qsmd_sett_value', width:80, align:'right', formatter:'number'}
        ],
        styleUI:"Bootstrap",
        loadonce:true,
        width:'auto',
        height:'auto',
        grouping: true,
        groupingView: {
            groupField: ["qss_desc"],
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
    $("#qlh_id").val("");
    $("#qlh_date").val("");
    $("#qlh_time").val("");
    $("#qlh_sub_plant, #qlh_cap_bank_1, #qlh_cap_bank_2, #qlh_cap_bank_3").val("");
    $("#divdetail").html("");
    $("#qlh_id, #qlh_date, #qlh_time, #qlh_sub_plant, #qlh_cap_bank_1, #qlh_cap_bank_2, #qlh_cap_bank_3").attr('disabled',false);
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    $("#aded").val("add");
    $("#qlh_id").val("OTOMATIS");
    $("#qlh_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
    $("#qlh_time").val(moment().format("HH:mm"));
    $.post(frm+"?mode=detailtabel", {stat:"add"}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#qlh_sub_plant").val(o.qlh_sub_plant);
        $("#divdetail").html(o.detailtabel);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#qlh_id").val(o.qlh_id);
        $("#qlh_date").val(o.qlh_date);
        $("#qlh_time").val(o.qlh_time);
        $("#qlh_sub_plant").val(o.qlh_sub_plant);
        $("#qlh_deep_wheel2").val(o.qlh_deep_wheel2);
        $("#qlh_deep_wheel3").val(o.qlh_deep_wheel3);
        $("#qlh_cap_bank_3").val(o.qlh_cap_bank_3);
        $("#qlh_glaze").val(o.qlh_glazing_line);
        $("#qlh_mus").val(o.qlh_data_mushola);
        $("#qlh_pdam").html(o.detailtabel);
        $("#divdetail").html(o.detailtabel);
        $("#qlh_id, #qlh_date, #qlh_time, #qlh_sub_plant, #qlh_cap_bank_1, #qlh_cap_bank_2, #qlh_cap_bank_3").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
        console.log(resp)
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#qlh_id").val(o.qlh_id);
        $("#qlh_date").val(o.qlh_date);
        $("#qlh_time").val(o.qlh_time);
        $("#qlh_sub_plant").val(o.qlh_sub_plant);
        $("#qlh_deep_wheel2").val(o.qlh_deep_wheel2);
        $("#qlh_deep_wheel3").val(o.qlh_deep_wheel3);
        $("#qlh_cap_bank_3").val(o.qlh_cap_bank_3);
        $("#qlh_glaze").val(o.qlh_glazing_line);
        $("#qlh_mus").val(o.qlh_data_mushola);
        $("#qlh_pdam").html(o.detailtabel);
        $("#divdetail").html(o.detailtabel);
        $("#qlh_sub_plant, #qlh_date, #qlh_time").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
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
                alert(resp);
            }  
        });
    } else {
        return false;
    }
}

function simpanData(mode) {
    // alert(mode);
    var rulenya = {
            qlh_sub_plant:{required:true},
            qlh_date:{required:true},
            qlh_time:{required:true}
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
                alert("Perubahan data "+$("#qlh_id").val()+" berhasil disimpan");
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
    
    $("#qlh_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-1d'
    });

    $('#qlh_time').timepicker({
      showInputs: false,
      showMeridian: false,
      minuteStep: 5
    });

    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    
    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#qlh_sub_plant").html(resp);
    });

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });

    $('#qlh_sub_plant').change(function(){
        var subplan = this.value;
        $.post(frm+"?mode=detailtabel", {stat:"add",subplan:subplan}, function(resp,stat){
            var o = JSON.parse(resp);
            $("#divdetail").html(o.detailtabel); 
        });
    });

});

function hanyanumerik(id, value) {
    if (/([^.0123456789]|)/g.test(value)) { 
        $("#"+id).val(value.replace(/([^.0123456789])/g, ''));
    }
}
</script>