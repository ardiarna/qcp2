<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta charset="utf-8" />
		<title>Report - Produksi </title>
		<meta name="description" content="Common form elements and layouts" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
		<!-- bootstrap & fontawesome -->
		<link rel="stylesheet" href="assets/css/bootstrap.min.css" />
		<link rel="stylesheet" href="assets/font-awesome/4.5.0/css/font-awesome.min.css" />
		<!-- page specific plugin styles -->
		<link rel="stylesheet" href="assets/css/jquery-ui.custom.min.css" />
		<link rel="stylesheet" href="assets/css/chosen.min.css" />
		<link rel="stylesheet" href="assets/css/bootstrap-datepicker3.min.css" />
		<link rel="stylesheet" href="assets/css/bootstrap-timepicker.min.css" />
		<link rel="stylesheet" href="assets/css/daterangepicker.min.css" />
		<link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.min.css" />
		<link rel="stylesheet" href="assets/css/bootstrap-colorpicker.min.css" />
		<!-- text fonts -->
		<link rel="stylesheet" href="assets/css/fonts.googleapis.com.css" />
		<!-- ace styles -->
		<link rel="stylesheet" href="assets/css/ace.min.css" class="ace-main-stylesheet" id="main-ace-style" />

		<!--[if lte IE 9]>
			<link rel="stylesheet" href="assets/css/ace-part2.min.css" class="ace-main-stylesheet" />
		<![endif]-->
		<link rel="stylesheet" href="assets/css/ace-skins.min.css" />
		<link rel="stylesheet" href="assets/css/ace-rtl.min.css" />

		<!--[if lte IE 9]>
		  <link rel="stylesheet" href="assets/css/ace-ie.min.css" />
		<![endif]-->

		<!-- inline styles related to this page -->

		<!-- ace settings handler -->
		<script src="assets/js/ace-extra.min.js"></script>
	</head>
	<body class="no-skin">
		<div id="navbar" class="navbar navbar-default          ace-save-state">
			<div class="navbar-container ace-save-state" id="navbar-container">
				
				<div class="navbar-header pull-left">
					<a href="index.html" class="navbar-brand">
						<small>
							<i class="fa fa-leaf"></i>
							Arwana Citramulia Tiles
						</small>
					</a>
				</div>
			</div><!-- /.navbar-container -->
		</div>
		<div class="main-container ace-save-state" id="main-container">
</div>
 <div class="space"></div>
