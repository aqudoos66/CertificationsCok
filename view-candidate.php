<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>COK - Admin Dashboard</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <link rel="shortcut icon" type="image/x-icon" href="assets/img/cok/logo.webp" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4>Candidate Records</h4>
                  <div class="card-header-form">
                    <form method="GET" action="">
                      <div class="input-group">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search candidates...">
                        <div class="input-group-btn">
                          <button type="button" class="btn btn-primary" title="Search"><i class="fas fa-search"></i></button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Serial No</th>
                          <th>Candidate Name</th>
                          <th>CNIC</th>
                          <th>Course</th>
                          <th>Grade</th>
                          <th>Duration</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        include('file/config.php');

                        // Search feature
                        $search = "";
                        if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
                            $search = trim($_GET['search']);
                        }

                        // Fetch candidate data
                        $sql = "SELECT `id`,`serial_no`, `candidate_name`, `cnic`, `course_name`, `grade`, `from_date`, `to_date` FROM `candidates`";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr id='row-".$row['id']."'>";
                                echo "<td>" . htmlspecialchars($row['serial_no']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['candidate_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['cnic']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['grade']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['from_date']) . " to " . htmlspecialchars($row['to_date']) . "</td>";
                                echo "<td>
                                        <div class='btn-group'>
                                          <a href='view-certificate.php?id=" . $row['id'] . "' class='btn btn-outline-primary' title='View Certificate'>
                                              <i class='fas fa-eye'></i>
                                          </a>
                                          <a href='edit-candidate.php?id=" . $row['id'] . "' class='btn btn-outline-warning' title='Edit'>
                                              <i class='fas fa-edit'></i>
                                          </a>
                                          <button class='btn btn-outline-danger delete-btn' data-id='" . $row['id'] . "' title='Delete'>
                                              <i class='fas fa-trash-alt'></i>
                                          </button>
                                        </div>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No candidates found.</td></tr>";
                        }
                        $conn->close();
                        ?>
                      </tbody>
                    </table>
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

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
  $(document).ready(function() {
      // Search Functionality
      $("#searchInput").on("keyup", function() {
          var search = $(this).val();
          $.ajax({
              url: "search_candidates.php",
              type: "GET",
              data: { search: search },
              success: function(data) {
                  $("tbody").html(data);
              }
          });
      });

      // Delete Functionality with SweetAlert
      $(document).on('click', '.delete-btn', function() {
          var id = $(this).data('id');
          var row = $(this).closest('tr');
          
          Swal.fire({
              title: 'Are you sure?',
              text: "You won't be able to revert this!",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, delete it!'
          }).then((result) => {
              if (result.isConfirmed) {
                  $.ajax({
                      url: "delete-candidate.php",
                      type: "POST",
                      data: { id: id },
                      dataType: 'json',
                      success: function(response) {
                          if (response.status === 'success') {
                              Swal.fire(
                                  'Deleted!',
                                  'Candidate has been deleted.',
                                  'success'
                              );
                              row.fadeOut(300, function() {
                                  $(this).remove();
                              });
                          } else {
                              Swal.fire(
                                  'Error!',
                                  response.message || 'Failed to delete candidate.',
                                  'error'
                              );
                          }
                      },
                      error: function() {
                          Swal.fire(
                              'Error!',
                              'An error occurred while processing the request.',
                              'error'
                          );
                      }
                  });
              }
          });
      });
  });
  </script>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>
</body>
</html>