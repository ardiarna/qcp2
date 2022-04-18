﻿<?php
include_once("libs/konfigurasi.php");
session_start();
$akses = $_SESSION[$app_id]['app_priv']['112'];
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
            <div class="modal fade" id="modalMaterial" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header alert-info">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Daftar Material</h4>
                        </div>
                        <div class="modal-body table-responsive">
                            <div class="input-group">
                                <input class="form-control input-sm" id="txt_cari" type="text" placeholder="Masukkan Kode Material">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-sm btn-info" onclick="pilihMaterial();">Go</button>
                                </span>
                            </div>
                            <br>
                            <div id="isiMdlMaterial" style="display:block;max-height:500px;overflow-y:auto;-ms-overflow-style:-ms-autohiding-scrollbar;">
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
            <div class="box-body" id="boxForm" style="display: none;">
                <form class="form-horizontal" id="frEdit">
                    <input type="hidden" name="aded" id="aded" readonly>
                    <input class="form-control input-sm" type="hidden" name="ic_id" id="ic_id" readonly>

                <div class="form-group">
                    <label class="col-sm-2 control-label" style="text-align:left;">NO KENDARAAN</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <div class="col-sm-4" style="margin-top:3px;padding:0px;">
                            <input class="form-control col-sm-4 input-sm text-center" type="text" name="no_prov" id="no_prov" size="2" maxlength="2" onkeyup="ucasefn1(this.id,this.value)" onchange="ucasefn1(this.id,this.value)">
                        </div>
                        <div class="col-sm-4" style="margin-top:3px;padding:0px;">
                            <input class="form-control input-sm text-center" type="text" name="no_kend" id="no_kend" size="4" maxlength="4" onkeyup="ucasefn2(this.id,this.value)" onchange="ucasefn2(this.id,this.value)">
                        </div>
                        <div class="col-sm-4" style="margin-top:3px;padding:0px;">
                            <input class="form-control input-sm text-center" type="text" name="no_wil" id="no_wil" size="3" maxlength="3" onkeyup="ucasefn1(this.id,this.value)" onchange="ucasefn1(this.id,this.value)">
                        </div>
                    </div>

                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">TANGGAL</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <div class="input-group">
                            <input class="form-control input-sm" type="text" name="ic_date" id="ic_date" readonly>
                            <div class="input-group-addon">
                                <span class="fa fa-calendar"></span>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">KD MATERIAL</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" name="ic_kd_material" id="ic_kd_material" readonly>
                            <div class="input-group-addon" title="Pilih nama item">
                                <span class="glyphicon glyphicon-option-horizontal" onClick="tampilMaterial();"></span>
                            </div>
                        </div>
                    </div>


                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">NM MATERIAL</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <input class="form-control input-sm" type="text" name="ic_nm_material" id="ic_nm_material" readonly>
                    </div>


                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">KADAR AIR</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <input class="form-control input-sm" type="text" name="ic_kadar_air" id="ic_kadar_air">
                    </div>

                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">LW</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <input class="form-control input-sm" type="text" name="ic_lw" id="ic_lw">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">VISCO</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <input class="form-control input-sm" type="text" name="ic_visco" id="ic_visco">
                    </div>

                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">RESIDU</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <input class="form-control input-sm" type="text" name="ic_residu" id="ic_residu">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label" style="text-align:left;">KETERANGAN</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <textarea class="form-control input-sm" name="ic_keterangan" id="ic_keterangan"></textarea>
                    </div>

                    <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">STATUS</label>
                    <div class="col-sm-4" style="margin-top:3px;">
                        <select name="ic_hasil" id="ic_hasil" class="form-control input-sm">
                            <option></option>
                            <option value="Y">OK</option>
                            <option value="N">NOT OK</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" id="buttonform"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var frm = "include/ic_kebasahan_input.inc.php";
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
            {label:"NO KENDARAAN", name:'ic_no_kendaraan', index:'ic_no_kendaraan', width:70, align:'center'},
            {label:"MATERIAL", name:'ic_nm_material', index:'ic_nm_material', width:130, align:'left'},
            {label:"LW", name:'ic_lw', index:'ic_lw', width:60, align:'center'},
            {label:"VISCO", name:'ic_visco', index:'ic_visco', width:60, align:'center'},
            {label:"KADAR AIR", name:'ic_kadar_air', index:'ic_kadar_air', width:60, align:'center'},
            {label:"STATUS", name:'ic_hasil', index:'ic_hasil', width:50, align:'center', stype:'select', searchoptions:{value:":;Y:OK;N:NOT OK"}},
            {label:"KABAG | PM", name:'ic_apr', index:'ic_apr', width:80, align:'center',},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:90, align:'center'}
        ],
        sortname:"ic_id",
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




function simpanData() {
    var mode = $("#aded").val();

    var rulenya = {
            ic_date:{required:true},
            no_prov:{required:true},
            no_kend:{required:true},
            no_wil:{required:true},
            ic_hasil:{required:true},
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
    $("#ic_hasil").val("");
    $("#boxAwal").show();
    $("#boxForm").hide();
    $("#ic_date, #no_prov, #no_kend, #no_wil, #ic_kadar_air, #ic_keterangan, #ic_lw, #ic_visco, #ic_residu, #ic_hasil").attr('disabled',false);
}

function tambahData() {
    $("#aded").val("add");
    $("#ic_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
    $("#boxForm").show();
    $("#boxAwal").hide();
    $.post(frm+"?mode=detailtabel", {stat:"add"}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#buttonform").html(o.detailtabel);
    });
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
        $("#buttonform").html(o.detailtabel);
        $("#boxForm").show();
        $("#boxAwal").hide();
        $("#ic_date, #no_prov, #no_kend, #no_wil, #ic_kadar_air, #ic_keterangan, #ic_lw, #ic_visco, #ic_residu, #ic_hasil").attr('disabled',false);
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
        $("#buttonform").html(o.detailtabel);
        $("#boxForm").show();
        $("#boxAwal").hide();
        $("#ic_date, #no_prov, #no_kend, #no_wil, #ic_kadar_air, #ic_keterangan, #ic_lw, #ic_visco, #ic_residu, #ic_hasil").attr('disabled',true);
    });
}

function hapusData(kode){
    var r = confirm("Hapus data id "+kode+"?");
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

    $('#txt_cari_subkon').keypress(function(event){
      if(event.which==13){
        pilihSubkon();
      }
    });

      $('#ic_no_po').mask('AAA/0/00/00000');
});



function tampilMaterial() {
    $("#txt_cari").val('');
    pilihMaterial();
    $("#modalMaterial").modal('show');
}

function pilihMaterial() {
    $.post(frm+"?mode=pilihMaterial", {txt_cari:$("#txt_cari").val()}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#isiMdlMaterial").html(o.out);
    });
}

function setMaterial(kdm,nmm) {
    $("#ic_kd_material").val(kdm);
    $("#ic_nm_material").val(nmm);
    $("#modalMaterial").modal('hide');
}

</script>