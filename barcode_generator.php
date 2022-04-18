<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['155'];
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
            <div class="box-body" id="boxForm" style="display: none;">
                <form class="form-horizontal" id="frEdit">
                    <input type="hidden" name="aded" id="aded" readonly>
                    <input class="form-control input-sm" type="hidden" name="ic_id" id="ic_id" readonly>

                <div class="form-group">
                    <label class="col-sm-2 control-label" style="text-align:left;">NO KENDARAAN</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <div class="col-sm-4" style="margin-top:3px;padding:0px;">
                            <input class="form-control col-sm-4 input-sm text-center" type="text" name="no_prov" id="no_prov" size="2" maxlength="2" onkeyup="ucasefn1(this.id,this.value)" onchange="ucasefn1(this.id,this.value)" readonly>
                        </div>
                        <div class="col-sm-4" style="margin-top:3px;padding:0px;">
                            <input class="form-control input-sm text-center" type="text" name="no_kend" id="no_kend" size="4" maxlength="4" onkeyup="ucasefn2(this.id,this.value)" onchange="ucasefn2(this.id,this.value)" readonly>
                        </div>
                        <div class="col-sm-4" style="margin-top:3px;padding:0px;">
                            <input class="form-control input-sm text-center" type="text" name="no_wil" id="no_wil" size="3" maxlength="3" onkeyup="ucasefn1(this.id,this.value)" onchange="ucasefn1(this.id,this.value)" readonly>
                        </div>
                    </div>

                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">TANGGAL</label>
                    <div class="col-sm-4" style="margin-top:3px;">  
                        <div class="input-group">
                            <input class="form-control input-sm" type="text" name="ic_date" id="ic_date" disabled>
                            <div class="input-group-addon">
                                <span class="fa fa-calendar"></span>
                            </div>
                        </div>

                    </div> 

                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">KD MATERIAL</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <input type="text" class="form-control input-sm" name="ic_kd_material" id="ic_kd_material" readonly>
                    </div>

                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">NM MATERIAL</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <input type="text" class="form-control input-sm" name="ic_nm_material" id="ic_nm_material" disabled>
                    </div>

                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">KADAR AIR</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <input class="form-control input-sm" type="text" name="ic_kadar_air" id="ic_kadar_air" disabled>
                    </div>

                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">LW</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <input class="form-control input-sm" type="text" name="ic_lw" id="ic_lw" disabled>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">VISCO</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <input class="form-control input-sm" type="text" name="ic_visco" id="ic_visco" disabled>
                    </div>

                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">RESIDU</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <input class="form-control input-sm" type="text" name="ic_residu" id="ic_residu" disabled>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label" style="text-align:left;">KETERANGAN</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <textarea class="form-control input-sm" name="ic_keterangan" id="ic_keterangan" disabled></textarea>
                    </div>

                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">STATUS</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <select name="ic_hasil" id="ic_hasil" class="form-control input-sm" disabled> 
                            <option></option>
                            <option value="Y">OK</option>
                            <option value="N">NOT OK</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">NO PO</label>
                    <div class="col-sm-4" style="margin-top:3px;">  
                        <input class="form-control input-sm" type="text" name="ic_no_po" id="ic_no_po">
                    </div> 

                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">NO SJ</label>
                    <div class="col-sm-4" style="margin-top:3px;">  
                        <input class="form-control input-sm" type="text" name="ic_no_sj" id="ic_no_sj">
                    </div> 
                </div>


                <div class="form-group" id="buttonform"></div>
                </form>
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/barcode_generator.inc.php";
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
            {label:"ID", name:'ic_id', index:'ic_id', width:70, align:'center'},
            {label:"TANGGAL", name:'ic_date', index:'ic_date', width:70, align:'center'},
            {label:"KD MATERIAL", name:'ic_kd_material', index:'ic_kd_material', width:100, align:'center'},
            {label:"NM MATERIAL", name:'ic_nm_material', index:'ic_nm_material', width:130, align:'left'},
            {label:"NO KENDARAAN", name:'ic_no_kendaraan', index:'ic_no_kendaraan', width:70, align:'center'},
            {label:"NO PO", name:'ic_no_po', index:'ic_no_po', width:90, align:'center'},
            {label:"NO SJ", name:'ic_no_sj', index:'ic_no_sj', width:90, align:'center'},
            {label:"NO PENERIMAAN", name:'ic_bpb_kode', index:'ic_bpb_kode', width:100, align:'center',},
            {label:"KABAG | PM", name:'ic_apr', index:'ic_apr', width:80, align:'center',},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:90, align:'center'}
        ],
        sortname:"ic_no_po",
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


    jQuery(tblnya).jqGrid('navButtonAdd', topnya+"_left", {
        caption:"", buttonicon:'glyphicon-search', title:"Tampilkan baris pencarian",
        onClickButton:function () {
            this.toggleToolbar();
       }
    });

    $(pgrnya+"_center").hide();
}




