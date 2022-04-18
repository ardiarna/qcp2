<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['75'];
?>
<style type="text/css">
    #tblsm,
    .ui-jqgrid-htable {
        font-size:11px;
   }
    th {
      text-align:center;
   }     
    .vericaltext{
        width:1px;
        word-wrap: break-word;
        font-family: monospace;
        font-size: 18px;
        margin-top: 15px;
        margin-left: 15px;
    }
}
</style>
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
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
                    <input class="form-control input-sm" type="hidden" name="kl_id" id="kl_id" readonly>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">Subplant</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="kl_sub_plant" name="kl_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">Speed</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="kl_speed" id="kl_speed"> 
                        </div>     
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">Kiln</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="id_kiln" name="id_kiln"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">Code</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="kl_code" id="kl_code">
                        </div> 
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">Jam</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="kl_time" name="kl_time">
                                <option value=""></option>
                                <option value="08:00">08:00</option>
                                <option value="09:00">09:00</option>
                                <option value="10:00">10:00</option>
                                <option value="11:00">11:00</option>
                                <option value="12:00">12:00</option>
                                <option value="13:00">13:00</option>
                                <option value="14:00">14:00</option>
                                <option value="15:00">15:00</option>
                                <option value="16:00">16:00</option>
                                <option value="17:00">17:00</option>
                                <option value="18:00">18:00</option>
                                <option value="19:00">19:00</option>
                                <option value="20:00">20:00</option>
                                <option value="21:00">21:00</option>
                                <option value="22:00">22:00</option>
                                <option value="23:00">23:00</option>
                                <option value="24:00">24:00</option>
                                <option value="01:00">01:00</option>
                                <option value="02:00">02:00</option>
                                <option value="03:00">03:00</option>
                                <option value="04:00">04:00</option>
                                <option value="05:00">05:00</option>
                                <option value="06:00">06:00</option>
                                <option value="07:00">07:00</option>
                            </select>   
                        </div>  
                        <label class="col-sm-2 control-label" style="text-align:left;">Presure</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="kl_presure" id="kl_presure">
                        </div>  
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">Tanggal</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="kl_date" id="kl_date" readonly>
                        </div> 
                        <label class="col-sm-2 control-label" style="text-align:left;"></label>
                    </div>

                   <div id="idbtnback">
                        <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button>
                    </div>

                    <div class="table-responsive" id="divdetail"></div>
                </form>    
            </div>
        </div>
    </div>
</div> 




<!-- Modal -->
<div class="modal fade" id="loadingmodal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body">
        Memuat data...
      </div>
    </div>
  </div>
</div>


<script type="text/javascript">
var frm = "include/kilninput.inc.php";
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
            {label:"ID", name:'kl_id', index:'kl_id', width:80, align:'center'},
            {label:"SUBPLANT", name:'kl_sub_plant', index:'kl_sub_plant', width:70, stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}, align:'center'},
            {label:"TANGGAL", name:'kl_date', index:'kl_date', width:80, align:'center'},
            {label:"KILN", name:'id_kiln', index:'id_kiln', width:70, align:'center'},
            {label:"JAM", name:'kl_time', index:'kl_time', width:70, align:'center'},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"kl_sub_plant asc, kl_id asc,kl_date desc, kl_time desc, id_kiln",
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
    $("#kl_sub_plant").val("");
    $("#kl_id").val("");
    $("#id_kiln").html("");
    $("#kl_time").val("");
    $("#kl_date").val("");
    $("#kl_speed").val("");
    $("#kl_code").val("");
    $("#kl_presure").val("");
    $("#divdetail").html("");
    $("#kl_id, #kl_sub_plant, #id_kiln, #kl_time, #kl_date, #kl_speed, #kl_code, #kl_presure").attr('disabled',false);
    $("#boxEdit").hide();
    $("#boxAwal").show();
    $("#idbtnback").hide();  
}

