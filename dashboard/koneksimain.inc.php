<?php

$host        = "host=db.p2.arwanacitra.com";
$port        = "port=5432";
$dbname      = "dbname=armasi_local";
$credentials = "user=armasi password=XLKH53xeKBjPhL3OEKlA";

$db = pg_connect( "$host $port $dbname $credentials"  ) ;


if (!$db) {
print("Connection Failed");
exit;
}

//else print("Connection Success");

	
