<?php
include '../includes/db_connect.php';

// Get available courses for the dropdown
$coursesQuery = "SELECT DISTINCT course FROM graduates";
$coursesResult = mysqli_query($conn, $coursesQuery);

// Get selected course from the dropdown
$selectedCourse = isset($_GET['course']) ? $_GET['course'] : '';

// Modify queries to filter by course if selected
$courseFilter = $selectedCourse ? " WHERE course = '$selectedCourse'" : "";

// Get distinct graduation years
$years = [];
$yearsQuery = "SELECT DISTINCT graduation_year FROM graduates" . $courseFilter . " ORDER BY graduation_year ASC";
$yearsResult = mysqli_query($conn, $yearsQuery);

if ($yearsResult && mysqli_num_rows($yearsResult) > 0) {
    while ($row = mysqli_fetch_assoc($yearsResult)) {
        $years[] = $row['graduation_year'];
    }
}

// Arrays for chart data
$graduatesPerYear = [];
$employedPerYear = [];
$unemployedPerYear = [];

// Loop through each year and fetch data
foreach ($years as $year) {
    $queryTotal = "SELECT COUNT(*) as total FROM graduates WHERE graduation_year = '$year'" . ($selectedCourse ? " AND course = '$selectedCourse'" : "");
    $queryEmployed = "SELECT COUNT(*) as employed FROM employment_survey 
                      WHERE employed = 'yes' AND graduate_id IN 
                      (SELECT id FROM graduates WHERE graduation_year = '$year'" . ($selectedCourse ? " AND course = '$selectedCourse'" : "") . ")";
    $queryUnemployed = "SELECT COUNT(*) as unemployed FROM employment_survey 
                        WHERE employed IN ('no', 'never_employed') AND graduate_id IN 
                        (SELECT id FROM graduates WHERE graduation_year = '$year'" . ($selectedCourse ? " AND course = '$selectedCourse'" : "") . ")";

    // Execute queries
    $graduatesPerYear[$year] = mysqli_fetch_assoc(mysqli_query($conn, $queryTotal))['total'] ?? 0;
    $employedPerYear[$year] = mysqli_fetch_assoc(mysqli_query($conn, $queryEmployed))['employed'] ?? 0;
    $unemployedPerYear[$year] = mysqli_fetch_assoc(mysqli_query($conn, $queryUnemployed))['unemployed'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Employment Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        canvas {
            width: 700px !important;
            height: 400px !important;
        }
    </style>
</head>
<body>
    <form method="GET">
        <label for="course">Select Course:</label>
        <select name="course" id="course" onchange="this.form.submit()">
            <option value="">All Courses</option>
            <?php while ($course = mysqli_fetch_assoc($coursesResult)): ?>
                <option value="<?php echo $course['course']; ?>" 
                    <?php echo ($selectedCourse == $course['course']) ? 'selected' : ''; ?>>
                    <?php echo $course['course']; ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if (empty($years)): ?>
        <p>No data available for the selected course.</p>
    <?php else: ?>
        <canvas id="employmentChart"></canvas>
        <script>
            new Chart(document.getElementById('employmentChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($years); ?>, 
                    datasets: [
                        {
                            label: 'Total Graduates',
                            data: <?php echo json_encode(array_values($graduatesPerYear)); ?>,
                            backgroundColor: 'blue'
                        },
                        {
                            label: 'Employed Graduates',
                            data: <?php echo json_encode(array_values($employedPerYear)); ?>,
                            backgroundColor: 'green'
                        },
                        {
                            label: 'Unemployed Graduates',
                            data: <?php echo json_encode(array_values($unemployedPerYear)); ?>,
                            backgroundColor: 'red'
                        }
                    ]
                },
                options: {
                    responsive: false,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Graduation Year'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Number of Graduates'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>
