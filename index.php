<?php
include('file/config.php'); // Include your database connection

// Initialize variables
$candidate = null;
$certificateMessage = '';
$certificateExists = false;

// Handle Search form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'search') {
    $cnic = $_POST['cnic'];
    header("Location: index.php?cnic=" . urlencode($cnic));
    exit();
}

// Handle Print Certificate form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'print') {
    $cnic = $_POST['cnic'];
    header("Location: E-certificate/index.php?cnic=" . urlencode($cnic));
    exit();
}

// Fetch candidate if CNIC passed via GET
if (isset($_GET['cnic'])) {
    $cnic = $_GET['cnic'];

    $stmt = $conn->prepare("SELECT * FROM candidates WHERE cnic = ?");
    $stmt->bind_param("s", $cnic);
    $stmt->execute();
    $result = $stmt->get_result();
    $candidate = $result->fetch_assoc();
    $stmt->close();

    if ($candidate) {
        $certificateMessage = "Congratulations! " . htmlspecialchars($candidate['candidate_name']);
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Certificate Verification System</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: 'Poppins', Arial, sans-serif;
      background: linear-gradient(135deg, #e0f7fa, #ffffff);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }
    .container {
      background: #ffffff;
      padding: 40px 30px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      text-align: center;
      max-width: 450px;
      width: 100%;
    }
    .logo img {
      width: 100%;
      max-width: 300px;
      margin-bottom: 25px;
    }
    h1 {
      font-size: 24px;
      color: #333;
      margin-bottom: 15px;
      font-weight: 600;
    }
    p {
      font-size: 15px;
      color: #666;
      margin-bottom: 25px;
    }
    .input-group {
      display: flex;
      flex-direction: column;
      gap: 15px;
      margin-top: 10px;
    }
    input[type="text"] {
      padding: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 16px;
      transition: 0.3s;
    }
    input[type="text"]:focus {
      border-color: #00acc1;
      outline: none;
      box-shadow: 0 0 5px rgba(0,172,193,0.5);
    }
    button {
      padding: 15px;
      background: linear-gradient(135deg, #00acc1, #00796b);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.4s ease, transform 0.2s ease;
    }
    button:hover {
      background: linear-gradient(135deg, #00796b, #004d40);
      transform: translateY(-2px);
    }
    @media (max-width: 600px) {
      .container {
        padding: 30px 20px;
      }
      h1 {
        font-size: 20px;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <div class="logo">
    <img src="assets/img/ver2.png" alt="Certificate Verification System Logo">
  </div>

  <p>Verify your credentials easily by entering your CNIC below.</p>
  
  <!-- Search Certificate Form -->
  <form action="index.php" method="POST">
    <div class="input-group">
      <input type="text" name="cnic" id="cnic" pattern="[0-9]{5}-[0-9]{7}-[0-9]{1}" placeholder="12345-1234567-1" required>
      <input type="hidden" name="action" value="search">
      <button type="submit">Search Certificate</button>
    </div>
  </form>

  <?php if (isset($_GET['cnic'])): ?>
    <h2>Certificate Information</h2>
    <p><?php echo $certificateMessage; ?></p>

    <?php if ($certificateExists): ?>
      <div id="certificate">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($candidate['candidate_name']); ?></p>
        <p><strong>CNIC:</strong> <?php echo htmlspecialchars($candidate['cnic']); ?></p>
      </div>

      <!-- Print Certificate Form -->
      <form action="index.php" method="POST">
        <input type="hidden" name="cnic" value="<?php echo htmlspecialchars($candidate['cnic']); ?>">
        <input type="hidden" name="action" value="print">
        <button type="submit">Print Certificate</button>
      </form>
    <?php endif; ?>

  <?php endif; ?>
</div>

</body>
</html>
