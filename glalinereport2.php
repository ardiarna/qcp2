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
                        <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">Subplant :</label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <select class="form-control input-sm" name="qgh_sub_plant" id="qgh_sub_plant">
                                <option value="All">All</option>
                                <option value="A">A</option>
                            </select>
                        </div>

                        <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">Line :</label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <select class="form-control input-sm" id="gqa_line" name="gqa_line">
                                <option value="All">All</option>
                                <option value="01">01</option>
                                <option value="02">02</option>
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

                        <div class="col-sm-1" style="margin-top:3px;">
                            <button type="button" id="btnCari" class="btn btn-primary btn-sm"><span>GO</span></button>
                        </div>
                    </div>
                </form>
                <form class="form-horizontal" id="frExport" style="display:none;">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="text-align:left;margin-top:3px;">Export to :</label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <select class="form-control input-sm" id="cmbExport">
                                <option value="XLS">EXCEL</option>
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
var frm = "include/glalinereport2.inc.php";

function lihatData(qgh_id) {
    $('#myModal').modal('show');
    $.post(frm+"?mode=lihatdata", {qgh_id:qgh_id} , function(resp,stat){
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

            

$.post(frm+"?mode=urai&tanggal=" +$("#tglFrom").val()+"@"+$("#tglTo").val()+"&subplan="+$("#qgh_sub_plant").val()+"&gqa_line="+$("#gqa_line").val(), function(resp,stat){
    var o = JSON.parse(resp);
    $("#kontensm").html("");
    $("#frExport, #dvInfo").hide();
    $("#dvLoading").show();
    if(o.detailtabel == 'TIDAKADA') {
        $("#kontensm").html('<div style="background-color:orange;"><center><strong>..Tidak Ada Data..</strong></center></div>');
    } else {
        $("#kontensm").html(o.detailtabel);
        $("#frExport, #dvInfo").show();    
    }
    $("#dvLoading").hide();
});
       
    

    $('#btnCari').click(function(){
        var rulenya = {
            qgh_sub_plant:{required:true},
            tglFrom:{required:true},
            tglTo:{required:true}
        };
        $("#frCari").validate({rules:rulenya});
        
        if($("#frCari").valid()) {
            $("#kontensm").html("");
            $("#frExport, #dvInfo").hide();
            $("#dvLoading").show();
            $.post(frm+"?mode=urai&tanggal=" +$("#tglFrom").val()+"@"+$("#tglTo").val()+"&subplan="+$("#qgh_sub_plant").val()+"&gqa_line="+$("#gqa_line").val(), function(resp,stat){
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
            window.open("include/mpdf-6.1.4/glalinereport2.pdf.php?&tanggal=" +$("#tglFrom").val()+"@"+$("#tglTo").val()+"&subplan="+$("#qgh_sub_plant").val()+"&gqa_line="+$("#gqa_line").val(),"",opsi);    
        } else{
            opsi = "width=900,height=600,screenX=500,toolbars=1,location=0,directories=0,status=1,menubar=0,scrollbars=1,resizable='no'";
            window.open(frm+"?mode=excel&tanggal=" +$("#tglFrom").val()+"@"+$("#tglTo").val()+"&subplan="+$("#qgh_sub_plant").val()+"&gqa_line="+$("#gqa_line").val(),"",opsi);   
        }
    });
});
</script>