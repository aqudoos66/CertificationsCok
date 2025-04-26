<?php
include('file/config.php');

$search = "";
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
}

$sql = "SELECT `serial_no`, `registration_no`, `cnic`, `candidate_name`, `father_name`, `course_name`, `grade` FROM `candidates`";

if ($search !== "") {
    $search = $conn->real_escape_string($search);
    $sql .= " WHERE candidate_name LIKE '%$search%' 
              OR father_name LIKE '%$search%'
              OR cnic LIKE '%$search%' 
              OR registration_no LIKE '%$search%'
              OR course_name LIKE '%$search%'
              OR grade LIKE '%$search%'";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['serial_no']) . "</td>";
        echo "<td>" . htmlspecialchars($row['candidate_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['cnic']) . "</td>";
        echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['grade']) . "</td>";
        echo "<td>" . htmlspecialchars($row['from_date']) . " to " . htmlspecialchars($row['to_date']) . "</td>";
        echo "<td>
              <a href='view_candidate.php?id=" . urlencode($row['serial_no']) . "' class='btn btn-outline-primary' title='View Candidate'>
                  <i class='fas fa-eye'></i> 
              </a>
              <a href='edit_candidate.php?id=" . urlencode($row['serial_no']) . "' class='btn btn-outline-warning' title='Edit Candidate'>
                  <i class='fas fa-edit'></i> 
              </a>
              <a href='delete_candidate.php?id=" . urlencode($row['serial_no']) . "' class='btn btn-outline-danger' title='Delete Candidate'>
                  <i class='fas fa-trash-alt'></i> 
              </a>
            </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='8'>No candidates found.</td></tr>";
}

$conn->close();
?>
