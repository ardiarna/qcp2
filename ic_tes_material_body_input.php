<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['116'];
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
                        <label class="col-sm-1 control-label" style="text-align:left;">From</label>
                        <div class="col-sm-2" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="tglFrom" id="tglFrom">
                        </div>
                        <label class="col-sm-1 control-label" style="text-align:left;margin-top:3px;">To :</label>
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
                    <input class="form-control input-sm" type="hidden" name="ic_id" id="ic_id" readonly>
                    <div class="form-group">
                        <label class="col-sm-1 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <select class="form-control input-sm" id="ic_sub_plant" name="ic_sub_plant"></select>   
                        </div>
                        <label class="col-sm-1 control-label" style="text-align:left;">TGL TES</label>
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
                        <label class="col-sm-1 control-label" style="text-align:left;">RESIDU</label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <input type="text" class="form-control input-sm" name="ic_residu" id="ic_residu" readonly>
                        </div>
                    </div>
                    <hr>

                    <div class="table-responsive" id="divdetail"></div>
                </form>    
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/ic_tes_material_body_input.inc.php";
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
            {label:"SUBPLANT", name:'ic_sub_plant', index:'ic_sub_plant', align:'center', width:70, stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"TANGGAL", name:'ic_date', index:'ic_date', width:80, align:'center'},
            {label:"NAMA MATERIAL", name:'ic_nm_material', index:'ic_nm_material', width:120},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'},
        ],
        sortname:"ic_date desc,ic_sub_plant asc,ic_id",
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

function formAwal(){
    $("#aded").val("");
    $("#ic_id").val("");
    $("#ic_date").val("");
    $("#ic_sub_plant").val("");
    $("#ic_idmasuk").val("");
    $("#ic_nm_material").val("");
    $("#ic_date_in").val("");
    $("#ic_kadar_air").val("");
    $("#ic_lw").val('');
    $("#ic_visco").val('');
    $("#ic_residu").val('');
    $("#divdetail").html("");
    $("#ic_id, #ic_date, #ic_sub_plant").attr('readonly',false);
    $("#boxEdit").hide();
    $("#boxAwal").show();
    $(".lvlbtn").hide();
}

function tambahData(){
    $.post(frm+"?mode=detailtabel", {stat:"add",id_masuk:''}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#ic_sub_plant").val('A');
        $("#aded").val("add");
        $("#ic_id").val("OTOMATIS");
        $("#ic_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
        $("#boxAwal").hide();
        $("#boxEdit").show(); 
        $(".lvlbtn").show();
        $("#divdetail").html(o.detailtabel);
    });
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#ic_id").val(o.ic_id);
        $("#ic_date").val(o.ic_date);
        $("#ic_sub_plant").val(o.ic_sub_plant);
        $("#ic_idmasuk").val(o.ic_idmasuk);
        $("#ic_nm_material").val(o.ic_nm_material);
        $("#ic_date_in").val(o.ic_date_in);
        $("#ic_kadar_air").val(o.ic_kadar_air);
        $("#ic_lw").val(o.ic_lw);
        $("#ic_visco").val(o.ic_visco);
        $("#ic_residu").val(o.ic_residu);
        $("#divdetail").html(o.detailtabel);
        $("#ic_id, #ic_date, #ic_sub_plant").attr('readonly',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();  
        $(".lvlbtn").hide();
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
        $("#ic_date_in").val(o.ic_date_in);
        $("#ic_kadar_air").val(o.ic_kadar_air);
        $("#ic_lw").val(o.ic_lw);
        $("#ic_visco").val(o.ic_visco);
        $("#ic_residu").val(o.ic_residu);
        $("#divdetail").html(o.detailtabel);
        $("#ic_sub_plant, #ic_date").attr('readonly',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();   
        $(".lvlbtn").hide();
    });
}

function copyData($idmsk,$idcopy){
    swal({
          title: "Confirm!",
          text: "Copy dari data terakhir ?",
          icon: "info",
          buttons: true,
          dangerMode: true,
        })
        .then((willOk) => {
          if (willOk) {
            $.post(frm+"?mode=detailtabel", {stat:"add",id_masuk:$idmsk,id_copy:$idcopy}, function(resp,stat){
                var o = JSON.parse(resp);
                $("#divdetail").html(o.detailtabel);
            });
          }
    }); 

}

function hapusData(kode){
    var r = confirm("Hapus data id "+kode+"?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {kode:kode}, function(resp,stat){
            if (resp=="OK") {
                $.jgrid.gridUnload("#tblsm");
                tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
            } else {
                alert(resp);
            }  
        });
    } else {
        return false;
    }
}

function simpanData(mode) {
    var rulenya = {
            ic_sub_plant:{required:true},
            ic_date:{required:true},
            ic_idmasuk:{required:true}
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

    
$(document).ready(function () {

    // swal("Dalam Penyesuaian - IT!", {
    //   icon: "success",
    // });

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
            alert('Tanggal From tidak boleh lebih cepat dari tanggal To, mohon ubah tanggal To.');
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

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });


    $('#ic_sub_plant').change(function(){
        var subplan = this.value;
        // $.post(frm+"?mode=cbobox", {subplan:subplan}, function(resp,stat){
        //     $("#no_box").html(resp);      
        // });
    });
});

function hanyanumerik(id, value) {
    if (/([^.0123456789]|)/g.test(value)) { 
        $("#"+id).val(value.replace(/([^.0123456789])/g, ''));
    }
}


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

function setMaterial(id) {
    $.post(frm+"?mode=detailtabel", {stat:"add",id_masuk:id}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#ic_idmasuk").val(o.ic_idmasuk);
        $("#ic_date_in").val(o.ic_date_in);
        $("#ic_nm_material").val(o.ic_nm_material);
        $("#ic_kadar_air").val(o.ic_kadar_air);
        $("#ic_lw").val(o.ic_lw);
        $("#ic_visco").val(o.ic_visco);
        $("#ic_residu").val(o.ic_residu);
        $("#divdetail").html(o.detailtabel);
        $("#modalMaterial").modal('hide');
    });
}

</script>