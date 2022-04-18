<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['34'];
?>
<style type="text/css">
    #tbl01,
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
                        <label class="col-sm-1 control-label" style="text-align:left;margin-top:3px;">Subplant :</label>
                        <div class="col-sm-2" style="margin-top:3px;">
                            <select class="form-control input-sm" name="cmbSubplan" id="cmbSubplan"></select>
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
                <form class="form-horizontal" id="frExport" style="display:none;">
                    <div class="form-group">
                        <label class="col-sm-1 control-label" style="text-align:left;margin-top:3px;">Export to :</label>
                        <div class="col-sm-2" style="margin-top:3px;">
                            <select class="form-control input-sm" id="cmbExport">
                                <option>PDF</option>
                                <option>XLSX</option>
                            </select>
                        </div>
                        <div class="col-sm-1" style="margin-top:3px;">
                            <button type="button" id="btnExport" class="btn btn-success btn-sm"><span>Export</span></button>
                        </div>
                    </div>
                </form>
                <div class="box box-default box-solid" id="dvNodata" style="display:none;">
                    <div class="box-header with-border">
                        <center><strong>Tidak Ada Data</strong></center>
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
var frm = "include/gasreport.inc.php";

function tampilTabel(pTanggal, pSubPlan = 'All') {
    $("#frExport, #dvInfo").hide();
    $("#dvNodata").show();
    var vshrinktofit = false;
    jQuery("#tbl01").jqGrid('jqPivot',
        frm + "?mode=urai&tanggal=" +pTanggal+"&subplan="+pSubPlan,
        {
            frozenStaticCols:false,
            xDimension:[
                {dataName:'subplan', label:"SUBPLANT", width:70, isGroupField:true, sortable:false, coloptions:{sorting:false, columns:false, filtering:false, grouping:false}},
                {dataName:'tanggal', label:"TANGGAL", width:70, isGroupField:false, sortable:false, coloptions:{sorting:false, columns:false, filtering:false, grouping:false}}
            ],
            yDimension:[
                {dataName:'mesin'},
                {dataName:'line'},
                {dataName:'seq'}
            ],
             aggregates:[
                {member:'nilai', aggregator:function(value, fieldName, record) {
                    return (value || "") + " " + record[fieldName];
                }, width:80, align:'right', colmenu:false, sortable:false}
            ]
        },
        {
            sortname:'subplan, tanggal, mesin, line, seq',
            styleUI:"Bootstrap",
            altRows:true,
            autowidth:true,
            rowNum:1000,
            shrinkToFit:vshrinktofit,
            colMenu:false,
            height:"auto",
            viewrecords:true,
            loadComplete:function(data) {
                $("#frExport, #dvInfo").show();
                $("#dvNodata").hide();
            },
            gridComplete:function() {
                $.post(frm + "?mode=urai&tanggal=" +pTanggal+"&subplan="+pSubPlan, function(resp,stat){
                    var o = JSON.parse(resp);
                    var user = {};
                    var date = {};
                    for(var i = 0; i < o.rows.length; i++) {
                        if(o.rows[i].usercreate) {
                            user[o.rows[i].usercreate] = o.rows[i].usercreate;
                        }
                        if(o.rows[i].datecreate) {
                            date[o.rows[i].datecreate] = o.rows[i].datecreate;
                        }
                    }
                    var txtuser = "";
                    var txtdate = "";
                    for(x in user) {
                        txtuser += user[x] + ". ";
                    }
                    for(x in date) {
                        txtdate += date[x] + ". ";
                    } 
                    $("#dvInfo").html('<table class="table"><tr><td width="150">Data di-input oleh</td><td width="20">:</td><td>'+txtuser+'</td></tr><tr><td>Data di-input tanggal </td><td>:</td><td>'+txtdate+'</td></tr></table>');   
                });
            }
        }
    );
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
            cmbSubplan:{required:true},
            tglFrom:{required:true},
            tglTo:{required:true}
        };
        $("#frCari").validate({rules:rulenya});

        if($("#frCari").valid()) {
            tampilTabel($("#tglFrom").val()+"@"+$("#tglTo").val(),$("#cmbSubplan").val());
        }
    });
    
    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tbl01");
        if($("#frCari").valid()) {
            tampilTabel($("#tglFrom").val()+"@"+$("#tglTo").val(),$("#cmbSubplan").val());
        }
    });

    $('#btnExport').click(function(){
        var data = salinDataTabel('.ui-jqgrid-htable[aria-labelledby="gbox_tbl01"], #tbl01');
        var frmt = $("#cmbExport").val();
        var nama = "Material_Body_Report."+frmt.toLowerCase();
        var mystyle = {
            headers:false
        }
        if (frmt == 'PDF') {
            opsi = "width=900,height=600,screenX=500,toolbars=1,location=0,directories=0,status=1,menubar=0,scrollbars=1,resizable='no'";
            window.open("include/mpdf-6.1.4/gasreport.pdf.php?subplan="+$("#cmbSubplan").val()+"&tanggal="+$("#tglFrom").val()+"@"+$("#tglTo").val(),"",opsi);    
        } else if (frmt == 'XLSX') {
            alasql('SELECT * INTO XLSX(?,?) FROM ?',[nama,mystyle,data]);    
        } else if (frmt == 'XML') {
            alasql('SELECT * INTO XLSXML(?,?) FROM ?',[nama,mystyle,data]);
        } 
    });
});
</script>