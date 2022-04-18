<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['130'];
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
                    <input class="form-control input-sm" type="hidden" name="op_id" id="op_id" readonly>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">SUBPLANT</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="op_sub_plant" name="op_sub_plant"></select>   
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">PRESS</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <select class="form-control input-sm" id="op_press" name="op_press"></select> 
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">JAM CHECK</label>
                        <div class="col-sm-4 bootstrap-timepicker" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="op_jam" id="op_jam">   
                        </div>

                        <label class="col-sm-2 control-label" style="text-align:left;">TEKANAN</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input type="text" class="form-control input-sm" id="op_tekanan" name="op_tekanan">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;">TANGGAL</label>
                        <div class="col-sm-4" style="margin-top:3px;">  
                            <input class="form-control input-sm" type="text" name="op_date" id="op_date" readonly>
                        </div>
                        <label class="col-sm-2 control-label" style="text-align:left;">UKURAN</label>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <select class="form-control input-sm" id="op_ukuran" name="op_ukuran">
                                <option>20 X 20</option>
                                <option>30 X 30</option>
                                <option>40 X 40</option>
                                <option>20 X 25</option>
                                <option>25 X 40</option>
                                <option>25 X 25</option>
                                <option>50 X 50</option>
                                <option>25 X 50</option>
                                <option>60 X 60</option>
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
var frm = "include/pd_thickness_input.inc.php";
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
            {label:"SUBPLANT", name:'op_sub_plant', index:'op_sub_plant', width:70, align:'center', stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant&withselect=true"}},
            {label:"ID", name:'op_id', index:'op_id', width:80, align:'center'},
            {label:"TANGGAL", name:'op_date', index:'op_date', width:50, align:'center'},
            {label:"JAM", name:'op_jam', index:'op_jam', width:40, align:'center'},
            {label:"PRESS", name:'op_press', index:'op_press', width:40, align:'center'},
            {label:"SHIFT", name:'op_shift', index:'op_shift', width:40, align:'center'},
            {label:"UKURAN", name:'op_ukuran', index:'op_ukuran', width:40, align:'center'},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"op_sub_plant asc, op_date desc, op_press",
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
    $("#op_id").val("");
    $("#op_tekanan").val("");
    $("#op_ukuran").val("");
    $("#op_date").val("");
    $("#op_press").html("");
    $("#op_jam").val("");
    $("#op_sub_plant").val("");
    $("#divdetail").html("");
    $("#op_id, #op_date, #op_press, #op_jam, #op_sub_plant").attr('disabled',false);
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
    formAwal();
    $("#aded").val("add");
    $("#op_id").val("OTOMATIS");
    $("#op_jam").val(moment().format("HH:mm"));
    $("#op_date").datepicker('setDate',moment().format("DD-MM-YYYY"));
    $("#boxAwal").hide();
    $("#boxEdit").show();  
    $("#op_sub_plant").val("");    
}

function lihatData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"view",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#op_id").val(o.op_id);
        $("#op_sub_plant").val(o.op_sub_plant);
        $("#op_date").val(o.op_date);
        $("#op_press").html(o.op_press);
        $("#op_tekanan").val(o.op_tekanan);
        $("#op_ukuran").val(o.op_ukuran);
        $("#op_jam").val(o.op_jam);
        $("#divdetail").html(o.detailtabel);
        $("#op_date, #op_press, #op_jam, #op_sub_plant").attr('disabled',true);
        $("#boxAwal").hide();
        $("#boxEdit").show();
    });
}

function editData(kode){
    $.post(frm+"?mode=detailtabel", {stat:"edit",kode:kode}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#aded").val("edit");
        $("#op_id").val(o.op_id);
        $("#op_sub_plant").val(o.op_sub_plant);
        $("#op_date").val(o.op_date);
        $("#op_press").html(o.op_press);
        $("#op_tekanan").val(o.op_tekanan);
        $("#op_ukuran").val(o.op_ukuran);
        $("#op_jam").val(o.op_jam);
        $("#divdetail").html(o.detailtabel);
        $("#op_date, #op_press, #op_jam, #op_sub_plant").attr('disabled',true);
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
            op_sub_plant:{required:true},
            op_date:{required:true},
            op_press:{required:true},
            op_jam:{required:true},
            op_tekanan:{required:true},
            op_ukuran:{required:true},
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
                alert("Perubahan data "+$("#op_id").val()+" berhasil disimpan");
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

    $("#op_date").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        endDate:'date',
        startDate:'-5d'
    });

    $('#op_jam').timepicker({
      showInputs: false,
      showMeridian: false,
      minuteStep: 5
    });

    tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    
    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#op_sub_plant").html(resp);
    });

    $('#op_sub_plant').change(function(){
        var subplan = this.value;
        $.post(frm+"?mode=cbopress", {subplan:subplan}, function(resp,stat){
            $("#op_press").html(resp);  
        });
    });

    $('#op_press').change(function(){
        var subplan2 = $('#op_sub_plant').val();
        var press2 = this.value;
        $.post(frm+"?mode=detailtabel", {stat:"add",subplan:subplan2,press:press2}, function(resp,stat){
            var o = JSON.parse(resp);
            $("#divdetail").html(o.detailtabel);
        });
    });
   
    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm",$("#tglFrom").val()+"@"+$("#tglTo").val());
    });
});


function hanyanumerik(id, value) {
    if (/([^.0123456789]|)/g.test(value)) { 
        $("#"+id).val(value.replace(/([^.0123456789])/g, ''));
    }
}

</script>