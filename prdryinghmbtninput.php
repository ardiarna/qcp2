<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['67'];
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
                    <input class="form-control input-sm" type="hidden" name="hph_id" id="hph_id" readonly>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="hph_sub_plant" name="hph_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">TANGGAL</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="hph_date" id="hph_date">
                        </div>      
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">LINE</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" id="hph_line" name="hph_line"></select>
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">SHIFT</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" id="hph_shift" name="hph_shift"></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">MESIN PRESS NOMOR</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" id="hph_press" name="hph_press"></select>
                        </div>
                    </div>
                    <div class="table-responsive" id="divdetail"></div>
                </form>    
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/prdryinghmbtninput.inc.php";
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
            {label:"SUBPLANT", name:'hph_sub_plant', index:'hph_sub_plant', width:70, align:'center', stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"ID", name:'hph_id', index:'hph_id', width:80, align:'center'},
            {label:"TANGGAL", name:'hph_date', index:'hph_date', width:50, align:'center'},
            {label:"SHIFT", name:'hph_shift', index:'hph_shift', width:40, align:'center'},
            {label:"LINE", name:'hph_line', index:'hph_line', width:40, align:'center'},
            {label:"NO. PRESS", name:'hph_press', index:'hph_press', width:40, align:'center'},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"hph_sub_plant asc, hph_date desc, hph_shift asc, hph_line asc, hph_press",
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
    $("#hph_id").val("");
    $("#hph_date").val("");
    $("#hph_line").val("");
    $("#hph_shift").val("");
    $("#hph_press").html("");
    $("#divdetail").html("");
    $("#hph_id, #hph_date, #hph_line, #hph_shift, #hph_press, #hph_sub_plant").attr('disabled',false);
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    var subplan = $('#hph_sub_plant').val();
    $.post(frm+"?mode=detailtabel", {stat:"add",subplan:subplan}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#divdetail").html(o.detailtabel);
        $("#aded").val("add");
        $("#hph_id").val("OTOMATIS");
        $("#hph_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
        $("#boxAwal").hide();
        $("#boxEdit").show();  
        $("#hph_sub_plant").val("");    
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#hph_id").val(o.hph_id);
        $("#hph_date").val(o.hph_date);
        $("#hph_line").html(o.hph_line);
        $("#hph_shift").html(o.hph_shift);
        $("#hph_press").html(o.hph_press);
        $("#hph_sub_plant").val(o.hph_sub_plant);
        $("#divdetail").html(o.detailtabel);
        $("#hph_id, #hph_date, #hph_line, #hph_shift, #hph_press, #hph_sub_plant").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#hph_id").val(o.hph_id);
        $("#hph_date").val(o.hph_date);
        $("#hph_line").html(o.hph_line);
        $("#hph_shift").html(o.hph_shift);
        $("#hph_press").html(o.hph_press);
        $("#hph_sub_plant").val(o.hph_sub_plant);
        $("#divdetail").html(o.detailtabel);
        $("#hph_date, #hph_line, #hph_shift, #hph_press, #hph_sub_plant").attr('disabled',true);
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
            hph_sub_plant:{required:true},
            hph_date:{required:true},
            hph_line:{required:true},
            hph_shift:{required:true},
            hph_press:{required:true},
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
                alert("Perubahan data "+$("#hph_id").val()+" berhasil disimpan");
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

    $("#hph_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-1d'
    });

    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    

    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#hph_sub_plant").html(resp);
    });

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });

    $('#hph_sub_plant').change(function(){
        var subplan = this.value;
        $.post(frm+"?mode=cbopress", {subplan:subplan}, function(resp,stat){
            $("#hph_press").html(resp);  
        });
        $.post(frm+"?mode=cboline", {subplan:subplan}, function(resp,stat){
            $("#hph_line").html(resp);  
        });   
        $.post(frm+"?mode=cboshift", {}, function(resp,stat){
            $("#hph_shift").html(resp);  
        });      
        

        if($("#aded").val() == "add") {
            $.post(frm+"?mode=detailtabel", {stat:"add",subplan:subplan}, function(resp,stat){
                var o = JSON.parse(resp);
                $("#divdetail").html(o.detailtabel);      
            });
        }
    });
});



function tambahItem() {
    var barisLast = $("#barisLast").val();
    var baris = 0;
    var baris = parseInt(barisLast) + 1;  

    var table = document.getElementById("tabeldetail");

    var row = table.insertRow(baris);
    row.setAttribute("id", "trdet_ke_"+barisLast);
    $("#barisLast").val(parseInt(barisLast) + 1);
    var kol0 = row.insertCell(0);
    var kol1 = row.insertCell(1);
    var kol2 = row.insertCell(2);
    var kol3 = row.insertCell(3);
    var kol4 = row.insertCell(4);
    kol0.innerHTML = '<a href="javascript:void(0)" class="btn btn-default btn-xs" onclick="hapusItem('+barisLast+')"><span class="glyphicon glyphicon-remove"></span></a>';
    kol1.innerHTML = '<input class="form-control input-sm" name="hpd_date_start['+barisLast+']" id="hpd_date_start_'+barisLast+'" type="time">';
    kol2.innerHTML = '<input class="form-control input-sm" name="hpd_date_stop['+barisLast+']" id="hpd_date_stop_'+barisLast+'" type="time" onchange="hitungDwWw('+barisLast+');">';
    kol3.innerHTML = '<input class="form-control input-sm" name="hpd_jml_menit['+barisLast+']" id="hpd_jml_menit_'+barisLast+'" readonly>';
    kol4.innerHTML = '<textarea class="form-control" rows="3" name="hpd_value['+barisLast+']" id="hpd_value_['+barisLast+']"></textarea>';
}

function hapusItem(baris) {
    $("#trdet_ke_"+baris).remove();
    var barisLast2 = $("#barisLast").val();
    $("#barisLast").val(parseInt(barisLast2) - 1);
}

function hitungDwWw($idbaris) {

    var start  = $('#hpd_date_start_'+$idbaris).val();
    var stop   = $('#hpd_date_stop_'+$idbaris).val();

    $.post(frm+"?mode=htgmenit", {start:start,stop:stop}, function(resp,stat){
        $("#hpd_jml_menit_"+$idbaris).val(resp);  
    });  
}

</script>