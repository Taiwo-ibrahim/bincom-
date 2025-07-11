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

// Get all parties for the form
$parties_sql = "SELECT DISTINCT party_abbreviation FROM announced_pu_results ORDER BY party_abbreviation";
$parties_result = $conn->query($parties_sql);

// Get all LGAs for dropdown
$lga_sql = "SELECT lga_id, lga_name FROM lga ORDER BY lga_name";
$lga_result = $conn->query($lga_sql);

// Handle form submission
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_results'])) {
    $polling_unit_id = $_POST['polling_unit_id'];
    $polling_unit_name = $_POST['polling_unit_name'];
    $lga_id = $_POST['lga_id'];
    $ward_id = $_POST['ward_id'];
    $entered_by_user = $_POST['entered_by_user'];
    $date_entered = date('Y-m-d H:i:s');
    
    // Validate required fields
    if (empty($polling_unit_id) || empty($polling_unit_name) || empty($lga_id) || empty($entered_by_user)) {
        $message = "Please fill in all required fields.";
        $messageType = "error";
    } else {
        // Check if polling unit already exists
        $check_sql = "SELECT uniqueid FROM polling_unit WHERE uniqueid = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $polling_unit_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $message = "Polling unit with ID '$polling_unit_id' already exists!";
            $messageType = "error";
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Insert new polling unit
                $insert_pu_sql = "INSERT INTO polling_unit (uniqueid, polling_unit_name, lga_id, ward_id, entered_by_user, date_entered) VALUES (?, ?, ?, ?, ?, ?)";
                $insert_pu_stmt = $conn->prepare($insert_pu_sql);
                $insert_pu_stmt->bind_param("ssiiss", $polling_unit_id, $polling_unit_name, $lga_id, $ward_id, $entered_by_user, $date_entered);
                $insert_pu_stmt->execute();
                
                // Insert results for each party
                $insert_result_sql = "INSERT INTO announced_pu_results (polling_unit_uniqueid, party_abbreviation, party_score, entered_by_user, date_entered) VALUES (?, ?, ?, ?, ?)";
                $insert_result_stmt = $conn->prepare($insert_result_sql);
                
                $total_votes = 0;
                $parties_with_votes = 0;
                
                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'party_') === 0) {
                        $party_abbreviation = substr($key, 6); // Remove 'party_' prefix
                        $party_score = intval($value);
                        
                        if ($party_score > 0) {
                            $parties_with_votes++;
                        }
                        
                        $total_votes += $party_score;
                        
                        $insert_result_stmt->bind_param("ssiss", $polling_unit_id, $party_abbreviation, $party_score, $entered_by_user, $date_entered);
                        $insert_result_stmt->execute();
                    }
                }
                
                // Commit transaction
                $conn->commit();
                
                $message = "Successfully added new polling unit '$polling_unit_name' with results for all parties. Total votes: $total_votes, Parties with votes: $parties_with_votes";
                $messageType = "success";
                
                // Clear form data
                $_POST = array();
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $message = "Error adding polling unit: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Polling Unit Results</title>
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
        .form-section {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 5px solid #28a745;
        }
        .form-section h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
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
        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus {
            outline: none;
            border-color: #28a745;
        }
        .required {
            color: #dc3545;
        }
        .parties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .party-input {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #007bff;
        }
        .party-input label {
            color: #007bff;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .party-input input {
            margin-bottom: 0;
        }
        .btn {
            padding: 15px 40px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: transform 0.2s;
            display: block;
            margin: 30px auto 0;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .vote-summary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: center;
        }
        .vote-summary h4 {
            margin: 0 0 10px 0;
        }
        .vote-summary #totalVotes {
            font-size: 2em;
            font-weight: bold;
        }
        .instructions {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }
        .instructions h4 {
            margin-top: 0;
            color: #1976d2;
        }
        .instructions ul {
            margin-bottom: 0;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗳️ Add New Polling Unit Results</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="instructions">
            <h4>📋 Instructions</h4>
            <ul>
                <li>Fill in the polling unit details in the form below</li>
                <li>Enter vote counts for each party (enter 0 if no votes)</li>
                <li>All fields marked with <span class="required">*</span> are required</li>
                <li>The system will automatically calculate total votes</li>
                <li>Click "Save Polling Unit Results" to store the data</li>
            </ul>
        </div>
        
        <form method="POST" action="">
            <div class="form-section">
                <h3>Polling Unit Information</h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="polling_unit_id">Polling Unit ID <span class="required">*</span></label>
                        <input type="text" name="polling_unit_id" id="polling_unit_id" 
                               value="<?php echo isset($_POST['polling_unit_id']) ? htmlspecialchars($_POST['polling_unit_id']) : ''; ?>" 
                               required placeholder="Enter unique polling unit ID">
                    </div>
                    
                    <div class="form-group">
                        <label for="polling_unit_name">Polling Unit Name <span class="required">*</span></label>
                        <input type="text" name="polling_unit_name" id="polling_unit_name" 
                               value="<?php echo isset($_POST['polling_unit_name']) ? htmlspecialchars($_POST['polling_unit_name']) : ''; ?>" 
                               required placeholder="Enter polling unit name">
                    </div>
                    
                    <div class="form-group">
                        <label for="lga_id">Local Government Area <span class="required">*</span></label>
                        <select name="lga_id" id="lga_id" required>
                            <option value="">Select LGA...</option>
                            <?php
                            if ($lga_result->num_rows > 0) {
                                while($lga = $lga_result->fetch_assoc()) {
                                    $selected = (isset($_POST['lga_id']) && $_POST['lga_id'] == $lga['lga_id']) ? 'selected' : '';
                                    echo "<option value='" . $lga['lga_id'] . "' $selected>" . 
                                         htmlspecialchars($lga['lga_name']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="ward_id">Ward ID</label>
                        <input type="number" name="ward_id" id="ward_id" 
                               value="<?php echo isset($_POST['ward_id']) ? htmlspecialchars($_POST['ward_id']) : ''; ?>" 
                               placeholder="Enter ward ID (optional)">
                    </div>
                    
                    <div class="form-group">
                        <label for="entered_by_user">Entered By <span class="required">*</span></label>
                        <input type="text" name="entered_by_user" id="entered_by_user" 
                               value="<?php echo isset($_POST['entered_by_user']) ? htmlspecialchars($_POST['entered_by_user']) : ''; ?>" 
                               required placeholder="Enter your name/ID">
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Party Results</h3>
                <p>Enter the number of votes received by each party:</p>
                
                <div class="parties-grid">
                    <?php
                    if ($parties_result->num_rows > 0) {
                        while($party = $parties_result->fetch_assoc()) {
                            $party_abbr = $party['party_abbreviation'];
                            $field_name = "party_" . $party_abbr;
                            $value = isset($_POST[$field_name]) ? $_POST[$field_name] : '';
                            
                            echo "<div class='party-input'>";
                            echo "<label for='$field_name'>$party_abbr</label>";
                            echo "<input type='number' name='$field_name' id='$field_name' value='$value' min='0' class='vote-input' placeholder='0'>";
                            echo "</div>";
                        }
                    }
                    ?>
                </div>
                
                <div class="vote-summary">
                    <h4>Total Votes</h4>
                    <div id="totalVotes">0</div>
                </div>
            </div>
            
            <button type="submit" name="submit_results" class="btn">💾 Save Polling Unit Results</button>
        </form>
    </div>
    
    <script>
        // Calculate total votes in real-time
        function calculateTotalVotes() {
            const voteInputs = document.querySelectorAll('.vote-input');
            let total = 0;
            
            voteInputs.forEach(input => {
                const value = parseInt(input.value) || 0;
                total += value;
            });
            
            document.getElementById('totalVotes').textContent = total.toLocaleString();
        }
        
        // Add event listeners to all vote inputs
        document.querySelectorAll('.vote-input').forEach(input => {
            input.addEventListener('input', calculateTotalVotes);
            input.addEventListener('change', calculateTotalVotes);
        });
        
        // Calculate initial total
        calculateTotalVotes();
        
        // Auto-generate polling unit ID suggestion
        document.getElementById('polling_unit_name').addEventListener('input', function() {
            const name = this.value.trim();
            if (name && !document.getElementById('polling_unit_id').value) {
                const suggestion = name.replace(/\s+/g, '_').toLowerCase() + '_' + Date.now().toString().slice(-4);
                document.getElementById('polling_unit_id').placeholder = 'Suggestion: ' + suggestion;
            }
        });
    </script>
</body>
</html>
