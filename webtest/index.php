<!-- 
    // echo "Generating the QR Code";

    // include('phpqrcode/qrlib.php');

    // $tempDir = "qrcodes/";
    
    // $codeContents = 'Abdul Qudoos PHP Developer';
    
    // $fileName = '005_file_'.md5($codeContents).'.png';
    
    // $pngAbsoluteFilePath = $tempDir.$fileName;
    // $urlRelativeFilePath = $tempDir.$fileName;
    
    // if (!file_exists($pngAbsoluteFilePath)) {
    //     QRcode::png($codeContents, $pngAbsoluteFilePath);
    //     echo 'File generated!';
    //     echo '<hr />';
    // } else {
    //     echo 'File already generated! We can use this cached file to speed up site on common codes!';
    //     echo '<hr />';
    // }
    
    // echo 'Server PNG File: '.$pngAbsoluteFilePath;
    // echo '<hr />';
    
    // echo '<img src="'.$urlRelativeFilePath.'" />'; -->



<?php

echo "Starting QR code generation...<br>";

include('database.php');
include('phpqrcode/qrlib.php');

$tempDir = "qrcodes/";

$sql = "SELECT id, serial_no, candidate_name FROM candidates";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $candidateId = $row['id'];
        $serialNo = $row['serial_no'];
        $candidateName = $row['candidate_name'];
        $cnic = $row['cnic'];

        $codeContents = "https://certification.cokinstitute.com/E-certificate/index.php?cnic=$cnic";

        
        $cleanName = preg_replace('/[^A-Za-z0-9\-]/', '_', $candidateName);

        
        $fileName = $serialNo . '_' . $cleanName . '.png';

        $pngAbsoluteFilePath = $tempDir . $fileName;
        $urlRelativeFilePath = $tempDir . $fileName;

                if (!file_exists($pngAbsoluteFilePath)) {
            QRcode::png($codeContents, $pngAbsoluteFilePath);
            echo "QR Code generated for: $candidateName<br>";
        } else {
            echo "QR Code already exists for: $candidateName<br>";
        }
    }
} else {
    echo "No candidates found!";
}

$conn->close();

?>
