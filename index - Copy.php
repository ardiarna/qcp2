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
  <title>Arwana Citramulia</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/bootstrap-datepicker.min.css">
  <link rel="stylesheet" href="css/bootstrap-timepicker.min.css">
  <link rel="stylesheet" href="css/font-awesome.min.css">
  <link rel="stylesheet" href="css/ionicons.min.css">
  <link rel="stylesheet" href="css/select2.min.css">
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
  <script src="js/jquery.min.js"></script>
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
  <script src="dist/js/adminlte.js"></script>
  <script src="dist/js/demo.js"></script>
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
        if($(window).width() <= 747){$('[data-toggle="push-menu"]').pushMenu('toggle');};
      });
      $("#aprofile").click(function(){
        $("#kontenKepala").text("");
        $("#kontenUtama").html("");
        $("#kontenKepala").text("User Profile");
        $("#kontenUtama").load("profile.php",function(){
          $.getScript("dist/js/adminlte.min.js"); 
        });
      });
    })
  </script>
</head>
<body class="hold-transition skin-blue-light sidebar-mini fixed">
<div class="wrapper">
  <header class="main-header">
    <a href="index.php" class="logo">
      <span class="logo-mini"><img src="dist/img/logo_arwana.png"></span>
      <span class="logo-lg"><b>Arwana</b> Citramulia</span>
    </a>
    <nav class="navbar navbar-static-top">
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
      <a class="lobarugo" style="float:left;width:51%;height:50px;text-align:center;line-height:50px;color:#fff;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;" href="index.php">
        <span><b>Arwana</b> Citramulia</span>
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
                  <a href="#" class="btn btn-default btn-flat" id="aprofile">Profile</a>
                </div>
                <div class="pull-right">
                  <a href="include/login.inc.php?mode=signout" class="btn btn-default btn-flat">Sign out</a>
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
      <ul class="sidebar-menu" data-widget="tree">
        <li class="active"><a href="index.php" class="menua"><i class="fa fa-dashboard"></i> <span>DASHBOARD</span></a></li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-folder"></i> <span>MASTER DATA</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class=""><a href="#" class="menua" lk="mdformula" judul="Komposisi Body"><i class="fa fa-circle-o"></i> Komposisi Body</a></li>
            <li class=""><a href="#" class="menua" lk="mdballmill" judul="Ball Mill Unit"><i class="fa fa-circle-o"></i> Ball Mill Unit</a></li>
            <li class=""><a href="#" class="menua" lk="mdbox" judul="Box Unit"><i class="fa fa-circle-o"></i> Box Unit</a></li>
            <li class=""><a href="#" class="menua" lk="mdsilo" judul="Silo Unit"><i class="fa fa-circle-o"></i> Silo Unit</a></li>
            <li class=""><a href="#" class="menua" lk="mdslip" judul="Slip Tank Unit"><i class="fa fa-circle-o"></i> Slip Tank Unit</a></li>
            <li class=""><a href="#" class="menua" lk="mdpress" judul="Press Unit"><i class="fa fa-circle-o"></i> Press Unit</a></li>
            <li class=""><a href="#" class="menua" lk="mdmp" judul="Mouldset Unit"><i class="fa fa-circle-o"></i> Mouldset Unit</a></li>
            <li class=""><a href="#" class="menua" lk="mdhd" judul="Horizontal Dryer Unit"><i class="fa fa-circle-o"></i> Horizontal Dryer Unit</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-folder"></i> <span>MATERIAL BODY</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class=""><a href="#" class="menua" lk="bodypenimbangan" judul="Penimbangan Material Body"><i class="fa fa-circle-o"></i> Penimbangan</a></li>
            <li class=""><a href="#" class="menua" lk="bodyreport" judul="Report Penimbangan Material Body"><i class="fa fa-book"></i> Report</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-folder"></i> <span>CONTROL BODY</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class=""><a href="#" class="menua" lk="conbodyinput" judul="Control Body"><i class="fa fa-circle-o"></i> Data Input</a></li>
            <li class=""><a href="#" class="menua" lk="conbodyreport" judul="Report Daily Ball Mill"><i class="fa fa-book"></i> Report</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-folder"></i> <span>GLAZE</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class=""><a href="#" class="menua" lk="glazeinput" judul="Glaze"><i class="fa fa-circle-o"></i> Data Input</a></li>
            <li class=""><a href="#" class="menua" lk="glazereport" judul="Report Glaze Preparation"><i class="fa fa-book"></i> Report</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-folder"></i> <span>SPRAY DRYER</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class=""><a href="#" class="menua" lk="spdryinput" judul="Spray Dryer"><i class="fa fa-circle-o"></i> Data Input</a></li>
            <li class=""><a href="#" class="menua" lk="spdryreport" judul="Report Monitor Setting Spray Dryer"><i class="fa fa-book"></i> Report</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-folder"></i> <span>PRESS &amp; DRYING</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class=""><a href="#" class="menua" lk="prdryinginput" judul="Press & Drying"><i class="fa fa-circle-o"></i> Data Input</a></li>
            <li class=""><a href="#" class="menua" lk="prdryingreport" judul="Report Press & Drying"><i class="fa fa-book"></i> Report</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-folder"></i> <span>F-TAG</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class=""><a href="#" class="menua" lk="ftaginput" judul="F-Tag"><i class="fa fa-circle-o"></i> Data Input</a></li>
            <li class=""><a href="#" class="menua" lk="ftagreport" judul="Report F-Tag"><i class="fa fa-book"></i> Report</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-gear"></i> <span>SETTING</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class=""><a href="#" class="menua" lk="appmenu" judul="Setting Menu"><i class="fa fa-circle-o"></i> Menu</a></li>
          </ul>
        </li>
      </ul>
    </section>
  </aside>
  <div class="content-wrapper">
    <section class="content-header">
      <h1 id="kontenKepala">
      </h1>
    </section>
    <section class="content" id="kontenUtama">
    </section>
  </div>
  <footer class="main-footer">
    <b>Arwana</b> Citramulia - 2018
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
