<?php
session_start();

// Check if user is not logged in (assuming you set a session variable like 'user_id' on login)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to your login page
    exit(); // Make sure to exit after redirect
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Admin Dashboard - Profile</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <link rel="shortcut icon" href="assets/img/favicon.ico" type="image/x-icon">
</head>

<body>
<div class="loader"></div>
<div id="app">
  <div class="main-wrapper main-wrapper-1">
    <div class="navbar-bg"></div>

    <?php include(__DIR__ . '/file/navbar.php'); ?>
    <?php include(__DIR__ . '/file/sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
      <section class="section">
        <div class="section-header">
          <h1>Profile</h1>
          <!-- <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="dashboard.php">Dashboard</a></div>
            <div class="breadcrumb-item">Profile</div>
          </div> -->
        </div>

        <div class="section-body">
          <div class="row mt-sm-4">
            <!-- Profile Section -->
            <div class="col-12 col-md-12 col-lg-4">
              <div class="card author-box">
                <div class="card-body">
                  <div class="author-box-center">
                    <img alt="profile" src="<?php echo htmlspecialchars($profile_image); ?>" class="rounded-circle author-box-picture" id="profilePreview">
                    <div class="clearfix"></div>
                    <div class="author-box-name mt-2">
                      <a href="#"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></a>
                    </div>
                    <div class="author-box-job"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></div>
                  </div>
                </div>
              </div>

              <!-- <div class="card">
                <div class="card-header">
                  <h4>Personal Details</h4>
                </div>
                <div class="card-body">
                  <div class="py-4">
                    <p class="clearfix">
                      <span class="float-left">Role</span>
                      <span class="float-right text-muted"><?php echo htmlspecialchars($user['role']); ?></span>
                    </p>
                   
                  </div>
                </div>
              </div> -->
            </div>

            <!-- Edit Profile Section -->
            <div class="col-12 col-md-12 col-lg-8">
              <div class="card">
                <div class="card-header">
                  <h4>Edit Profile</h4>
                </div>
                <div class="card-body">
                  <?php 
                  if (isset($_SESSION['success'])) {
                      echo '<div class="alert alert-success">'.htmlspecialchars($_SESSION['success']).'</div>';
                      unset($_SESSION['success']);
                  }
                  if (isset($_SESSION['error'])) {
                      echo '<div class="alert alert-danger">'.htmlspecialchars($_SESSION['error']).'</div>';
                      unset($_SESSION['error']);
                  }
                  ?>

                  <form method="post" enctype="multipart/form-data" action="file/update-profile.php" class="needs-validation" novalidate="">
                    <input type="hidden" name="csrf_token" value="">
                    
                    <div class="row">
                      <div class="form-group col-md-6 col-12">
                        <label>First Name</label>
                        <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required maxlength="50">
                        <div class="invalid-feedback">Please provide a valid first name (max 50 characters)</div>
                      </div>
                      <div class="form-group col-md-6 col-12">
                        <label>Last Name</label>
                        <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required maxlength="50">
                        <div class="invalid-feedback">Please provide a valid last name (max 50 characters)</div>
                      </div>
                    </div>
                    
                    <div class="row">
                      <div class="form-group col-md-7 col-12">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required maxlength="100">
                        <div class="invalid-feedback">Please provide a valid email</div>
                      </div>
                      <div class="form-group col-md-5 col-12">
                        <label>Phone</label>
                        <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" maxlength="20">
                        <div class="invalid-feedback">Please provide a valid phone number</div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="form-group col-md-6 col-12">
                        <label>New Password</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Leave blank if not changing" minlength="8">
                        <small class="form-text text-muted">Minimum 8 characters</small>
                      </div>
                      <div class="form-group col-md-6 col-12">
                        <label>Confirm Password</label>
                        <input type="password" class="form-control" name="confirm_password" placeholder="Confirm new password">
                        <div class="invalid-feedback">Passwords must match</div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="form-group col-12">
                        <label>Profile Picture</label>
                        <div class="custom-file">
                          <input type="file" name="profile_image" accept="image/jpeg,image/png" class="custom-file-input" id="profileImage" onchange="previewImage(event)">
                          <label class="custom-file-label" for="profileImage">Choose file (JPEG/PNG, max 2MB)</label>
                        </div>
                        <small class="form-text text-muted">Current: <?php echo basename($profile_image); ?></small>
                      </div>
                    </div>

                    <div class="card-footer text-right">
                      <button type="submit" class="btn btn-primary">Save Changes</button>
                      <!-- <button type="reset" class="btn btn-secondary">Reset</button> -->
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <?php include(__DIR__ . '/file/footer.php'); ?>
  </div>
</div>

<script src="assets/js/app.min.js"></script>
<script src="assets/js/scripts.js"></script>

<script>
function previewImage(event) {
  const reader = new FileReader();
  reader.onload = function() {
    const output = document.getElementById('profilePreview');
    output.src = reader.result;
  };
  reader.readAsDataURL(event.target.files[0]);
  
  // Update file label
  const fileName = event.target.files[0].name;
  event.target.nextElementSibling.innerText = fileName;
}

// Password confirmation validation
document.querySelector('form').addEventListener('submit', function(e) {
  const password = document.getElementById('password');
  const confirm = document.querySelector('input[name="confirm_password"]');
  
  if (password.value && password.value !== confirm.value) {
    e.preventDefault();
    confirm.setCustomValidity("Passwords must match");
    confirm.reportValidity();
  } else {
    confirm.setCustomValidity('');
  }
});
</script>
</body>
</html>