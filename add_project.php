<?php

require_once 'config_assetexchange.php';

define('APP_PUBLIC_DIR', __DIR__. '/filegator/repository/user/');

$MAX_FILE_SIZE = 40; // 40 MB

function check_file_name($file_name) {
    if (preg_match("`^[-0-9A-Z_\.]+$`i", $file_name)) {
        return true;
    }
    else {
        return false;
    }
}

function generateUuidV4() {
    $data = random_bytes(16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST["no_image"])) {
            try {
                $conn = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
                // set the PDO error mode to exception to enable error reporting
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // prevent the PDO from using emulated prepared statements to reduce SQL injection risk
                $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                if ($conn->beginTransaction()) {
                    if (isset($_POST['project_owner_id']) && isset($_POST['project_title']) && isset($_POST['project_description'])) {
                        if (isset($_POST['priority'])) {
                            $priority = $_POST['priority'] == 'true';
                        }
                        else {
                            $priority = 0;
                        }
                        
                        if (isset($_POST['due_date']) && !is_null($_POST['due_date']) && $_POST["due_date"] != "null") {
                            $sql_query = "INSERT INTO `projects` (project_owner_id, project_title, project_description, due_date, priority) VALUES (?, ?, ?, ?, ?);";
                            $sql_data = [$_POST['project_owner_id'], $_POST['project_title'], $_POST['project_description'], $_POST['due_date'], $priority];
                        }
                        else {
                            $sql_query = "INSERT INTO `projects` (project_owner_id, project_title, project_description, priority) VALUES (?, ?, ?, ?);";
                            $sql_data = [$_POST['project_owner_id'], $_POST['project_title'], $_POST['project_description'], $priority];
                        }
                        
                        $stmt = $conn->prepare($sql_query);

                        if ($stmt->execute($sql_data)) {
                            $project_id = $conn->lastInsertId();

                            if (isset($_POST['share_with'])) {
                                $stmt = $conn->prepare("SELECT user_id FROM `users` WHERE email = ?;");
                                $stmt->execute([$_POST['share_with']]);

                                if ($stmt->rowCount() == 0) {
                                    $conn->rollBack();
                                    die(json_encode([
                                        'code'=> 'Error: Shared email address does not exist.'
                                    ]));
                                }
                                else {
                                    $share_user_id = $stmt->fetchColumn();

                                    if ($_POST['project_owner_id'] != $share_user_id) {
                                        $stmt = $conn->prepare("INSERT INTO `project_shares` (project_id, project_owner_id, share_user_id, privileges) VALUES (?, ?, ?, ?);");
                                        if ($stmt->execute([$project_id, $_POST['project_owner_id'], $share_user_id, 2])) {
                                            if ($conn->commit()) {
                                                die(json_encode([
                                                    "code" => "Project added and shared successfully."
                                                ]));
                                            }
                                            else {
                                                $conn->rollBack();
                                                die(json_encode([
                                                    "code" => "Error: Unable to add the project."
                                                ]));
                                            }
                                        }
                                    }
                                    else {
                                        $conn->rollBack();
                                        die(json_encode([
                                            "code" => "Error: Cannot share the project to oneself"
                                        ]));
                                    }
                                }
                            }
                            else {
                                if ($conn->commit()) {
                                    die(json_encode([
                                        "code" => "Project added successfully."
                                    ]));
                                }
                                else {
                                    $conn->rollBack();
                                    die(json_encode([
                                        "code" => "Error: Unable to add the project."
                                    ]));
                                }
                            }
                        }
                        else {
                            $conn->rollBack();
                            die(json_encode([
                                "code" => "Error: Unable to add project to the database."
                            ]));
                        }
                    }
                    else {
                        $conn->rollBack();
                        die(json_encode([
                            "code" => "Error: Missing project details."
                        ]));
                    }
                }
                else {
                    die(json_encode([
                        "code" => "Error: Unable to add the project due to database error."
                    ]));
                }
            }
            catch (PDOException $e) {
                die(json_encode([
                    "code" => "Error: Unable to add project due to database error. Line: " .  $e->getLine() . " " . $e->getMessage()
                ]));
            }
            catch (Exception $e) {
                die(json_encode([
                    "code"=> "Error: Unable to add project due to a backend error: " . $e->getMessage()
                ]));
            }
        }
        else {
            $file_name = basename($_FILES["file"]["name"]); // file extension is included in the basename
            $file_ext =  strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $new_file_name = generateUuidV4();
            $target_file = APP_PUBLIC_DIR . $new_file_name . '.' . $file_ext;

            if ($_FILES["file"]["size"] > $MAX_FILE_SIZE * 1000 * 1000) {
                die(json_encode([
                    "code" => "Error: File size cannot exceed {$MAX_FILE_SIZE} MB"
                ]));
            }

            if (strlen($file_name) > 255) {
                die(json_encode([
                    "code" => "Error: File name is too long"
                ]));
            }
            
            // Check if the upload is successful
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                $file_hash = hash_file("sha256", $target_file);

                if ($file_hash) {
                    try {
                        $conn = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
                        // set the PDO error mode to exception to enable error reporting
                        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        // prevent the PDO from using emulated prepared statements to reduce SQL injection risk
                        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                        if ($conn->beginTransaction()) {
                            $stmt = $conn->prepare("INSERT INTO `files` (file_id, file_name, file_ext, file_hash) VALUES (?, ?, ?, ?);");
                            // Executes if user already exists
                            if ($stmt->execute([$new_file_name, $file_name, $file_ext, $file_hash])) {
                                // project_id  date_created    project_owner_id    project_title   project_description priority    project_image_path  

                                if (isset($_POST['project_owner_id']) && isset($_POST['project_title']) && isset($_POST['project_description'])) {
                                    if (isset($_POST['priority'])) {
                                        $priority = $_POST['priority'] == 'true';
                                    }
                                    else {
                                        $priority = 0;
                                    }
                                    
                                    if (isset($_POST['due_date']) && !is_null($_POST['due_date']) && $_POST["due_date"] != "null") {
                                        $sql_query = "INSERT INTO `projects` (project_owner_id, project_title, project_description, due_date, priority, project_image_path) VALUES (?, ?, ?, ? ,?, ?);";
                                        $sql_data = [$_POST['project_owner_id'], $_POST['project_title'], $_POST['project_description'], $_POST['due_date'], $priority, $new_file_name . '.' . $file_ext];
                                    }
                                    else {
                                        $sql_query = "INSERT INTO `projects` (project_owner_id, project_title, project_description, priority, project_image_path) VALUES (?, ?, ?, ?, ?);";
                                        $sql_data = [$_POST['project_owner_id'], $_POST['project_title'], $_POST['project_description'], $priority,  $new_file_name . '.' . $file_ext];
                                    }
                                    
                                    $stmt = $conn->prepare($sql_query);

                                    if ($stmt->execute($sql_data)) {
                                        $project_id = $conn->lastInsertId();

                                        if (isset($_POST['share_with'])) {
                                            $stmt = $conn->prepare("SELECT user_id FROM `users` WHERE email = ?;");
                                            $stmt->execute([$_POST['share_with']]);

                                            if ($stmt->rowCount() == 0) {
                                                $conn->rollBack();
                                                die(json_encode([
                                                    'code'=> 'Error: Shared email address does not exist.'
                                                ]));
                                            }
                                            else {
                                                $share_user_id = $stmt->fetchColumn();

                                                if ($_POST['project_owner_id'] != $share_user_id) {
                                                    $stmt = $conn->prepare("INSERT INTO `project_shares` (project_id, project_owner_id, share_user_id, privileges) VALUES (?, ?, ?, ?);");
                                                    if ($stmt->execute([$project_id, $_POST['project_owner_id'], $share_user_id, 2])) {
                                                        if ($conn->commit()) {
                                                            die(json_encode([
                                                                "code" => "Project added and shared successfully."
                                                            ]));
                                                        }
                                                        else {
                                                            $conn->rollBack();
                                                            die(json_encode([
                                                                "code" => "Error: Unable to add the project."
                                                            ]));
                                                        }
                                                    }
                                                }
                                                else {
                                                    $conn->rollBack();
                                                    die(json_encode([
                                                        "code" => "Error: Cannot share the project to oneself"
                                                    ]));
                                                }
                                            }
                                        }
                                        else {
                                            if ($conn->commit()) {
                                                die(json_encode([
                                                    "code" => "Project added successfully."
                                                ]));
                                            }
                                            else {
                                                $conn->rollBack();
                                                die(json_encode([
                                                    "code" => "Error: Unable to add the project."
                                                ]));
                                            }
                                        }
                                    }
                                    else {
                                        $conn->rollBack();
                                        die(json_encode([
                                            "code" => "Error: Unable to add project to the database."
                                        ]));
                                    }
                                }
                                else {
                                    $conn->rollBack();

                                    die(json_encode([
                                        "code" => "Error: Missing project details."
                                    ]));
                                }
                            }
                            else {
                                $conn->rollBack();
                                die(json_encode([
                                    "code"=> "Error: Unable to add file to the database."
                                ]));
                            }

                        }
                    }
                    catch (PDOException $e) {
                        die(json_encode([
                            "code" => "Error: Unable to process uploaded file due to database error. " . $e->getMessage()
                        ]));
                    }
                    catch (Exception $e) {
                        die(json_encode([
                            "code"=> "Error: Unable to process uploaded file due to a backend error: " . $e->getMessage()
                        ]));
                    }
                }
                else {
                    die(json_encode([
                        "code"=> "Unable to hash uploaded file."
                    ]));
                }
            }
            else {
                die(json_encode([
                    "code" => "Error: File upload failed."
                ]));
            }
        }
    }
    catch (Exception $e) {
        die(json_encode([
            "code" => "Error: An error occurred while uploading your file. " . $e->getMessage()
        ]));
    }
}

?>