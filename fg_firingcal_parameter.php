<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['93'];
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
                <div id="kontensm">
                    <table id="tblsm"></table>
                    <div id="pgrsm"></div>        
                </div>
            </div>
            <div class="box-body" id="boxEdit" style="display: none;">
                    <h4><b>SUBPLANT : <span id="planid2"></span></b></h4>
                    <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Kembali</button>
                    <hr>
                    <div class="table-responsive" id="divdetail"></div>
                </form>    
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="frmmdl" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Parameter Information</h4>
        </div>
        <div class="modal-body">
            <h5><b><span id="grouptxt"></span></b></h5><h5><b><span id="parrenttxt"></span></b></h5>
            <form id="frm1">
                <input class="form-control input-sm" type="hidden" name="subplanid" id="subplanid" readonly>
                <input class="form-control input-sm" type="hidden" name="groupid" id="groupid" readonly>
                <input class="form-control input-sm" type="hidden" name="myid" id="myid" readonly>
                <input class="form-control input-sm" type="hidden" name="myparrent" id="myparrent" readonly>
                <input class="form-control input-sm" type="hidden" name="jnsaksi" id="jnsaksi" readonly>
                
                <div class="form-group">
                    <label for="fc_gddesc">Descripsi :</label>
                    <input type="text" class="form-control" id="fc_gddesc" name="fc_gddesc">
                </div>
                <div class="form-group">
                    <label for="fc_gdunit">Unit :</label>
                    <select class="form-control" id="fc_gdunit" name="fc_gdunit">
                    	<option value=""></option>
                    	<option value="c&deg;">c&deg;</option>
                    	<option value="mm">mm</option>
                    	<option value="kg/cm&sup2;">kg/cm&sup2;</option>
                    	<option value="menit">menit</option>
                    	<option value="%">%</option>
                    </select>
                </div>
            </form>  
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
          <a href="javascript:void(0)" class="btn btn-primary" onclick="simpanData2()">Simpan</a>
        </div>
      </div>
    </div>
</div>

<script type="text/javascript">
var frm = "include/fg_firingcal_parameter.inc.php";
var vdropmenu = false;
var validator = "";


function tampilTabel(tblnya, pgrnya, pTanggal, pSubPlan = 'All'){
    var topnya = tblnya+"_toppager";
    var vshrinktofit = true;
    var vpanjanglayar = 150;
    if($(window).height() >= 520){vpanjanglayar = $(window).height()-(250+$("#frCari").height()+$(".content-header").height());}
    if($(window).width() <= 800){vshrinktofit = false;}
    jQuery(tblnya).jqGrid({
        url:frm + "?mode=urai&subplan="+pSubPlan,
        mtype:"POST",
        datatype:"json",
        colModel:[
            {label:"SUBPLANT", name:'qc_subplan', index:'qc_subplan', width:70, align:'center'},
            {label:"KONTROL", name:'kontrol', index:'kontrol', width:60, align:'center'}
        ],
        sortname:"qc_subplan",
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


    $(pgrnya+"_center").hide();
}


function formAwal(){
    $("#boxAwal").show();
    $("#boxEdit").hide();
}

function tambahData($plan,$grup,$grouptxt,$parrent){
    document.getElementById("subplanid").value = $plan;
    document.getElementById("groupid").value = $grup;
    document.getElementById("myparrent").value = $parrent;
    document.getElementById("myid").value = '';
    document.getElementById("fc_gddesc").value = '';
    document.getElementById("fc_gdunit").value = '';
    document.getElementById("grouptxt").innerHTML = 'Parent : '+$grouptxt;
    document.getElementById("jnsaksi").value = 'add';
    $("#frmmdl").modal();
}
function editData2($plan,$grup,$desc,$unit,$id){
    document.getElementById("grouptxt").innerHTML = 'Edit Data :';
    document.getElementById("subplanid").value = $plan;
    document.getElementById("groupid").value = $grup;
    document.getElementById("myid").value = $id;
    document.getElementById("fc_gddesc").value = $desc;
    document.getElementById("fc_gdunit").value = $unit;
    document.getElementById("jnsaksi").value = 'edit';
    $("#frmmdl").modal();
}

function editData(plan){
    $.post(frm+"?mode=detailtabel", {plan:plan}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#boxAwal").hide();
        $("#planid2").html(plan);
        $("#boxEdit").show();
        $("#divdetail").html(o.detailtabel);
    });
}


function hapusData($plan,$grup,$id,$value){
    var r = confirm("Hapus Data : "+$value+" ?");
    if (r == true) {
        $.post(frm+"?mode=hapus", {subplanid:$plan,groupid:$grup,myid:$id}, function(resp,stat){
            alert(resp);
            editData($plan);
        });
    } else {
        return false;
    }
}

function simpanData2() {
    var rulenya = {
            subplanid:{required:true},
            fc_gddesc:{required:true},
        };
    
    if(validator != ""){
        validator.destroy();
    }
    
    validator = $("#frm1").validate({rules:rulenya});


    var mode = $("#jnsaksi").val();
    var subplanid = $("#subplanid").val();
    var fc_gddesc = $("#fc_gddesc").val();

    if($("#frm1").valid()) { 
        $.post(frm+"?mode="+mode, $("#frm1").serialize(), function(resp,stat){
        alert(resp);
        editData(subplanid);
        $('#frmmdl').modal('hide');
        });
    }   
}


function hideGrup(grupke) {
    $("#trgrup_ke_"+grupke).toggle();
}


$(document).ready(function (){
    var ubahUkuranJqGrid = function(){
        var vukur = $('#kontensm').width(); 
        if(vukur <= 550){
            $("#tblsm").setGridWidth(vukur, false);    
        } else {
            $("#tblsm").setGridWidth(vukur, true);    
        }
    };
    $('#kontensm').resize(ubahUkuranJqGrid);
    var ubahTinggiJqGrid = function(){
        var vpanjanglayar = 150;
        if($(window).height() >= 520){
            vpanjanglayar = $(window).height()-(230+$(".content-header").height());
        }
        $("#tblsm").setGridHeight(vpanjanglayar);
    };
    $('.content-header').resize(ubahTinggiJqGrid);

    tampilTabel("#tblsm","#pgrsm");

});
</script>

