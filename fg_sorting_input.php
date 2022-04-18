<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['88'];
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
            <div class="modal fade" id="modalMotif" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header alert-info">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Daftar Motif</h4>
                        </div>
                        <input type="hidden" name="idx_motif" id="idx_motif">
                        <div class="modal-body table-responsive">
                            <div class="input-group">
                                <input class="form-control input-sm" id="txt_cari" type="text" placeholder="Masukkan code motif">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-sm btn-info" onclick="pilihMotif();">Go</button>
                                </span>
                            </div>
                            <br>
                            <div id="isiMdlMotif" style="display:block;max-height:500px;overflow-y:auto;-ms-overflow-style:-ms-autohiding-scrollbar;">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
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
                    <input class="form-control input-sm" type="hidden" name="sp_id" id="sp_id" readonly>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="sp_sub_plant" name="sp_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">LINE</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="sp_line" name="sp_line">
                                <option value=""></option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>   
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SHIFT</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" id="sp_shift" name="sp_shift">
                                <option value=""></option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select> 
                        </div>

                        <label class="col-sm-2 control-label" style="text-align:left;">TANGGAL</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="sp_date" id="sp_date" readonly>
                        </div> 
                    </div>
                    <div class="table-responsive" id="divdetail"></div>
                </form>    
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/fg_sorting_input.inc.php";
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
            {label:"SUBPLANT", name:'sp_sub_plant', index:'sp_sub_plant', width:70, align:'center', stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"ID", name:'sp_id', index:'sp_id', width:80, align:'center'},
            {label:"TANGGAL", name:'sp_date', index:'sp_date', width:50, align:'center'},
            {label:"SHIFT", name:'sp_shift', index:'sp_shift', width:40, align:'center'},
            {label:"LINE", name:'sp_line', index:'sp_line', width:40, align:'center'},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"sp_sub_plant asc, sp_date desc,  sp_shift asc, sp_line",
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
    $("#sp_id").val("");
    $("#sp_date").val("");
    $("#divdetail").html("");
    $("#sp_id, #sp_date, #sp_shift, #sp_line, #sp_sub_plant").attr('disabled',false);
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    formAwal();
    var subplan = $('#sp_sub_plant').val();
    $.post(frm+"?mode=detailtabel", {stat:"add",subplan:subplan}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#divdetail").html(o.detailtabel);
        $("#aded").val("add");
        $("#sp_id").val("OTOMATIS");
        $("#sp_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
        $("#boxAwal").hide();
        $("#boxEdit").show();  
        $("#sp_sub_plant").val("");    
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#sp_id").val(o.sp_id);
        $("#sp_sub_plant").val(o.sp_sub_plant);
        $("#sp_date").val(o.sp_date);
        $("#sp_shift").val(o.sp_shift);
        $("#sp_line").val(o.sp_line);
        $("#divdetail").html(o.detailtabel);
        $("#sp_id, #sp_date, #sp_shift, #sp_line, #sp_sub_plant").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#sp_id").val(o.sp_id);
        $("#sp_sub_plant").val(o.sp_sub_plant);
        $("#sp_date").val(o.sp_date);
        $("#sp_shift").val(o.sp_shift);
        $("#sp_line").val(o.sp_line);
        $("#divdetail").html(o.detailtabel);
        $("#sp_date, #sp_shift, #sp_line, #sp_sub_plant").attr('disabled',true);
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
            sp_sub_plant:{required:true},
            sp_date:{required:true},
            sp_shift:{required:true},
            sp_line:{required:true},
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
                alert("Perubahan data "+$("#sp_id").val()+" berhasil disimpan");
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

    $("#sp_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-1d'
    });

    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    

    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#sp_sub_plant").html(resp);
    });


    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });

    $("#txt_cari").keyup(function(e){
        if(e.keyCode == 13) {
            pilihMotif();
        }
    });

    $.post(frm+"?mode=pilihmotif", function(resp,stat){
        var o = JSON.parse(resp);
        $("#isiMdlMotif").html(o.out);
    });

});



