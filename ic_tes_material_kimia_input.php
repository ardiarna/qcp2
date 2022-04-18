<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['120'];
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
                            <h4 class="modal-title">DAFTAR MATERIAL MASUK</h4>
                        </div>
                        <div class="modal-body table-responsive">
                            <div class="input-group">
                                <input class="form-control input-sm" id="txt_cari" type="text" placeholder="Pencarian">
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
                    <label class="col-sm-1 control-label" style="text-align:left;">SUBPLANT</label>
                    <div class="col-sm-3" style="margin-top:3px;">
                        <select class="form-control input-sm" id="ic_sub_plant" name="ic_sub_plant"></select>   
                    </div>
                    <label class="col-sm-1 control-label" style="text-align:left;">TANGGAL</label>
                    <div class="col-sm-3" style="margin-top:3px;">  
                        <input class="form-control input-sm" type="text" name="ic_date" id="ic_date" readonly>
                    </div>    
                    <label class="col-sm-1 control-label" style="text-align:left;">ID MASUK</label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" name="ic_idmasuk" id="ic_idmasuk" readonly>
                                <div class="input-group-addon lvlbtn" title="Pilih ID">
                                    <span class="glyphicon glyphicon-option-horizontal" onClick="tampilMaterial();"></span>
                                </div>
                            </div>
                        </div>  
                </div>
                <div class="form-group">
                    <label class="col-sm-1 control-label" style="text-align:left;">MATERIAL</label>
                    <div class="col-sm-3" style="margin-top:3px;">
                        <input type="text" class="form-control input-sm" name="ic_nm_material" id="ic_nm_material" readonly>
                    </div>
                    <label class="col-sm-1 control-label" style="text-align:left;">TGL MASUK</label>
                    <div class="col-sm-3" style="margin-top:3px;">  
                        <input type="text" class="form-control input-sm" name="ic_date_in" id="ic_date_in" readonly>
                    </div>
                    <label class="col-sm-1 control-label" style="text-align:left;">KADAR AIR</label>
                    <div class="col-sm-3" style="margin-top:3px;">
                        <input type="text" class="form-control input-sm" name="ic_kadar_air" id="ic_kadar_air" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-1 control-label" style="text-align:left;">LW</label>
                    <div class="col-sm-3" style="margin-top:3px;">
                        <input type="text" class="form-control input-sm" name="ic_lw" id="ic_lw" readonly>
                    </div>
                    <label class="col-sm-1 control-label" style="text-align:left;">VISCO</label>
                    <div class="col-sm-3" style="margin-top:3px;">  
                        <input type="text" class="form-control input-sm" name="ic_visco" id="ic_visco" readonly>
                    </div>
                    <label class="col-sm-1 control-label" style="text-align:left;">BERAT</label>
                    <div class="col-sm-3" style="margin-top:3px;">
                        <input class="form-control input-sm" type="text" name="berat" id="berat">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-1 control-label" style="text-align:left;">GLOSSY</label>
                    <div class="col-sm-3" style="margin-top:3px;">
                        <select class="form-control input-sm" name="glossy" id="glossy">
                            <option value=""></option>
                            <option value="Y">OK</option>
                            <option value="N">NOT OK</option>
                        </select>
                    </div>
                    <label class="col-sm-1 control-label" style="text-align:left;margin-top:3px;">FLATNESS</label>
                    <div class="col-sm-3" style="margin-top:3px;">
                        <select class="form-control input-sm" name="flatness" id="flatness">
                            <option value=""></option>
                            <option value="Y">OK</option>
                            <option value="N">NOT OK</option>
                        </select>
                    </div>
                    <label class="col-sm-1 control-label" style="text-align:left;margin-top:3px;">PIN HOLE</label>
                    <div class="col-sm-3" style="margin-top:3px;">
                        <select class="form-control input-sm" name="pinhole" id="pinhole">
                            <option value=""></option>
                            <option value="Y">OK</option>
                            <option value="N">NOT OK</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-1 control-label" style="text-align:left;">KETERANGAN</label>
                    <div class="col-sm-5" style="margin-top:3px;">
                        <textarea class="form-control input-sm" name="keterangan" id="keterangan"></textarea>
                    </div>

                    <label class="col-sm-1 control-label" style="text-align:left;">KESIMPULAN</label>
                    <div class="col-sm-5" style="margin-top:3px;">
                        <textarea class="form-control input-sm" name="kesimpulan" id="kesimpulan"></textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="col-sm-3" style="margin-top:3px;">
                        <p style="color:red;">
                            Standar acuan Glossy, Flatness dan Pin Hole : <br>
                            < 5 : TIDAK OK <br>
                            <u>></u>5 : OK
                        </p>
                    </div>
                </div>

                <div class="form-group" id="buttonform"></div>
                </form>
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/ic_tes_material_kimia_input.inc.php";
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
            {label:"SUBPLANT", name:'ic_sub_plant', index:'ic_sub_plant', align:'center' , width:50, stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"ID", name:'ic_id', index:'ic_id', width:50, align:'center'},
            {label:"TANGGAL", name:'ic_date', index:'ic_date', width:50, align:'center'},
            {label:"MATERIAL", name:'ic_nm_material', index:'ic_nm_material', width:150, align:'left'},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
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
            ic_sub_plant:{required:true},
            ic_date:{required:true},
            ic_idmasuk:{required:true},
            glossy:{required:true},
            flatness:{required:true},
            pinhole:{required:true},
            berat:{required:true},
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
    $("#ic_idmasuk").val("");
    $("#ic_nm_material").val("");
    $("#ic_date_in").val("");
    $("#ic_kadar_air").val("");
    $("#ic_lw").val("");
    $("#ic_visco").val("");
    $("#berat").val("");
    $("#glossy").val("");
    $("#flatness").val("");
    $("#pinhole").val("");
    $("#keterangan").val("");
    $("#kesimpulan").val("");
    $("#boxAwal").show();
    $("#boxForm").hide();
    $(".lvlbtn").hide();
    $("#ic_date, #berat, #glossy, #flatness, #pinhole, #keterangan, #kesimpulan").attr('readonly',false);
}


