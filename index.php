<?php
require_once 'libs/init.php'; 
if(!authenticated()){
  header("Location:login.php");
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Arwana QC</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/bootstrap-datepicker.min.css">
  <link rel="stylesheet" href="css/bootstrap-timepicker.min.css">
  <link rel="stylesheet" href="css/font-awesome.min.css">
  <link rel="stylesheet" href="css/ionicons.min.css">
  <link rel="stylesheet" href="css/select2.min.css">
  <link rel="stylesheet" href="css/jtsage-datebox.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.css">
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" href="css/ui.jqgrid-bootstrap.css" />
  <link rel="stylesheet" href="dist/css/hurufGoogle.css">
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <script src="js/alasql.min.js"></script>
  <script src="js/xlsx.core.min.js"></script>
  <script src="js/jquery.min.js"></script>
  <script src="js/jquery-ui.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/jquery.slimscroll.min.js"></script>
  <script src="js/fastclick.js"></script>
  <script src="js/bootstrap-datepicker.min.js"></script>
  <script src="js/bootstrap-timepicker.min.js"></script>
  <script src="js/i18n/grid.locale-id.js"></script>
  <script src="js/jquery.jqGrid.min.js"></script>
  <script src="js/jquery.resize.js"></script>
  <script src="js/jszip.min.js"></script>
  <script src="js/pdfmake.min.js"></script>
  <script src="js/vfs_fonts.js"></script>
  <script src="js/underscore-min.js"></script>
  <script src="js/bootstrap-formform.js"></script>
  <script src="js/moment-with-locales.min.js"></script>  
  <script src="js/select2.min.js"></script>
  <script src="js/FileSaver.min.js"></script>
  <script src="js/Blob.min.js"></script>
  <script src="js/xls.core.min.js"></script>
  <script src="js/tableexport.js"></script>
  <script src="js/highcharts.js"></script> 
  <script src="js/data.js"></script> 
  <script src="js/drilldown.js"></script>  
  <script src="js/jquery.validate.js"></script> 
  <script src="js/jtsage-datebox.min.js"></script>
  <script src="dist/js/adminlte.js"></script>
  <script src="dist/js/demo.js"></script>
  <script src="js/jquery.mask.min.js"></script>
  <script src="dist/sweetalert/sweetalert.min.js"></script>
  <script>
    $(document).ready(function () {
      $(".sidebar-menu").tree();
      // $("#kontenKepala").text("Penimbangan Material Body");
      // $("#kontenUtama").load("bodypenimbangan.php");
      $("a.menua").click(function(){
        $("a.menua").parent().removeClass("active");
        $(".treeview").removeClass("active");
        $("#kontenKepala").text("");
        $("#kontenUtama").html("");    
        $(this).parent().addClass("active");
        $(this).parents(".treeview").addClass("active");
        $("#kontenKepala").text($(this).attr("judul"));
        $("#kontenUtama").load($(this).attr("lk")+".php");
        $("#kepala").show();
        if($(window).width() <= 747){$('[data-toggle="push-menu"]').pushMenu('toggle');};
      });
      $("#aprofile").click(function(){
        $("#kontenKepala").text("");
        $("#kontenUtama").html("");
        $("#kontenKepala").text("Profil Pengguna");
        $("#kontenUtama").load("profile.php",function(){
          $.getScript("dist/js/adminlte.min.js"); 
        });
      });
    })

    function bodyload(){
      $("#kontenKepala").text('WELCOME');
      $("#kontenUtama").load("welcome.php");
    }
  </script>
</head>
<body class="hold-transition skin-blue-light sidebar-mini fixed" onload="bodyload()">
<div class="wrapper">
  <header class="main-header">
    <a href="index.php" class="logo">
      <span class="logo-mini"><img src="dist/img/logo_arwana.png"></span>
      <span class="logo-lg"><b>Arwana QC</b></span>
    </a>
    <nav class="navbar navbar-static-top">
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
      <a class="lobarugo" style="float:left;width:51%;height:50px;text-align:center;line-height:50px;color:#fff;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;" href="index.php">
        <span><b>Arwana QC</b></span>
      </a>
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="dist/img/usergbr.png" class="user-image" alt="User Image">
              <span class="hidden-xs">
                <?php echo $_SESSION[$app_id]['user']['user_name'] ?>
              </span>
            </a>
            <ul class="dropdown-menu">
              <li class="user-header">
                <img src="dist/img/usergbr.png" class="img-circle" alt="User Image">
                <p>
                  <?php echo $_SESSION[$app_id]['user']['first_name']." ".$_SESSION[$app_id]['user']['last_name']."<small>".$_SESSION[$app_id]['user']['plan_nama']."</small>" ?>
                </p>
              </li>
              <li class="user-footer">
                <div class="pull-left">
                  <a class="btn btn-default btn-flat" id="aprofile">Profil</a>
                </div>
                <div class="pull-right">
                  <a href="include/login.inc.php?mode=signout" class="btn btn-default btn-flat">Keluar</a>
                </div>
              </li>
            </ul>
          </li>
          <li>
            <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
          </li>
        </ul>
      </div>
    </nav>
  </header>
  <aside class="main-sidebar">
    <section class="sidebar">
      <?php echo tampilkan_menu(0, 0, $_SESSION[$app_id]['daftarmenu']); ?>
    </section>
  </aside>
  <div class="content-wrapper">
    <section class="content-header">
      <div class="col-md=12" style="display: none;" id="kepala">
            <ol class="breadcrumb" style="background-color: #3c8dbc">
                <li class="active" style="color: #fff"><i class="fa fa-cube"></i> <span id="kontenKepala"> WELCOME</span> </li>
            </ol>
    </div>
    </section>
    <section class="content" id="kontenUtama">
    </section>
  </div>
  <footer class="main-footer fixed">
    <b>Arwana</b> - 2018
    <a href="#" class="back-to-top" title="Back to top"><i class="fa fa-angle-double-up fa-2x" style="color:#ffffff"></i></a>
  </footer>
  <aside class="control-sidebar control-sidebar-dark">
    <div class="tab-content">
      <div class="tab-pane" id="control-sidebar-home-tab"></div>
    </div>
  </aside>
  <div class="control-sidebar-bg"></div>
</div>

<script src="js/ardi.js"></script>

</body>
</html>
