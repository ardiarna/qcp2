<?php
include_once("libs/konfigurasi.php");
session_start();
global $app_id;
$akses = $_SESSION[$app_id]['app_priv']['40'];
?>
<link rel="stylesheet" href="dashboard/assets/css/daterangepicker.min.css">
<style type="text/css">
    #tblsm,
    .ui-jqgrid-htable {
        font-size: 11px;
    }

    th {
        text-align: center;
    }

    .genset-row {
        padding: 15px 0;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">

            <!-- Start Search Box -->
            <div class="box-body" id="boxAwal">
                <form class="form-horizontal" id="frCari">
                    <div class="form-group">
                        <label class="col-sm-1 control-label" style="text-align:left;">From</label>
                        <div class="col-sm-2" style="margin-top:3px;">
                            <input class="form-control input-sm" type="text" name="tglFrom" id="tglFrom">
                        </div>
                        <label class="col-sm-1 control-label" style="text-align:left;margin-top:3px;">To :</label>
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
            <!-- End Search Box -->

            <!-- Start Create/Update Box -->
            <div class="box-body" id="boxEdit" style="display: none;">
                <form class="form-horizontal" id="frEdit">
                    <input class="form-control input-sm" type="hidden" name="qgh_id" id="qgh_id" readonly>
                    <div class='container col col-md-12'>
                        <div class='row'>
                            <div class='col col-md-6'>
                                <label>Subplant</label>
                                <select class="form-control input-sm" name="qgh_sub_plant" id="qgh_sub_plant"></select>
                            </div>
                            <div class='col col-md-4'>
                                <label>Tanggal Input</label>
                                <input class='form-control input-sm' type='text' name='qgh_date' id="qgh_date" readonly>
                            </div>
                            <div class="col-md-2 bootstrap-timepicker">
                                <label>Jam</label>
                                <input class="form-control input-sm" type="text" name="qgh_time" id="qgh_time">
                            </div>
                        </div>
                </form>
                <div class="table-responsive" id="divdetail">
                </div>
            </div>
            <!-- End Create/Update Box -->

        </div>
    </div>
</div>
<script type="text/javascript">
    var frm = "include/genset_runhour.inc.php";
    var vdropmenu = false;
    var validator = "";

    function tampilTabel(tblnya, pgrnya, pTanggal, pSubPlan = 'All') {
        var topnya = tblnya + "_toppager";
        var vshrinktofit = true;
        var vpanjanglayar = 150;
        if ($(window).height() >= 520) {
            vpanjanglayar = $(window).height() - (250 + $("#frCari").height() + $(".content-header").height());
        }
        if ($(window).width() <= 470) {
            vshrinktofit = false;
        }
        jQuery(tblnya).jqGrid({
            url: frm + "?mode=urai&tanggal=" + pTanggal + "&subplan=" + pSubPlan,
            mtype: "POST",
            datatype: "json",
            colModel: [
                {label: "ID", name: 'qgh_id', index: 'qgh_id', width: 80},
                {
                    label: "SUBPLANT",
                    name: 'qgh_sub_plant',
                    index: 'qgh_sub_plant',
                    width: 70,
                    stype: 'select',
                    searchoptions: {dataUrl: frm + "?mode=cbosubplant&withselect=true"}
                },
                {label: "TANGGAL", name: 'qgh_date', index: 'qgh_date', width: 80},
                {label: "JAM", name: 'qgh_time', index: 'qgh_time', width: 70},
                {label: "KONTROL", name: 'kontrol', index: 'kontrol', width: 60, align: 'center'}
            ],
            sortname: "qgh_date desc,qgh_sub_plant asc,qgh_id",
            sortorder: 'desc',
            styleUI: "Bootstrap",
            hoverrows: false,
            loadonce: false,
            height: vpanjanglayar,
            rowNum: -1,
            rowList: [5, 10, 15, 20, "-1:All"],
            rownumbers: true,
            pager: pgrnya,
            editurl: frm,
            altRows: true,
            viewrecords: true,
            autowidth: true,
            shrinkToFit: vshrinktofit,
            toppager: true,
        });

        jQuery(tblnya).jqGrid('navGrid', topnya,
            {
                add: false,
                edit: false,
                del: false,
                view: false,
                search: false,
                refresh: false,
                alertwidth: 250,
                dropmenu: vdropmenu
            }, //navbar
            {}, //edit
            {}, //new
            {}, //del
            {}, //serch
            {}, //view
        );
        jQuery(tblnya).jqGrid('filterToolbar');
        $('.ui-search-toolbar').hide();
        $(topnya + "_center").hide();
        $(topnya + "_right").hide();
        $(topnya + "_left").attr("colspan", "3");

        <?php if ($akses[add] == 'Y') { ?>
        jQuery(tblnya).jqGrid('navButtonAdd', topnya + "_left", {
            caption: "",
            buttonicon: 'glyphicon-plus-sign',
            title: "Tambah data",
            onClickButton: tambahData
        });
        <?php } ?>

        jQuery(tblnya).jqGrid('navButtonAdd', topnya + "_left", {
            caption: "", buttonicon: 'glyphicon-search', title: "Tampilkan baris pencarian",
            onClickButton: function () {
                this.toggleToolbar();
            }
        });

        $(pgrnya + "_center").hide();
    }

    function tampilSubTabel(parentRowID, parentRowKey, pId) {
        var childGridID = parentRowID + "_table";
        $('#' + parentRowID).append('<table id=' + childGridID + '></table>');
        jQuery("#" + childGridID).jqGrid({
            url: frm + "?mode=suburai",
            mtype: "POST",
            postData: {'qgh_id': pId},
            datatype: "json",
            colModel: [
                {label: 'GROUP', name: 'qss_desc', width: 100, hidden: true},
                {label: 'NO', name: 'qsmd_sett_seq', width: 100},
                {label: 'DESKRIPSI', name: 'qssd_monitoring_desc', width: 200},
                {label: 'REMARK', name: 'qsmd_sett_remark', width: 100},
                {label: 'NILAI', name: 'qsmd_sett_value', width: 80, align: 'right', formatter: 'number'}
            ],
            styleUI: "Bootstrap",
            loadonce: true,
            width: 'auto',
            height: 'auto',
            grouping: true,
            groupingView: {
                groupField: ["qss_desc"],
                groupColumnShow: [false],
                groupText: ["<b>{0}</b>"],
                groupOrder: ["desc"],
                groupSummary: [true],
                groupCollapse: false
            }
        });
    }

    function formAwal() {
        $("#aded").val("");
        $("#qgh_id").val("");
        $("#qgh_date").val("");
        $("#qgh_time").val("");
        $("#qgh_sub_plant").val("");
        $("#divdetail").html("");
        $("#qgh_id, #qgh_date, #qgh_time, #qgh_sub_plant").attr('disabled', false);
        $("#boxEdit").hide();
        $("#boxAwal").show();
    }

    function tambahData() {
        $("#aded").val("add");
        $("#qgh_id").val("OTOMATIS");
        $("#qgh_date").datepicker('setDate', moment().format("DD-MM-YYYY"));
        $("#qgh_time").val(moment().format("HH:mm"));
        $.post(frm + "?mode=detailtabel", {stat: "add"}, function (resp, stat) {
            var o = JSON.parse(resp);
            $("#qgh_sub_plant").val(o.qgh_sub_plant);
            $("#divdetail").html(o.detailtabel);
            $("#boxAwal").hide();
            $("#boxEdit").show();
            $(".daterange-input").daterangepicker({
                timePicker: true,
                timePickerIncrement: 1,
                timePicker24Hour: true,
                locale: {
                    format: 'DD/MM/YYYY hh:mm'
                }
            });
        });
    }

    function lihatData(kode) {
        $.post(frm + "?mode=detailtabel", {stat: "view", kode: kode}, function (resp, stat) {
            var o = JSON.parse(resp);
            $("#qgh_id").val(o.qgh_id);
            $("#qgh_date").val(o.qgh_date);
            $("#qgh_time").val(o.qgh_time);
            $("#qgh_sub_plant").val(o.qgh_sub_plant);
            $("#divdetail").html(o.detailtabel);
            $("#boxEdit > input, #boxEdit > select").attr('disabled', true);
            $("#boxAwal").hide();
            $("#boxEdit").show();
        });
    }

    function editData(kode) {
        $.post(frm + "?mode=detailtabel", {stat: "edit", kode: kode}, function (resp, stat) {
            var o = JSON.parse(resp);
            $("#aded").val("edit");
            $("#qgh_id").val(o.qgh_id);
            $("#qgh_date").val(o.qgh_date);
            $("#qgh_time").val(o.qgh_time);
            $("#qgh_sub_plant").val(o.qgh_sub_plant);
            $("#divdetail").html(o.detailtabel);
            $("#qgh_sub_plant, #qgh_date, #qgh_time").attr('disabled', true);
            $("#boxAwal").hide();
            $("#boxEdit").show();
            $(".daterange-input").daterangepicker({
                timePicker: true,
                timePickerIncrement: 1,
                timePicker24Hour: true,
                locale: {
                    format: 'DD/MM/YYYY hh:mm'
                }
            });

        });
    }

    function hapusData(kode) {
        var r = confirm("Batalkan data pemakaian listrik dengan id " + kode + "?");
        if (r == true) {
            $.post(frm + "?mode=hapus", {kode: kode}, function (resp, stat) {
                if (resp == "OK") {
                    $.jgrid.gridUnload("#tblsm");
                    tampilTabel("#tblsm", "#pgrsm", $("#tglFrom").val() + "@" + $("#tglTo").val());
                } else {
                    alert(resp);
                }
            });
        } else {
            return false;
        }
    }

    function simpanData(mode) {
        var rulenya = {
            qgh_sub_plant: {required: true},
            qgh_date: {required: true},
            qgh_time: {required: true},
            qgh_run_hour: {required: true},
            qgh_meter_solar: {required: true},
            qgh_warming: {required: true}
        };
        if (validator !== "") validator.destroy()
        validator = $("#frEdit").validate({rules: rulenya})

        if ($("#frEdit").valid()) {

            $.post(frm + "?mode=" + mode, $("#frEdit").serialize(), function (resp, stat) {
                if (resp == "OK") {
                    (mode === "add") ? swal("Data berhasil disimpan", {icon: "success"}) : swal("Perubahan data " + $("#qgh_id").val() + " berhasil disimpan", {icon: "info"});
                    formAwal();
                    $.jgrid.gridUnload("#tblsm");
                    tampilTabel("#tblsm", "#pgrsm", $("#tglFrom").val() + "@" + $("#tglTo").val());
                } else {
                    swal(resp, {icon: "warning"});
                }
            });
            event.preventDefault()
        }
    }

    function hideGrup(grupke) {
        $(".trgrup_ke_" + grupke).toggle();
    }

    $(document).ready(function () {
        var ubahUkuranJqGrid = function () {
            var vukur = $('#kontensm').width();
            if (vukur <= 470) {
                $("#tblsm").setGridWidth(vukur, false);
            } else {
                $("#tblsm").setGridWidth(vukur, true);
            }
        };
        $('#kontensm').resize(ubahUkuranJqGrid);
        var ubahTinggiJqGrid = function () {
            var vpanjanglayar = 150;
            if ($(window).height() >= 520) {
                vpanjanglayar = $(window).height() - (250 + $("#frCari").height() + $(".content-header").height());
            }
            $("#tblsm").setGridHeight(vpanjanglayar);
        };
        $('#frCari').resize(ubahTinggiJqGrid);

        $("#tglFrom").datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            endDate: 'date'
        }).on('changeDate', function (e) {
            var tglTo = $("#tglTo").val().split("-");
            var tglb = new Date(tglTo[2], parseInt(tglTo[1]) - 1, tglTo[0]);
            var tgla = new Date(e.date.getFullYear(), e.date.getMonth(), e.date.getDate());
            $("#tglTo").datepicker('setStartDate', tgla);
            if (tgla > tglb) {
                alert('Tanggal From tidak boleh lebih cepat dari tanggal To, mohon ubah tanggal To.');
                $("#tglTo").datepicker('show');
            }
        }).val(moment().format("01-MM-YYYY"));

        $("#tglTo").datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            endDate: 'date',
            startDate: 'date'
        }).val(moment().format("DD-MM-YYYY"));

        $("#qgh_date").datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',
            endDate: 'date',
            startDate: '-1d'
        });

        $('#qgh_time').timepicker({
            showInputs: false,
            showMeridian: false,
            minuteStep: 5
        });



        tampilTabel("#tblsm", "#pgrsm", $("#tglFrom").val() + "@" + $("#tglTo").val());

        $.post(frm + "?mode=cbosubplant", function (resp, stat) {
            $("#qgh_sub_plant").html(resp);
        });

        $('#btnCari').click(function () {
            $.jgrid.gridUnload("#tblsm");
            tampilTabel("#tblsm", "#pgrsm", $("#tglFrom").val() + "@" + $("#tglTo").val());
        });

        $('#qgh_sub_plant').change(function () {
            var subplan = this.value;
            $.post(frm + "?mode=detailtabel", {stat: "add", subplan: subplan}, function (resp, stat) {
                var o = JSON.parse(resp);
                $("#divdetail").html(o.detailtabel);
                initDateTime()
            });
        });

    });

    function initDateTime()
    {
        $(".date-input").datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',
            endDate: 'date',
            startDate: '-1d'
        });
        $(".time-input").timepicker({
            showInputs: false,
            showMeridian: false,
            minuteStep: 5,
            defaultTime: false
        });
    }

    function hanyanumerik(id, value) {
        if (/([^.0123456789]|)/g.test(value)) {
            $("#" + id).val(value.replace(/([^.0123456789])/g, ''));
        }
    }

    function addItems() {
        let baris = $("div.genset-row");
        $("div.genset-row:last").after($("div.genset-row:first").clone());
        $("div.genset-row:last input[name='qgh_no_urut']").val($("div.genset-row:first input[name='qgh_no_urut']").val().slice(0, -1) + (baris.length + 1));
        $("div.genset-row:last input[name='qgh_run_hour']").attr('name', 'qgh_run_hour[' + (baris.length) + ']').attr('id', 'qgh_run_hour' + (baris.length));
        $("div.genset-row:last input[name='qgh_meter_solar']").attr('name', 'qgh_meter_solar[' + (baris.length) + ']').attr('id', 'qgh_meter_solar' + (baris.length));
        $("div.genset-row:last input[name='qgh_warming']").attr('name', 'qgh_warming[' + (baris.length) + ']').attr('id', 'qgh_warming' + (baris.length));
    }

    function dropItems(x) {
        let y = $("div.genset-row")
        if (y.length > 3) {
            $(x).closest(y).remove()
        } else {
            swal("Minimal input 1 data", {icon: "warning"})
        }
    }
</script>

<script src="dashboard/assets/js/daterangepicker.min.js"></script>
