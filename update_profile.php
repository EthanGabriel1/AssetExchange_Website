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
            if (isset($_POST['action']) && $_POST['action'] == 'update_profile_data') {
                try {
                    $conn = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
                    // set the PDO error mode to exception to enable error reporting
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    // prevent the PDO from using emulated prepared statements to reduce SQL injection risk
                    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                    if ($conn->beginTransaction()) {
                        if (isset($_POST['user_id']) && isset($_POST['full_name'])) {
                            $stmt = $conn->prepare("UPDATE `users` SET full_name = ? WHERE user_id = ?;");

                            if ($stmt->execute([$_POST['full_name'], $_POST['user_id']])) {
                                if ($conn->commit()) {
                                    die(json_encode([
                                        "code" => "Profile updated successfully."
                                    ]));
                                }
                                else {
                                    $conn->rollBack();
                                    die(json_encode([
                                        "code" => "Error: Unable to update profile."
                                    ]));
                                }
                            }
                            else {
                                $conn->rollBack();
                                die(json_encode([
                                    "code" => "Error: Unable to update profile in the database."
                                ]));
                            }
                        }
                        else {
                            $conn->rollBack();
                            die(json_encode([
                                "code" => "Error: Missing profile details."
                            ]));
                        }
                    }
                    else {
                        die(json_encode([
                            "code" => "Error: Unable to update profile due to database error."
                        ]));
                    }
                }
                catch (PDOException $e) {
                    die(json_encode([
                        "code" => "Error: Unable to update profile due to database error. " . $e->getMessage()
                    ]));
                }
                catch (Exception $e) {
                    die(json_encode([
                        "code"=> "Error: Unable to update profile due to a backend error: " . $e->getMessage()
                    ]));
                }
            }
            else {
                die(json_encode([
                    "code" => "Error: Action not supported"
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


                                if (isset($_POST['user_id'])) {
                                    $stmt = $conn->prepare("UPDATE `users` SET profile_pic_path = ? WHERE user_id = ?;");

                                    if ($stmt->execute([$new_file_name . "." . $file_ext, $_POST['user_id']])) {
                                        if (isset($_POST['full_name'])) {
                                            $stmt = $conn->prepare("UPDATE `users` SET full_name = ? WHERE user_id = ?;");
                                            if ($stmt->execute([$_POST['full_name'], $_POST['user_id']])) {
                                                if ($conn->commit()) {
                                                    die(json_encode([
                                                        "code" => "Profile updated successfully."
                                                    ]));
                                                }
                                                else {
                                                    $conn->rollBack();
                                                    die(json_encode([
                                                        "code" => "Error: Unable to update profile."
                                                    ]));
                                                }
                                            }
                                            else {
                                                $conn->rollBack();
                                                die(json_encode([
                                                    "code" => "Error: Unable to update profile in the database."
                                                ]));
                                            }
                                        }
                                        else {
                                            if ($conn->commit()) {
                                                die(json_encode([
                                                    "code" => "Profile updated successfully."
                                                ]));
                                            }
                                            else {
                                                $conn->rollBack();
                                                die(json_encode([
                                                    "code" => "Error: Unable to update profile."
                                                ]));
                                            }
                                        }
                                    }
                                    else {
                                        $conn->rollBack();
                                        die(json_encode([
                                            "code" => "Error: Unable to update profile in the database."
                                        ]));
                                    }
                                }
                                else {
                                    $conn->rollBack();

                                    die(json_encode([
                                        "code" => "Error: Missing user ID."
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