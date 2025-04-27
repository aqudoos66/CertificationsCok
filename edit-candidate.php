<?php
session_start();
require_once('file/config.php');

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if candidate ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid candidate ID";
    header("Location: view-candidate.php");
    exit();
}

$id = intval($_GET['id']);

// Fetch candidate data
$stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$candidateData = $result->fetch_assoc();
$stmt->close();

if (!$candidateData) {
    $_SESSION['error'] = "Candidate not found";
    header("Location: view-candidate.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    
    // Validate inputs
    $fields = [
        'serialNo' => ['pattern' => '/^[A-Za-z0-9]{1,20}$/', 'error' => 'Serial No must be alphanumeric (max 20 characters)'],
        'regNo' => ['pattern' => '/^[A-Za-z0-9-]{1,20}$/', 'error' => 'Registration No must be alphanumeric with hyphens only'],
        'cnic' => ['pattern' => '/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/', 'error' => 'CNIC must be in format 12345-1234567-1'],
        'candidateName' => ['pattern' => '/^[A-Za-z ]{3,50}$/', 'error' => 'Candidate Name must be 3-50 letters and spaces only'],
        'fatherName' => ['pattern' => '/^[A-Za-z ]{3,50}$/', 'error' => 'Father Name must be 3-50 letters and spaces only']
    ];
    
    foreach ($fields as $field => $validation) {
        if (!preg_match($validation['pattern'], $_POST[$field])) {
            $errors[] = $validation['error'];
        }
    }
    
    // Validate dates
    $fromDate = new DateTime($_POST['fromDate']);
    $toDate = new DateTime($_POST['toDate']);
    $issueDate = new DateTime($_POST['issueDate']);
    
    if ($toDate <= $fromDate) {
        $errors[] = "'To' date must be after 'From' date";
    }
    
    if ($issueDate > new DateTime()) {
        $errors[] = "Issue date cannot be in the future";
    }
    
    // Process if no errors
    if (empty($errors)) {
        // Prepare data
        $data = [
            'id' => $id,
            'serial_no' => $conn->real_escape_string($_POST['serialNo']),
            'registration_no' => $conn->real_escape_string($_POST['regNo']),
            'cnic' => $conn->real_escape_string($_POST['cnic']),
            'candidate_name' => $conn->real_escape_string($_POST['candidateName']),
            'father_name' => $conn->real_escape_string($_POST['fatherName']),
            'course_name' => $conn->real_escape_string($_POST['courseName']),
            'issue_date' => $conn->real_escape_string($_POST['issueDate']),
            'grade' => $conn->real_escape_string($_POST['grade']),
            'from_date' => $conn->real_escape_string($_POST['fromDate']),
            'to_date' => $conn->real_escape_string($_POST['toDate'])
        ];
        
        // Handle file uploads
        $uploadDir = 'uploads/signatures/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxSize = 100 * 1024; // 100KB
        
        foreach (['directorSignature', 'trainerSignature'] as $signatureType) {
            if (isset($_FILES[$signatureType]) && $_FILES[$signatureType]['error'] == 0) {
                $file = $_FILES[$signatureType];
                
                if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
                    // Delete old file if exists
                    if (!empty($candidateData[$signatureType]) && file_exists($candidateData[$signatureType])) {
                        unlink($candidateData[$signatureType]);
                    }
                    
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = $signatureType . '_' . $id . '_' . time() . '.' . $ext;
                    $targetPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $data[$signatureType] = $targetPath;
                    }
                }
            } else {
                $data[$signatureType] = $candidateData[$signatureType];
            }
        }
        
        // Update record
        $stmt = $conn->prepare("UPDATE candidates SET 
                              serial_no = ?, 
                              registration_no = ?, 
                              cnic = ?, 
                              candidate_name = ?, 
                              father_name = ?, 
                              course_name = ?, 
                              issue_date = ?, 
                              grade = ?, 
                              from_date = ?, 
                              to_date = ?, 
                              director_signature = ?, 
                              trainer_signature = ?
                              WHERE id = ?");
        
        $stmt->bind_param("sssssssssssi", 
            $data['serial_no'], $data['registration_no'], $data['cnic'], 
            $data['candidate_name'], $data['father_name'], $data['course_name'], 
            $data['issue_date'], $data['grade'], $data['from_date'], 
            $data['to_date'], $data['directorSignature'], $data['trainerSignature'], 
            $data['id']);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Candidate updated successfully";
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
  <title>Edit Candidate - COK Admin</title>
  
  <!-- CSS Files -->
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/bootstrap-daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="assets/bundles/select2/dist/css/select2.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <link rel="shortcut icon" type="image/x-icon" href="assets/img/cok/logo.webp" />
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      
      <?php include('file/navbar.php'); ?>
      <?php include('file/sidebar.php'); ?>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>Edit Candidate</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard.php">Dashboard</a></div>
              <div class="breadcrumb-item"><a href="view-candidate.php">Candidates</a></div>
              <div class="breadcrumb-item">Edit Candidate</div>
            </div>
          </div>

          <div class="section-body">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h4>Edit Candidate Details</h4>
                  </div>
                  <div class="card-body">
                    <?php if (!empty($errors)): ?>
                      <div class="alert alert-danger">
                        <ul>
                          <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                          <?php endforeach; ?>
                        </ul>
                      </div>
                    <?php elseif (isset($_SESSION['success'])): ?>
                      <div class="alert alert-success">
                        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                      </div>
                    <?php endif; ?>
                    
                    <form id="candidateForm" method="post" action="" enctype="multipart/form-data">
                      <input type="hidden" name="id" value="<?php echo $candidateData['id']; ?>">
                      
                      <div class="form-row">
                        <div class="form-group col-md-4">
                          <label>Serial No *</label>
                          <input name="serialNo" type="text" class="form-control" required
                                 pattern="[A-Za-z0-9]{1,20}" 
                                 title="Alphanumeric characters only (max 20)"
                                 value="<?php echo htmlspecialchars($candidateData['serial_no']); ?>">
                        </div>
                       
                        <div class="form-group col-md-4">
                          <label>Registration No *</label>
                          <input name="regNo" type="text" class="form-control" required
                                 pattern="[A-Za-z0-9-]{1,20}" 
                                 title="Alphanumeric and hyphens only"
                                 value="<?php echo htmlspecialchars($candidateData['registration_no']); ?>">
                        </div>
                      
                        <div class="form-group col-md-4">
                          <label>CNIC *</label>
                          <input type="text" name="cnic" class="form-control" required
                                 pattern="[0-9]{5}-[0-9]{7}-[0-9]{1}" 
                                 placeholder="12345-1234567-1"
                                 value="<?php echo htmlspecialchars($candidateData['cnic']); ?>">
                        </div>
                      </div>

                      <div class="form-row">
                        <div class="form-group col-md-6">
                          <label>Candidate Name *</label>
                          <input type="text" name="candidateName" class="form-control" required
                                 pattern="[A-Za-z ]{3,50}" 
                                 title="Letters and spaces only (3-50 characters)"
                                 value="<?php echo htmlspecialchars($candidateData['candidate_name']); ?>">
                        </div>

                        <div class="form-group col-md-6">
                          <label>Father Name *</label>
                          <input type="text" name="fatherName" class="form-control" required
                                 pattern="[A-Za-z ]{3,50}" 
                                 title="Letters and spaces only (3-50 characters)"
                                 value="<?php echo htmlspecialchars($candidateData['father_name']); ?>">
                        </div>
                      </div>

                      <div class="form-row">
                        <div class="form-group col-md-4">
                          <label>Course Name *</label>
                          <select class="form-control select2" name="courseName" required>
                            <option value="">Select Course</option>
                            <?php
                            $courses = ['Web Development', 'Graphic Design', 'Digital Marketing', 'Mobile App Development'];
                            foreach ($courses as $course) {
                                $selected = ($course == $candidateData['course_name']) ? 'selected' : '';
                                echo "<option value='$course' $selected>$course</option>";
                            }
                            ?>
                          </select>
                        </div>

                        <div class="form-group col-md-4">
                          <label>Issue Date *</label>
                          <input type="date" name="issueDate" class="form-control" required
                                 max="<?php echo date('Y-m-d'); ?>"
                                 value="<?php echo htmlspecialchars($candidateData['issue_date']); ?>">
                        </div>

                        <div class="form-group col-md-4">
                          <label>Grade *</label>
                          <select class="form-control select2" name="grade" required>
                            <option value="">Select Grade</option>
                            <?php
                            $grades = ['A+', 'A', 'B+', 'B', 'C'];
                            foreach ($grades as $grade) {
                                $selected = ($grade == $candidateData['grade']) ? 'selected' : '';
                                echo "<option value='$grade' $selected>$grade</option>";
                            }
                            ?>
                          </select>
                        </div>
                      </div>

                      <div class="form-row">
                        <div class="form-group col-md-6">
                          <label>From Date *</label>
                          <input type="date" class="form-control" name="fromDate" required
                                 value="<?php echo htmlspecialchars($candidateData['from_date']); ?>">
                        </div>

                        <div class="form-group col-md-6">
                          <label>To Date *</label>
                          <input type="date" class="form-control" name="toDate" required
                                 value="<?php echo htmlspecialchars($candidateData['to_date']); ?>">
                        </div>
                      </div>

                      <div class="form-row">
                        <div class="form-group col-md-6">
                          <label>Director Signature</label>
                          <input type="file" class="form-control-file" name="directorSignature" accept="image/jpeg, image/png">
                          <small class="text-muted">Max 100KB, JPG/PNG only</small>
                          <?php if (!empty($candidateData['director_signature'])): ?>
                            <div class="mt-2">
                              <img src="<?php echo htmlspecialchars($candidateData['director_signature']); ?>" 
                                   alt="Director Signature" class="img-thumbnail" style="max-height: 100px;">
                              <p class="text-muted">Current signature</p>
                            </div>
                          <?php endif; ?>
                        </div>

                        <div class="form-group col-md-6">
                          <label>Trainer Signature</label>
                          <input type="file" class="form-control-file" name="trainerSignature" accept="image/jpeg, image/png">
                          <small class="text-muted">Max 100KB, JPG/PNG only</small>
                          <?php if (!empty($candidateData['trainer_signature'])): ?>
                            <div class="mt-2">
                              <img src="<?php echo htmlspecialchars($candidateData['trainer_signature']); ?>" 
                                   alt="Trainer Signature" class="img-thumbnail" style="max-height: 100px;">
                              <p class="text-muted">Current signature</p>
                            </div>
                          <?php endif; ?>
                        </div>
                      </div>

                      <div class="form-group text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                          <i class="fas fa-save"></i> Update Candidate
                        </button>
                        <a href="view-candidate.php" class="btn btn-secondary btn-lg ml-2">
                          <i class="fas fa-times"></i> Cancel
                        </a>
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

  <!-- JavaScript Libraries -->
  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/cleave-js/dist/cleave.min.js"></script>
  <script src="assets/bundles/select2/dist/js/select2.full.min.js"></script>
  
  <!-- Page Specific JS -->
  <script>
  $(document).ready(function() {
      // Initialize select2
      $('.select2').select2();
      
      // CNIC input mask
      new Cleave('input[name="cnic"]', {
          delimiters: ['-','-'],
          blocks: [5,7,1],
          numericOnly: true
      });
      
      // Form validation
      $('#candidateForm').on('submit', function(e) {
          // Validate date range
          const fromDate = new Date($('input[name="fromDate"]').val());
          const toDate = new Date($('input[name="toDate"]').val());
          
          if (toDate <= fromDate) {
              alert('"To" date must be after "From" date');
              e.preventDefault();
              return false;
          }
          
          // Validate file types and sizes
          const maxSize = 100 * 1024; // 100KB
          const allowedTypes = ['image/jpeg', 'image/png'];
          
          $('input[type="file"]').each(function() {
              if (this.files.length > 0) {
                  const file = this.files[0];
                  
                  if (!allowedTypes.includes(file.type)) {
                      alert('Only JPG and PNG images are allowed');
                      e.preventDefault();
                      return false;
                  }
                  
                  if (file.size > maxSize) {
                      alert('File size must be less than 100KB');
                      e.preventDefault();
                      return false;
                  }
              }
          });
          
          return true;
      });
  });
  </script>
</body>
</html>