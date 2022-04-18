<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['51'];
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
                <form class="form-horizontal">
                    <div class="form-group" id="frCari">
                        <label class="col-sm-1 control-label" style="text-align:left;">Periode</label>
                        <div class="col-sm-2" style="margin-top:3px;">
                            <select class="form-control input-sm" id="cmbBulan">
                                <option value="All">All</option>
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                        <div class="col-sm-2" style="margin-top:3px;">
                            <select class="form-control input-sm" id="cmbTahun">
                                <option>All</option>
                                <option>2004</option>
                                <option>2005</option>
                                <option>2006</option>
                                <option>2007</option>
                                <option>2008</option>
                                <option>2009</option>
                                <option>2010</option>
                                <option>2011</option>
                                <option>2012</option>
                                <option>2013</option>
                                <option>2014</option>
                                <option>2015</option>
                                <option>2016</option>
                                <option>2017</option>
                                <option>2018</option>
                                <option>2019</option>
                                <option>2020</option>
                                <option>2021</option>
                                <option>2022</option>
                            </select>
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
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">NOMOR</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="qex_id" id="qex_id" readonly>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">TANGGAL</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="qex_date" id="qex_date">
                        </div>      
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" name="qex_sub_plant" id="qex_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">JAM</label>
                        <div class="col-sm-4 bootstrap-timepicker" style="margin-top:3px;">
                            <select class="form-control input-sm" name="qex_time" id="qex_time">
                                <option>08:00</option>
                                <option>09:00</option>
                                <option>10:00</option> 
                                <option>11:00</option> 
                                <option>12:00</option> 
                                <option>13:00</option> 
                                <option>14:00</option> 
                                <option>15:00</option> 
                                <option>16:00</option> 
                                <option>17:00</option> 
                                <option>18:00</option> 
                                <option>19:00</option> 
                                <option>20:00</option> 
                                <option>21:00</option> 
                                <option>22:00</option> 
                                <option>23:00</option> 
                                <option>00:00</option>
                            </select>  
                        </div>     
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">LINE</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="qex_line" name="qex_line"></select>   
                        </div>
                         <label class="col-sm-2 control-label" style="text-align:left;">SERI</label>
                        <div class="col-sm-4 bootstrap-timepicker" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="qex_seri" id="qex_seri">   
                        </div> 
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">MOTIF</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" name="qex_motif" id="qex_motif"></select>
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">SHADING</label>
                        <div class="col-sm-4 bootstrap-timepicker" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="qex_shading" id="qex_shading">   
                        </div>     
                    </div>
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align:left;"></label>     
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">EXPORT (m2)</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm text-right" type="text" name="qex_exp" id="qex_exp" onkeyup="hanyanumerik(this.id,this.value);hitungTotalKw();">
                        </div>  
                    </div>
                    
                    <div class="table-responsive" id="divdetail"></div>
                </form>    
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/ad_input.inc.php";
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
            {label:"ID", name:'qex_id', index:'qex_id', width:80},
            {label:"SUBPLANT", name:'qex_sub_plant', index:'qex_sub_plant', width:70, stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"LINE", name:'qex_line', index:'qex_line', width:80},
            {label:"TANGGAL", name:'qex_date', index:'qex_date', width:80},
            {label:"JAM", name:'qex_time', index:'qex_time', width:70},
            {label:"MOTIF", name:'qex_motif', index:'qex_motif', width:80},
            {label:"SERI", name:'qex_seri', index:'qex_seri', width:80},
            {label:"SHADING", name:'qex_shading', index:'qex_shading', width:80},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"qex_sub_plant asc,qex_date desc,qex_id",
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
        //     var vid = $self.jqGrid("getCell", parentRowKey, "qex_id");
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
        postData:{'qex_id':pId},
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
    $("#qex_id").val("");
    $("#qex_date").val("");
    $("#qex_time").val("");
    $("#qex_sub_plant").val("");
    $("#qex_line").html("");
    $("#qex_motif").val("");
    $("#qex_seri").val("");
    $("#qex_shading").val("");
    $("#qex_exp").val("");
    $("#divdetail").html("");
    $("#qex_id, #qex_date, #qex_time, #qex_sub_plant, #qex_line, #qex_motif, #qex_seri, #qex_shading").attr('disabled',false);
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    $("#aded").val("add");
    $("#qex_id").val("OTOMATIS");
    $("#qex_date").val(moment().format("DD-MM-YYYY"));
    $("#qex_time").val(moment().format("HH:00"));
    $.post(frm+"?mode=detailtabel", {stat:"add"}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#divdetail").html(o.detailtabel);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#qex_id").val(o.qex_id);
        $("#qex_date").val(o.qex_date);
        $("#qex_time").val(o.qex_time);
        $("#qex_sub_plant").val(o.qex_sub_plant);
        $("#qex_line").html(o.qex_line);
        $("#qex_motif").val(o.qex_motif);
        $("#qex_seri").val(o.qex_seri);
        $("#qex_shading").val(o.qex_shading);
        $("#qex_exp").val(o.qex_exp);
        $("#divdetail").html(o.detailtabel);
        $("#qex_date, #qex_time, #qex_sub_plant, #qex_line, #qex_motif, #qex_seri, #qex_shading, #qex_exp").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#qex_id").val(o.qex_id);
        $("#qex_date").val(o.qex_date);
        $("#qex_time").val(o.qex_time);
        $("#qex_sub_plant").val(o.qex_sub_plant);
        $("#qex_line").html(o.qex_line);
        $("#qex_motif").val(o.qex_motif);
        $("#qex_seri").val(o.qex_seri);
        $("#qex_shading").val(o.qex_shading);
        $("#qex_exp").val(o.qex_exp);
        $("#divdetail").html(o.detailtabel);
        // $("#qex_sub_plant").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function hapusData(kode){
    var r = confirm("Batalkan data analisa defect dengan id "+kode+"?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {kode:kode}, function(resp,stat){
            if (resp=="OK") {
                $.jgrid.gridUnload("#tblsm");
                tampilTabel("#tblsm","#pgrsm","All-"+moment().format("YYYY"));
            } else {
                alert(resp);
            }  
        });
    } else {
        return false;
    }
}

