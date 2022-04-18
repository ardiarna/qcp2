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

    function bikinTabel(data){
	    var out  = "<tr><th>NO</th>";
		    out += "<th>tanggal_peroleh</th>";
		    out += "<th>jenis_aktifa</th>";
		    out += "<th>nama_unit</th>";
		    out += "<th>qty</th>";
		    out += "<th>no_bukti</th>";
		    out += "<th>umur</th>";
		    out += "<th>harga_peroleh</th>";
		    out += "<th>akumulasi_penyusutan</th>";
		    out += "<th>nilai_buku</th>";
		    out += "</tr>";
	    var no = 1;
	    for(var row of data) {
	        out += "<tr><td>"+no+"</td>";
	        out += "<td>"+ExcelDateToJSDate(row['tanggal_peroleh'])+"</td>";
	        out += "<td>"+row['jenis_aktifa']+"</td>";
	        out += "<td>"+row['nama_unit']+"</td>";
	        out += "<td>"+row['qty']+"</td>";
	        out += "<td>"+row['no_bukti']+"</td>";
	        out += "<td>"+row['umur']+"</td>";
	        out += "<td>"+row['harga_peroleh']+"</td>";
	        out += "<td>"+row['akumulasi_penyusutan']+"</td>";
	        out += "<td>"+row['nilai_buku']+"</td>";
	        out += "</tr>";
	        no++;
	    }
	    return out;
	}

	function loadFile(event) {
		var plan = $("#plan").val();
		alasql('SELECT * FROM FILE(?,{headers:true})',[event],function(data){
			// $.post("include/aa.inc.php?mode=simpan&plan="+plan, {data}, function(resp,stat){
		 //    	var o = JSON.parse(resp);
		 //    	if(o.hasil=="OK") {
		 //    		alert(o.hasil);
		 //    	} else {
		 //    		$("#dvHasil").html(o.sql);
		 //    	}
		    	
		 //    });
			$("#tblsm").html(bikinTabel(data)); 
		});
	}
</script>

</body>
</html>