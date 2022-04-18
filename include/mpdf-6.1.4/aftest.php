<?php

$html = '<style type="text/css">
	table {
    font-family: arial, sans-serif;
    border-collapse: collapse;
    width: 100%;
}

td, th {
    border: 1px solid #dddddd;
    text-align: left;
    padding: 8px;
}

tr:nth-child(even) {
    background-color: #dddddd;
}
</style>
<table id="tblExport">
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>SHIFT - 1</td>
    </tr>
    <tr>
        <td>SUBPLANT</td>
        <td>TIPE</td>
        <td>CODE MATERIAL</td>
        <td>NO. BOX</td>
        <td>FORMULA (%)</td>
        <td>DW (kg)</td>
        <td>DW (%)</td>
        <td>DW (kg)</td>
        <td>02</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>A</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>BIIb PL.2A K-123</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>MATERIAL</td>
        <td>CLAY PARUNG PANJANG/CPN</td>
        <td>104</td>
        <td>7</td>
        <td>1190</td>
        <td>22.1</td>
        <td>1527.6</td>
        <td>0.00</td>
    </tr>
    <tr>
        <td></td>
        <td>MATERIAL</td>
        <td>CLAY SUKABUMI / CSK - MIJ</td>
        <td>103</td>
        <td>27</td>
        <td>4590</td>
        <td>21.6</td>
        <td>5854.59</td>
        <td>0.00</td>
    </tr>
    <tr>
        <td></td>
        <td>MATERIAL</td>
        <td>FELDSPAR PURWAKARTA / FPS</td>
        <td>106</td>
        <td>27</td>
        <td>4590</td>
        <td>21.8</td>
        <td>5869.57</td>
        <td>0.00</td>
    </tr>
    <tr>
        <td></td>
        <td>MATERIAL</td>
        <td>ANDESITE</td>
        <td>113</td>
        <td>11</td>
        <td>1870</td>
        <td>9.6</td>
        <td>2068.58</td>
        <td>0.00</td>
    </tr>
    <tr>

        <td></td>
        <td>MATERIAL</td>
        <td>AFAL</td>
        <td></td>
        <td>2</td>
        <td>340</td>
        <td>0</td>
        <td>340</td>
        <td>0.00</td>
    </tr>
    <tr>
        <td></td>
        <td>ADDITIVE</td>
        <td>WATER GLASS 52 BE</td>
        <td></td>
        <td>0.96</td>
        <td>163.2</td>
        <td>0</td>
        <td>163.2</td>
        <td>0.00</td>
    </tr>
    <tr>
        <td></td>
        <td>ADDITIVE</td>
        <td>SODIUM TRIPOSHPART PETROCENTRAL</td>
        <td></td>
        <td>0.025</td>
        <td>4.25</td>
        <td>0</td>
        <td>4.25</td>
        <td>0.00</td>
    </tr>
</table>';

// mulai menggunakan mPDF

include("mpdf.php");

/*lihat method constructor pada file mpdf.php

/  disana terdapat penjelasan lebih detail tentang parameternya,

atau lihatlah dokumentasinya */



// A4 maksudnya ukuran kertas

// $mpdf = new mPDF('utf-8', 'A4', 0, '', 10, 10, 5, 1, 1, 1, '');
$mpdf = new mPDF();

$mpdf->WriteHTML($html);

$out = $mpdf->Output();

echo "JAJA"; 
// $out;

?>