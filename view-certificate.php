<?php
session_start();
require_once('file/config.php');

// Check if ID parameter exists
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid candidate ID");
}

$id = intval($_GET['id']);

// Fetch candidate data
$stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$candidate = $result->fetch_assoc();
$stmt->close();

if (!$candidate) {
    die("Candidate not found");
}

// Format dates
$issueDate = date('d M, Y', strtotime($candidate['issue_date']));
$fromDate = date('d M, Y', strtotime($candidate['from_date']));
$toDate = date('d M, Y', strtotime($candidate['to_date']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - <?php echo htmlspecialchars($candidate['candidate_name']); ?></title>
    <link rel="stylesheet" href="candidate/styles.css">
</head>
<body>


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
        <img src="assets/img/cok/logo.webp" alt="Logo" class="logo">
        <div class="title-text">
            <h1>CITY OF KNOWLEDGE</h1>
            <p>INSTITUTE OF PROFESSIONAL & VOCATIONAL TRAININGS</p>
        </div>
    </div>

    <!-- Certificate Details -->
    <div class="certificate-body">
        <p>This Certificate is awarded to <span class="bold underline"><?php echo htmlspecialchars($candidate['candidate_name']); ?></span> S/D/o <span class="bold underline"><?php echo htmlspecialchars($candidate['father_name']); ?></span></p>
        <p>at <span class="bold underline">Nawabshah</span> on this <span class="bold underline"><?php echo $issueDate; ?></span> on successful</p>
        <p>completion of the certificate course of <span class="bold underline"><?php echo htmlspecialchars($candidate['course_name']); ?></span> in <span class="bold underline"><?php echo htmlspecialchars($candidate['grade']); ?></span> Grade</p>
        <p>from <span class="bold underline"><?php echo $fromDate; ?></span> to <span class="bold underline"><?php echo $toDate; ?></span> from</p>
        <p><u><b>CITY OF KNOWLEDGE</b> (Institute of Professional & Vocational Trainings)</u></p>
    </div>

    <!-- Footer -->
<!-- Footer -->
<div class="footer">
    <div class="footer-item">
        <?php if (!empty($candidate['director_signature'])): ?>
            <img src="<?php echo htmlspecialchars($candidate['director_signature']); ?>" alt="Director Signature" width="80px" height="80px" class="signature">
        <?php endif; ?>
        <div class="line"></div>
        <p><b>DIRECTOR</b></p>
    </div>
    <div class="footer-item">
        <?php 
        // Generate QR code path
        $qrPath = 'webtest/qrcodes/' . $candidate['qr_code_filename'];
        if (file_exists($qrPath)): ?>
            <img src="<?php echo $qrPath; ?>" alt="QR Code" width="80px" height="80px" class="signature" style="position: relative; top: 15px;">
            <p style="font-size: 12px;">This certificate can be verified online via the QR code</p>
        <?php else: ?>
            <p style="font-size: 12px;">Verification QR code not available</p>
        <?php endif; ?>
    </div>
    <div class="footer-item">
        <?php if (!empty($candidate['trainer_signature'])): ?>
            <img src="<?php echo htmlspecialchars($candidate['trainer_signature']); ?>" alt="Trainer Signature" width="80px" height="80px" class="signature">
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

// After print dialog (whether print or cancel), redirect
window.onafterprint = function() {
    window.location.href = "view-candidate.php"; // Redirect to candidate list page
};
</script>


</body>
</html>
