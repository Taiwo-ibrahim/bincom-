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

// Get all local governments for dropdown
$lga_sql = "SELECT DISTINCT lga_id, lga_name FROM lga ORDER BY lga_name";
$lga_result = $conn->query($lga_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LGA Results Summary</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
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
            border-left: 5px solid #3498db;
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
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        select:focus {
            outline: none;
            border-color: #3498db;
        }
        .btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        .results-section {
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .results-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .results-header h2 {
            margin: 0;
            font-size: 1.8em;
        }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #3498db;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #666;
            font-size: 0.9em;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .results-table th,
        .results-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .results-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
            text-align: center;
        }
        .results-table td {
            text-align: center;
        }
        .party-column {
            text-align: left !important;
            font-weight: bold;
        }
        .results-table tr:hover {
            background-color: #f8f9fa;
        }
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.2em;
        }
        .vote-bar {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .bar {
            height: 20px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 10px;
            min-width: 30px;
        }
        .comparison-note {
            background-color: #e8f4f8;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #3498db;
        }
        .comparison-note h4 {
            margin-top: 0;
            color: #2c3e50;
        }
        .comparison-note p {
            margin-bottom: 0;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 LGA Results Summary</h1>
        
        <div class="search-section">
            <h3>Select Local Government Area</h3>
            
            <form method="GET" action="">
                <div class="form-group">
                    <label for="lga_id">Choose Local Government:</label>
                    <select name="lga_id" id="lga_id">
                        <option value="">Select a Local Government...</option>
                        <?php
                        if ($lga_result->num_rows > 0) {
                            while($lga = $lga_result->fetch_assoc()) {
                                $selected = (isset($_GET['lga_id']) && $_GET['lga_id'] == $lga['lga_id']) ? 'selected' : '';
                                echo "<option value='" . $lga['lga_id'] . "' $selected>" . 
                                     htmlspecialchars($lga['lga_name']) . " (ID: " . $lga['lga_id'] . ")</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn">Show LGA Results</button>
            </form>
        </div>

        <?php
        if (isset($_GET['lga_id']) && !empty($_GET['lga_id'])) {
            $selected_lga_id = $_GET['lga_id'];
            
            // Get LGA details
            $lga_details_sql = "SELECT * FROM lga WHERE lga_id = ?";
            $stmt = $conn->prepare($lga_details_sql);
            $stmt->bind_param("i", $selected_lga_id);
            $stmt->execute();
            $lga_details_result = $stmt->get_result();
            
            if ($lga_details_result->num_rows > 0) {
                $lga_data = $lga_details_result->fetch_assoc();
                
                echo "<div class='results-section'>";
                echo "<div class='results-header'>";
                echo "<h2>" . htmlspecialchars($lga_data['lga_name']) . "</h2>";
                echo "<p>Local Government Area ID: " . htmlspecialchars($lga_data['lga_id']) . "</p>";
                echo "</div>";
                
                // Get all polling units in this LGA and sum their results
                $polling_units_sql = "SELECT uniqueid FROM polling_unit WHERE lga_id = ?";
                $stmt2 = $conn->prepare($polling_units_sql);
                $stmt2->bind_param("i", $selected_lga_id);
                $stmt2->execute();
                $polling_units_result = $stmt2->get_result();
                
                $polling_unit_ids = [];
                $total_polling_units = 0;
                
                while($unit = $polling_units_result->fetch_assoc()) {
                    $polling_unit_ids[] = $unit['uniqueid'];
                    $total_polling_units++;
                }
                
                if (!empty($polling_unit_ids)) {
                    // Create placeholders for the IN clause
                    $placeholders = str_repeat('?,', count($polling_unit_ids) - 1) . '?';
                    
                    // Get summed results for all polling units in this LGA
                    $results_sql = "SELECT 
                        ar.party_abbreviation,
                        SUM(ar.party_score) as total_votes
                    FROM announced_pu_results ar
                    WHERE ar.polling_unit_uniqueid IN ($placeholders)
                    GROUP BY ar.party_abbreviation
                    ORDER BY total_votes DESC";
                    
                    $stmt3 = $conn->prepare($results_sql);
                    $stmt3->bind_param(str_repeat('s', count($polling_unit_ids)), ...$polling_unit_ids);
                    $stmt3->execute();
                    $results_result = $stmt3->get_result();
                    
                    $total_votes = 0;
                    $results_data = [];
                    
                    while($result = $results_result->fetch_assoc()) {
                        $total_votes += $result['total_votes'];
                        $result['party_name'] = $result['party_abbreviation']; // Use abbreviation as name for now
                        $results_data[] = $result;
                    }
                    
                    if (!empty($results_data)) {
                        // Display summary statistics
                        echo "<div class='summary-stats'>";
                        echo "<div class='stat-card'>";
                        echo "<div class='stat-number'>" . number_format($total_votes) . "</div>";
                        echo "<div class='stat-label'>Total Votes Cast</div>";
                        echo "</div>";
                        
                        echo "<div class='stat-card'>";
                        echo "<div class='stat-number'>" . $total_polling_units . "</div>";
                        echo "<div class='stat-label'>Polling Units</div>";
                        echo "</div>";
                        
                        echo "<div class='stat-card'>";
                        echo "<div class='stat-number'>" . count($results_data) . "</div>";
                        echo "<div class='stat-label'>Parties Participated</div>";
                        echo "</div>";
                        
                        if ($total_votes > 0) {
                            $winning_party = $results_data[0];
                            echo "<div class='stat-card'>";
                            echo "<div class='stat-number'>" . number_format($winning_party['total_votes']) . "</div>";
                            echo "<div class='stat-label'>Highest Votes<br>(" . htmlspecialchars($winning_party['party_abbreviation']) . ")</div>";
                            echo "</div>";
                        }
                        echo "</div>";
                        
                        // Display detailed results table
                        echo "<table class='results-table'>";
                        echo "<thead>";
                        echo "<tr>";
                        echo "<th>Position</th>";
                        echo "<th>Party</th>";
                        echo "<th>Votes</th>";
                        echo "<th>Percentage</th>";
                        echo "<th>Vote Share</th>";
                        echo "</tr>";
                        echo "</thead>";
                        echo "<tbody>";
                        
                        $position = 1;
                        $max_votes = $results_data[0]['total_votes'];
                        
                        foreach($results_data as $result) {
                            $percentage = $total_votes > 0 ? ($result['total_votes'] / $total_votes) * 100 : 0;
                            $bar_width = $max_votes > 0 ? ($result['total_votes'] / $max_votes) * 100 : 0;
                            
                            echo "<tr>";
                            echo "<td><strong>" . $position . "</strong></td>";
                            echo "<td class='party-column'>";
                            echo "<strong>" . htmlspecialchars($result['party_abbreviation']) . "</strong>";
                            echo "</td>";
                            echo "<td><strong>" . number_format($result['total_votes']) . "</strong></td>";
                            echo "<td>" . number_format($percentage, 2) . "%</td>";
                            echo "<td>";
                            echo "<div class='vote-bar'>";
                            echo "<div class='bar' style='width: " . $bar_width . "%;'></div>";
                            echo "<span>" . number_format($percentage, 1) . "%</span>";
                            echo "</div>";
                            echo "</td>";
                            echo "</tr>";
                            
                            $position++;
                        }
                        
                        echo "</tbody>";
                        echo "</table>";
                        
                        // Add comparison note
                        echo "<div class='comparison-note'>";
                        echo "<h4>📋 Note</h4>";
                        echo "<p>These results are calculated by summing all polling unit results within " . 
                             htmlspecialchars($lga_data['lga_name']) . " Local Government Area. " .
                             "This provides a comprehensive view of the actual voting patterns across all " . 
                             $total_polling_units . " polling units in this LGA.</p>";
                        echo "</div>";
                        
                    } else {
                        echo "<div class='no-results'>No voting results found for polling units in this LGA.</div>";
                    }
                } else {
                    echo "<div class='no-results'>No polling units found in this Local Government Area.</div>";
                }
                
                echo "</div>";
                
            } else {
                echo "<div class='no-results'>Local Government Area not found.</div>";
            }
        } else {
            echo "<div class='no-results'>Please select a Local Government Area to view results.</div>";
        }

        $conn->close();
        ?>
    </div>
</body>
</html>