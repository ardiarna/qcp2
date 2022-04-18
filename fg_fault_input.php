<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['85'];
?>
<style type="text/css">
    #tblsm,
    .ui-jqgrid-htable {
        font-size:11px;
   }
    th {
      text-align:center;
   }     
select {
  font-family: 'FontAwesome', 'sans-serif';
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
                    <input class="form-control input-sm" type="hidden" name="fgf_id" id="fgf_id" readonly>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">Subplant</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="fgf_sub_plant" name="fgf_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">Tanggal</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="fgf_date" id="fgf_date" readonly>
                        </div>      
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">kiln</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="fgf_kiln" name="fgf_kiln">
                                <option></option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">Jam</label>
                        <div class="col-sm-4 bootstrap-timepicker" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="fgf_time" id="fgf_time">   
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">Export Quality (%)</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input type="number" name="fgf_quality" id="fgf_quality" class="form-control input-sm" min="0" max="100">
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">Motive / Type</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input type="text" name="fgf_type" id="fgf_type" class="form-control input-sm">
                        </div>
                    </div>
                    <div class="table-responsive" id="divdetail"></div>
                </form>    
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/fg_fault_input.inc.php";
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
            {label:"SUBPLANT", name:'fgf_sub_plant', index:'fgf_sub_plant', width:70, align:'center', stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"ID", name:'fgf_id', index:'fgf_id', width:80, align:'center'},
            {label:"TANGGAL", name:'date', index:'date', width:50, align:'center'},
            {label:"JAM", name:'time', index:'time', width:40, align:'center'},
            {label:"KILN", name:'fgf_kiln', index:'fgf_kiln', width:40, align:'center'},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"fgf_sub_plant asc, fgf_date desc, fgf_kiln",
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

function formAwal(){
    $("#aded").val("");
    $("#fgf_id").val("");
    $("#fgf_date").val("");
    $("#fgf_quality").val("");
    $("#fgf_time").val("");
    $("#fgf_type").val("");
    $("#divdetail").html("");
    $("#fgf_sub_plant, #fgf_date, #fgf_kiln, #fgf_time").attr('disabled',false);
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    var subplan = $('#fgf_sub_plant').val();
    $.post(frm+"?mode=detailtabel", {stat:"add",subplan:subplan}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#divdetail").html(o.detailtabel);
        $("#aded").val("add");
        $("#fgf_id").val("OTOMATIS");
        $("#fgf_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
        $("#boxAwal").hide();
        $("#boxEdit").show();  
        $("#fgf_sub_plant").val("");    
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#fgf_id").val(o.fgf_id);
        $("#fgf_date").val(o.fgf_date);
        $("#fgf_time").val(o.fgf_time);
        $("#fgf_kiln").val(o.fgf_kiln);
        $("#fgf_sub_plant").val(o.fgf_sub_plant);
        $("#fgf_quality").val(o.fgf_quality);
        $("#fgf_type").val(o.fgf_type);
        $("#divdetail").html(o.detailtabel);
        $("#fgf_sub_plant, #fgf_date, #fgf_kiln, #fgf_time").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#fgf_id").val(o.fgf_id);
        $("#fgf_date").val(o.fgf_date);
        $("#fgf_time").val(o.fgf_time);
        $("#fgf_kiln").val(o.fgf_kiln);
        $("#fgf_sub_plant").val(o.fgf_sub_plant);
        $("#fgf_quality").val(o.fgf_quality);
        $("#fgf_type").val(o.fgf_type);
        $("#divdetail").html(o.detailtabel);
        $("#fgf_sub_plant, #fgf_date, #fgf_kiln, #fgf_time").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function hapusData(kode){
    var r = confirm("Hapus Data id "+kode+"?");
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
            fgf_sub_plant:{required:true},
            fgf_date:{required:true},
            cmh_kiln:{required:true},
            cmh_time:{required:true},
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
            } else {
                alert("Perubahan data "+$("#fgf_id").val()+" berhasil disimpan");
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


function hanyanumerik(id, value) {
    if (/([^.0123456789]|)/g.test(value)) { 
        $("#"+id).val(value.replace(/([^.0123456789])/g, ''));
    }
}

function hitungTotalStokPow($idd) {
    var total = 0;
    $('input.stokpow'+$idd).each(function(index, el){
        if(el.value) {
            total += parseFloat(el.value);
        }    
    });
    $('#tot_stokpow'+$idd).val(total);
} 

function hitungTotal($jns) {
    var total = 0;
    $('input.'+$jns).each(function(index, el){
        if(el.value) {
            total += parseFloat(el.value);
        }    
    });
    $('#tot_'+$jns).val(total);
    // alert($idd);
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

    $("#fgf_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-1d'
    });

    $('#fgf_time').timepicker({
      showInputs: false,
      showMeridian: false,
      minuteStep: 60
    });

    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    

    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#fgf_sub_plant").html(resp);
    });

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });

    $('#fgf_sub_plant').change(function(){
        var subplan = this.value;
        $.post(frm+"?mode=cbopress", {subplan:subplan}, function(resp,stat){
            $("#cmh_press").html(resp);  
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