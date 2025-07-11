<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bincom_test.sql"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected to imported database successfully";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bincom Test</title>
</head>
<body>
    <!-- available  -->
</body>
</html>


<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bincom_test.sql";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all polling units for dropdown
$polling_units_sql = "SELECT DISTINCT uniqueid, polling_unit_name FROM polling_unit ORDER BY polling_unit_name";
$polling_units_result = $conn->query($polling_units_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polling Unit Results</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        .search-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 5px solid #4CAF50;
        }
        .search-section h3 {
            margin-top: 0;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        select, input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        select:focus, input[type="text"]:focus {
            outline: none;
            border-color: #4CAF50;
        }
        .btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }
        .results-section {
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .results-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .results-header h2 {
            margin: 0;
            font-size: 1.8em;
        }
        .polling-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        .info-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        .info-value {
            color: #333;
            font-size: 1.1em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.2em;
        }
        .search-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .search-methods {
                grid-template-columns: 1fr;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗳️ Polling Unit Results</h1>
        
        <div class="search-section">
            <h3>Search for Polling Unit Results</h3>
            
            <div class="search-methods">
                <!-- Search by Dropdown -->
                <div>
                    <form method="GET" action="">
                        <div class="form-group">
                            <label for="polling_unit">Select Polling Unit:</label>
                            <select name="polling_unit" id="polling_unit">
                                <option value="">Choose a polling unit...</option>
                                <?php
                                if ($polling_units_result->num_rows > 0) {
                                    while($unit = $polling_units_result->fetch_assoc()) {
                                        $selected = (isset($_GET['polling_unit']) && $_GET['polling_unit'] == $unit['uniqueid']) ? 'selected' : '';
                                        echo "<option value='" . $unit['uniqueid'] . "' $selected>" . 
                                             htmlspecialchars($unit['polling_unit_name']) . " (ID: " . $unit['uniqueid'] . ")</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="btn">Show Results</button>
                    </form>
                </div>

                <!-- Search by ID -->
                <div>
                    <form method="GET" action="">
                        <div class="form-group">
                            <label for="unit_id">Or Enter Polling Unit ID:</label>
                            <input type="text" name="unit_id" id="unit_id" placeholder="Enter polling unit ID..." 
                                   value="<?php echo isset($_GET['unit_id']) ? htmlspecialchars($_GET['unit_id']) : ''; ?>">
                        </div>
                        <button type="submit" class="btn">Search by ID</button>
                    </form>
                </div>
            </div>
        </div>

        <?php
        $selected_unit_id = '';
        
        // Check which search method was used
        if (isset($_GET['polling_unit']) && !empty($_GET['polling_unit'])) {
            $selected_unit_id = $_GET['polling_unit'];
        } elseif (isset($_GET['unit_id']) && !empty($_GET['unit_id'])) {
            $selected_unit_id = $_GET['unit_id'];
        }

        if (!empty($selected_unit_id)) {
            // Fetch polling unit details
            $unit_sql = "SELECT * FROM polling_unit WHERE uniqueid = ?";
            $stmt = $conn->prepare($unit_sql);
            $stmt->bind_param("s", $selected_unit_id);
            $stmt->execute();
            $unit_result = $stmt->get_result();

            if ($unit_result->num_rows > 0) {
                $unit_data = $unit_result->fetch_assoc();
                
                echo "<div class='results-section'>";
                echo "<div class='results-header'>";
                echo "<h2>" . htmlspecialchars($unit_data['polling_unit_name']) . "</h2>";
                echo "<p>Polling Unit ID: " . htmlspecialchars($unit_data['uniqueid']) . "</p>";
                echo "</div>";
                
                echo "<div class='polling-info'>";
                echo "<div class='info-grid'>";
                
                // Display all available information
                foreach ($unit_data as $key => $value) {
                    if ($key != 'uniqueid' && $key != 'polling_unit_name') {
                        echo "<div class='info-item'>";
                        echo "<div class='info-label'>" . ucwords(str_replace('_', ' ', $key)) . ":</div>";
                        echo "<div class='info-value'>" . htmlspecialchars($value) . "</div>";
                        echo "</div>";
                    }
                }
                
                echo "</div>";
                echo "</div>";
                echo "</div>";
                
            } else {
                echo "<div class='no-results'>No polling unit found with ID: " . htmlspecialchars($selected_unit_id) . "</div>";
            }
        } else {
            echo "<div class='no-results'>Please select a polling unit or enter a polling unit ID to view results.</div>";
        }

        $conn->close();
        ?>
    </div>
</body>
</html>