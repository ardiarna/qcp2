<!DOCTYPE html>
<html>
<head>
	<title>Test</title>
	<script src="js/alasql.min.js"></script>
	<script src="js/xlsx.core.min.js"></script>
	<script src="js/jquery.min.js"></script>
</head>
<body>
<select id="plan" name="plan">
	<option value="1">1. PT Arwana Citramulia Tbk</option>
	<option value="2">2. PT Arwana Nuansakeramik</option>
	<option value="3">3. PT Sinar Karya Duta Abadi</option>
	<option value="4">4. PT Arwana Anugerah Keramik</option>
	<option value="5">5. PT Sinar Karya Duta Abadi - P5</option>
	<option value="6">6. TEST</option>
</select>
<input id="readfile" type="file" onchange="loadFile(event)"/>
<div id="dvHasil"></div>
<table id="tblsm" border="1px"></table>

<script type="text/javascript">
	function ExcelDateToJSDate(serial) {
        var date = new Date((serial - (25567 + 2)) * 86400 * 1000);
		var localTime = new Date(date.getTime() + (new Date()).getTimezoneOffset() * 60000);
		var tanggal = localTime.getDate();
		var bulan = parseInt(localTime.getMonth())+1;
		var tahun = localTime.getFullYear();
		return tahun+"-"+bulan+"-"+tanggal;
    };

	function loadFile(event) {
		var plan = $("#plan").val();
		alasql('SELECT * FROM FILE(?,{headers:true})',[event],function(data){
			$.post("include/ab.inc.php?mode=simpan&plan="+plan, {data}, function(resp,stat) {
		    	var o = JSON.parse(resp);
		    	if(o.hasil=="OK") {
		    		alert(o.hasil);
		    	} else {
		    		$("#dvHasil").html(o.sql);
		    	}		    	
		    }); 
		});
	}
</script>

</body>
</html>