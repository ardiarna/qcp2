<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['83'];
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
                    <input class="form-control input-sm" type="hidden" name="kb_id" id="kb_id" readonly>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="kb_sub_plant" name="kb_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">TEMPERATUR</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input type="text" class="form-control input-sm" id="kb_temp" name="kb_temp">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">KILN</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" id="kb_kiln" name="kb_kiln">
                                <option value=""></option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select> 
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">SPEED</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input type="text" class="form-control input-sm" id="kb_speed" name="kb_speed">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">TANGGAL</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="kb_date" id="kb_date" readonly>
                        </div>   
                        <label class="col-sm-2 control-label" style="text-align:left;">TEBAL TILE</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input type="text" class="form-control input-sm" id="kb_tt" name="kb_tt">
                        </div> 
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">JAM</label>
                        <div class="col-sm-4 bootstrap-timepicker" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="kb_jam" id="kb_jam">   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">PRESI</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input type="text" class="form-control input-sm" id="kb_presi" name="kb_presi">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;"></label>
                        <div class="col-sm-4 bootstrap-timepicker" style="margin-top:3px;">   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">KETERANGAN</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input type="text" class="form-control input-sm" id="kb_desc" name="kb_desc">
                        </div>
                    </div>
                    <div class="table-responsive" id="divdetail"></div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">WATER ABORTION</label>
                        <div class="col-sm-10 bootstrap-timepicker" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" id="kb_wa" name="kb_wa">   
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">AUTOCLAVE</label>
                        <div class="col-sm-10 bootstrap-timepicker" style="margin-top:3px;">
                            <select class="form-control input-sm" id="kb_ac" name="kb_ac">
                                <option value=""></option>
                                <option>Passed</option>
                                <option>Failed</option>
                            </select>   
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">WATERMARK</label>
                        <div class="col-sm-10 bootstrap-timepicker" style="margin-top:3px;">
                            <select class="form-control input-sm" id="kb_wm" name="kb_wm">
                                <option value=""></option>
                                <option>Passed</option>
                                <option>Failed</option>
                            </select>   
                        </div>
                    </div>
                    <div class="col-sm-12 text-center" id="divTombol"></div>
                </form>    
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/fg_kiln_bending_input.inc.php";
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
            {label:"SUBPLANT", name:'kb_sub_plant', index:'kb_sub_plant', width:70, align:'center', stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"ID", name:'kb_id', index:'kb_id', width:80, align:'center'},
            {label:"TANGGAL", name:'kb_date', index:'kb_date', width:50, align:'center'},
            {label:"JAM", name:'kb_jam', index:'kb_jam', width:40, align:'center'},
            {label:"KILN", name:'kb_kiln', index:'kb_kiln', width:40, align:'center'},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"kb_sub_plant asc, kb_date desc, kb_kiln",
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
    $("#kb_id").val("");
    $("#kb_date").val("");
    $("#kb_kiln").val("");
    $("#kb_jam").val("");
    $("#divdetail, #divTombol").html("");
    $("#kb_id, #kb_date, #kb_kiln, #kb_jam, #kb_sub_plant").attr('disabled',false);
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    formAwal();
    var subplan = $('#kb_sub_plant').val();
    $.post(frm+"?mode=detailtabel", {stat:"add",subplan:subplan}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#divdetail").html(o.detailtabel);
        $("#divTombol").html(o.tombol);
        $("#aded").val("add");
        $("#kb_id").val("OTOMATIS");
        $("#kb_jam").val(moment().format("HH:00"));
        $("#kb_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
        $("#boxAwal").hide();
        $("#boxEdit").show();  
        $("#kb_sub_plant").val("");
        tambahItem();
        tambahItem();
        tambahItem();
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#kb_id").val(o.kb_id);
        $("#kb_sub_plant").val(o.kb_sub_plant);
        $("#kb_date").val(o.kb_date);
        $("#kb_kiln").val(o.kb_kiln);
        $("#kb_jam").val(o.kb_jam);
        $("#kb_temp").val(o.kb_temp);
        $("#kb_speed").val(o.kb_speed);
        $("#kb_wa").val(o.kb_wa);
        $("#kb_ac").val(o.kb_ac);
        $("#kb_wm").val(o.kb_wm);
        $("#kb_tt").val(o.kb_tt);
        $("#kb_presi").val(o.kb_presi);
        $("#kb_desc").val(o.kb_desc);
        $("#divdetail").html(o.detailtabel);
        $("#divTombol").html(o.tombol);
        $("#kb_id, #kb_date, #kb_kiln, #kb_jam, #kb_sub_plant").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#kb_id").val(o.kb_id);
        $("#kb_sub_plant").val(o.kb_sub_plant);
        $("#kb_date").val(o.kb_date);
        $("#kb_kiln").val(o.kb_kiln);
        $("#kb_jam").val(o.kb_jam);
        $("#kb_temp").val(o.kb_temp);
        $("#kb_speed").val(o.kb_speed);
        $("#kb_wa").val(o.kb_wa);
        $("#kb_ac").val(o.kb_ac);
        $("#kb_wm").val(o.kb_wm);
        $("#kb_tt").val(o.kb_tt);
        $("#kb_presi").val(o.kb_presi);
        $("#kb_desc").val(o.kb_desc);
        $("#divdetail").html(o.detailtabel);
        $("#divTombol").html(o.tombol);
        $("#kb_date, #kb_kiln, #kb_jam, #kb_sub_plant").attr('disabled',true);
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
            kb_sub_plant:{required:true},
            kb_date:{required:true},
            kb_kiln:{required:true},
            kb_jam:{required:true},
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
                alert("Perubahan data "+$("#kb_id").val()+" berhasil disimpan");
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

    $("#kb_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-1d'
    });

    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    

    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#kb_sub_plant").html(resp);
    });
   
    $('#kb_jam').timepicker({
      showInputs: false,
      showMeridian: false,
      minuteStep: 60
    });

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
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
    kol0.innerHTML = '<div id="linkremove'+barisLast+'"><a href="javascript:void(0)" class="btn btn-default btn-xs" onclick="hapusItem('+barisLast+')"><span class="glyphicon glyphicon-remove"></span></a></div>';
    kol1.innerHTML = '<input class="form-control input-sm" name="kbd_posisi['+barisLast+']" id="kbd_posisi_'+barisLast+'" type="number">';
    kol2.innerHTML = '<input class="form-control input-sm text-right" name="kbd_kg['+barisLast+']" id="kbd_kg_'+barisLast+'" type="text" onkeyup="hanyanumerik(this.id,this.value);">';
    kol3.innerHTML = '<input class="form-control input-sm text-right" name="kbd_cm['+barisLast+']" id="kbd_cm_'+barisLast+'" type="text" onkeyup="hanyanumerik(this.id,this.value);">';

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

</script>