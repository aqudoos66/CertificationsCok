<?php
// Include database connection
include('../file/config.php');

// Get CNIC from the URL parameter
if (isset($_GET['cnic'])) {
    // $id = $_GET['id'];
    $cnic = $_GET['cnic'];

    // Fetch candidate data
    $stmt = $conn->prepare("SELECT * FROM candidates WHERE cnic = ? or id = ?");
    $stmt->bind_param("ss", $cnic, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $candidate = $result->fetch_assoc();
    $stmt->close();

    if (!$candidate) {
        die("Certificate not found.");
    }

    // Format dates
    $issueDate = date('d M, Y', strtotime($candidate['issue_date']));
    $fromDate = date('d M, Y', strtotime($candidate['from_date']));
    $toDate = date('d M, Y', strtotime($candidate['to_date']));
} else {
    die("No CNIC provided.");
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - <?php echo htmlspecialchars($candidate['candidate_name']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<!-- Print Button -->
<button onclick="window.print()" class="print-button">
    View Certificate 🖨️
</button>

<!-- Certificate Container -->
<div class="certificate-container">
    <!-- Header Section -->
    <div class="header">
        <div class="header-item">Sr. No. <span class="underline"><?php echo htmlspecialchars($candidate['serial_no']); ?></span></div>
        <div class="header-item">Registration No. <span class="underline"><?php echo htmlspecialchars($candidate['registration_no']); ?></span></div>
        <div class="header-item">CNIC No. <span class="underline"><?php echo htmlspecialchars($candidate['cnic']); ?></span></div>
    </div>

    <!-- Logo & Title -->
    <div class="title-section">
        <div> 
            <img src="assets/TTB-stevta.jpg" alt="Logo" class="logo">
        </div>
        <div style="display: flex; flex-direction: column; align-items: center;">
            <img src="assets/logo.png" alt="Logo" class="logo" style="width: 140px; height: 140px">
            <div class="title-text">
                <h1>CITY OF KNOWLEDGE</h1>
                <p>INSTITUTE OF PROFESSIONAL & VOCATIONAL TRAININGS</p>
            </div>
        </div>
        <div>
            <img src="assets/stevta-removebg-preview.png" alt="Logo" class="logo">
        </div>
    </div>

    <!-- Certificate Details -->
    <div class="certificate-body">
        <p>This Certificate is awarded to <span class="bold underline"> &emsp;&nbsp;<?php echo htmlspecialchars($candidate['candidate_name']); ?>&emsp;</span>&emsp; S/D/o&emsp; <span class="bold underline">&emsp;&emsp;<?php echo htmlspecialchars($candidate['father_name']); ?>&emsp;&emsp;</span></p>
        <p>at &emsp; <span class="bold underline"> &emsp;&emsp;Nawabshah &emsp;&emsp;</span>&emsp; on this&emsp; <span class="bold underline"> &emsp;<?php echo $issueDate; ?>&emsp;</span></p>
        <p> on successful completion of the certificate course of <span class="bold underline"> &emsp;<?php echo htmlspecialchars($candidate['course_name']); ?></span>&emsp; </p>
        <p> in &emsp;<span class="bold underline"> &emsp;&nbsp;<?php echo htmlspecialchars($candidate['grade']); ?>&emsp;&nbsp;</span>Grade  from &emsp;<span class="bold underline">&emsp;&emsp;<?php echo $fromDate; ?>&emsp;&emsp;</span> to <span class="bold underline">&emsp;&emsp;<?php echo $toDate; ?>&emsp;&emsp;</span></p>
        <p> from<u><b> &emsp;CITY OF KNOWLEDGE</b> (Institute of Professional & Vocational Trainings)&emsp;</u></p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-item">
            <?php if (!empty($candidate['director_signature'])): ?>
                <img src="../<?php echo htmlspecialchars($candidate['director_signature']); ?>" alt="Director Signature" width="80px" height="80px" class="signature">
            <?php endif; ?>
            <div class="line"></div>
            <p><b>DIRECTOR</b></p>
        </div>

        <div class="footer-item">
            <?php 
            // Generate QR code path
            $qrPath = '../webtest/qrcodes/' . $candidate['qr_code_filename'];
            if (file_exists($qrPath)): ?>
                <img src="<?php echo $qrPath; ?>" alt="QR Code" width="80px" height="80px" class="signature" style="position: relative; top: 15px;">
                <p style="font-size: 12px;">This certificate can be verified online via the QR code</p>
            <?php else: ?>
                <p style="font-size: 12px;">Verification QR code not available</p>
            <?php endif; ?>
        </div>

        <div class="footer-item">
            <?php if (!empty($candidate['trainer_signature'])): ?>
                <img src="../<?php echo htmlspecialchars($candidate['trainer_signature']); ?>" alt="Trainer Signature" width="80px" height="80px" class="signature">
            <?php endif; ?>
            <div class="line"></div>
            <p><b>TRAINER</b></p>
        </div>
    </div>
</div>

<script>
window.onload = function() {
    window.print();
};


window.onafterprint = function() {
    window.location.href = "index.php";
};
</script>

</body>
</html>
