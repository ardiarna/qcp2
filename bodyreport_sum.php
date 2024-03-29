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
                                <div class="input-group-addon"> Shift : </div>
                                <select class="form-control input-sm" name="cmbShift" id="cmbShift">
                                    <option>All</option>
                                    <option>1</option>
                                    <option>2</option>
                                    <option>3</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-5" style="margin-top:3px;">
                            <div class="input-group">
                                <div class="input-group-addon"> Dari : </div>
                                <input class="form-control input-sm" type="text" name="tglFrom" id="tglFrom">
                                <div class="input-group-addon"> s/d : </div>
                                <input class="form-control input-sm" type="text" name="tglTo" id="tglTo">
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
                            <div class="input-group">
                                <div class="input-group-addon"> Ekspor ke : </div>
                                <select class="form-control input-sm" id="cmbExport">
                                    <option value="XLSX">Excel (.xlsx)</option>
                                    <option value="XLS">Excel 97-2003 (.xls)</option>
                                    <option value="PDF">PDF</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-1" style="margin-top:3px;">
                            <button type="button" id="btnExport" class="btn btn-success btn-sm"><span>Ekspor</span></button>
                        </div>
                    </div>
                </form>
                <div class="box box-default box-solid" id="dvLoading" style="display:none;">
                    <div class="box-header with-border">
                        <center><strong>..Sedang Memuat Data..</strong></center>
                    </div>
                </div>
                <div id="kontensm"></div>
                <div id="dvInfo" style="display:none;"></div>
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/bodyreport_sum.inc.php";

function uraiData() {
    $("#kontensm").html("");
    $("#frExport, #dvInfo").hide();
    $("#dvLoading").show();
    $.post(frm+"?mode=urai&subplan="+$("#cmbSubplan").val()+"&tanggal=" +$("#tglFrom").val()+"@"+$("#tglTo").val()+"&shift="+$("#cmbShift").val(), function(resp,stat){
        var o = JSON.parse(resp);
        if(o.detailtabel == 'TIDAKADA') {
            $("#kontensm").html('<div style="background-color:orange;"><center><strong>..Tidak Ada Data..</strong></center></div>');
        } else {
            $("#kontensm").html(o.detailtabel);
            $("#frExport, #dvInfo").show();    
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
    }).on('changeDate', function(e) {
        var tglTo = $("#tglTo").val().split("-");
        var tglb = new Date(tglTo[2], parseInt(tglTo[1])-1, tglTo[0]);
        var tgla = new Date(e.date.getFullYear(), e.date.getMonth(), e.date.getDate());
        $("#tglTo").datepicker('setStartDate', tgla);
        if(tgla > tglb) {
            alert('Tanggal Dari tidak boleh lebih cepat dari Tanggal s/d, mohon ubah Tanggal s/d.');
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
            cmbSubplan:{required:true},
            cmbShift:{required:true},
            tglFrom:{required:true},
            tglTo:{required:true}
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
        var frmt = $("#cmbExport").val();
        if (frmt == 'PDF') {
            opsi = "width=900,height=600,screenX=500,toolbars=1,location=0,directories=0,status=1,menubar=0,scrollbars=1,resizable='no'";
            window.open("include/mpdf-6.1.4/bodyreport_sum.pdf.php?subplan="+$("#cmbSubplan").val()+"&tanggal="+$("#tglFrom").val()+"@"+$("#tglTo").val()+"&shift="+$("#cmbShift").val(),"",opsi);    
        } else if (frmt == 'XLSX') {
            opsi = "width=900,height=600,screenX=500,toolbars=1,location=0,directories=0,status=1,menubar=0,scrollbars=1,resizable='no'";
            window.open(frm+"?mode=excel&tipe=xlsx&subplan="+$("#cmbSubplan").val()+"&tanggal=" +$("#tglFrom").val()+"@"+$("#tglTo").val()+"&shift="+$("#cmbShift").val(),"",opsi);    
        } else if (frmt == 'XLS') {
            opsi = "width=900,height=600,screenX=500,toolbars=1,location=0,directories=0,status=1,menubar=0,scrollbars=1,resizable='no'";
            window.open(frm+"?mode=excel&tipe=xls&subplan="+$("#cmbSubplan").val()+"&tanggal=" +$("#tglFrom").val()+"@"+$("#tglTo").val()+"&shift="+$("#cmbShift").val(),"",opsi);    
        }
    });
});
</script>