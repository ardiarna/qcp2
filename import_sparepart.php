<?php
include_once("libs/konfigurasi.php");
session_start(); 
$akses = $_SESSION[$app_id]['app_priv']['106'];
?>
<style type="text/css">
    #tblsm,
    .ui-jqgrid-htable {
        font-size:11px;
   }
    th {
      text-align:center;
   }     
select {
  font-family: 'FontAwesome', 'sans-serif';
}
</style>
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-body" id="boxAwal">
                <form class="form-horizontal" id="frCari">
                    <div class="form-group">
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
                <form class="form-horizontal" id="frEdit" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="col-sm-1 control-label" style="text-align:left;">KODE ASSETS</label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="kdasset" id="kdasset"> 
                        </div>
                        <label class="col-sm-1 control-label" style="text-align:left;">FILE</label>
                        <div class="col-sm-7" style="margin-top:3px;">  
                            <input type="file" name="file" required>
                        </div>      
                    </div>
                    <div class="form-group">
                        <label class="col-sm-1 control-label" style="text-align:left;">SHEET</label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <input type="number" min="1" class="form-control" name="sheet" id="sheet" value="1" required>
                        </div>
                        
                        <label class="col-sm-1 control-label" style="text-align:left;">AWAL</label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <input type="number" min="1" class="form-control" name="awal" id="awal" value="1" required>
                        </div>

                        <label class="col-sm-1 control-label" style="text-align:left;">AKHIR</label>
                        <div class="col-sm-3" style="margin-top:3px;">
                            <input type="number" min="1" class="form-control" name="akhir" id="akhir" value="1" required>
                        </div>
                    </div>
                        <button type="button" class="btn btn-primary btn-sm" onClick="simpanData()">Simpan</button> 
                        <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button>
                </form>    
            </div>
        </div>
    </div>
</div> 
<script type="text/javascript">
var frm = "include/import_sparepart.inc.php";
var vdropmenu = false;
var validator = "";

function tampilTabel(tblnya, pgrnya){
    var topnya = tblnya+"_toppager";
    var vshrinktofit = true;
    var vpanjanglayar = 150;
    if($(window).height() >= 520){vpanjanglayar = $(window).height()-(250+$("#frCari").height()+$(".content-header").height());}
    if($(window).width() <= 800){vshrinktofit = false;}
    jQuery(tblnya).jqGrid({
        url:frm + "?mode=urai",
        mtype:"POST",
        datatype:"json",
        colModel:[
            {label:"KD ASSET", name:'kdasset', index:'kdasset', width:80, align:'center'},
            {label:"ITEM KODE", name:'item_kode', index:'item_kode', width:50, align:'center'},
            {label:"ITEM NAMA", name:'item_nama', index:'item_nama', width:150, align:'center'},
            {label:"SATUAN", name:'item_satuan', index:'item_satuan', width:50, align:'center'}
        ],
        sortname:"kdasset asc, item_kode",
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
    $("#kdasset").val("");
    $("#sheet").val("1");
    $("#awal").val("1");
    $("#akhir").val("1");
    $("#boxEdit").hide();
    $("#boxAwal").show();
}

function tambahData(){
        $("#boxAwal").hide();
        $("#boxEdit").show();  
}

function simpanData(mode) {
    var rulenya = {
            kdasset:{required:true},
            sheet:{required:true},
            awal:{required:true},
            akhir:{required:true},
        };
    
    if(validator != ""){
        validator.destroy();
    }
    
    validator = $("#frEdit").validate({rules:rulenya});
    
    if($("#frEdit").valid()) { 
            var formData = new FormData($('#frEdit')[0]);
            $.ajax({
            url: frm+'?mode=prosesimport',
            type: 'POST',
            xhr: function() {
                var myXhr = $.ajaxSettings.xhr();
                return myXhr;
            },
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            
            success: function (data) {
                

            },
            error: function(data){
                
            },
            
        });
    }   
}


function hanyanumerik(id, value) {
    if (/([^.0123456789]|)/g.test(value)) { 
        $("#"+id).val(value.replace(/([^.0123456789])/g, ''));
    }
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
    

    tampilTabel("#tblsm","#pgrsm");
    

    $('#btnCari').click(function(){
        $.jgrid.gridUnload("#tblsm");
        tampilTabel("#tblsm","#pgrsm");
    });
});
</script>