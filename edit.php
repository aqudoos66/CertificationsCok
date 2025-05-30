<?php
session_start();
include('file/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: view-candidate.php");
    exit();
}

$candidateId = intval($_GET['id']);
$errors = [];

// Fetch existing candidate data
$stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->bind_param("i", $candidateId);
$stmt->execute();
$result = $stmt->get_result();
$candidate = $result->fetch_assoc();
$stmt->close();

if (!$candidate) {
    header("Location: view-candidate.php");
    exit();
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validation
    if (!preg_match('/^[A-Za-z0-9]{1,20}$/', $_POST['serialNo'])) {
        $errors[] = "Serial No must be alphanumeric (max 20 characters)";
    }
    if (!preg_match('/^[A-Za-z0-9-]{1,20}$/', $_POST['regNo'])) {
        $errors[] = "Registration No must be alphanumeric with hyphens only";
    }
    if (!preg_match('/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/', $_POST['cnic'])) {
        $errors[] = "CNIC must be in format 12345-1234567-1";
    }
    if (!preg_match('/^[A-Za-z ]{3,50}$/', $_POST['candidateName'])) {
        $errors[] = "Candidate Name must be 3-50 letters and spaces only";
    }
    if (!preg_match('/^[A-Za-z ]{3,50}$/', $_POST['fatherName'])) {
        $errors[] = "Father Name must be 3-50 letters and spaces only";
    }

    $fromDate = new DateTime($_POST['fromDate']);
    $toDate = new DateTime($_POST['toDate']);
    $issueDate = new DateTime($_POST['issueDate']);
    $today = new DateTime();

    if ($toDate <= $fromDate) {
        $errors[] = "'To' date must be after 'From' date";
    }

    if ($issueDate > $today) {
        $errors[] = "Issue date cannot be in the future";
    }

    if (empty($errors)) {
        $serialNo = $conn->real_escape_string($_POST['serialNo']);
        $regNo = $conn->real_escape_string($_POST['regNo']);
        $cnic = $conn->real_escape_string($_POST['cnic']);
        $candidateName = $conn->real_escape_string($_POST['candidateName']);
        $fatherName = $conn->real_escape_string($_POST['fatherName']);
        $courseName = $conn->real_escape_string($_POST['courseName']);
        $issueDateStr = $conn->real_escape_string($_POST['issueDate']);
        $grade = $conn->real_escape_string($_POST['grade']);
        $fromDateStr = $conn->real_escape_string($_POST['fromDate']);
        $toDateStr = $conn->real_escape_string($_POST['toDate']);

        // Handle file uploads
        $uploadDir = 'uploads/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 1024 * 100; // 100KB

        $directorSignature = $candidate['director_signature'];
        $trainerSignature = $candidate['trainer_signature'];

        // Director Signature
        if (isset($_FILES['directorSignature']) && $_FILES['directorSignature']['error'] == 0) {
            if (in_array($_FILES['directorSignature']['type'], $allowedTypes) && $_FILES['directorSignature']['size'] <= $maxSize) {
                $ext = pathinfo($_FILES['directorSignature']['name'], PATHINFO_EXTENSION);
                $filename = 'director_' . uniqid() . '.' . $ext;
                $targetPath = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['directorSignature']['tmp_name'], $targetPath)) {
                    $directorSignature = $targetPath;
                }
            }
        }

        // Trainer Signature
        if (isset($_FILES['trainerSignature']) && $_FILES['trainerSignature']['error'] == 0) {
            if (in_array($_FILES['trainerSignature']['type'], $allowedTypes) && $_FILES['trainerSignature']['size'] <= $maxSize) {
                $ext = pathinfo($_FILES['trainerSignature']['name'], PATHINFO_EXTENSION);
                $filename = 'trainer_' . uniqid() . '.' . $ext;
                $targetPath = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['trainerSignature']['tmp_name'], $targetPath)) {
                    $trainerSignature = $targetPath;
                }
            }
        }

        // Update query
        $stmt = $conn->prepare("UPDATE candidates SET 
            serial_no = ?, registration_no = ?, cnic = ?, candidate_name = ?, father_name = ?, 
            course_name = ?, issue_date = ?, grade = ?, from_date = ?, to_date = ?, 
            director_signature = ?, trainer_signature = ?
            WHERE id = ?");
        
        $stmt->bind_param("ssssssssssssi", $serialNo, $regNo, $cnic, $candidateName, $fatherName, 
                          $courseName, $issueDateStr, $grade, $fromDateStr, $toDateStr,
                          $directorSignature, $trainerSignature, $candidateId);

        if ($stmt->execute()) {
            header("Location: view-candidate.php");
            exit();
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>COK - Edit Candidate</title>
  
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/bootstrap-daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="assets/bundles/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css">
  <link rel="stylesheet" href="assets/bundles/select2/dist/css/select2.min.css">
  <link rel="stylesheet" href="assets/bundles/jquery-selectric/selectric.css">
  <link rel="stylesheet" href="assets/bundles/bootstrap-timepicker/css/bootstrap-timepicker.min.css">
  <link rel="stylesheet" href="assets/bundles/bootstrap-tagsinput/dist/bootstrap-tagsinput.css">
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
      
      <?php include('file/navbar.php'); ?>
      
      <!-- sidebar link -->
      <?php include('file/sidebar.php'); ?>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-body">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h4>Edit Candidate Details</h4>
                  </div>
                    <div class="card-body">
                     <?php if (!empty($errors)): ?>
                        <div style="color:red;">
                            <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
                        </div>
                     <?php endif; ?>
                    
                    <form id="candidateForm" class="form-horizontal" method="post" action="" enctype="multipart/form-data" onsubmit="return validateForm()">
                      <div class="form-row">
                        <div class="form-group col-md-4">
                          <label>Serial No</label>
                          <input name="serialNo" type="text" class="form-control" id="serialNo" 
                                 pattern="[A-Za-z0-9]{1,20}" 
                                 title="Alphanumeric characters only (max 20)" required
                                 value="<?php echo htmlspecialchars($candidate['serial_no']); ?>">
                          <small class="form-text text-muted">Alphanumeric (max 20 characters)</small>
                        </div>
                       
                        <div class="form-group col-md-4">
                          <label>Registration No</label>
                          <input name="regNo" type="text" class="form-control" id="regNo" 
                                 pattern="[A-Za-z0-9-]{1,20}" 
                                 title="Alphanumeric and hyphens only" required
                                 value="<?php echo htmlspecialchars($candidate['registration_no']); ?>">
                          <small class="form-text text-muted">Format: ABC-1234</small>
                        </div>
                      
                        <div class="form-group col-md-4">
                          <label>CNIC</label>
                          <input type="text" name="cnic" class="form-control" id="cnic" 
                                 pattern="[0-9]{5}-[0-9]{7}-[0-9]{1}" 
                                 placeholder="12345-1234567-1" required
                                 value="<?php echo htmlspecialchars($candidate['cnic']); ?>">
                          <small class="form-text text-muted">Format: 12345-1234567-1</small>
                        </div>
                      </div>

                      <div class="form-row">
                        <div class="form-group col-md-6">
                          <label>Candidate Name</label>
                          <input type="text" name="candidateName" class="form-control" id="candidateName" 
                                 pattern="[A-Za-z ]{3,50}" 
                                 title="Letters and spaces only (3-50 characters)" required
                                 value="<?php echo htmlspecialchars($candidate['candidate_name']); ?>">
                        </div>

                        <div class="form-group col-md-6">
                          <label>Father Name</label>
                          <input type="text" name="fatherName" class="form-control" id="fatherName" 
                                 pattern="[A-Za-z ]{3,50}" 
                                 title="Letters and spaces only (3-50 characters)" required
                                 value="<?php echo htmlspecialchars($candidate['father_name']); ?>">
                        </div>
                      </div>

                      <div class="form-row">
                        <div class="form-group col-md-4">
                        <label>Course Name</label>
  <select class="form-control" name="courseName" id="courseName" required>
    <option value="">Select Course</option>
    <option value="Web Development" <?php echo ($candidate['course_name'] == 'Web Development') ? 'selected' : ''; ?>>Web Development</option>
    <option value="Graphic Design" <?php echo ($candidate['course_name'] == 'Graphic Design') ? 'selected' : ''; ?>>Graphic Design</option>
    <option value="Digital Marketing" <?php echo ($candidate['course_name'] == 'Digital Marketing') ? 'selected' : ''; ?>>Digital Marketing</option>
    <option value="Mobile App Development" <?php echo ($candidate['course_name'] == 'Mobile App Development') ? 'selected' : ''; ?>>Mobile App Development</option>
  </select>
                        </div>

                        <div class="form-group col-md-4">
                        <label>Issue Date</label>
  <input type="date" name="issueDate" class="form-control" id="issueDate" 
         max="<?php echo date('Y-m-d'); ?>" required
         value="<?php echo htmlspecialchars($candidate['issue_date']); ?>">
                        </div>

                        <div class="form-group col-md-4">
                        <label>Grade</label>
  <select class="form-control" id="grade" name="grade" required>
    <option value="">Select Grade</option>
    <option value="A+" <?php echo ($candidate['grade'] == 'A+') ? 'selected' : ''; ?>>A+</option>
    <option value="A" <?php echo ($candidate['grade'] == 'A') ? 'selected' : ''; ?>>A</option>
    <option value="B+" <?php echo ($candidate['grade'] == 'B+') ? 'selected' : ''; ?>>B+</option>
    <option value="B" <?php echo ($candidate['grade'] == 'B') ? 'selected' : ''; ?>>B</option>
    <option value="C" <?php echo ($candidate['grade'] == 'C') ? 'selected' : ''; ?>>C</option>
  </select>
                        </div>
                      </div>

                      <div class="form-row">
                        <div class="form-group col-md-6">
                        <label>From Date</label>
  <input type="date" class="form-control" name="fromDate" id="fromDate" required
         value="<?php echo htmlspecialchars($candidate['from_date']); ?>">
                        </div>

                        <div class="form-group col-md-6">
                        <label>To Date</label>
  <input type="date" class="form-control" name="toDate" id="toDate" required
         value="<?php echo htmlspecialchars($candidate['to_date']); ?>">
                        </div>
                      </div>

                      <div class="form-row">
                        <div class="form-group col-md-6">
                          <label>Director Signature</label>
                            <?php if (!empty($candidate['director_signature'])): ?>
                                <img src="<?php echo $candidate['director_signature']; ?>" width="100"><br>
                            <?php endif; ?>
                          <input type="file" class="form-control" name="directorSignature" id="directorSignature" accept="image/*">
                          <small class="form-text text-muted">Max 100KB, JPG/PNG/GIF</small>
                        </div>

                        <div class="form-group col-md-6">
                          <label>Trainer Signature</label>
                            <?php if (!empty($candidate['trainer_signature'])): ?>
                                <img src="<?php echo $candidate['trainer_signature']; ?>" width="100"><br>
                            <?php endif; ?>
                          <input type="file" class="form-control" name="trainerSignature" id="trainerSignature" accept="image/*">
                          <small class="form-text text-muted">Max 100KB, JPG/PNG/GIF</small>
                        </div>
                      </div>

                      <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary">Update Candidate</button>
                        <!-- <button type="reset" class="btn btn-secondary ml-2">Reset</button> -->
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
      
      <?php include('file/footer.php'); ?>
    </div>
  </div>

  <!-- General JS Scripts -->
  <script src="assets/js/app.min.js"></script>
  <!-- JS Libraies -->
  <script src="assets/bundles/cleave-js/dist/cleave.min.js"></script>
  <script src="assets/bundles/cleave-js/dist/addons/cleave-phone.us.js"></script>
  <script src="assets/bundles/jquery-pwstrength/jquery.pwstrength.min.js"></script>
  <script src="assets/bundles/bootstrap-daterangepicker/daterangepicker.js"></script>
  <script src="assets/bundles/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
  <script src="assets/bundles/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
  <script src="assets/bundles/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js"></script>
  <script src="assets/bundles/select2/dist/js/select2.full.min.js"></script>
  <script src="assets/bundles/jquery-selectric/jquery.selectric.min.js"></script>
  
  <!-- Custom Validation Script -->
  <script>
    function validateForm() {
      // Validate date range (To date should be after From date)
      const fromDate = new Date(document.getElementById('fromDate').value);
      const toDate = new Date(document.getElementById('toDate').value);
      
      if (toDate <= fromDate) {
        alert('"To" date must be after "From" date');
        return false;
      }
      
      // Validate CNIC format using regex
      const cnic = document.getElementById('cnic').value;
      const cnicRegex = /^[0-9]{5}-[0-9]{7}-[0-9]{1}$/;
      if (!cnicRegex.test(cnic)) {
        alert('Please enter CNIC in correct format: 12345-1234567-1');
        return false;
      }
      
      // Validate file sizes
      const maxSize = 100 * 1024; // 100KB
      
      const directorFile = document.getElementById('directorSignature').files[0];
      if (directorFile && directorFile.size > maxSize) {
        alert('Director signature image must be less than 100KB');
        return false;
      }
      
      const trainerFile = document.getElementById('trainerSignature').files[0];
      if (trainerFile && trainerFile.size > maxSize) {
        alert('Trainer signature image must be less than 100KB');
        return false;
      }
      
      return true;
    }
    
    // Initialize CNIC input mask
    new Cleave('#cnic', {
      delimiters: ['-','-'],
      blocks: [5,7,1],
      numericOnly: true
    });
    
    // Initialize date pickers
    $('#fromDate, #toDate, #issueDate').daterangepicker({
      singleDatePicker: true,
      showDropdowns: true,
      minYear: 2000,
      maxYear: parseInt(moment().format('YYYY'), 10),
      locale: {
        format: 'YYYY-MM-DD'
      }
    });
  </script>

  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <!-- Custom JS File -->
  <script src="assets/js/custom.js"></script>
</body>
</html>
