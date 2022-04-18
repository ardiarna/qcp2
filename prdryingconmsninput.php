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
                    <input class="form-control input-sm" type="hidden" name="cmh_id" id="cmh_id" readonly>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="cmh_sub_plant" name="cmh_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">TANGGAL</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="cmh_date" id="cmh_date">
                        </div>      
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">MESIN PRESS NOMOR</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" id="cmh_press" name="cmh_press"></select>
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">JAM</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="cmh_time" name="cmh_time">
                                <option value=""></option>
                                <option value="08:00">08:00</option>
                                <option value="11:00">11:00</option>
                                <option value="14:00">14:00</option>
                                <option value="17:00">17:00</option>
                                <option value="20:00">20:00</option>
                                <option value="22:00">22:00</option>
                                <option value="24:00">24:00</option>
                                <option value="03:00">03:00</option>
                                <option value="06:00">06:00</option>
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
var frm = "include/prdryingconmsninput.inc.php";
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
            {label:"SUBPLANT", name:'cmh_sub_plant', index:'cmh_sub_plant', width:70, align:'center', stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"ID", name:'cmh_id', index:'cmh_id', width:80, align:'center'},
            {label:"TANGGAL", name:'cmh_date', index:'cmh_date', width:50, align:'center'},
            {label:"JAM", name:'time', index:'time', width:40, align:'center'},
            {label:"NO. PRESS", name:'cmh_press', index:'cmh_press', width:40, align:'center'},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"cmh_sub_plant asc, cmh_date desc, cmh_press",
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
    $("#cmh_id").val("");
    $("#cmh_date").val("");
    $("#cmh_time").val("");
    $("#cmh_press").html("");
    $("#divdetail").html("");
    $("#cmh_id, #cmh_date, #cmh_press, #cmh_sub_plant, #cmh_time").attr('disabled',false);
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    var subplan = $('#cmh_sub_plant').val();
    $.post(frm+"?mode=detailtabel", {stat:"add",subplan:subplan}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#divdetail").html(o.detailtabel);
        $("#aded").val("add");
        $("#cmh_id").val("OTOMATIS");
        $("#cmh_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
        $("#boxAwal").hide();
        $("#boxEdit").show();  
        $("#cmh_sub_plant").val("");    
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#cmh_id").val(o.cmh_id);
        $("#cmh_date").val(o.cmh_date);
        $("#cmh_time").val(o.cmh_time);
        $("#cmh_press").html(o.cmh_press);
        $("#cmh_sub_plant").val(o.cmh_sub_plant);
        $("#divdetail").html(o.detailtabel);
        $("#cmh_id, #cmh_date, #cmh_press, #cmh_sub_plant, #cmh_time").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#cmh_id").val(o.cmh_id);
        $("#cmh_date").val(o.cmh_date);
        $("#cmh_time").val(o.cmh_time);
        $("#cmh_press").html(o.cmh_press);
        $("#cmh_sub_plant").val(o.cmh_sub_plant);
        $("#divdetail").html(o.detailtabel);
        $("#cmh_sub_plant, #cmh_date, #cmh_press, #cmh_time").attr('disabled',true);
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
            cmh_sub_plant:{required:true},
            cmh_date:{required:true},
            cmh_press:{required:true},
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
                alert("Perubahan data "+$("#cmh_id").val()+" berhasil disimpan");
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

function hitungTotalStokPow($idd) {
    var total = 0;
    $('input.stokpow'+$idd).each(function(index, el){
        if(el.value) {
            total += parseFloat(el.value);
        }    
    });
    $('#tot_stokpow'+$idd).val(total);
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

    $("#cmh_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-1d'
    });

    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    

    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#cmh_sub_plant").html(resp);
    });

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });

    $('#cmh_sub_plant').change(function(){
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