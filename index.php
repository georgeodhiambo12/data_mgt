<?php
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dynamic Chart Generation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js"></script>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        padding: 0;
        box-sizing: border-box;
        background-color: #f4f4f9;
        color: #333;
    }
    h2 {
        color: green;
    }
    .button {
        background-color: #4CAF50;
        border: none;
        color: white;
        padding: 10px 16px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        margin: 2px 1px;
        cursor: pointer;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }
    .button:hover {
        background-color: #45a049;
    }
    .criteriaSet {
        border: 1px solid #ddd;
        padding: 10px;
        margin: 10px 0;
        border-radius: 8px;
        background-color: #fff;
    }
    @media (max-width: 600px) {
        .criteriaSet label, .criteriaSet select, .criteriaSet button {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }
    }
    form {
        margin-bottom: 20px;
    }
    .chart-container {
        width: 100%;
        max-width: 800px;
        margin: auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .statistics {
        margin-top: 20px;
    }
    /* Apply hover effect to individual options within the dropdowns */
    select option:hover {
        background-color: #ADD8E6; /* Light blue color for hover effect */
        color: #000; /* Optional: change text color on hover */
    }
    footer {
        background: #50b3a2;
        color: white;
        text-align: center;
        padding: 10px 0;
        margin-top: 20px;
    }
    .home-link {
        display: inline-block;
        padding: 10px 16px;
        margin-bottom: 20px;
        background-color: #4CAF50;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }
    .home-link:hover {
        background-color: #45a049;
    }
    .home-container {
        text-align: center;
        margin-bottom: 20px;
    }
    a{
        text-decoration:none;
    }
    </style>
</head>
<body>
    <h2><a href="https://tendersoko.co.ke/cpanel">Dynamic Chart Generation</a></h2>
    <div class="home-container">
        <a href="push.php" class="home-link">PUSH</a>
    </div>
    <?php
    $servername = "localhost";
    $username = "tenderso_george";
    $password = "iVL?F1jO&.VP";
    $dbname = "tenderso_data_mgt";

    // Establish connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Initialize data array
    $data = [];
    $chartType = 'bar'; // Default chart type
    $labels = [];

    // Function to fetch data based on criteria set
    function fetchData($conn, $criteria) {
        $conditions = [];
        $criteriaLabelParts = [];
        
        foreach ($criteria as $key => $values) {
            if (!empty($values) && !in_array('All', $values)) {
                $conditions[] = "`$key` IN ('" . implode("', '", $values) . "')";
                $criteriaLabelParts[] = $key . ": " . implode(", ", $values);
            }
        }

        $criteriaLabel = implode(", ", $criteriaLabelParts);
        $sql = "SELECT * FROM tendersodata";
        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $result = $conn->query($sql);
        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        
        return [$data, $criteriaLabel];
    }

    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $criteriaSets = json_decode($_POST['criteriaSets'], true);
        foreach ($criteriaSets as $criteria) {
            list($fetchedData, $criteriaLabel) = fetchData($conn, $criteria);
            $data[] = $fetchedData;
            $labels[] = $criteriaLabel;
        }
        $chartType = $_POST['chartType'];
    }

    $conn->close();
    ?>

    <form method="POST" id="criteriaForm">
        <div id="criteriaSetsContainer">
            <!-- Initial Criteria Set Template -->
            <div class="criteriaSet">
                <h3>Criteria Set 1:</h3>
                <label for="year1">Year:</label>
                <select name="year[]" class="year" multiple>
                    <option value="All">All</option>
                    <?php for ($year = 2021; $year <= 2070; $year++) {
                        echo "<option value='$year'>$year</option>";
                    } ?>
                </select>

                <label for="month1">Month:</label>
                <select name="month[]" class="month" multiple>
                    <option value="All">All</option>
                    <option value="January">January</option>
                    <option value="February">February</option>
                    <option value="March">March</option>
                    <option value="April">April</option>
                    <option value="May">May</option>
                    <option value="June">June</option>
                    <option value="July">July</option>
                    <option value="August">August</option>
                    <option value="September">September</option>
                    <option value="October">October</option>
                    <option value="November">November</option>
                    <option value="December">December</option>
                </select>

                <label for="quarter1">Quarter:</label>
                <select name="quarter[]" class="quarter" multiple>
                    <option value="All">All</option>
                    <option value="Q1">Q1</option>
                    <option value="Q2">Q2</option>
                    <option value="Q3">Q3</option>
                    <option value="Q4">Q4</option>
                </select>

                <label for="week1">Week:</label>
                <select name="week[]" class="week" multiple>
                    <option value="All">All</option>
                    <?php for ($week = 1; $week <= 53; $week++) {
                        $weekFormatted = sprintf('Week %02d', $week);
                        echo "<option value='$weekFormatted'>$weekFormatted</option>";
                    } ?>
                </select>

                <label for="status1">Status:</label>
                <select name="status[]" class="status" multiple>
                    <option value="All">All</option>
                    <option value="One Time">One Time</option>
                    <option value="Repeat">Repeat</option>
                </select>

                <label for="subplan1">Sub_Plan:</label>
                <select name="subplan[]" class="subplan" multiple>
                    <option value="All">All</option>
                    <option value="Annual">Annual</option>
                    <option value="Monthly">Monthly</option>
                    <option value="Archived">Archived</option>
                </select>

                <label for="paymentmode1">Payment_Mode:</label>
                <select name="paymentmode[]" class="paymentmode" multiple>
                    <option value="All">All</option>
                    <option value="MPESA">MPESA</option>
                    <option value="M-PESA">M-PESA</option>
                    <option value="VISA">VISA</option>
                    <option value="MTN">MTN</option>
                    <option value="ZAP">ZAP</option>
                    <option value="TIGO">TIGO</option>
                </select>

                <label for="day1">Day:</label>
                <select name="day[]" class="day" multiple>
                    <option value="All">All</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>

                <label for="country1">Country:</label>
                <select name="country[]" class="country" multiple>
                    <option value="All">All</option>
                    <option value="KENYA">KENYA</option>
                    <option value="TANZANIA">TANZANIA</option>
                    <option value="UGANDA">UGANDA</option>
                </select>

                <label for="ampm1">AM/PM:</label>
                <select name="ampm[]" class="ampm" multiple>
                    <option value="All">All</option>
                    <option value="AM">AM</option>
                    <option value="PM">PM</option>
                </select>
                <button type="button" class="button" onclick="removeCriteria(this)">Remove Criteria</button>
            </div>
        </div>
        <button type="button" class="button" onclick="addCriteria()">Add Criteria Set</button>

        <label for="chartType">Chart Type:</label>
        <select name="chartType" id="chartType">
            <option value="bar">Bar Chart</option>
            <option value="line">Line Chart</option>
            <option value="pie">Pie Chart</option>
        </select>

        <button type="submit" class="button">Generate Chart</button>
    </form>

    <div class="chart-container">
        <canvas id="myChart"></canvas>
    </div>
    <div class="statistics" id="chartStats"></div>
    <button id="downloadButton" class="button">Download Chart</button>
    <button id="downloadExcelButton" class="button">Download Data as Excel</button>

    <form action="logout.php" method="post">
        <button type="submit" class="button">Logout</button>
    </form>
    
    <script>
        let criteriaSetCounter = 1;

        function addCriteria() {
            criteriaSetCounter++;
            const criteriaSetsContainer = document.getElementById('criteriaSetsContainer');
            const newCriteriaSet = document.createElement('div');
            newCriteriaSet.className = 'criteriaSet';
            newCriteriaSet.innerHTML = `
                <h3>Criteria Set ${criteriaSetCounter}:</h3>
                <label for="year${criteriaSetCounter}">Year:</label>
                <select name="year[]" class="year" multiple>
                    <option value="All">All</option>
                    <?php for ($year = 2021; $year <= 2070; $year++) {
                        echo "<option value='$year'>$year</option>";
                    } ?>
                </select>

                <label for="month${criteriaSetCounter}">Month:</label>
                <select name="month[]" class="month" multiple>
                    <option value="All">All</option>
                    <option value="January">January</option>
                    <option value="February">February</option>
                    <option value="March">March</option>
                    <option value="April">April</option>
                    <option value="May">May</option>
                    <option value="June">June</option>
                    <option value="July">July</option>
                    <option value="August">August</option>
                    <option value="September">September</option>
                    <option value="October">October</option>
                    <option value="November">November</option>
                    <option value="December">December</option>
                </select>

                <label for="quarter${criteriaSetCounter}">Quarter:</label>
                <select name="quarter[]" class="quarter" multiple>
                    <option value="All">All</option>
                    <option value="Q1">Q1</option>
                    <option value="Q2">Q2</option>
                    <option value="Q3">Q3</option>
                    <option value="Q4">Q4</option>
                </select>

                <label for="week${criteriaSetCounter}">Week:</label>
                <select name="week[]" class="week" multiple>
                    <option value="All">All</option>
                    <?php for ($week = 1; $week <= 52; $week++) {
                        $weekFormatted = sprintf('Week %02d', $week);
                        echo "<option value='$weekFormatted'>$weekFormatted</option>";
                    } ?>
                </select>

                <label for="status${criteriaSetCounter}">Status:</label>
                <select name="status[]" class="status" multiple>
                    <option value="All">All</option>
                    <option value="One Time">One Time</option>
                    <option value="Repeat">Repeat</option>
                </select>

                <label for="subplan${criteriaSetCounter}">Sub_Plan:</label>
                <select name="subplan[]" class="subplan" multiple>
                    <option value="All">All</option>
                    <option value="Annual">Annual</option>
                    <option value="Monthly">Monthly</option>
                    <option value="Archived">Archived</option>
                </select>

                <label for="paymentmode${criteriaSetCounter}">Payment_Mode:</label>
                <select name="paymentmode[]" class="paymentmode" multiple>
                    <option value="All">All</option>
                    <option value="MPESA">MPESA</option>
                    <option value="M-PESA">M-PESA</option>
                    <option value="VISA">VISA</option>
                    <option value="MTN">MTN</option>
                    <option value="ZAP">ZAP</option>
                    <option value="TIGO">TIGO</option>
                </select>

                <label for="day${criteriaSetCounter}">Day:</label>
                <select name="day[]" class="day" multiple>
                    <option value="All">All</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>

                <label for="country${criteriaSetCounter}">Country:</label>
                <select name="country[]" class="country" multiple>
                    <option value="All">All</option>
                    <option value="KENYA">KENYA</option>
                    <option value="TANZANIA">TANZANIA</option>
                    <option value="UGANDA">UGANDA</option>
                </select>

                <label for="ampm${criteriaSetCounter}">AM/PM:</label>
                <select name="ampm[]" class="ampm" multiple>
                    <option value="All">All</option>
                    <option value="AM">AM</option>
                    <option value="PM">PM</option>
                </select>
                <button type="button" class="button" onclick="removeCriteria(this)">Remove Criteria</button>
            `;
            criteriaSetsContainer.appendChild(newCriteriaSet);
            alert('Criteria Set ' + criteriaSetCounter + ' added.');
        }

        function removeCriteria(button) {
            const criteriaSet = button.parentElement;
            criteriaSet.remove();
            alert('Criteria Set removed.');
        }

        document.getElementById('criteriaForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const form = event.target;
            const criteriaSets = [];
            const criteriaSetElements = document.querySelectorAll('.criteriaSet');
            criteriaSetElements.forEach((criteriaSetElement, index) => {
                const criteria = {};
                criteria.YEAR = Array.from(criteriaSetElement.querySelectorAll('.year option:checked')).map(option => option.value);
                criteria.MONTH = Array.from(criteriaSetElement.querySelectorAll('.month option:checked')).map(option => option.value);
                criteria.QUARTER = Array.from(criteriaSetElement.querySelectorAll('.quarter option:checked')).map(option => option.value);
                criteria.WEEK = Array.from(criteriaSetElement.querySelectorAll('.week option:checked')).map(option => option.value);
                criteria.STATUS = Array.from(criteriaSetElement.querySelectorAll('.status option:checked')).map(option => option.value);
                criteria.SUB_PLAN = Array.from(criteriaSetElement.querySelectorAll('.subplan option:checked')).map(option => option.value);
                criteria.PAYMENT_MODE = Array.from(criteriaSetElement.querySelectorAll('.paymentmode option:checked')).map(option => option.value);
                criteria.DAY = Array.from(criteriaSetElement.querySelectorAll('.day option:checked')).map(option => option.value);
                criteria.COUNTRY = Array.from(criteriaSetElement.querySelectorAll('.country option:checked')).map(option => option.value);
                criteria['AM/PM'] = Array.from(criteriaSetElement.querySelectorAll('.ampm option:checked')).map(option => option.value);
                criteriaSets.push(criteria);
            });

            if (criteriaSets.length === 0) {
                alert('Please add at least one criteria set.');
                return;
            }

            const hiddenField = document.createElement('input');
            hiddenField.type = 'hidden';
            hiddenField.name = 'criteriaSets';
            hiddenField.value = JSON.stringify(criteriaSets);
            form.appendChild(hiddenField);

            form.submit();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const chartData = <?php echo json_encode($data); ?>;
            const chartType = '<?php echo $chartType; ?>';
            const labels = <?php echo json_encode($labels); ?>;

            function getRandomColor() {
                const letters = '0123456789ABCDEF';
                let color = '#';
                for (let i = 0; i < 6; i++) {
                    color += letters[Math.floor(Math.random() * 16)];
                }
                return color;
            }

            // Prepare data for the chart
            const frequencies = chartData.map(set => set.length);
            const changes = frequencies.slice(1).map((num, i) => num - frequencies[i]);

            // Generate datasets including changes indication
            const datasets = [{
                data: frequencies,
                backgroundColor: labels.map(() => getRandomColor()),
                borderColor: labels.map(() => getRandomColor()),
                borderWidth: 1
            }];

            // Generate dynamic chart title
            let chartTitle = 'Comparison between ';
            if (labels.length >= 2) {
                chartTitle += `${labels[0]} and ${labels[1]}`;
            } else {
                chartTitle += labels.join(' and ');
            }

            const ctx = document.getElementById('myChart').getContext('2d');
            const config = {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Frequency'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Criteria'
                            },
                            ticks: {
                                callback: function(value, index, values) {
                                    return labels[index];
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.raw;
                                    // Adding change indication
                                    if (context.dataIndex > 0) {
                                        const change = changes[context.dataIndex - 1];
                                        const symbol = change > 0 ? '↑' : '↓';
                                        label += ` (${symbol}${Math.abs(change)})`;
                                    }
                                    return label;
                                }
                            }
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            formatter: function(value, context) {
                                let changeText = '';
                                if (context.dataIndex > 0 && changes[context.dataIndex - 1] != null) {
                                    const change = changes[context.dataIndex - 1];
                                    const symbol = change > 0 ? '↑' : '↓';
                                    changeText = ` (${symbol}${Math.abs(change)})`;
                                }
                                return value + (changeText || '');
                            },
                            font: {
                                weight: 'bold'
                            }
                        },
                        title: {
                            display: true,
                            text: chartTitle
                        }
                    }
                },
                plugins: [ChartDataLabels]
            };

            const myChart = new Chart(ctx, config);

            document.getElementById('downloadButton').addEventListener('click', function() {
                const link = document.createElement('a');
                link.href = myChart.toBase64Image();
                link.download = 'chart.png';
                link.click();
            });

            document.getElementById('downloadExcelButton').addEventListener('click', function() {
                // Flatten the chart data
                const flattenedData = chartData.flat().map((row, index) => ({
                    ...row,
                    Criteria: labels[Math.floor(index / chartData[0].length)]
                }));
                
                const ws = XLSX.utils.json_to_sheet(flattenedData);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Chart Data");
                XLSX.writeFile(wb, "chart_data.xlsx");
            });

            // Display statistical notes
            const chartStats = document.getElementById('chartStats');
            let statsHTML = '<h3>Statistical Notes:</h3>';
            statsHTML += '<ul>';
            labels.forEach((label, index) => {
                statsHTML += `<li><strong>${label}:</strong> Frequency = ${frequencies[index]}`;
                if (index > 0) {
                    const change = changes[index - 1];
                    const changeSymbol = change > 0 ? '<span style="color: green;">↑ Increase</span>' : '<span style="color: red;">↓ Decrease</span>';
                    statsHTML += ` (${changeSymbol} of ${Math.abs(change)}) from previous criteria`;
                }
                statsHTML += '</li>';
            });
            statsHTML += '</ul>';
            chartStats.innerHTML = statsHTML;
        });
    </script>
    <footer>
        &copy; TenderSoko, 2024. All rights reserved
    </footer>
</body>
</html>
