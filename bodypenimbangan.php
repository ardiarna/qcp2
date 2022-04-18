<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['18'];
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
                    <input class="form-control input-sm" type="hidden" name="qbh_id" id="qbh_id" readonly>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qbh_sub_plant" name="qbh_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">NOMOR BALL MILL</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qbh_bm_no" name="qbh_bm_no"></select>       
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SHIFT</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" id="qbh_shift" name="qbh_shift"></select>
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">KAPASITAS</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="qbh_volume" id="qbh_volume">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">TANGGAL</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="qbh_date" id="qbh_date" readonly>
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">KODE BODY</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qbh_body_code" name="qbh_body_code"></select>   
                        </div>
                    </div>
                    <div class="table-responsive" id="divdetail"></div>
                </form>    
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/bodypenimbangan.inc.php";
var vdropmenu = false;
var validator = "";

function tampilTabel(tblnya, pgrnya, pJenis, pTanggal, pSubPlan = 'All'){
    var topnya = tblnya+"_toppager";
    var vshrinktofit = true;
    var vpanjanglayar = 150;
    if($(window).height() >= 520){vpanjanglayar = $(window).height()-(250+$("#frCari").height()+$(".content-header").height());}
    if($(window).width() <= 800){vshrinktofit = false;}
    jQuery(tblnya).jqGrid({
        url:frm + "?mode=urai&jenis="+pJenis+"&tanggal="+pTanggal+"&subplan="+pSubPlan,
        mtype:"POST",
        datatype:"json",
        colModel:[
            {label:"ID", name:'qbh_id', index:'qbh_id', width:80},
            {label:"SUBPLANT", name:'qbh_sub_plant', index:'qbh_sub_plant', width:70, stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"TANGGAL", name:'qbh_date', index:'qbh_date', width:80},
            {label:"SHIFT", name:'qbh_shift', index:'qbh_shift', width:70},
            {label:"KODE BODY", name:'qbh_body_code', index:'qbh_body_code', width:200},
            {label:"NOMOR BALL MILL", name:'qbh_bm_no', index:'qbh_bm_no', width:100},
            {label:"KAPASITAS", name:'qbh_volume', index:'qbh_volume', width:100, sorttype:"int", align:'right', formatter:'integer'},
            {label:"No. PBD", name:'qbh_kode_pbd', index:'qbh_kode_pbd', width:80},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:111, align:'center'},
        ],
        sortname:"qbh_date desc,qbh_sub_plant asc,qbh_id",
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
        //     var vid = $self.jqGrid("getCell", parentRowKey, "qbh_id");
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
        postData:{'qbh_id':pId},
        datatype:"json",
        colModel:[
            {label:'TYPE', name:'qbd_material_type', width:100, hidden:true},
            {label:'KODE MATERIAL', name:'qbd_material_code', width:100},
            {label:'NAMA MATERIAL', name:'qbd_material_name', width:200},
            {label:'NO. BOX', name:'qbd_box_unit', width:200},
            {label:'FORMULA (%)', name:'qbd_formula', width:80, align:'right', formatter:'number', summaryType: "sum"},
            {label:'DW (kg)', name:'qbd_dw', width:80, align:'right', formatter:'number', summaryType: "sum"},
            {label:'M.C (%)', name:'qbd_mc', width:80, align:'right', formatter:'number', summaryType: "sum"},
            {label:'WW (kg)', name:'qbd_ww', width:80, align:'right', formatter:'number', summaryType: "sum"},
            {label:'NILAI', name:'qbd_value', width:80, align:'right', formatter:'number', summaryType: "sum"},
            {label:'REMARK', name:'qbd_remark', width:100}
        ],
        styleUI:"Bootstrap",
        loadonce:true,
        width:'auto',
        height:'auto',
        rowNum:100,
        footerrow:true,
        grouping: true,
        groupingView: {
            groupField: ["qbd_material_type"],
            groupColumnShow: [false],
            groupText: ["<b>{0}</b>"],
            groupOrder: ["desc"],
            groupSummary: [true],
            groupCollapse: false  
        },
        loadComplete:function(id){
            var $self = $(this),
            sumFormula = $self.jqGrid("getCol", "qbd_formula", false, "sum");
            sumDW = $self.jqGrid("getCol", "qbd_dw", false, "sum");
            sumMC = $self.jqGrid("getCol", "qbd_mc", false, "sum");
            sumWW = $self.jqGrid("getCol", "qbd_ww", false, "sum");
            sumValue = $self.jqGrid("getCol", "qbd_value", false, "sum");
            $self.jqGrid("footerData", "set", {qbd_box_unit:"Total :", qbd_formula:sumFormula, qbd_dw:sumDW, qbd_mc:sumMC, qbd_ww:sumWW, qbd_value:sumValue});
        }
   });
}

