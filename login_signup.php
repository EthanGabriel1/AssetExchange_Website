<?php 
    
    require_once 'phpmailer_demo.php';
    require_once 'config_assetexchange.php';

    function isValidUuid($uuid) {
        // Can check if valid UUID - though UUIDv1 is not secure
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[14][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid) === 1;
    }

    function generateUuidV4() {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    try {
        // Instantiate a new PDO connection
        $conn = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);

        // set the PDO error mode to exception to enable error reporting
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // prevent the PDO from using emulated prepared statements to reduce SQL injection risk
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        // Check if the user sent a POST request (this means the user is sending data)
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Determine if the user's action is to register a new account or login an existing account
            if (isset($_POST['action']) && $_POST['action'] == 'signup') {
                if (!isset($_POST['email']) || !isset($_POST['password'])) {
                    die(json_encode([
                        "code"=>'Error: Missing email or password.'
                    ]));
                }

                // Preprocess the username
                $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));

                // Check if credentials are missing
                if (empty($email) || empty($_POST['password']) || $email === "" || $_POST['password'] === "") {
                    die(json_encode([
                        'code'=> 'Error: Empty email or password.'
                    ]));
                }

                // Check if the username already exists with a prepared statement
                try {
                    $stmt = $conn->prepare("SELECT count(*) FROM `users` WHERE email = ?");
                    $stmt->execute([$email]);

                     // Executes if user already exists
                    if (((int) $stmt->fetchColumn()) > 0) {
                        die(json_encode([
                            'code'=> 'Error: Email already exists.'
                        ]));
                    }
                }
                catch (PDOException $e) {
                    die(json_encode([
                        'code'=> 'Error: User registration failed due to a database error. ' . $e->getMessage()
                    ]));
                }
                catch (Exception $e) {
                    die(json_encode([
                        'code'=> 'Error: User registration failed due to a backend error. ' . $e->getMessage()
                    ]));
                }

                if (isset($_POST['full_name']) && isset($_POST['role_id'])) {
                    // Hash the password (bcrypt limits the hash length to 60 chars but we will have 255 chars in the database just in case)
                    $pw_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
                    $fullName = trim($_POST["full_name"]);

                    // Insert the new user into the database
                    try {
                        // Prepare the SQL statement to insert a new user to the database
                        $stmt = $conn->prepare("INSERT INTO `users` (pw_hash, full_name, email, role_id) VALUES (?, ?, ?, ?);");
                        // Execute the prepared statement with the supplied details
                        if ($stmt->execute([$pw_hash, $fullName, $email, $_POST['role_id']])) {
                            $stmt = $conn->prepare("SELECT (email_verification_code) FROM `users` WHERE email = ?;");

                            // Execute the prepared statement with the supplied details
                            if ($stmt->execute([$email])) {
                                $code = $stmt->fetchColumn();
                                if ($code) {
                                    send_verification_mail($email, $code);
                                }
                                else {
                                    die('{"code": "Error: Email verification failed due to a database error."}');
                                }
                            }
                            else {
                                die('{"code": "Error: Email verification failed due to a database error."}');
                            }
                            // echo '{"code": "User registration successful!"}';
                        }
                        else {
                            die('{"code": "Error: User registration failed due to a database error."}');
                        }
                    }
                    catch (PDOException $e) {
                        die('{"code": "Error: User registration failed due to a database error. "' . $e->getMessage() . '"}');
                    }
                    catch (Exception $e) {
                        die('{"code": "Error: User registration failed due to a backend error. ' . $e->getMessage() . '"}');
                    }
                }
                else {
                    die('{"code": "Error: Missing full name or role ID"}');
                }
            }
            elseif (isset($_POST['action']) && $_POST['action'] == 'send_verification_code') {
                if (!isset($_POST['email'])) {
                    die(json_encode([
                        "code"=>'Error: Email is missing.'
                    ]));
                }

                // Preprocess the username
                $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));

                // Check if credentials are missing
                if (empty($email) || $email === "") {
                    die(json_encode([
                        'code'=> 'Error: Empty email.'
                    ]));
                }

                try {
                    $stmt = $conn->prepare("SELECT * FROM `users` WHERE email = ?;");
                    $stmt->execute([$email]);

                     // Executes if user doesn't exist
                    if ($stmt->rowCount() == 0) {
                        die(json_encode([
                            'code'=> 'Error: Error: Email does not exist.'
                        ]));
                    }
                }
                catch (PDOException $e) {
                    die(json_encode([
                        'code'=> 'Error: Email verification failed due to a database error. ' . $e->getMessage()
                    ]));
                }
                catch (Exception $e) {
                    die(json_encode([
                        'code'=> 'Error: Email verification failed due to a backend error. ' . $e->getMessage()
                    ]));
                }

                if (isset($_POST['email_verification_code']) && !is_null($_POST['email_verification_code']) && !empty($_POST['email_verification_code'])) {
                    try {
                        // Prepare the SQL statement to insert a new user to the database
                        $stmt = $conn->prepare("SELECT * FROM `users` WHERE email = ?;");

                        // Execute the prepared statement with the supplied details
                        if ($stmt->execute([$email])) {
                            $user = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($user) {
                                if ($user['email_verification_code'] == $_POST['email_verification_code']) {
                                    $stmt = $conn->prepare("UPDATE `users` SET email_verified = 1 WHERE email = ?;");
                                    if ($stmt->execute([$email])) {

                                        $uuid = generateUuidV4();

                                        $stmt = $conn->prepare("INSERT INTO `sessions` (session_id, user_id) VALUES (?, ?);");
                                        if ($stmt->execute([$uuid, $user["user_id"]])) {
                                            echo json_encode([
                                                'code' => 'Email is now verified',
                                                'user_id' => $user['user_id'],
                                                'session_id' => $uuid,
                                                'email' => $user['email'],
                                                'full_name' => $user['full_name'],
                                                'role_id' => $user['role_id'],
                                                'profile_pic_path' => $user['profile_pic_path']
                                            ]);
                                        }
                                    }
                                    else {
                                        die('{"code": "Error: Email verification failed due to a database error."}');
                                    }
                                }
                                else {
                                    echo '{"code": "Error: Invalid verification code"}';
                                }
                                // echo '{"code": "' . $code . '-' . $_POST['email_verification_code'] . '"}';
                            }
                            else {
                                die(json_encode([
                                    'code'=> 'Error: Email verification failed due to a database error. ' . $e->getMessage()
                                ]));
                            }
                        }
                        else {
                            die(json_encode([
                                'code'=> 'Error: Email verification failed due to a backend error. ' . $e->getMessage()
                            ]));
                        }
                    }
                    catch (PDOException $e) {
                        die(json_encode([
                            'code'=> 'Error: Email verification failed due to a database error. ' . $e->getMessage()
                        ]));
                    }
                    catch (Exception $e) {
                        die(json_encode([
                            'code'=> 'Error: Email verification failed due to a backend error. ' . $e->getMessage()
                        ]));
                    }
                }
                else {
                    die('{"code": "Error: Missing verification code."}');
                }
            }
            elseif (isset($_POST['action']) && $_POST['action'] == 'login') {
                if (!isset($_POST['email']) || !isset($_POST['password'])) {
                    die(json_encode([
                        "code"=>'Error: Email is missing.'
                    ]));
                }

                // Preprocess the username
                $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));

                // Check if credentials are missing
                if (empty($email) || empty($_POST['password']) || $email === "" || $_POST['password'] === "") {
                    die(json_encode([
                        'code'=> 'Error: Empty email.'
                    ]));
                }
                
                try {
                    $stmt = $conn->prepare("SELECT * FROM `users` WHERE email = ?;");
                    $stmt->execute([$email]);

                     // Executes if user doesn't exist
                    if ($stmt->rowCount() == 0) {
                        die('{"code": "Error: Email does not exist."}');
                    }
                }
                catch (PDOException $e) {
                    die('{"code": "Error: Email verification failed due to a database error. ' . $e->getMessage() . '"}');
                }
                catch (Exception $e) {
                    die('{"code": "Error: Email verification failed due to a backend error. ' . $e->getMessage() . '"}');
                }

                try {
                    // Prepare the SQL statement to insert a new user to the database
                    $stmt = $conn->prepare("SELECT * FROM `users` WHERE email = ?;");

                    // Execute the prepared statement with the supplied details
                    if ($stmt->execute([$email])) {
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($user) {
                            if (password_verify($_POST["password"], $user["pw_hash"])) {
                                $uuid = generateUuidV4();

                                $stmt = $conn->prepare("INSERT INTO `sessions` (session_id, user_id) VALUES (?, ?);");
                                if ($stmt->execute([$uuid, $user["user_id"]])) {
                                    echo json_encode([
                                        'code' => 'Login successful',
                                        'user_id' => $user['user_id'],
                                        'session_id' => $uuid,
                                        'email' => $user['email'],
                                        'full_name' => $user['full_name'],
                                        'role_id' => $user['role_id'],
                                        'profile_pic_path' => $user['profile_pic_path']
                                    ]);
                                }
                            }
                            else {
                                echo '{"code": "Error: Invalid password"}';
                                // echo '{"code": "Error: Invalid password ' . $_POST["password"] . ' ' . $pw_hash . '"}';
                            }
                        }
                        else {
                            die('{"code": "Error: Email verification failed due to a database error."}');
                        }
                    }
                    else {
                        die('{"code": "Error: Email verification failed due to a database error."}');
                    }
                }
                catch (PDOException $e) {
                    die('{"code": "Error: Email verification failed due to a database error. "' . $e->getMessage() . '"}');
                }
                catch (Exception $e) {
                    die('{"code": "Error: Email verification failed due to a backend error. ' . $e->getMessage() . '"}');
                }
            }
            elseif (isset($_POST['action']) && $_POST['action'] == 'logout') {
                if (isset($_POST['session_id'])) {
                    $uuid = trim($_POST['session_id']);
                    if (!empty($uuid) && isValidUuid($uuid)) {
                        die(json_encode([
                            "code"=> "Error: Empty or invalid session ID"
                        ]));
                    }

                    try {
                        $stmt = $conn->prepare("SELECT * FROM `sessions` WHERE session_id = ?;");
                        $stmt->execute([$uuid]);

                         // Executes if session doesn't exist
                        if ($stmt->rowCount() == 0) {
                            die('{"code": "Error: You are already logged out!"}');
                        }

                        $stmt = $conn->prepare('DELETE FROM `sessions` WHERE session_id = ?;');
                        $stmt->execute([$uuid]);

                        echo json_encode([
                            'code' => "You are now logged out."
                        ]);
                    }
                    catch (PDOException $e) {
                        die('{"code": "Error: Email verification failed due to a database error. "' . $e->getMessage() . '"}');
                    }
                    catch (Exception $e) {
                        die('{"code": "Error: Email verification failed due to a backend error. "' . $e->getMessage() . '"}');
                    }
                }
                else {
                    die(json_encode([
                        "code"=> "Error: Missing session ID"
                    ]));
                }
            }
            elseif (isset($_POST['action']) && $_POST['action'] == 'check_email_exists') {
                if (!isset($_POST['email'])) {
                    die(json_encode([
                        "code"=>'Error: Missing email.'
                    ]));

                    // Check if the username already exists with a prepared statement
                    try {
                        $stmt = $conn->prepare("SELECT count(*) FROM `users` WHERE email = ?");
                        $stmt->execute([$email]);

                         // Executes if user already exists
                        if (((int) $stmt->fetchColumn()) > 0) {
                            die(json_encode([
                                'code'=> 'Email exists.'
                            ]));
                        }
                        else {
                            die(json_encode([
                                'code'=> 'Email does not exist.'
                            ]));
                        }
                    }
                    catch (PDOException $e) {
                        die(json_encode([
                            'code'=> 'Error: User registration failed due to a database error. ' . $e->getMessage()
                        ]));
                    }
                    catch (Exception $e) {
                        die(json_encode([
                            'code'=> 'Error: User registration failed due to a backend error. ' . $e->getMessage()
                        ]));
                    }
                }

                // Preprocess the username
                $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
            }

            else {
                // User tries to do anything other than signing up
                die('{"code": "Error: Action not supported."}');
            }
        }
        elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // For testing purposes
            echo 'Signup landing page';
        }
    }
    catch (PDOException $e) {
        // Usually if no internet or can't connect to the database
        die('{"code": "Error: Connection failed: ' . $e->getMessage() . '"}');
    }
    catch (Exception $e) {
        die('{"code": "Error: Backend error: ' . $e->getMessage() . '"}');
    }

     ?>