function tambahData(){
    $("#aded").val("add");
    $("#kl_id").val("OTOMATIS");
    $("#kl_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
    $("#divdetail").html("");
    $("#boxAwal").hide();
    $("#boxEdit").show();  
    $("#idbtnback").show();  

}

function hideGrup(grupke) {
    $("#trgrup_ke_"+grupke).toggle();
}

function hanyanumerik(id, value) {
    if (/([^-.0123456789]|)/g.test(value)) { 
        $("#"+id).val(value.replace(/([^-.0123456789])/g, ''));
    }
}

function lihatData(kode){
    $('#loadingmodal').modal('show');
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#kl_id").val(o.kl_id);
        $("#kl_date").val(o.kl_date);
        $("#id_kiln").html(o.id_kiln);
        $("#kl_sub_plant").val(o.kl_sub_plant);
        $("#kl_code").val(o.kl_code);
        $("#kl_presure").val(o.kl_presure);
        $("#kl_time").html(o.kl_time);
        $("#kl_speed").val(o.kl_speed);

        $("#divdetail").html(o.detailtabel);
        $("#kl_id, #kl_sub_plant, #id_kiln, #kl_time, #kl_date, #kl_speed, #kl_code, #kl_presure").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
        $("#idbtnback").hide();  
        $('#loadingmodal').modal('hide');
    });
}

function editData(kode){
    $('#loadingmodal').modal('show');
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#kl_id").val(o.kl_id);
        $("#kl_date").val(o.kl_date);
        $("#id_kiln").html(o.id_kiln);
        $("#kl_sub_plant").val(o.kl_sub_plant);
        $("#kl_code").val(o.kl_code);
        $("#kl_presure").val(o.kl_presure);
        $("#kl_time").html(o.kl_time);
        $("#kl_speed").val(o.kl_speed);

        $("#divdetail").html(o.detailtabel);
        $("#kl_sub_plant, #id_kiln, #kl_time, #kl_date").attr('disabled',true);
        $("#kl_id, #kl_speed, #kl_code, #kl_presure").attr('disabled',false);
        $("#boxAwal").hide();
        $("#boxEdit").show();
        $("#idbtnback").hide();  
        $('#loadingmodal').modal('hide');
    });
}

function hapusData(kode){
    var r = confirm("Hapus Data dengan id "+kode+"?");
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
            kl_sub_plant:{required:true},
            kl_date:{required:true},
            kl_time:{required:true},
            id_kiln:{required:true}
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
                alert("Perubahan data "+$("#kl_id").val()+" berhasil disimpan");
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
    
    $("#kl_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-1d'
    });

    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    
    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#kl_sub_plant").html(resp);
    });

    $('#kl_sub_plant').change(function(){
        $("#idbtnback").hide();  

        var subplan = this.value;
        $.post(frm+"?mode=cboklin", {subplan:subplan}, function(resp,stat){
            $("#id_kiln").html(resp);  
        });

        if($("#aded").val() == "add") {
            $.post(frm+"?mode=detailtabel", {stat:"add",subplan:subplan}, function(resp,stat){
                var o = JSON.parse(resp);
                $("#divdetail").html(o.detailtabel);      
            });
        }
    });

    $('#kl_date').change(function(){
       var kl_date = this.value;
        var id_kiln = $("#id_kiln").val()
        var kl_sub_plant = $("#kl_sub_plant").val()
       
        $.post(frm+"?mode=loadscp", {kl_sub_plant:kl_sub_plant,kl_date:kl_date,id_kiln:id_kiln}, function(resp,stat){
            var o = JSON.parse(resp);
            $("#kl_code").val(o.kl_coderr);
            $("#kl_presure").val(o.kl_presurerr);
            $("#kl_speed").val(o.kl_speedrr);
        });
    });

    $('#id_kiln').change(function(){
        var id_kiln = this.value;
        var kl_date = $("#kl_date").val()
        var kl_sub_plant = $("#kl_sub_plant").val()
       
        $.post(frm+"?mode=loadscp", {kl_sub_plant:kl_sub_plant,kl_date:kl_date,id_kiln:id_kiln}, function(resp,stat){
            var o = JSON.parse(resp);
            $("#kl_code").val(o.kl_coderr);
            $("#kl_presure").val(o.kl_presurerr);
            $("#kl_speed").val(o.kl_speedrr);
        });
    });


    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });

});
</script>