<?php
session_start();
include('file/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$errors = [];
$course = [];

// Get course data if ID is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM course WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    $stmt->close();
    
    if (!$course) {
        $errors[] = "Course not found";
        header("Location: course.php");
        exit();
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Server-side validation
    if (empty($_POST['course'])) {
        $errors[] = "Course name is required";
    }

    $id = intval($_POST['id']);
    $course_name = trim($_POST['course']);

    // Process if no errors
    if (empty($errors)) {
        // Sanitize inputs
        $course_name = $conn->real_escape_string($course_name);

        // SQL Update with prepared statement
        $stmt = $conn->prepare("UPDATE course SET course_name = ? WHERE id = ?");
        $stmt->bind_param("si", $course_name, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: course.php");
            exit();
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>COK - Admin Dashboard</title>
  
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
                    <h4><?php echo isset($course['id']) ? 'Update' : 'Add'; ?> Course</h4>
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
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                      <?php if (isset($course['id'])): ?>
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($course['id']); ?>">
                      <?php endif; ?>
                      
                      <div class="form-row">
                        <div class="form-group col-md-12">
                          <label for="course">Course Name</label>
                          <input type="text" class="form-control" id="course" name="course" 
                                value="<?php echo isset($course['course_name']) ? htmlspecialchars($course['course_name']) : ''; ?>"
                                placeholder="eg., Web Development" required>
                          <small class="form-text text-muted">Format: Web Development</small>
                        </div>
                      </div>
                      
                      <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary"><?php echo isset($course['id']) ? 'Update' : 'Add'; ?> Course</button>
                        
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
  
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <!-- Custom JS File -->
  <script src="assets/js/custom.js"></script>
</body>
</html>