<!-- /.ace-settings-container -->
<div class="row">
<div class="col-xs-12">
<center>    
<a href="MC.php"><button class="btn btn-lg btn-pink" >&nbsp;&nbsp;MC Test&nbsp;&nbsp;</button></a>
<a href="Glaze.php"><button class="btn btn-lg btn-success" >Glaze Line</button>
</a>
</center>
	<div class="col-sm-12">
	<div class="widget-box">
	<div class="widget-header">
	<h4 class="widget-title">Data Glaze</h4>
	</div>
    <center>
    <div class="space"></div>
	<div class="widget-body">
	<div class="widget-main">
	<form class="form-inline" method="POST" action="Glaze.php">
    <!-- <div class="widget-body">
    <div class="widget-main"> -->
	<label for="id-date-picker-1">Tanggal</label>
    <label> : </label>
    <label></label>
 	<div class="input-group">
    <input class="form-control date-picker"  <?php $validated = false;  
    if(isset($_POST['detail'])){echo "value='$_POST[tanggal]'"; $validated = true; } else if(isset($_POST['perjam'])){echo "value='$_POST[tanggal]'"; $validated= true; } else{ echo "value= ".date('Y-m-d')."";} ?> id="id-date-picker-1" name="tanggal" type="text" data-date-format="yyyy-mm-dd" size="10"/>
	<span class="input-group-addon">
	<i class="fa fa-calendar bigger-110"></i>
	</span>
	</div>
    <!-- </div>
    </div> -->
    <label></label>
    <label></label>
	<label for="Sub-Plant">Sub Plant </label>
    <label> : </label>
    <label></label>
 	<div class="input-group">
	<select class="form-control" id="form-field-select-1" name="sub_plant">
	 <?php $validated = false;  
        if(isset($_POST['detail'])){
        if($_POST['sub_plant']=='A'){
            echo "<option selected value='A';>A</option>
            <option value='B';>B</option>
            <option value='C';>C</option>";
        }else if($_POST['sub_plant']=='B'){
            echo "<option  value='A';>A</option>
            <option selected value='B';>B</option>
            <option value='C';>C</option>";
        }else {
            echo "<option value='A';>A</option>
            <option value='B';>B</option>
            <option selected value='C';>C</option>"; $validated = true;}
        }else if(isset($_POST['perjam'])){
        if($_POST['sub_plant']=='A'){
            echo "<option selected value='A';>A</option>
            <option value='B';>B</option>
            <option value='C';>C</option>";
        }else if($_POST['sub_plant']=='B'){
            echo "<option  value='A';>A</option>
            <option selected value='B';>B</option>
            <option value='C';>C</option>";
        }else {
            echo "<option value='A';>A</option>
            <option value='B';>B</option>
            <option selected value='C';>C</option>"; $validated = true;}
        }else {
           echo "<option selected value='A';>A</option>
           <option value='B';>B</option>
           <option value='C';>C</option>";
    }
    ?>
	</select>
	</div>
    <label></label>
    <label></label>
    <label for="Line">Line</label>
    <label> : </label>
    <label></label>
 	<div class="input-group">
    <select class="form-control" id="form-field-select-1" name="line">
    <?php $validated = false;  
        if(isset($_POST['detail'])){
        if($_POST['line']=='1'){
            echo "<option selected value='1';>1</option>
            <option value='2';>2</option>
            <option value='3';>3</option>";
        } else if($_POST['line']=='2'){
            echo "<option  value='1';>1</option>
            <option selected value='2';>2</option>
            <option value='3';>3</option>";
        } else {
            echo "<option value='1';>1</option>
            <option value='2';>2</option>
            <option selected value='3';>3</option>"; $validated = true;}
        }else if(isset($_POST['perjam'])){
        if($_POST['line']=='1'){
            echo "<option selected value='1';>1</option>
            <option value='2';>2</option>
            <option value='3';>3</option>";
        } else if($_POST['line']=='2'){
            echo "<option  value='1';>1</option>
            <option selected value='2';>2</option>
            <option value='3';>3</option>";
        } else {
            echo "<option value='1';>1</option>
            <option value='2';>2</option>
            <option selected value='3';>3</option>"; $validated = true;}
        } else {
           echo "<option selected value='1';>1</option>
           <option value='2';>2</option>
           <option value='3';>3</option>";
    }
    ?>
	</select>
	</div>
    <label></label>
	<button class="btn btn-danger" name="perjam">PERJAM</button>
    <label></label>
    <button class="btn btn-success" name="detail">DETAIL </button>
    
	</form>
    </center>
    <?php
    if(isset($_POST['detail'])){
    $tanggal=$_POST['tanggal'];
    $sub_plant=$_POST['sub_plant'];
    $line=$_POST['line'];
    // if(empty($tanggal) and empty($sub_plant)){
    // $msg="Pilih Tanggal dan Sub Plant !!!";
    // echo "<script type='text/javascript'>alert('$msg');</script>";
    {
    include_once "koneksimain.inc.php";
    $sql= "SELECT gqa_date,to_char(gqa_date,'HH24:MI') as jm, 
            case when gqa_app_type='S' then gqa_reo_val end as spray, 
            case when gqa_app_type='E' and gqa_reo='L' then gqa_reo_val end as el, 
            case when gqa_app_type='E' and gqa_reo='B' then gqa_reo_val end as eb, 
            case when gqa_app_type='E' and gqa_reo='V' then gqa_reo_val end as ev, 
            case when gqa_app_type='G' and gqa_reo='L' then gqa_reo_val end as gl, 
            case when gqa_app_type='G' and gqa_reo='B' then gqa_reo_val end as gb, 
            case when gqa_app_type='G' and gqa_reo='V' then gqa_reo_val end as gv, 
            case when gqa_app_type='P' and gqa_reo='L' then gqa_reo_val end as pl, 
            case when gqa_app_type='P' and gqa_reo='V' then gqa_reo_val end as pv, 
            case when gqa_app_type='P2' and gqa_reo='L' then gqa_reo_val end as p2l, 
            case when gqa_app_type='P2' and gqa_reo='V' then gqa_reo_val end as p2v, 
            case when gqa_app_type='P3' and gqa_reo='L' then gqa_reo_val end as p3l, 
            case when gqa_app_type='P3' and gqa_reo='V' then gqa_reo_val end as p3v, 
            case when gqa_app_type='R' and gqa_reo='L' then gqa_reo_val end as rl, 
            case when gqa_app_type='R' and gqa_reo='B' then gqa_reo_val end as rb, 
            case when gqa_app_type='R' and gqa_reo='V' then gqa_reo_val end as rv,
            gqa_ket 
            FROM gl_qc_app where gqa_line='$line' and gqa_sub_plant='$sub_plant' 
            and gqa_date>='$tanggal 00:00' and gqa_date<='$tanggal 23:59' 
            order by gqa_date";
    $hasil=pg_query($sql); 
    }
    ?>
    <div class="row">
    <div class="col-xs-12">
    <div class="clearfix">
    <div class="pull-right tableTools-container"></div>
    </div>
    <div class="table-header">
    </div>
    <div>
    <table id="dynamic-table" class="table table-striped table-bordered table-hover" align="center">
    <thead>
    <tr align="center">
    <th class="table-header" width="52" rowspan="3" scope="col" align="center">Jam</th>
    <th class="table-header" width="70" scope="col" align="center">Spray Air</th>
    <th class="table-header" colspan="3" scope="col" align="center">Engobe</th>
    <th class="table-header" colspan="3" scope="col">Glaze</th>
    <th class="table-header" colspan="2" scope="col">Pasta 1</th>
    <th class="table-header" colspan="2" scope="col">Pasta 2</th>
    <th class="table-header" colspan="2" scope="col">Pasta 3</th>
    <th class="table-header" colspan="3" scope="col">Granula</th>
    <th class="table-header" width="79" rowspan="3" scope="col">Keterangan</th>
    </tr>
    <tr>
    <th class="table-header" >Rata</th>
    <th class="table-header" width="47">LW</th>
    <th class="table-header" width="41">Berat</th>
    <th class="table-header" width="47">Visco</th>
    <th class="table-header" width="47">LW</th>
    <th class="table-header" width="41">Berat</th>
    <th class="table-header" width="47">Visco</th>
    <th class="table-header" width="9">LW</th>
    <th class="table-header" width="9">Visco</th>
    <th class="table-header" width="9">LW</th>
    <th class="table-header" width="9">Visco</th>
    <th class="table-header" width="9">LW</th>
    <th class="table-header" width="9">Visco</th>
    <th class="table-header" width="9">LW</th>
    <th class="table-header" width="9">Berat</th>
    <th class="table-header" width="9">Visco</th>
  </tr>
  <tr>
    <th class="table-header">Tidak</th>
    <th class="table-header">(gr/ltr)</th>
    <th class="table-header">(gr)</th>
    <th class="table-header">(detik)</th>
    <th class="table-header">(gr/ltr)</th>
    <th class="table-header">(gr)</th>
    <th class="table-header">(detik)</th>
    <th class="table-header">(gr/ltr)</th>
    <th class="table-header">(gr)</th>
    <th class="table-header">(detik)</th>
    <th class="table-header">(gr/ltr)</th>
    <th class="table-header">(detik)</th>
    <th class="table-header">(gr/ltr)</th>
    <th class="table-header">(detik)</th>
    <th class="table-header">(gr/ltr)</th>
    <th class="table-header">(detik)</th>
  </tr>
  <tr>
   <?php
    while($data= pg_fetch_array ($hasil)){
    echo" <tr >
    <td>".$data['jm']."</td>
    <td>".$data['spray']."</td>
    <td>".$data['el']."</td>
    <td>".$data['eb']."</td>
    <td>".$data['ev']."</td>
    <td>".$data['gl']."</td>
    <td>".$data['gb']."</td>
    <td>".$data['gv']."</td>
    <td>".$data['pl']."</td>
    <td>".$data['pv']."</td>
    <td>".$data['p2l']."</td>
    <td>".$data['p2v']."</td>
    <td>".$data['p3l']."</td>
    <td>".$data['p3v']."</td>
    <td>".$data['rl']."</td>
    <td>".$data['rb']."</td>
    <td>".$data['rv']."</td>
    <td>".$data['gqa_ket']."</td>
     ";}
    ?>
    </tr>
    </tbody>
    </table>
    </div>
    </div>
    </div>
      <?php
    }
    ?>
    <?php
    if(isset($_POST['perjam'])){
    $tanggal=$_POST['tanggal'];
    $tanggal2=date ("Y-m-d", strtotime ($tanggal ."+1 days"));
    $sub_plant=$_POST['sub_plant'];
    $line=$_POST['line'];
    // if(empty($tanggal) and empty($sub_plant)){
    // $msg="Pilih Tanggal dan Sub Plant !!!";
    // echo "<script type='text/javascript'>alert('$msg');</script>";
    {
    include_once "koneksimain.inc.php";
    $sql= "SELECT * from pcs_jam left outer join ( select jm,replarr(sp) as sp,replarr(el) as el,replarr(eb) as eb,replarr(ev) as ev, replarr(gl) as gl,replarr(gb) as gb,replarr(gv) as gv, replarr(pl) as pl,replarr(pv) as pv, replarr(p2l) as p2l,replarr(p2v) as p2v, replarr(p3l) as p3l,replarr(p3v) as p3v, replarr(rl) as rl,replarr(rb) as rb,replarr(rv) as rv ,replarrtext(ket)as ket from ( select jm, array_accum(sp) as sp, array_accum(el) as el,array_accum(eb) as eb,array_accum(ev) as ev, array_accum(gl) as gl,array_accum(gb) as gb,array_accum(gv) as gv, array_accum(pl) as pl,array_accum(pv) as pv, array_accum(p2l) as p2l,array_accum(p2v) as p2v, array_accum(p3l) as p3l,array_accum(p3v) as p3v, array_accum(rl) as rl,array_accum(rb) as rb, array_accum(rv) as rv,array_accum(gqa_ket) as ket from (select to_char(gqa_date,'HH24:00') as jm, case when gqa_app_type='S' then gqa_reo_val else 0 end as sp, case when gqa_app_type='E' and gqa_reo='L' then gqa_reo_val else 0 end as el, case when gqa_app_type='E' and gqa_reo='B' then gqa_reo_val else 0 end as eb, case when gqa_app_type='E' and gqa_reo='V' then gqa_reo_val else 0 end as ev, case when gqa_app_type='G' and gqa_reo='L' then gqa_reo_val else 0 end as gl, case when gqa_app_type='G' and gqa_reo='B' then gqa_reo_val else 0 end as gb, case when gqa_app_type='G' and gqa_reo='V' then gqa_reo_val else 0 end as gv, case when gqa_app_type='P' and gqa_reo='L' then gqa_reo_val else 0 end as pl, case when gqa_app_type='P' and gqa_reo='V' then gqa_reo_val else 0 end as pv, case when gqa_app_type='P2' and gqa_reo='L' then gqa_reo_val else 0 end as p2l, case when gqa_app_type='P2' and gqa_reo='V' then gqa_reo_val else 0 end as p2v, case when gqa_app_type='P3' and gqa_reo='L' then gqa_reo_val else 0 end as p3l, case when gqa_app_type='P3' and gqa_reo='V' then gqa_reo_val else 0 end as p3v, case when gqa_app_type='R' and gqa_reo='L' then gqa_reo_val else 0 end as rl, case when gqa_app_type='R' and gqa_reo='B' then gqa_reo_val else 0 end as rb, case when gqa_app_type='R' and gqa_reo='V' then
        gqa_reo_val else 0 end as rv,gqa_ket from gl_qc_app where gqa_plant=2  and gqa_sub_plant='$sub_plant' and gqa_line='$line' and gqa_date>='$tanggal 07:00' and gqa_date<'$tanggal2 07:00' ) as b group by jm ) as c ) as d on jm=pj_jam order by pj_no";

     $hasil=pg_query($sql); 
    }
    ?>
    <div class="space"></div>
    <div class="row">
    <div class="col-xs-12">
    <div class="clearfix">
    <div class="pull-right tableTools-container"></div>
    </div>
    <div class="table-header">
    </div>
    <table id="dynamic-table" class="table table-striped table-bordered table-hover" align="center">
    <thead>
    <tr align="center">
    <th class="table-header" width="52" rowspan="3" scope="col" align="center">Jam</th>
    <th class="table-header" width="70" scope="col" align="center">Spray Air</th>
    <th class="table-header" colspan="3" scope="col">Engobe</th>
    <th class="table-header" colspan="3" scope="col">Glaze</th>
    <th class="table-header" colspan="2" scope="col">Pasta 1</th>
    <th class="table-header" colspan="2" scope="col">Pasta 2</th>
    <th class="table-header" colspan="2" scope="col">Pasta 3</th>
    <th class="table-header" colspan="3" scope="col">Granula</th>
    <th class="table-header" width="79" rowspan="3" scope="col">Keterangan</th>
    </tr>
    <tr>
    <th class="table-header" >Rata</th>
    <th class="table-header" width="47">LW</th>
    <th class="table-header" width="41">Berat</th>
    <th class="table-header" width="47">Visco</th>
    <th class="table-header" width="47">LW</th>
    <th class="table-header" width="41">Berat</th>
    <th class="table-header" width="47">Visco</th>
    <th class="table-header" width="9">LW</th>
    <th class="table-header" width="9">Visco</th>
    <th class="table-header" width="9">LW</th>
    <th class="table-header" width="9">Visco</th>
    <th class="table-header" width="9">LW</th>
    <th class="table-header" width="9">Visco</th>
    <th class="table-header" width="9">LW</th>
    <th class="table-header" width="9">Berat</th>
    <th class="table-header" width="9">Visco</th>
  </tr>
  <tr>
    <th class="table-header">Tidak</th>
    <th class="table-header">(gr/ltr)</th>
    <th class="table-header">(gr)</th>
    <th class="table-header">(detik)</th>
    <th class="table-header">(gr/ltr)</th>
    <th class="table-header">(gr)</th>
    <th class="table-header">(detik)</th>
    <th class="table-header">(gr/ltr)</th>
    <th class="table-header">(gr)</th>
    <th class="table-header">(detik)</th>
    <th class="table-header">(gr/ltr)</th>
    <th class="table-header">(detik)</th>
    <th class="table-header">(gr/ltr)</th>
    <th class="table-header">(detik)</th>
    <th class="table-header">(gr/ltr)</th>
    <th class="table-header">(detik)</th>
  </tr>
    <?php

      while($data= pg_fetch_array($hasil)){
        
        if($data['sp']== NULL && $data['el']== NULL && $data['eb']== NULL && $data['ev']== NULL && $data['gl']== NULL && $data['gb']== NULL && $data['pl']== NULL && $data['pv']== NULL && $data['p2l']=== NULL && $data['p2v']=== NULL && $data['p3l']=== NULL && $data['p3v']=== NULL && $data['rl']=== NULL && $data['rb']=== NULL && $data['rv']=== NULL){
            $bg=" style='background:red'";
        }else{
            $bg="";
        }   
      echo" <tr>
    <td>".$data['pj_jam']."</td>
    <td $bg>".$data['sp']."</td>
    <td $bg>".$data['el']."</td>
    <td $bg>".$data['eb']."</td>
    <td $bg>".$data['ev']."</td>
    <td $bg>".$data['gl']."</td>
    <td $bg>".$data['gb']."</td>
    <td $bg>".$data['gv']."</td>
    <td>".$data['pl']."</td>
    <td>".$data['pv']."</td>
    <td>".$data['p2l']."</td>
    <td>".$data['p2v']."</td>
    <td>".$data['p3l']."</td>
    <td>".$data['p3v']."</td>
    <td>".$data['rl']."</td>
    <td>".$data['rb']."</td>
    <td>".$data['rv']."</td>
    <td>".$data['ket']."</td>
    </tr>";}
    ?>
    </thead>
    </table>
    </div>
    </div>
    </div>
    <?php
    }
    ?>
	</div>
    </div>
    </div>
    </div>
</div><!-- /.page-content -->
</div>
</div><!-- /.main-content -->
<div class="space"></div>
<div class="space"></div>
&nbsp; &nbsp;			
<div class="footer">
<div class="footer-inner">
<div class="footer-content">
<span class="bigger-120">
<span class="blue bolder">IT PROJECT</span>
REPORT PRODUCTION &copy; 2017
</span>
&nbsp; &nbsp;
</div>
</div>
</div>
</div>
		<script src="assets/js/jquery-2.1.4.min.js"></script>
		<script type="text/javascript">
			if('ontouchstart' in document.documentElement) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
		</script>
		<script src="assets/js/bootstrap.min.js"></script>
		<script src="assets/js/jquery-ui.custom.min.js"></script>
		<script src="assets/js/jquery.ui.touch-punch.min.js"></script>
		<script src="assets/js/chosen.jquery.min.js"></script>
		<script src="assets/js/spinbox.min.js"></script>
		<script src="assets/js/bootstrap-datepicker.min.js"></script>
		<script src="assets/js/bootstrap-timepicker.min.js"></script>
		<script src="assets/js/moment.min.js"></script>
		<script src="assets/js/daterangepicker.min.js"></script>
		<script src="assets/js/bootstrap-datetimepicker.min.js"></script>
		<script src="assets/js/bootstrap-colorpicker.min.js"></script>
		<script src="assets/js/jquery.knob.min.js"></script>
		<script src="assets/js/autosize.min.js"></script>
		<script src="assets/js/jquery.inputlimiter.min.js"></script>
		<script src="assets/js/jquery.maskedinput.min.js"></script>
		<script src="assets/js/bootstrap-tag.min.js"></script>
		<script src="assets/js/ace-elements.min.js"></script>
		<script src="assets/js/ace.min.js"></script>
		<script type="text/javascript">
			jQuery(function($) {
				$('#id-disable-check').on('click', function() {
					var inp = $('#form-input-readonly').get(0);
					if(inp.hasAttribute('disabled')) {
						inp.setAttribute('readonly' , 'true');
						inp.removeAttribute('disabled');
						inp.value="This text field is readonly!";
					}
					else {
						inp.setAttribute('disabled' , 'disabled');
						inp.removeAttribute('readonly');
						inp.value="This text field is disabled!";
					}
				});
			
			
				if(!ace.vars['touch']) {
					$('.chosen-select').chosen({allow_single_deselect:true}); 
					//resize the chosen on window resize
			
					$(window)
					.off('resize.chosen')
					.on('resize.chosen', function() {
						$('.chosen-select').each(function() {
							 var $this = $(this);
							 $this.next().css({'width': $this.parent().width()});
						})
					}).trigger('resize.chosen');
					//resize chosen on sidebar collapse/expand
					$(document).on('settings.ace.chosen', function(e, event_name, event_val) {
						if(event_name != 'sidebar_collapsed') return;
						$('.chosen-select').each(function() {
							 var $this = $(this);
							 $this.next().css({'width': $this.parent().width()});
						})
					});
			     	$('#chosen-multiple-style .btn').on('click', function(e){
						var target = $(this).find('input[type=radio]');
						var which = parseInt(target.val());
						if(which == 2) $('#form-field-select-4').addClass('tag-input-style');
						 else $('#form-field-select-4').removeClass('tag-input-style');
					});
				}
			    $('[data-rel=tooltip]').tooltip({container:'body'});
				$('[data-rel=popover]').popover({container:'body'});
			
				autosize($('textarea[class*=autosize]'));
				
				$('textarea.limited').inputlimiter({
					remText: '%n character%s remaining...',
					limitText: 'max allowed : %n.'
				});
			
				$.mask.definitions['~']='[+-]';
				$('.input-mask-date').mask('99/99/9999');
				$('.input-mask-phone').mask('(999) 999-9999');
				$('.input-mask-eyescript').mask('~9.99 ~9.99 999');
				$(".input-mask-product").mask("a*-999-a999",{placeholder:" ",completed:function(){alert("You typed the following: "+this.val());}});
			
			
			
				$( "#input-size-slider" ).css('width','200px').slider({
					value:1,
					range: "min",
					min: 1,
					max: 8,
					step: 1,
					slide: function( event, ui ) {
						var sizing = ['', 'input-sm', 'input-lg', 'input-mini', 'input-small', 'input-medium', 'input-large', 'input-xlarge', 'input-xxlarge'];
						var val = parseInt(ui.value);
						$('#form-field-4').attr('class', sizing[val]).attr('placeholder', '.'+sizing[val]);
					}
				});
			
				$( "#input-span-slider" ).slider({
					value:1,
					range: "min",
					min: 1,
					max: 12,
					step: 1,
					slide: function( event, ui ) {
						var val = parseInt(ui.value);
						$('#form-field-5').attr('class', 'col-xs-'+val).val('.col-xs-'+val);
					}
				});
			
			
				
				//"jQuery UI Slider"
				//range slider tooltip example
				$( "#slider-range" ).css('height','200px').slider({
					orientation: "vertical",
					range: true,
					min: 0,
					max: 100,
					values: [ 17, 67 ],
					slide: function( event, ui ) {
						var val = ui.values[$(ui.handle).index()-1] + "";
			
						if( !ui.handle.firstChild ) {
							$("<div class='tooltip right in' style='display:none;left:16px;top:-6px;'><div class='tooltip-arrow'></div><div class='tooltip-inner'></div></div>")
							.prependTo(ui.handle);
						}
						$(ui.handle.firstChild).show().children().eq(1).text(val);
					}
				}).find('span.ui-slider-handle').on('blur', function(){
					$(this.firstChild).hide();
				});
				
				
				$( "#slider-range-max" ).slider({
					range: "max",
					min: 1,
					max: 10,
					value: 2
				});
				
				$( "#slider-eq > span" ).css({width:'90%', 'float':'left', margin:'15px'}).each(function() {
					// read initial values from markup and remove that
					var value = parseInt( $( this ).text(), 10 );
					$( this ).empty().slider({
						value: value,
						range: "min",
						animate: true
						
					});
				});
				
				$("#slider-eq > span.ui-slider-purple").slider('disable');//disable third item
			
				
				$('#id-input-file-1 , #id-input-file-2').ace_file_input({
					no_file:'No File ...',
					btn_choose:'Choose',
					btn_change:'Change',
					droppable:false,
					onchange:null,
					thumbnail:false //| true | large
					//whitelist:'gif|png|jpg|jpeg'
					//blacklist:'exe|php'
					//onchange:''
					//
				});
				//pre-show a file name, for example a previously selected file
				//$('#id-input-file-1').ace_file_input('show_file_list', ['myfile.txt'])
			
			
				$('#id-input-file-3').ace_file_input({
					style: 'well',
					btn_choose: 'Drop files here or click to choose',
					btn_change: null,
					no_icon: 'ace-icon fa fa-cloud-upload',
					droppable: true,
					thumbnail: 'small'//large | fit
					//,icon_remove:null//set null, to hide remove/reset button
					/**,before_change:function(files, dropped) {
						//Check an example below
						//or examples/file-upload.html
						return true;
					}*/
					/**,before_remove : function() {
						return true;
					}*/
					,
					preview_error : function(filename, error_code) {
						//name of the file that failed
						//error_code values
						//1 = 'FILE_LOAD_FAILED',
						//2 = 'IMAGE_LOAD_FAILED',
						//3 = 'THUMBNAIL_FAILED'
						//alert(error_code);
					}
			
				}).on('change', function(){
					//console.log($(this).data('ace_input_files'));
					//console.log($(this).data('ace_input_method'));
				});
				
				
				//$('#id-input-file-3')
				//.ace_file_input('show_file_list', [
					//{type: 'image', name: 'name of image', path: 'http://path/to/image/for/preview'},
					//{type: 'file', name: 'hello.txt'}
				//]);
			
				
				
			
				//dynamically change allowed formats by changing allowExt && allowMime function
				$('#id-file-format').removeAttr('checked').on('change', function() {
					var whitelist_ext, whitelist_mime;
					var btn_choose
					var no_icon
					if(this.checked) {
						btn_choose = "Drop images here or click to choose";
						no_icon = "ace-icon fa fa-picture-o";
			
						whitelist_ext = ["jpeg", "jpg", "png", "gif" , "bmp"];
						whitelist_mime = ["image/jpg", "image/jpeg", "image/png", "image/gif", "image/bmp"];
					}
					else {
						btn_choose = "Drop files here or click to choose";
						no_icon = "ace-icon fa fa-cloud-upload";
						
						whitelist_ext = null;//all extensions are acceptable
						whitelist_mime = null;//all mimes are acceptable
					}
					var file_input = $('#id-input-file-3');
					file_input
					.ace_file_input('update_settings',
					{
						'btn_choose': btn_choose,
						'no_icon': no_icon,
						'allowExt': whitelist_ext,
						'allowMime': whitelist_mime
					})
					file_input.ace_file_input('reset_input');
					
					file_input
					.off('file.error.ace')
					.on('file.error.ace', function(e, info) {
						//console.log(info.file_count);//number of selected files
						//console.log(info.invalid_count);//number of invalid files
						//console.log(info.error_list);//a list of errors in the following format
						
						//info.error_count['ext']
						//info.error_count['mime']
						//info.error_count['size']
						
						//info.error_list['ext']  = [list of file names with invalid extension]
						//info.error_list['mime'] = [list of file names with invalid mimetype]
						//info.error_list['size'] = [list of file names with invalid size]
						
						
						/**
						if( !info.dropped ) {
							//perhapse reset file field if files have been selected, and there are invalid files among them
							//when files are dropped, only valid files will be added to our file array
							e.preventDefault();//it will rest input
						}
						*/
						
						
						//if files have been selected (not dropped), you can choose to reset input
						//because browser keeps all selected files anyway and this cannot be changed
						//we can only reset file field to become empty again
						//on any case you still should check files with your server side script
						//because any arbitrary file can be uploaded by user and it's not safe to rely on browser-side measures
					});
					
					
					/**
					file_input
					.off('file.preview.ace')
					.on('file.preview.ace', function(e, info) {
						console.log(info.file.width);
						console.log(info.file.height);
						e.preventDefault();//to prevent preview
					});
					*/
				
				});
			
				$('#spinner1').ace_spinner({value:0,min:0,max:200,step:10, btn_up_class:'btn-info' , btn_down_class:'btn-info'})
				.closest('.ace-spinner')
				.on('changed.fu.spinbox', function(){
					//console.log($('#spinner1').val())
				}); 
				$('#spinner2').ace_spinner({value:0,min:0,max:10000,step:100, touch_spinner: true, icon_up:'ace-icon fa fa-caret-up bigger-110', icon_down:'ace-icon fa fa-caret-down bigger-110'});
				$('#spinner3').ace_spinner({value:0,min:-100,max:100,step:10, on_sides: true, icon_up:'ace-icon fa fa-plus bigger-110', icon_down:'ace-icon fa fa-minus bigger-110', btn_up_class:'btn-success' , btn_down_class:'btn-danger'});
				$('#spinner4').ace_spinner({value:0,min:-100,max:100,step:10, on_sides: true, icon_up:'ace-icon fa fa-plus', icon_down:'ace-icon fa fa-minus', btn_up_class:'btn-purple' , btn_down_class:'btn-purple'});
			
				//$('#spinner1').ace_spinner('disable').ace_spinner('value', 11);
				//or
				//$('#spinner1').closest('.ace-spinner').spinner('disable').spinner('enable').spinner('value', 11);//disable, enable or change value
				//$('#spinner1').closest('.ace-spinner').spinner('value', 0);//reset to 0
			
			
				//datepicker plugin
				//link
				$('.date-picker').datepicker({
					autoclose: true,
					todayHighlight: true
				})
				//show datepicker when clicking on the icon
				.next().on(ace.click_event, function(){
					$(this).prev().focus();
				});
			
				//or change it into a date range picker
				$('.input-daterange').datepicker({autoclose:true});
			
			
				//to translate the daterange picker, please copy the "examples/daterange-fr.js" contents here before initialization
				$('input[name=date-range-picker]').daterangepicker({
					'applyClass' : 'btn-sm btn-success',
					'cancelClass' : 'btn-sm btn-default',
					locale: {
						applyLabel: 'Apply',
						cancelLabel: 'Cancel',
					}
				})
				.prev().on(ace.click_event, function(){
					$(this).next().focus();
				});
			
			
				$('#timepicker1').timepicker({
					minuteStep: 1,
					showSeconds: true,
					showMeridian: false,
					disableFocus: true,
					icons: {
						up: 'fa fa-chevron-up',
						down: 'fa fa-chevron-down'
					}
				}).on('focus', function() {
					$('#timepicker1').timepicker('showWidget');
				}).next().on(ace.click_event, function(){
					$(this).prev().focus();
				});
				
				
			
				
				if(!ace.vars['old_ie']) $('#date-timepicker1').datetimepicker({
				 //format: 'MM/DD/YYYY h:mm:ss A',//use this option to display seconds
				 icons: {
					time: 'fa fa-clock-o',
					date: 'fa fa-calendar',
					up: 'fa fa-chevron-up',
					down: 'fa fa-chevron-down',
					previous: 'fa fa-chevron-left',
					next: 'fa fa-chevron-right',
					today: 'fa fa-arrows ',
					clear: 'fa fa-trash',
					close: 'fa fa-times'
				 }
				}).next().on(ace.click_event, function(){
					$(this).prev().focus();
				});
				
			
				$('#colorpicker1').colorpicker();
				//$('.colorpicker').last().css('z-index', 2000);//if colorpicker is inside a modal, its z-index should be higher than modal'safe
			
				$('#simple-colorpicker-1').ace_colorpicker();
				//$('#simple-colorpicker-1').ace_colorpicker('pick', 2);//select 2nd color
				//$('#simple-colorpicker-1').ace_colorpicker('pick', '#fbe983');//select #fbe983 color
				//var picker = $('#simple-colorpicker-1').data('ace_colorpicker')
				//picker.pick('red', true);//insert the color if it doesn't exist
			
			
				$(".knob").knob();
				
				
				var tag_input = $('#form-field-tags');
				try{
					tag_input.tag(
					  {
						placeholder:tag_input.attr('placeholder'),
						//enable typeahead by specifying the source array
						source: ace.vars['US_STATES'],//defined in ace.js >> ace.enable_search_ahead
						/**
						//or fetch data from database, fetch those that match "query"
						source: function(query, process) {
						  $.ajax({url: 'remote_source.php?q='+encodeURIComponent(query)})
						  .done(function(result_items){
							process(result_items);
						  });
						}
						*/
					  }
					)
			
					//programmatically add/remove a tag
					var $tag_obj = $('#form-field-tags').data('tag');
					$tag_obj.add('Programmatically Added');
					
					var index = $tag_obj.inValues('some tag');
					$tag_obj.remove(index);
				}
				catch(e) {
					//display a textarea for old IE, because it doesn't support this plugin or another one I tried!
					tag_input.after('<textarea id="'+tag_input.attr('id')+'" name="'+tag_input.attr('name')+'" rows="3">'+tag_input.val()+'</textarea>').remove();
					//autosize($('#form-field-tags'));
				}
				
				
				/////////
				$('#modal-form input[type=file]').ace_file_input({
					style:'well',
					btn_choose:'Drop files here or click to choose',
					btn_change:null,
					no_icon:'ace-icon fa fa-cloud-upload',
					droppable:true,
					thumbnail:'large'
				})
				
				//chosen plugin inside a modal will have a zero width because the select element is originally hidden
				//and its width cannot be determined.
				//so we set the width after modal is show
				$('#modal-form').on('shown.bs.modal', function () {
					if(!ace.vars['touch']) {
						$(this).find('.chosen-container').each(function(){
							$(this).find('a:first-child').css('width' , '210px');
							$(this).find('.chosen-drop').css('width' , '210px');
							$(this).find('.chosen-search input').css('width' , '200px');
						});
					}
				})
				/**
				//or you can activate the chosen plugin after modal is shown
				//this way select element becomes visible with dimensions and chosen works as expected
				$('#modal-form').on('shown', function () {
					$(this).find('.modal-chosen').chosen();
				})
				*/
			
				
				
				$(document).one('ajaxloadstart.page', function(e) {
					autosize.destroy('textarea[class*=autosize]')
					
					$('.limiterBox,.autosizejs').remove();
					$('.daterangepicker.dropdown-menu,.colorpicker.dropdown-menu,.bootstrap-datetimepicker-widget.dropdown-menu').remove();
				});
			
			});
		</script>
	</body>
</html>
