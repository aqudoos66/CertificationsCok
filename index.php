<?php

include('file/config.php');

$candidate = null;
$certificateMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the CNIC from the form
    $cnic = $_POST['cnic'];

    // Fetch candidate data
    $stmt = $conn->prepare("SELECT * FROM candidates WHERE cnic = ?");
    $stmt->bind_param("s", $cnic);
    $stmt->execute();
    $result = $stmt->get_result();
    $candidate = $result->fetch_assoc();
    $stmt->close();

    // If the certificate exists, show success message and the "View Certificate" button
    if ($candidate) {
        $certificateMessage = "Congratulations! You have a certificate.";
        $certificateExists = true;
    } else {
        $certificateMessage = "Certificate not found for the provided CNIC.";
        $certificateExists = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>COK - Certificate</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <link rel='shortcut icon' type='image/x-icon' href='assets/img/cok/logo.webp' />
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>

      <nav class="navbar navbar-expand-lg main-navbar sticky">
        <div class="navbar-nav ml-auto">
          <a href="login.php" style="text-decoration:none; color:white;" class="btn btn-primary mr-3">Admin</a>
        </div>
      </nav>

      <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">
          <div class="sidebar-brand">
            <a href="index.php"> <img alt="image" src="assets/img/cok/logo.webp" class="header-logo" /> <span class="logo-name">COK</span>
            </a>
          </div>
        </aside>
      </div>

      <div class="main-content">
        <section class="section">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4>Search your certificate</h4>
                </div>
                <div class="card-body">
                  <form id="candidateForm" class="form-horizontal" action="index.php" method="POST">
                    <div class="form-row">
                      <div class="form-group col-md-12">
                        <label>CNIC</label>
                        <input type="text" class="form-control" name="cnic" id="cnic" pattern="[0-9]{5}-[0-9]{7}-[0-9]{1}" placeholder="12345-1234567-1" required>
                        <small class="form-text text-muted">Format: 12345-1234567-1</small>
                      </div>
                    </div>

                    <div class="form-group text-center">
                      <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                  </form>
                </div>
                
                <!-- Display Success Message or Error -->
                <?php
                if ($certificateMessage) {
                    echo "<div class='alert alert-".($certificateExists ? "success" : "danger")." text-center'>$certificateMessage</div>";
                    // Show "View Certificate" button if certificate exists
                    if ($certificateExists) {
                        echo "<div class='text-center'>
                                <a href='certificate.php?cnic=" . urlencode($cnic) . "' class='btn btn-success'>View Certificate</a>
                              </div>";
                    }
                }
                ?>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/js/page/index.js"></script>
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>
</body>

</html>