function tambahItem() {
    var barisLast = $("#barisLast").val();
    var baris = 0;
    var baris = parseInt(barisLast) + 1;  
    var barisold = parseInt(barisLast) - 1;  

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
    kol1.setAttribute("width", "400");
    kol2.setAttribute("width", "90");
    kol3.setAttribute("width", "90");
    kol4.setAttribute("width", "90");
    kol5.setAttribute("width", "90");
    kol6.setAttribute("width", "90");
    kol7.setAttribute("width", "90");
    kol8.setAttribute("width", "120");
    kol0.innerHTML = '<div id="linkremove'+barisLast+'"><a href="javascript:void(0)" class="btn btn-default btn-xs" onclick="hapusItem('+barisLast+')"><span class="glyphicon glyphicon-remove"></span></a></div>';
    // kol1.innerHTML = '<input class="form-control input-sm" name="code['+barisLast+']" id="code_'+barisLast+'" type="text">';
    kol1.innerHTML = '<div class="input-group"><input class="form-control input-sm" name="code['+barisLast+']" id="code_'+barisLast+'" type="text" readonly><div class="input-group-addon" title="Pilih code"><span class="glyphicon glyphicon-option-horizontal" onClick="tampilMotif(\''+barisLast+'\');"></span></div></div>';
    kol2.innerHTML = '<input class="form-control input-sm text-right" name="size['+barisLast+']" id="size_'+barisLast+'" type="text">';
    kol3.innerHTML = '<input class="form-control input-sm text-right" name="export['+barisLast+']" id="export_'+barisLast+'" type="text" onkeyup="hanyanumerik(this.id,this.value);">';
    kol4.innerHTML = '<input class="form-control input-sm text-right" name="ekonomi['+barisLast+']" id="ekonomi_'+barisLast+'" type="text" onkeyup="hanyanumerik(this.id,this.value);">';
    kol5.innerHTML = '<input class="form-control input-sm text-right" name="reject['+barisLast+']" id="reject_'+barisLast+'" type="text" onkeyup="hanyanumerik(this.id,this.value);">';
    kol6.innerHTML = '<input class="form-control input-sm text-right" name="rijek_palet['+barisLast+']" id="rijek_palet_'+barisLast+'" type="text" onkeyup="hanyanumerik(this.id,this.value);">';
    kol7.innerHTML = '<input class="form-control input-sm text-right" name="rijek_buang['+barisLast+']" id="rijek_buang_'+barisLast+'" type="text" onkeyup="hanyanumerik(this.id,this.value);">';
    kol8.innerHTML = '<input class="form-control input-sm text-right" name="keterangan['+barisLast+']" id="keterangan_'+barisLast+'" type="text">';

    $("#linkremove"+barisold).hide();
}

function hapusItem(baris) {
    $("#trdet_ke_"+baris).remove();
    var barisLast2 = $("#barisLast").val();
    $("#barisLast").val(parseInt(barisLast2) - 1);

    var barisold2 = parseInt(baris) - 1; 
    $("#linkremove"+barisold2).show();
}

function hanyanumerik(id, value) {
    if (/([^.0123456789]|)/g.test(value)) { 
        $("#"+id).val(value.replace(/([^.0123456789])/g, ''));
    }
}

function tampilMotif(idx) {
    $("#idx_motif").val(idx);
    $("#modalMotif").modal('show');    
}

function pilihMotif() {
    $.post(frm+"?mode=pilihmotif", {txt_cari:$("#txt_cari").val()}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#isiMdlMotif").html(o.out);
    });
}

function setMotif(motif, size) {
    var idx = $("#idx_motif").val();
    $("#code_"+idx).val(motif);
    $("#size_"+idx).val(size);
    $("#modalMotif").modal('hide');
    $("#export_"+idx).focus();
}

</script>