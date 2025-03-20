<?php
include_once '../CAPSTONE/templates/dash.php'; // Include your dashboard template
require_once __DIR__ . '/../includes/db_connect.php'; // Include the database connection

// Check if the connection is established
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Total number of graduates who submitted forms
$queryTotalForms = "SELECT COUNT(*) AS total FROM graduates";
$resultTotalForms = mysqli_query($conn, $queryTotalForms);
$totalForms = ($resultTotalForms) ? mysqli_fetch_assoc($resultTotalForms)['total'] : 0;

// Total number of employed graduates
$queryEmployed = "SELECT COUNT(*) AS total FROM employment_survey WHERE employed = 'yes'";
$resultEmployed = mysqli_query($conn, $queryEmployed);
$totalEmployed = ($resultEmployed) ? mysqli_fetch_assoc($resultEmployed)['total'] : 0;

// Total number of unemployed graduates (Fixed Query)
$queryUnemployed = "SELECT COUNT(g.id) AS total 
                    FROM graduates g 
                    LEFT JOIN employment_survey e ON g.id = e.graduate_id 
                    WHERE (e.employed IS NULL OR e.employed = 'no' OR e.employed = 'never_employed')";

$resultUnemployed = mysqli_query($conn, $queryUnemployed);
$totalUnemployed = ($resultUnemployed) ? mysqli_fetch_assoc($resultUnemployed)['total'] : 0;

// Course-Job Misalignment Cases
$queryMisalignment = "SELECT COUNT(*) AS total FROM employment_survey 
                      JOIN graduates ON employment_survey.graduate_id = graduates.id 
                      WHERE TRIM(LOWER(graduates.course)) != TRIM(LOWER(employment_survey.business_type))";
$resultMisalignment = mysqli_query($conn, $queryMisalignment);
$totalMisalignment = ($resultMisalignment) ? mysqli_fetch_assoc($resultMisalignment)['total'] : 0;

// Total reports generated
$queryReports = "SELECT COUNT(*) AS total FROM reports"; 
$resultReports = mysqli_query($conn, $queryReports);
$totalReports = ($resultReports) ? mysqli_fetch_assoc($resultReports)['total'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Alumni Tracer System</title>
    <link rel="stylesheet" href="../assets/style.css"> <!-- Ensure this CSS file exists -->
</head>
<body>

    <div class="dashboard-container">
        <h2>Dashboard - Alumni Tracer System</h2>

        <div class="card-container">
            <div class="card">
                <img src="../assets/logo.png" alt="Logo" class="card-logo">
                <h3>Total of Submitted Form</h3>
                <p><?php echo $totalForms; ?></p>
            </div>

            <div class="card">
                <img src="../assets/logo.png" alt="Logo" class="card-logo">
                <h3>Total of Employed Graduates</h3>
                <p><?php echo $totalEmployed; ?></p>
            </div>

            <div class="card">
                <img src="../assets/logo.png" alt="Logo" class="card-logo">
                <h3>Total of Unemployed Graduates</h3>
                <p><?php echo $totalUnemployed; ?></p>
            </div>

            <div class="card">
                <img src="../assets/logo.png" alt="Logo" class="card-logo">
                <h3>Course-Job Misalignment Cases</h3>
                <p><?php echo $totalMisalignment; ?></p>
            </div>

            <div class="card">
                <img src="../assets/logo.png" alt="Logo" class="card-logo">
                <h3>Reports Generated</h3>
                <p><?php echo $totalReports; ?></p>
            </div>
        </div>
    </div>

</body>
</html>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f4f4f4;
    }

    .dashboard-container {
        text-align: center;
    }

    .card-container {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .card {
        width: 250px;
        padding: 20px;
        text-align: center;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        font-family: Arial, sans-serif;
        margin: 10px;
    }

    .card-logo {
        width: 50px;
        height: 50px;
        margin-bottom: 10px;
    }

    h3 {
        font-size: 18px;
        margin-bottom: 5px;
    }

    p {
        font-size: 24px;
        font-weight: bold;
        color: #28a745;
    }
</style>
