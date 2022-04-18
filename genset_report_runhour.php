<?php
include_once("libs/konfigurasi.php");
session_start();
$akses = $_SESSION[$app_id]['app_priv']['41'];
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
            <div class="box-body" id="boxAwal">
                <div class="modal fade" id="myModal" role="dialog">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Info Inputan Data</h4>
                            </div>
                            <div class="modal-body table-responsive" id="isiModal">...LOADING...</div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <form class="form-horizontal" id="frCari">
                    <div class="form-group">
                        <label class="col-sm-1 control-label" style="text-align:left;margin-top:3px;">Subplant :</label>
                        <div class="col-sm-2" style="margin-top:3px;">
                            <select class="form-control input-sm" id="cmbSubplan"></select>
                        </div>
                        <label class="col-sm-1 control-label" style="text-align:left;margin-top:3px;">From :</label>
                        <div class="col-sm-2" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="tglFrom" id="tglFrom">
                        </div>
                        <label class="col-sm-1 control-label" style="text-align:left;margin-top:3px;">To :</label>
                        <div class="col-sm-2" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="tglTo" id="tglTo">
                        </div>
                        <div class="col-sm-1" style="margin-top:3px;">
                            <button type="button" id="btnCari" class="btn btn-primary btn-sm"><span>GO</span></button>
                        </div>
                    </div>
                </form>
<!--                <form class="form-horizontal" id="frExport" style="display:none;">
                    <div class="form-group">
                        <label class="col-sm-1 control-label" style="text-align:left;margin-top:3px;">Export to :</label>
                        <div class="col-sm-2" style="margin-top:3px;">
                            <select class="form-control input-sm" id="cmbExport">
                                <option>XLSX</option>
                                <option>Excel 97-2003 (XLS)</option>
                                <option>PDF</option>
                            </select>
                        </div>
                        <div class="col-sm-1" style="margin-top:3px;">
                            <button type="button" id="btnExport" class="btn btn-success btn-sm"><span>Export</span></button>
                        </div>
                    </div>
                </form>-->
                <div class="box box-default box-solid" id="dvNodata" style="display:none;">
                    <div class="box-header with-border">
                        <center><strong>..Sedang Memuat Data..</strong></center>
                    </div>
                </div>
                <div id="kontensm">
                    <table id="tbl01"></table>
                </div>
                <div id="dvInfo" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var frm = "include/genset_report_runhour.inc.php";


    function lihatData(qch_id) {
        $('#myModal').modal('show');
        $.post(frm+"?mode=lihatdata", {qch_id:qch_id} , function(resp,stat){
            var o = JSON.parse(resp);
            $("#isiModal").html(o.hasil);
        });
    }

    $(document).ready(function () {
        var ubahUkuranJqGrid = function(){
            var vukur = $('#kontensm').width();
            $("#tbl01").setGridWidth(vukur, false);
        };
        $('#kontensm').resize(ubahUkuranJqGrid);

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
        }).val(moment().format("DD-MM-YYYY"));

        $("#tglTo").datepicker({
            autoclose:true,
            format:'dd-mm-yyyy',
            todayHighlight:true,
            endDate:'date',
            startDate:'date'
        }).val(moment().format("DD-MM-YYYY"));

        $.post(frm+"?mode=cbosubplant", function(resp,stat){
            $("#cmbSubplan").html(resp);
            var rulenya = {
                tglFrom:{required:true},
                tglTo:{required:true}
            };
            $("#frCari").validate({rules:rulenya});

            if($("#frCari").valid()) {
                $("#kontensm").html("");
                $("#frExport, #dvInfo").hide();
                $("#dvNodata").show();
                $.post(frm+"?mode=urai&tanggal=" +$("#tglFrom").val()+"@"+$("#tglTo").val()+"&subplan="+$("#cmbSubplan").val(), function(resp,stat){
                    var o = JSON.parse(resp);
                    $("#kontensm").html(o.detailtabel);
                    $("#frExport, #dvInfo").show();
                    $("#dvNodata").hide();
                });
            }
        });

        $('#btnCari').click(function(){
            if($("#frCari").valid()) {
                $("#kontensm").html("");
                $("#frExport, #dvInfo").hide();
                $("#dvNodata").show();
                $.post(frm+"?mode=urai&tanggal=" +$("#tglFrom").val()+"@"+$("#tglTo").val()+"&subplan="+$("#cmbSubplan").val(), function(resp,stat){
                    var o = JSON.parse(resp);
                    $("#kontensm").html(o.detailtabel);
                    $("#frExport, #dvInfo").show();
                    $("#dvNodata").hide();
                });
            }
        });

        $('#btnExport').click(function(){
            var frmt = $("#cmbExport").val();
            if (frmt == 'PDF') {
                opsi = "width=900,height=600,screenX=500,toolbars=1,location=0,directories=0,status=1,menubar=0,scrollbars=1,resizable='no'";
                window.open("include/mpdf-6.1.4/airreport.pdf.php?subplan="+$("#cmbSubplan").val()+"&tanggal="+$("#tglFrom").val()+"@"+$("#tglTo").val(),"",opsi);
            } else if (frmt == 'XLSX' || frmt == 'Excel 97-2003 (XLS)') {
                opsi = "width=900,height=600,screenX=500,toolbars=1,location=0,directories=0,status=1,menubar=0,scrollbars=1,resizable='no'";
                window.open(frm+"?mode=excel&tanggal=" +$("#tglFrom").val()+"@"+$("#tglTo").val()+"&subplan="+$("#cmbSubplan").val()+"&tipe="+frmt,"",opsi);
            }
        });
    });
</script>