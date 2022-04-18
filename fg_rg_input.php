<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['94'];
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
                    <input class="form-control input-sm" type="hidden" name="rg_id" id="rg_id" readonly>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">Subplant</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="rg_sub_plant" name="rg_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">Tanggal</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="rg_date" id="rg_date" readonly>
                        </div>      
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">Line</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="rg_line" name="rg_line">
                                <option value=""></option>
                                <option value="01">01</option>
                                <option value="02">02</option>
                                <option value="03">03</option>
                                <option value="04">04</option>
                                <option value="05">05</option>
                                <option value="06">06</option>
                                <option value="07">07</option>
                            </select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">Jam</label>
                        <div class="col-sm-4 bootstrap-timepicker" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="rg_time" id="rg_time">   
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">Motive</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="rg_motif" name="rg_motif"></select>   
                        </div>
                    </div>
                    <div class="table-responsive" id="divdetail"></div>
                </form>    
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/fg_rg_input.inc.php";
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
            {label:"SUBPLANT", name:'rg_sub_plant', index:'rg_sub_plant', width:70, align:'center', stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"ID", name:'rg_id', index:'rg_id', width:80, align:'center'},
            {label:"TANGGAL", name:'date', index:'date', width:50, align:'center'},
            {label:"JAM", name:'time', index:'time', width:40, align:'center'},
            {label:"LINE", name:'rg_line', index:'rg_line', width:40, align:'center'},
            {label:"MOTIVE", name:'rg_motif', index:'rg_motif', width:80},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"rg_sub_plant asc, rg_date desc, rg_line",
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
    $("#rg_id").val("");
    $("#rg_date").val("");
    $("#rg_time").val("");
    $("#rg_motif").val("");
    $("#divdetail").html("");
    $("#rg_sub_plant, #rg_date, #rg_line, #rg_time, #rg_motif").attr('disabled',false);
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    var subplan = $('#rg_sub_plant').val();
    $.post(frm+"?mode=detailtabel", {stat:"add",subplan:subplan}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#divdetail").html(o.detailtabel);
        $("#aded").val("add");
        $("#rg_id").val("OTOMATIS");
        $("#rg_sub_plant").val("");
        $("#rg_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
        $("#boxAwal").hide();
        $("#boxEdit").show();
        tambahItem('ekonomi');
        tambahItem('ekonomi');
        tambahItem('ekonomi');
        tambahItem('ekonomi');
        tambahItem('rijsor');
        tambahItem('rijsor');
        tambahItem('rijpal');
        tambahItem('rijbua');
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#rg_id").val(o.rg_id);
        $("#rg_date").val(o.rg_date);
        $("#rg_time").val(o.rg_time);
        $("#rg_line").val(o.rg_line);
        $("#rg_motif").val(o.rg_motif);
        $("#rg_sub_plant").val(o.rg_sub_plant);
        $("#divdetail").html(o.detailtabel);
        $("#rg_sub_plant, #rg_date, #rg_line, #rg_time").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#rg_id").val(o.rg_id);
        $("#rg_date").val(o.rg_date);
        $("#rg_time").val(o.rg_time);
        $("#rg_line").val(o.rg_line);
        $("#rg_motif").val(o.rg_motif);
        $("#rg_sub_plant").val(o.rg_sub_plant);
        $("#divdetail").html(o.detailtabel);
        $("#rg_sub_plant, #rg_date, #rg_line, #rg_time").attr('disabled',true);
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
            rg_sub_plant:{required:true},
            rg_date:{required:true},
            cmh_kiln:{required:true},
            cmh_time:{required:true},
            // rg_motif:{required:true},
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
                alert("Perubahan data "+$("#rg_id").val()+" berhasil disimpan");
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

function tambahItem(jenis) {
    $.post(frm+"?mode=cbodefect", function(resp,stat){
        var jml_ekonomi = $("#jml_ekonomi").val();
        var jml_rijsor = $("#jml_rijsor").val();
        var jml_rijpal = $("#jml_rijpal").val();
        var jml_rijbua = $("#jml_rijbua").val();
        var table = document.getElementById("tblitem");
        if(jenis == 'ekonomi') {
            jml = jml_ekonomi;
            barisnya = 3 + parseInt(jml_ekonomi);
            $("#jml_ekonomi").val(parseInt(jml)+1);
        } else if(jenis == 'rijsor') {
            jml = jml_rijsor;
            barisnya = 4 + parseInt(jml_ekonomi) + parseInt(jml_rijsor);
            $("#jml_rijsor").val(parseInt(jml)+1);
        } else if(jenis == 'rijpal') {
            jml = jml_rijpal;
            barisnya = 5 + parseInt(jml_ekonomi) + parseInt(jml_rijsor) + parseInt(jml_rijpal);
            $("#jml_rijpal").val(parseInt(jml)+1);
        } else if(jenis == 'rijbua') {
            jml = jml_rijbua;
            barisnya = 6 + parseInt(jml_ekonomi) + parseInt(jml_rijsor) + parseInt(jml_rijpal) + parseInt(jml_rijbua);
            $("#jml_rijbua").val(parseInt(jml)+1);
        }
        var row = table.insertRow(barisnya);
        var kol0 = row.insertCell(0);
        var kol1 = row.insertCell(1);
        var kol2 = row.insertCell(2);
        kol1.innerHTML = '<select class="form-control input-sm" id="defect_kode_'+jenis+'_'+jml+'" name="defect_kode_'+jenis+'['+jml+']"></select>';
        kol2.innerHTML = '<input class="form-control input-sm text-right" type="text" id="per_2h_'+jenis+'_'+jml+'" name="per_2h_'+jenis+'['+jml+']" onkeyup="hanyanumerik(this.id,this.value);hitungTotal(\''+jenis+'\');" placeholder="QTY">';
        $("#defect_kode_"+jenis+'_'+jml).html(resp);
    });
}

function hitungTotal(jenis) {
    var total = 0;
    $('input[id^=per_2h_'+jenis+'_]').each(function(index, el){
        if(el.value) {
            total += parseFloat(el.value);
        }    
    });
    $('#qty_'+jenis).val(total);  
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

    $("#rg_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-1d'
    });

    $('#rg_time').timepicker({
      showInputs: false,
      showMeridian: false,
      minuteStep: 60
    });

    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    

    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#rg_sub_plant").html(resp);
    });

    $.post(frm+"?mode=cbomotif", function(resp,stat){
        $("#rg_motif").html(resp);
    });

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });

});
</script>