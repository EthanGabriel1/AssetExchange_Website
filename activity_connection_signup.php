<?php 

// Define the variables containining the details of the connection
$dbname = "u843230181_test"; // Name of the database
$hostname = "localhost"; // Hostname of the database
$username = "u843230181_group7"; // Username of the database user
$password = "Pass12346969"; // Password of the database user

try {
    // Instantiate a new PDO connection
    $conn = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);

    // set the PDO error mode to exception to enable error reporting
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // prevent the PDO from using emulated prepared statements to reduce SQL injection risk
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Check if the user sent a POST request (this means the user is sending data)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
        // Preprocess the username
        $username = trim($_POST['username']);

        // Check if credentials are missing
        if (empty($username) || empty($_POST['password']) || $username === "" || $_POST['password'] === "") {
            die("Error: Username or password is missing.");
        }

        // Determine if the user's action is to register a new account or login an existing account
        if (isset($_POST['action']) && $_POST['action'] == 'signup') {
            // Check if the username already exists with a prepared statement
            try {
                $stmt = $conn->prepare("SELECT count(*) FROM `users` WHERE username = ?");
                $stmt->execute([$username]);

                 // Executes if user already exists
                if (((int) $stmt->fetchColumn()) > 0) {
                    die("Error: Username already exists.");
                }
            }
            catch (PDOException $e) {
                die("Error: User registration failed due to a database error. " . $e->getMessage());
            }
            catch (Exception $e) {
                die("Error: User registration failed due to a backend error. " . $e->getMessage());
            }

            // Hash the password (bcrypt limits the hash length to 60 chars but we will have 255 chars in the database just in case)
            $pwHash = password_hash($_POST['password'], PASSWORD_BCRYPT);

            // Insert the new user into the database
            try {
                // Prepare the SQL statement to insert a new user to the database
                $stmt = $conn->prepare("INSERT INTO `users` (username, pwHash) VALUES (?, ?)");
                // Execute the prepared statement with the supplied details
                if ($stmt->execute([$username, $pwHash])) {
                    echo "User registration successful!";
                }
                else {
                    die("Error: User registration failed due to a database error.");
                }
                
            }
            catch (PDOException $e) {
                die("Error: User registration failed due to a database error. " . $e->getMessage());
            }
            catch (Exception $e) {
                die("Error: User registration failed due to a backend error. " . $e->getMessage());
            }
        }
        else {
            // User tries to do anything other than signing up
            die("Error: Action not supported.");
        }
    }
    elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
        // For testing purposes
        echo 'Signup landing page';
    }
}
catch (PDOException $e) {
    // Usually if no internet or can't connect to the database
    die('Error: Connection failed: ' . $e->getMessage());
}
catch (Exception $e) {
    die('Error: Backend error: ' . $e->getMessage());
}

 ?>