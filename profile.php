<?php
include_once("libs/konfigurasi.php");
session_start(); 
?>
<div class="row">
	<div class="col-sm-12">
		<div class="box box-primary">
			<div class="box-body box-profile">
				<img class="profile-user-img img-responsive img-circle" src="dist/img/usergbr2.png" alt="User profile picture">
				<br>
				<form class="form-horizontal" id="frEdit">
					<input type="hidden" name="user_id" id="user_id" value= <?php echo $_SESSION[$app_id]['user']['user_id'] ?> >
					<div id="dvDepan">
						<ul class="list-group list-group-unbordered">
							<li class="list-group-item">
								<b class="auser_name">Username</b> <a class="pull-right auser_name"></a>
								<input class="pull-right" type="text" name="user_name" id="user_name" style="display:none">
							</li>
							<li class="list-group-item">
								<b class="afirst_name">Nama Depan</b> <a class="pull-right afirst_name"></a>
								<input class="pull-right" type="text" name="first_name" id="first_name" style="display:none">
							</li>
							<li class="list-group-item">
								<b class="alast_name">Nama Belakang</b> <a class="pull-right alast_name"></a>
								<input class="pull-right" type="text" name="last_name" id="last_name" style="display:none">
							</li>
							<li class="list-group-item">
								<b class="aplan_kode">Plant</b> <a class="pull-right aplan_kode"></a>
								<select class="pull-right" name="plan_kode" id="plan_kode" style="display:none"></select>
							</li>
						</ul>
						<div class="col-sm-6">
							<button type="button" class="btn btn-primary btn-block" onClick="gantiPwd()"><b>Ganti Kata Sandi</b></button>  
						</div>
						<div class="col-sm-6">
							<button type="button" class="btn btn-primary btn-block" onClick="simpanData()"><b>Simpan</b></button>  
						</div>
					</div>
					<div id="dvPwd" style="display:none;">
						<div class="form-group">
	                        <label class="col-sm-3 control-label" style="text-align:left;">Kata Sandi Lama</label>
	                        <div class="col-sm-8" style="margin-top:3px;">
	                            <input type="password" class="form-control input-sm" id="pwdlama" name="pwdlama" maxlength="20">    
	                        </div>
	                        <div class="col-sm-1" style="margin-top:3px;">
	                        	<button type="button" class="btn btn-default btn-sm" id="btnPwdLama"><i class="fa fa-binoculars"></i></button>
	                        </div>
	                    </div>
	                    <div class="form-group">
	                        <label class="col-sm-3 control-label" style="text-align:left;">Kata Sandi Baru</label>
	                        <div class="col-sm-8" style="margin-top:3px;">
	                            <input type="password" class="form-control input-sm" id="pwdbaru" name="pwdbaru" maxlength="20">    
	                        </div>
	                        <div class="col-sm-1" style="margin-top:3px;">
	                        	<button type="button" class="btn btn-default btn-sm" id="btnPwdBaru"><i class="fa fa-binoculars"></i></button>
	                        </div>
	                    </div>
	                    <div class="form-group">
	                        <label class="col-sm-3 control-label" style="text-align:left;">Ulangi Kata Sandi Baru</label>
	                        <div class="col-sm-8" style="margin-top:3px;">
	                            <input type="password" class="form-control input-sm" id="pwdkonf" name="pwdkonf" maxlength="20">   
	                        </div>
	                        <div class="col-sm-1" style="margin-top:3px;">
	                        	<button type="button" class="btn btn-default btn-sm" id="btnPwdKonf"><i class="fa fa-binoculars"></i></button>
	                        </div>
	                    </div>
	                    <div class="form-group">
                        <div class="col-sm-12" style="margin-top:3px;text-align:center;">
                            <button type="button" class="btn btn-primary btn-sm" onClick="simpanPwd()">Simpan</button> <button type="button" class="btn btn-warning btn-sm" onClick="formAwal()">Batal</button>
                        </div>
                    </div>
	                </div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
var frm = "include/profile.inc.php";
var validator = "";

function formAwal() {
	$('#pwdlama').val("");
	$('#pwdbaru').val("");
	$('#pwdkonf').val("");
	$("#dvPwd").hide();
	$("#dvDepan").show();
}

function gantiPwd() {
	$("#dvDepan").hide();
	$("#dvPwd").show();
}

