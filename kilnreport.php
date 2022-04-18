<style type="text/css">
    .adaborder {
        font-size:11px;
    }
    th {
        text-align:center;
    }  
    .jdlbwh{
        font-weight: bold;
        font-size: 12px;
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
                        <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">Subplant :</label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <select class="form-control input-sm" name="kl_sub_plant" id="kl_sub_plant"></select>
                        </div>

                        <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">Kiln :</label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <select class="form-control input-sm" id="id_kiln" name="id_kiln">
                                <option value="All">All</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">From :</label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="tglFrom" id="tglFrom">
                        </div>

                        <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">To :</label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="tglTo" id="tglTo">
                        </div>
                        <div class="col-sm-2" style="margin-top:3px;">
                            <button type="button" id="btnCari" class="btn btn-primary btn-sm"><span>GO</span></button>
                        </div>
                    </div>
                </form>
                <form class="form-horizontal" id="frExport" style="display:none;">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">Export to :</label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <select class="form-control input-sm" id="cmbExport">
                                <option value="XLSX">Excel (.xlsx)</option>
                                <option value="XLS">Excel 97-2003 (.xls)</option>
                                <option value="PDF">PDF</option>
                            </select>
                        </div>
                        <div class="col-sm-1" style="margin-top:3px;">
                            <button type="button" id="btnExport" class="btn btn-success btn-sm"><span>Export</span></button>
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
var frm = "include/kilnreport.inc.php";

function lihatData(kl_id) {
    $('#myModal').modal('show');
    $.post(frm+"?mode=lihatdata", {kl_id:kl_id} , function(resp,stat){
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


    $('#kl_sub_plant').change(function(){
        var subplan = this.value;
        $.post(frm+"?mode=cboklin", {subplan:subplan}, function(resp,stat){
            $("#id_kiln").html(resp);  
        });
    });

    $.post(frm+"?mode=cbosubplant", function(resp,stat){
        $("#kl_sub_plant").html(resp);
        var rulenya = {
            kl_sub_plant:{required:true},
            id_kiln:{required:true},
            tglFrom:{required:true}
        };
        $("#frCari").validate({rules:rulenya});

        if($("#frCari").valid()) {
            $("#kontensm").html("");
            $("#frExport, #dvInfo").hide();
            $("#dvLoading").show();
            $.post(frm+"?mode=urai&tanggal=" +$("#tglFrom").val()+"@"+$("#tglTo").val()+"&subplan="+$("#kl_sub_plant").val()+"&id_kiln="+$("#id_kiln").val(), function(resp,stat){
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
    });

  $('#btnCari').click(function(){
        if($("#frCari").valid()) {
            $("#kontensm").html("");
            $("#frExport, #dvInfo").hide();
            $("#dvLoading").show();
            $.post(frm+"?mode=urai&tanggal=" +$("#tglFrom").val()+"@"+$("#tglTo").val()+"&subplan="+$("#kl_sub_plant").val()+"&id_kiln="+$("#id_kiln").val(), function(resp,stat){
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
    });

    $('#btnExport').click(function(){
        var frmt = $("#cmbExport").val();
        if (frmt == 'PDF') {
            opsi = "width=900,height=600,screenX=500,toolbars=1,location=0,directories=0,status=1,menubar=0,scrollbars=1,resizable='no'";
            window.open("include/mpdf-6.1.4/kilnreport.pdf.php?subplan="+$("#kl_sub_plant").val()+"&tanggal="+$("#tglFrom").val()+"@"+$("#tglTo").val()+"&id_kiln="+$("#id_kiln").val(),"",opsi);    
        } else if (frmt == 'XLSX') {
            opsi = "width=900,height=600,screenX=500,toolbars=1,location=0,directories=0,status=1,menubar=0,scrollbars=1,resizable='no'";
            window.open(frm+"?mode=excel&tipe=xlsx&tanggal=" +$("#tglFrom").val()+"&subplan="+$("#kl_sub_plant").val()+"&id_kiln="+$("#id_kiln").val(),"",opsi);    
        } else if (frmt == 'XLS') {
            opsi = "width=900,height=600,screenX=500,toolbars=1,location=0,directories=0,status=1,menubar=0,scrollbars=1,resizable='no'";
            window.open(frm+"?mode=excel&tipe=xls&tanggal=" +$("#tglFrom").val()+"&subplan="+$("#kl_sub_plant").val()+"&id_kiln="+$("#id_kiln").val(),"",opsi);    
        } 
    });
});
</script>