<?php
// Database connection
include('file/config.php'); // Adjust path if needed

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cnic = $_POST['cnic'];

    if (!empty($cnic)) {
        $query = "SELECT id FROM candidates WHERE cnic = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $cnic);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id);
            $stmt->fetch();
            
            // Redirect to view certificate page
            header("Location: view-certificate.php?id=" . $id);
            exit();
        } else {
            $error = "No certificate found with this CNIC";
        }
    } else {
        $error = "Please enter CNIC number";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Certificate</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .search-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        h1 {
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="search-container">
        <h1>Search Certificate</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="cnic">Enter CNIC Number (without dashes):</label>
                <input type="text" id="cnic" name="cnic" placeholder="e.g. 4220112345678" required>
            </div>
            <button type="submit">Search Certificate</button>
        </form>
    </div>
</body>
</html>