function tambahData() {
    $("#ic_sub_plant").val('A');
    $("#aded").val("add");
    $("#ic_date").datepicker('setDate',moment().format("DD-MM-YYYY"));  
    $("#boxForm").show(); 
    $("#boxAwal").hide(); 
    $(".lvlbtn").show();
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
        $("#ic_sub_plant").val(o.ic_sub_plant);
        $("#ic_idmasuk").val(o.ic_idmasuk);
        $("#ic_nm_material").val(o.ic_nm_material);
        $("#ic_kadar_air").val(o.ic_kadar_air);
        $("#ic_lw").val(o.ic_lw);
        $("#ic_visco").val(o.ic_visco);
        $("#no_lot").val(o.no_lot);
        $("#berat").val(o.berat);
        $("#glossy").val(o.glossy);
        $("#flatness").val(o.flatness);
        $("#pinhole").val(o.pinhole);
        $("#keterangan").val(o.keterangan);
        $("#kesimpulan").val(o.kesimpulan);
        $("#buttonform").html(o.detailtabel);
        $("#boxForm").show(); 
        $("#boxAwal").hide(); 
        $(".lvlbtn").hide();
        $("#ic_date, #berat, #no_lot, #glossy, #flatness, #pinhole, #keterangan, #kesimpulan").attr('readonly',false);
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("view");
        $("#ic_id").val(o.ic_id);
        $("#ic_date").val(o.ic_date);
        $("#ic_sub_plant").val(o.ic_sub_plant);
        $("#ic_idmasuk").val(o.ic_idmasuk);
        $("#ic_nm_material").val(o.ic_nm_material);
        $("#ic_kadar_air").val(o.ic_kadar_air);
        $("#ic_lw").val(o.ic_lw);
        $("#ic_visco").val(o.ic_visco);
        $("#no_lot").val(o.no_lot);
        $("#berat").val(o.berat);
        $("#glossy").val(o.glossy);
        $("#flatness").val(o.flatness);
        $("#pinhole").val(o.pinhole);
        $("#keterangan").val(o.keterangan);
        $("#kesimpulan").val(o.kesimpulan);
        $("#buttonform").html(o.detailtabel);
        $("#boxForm").show(); 
        $("#boxAwal").hide(); 
        $(".lvlbtn").hide();
        $("#ic_sub_plant, #ic_date, #berat, #glossy, #no_lot, #flatness, #pinhole, #keterangan, #kesimpulan").attr('readonly',true);
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

    $("#ic_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-5d'
    });


    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    
    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#ic_sub_plant").html(resp);
    });

    $('#ic_sub_plant').change(function(){
        var subplan = this.value;
        // $.post(frm+"?mode=cbobox", {subplan:subplan}, function(resp,stat){
        //     $("#no_box").html(resp);      
        // });
    });

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });

    $('#berat').afNumericOnly();

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

function setMaterial(id,tgl,material,ka,lw,visco) {
    $("#ic_idmasuk").val(id);
    $("#ic_date_in").val(tgl);
    $("#ic_nm_material").val(material);
    $("#ic_kadar_air").val(ka);
    $("#ic_lw").val(lw);
    $("#ic_visco").val(visco);
    $("#modalMaterial").modal('hide');
}

</script>