function formAwal(){
    $("#aded").val("");
    $("#qbh_id").val("");
    $("#qbh_date").val("");
    $("#qbh_shift").html("");
    $("#qbh_sub_plant").val("");
    $("#qbh_bm_no").html("");
    $("#qbh_body_code").html("");  
    $("#qbh_volume").val("");
    $("#divdetail").html("");
    $("#qbh_id, #qbh_date, #qbh_shift, #qbh_sub_plant,#qbh_bm_no, #qbh_body_code, #qbh_volume").attr('disabled',false);
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    $.post(frm+"?mode=cboshift", function(resp,stat){
        $("#aded").val("add");
        $("#qbh_id").val("OTOMATIS");
        $("#qbh_shift").html(resp);
        $("#qbh_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#qbh_id").val(o.qbh_id);
        $("#qbh_date").val(o.qbh_date);
        $("#qbh_shift").html(o.qbh_shift);
        $("#qbh_sub_plant").val(o.qbh_sub_plant);
        $("#qbh_bm_no").html(o.qbh_bm_no);
        $("#qbh_body_code").html(o.qbh_body_code);  
        $("#qbh_volume").val(o.qbh_volume);
        $("#divdetail").html(o.detailtabel);
        $("#qbh_id, #qbh_date, #qbh_shift, #qbh_sub_plant,#qbh_bm_no, #qbh_body_code, #qbh_volume").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#qbh_id").val(o.qbh_id);
        $("#qbh_date").val(o.qbh_date);
        $("#qbh_shift").html(o.qbh_shift);
        $("#qbh_sub_plant").val(o.qbh_sub_plant);
        $("#qbh_bm_no").html(o.qbh_bm_no);
        $("#qbh_body_code").html(o.qbh_body_code);  
        $("#qbh_volume").val(o.qbh_volume);
        $("#divdetail").html(o.detailtabel);
        $("#qbh_sub_plant, #qbh_date, #qbh_shift, #qbh_body_code").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function copyData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"copy",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("copy");
        $("#qbh_id").val(o.qbh_id);
        $("#qbh_date").val(o.qbh_date);
        $("#qbh_shift").html(o.qbh_shift);
        $("#qbh_sub_plant").val(o.qbh_sub_plant);
        $("#qbh_bm_no").html(o.qbh_bm_no);
        $("#qbh_body_code").html(o.qbh_body_code);  
        $("#qbh_volume").val(o.qbh_volume);
        $("#divdetail").html(o.detailtabel);
        $("#qbh_id, #qbh_date, #qbh_shift, #qbh_sub_plant, #qbh_body_code, #qbh_volume").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function hapusData(kode){
    var r = confirm("Hapus data penimbangan dengan id "+kode+"?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {kode:kode}, function(resp,stat){
            if (resp=="OK") {
                $.jgrid.gridUnload("#tblsm");
                tampilTabel("#tblsm","#pgrsm","body",$("#tglFrom").val()+"@"+$("#tglTo").val());
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
            qbh_sub_plant:{required:true},
            qbh_date:{required:true},
            qbh_shift:{required:true},
            qbh_bm_no:{required:true},
            qbh_volume:{required:true,digits:true},
            qbh_body_code:{required:true}
        };
    $('input[id^=qbd_value_]').each(function(index, el){
        var key = el.id.substr(10)
        if(el.value) {
            rulenya["qbd_box_unit["+key+"]"] = {required:true};
        }    
    });
    
    if(validator != "") {
        validator.destroy();
    }
    
    validator = $("#frEdit").validate({rules:rulenya});
    
    if($("#frEdit").valid()) {
        $("#qbh_id, #qbh_date, #qbh_shift, #qbh_sub_plant,#qbh_bm_no, #qbh_body_code, #qbh_volume").attr('disabled',false);
        $.post(frm+"?mode="+mode, $("#frEdit").serialize(), function(resp,stat){
          if (resp=="OK") {
            if (mode == "add" || mode == "copy") {
                alert("Data berhasil disimpan");
            } else {
                alert("Perubahan data "+$("#qbh_id").val()+" berhasil disimpan");
            }
            formAwal();
            $.jgrid.gridUnload("#tblsm");
            tampilTabel("#tblsm","#pgrsm","body",$("#tglFrom").val()+"@"+$("#tglTo").val());
          }else{
           alert(resp);
          }
        });
    } 
}

function tambahItem(tipe) {
    var barisLast = $("#barisLast").val();
    var jmlMaterial = $("#jmlMaterial").val();
    var jmlAdditive = $("#jmlAdditive").val();
    if(tipe == '1') {
        readonlydw = 'readonly';
        readonlyww = 'readonly';
    } else {
        readonlydw = '';
        readonlyww = '';
    }
    var baris = 0;    
    if (tipe == '1') {
        baris = parseInt(jmlMaterial) + 3;
        $("#jmlMaterial").val(parseInt(jmlMaterial) + 1);
    } else {
        baris = parseInt(jmlMaterial) + parseInt(jmlAdditive) + 4;
        $("#jmlAdditive").val(parseInt(jmlAdditive) + 1);
    }
    var table = document.getElementById("tabeldetail");
    var row = table.insertRow(baris);
    row.setAttribute("id", "trdet_ke_"+barisLast);
    $("#barisLast").val(parseInt(barisLast) + 1);
    var kol0 = row.insertCell(0);
    var kol1 = row.insertCell(1);
    var kol2 = row.insertCell(2);
    var kol3 = row.insertCell(3);
    var kol4 = row.insertCell(4);
    var kol5 = row.insertCell(5);
    var kol6 = row.insertCell(6);
    var kol7 = row.insertCell(7);
    var kol8 = row.insertCell(8);
    kol0.innerHTML = '<span class="glyphicon glyphicon-remove" onclick="hapusItem('+tipe+','+barisLast+')"></span>';
    kol1.innerHTML = '<input name="qbd_material_type['+barisLast+']" value="'+tipe+'" type="hidden"><select class="form-control input-sm klasbaris" name="qbd_material_code['+barisLast+']" id="qbd_material_code_'+barisLast+'"></select>';
    if (tipe == '1') {
        kol2.innerHTML = '<select class="form-control input-sm" name="qbd_box_unit['+barisLast+']" id="qbd_box_unit_'+barisLast+'"></select>';
    }
    kol3.innerHTML = '<input class="form-control input-sm" name="qbd_formula['+barisLast+']" id="qbd_formula_'+tipe+'_'+barisLast+'" type="text" onkeyup="hitungDwWw();">';
    kol4.innerHTML = '<input class="form-control input-sm" name="qbd_dw['+barisLast+']" id="qbd_dw_'+barisLast+'" type="text" '+readonlydw+'>';
    kol5.innerHTML = '<input class="form-control input-sm" name="qbd_mc['+barisLast+']" id="qbd_mc_'+barisLast+'" type="text" onkeyup="hitungDwWw();">';
    kol6.innerHTML = '<input class="form-control input-sm" name="qbd_ww['+barisLast+']" id="qbd_ww_'+barisLast+'" type="text" '+readonlydw+'>';
    kol7.innerHTML = '<input class="form-control input-sm" name="qbd_value['+barisLast+']" id="qbd_value_'+barisLast+'" type="text">';
    kol8.innerHTML = '<input class="form-control input-sm" name="qbd_remark['+barisLast+']" id="qbd_remark_'+barisLast+'" type="text">';
    var subplan = $('#qbh_sub_plant').val();
    $.post(frm+"?mode=additem", {subplan:subplan,tipe:tipe}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#qbd_material_code_"+barisLast).html(o.qbd_material_code);
        $("#qbd_box_unit_"+barisLast).html(o.qbd_box_unit);
        // $("#qbd_material_code_"+barisLast).select2();
    });
}

function hapusItem(tipe, baris) {
    var jmlMaterial = $("#jmlMaterial").val();
    var jmlAdditive = $("#jmlAdditive").val();
    if (tipe == '1'){
        $("#jmlMaterial").val(parseInt(jmlMaterial) - 1);
    } else {
        $("#jmlAdditive").val(parseInt(jmlAdditive) - 1);
    }
    $("#trdet_ke_"+baris).remove();
}

function hitungDwWw() {
    var volume = $('#qbh_volume').val();
    var grandformula = 0;
    $('input[id^=qbd_formula_1_]').each(function(index, el){
        if(el.value) {
            grandformula += parseFloat(el.value);
        }    
    });
    var volfor = volume / grandformula;
    $('input[id^=qbd_formula_]').each(function(index, el){
        var key = el.id.substr(14)
        if(el.value) {
            var mc = parseFloat($('#qbd_mc_'+key).val());
            var dw = (el.value/grandformula)*volume;
            // var ww = dw / ((100-mc) / 100)
            var ww = (el.value/(grandformula-mc))*volume;
            $('#qbd_dw_'+key).val(dw.toFixed(0));
            $('#qbd_ww_'+key).val(ww.toFixed(0));
        }  
    });
}

function hanyanumerik(id, value) {
    if (/([^.0123456789]|)/g.test(value)) { 
        $("#"+id).val(value.replace(/([^.0123456789])/g, ''));
    }
} 
    
$(document).ready(function () {
    var ubahUkuranJqGrid = function(){
        var vukur = $('#kontensm').width(); 
        if (vukur <= 800){
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

    $("#qbh_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-8d'
    });

    tampilTabel("#tblsm","#pgrsm","body",$("#tglFrom").val()+"@"+$("#tglTo").val());

    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#qbh_sub_plant").html(resp);
        var subplan = $('#qbh_sub_plant').val();
        $.post(frm+"?mode=cboballmill", {subplan:subplan}, function(resp,stat){
            $("#qbh_bm_no").html(resp);  
        });
    });
    
    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm","body",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });

    $('#qbh_sub_plant').change(function(){
        var subplan = this.value;
        $.post(frm+"?mode=cboballmill", {subplan:subplan}, function(resp,stat){
            $("#qbh_bm_no").html(resp);  
        });
    });

    $('#qbh_bm_no').change(function(){
        var aded = $('#aded').val();
        if (aded == "add") {
            var subplan = $('#qbh_sub_plant').val();
            var kode = this.value;
            $.post(frm+"?mode=txtkapasitas", {subplan:subplan,kode:kode}, function(resp,stat){
                $("#qbh_volume").val(resp);  
            });
            $.post(frm+"?mode=cbokodebody", {subplan:subplan}, function(resp,stat){
                $("#qbh_body_code").html(resp);  
            });
        }
    });

    $('#qbh_body_code').change(function(){
        var subplan = $('#qbh_sub_plant').val();
        var kode = this.value;
        var volume = $("#qbh_volume").val();
        $.post(frm+"?mode=detailtabel", {stat:"add",subplan:subplan,kode:kode,volume:volume}, function(resp,stat){
            var o = JSON.parse(resp);
            $("#divdetail").html(o.detailtabel);
        });
    });

    $("#qbh_volume").afDigitOnly();

    $("#qbh_volume").keyup(function(){
        hitungDwWw();
    });    

});
</script>