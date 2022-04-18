<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['100'];
?>
<style type="text/css">
    .adaborder {
        font-size:11px;
    }
    th {
        text-align:center;
    }     
</style>
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="modal fade" id="myModal" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Info Inputan Data</h4>
                        </div>
                        <div class="modal-body table-responsive" id="isiModal">...Sedang Memuat Data...</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body" id="boxAwal">
                <form class="form-horizontal" id="frCari">
                    <div class="form-group">
                        <div class="col-sm-3" style="margin-top:3px;">
                            <div class="input-group">
                                <div class="input-group-addon"> Subplant : </div>
                                <select class="form-control input-sm" name="cmbSubplan" id="cmbSubplan"></select>  
                            </div>
                        </div>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <div class="input-group">
                                <div class="input-group-addon"> Tanggal : </div>
                                <input class="form-control input-sm" type="text" name="tglFrom" id="tglFrom">
                            </div>
                        </div>
                        <div class="col-sm-1" style="margin-top:3px;">
                            <button type="button" id="btnCari" class="btn btn-primary btn-sm"><span>GO</span></button>
                        </div>
                    </div>
                </form>
                <form class="form-horizontal" id="frExport" style="display:none;">
                    <div class="form-group">
                        <div class="col-sm-3" style="margin-top:3px;">
                            <button type="button" id="btnExport" class="btn btn-success btn-sm"><span>Ekspor Penimbangan ke PBD</span></button>
                        </div>
                    </div>
                </form>
                <div class="box box-default box-solid" id="dvLoading" style="display:none;">
                    <div class="box-header with-border">
                        <center><strong>..Sedang Memuat Data..</strong></center>
                    </div>
                </div>
                <div id="kontensm"></div>
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/bodytimbangkepbd.inc.php";

function uraiData() {
    $("#kontensm").html("");
    $("#frExport").hide();
    $("#dvLoading").show();
    $.post(frm+"?mode=urai&subplan="+$("#cmbSubplan").val()+"&tanggal=" +$("#tglFrom").val(), function(resp,stat){
        var o = JSON.parse(resp);
        if(o.detailtabel == 'TIDAKADA') {
            $("#kontensm").html('<div style="background-color:orange;"><center><strong>..Tidak Ada Data..</strong></center></div>');
        } else {
            $("#kontensm").html(o.detailtabel);
            $("#frExport").show();    
        }
        $("#dvLoading").hide();
    });
}

function lihatData(qbh_id) {
    $('#myModal').modal('show');
    $.post(frm+"?mode=lihatdata", {qbh_id:qbh_id} , function(resp,stat){
        var o = JSON.parse(resp);
        $("#isiModal").html(o.hasil);
    });
}
    
$(document).ready(function () {
    $("#tglFrom").datepicker({
        autoclose:true,
        format:'dd-mm-yyyy',
        todayHighlight:true,
        endDate:'date'
    }).val(moment().format("DD-MM-YYYY"));


    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#cmbSubplan").html(resp);
        var rulenya = {
            cmbSubplan:{required:true},
            tglFrom:{required:true}
        };
        $("#frCari").validate({rules:rulenya});

        if($("#frCari").valid()) {
            uraiData();
        }
    });
    
    $('#btnCari').click(function(){
        if($("#frCari").valid()) {
            uraiData();
        }
    });

    $('#btnExport').click(function(){
        $("#frExport").hide();
        $("#dvLoading").show();
        $.post(frm+"?mode=eksporpbd&subplan="+$("#cmbSubplan").val()+"&tanggal=" +$("#tglFrom").val(), function(resp,stat){
            var o = JSON.parse(resp);
            alert(o.detailtabel);
            $("#dvLoading").hide();
            uraiData();
        });
    });
});
</script>