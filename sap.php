<?php
$registered = 0;

// Function to calculate GPA
function calculateGPA($grades, $credits) {
    $gradePoints = [
        'O' => 6, 'A+' => 5, 'A' => 4.0, 'A-' => 3.7, 'B+' => 3.3, 'B' => 3.0,
        'B-' => 2.7, 'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7, 'D+' => 1.3, 'D' => 1.0, 'D-' => 0.7, 'F' => 0.0
    ];
    $totalPoints = 0;
    $totalCredits = 0;

    for ($i = 0; $i < count($grades); $i++) {
        if (isset($gradePoints[$grades[$i]])) {
            $totalPoints += $gradePoints[$grades[$i]] * $credits[$i];
            $totalCredits += $credits[$i];
        }
    }

    return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.0;
}

// Function to calculate SAP points
function calculateSAPPoints($gpa) {
    $sapPointsPerGPA = [
        0 => 0, 1 => 0, 1.01 => 1, 1.5 => 1, 1.99 => 1, 2 => 2, 2.5 => 2,
        2.99 => 2, 3 => 3, 3.5 => 3, 3.99 => 3, 4 => 4, 4.5 => 4, 4.99 => 4,
        5 => 5, 5.5 => 5, 6 => 6
    ];

    foreach ($sapPointsPerGPA as $key => $value) {
        if ($gpa >= $key) {
            return $value;
        }
    }

    return 0;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'server.php'; 

    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Collect form data
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $grade = mysqli_real_escape_string($con, $_POST['grade']);
    $credit = mysqli_real_escape_string($con, $_POST['credit']);
    $grade1 = mysqli_real_escape_string($con, $_POST['grade1']);
    $credit1 = mysqli_real_escape_string($con, $_POST['credit1']);
    $grade2 = mysqli_real_escape_string($con, $_POST['grade2']);
    $credit2 = mysqli_real_escape_string($con, $_POST['credit2']);
    $grade3 = mysqli_real_escape_string($con, $_POST['grade3']);
    $credit3 = mysqli_real_escape_string($con, $_POST['credit3']);

    // Check if the user already exists
    $sql_check = "SELECT * FROM calculation WHERE name='$name'";
    $result_check = mysqli_query($con, $sql_check);
    $num = mysqli_num_rows($result_check);

    if ($num >= 0) {
        // Calculate GPA and SAP points
        $grades = [$grade,$grade1, $grade2, $grade3];
        $credits = [$credit,$credit1, $credit2, $credit3];

        $gpa = calculateGPA($grades, $credits);
        $sap_points = calculateSAPPoints($gpa);

        // Insert data into the calculation table
        $sql_insert = "INSERT INTO calculation (name,grade,credit, grade1, credit1, grade2, credit2, grade3, credit3, gpa, SAP) 
                       VALUES ('$name', '$grade', '$credit','$grade1', '$credit1', '$grade2', '$credit2', '$grade3', '$credit3', '$gpa', '$sap_points')";
        $result_insert = mysqli_query($con, $sql_insert);

        if ($result_insert) {
            $registered = 1;
        } else {
            die(mysqli_error($con));
        }
    }

    mysqli_close($con); // Close connection
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student SAP Points Calculator</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Student SAP Points Calculator</h1><br><br>
        <form action="Signin.php" method="POST" id="sapCalculatorForm" onsubmit="return formvalidation()" class="row g-3 form-text">
        <div class="mb-3 ">
            <div class="col">
                <label>NAME:</label><br>
                
                <input type="text" name="name" class="form-control" placeholder="Student name" style="width:400px;">
            </div></div>
            <div class="form-row mb-3">
                <div class="col mt-2">
                    <input type="text" class="form-control" name="grade" placeholder="Grade (e.g., A, B+)" required style="width:300px;">
                </div>
                <div class="col">
                    <input type="number" class="form-control" name="credit" placeholder="Credits" required style="width:300px;">
                </div>
            </div>
            <div class="form-row mb-3">
                <div class="col mt-2">
                    <input type="text" class="form-control" name="grade1" placeholder="Grade (e.g., A, B+)" required style="width:300px;">
                </div>
                <div class="col">
                    <input type="number" class="form-control" name="credit1" placeholder="Credits" required style="width:300px;">
                </div>
            </div>
            <div class="form-row mb-3">
                <div class="col">
                    <input type="text" class="form-control" name="grade2" placeholder="Grade (e.g., A, B+)" required style="width:300px;">
                </div>
                <div class="col">
                    <input type="number" class="form-control" name="credit2" placeholder="Credits" required style="width:300px;">
                </div>
            </div>
            <div class="form-row mb-3">
                <div class="col">
                    <input type="text" class="form-control" name="grade3" placeholder="Grade (e.g., A, B+)" required style="width:300px;">
                </div>
                <div class="col">
                    <input type="number" class="form-control" name="credit3" placeholder="Credits" required style="width:300px;">
                </div>
            </div>
            <button type="submit" class="btn btn-success">Calculate SAP Points</button>
        </form>
        <div class="mt-4">
            <h2>Results</h2>
            <p id="resultGPA">GPA: </p>
            <p id="resultSAP">SAP Points: </p>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('sapCalculatorForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const form = this;
            const data = new FormData(form);

            // Fetch data from form
            const name = data.get('name');
            const grade = data.get('grade');
            const credit = parseFloat(data.get('credit'));
            const grade1 = data.get('grade1');
            const credit1 = parseFloat(data.get('credit1'));
            const grade2 = data.get('grade2');
            const credit2 = parseFloat(data.get('credit2'));
            const grade3 = data.get('grade3');
            const credit3 = parseFloat(data.get('credit3'));

            // Calculate GPA
            const grades = [grade,grade1, grade2, grade3];
            const credits = [credit,credit1, credit2, credit3];

            const calculateGPA = (grades, credits) => {
                const gradePoints = {
                    'O': 6, 'A+': 5, 'A': 4.0, 'A-': 3.7, 'B+': 3.3, 'B': 3.0,
                    'B-': 2.7, 'C+': 2.3, 'C': 2.0, 'C-': 1.7, 'D+': 1.3, 'D': 1.0, 'D-': 0.7, 'F': 0.0
                };
                let totalPoints = 0;
                let totalCredits = 0;

                grades.forEach((grade, index) => {
                    if (gradePoints[grade]) {
                        totalPoints += gradePoints[grade] * credits[index];
                        totalCredits += credits[index];
                    }
                });

                const gpa = totalCredits ? (totalPoints / totalCredits).toFixed(2) : 0;
                return gpa;
            };

            const calculateSAPPoints = (gpa) => {
                const sapPointsPerGPA = {
                    0: 0, 1: 0, 1.01: 1, 1.5: 1, 1.99: 1, 2: 2, 2.5: 2,
                    2.99: 2, 3: 3, 3.5: 3, 3.99: 3, 4: 4, 4.5: 4, 4.99: 4,
                    5: 5, 5.5: 5, 6: 6
                };

                for (let key in sapPointsPerGPA) {
                    if (gpa >= parseFloat(key)) {
                        return sapPointsPerGPA[key];
                    }
                }

                return 0;
            };

            // Perform calculations
            const gpa = calculateGPA(grades, credits);
            const sapPoints = calculateSAPPoints(gpa);

            // Update UI with results
            document.getElementById('resultGPA').textContent = `GPA: ${gpa}`;
            document.getElementById('resultSAP').textContent = `SAP Points: ${gpa}`;

            // Submit the form data to server.php
            fetch('server.php', {
                method: 'POST',
                body: data
            })
            .then(response => response.text())
            .then(data => {
                console.log(data); // Optional: Log server response
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
