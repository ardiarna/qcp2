<?

Srequire_once 'libs/konfigurasi.php';
session_start(); 
if($_SESSION[$app_id]['authenticated'] == 1){
  header("Location:index.php");
  exit;
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Arwana QC | Masuk</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/font-awesome.min.css">
  <link rel="stylesheet" href="css/ionicons.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.css">
  <link rel="stylesheet" href="css/iCheck/square/blue.css">
  <link rel="stylesheet" href="dist/css/hurufGoogle.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <a href="https://www.arwanacitra.com/"><b> Arwana QC</b></a>
  </div>
  <div class="login-box-body">
    <p class="login-box-msg">Login untuk memulai sesi anda</p>
    <form id="frLogin">
      <input type="hidden" name="oper" value="signin">
      <div class="form-group has-feedback">
        <input type="text" class="form-control" placeholder="Username" name="uname" id="uname">
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" placeholder="Kata Sandi" name="pwd" id="pwd">
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="row" id="rwMessage"></div>
      <div class="row">
        <div class="col-xs-7">
          <div class="checkbox icheck">
            <label>
              <input type="checkbox" id="cbShowPass"> Perlihatkan Kata Sandi
            </label>
          </div>
        </div>
        <div class="col-xs-5">
          <button id="btLogin" type="button" class="btn btn-primary btn-block btn-flat">Masuk</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="css/iCheck/icheck.min.js"></script>
<script src="js/jquery.validate.js"></script>
<script>
  $(document).ready(function (){
    var frm = "include/login.inc.php";

    function fnlogin() {
      if($("#frLogin").valid()){
        $.post(frm, $("#frLogin").serialize(), function(resp,stat){
          if(resp=="OK"){
            self.location = "index.php";
          }else{
            $('#rwMessage').html('<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><h4><i class="icon fa fa-ban"></i>Login Gagal!</h4>'+resp+'</div>');
          }
        });  
      }
    }

    $("#frLogin").validate({
      rules:{
        uname:{required:true, minlength:3},
        pwd:{required:true, minlength:5}
      }
    });

    $('#cbShowPass').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      increaseArea: '20%'
    });

    $('#cbShowPass').on('ifChecked', function(event){
      $('#pwd').attr('type','text');
    });

    $('#cbShowPass').on('ifUnchecked', function(event){
      $('#pwd').attr('type','password');
    });

    $('#btLogin').click(function(){
      fnlogin();
    });

    $('#uname,#pwd').keypress(function(event){
      if(event.which==13){
        fnlogin();
      }
    });

  });
</script>
</body>
</html>