function simpanData() {
    var mode = $("#aded").val();

    var rulenya = {
            ic_no_sj:{required:true},
            ic_no_po:{required:true},
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
                alert("Perubahan data "+$("#ic_id").val()+" berhasil disimpan");
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


function formAwal(){
    $("#aded").val("");
    $("#ic_id").val("");
    $("#ic_date").val("");
    $("#no_prov").val("");
    $("#no_kend").val("");
    $("#no_wil").val("");
    $("#ic_kd_material").val("");
    $("#ic_nm_material").val("");
    $("#ic_kadar_air").val("");
    $("#ic_keterangan").val("");
    $("#ic_lw").val("");
    $("#ic_visco").val("");
    $("#ic_residu").val("");
    $("#ic_no_po").val("");
    $("#ic_no_sj").val("");
    $("#ic_hasil").val("");
    $("#boxAwal").show();
    $("#boxForm").hide();
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#ic_id").val(o.ic_id);
        $("#ic_date").val(o.ic_date);
        $("#no_prov").val(o.no_prov);
        $("#no_kend").val(o.no_kend);
        $("#no_wil").val(o.no_wil);
        $("#ic_kd_material").val(o.ic_kd_material);
        $("#ic_nm_material").val(o.ic_nm_material);
        $("#ic_kadar_air").val(o.ic_kadar_air);
        $("#ic_keterangan").val(o.ic_keterangan);
        $("#ic_lw").val(o.ic_lw);
        $("#ic_visco").val(o.ic_visco);
        $("#ic_residu").val(o.ic_residu);
        $("#ic_hasil").val(o.ic_hasil);
        $("#ic_no_sj").val(o.ic_no_sj);
        $("#ic_no_po").val(o.ic_no_po);
        $("#buttonform").html(o.detailtabel);
        $("#boxForm").show(); 
        $("#boxAwal").hide(); 
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("view");
        $("#ic_id").val(o.ic_id);
        $("#ic_date").val(o.ic_date);
        $("#no_prov").val(o.no_prov);
        $("#no_kend").val(o.no_kend);
        $("#no_wil").val(o.no_wil);
        $("#ic_kd_material").val(o.ic_kd_material);
        $("#ic_nm_material").val(o.ic_nm_material);
        $("#ic_kadar_air").val(o.ic_kadar_air);
        $("#ic_keterangan").val(o.ic_keterangan);
        $("#ic_lw").val(o.ic_lw);
        $("#ic_visco").val(o.ic_visco);
        $("#ic_residu").val(o.ic_residu);
        $("#ic_hasil").val(o.ic_hasil);
        $("#ic_no_sj").val(o.ic_no_sj);
        $("#ic_no_po").val(o.ic_no_po);
        $("#buttonform").html(o.detailtabel);
        $("#boxForm").show(); 
        $("#boxAwal").hide(); 
    });
}


function ucasefn1(kode, nilai) {
    var a = document.getElementById(kode);
    a.value = nilai.toUpperCase();

    if (!/^[A-Z]+$/.test(a.value)) {
        // alert("Tidak boleh angka!!");
        a.value = a.value.substring(0, a.value.length - 1000);
    }

}

function ucasefn2(kode, nilai) {
    //alert("test");
    var a = document.getElementById(kode);
    a.value = nilai.toUpperCase();

    if (!/^[0-9]+$/.test(a.value)) {
        // alert("Tidak boleh huruf!!");
        a.value = a.value.substring(0, a.value.length - 1000);
    }

}


$(document).ready(function () {
    $("#ic_kadar_air, #ic_lw, #ic_visco, #ic_residu").afNumericOnly();

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

    $("#ic_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-5d'
    });


    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
   

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });


    $('#txt_cari').keypress(function(event){
      if(event.which==13){
        pilihMaterial();
      }
    });


    $('#ic_no_po').mask('AAA/0/00/00000');
});


</script>