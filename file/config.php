<?php
// session_start();


// Create connection
$conn = new mysqli('localhost', 'root', '', 'cok_dashboard');
// $conn = new mysqli('localhost', 'u160915605_certificatecok', 'CertificateCok@321', 'u160915605_certificatecok');


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>