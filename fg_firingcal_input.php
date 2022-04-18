<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['91'];
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
                    <input class="form-control input-sm" type="hidden" name="fh_id" id="fh_id" readonly>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="fh_sub_plant" name="fh_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">TANGGAL</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="fh_date" id="fh_date" readonly>
                        </div>      
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SHIFT</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" id="fh_shift" name="fh_shift"></select>
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">KILN</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" id="fh_kiln" name="fh_kiln">
                                <option value=""></option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive" id="divdetail"></div>
                </form>    
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/fg_firingcal_input.inc.php";
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
            {label:"SUBPLANT", name:'fh_sub_plant', index:'fh_sub_plant', width:70, align:'center', stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"ID", name:'fh_id', index:'fh_id', width:80, align:'center'},
            {label:"TANGGAL", name:'fh_date', index:'fh_date', width:80, align:'center'},
            {label:"SHIFT", name:'fh_shift', index:'fh_shift', width:70, align:'center'},
            {label:"KILN", name:'fh_kiln', index:'fh_kiln', width:80, align:'center'},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"fh_date desc,fh_sub_plant asc,fh_id",
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
    $("#fh_id").val("");
    $("#fh_date").val("");
    $("#fh_shift").html("");
    $("#fh_sub_plant").val("");
    $("#divdetail").html("");
    $("#fh_id, #fh_date, #fh_shift, #fh_sub_plant, #fh_kiln").attr('disabled',false);
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    $.post(frm+"?mode=cboshift", function(resp,stat){
        $("#aded").val("add");
        $("#fh_id").val("OTOMATIS");
        $("#fh_shift").html(resp);
        $("#fh_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#fh_id").val(o.fh_id);
        $("#fh_date").val(o.fh_date);
        $("#fh_shift").html(o.fh_shift);
        $("#fh_sub_plant").val(o.fh_sub_plant);
        $("#fh_kiln").val(o.fh_kiln);
        $("#divdetail").html(o.detailtabel);
        $("#fh_id, #fh_date, #fh_shift, #fh_sub_plant, #fh_kiln").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#fh_id").val(o.fh_id);
        $("#fh_date").val(o.fh_date);
        $("#fh_shift").html(o.fh_shift);
        $("#fh_sub_plant").val(o.fh_sub_plant);
        $("#fh_kiln").val(o.fh_kiln);
        $("#divdetail").html(o.detailtabel);
        $("#fh_sub_plant, #fh_date, #fh_shift, #fh_kiln").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function hapusData(kode){
    var r = confirm("Batalkan data input dengan id "+kode+"?");
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
            fh_sub_plant:{required:true},
            fh_date:{required:true},
            fh_shift:{required:true},
            fh_kiln:{required:true}
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
                alert("Perubahan data "+$("#fh_id").val()+" berhasil disimpan");
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

    $("#fh_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-1d'
    });

    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    
    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#fh_sub_plant").html(resp);
    });

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });

    $('#fh_sub_plant').change(function(){
        var subplan = this.value;
        if($("#aded").val() == "add") {
            var press = this.value;
            $.post(frm+"?mode=detailtabel", {stat:"add",subplan:subplan}, function(resp,stat){
                var o = JSON.parse(resp);
                $("#divdetail").html(o.detailtabel);      
            });
        }

    });
});
</script>