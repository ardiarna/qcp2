<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['10'];
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
        <div class="box box-warning">
        <div class="modal fade" id="myModal" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Impor Material dari Armasi</h4>
                        </div>
                        <div class="modal-body table-responsive" id="isiModal" style="display:block;max-height:500px;overflow-y:auto;-ms-overflow-style:-ms-autohiding-scrollbar;">...LOADING...</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <form class="form-horizontal" id="frCari">
                    <div class="form-group">
                        <label class="col-sm-1 control-label" style="text-align:left;">Periode</label>
                        <div class="col-sm-2" style="margin-top:3px;">
                            <select class="form-control input-sm" id="cmbBulan">
                                <option value="All">All</option>
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                        <div class="col-sm-2" style="margin-top:3px;">
                            <select class="form-control input-sm" id="cmbTahun">
                                <option>All</option>
                                <option>2004</option>
                                <option>2005</option>
                                <option>2006</option>
                                <option>2007</option>
                                <option>2008</option>
                                <option>2009</option>
                                <option>2010</option>
                                <option>2011</option>
                                <option>2012</option>
                                <option>2013</option>
                                <option>2014</option>
                                <option>2015</option>
                                <option>2016</option>
                                <option>2017</option>
                                <option>2018</option>
                                <option>2019</option>
                                <option>2020</option>
                                <option>2021</option>
                                <option>2022</option>
                                <option>2023</option>
                                <option>2024</option>
                                <option>2025</option>
                            </select>
                        </div>
                        <div class="col-sm-4" style="margin-top:3px;">
                            <button type="button" id="btnCari" class="btn btn-primary btn-sm"> <span>GO </span> <span class="glyphicon glyphicon-ok-circle"></span> </button>
                        </div>
                    </div>
                </form>
                <div id="kontensm">
                    <table id="tblsm"></table>
                    <div id="pgrsm"></div>        
                </div>
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/mdformula.inc.php";
var vdropmenu = false;

function tampilTabel(tblnya, pgrnya, pJenis, pTanggal, pSubPlan = 'All'){
    var topnya = tblnya+"_toppager";
    var vshrinktofit = true;
    var vpanjanglayar = 150;
    if($(window).height() >= 520){vpanjanglayar = $(window).height()-(250+$("#frCari").height()+$(".content-header").height());}
    if($(window).width() <= 800){vshrinktofit = false;}
    jQuery(tblnya).jqGrid({
        url:frm + "?mode=urai&jenis="+pJenis+"&tanggal="+pTanggal+"&subplan="+pSubPlan,
        mtype:"POST",
        datatype:"json",
        colModel:[
            {label:"ID KOMPOSISI", name:'id_komposisi', index:'id_komposisi', width:80, hidden:true},
            {label:"SUBPLANT", name:'sub_plan', index:'sub_plan', width:70, stype:'select', searchoptions:{dataUrl:frm+"?mode=cbosubplant"}},
            {label:"KODE BODY", name:'komposisi_kode', index:'komposisi_kode', width:200},
            {label:"TANGGAL", name:'tanggal', index:'tanggal', width:70},
            {label:"VOLUME BALLMILL (KG)", name:'volume', index:'volume', width:100, sorttype:"int", align:'right', formatter:'integer'},
            {label:"KETERANGAN", name:'keterangan', index:'keterangan', width:200}
        ],
        sortname:"sub_plan asc,tanggal desc,komposisi_kode",
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
        subGrid:true,
        subGridRowExpanded:function(parentRowID, parentRowKey) {
            var $self = $(this);
            var vid = $self.jqGrid("getCell", parentRowKey, "id_komposisi");
            var vkode = $self.jqGrid("getCell", parentRowKey, "komposisi_kode");
            var vvolume = $self.jqGrid("getCell", parentRowKey, "volume");
            tampilSubDuaTabel(parentRowID, parentRowKey, pJenis, vkode, vid, vvolume);
        }
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

    jQuery(tblnya).jqGrid('navButtonAdd', topnya+"_left", {
        caption:"", buttonicon:'glyphicon-search', title:"Tampilkan baris pencarian",
        onClickButton:function () {
            this.toggleToolbar();
       }
    });

    <?php if ($akses[add]=='Y') { ?>
    jQuery(tblnya).jqGrid('navButtonAdd', topnya+"_left", {caption:"", buttonicon:'glyphicon-import', title:"Impor material dari Armasi",onClickButton:imporData});
    <?php } ?>

    $(pgrnya+"_center").hide();
}

function tampilSubDuaTabel(parentRowID, parentRowKey, pJenis, pKode, pId, pVolume) {
    var childGridID = parentRowID + "_table";
    $('#' + parentRowID).append('<table id=' + childGridID + '></table>');
    jQuery("#" + childGridID).jqGrid({
        url:frm + "?mode=sub2urai",
        mtype:"POST",
        postData:{'jenis':pJenis,'komposisi_kode':pKode,'id_komposisi':pId,'volume':pVolume},
        datatype:"json",
        colModel:[
            {label:'KELOMPOK', name:'kelompok', width:100, hidden:true},
            {label:'KODE MATERIAL', name:'item_kode', width:100},
            {label:'NAMA MATERIAL', name:'item_nama', width:200},
            {label:'SUB KONTRAKTOR', name:'company', width:200},
            {label:'FORMULA (%)', name:'formula', width:80, align:'right', formatter:'number', summaryType: "sum"},
            {label:'DW (kg)', name:'dw', width:80, align:'right', formatter:'number', summaryType: "sum"},
            {label:'M.C (%)', name:'mc', width:80, align:'right', formatter:'number', summaryType: "sum"},
            {label:'WW (kg)', name:'ww', width:80, align:'right', formatter:'number', summaryType: "sum"}
        ],
        styleUI:"Bootstrap",
        loadonce:true,
        width:'100%',
        height:'100%',
        footerrow:true,
        grouping: true,
        groupingView: {
            groupField: ["kelompok"],
            groupColumnShow: [false],
            groupText: ["<b>{0}</b>"],
            groupOrder: ["desc"],
            groupSummary: [true],
            groupCollapse: false  
        },
        loadComplete:function(id){
            var $self = $(this),
            sumFormula = $self.jqGrid("getCol", "formula", false, "sum");
            sumDW = $self.jqGrid("getCol", "dw", false, "sum");
            sumMC = $self.jqGrid("getCol", "mc", false, "sum");
            sumWW = $self.jqGrid("getCol", "ww", false, "sum");
            $self.jqGrid("footerData", "set", {company:"Total :", formula:sumFormula, dw:sumDW, mc:sumMC, ww:sumWW});

            rpp = $("#tblsm").jqGrid('getGridParam', 'records');
            rpp2 = $("#" + childGridID).jqGrid('getGridParam', 'records');
            var tinggi = ((parseInt(rpp) * 27) + 16) + ((parseInt(rpp2) * 27) + 16);
            $("#tblsm").setGridHeight(tinggi);
        }
   });
}

function imporData(){
    $('#myModal').modal('show');
    $.post(frm+"?mode=sinkitem", function(resp,stat){
        var o = JSON.parse(resp);
        $("#isiModal").html(o.hasil);
    });
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

    // $("#cmbBulan").val(moment().format("M"));
    $("#cmbTahun").val(moment().format("YYYY"));

    tampilTabel("#tblsm","#pgrsm","body","All-"+moment().format("YYYY"));
    
    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm","body",$("#cmbBulan").val()+"-"+$("#cmbTahun").val());
    });
});
</script>