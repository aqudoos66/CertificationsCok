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
    <div class="form-inline mr-auto">
        <!-- <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg collapse-btn">
                <i data-feather="align-justify"></i></a>
            </li>
        </ul> -->
    </div>

</nav>


      <!-- Sidebar Link -->
      <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">
          <div class="sidebar-brand">
            <a href="index.php"> <img alt="image" src="assets/img/cok/logo.webp" class="header-logo" /> <span
                class="logo-name">COK</span>
            </a>
          </div>
        </aside>
      </div>



      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
        <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h4>Search your certificate</h4>
                  </div>
                  <div class="card-body">
                    <form id="candidateForm" class="form-horizontal" onsubmit="return validateForm()">
                      
                    <div class="form-row">
    <div class="form-group col-md-12"> <!-- changed col-md-4 to col-md-12 -->
      <label>CNIC</label>
      <input type="text" class="form-control" id="cnic" pattern="[0-9]{5}-[0-9]{7}-[0-9]{1}" 
             placeholder="12345-1234567-1" required>
      <small class="form-text text-muted">Format: 12345-1234567-1</small>
    </div>
</div>


                      <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary">Download</button>
                        <!-- <button type="reset" class="btn btn-secondary ml-2">Reset</button> -->
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
        </section>
      </div>

        <?php
          include('file/footer.php');
        ?>
    </div>
  </div>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/apexcharts/apexcharts.min.js"></script>
  <script src="assets/js/page/index.js"></script>
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>
</body>

</html>