function simpanData(mode) {
    var defectKode = [];
    $('select[id^=qec_defect_kode_]').each(function(index, el){
        if(el.value) {
            defectKode.push(el.value);
        }
    });
    
    if(cekNilaiDuplikat(defectKode) == 'ADA') {
        alert('Anda memilih jenis defect yang sama, mohon periksa kembali.');
        return false;
    } else {
        var rulenya = {
            qex_sub_plant:{required:true},
            qex_date:{required:true},
            qex_time:{required:true},
            qex_line:{required:true},
            qex_motif:{required:true}
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
                    alert("Perubahan data "+$("#qex_id").val()+" berhasil disimpan");
                }
                formAwal();
                $.jgrid.gridUnload("#tblsm");
                tampilTabel("#tblsm","#pgrsm","All-"+moment().format("YYYY"));
              }else{
               alert(resp);
              }
            });
        }
    }
}


function hanyanumerik(id, value) {
    if (/([^.0123456789]|)/g.test(value)) { 
        $("#"+id).val(value.replace(/([^.0123456789])/g, ''));
    }
}

function hitungTotalEco() {
    var total = 0;
    $('input[id^=qec_m2_]').each(function(index, el){
        if(el.value) {
            total += parseFloat(el.value);
        }    
    });
    $('#qex_eco').val(total);  
}

function hitungTotalKw() {
    var total = 0;
    $('input[id^=qkw_m2_]').each(function(index, el){
        if(el.value) {
            total += parseFloat(el.value);
        }    
    });
    $('#qex_kw').val(total);  
}

function tambahItem() {
    var barisLast = $("#barisLast").val();
    var baris = parseInt(barisLast) + 1;
    var table = document.getElementById("tabeldetail");
    var row = table.insertRow(baris);
    row.setAttribute("id", "trdet_ke_"+barisLast);
    $("#barisLast").val(parseInt(barisLast) + 1);
    var kol0 = row.insertCell(0);
    kol0.innerHTML = '<span class="glyphicon glyphicon-remove" onclick="hapusItem('+barisLast+')"></span>';
}

function cekNilaiDuplikat(arr) {
    var x;
    for(var i=0;i<arr.length;i++){
        x = arr[i];
        for(var j=i+1;j<arr.length;j++){
            if(x==arr[j]){return 'ADA'}
        }
    }
    return '';
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
    
    // $("#cmbBulan").val(moment().format("M"));
    
    $("#cmbTahun").val(moment().format("YYYY"));
    
    // $("#qex_date").datepicker({
    //     autoclose:true,
    //     format:'dd-mm-yyyy',
    //     endDate:'date'
    // });

    $('#qex_date').datebox({
        mode:"calbox",
        overrideDateFormat:"%d-%m-%Y",
        beforeToday:true
    });

    tampilTabel("#tblsm","#pgrsm","All-"+moment().format("YYYY"));
    
    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#qex_sub_plant").html(resp);
        var subplan = $('#qex_sub_plant').val();
        $.post(frm+"?mode=cboline", {subplan:subplan}, function(resp,stat){
            $("#qex_line").html(resp);  
        });
    });

    $.post(frm+"?mode=cbomotif", function(resp,stat){
        $("#qex_motif").html(resp);
    });

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#cmbBulan").val()+"-"+$("#cmbTahun").val());
    });

    $('#qex_sub_plant').change(function(){
        var subplan = this.value;
        $.post(frm+"?mode=cboline", {subplan:subplan}, function(resp,stat){
            $("#qex_line").html(resp);
            $("#qex_motif").val("");
            $("#qex_seri").val("");
            $("#qex_shading").val("");  
        });
    });

    $('#qex_line').change(function(){
        var subplan = $('#qex_sub_plant').val();
        var line = this.value;
        $.post(frm+"?mode=cariprevqcdaily", {subplan:subplan,line:line}, function(resp,stat){
            var o = JSON.parse(resp);
            $("#qex_motif").val(o.qex_motif);
            $("#qex_seri").val(o.qex_seri);
            $("#qex_shading").val(o.qex_shading);
        });
    });
});
</script>