function simpanPwd() {
    var rulenya = {
            pwdlama:{required:true,minlength:3},
            pwdbaru:{required:true,minlength:3},
            pwdkonf:{required:true,minlength:3,equalTo:"#pwdbaru"}
        };
    
    if(validator != "") {
        validator.destroy();
    }
    
    validator = $("#frEdit").validate({rules:rulenya});
    
    if($("#frEdit").valid()) {
        $.post(frm+"?mode=gantipwd", $("#frEdit").serialize(), function(resp,stat){
          if (resp=="OK") {
            alert("Kata Sandi untuk username "+$("#user_name").val()+" berhasil diubah");
            formAwal();
          }else if (resp=="PWDSALAH") {
            alert("Kata Sandi lama yang anda masukkan salah");
          }else{
            alert("Terjadi Error, mohon hubungi Administrator");
          }
        });
    } 
}

function simpanData() {
    if($("#user_name").val() == "" ) {
    	alert("Username harus diisi");
    	return false;
    }
    if($("#first_name").val() == "" ) {
    	alert("Nama Depan harus diisi");
    	return false;
    }
    if($("#plan_kode").val() == "" ) {
    	alert("Plant harus diisi");
    	return false;
    }
    $.post(frm+"?mode=edit", $("#frEdit").serialize(), function(resp,stat){
      if (resp=="OK") {
        alert("Perubahan data "+$("#user_name").val()+" berhasil disimpan");
      }else if (resp=="OK2") {
        alert("Perubahan data "+$("#user_name").val()+" berhasil disimpan");
        location.reload();
      }else{
        alert("Terjadi Error, mohon hubungi Administrator");
      }
    });
}

$(document).ready(function () {
	var user_id = $("#user_id").val();
	$.post(frm+"?mode=detailtabel", {stat:"edit",user_id:user_id}, function(resp,stat){
        var o = JSON.parse(resp);
        $("#user_name").val(o.user_name);
        $("#first_name").val(o.first_name);
        $("#last_name").val(o.last_name);
        $("#plan_kode").html(o.plan_kode);
        $("a.auser_name").html(o.user_name);
        $("a.afirst_name").html(o.first_name);
        $("a.alast_name").html(o.last_name);
        $("a.aplan_kode").html(o.plan_nama);
    });

    $("#user_name").afInputVal();
    $("#first_name").afInputVal();
    $("#last_name").afInputVal();
    $("#pwdlama").afInputVal();
    $("#pwdbaru").afInputVal();
    $("#pwdkonf").afInputVal();

	$(".auser_name").click(function(){
        $("a.auser_name").hide();
        $("#user_name").show(); 
        $("#user_name").focus();
    });
    $("#user_name").focusout(function(){
    	$("#user_name").hide();
    	$("a.auser_name").html(this.value);
        $("a.auser_name").show(); 
    });
    $(".afirst_name").click(function(){
        $("a.afirst_name").hide();
        $("#first_name").show(); 
        $("#first_name").focus();
    });
    $("#first_name").focusout(function(){
    	$("#first_name").hide();
    	$("a.afirst_name").html(this.value);
        $("a.afirst_name").show(); 
    });
    $(".alast_name").click(function(){
        $("a.alast_name").hide();
        $("#last_name").show(); 
        $("#last_name").focus();
    });
    $("#last_name").focusout(function(){
    	$("#last_name").hide();
    	$("a.alast_name").html(this.value);
        $("a.alast_name").show(); 
    });
    $(".aplan_kode").click(function(){
        $("a.aplan_kode").hide();
        $("#plan_kode").show(); 
        $("#plan_kode").focus();
    });
    $("#plan_kode").on("change focusout", function(){
    	$("#plan_kode").hide();
    	$("a.aplan_kode").html($("option:selected",this).text());
        $("a.aplan_kode").show(); 
    });
    $("#btnPwdLama").mousedown(function() {
	    $('#pwdlama').attr('type','text');
	});
	$("#btnPwdLama").mouseup(function() {
	    $('#pwdlama').attr('type','password');
	});
	$("#btnPwdBaru").mousedown(function() {
	    $('#pwdbaru').attr('type','text');
	});
	$("#btnPwdBaru").mouseup(function() {
	    $('#pwdbaru').attr('type','password');
	});
	$("#btnPwdKonf").mousedown(function() {
	    $('#pwdkonf').attr('type','text');
	});
	$("#btnPwdKonf").mouseup(function() {
	    $('#pwdkonf').attr('type','password');
	});
});
</script>