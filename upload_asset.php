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
                            if (isset($_POST['project_id']) && isset($_POST['asset_title']) && isset($_POST['asset_description'])) {
                                $stmt = $conn->prepare("INSERT INTO `assets` (asset_title, asset_description, latest_revision_file_path) VALUES (?, ?, ?);");
                                if ($stmt->execute([$_POST['asset_title'], $_POST['asset_description'], $new_file_name])) {
                                    $stmt = $conn->prepare("SELECT * FROM `assets` WHERE latest_revision_file_path = ?;");
                                    if ($stmt->execute([$new_file_name])) {
                                        $asset = $stmt->fetch(PDO::FETCH_ASSOC);
                                        if ($asset) {
                                            
                                            $stmt = $conn->prepare("INSERT INTO `revisions` (revision_id, revision_id_file_path, asset_revision_id, revision_status, comment) VALUES (?, ?, ?, ?, ?);");
                                            $revision_id = generateUuidV4();
                                            
                                            if ($stmt->execute([$revision_id, $new_file_name, $asset["asset_id"], 1, "Initial version"])) {
                                                $stmt = $conn->prepare("UPDATE `assets` SET latest_revision = ? WHERE asset_id = ?;");
                                                if ($stmt->execute([$revision_id, $asset["asset_id"]])) {
                                                    $stmt = $conn->prepare("INSERT INTO `project_assets` (asset_id, project_id) VALUES (?, ?);");
                                                    if ($stmt->execute([$asset["asset_id"], $_POST['project_id']])) {
                                                        if ($conn->commit()) {
                                                            echo json_encode([
                                                                "code" => "The asset has been added to the project."
                                                            ]);
                                                        }
                                                        else {
                                                            die(json_encode([
                                                                "code" => "Error: Unable to add the asset. Please try again."
                                                            ]));
                                                        }
                                                    }
                                                    else {
                                                        $conn->rollBack();
                                                        die(json_encode([
                                                            "code" => "Error: Unable to add the asset. Please try again."
                                                        ]));
                                                    }
                                                }
                                                else {
                                                    $conn->rollBack();
                                                    die(json_encode([
                                                        "code" => "Error: Unable to add the asset. Please try again."
                                                    ]));
                                                }
                                            }
                                            else {
                                                $conn->rollBack();
                                                die(json_encode([
                                                    "code" => "Error: Unable to add the asset. Please try again."
                                                ]));
                                            }
                                        }
                                        else {
                                            $conn->rollBack();
                                            die(json_encode([
                                                "code" => "Error: Unable to add the asset. Please try again."
                                            ]));
                                        }
                                    }
                                    else {
                                        $conn->rollBack();
                                        die(json_encode([
                                            "code" => "Error: Unable to add the asset. Please try again."
                                        ]));
                                    }
                                }
                                else {
                                    $conn->rollBack();
                                    die(json_encode([
                                        "code" => "Error: Unable to add the asset. Please try again."
                                    ]));
                                }
                            }
                            else {
                                $conn->rollBack();
                                die(json_encode([
                                    "code" => "Error: Missing asset details. Please try again."
                                ]));
                            }
                        }
                        else {
                            $conn->rollBack();
                            die(json_encode([
                                "code"=> "Error: Unable to add the asset. Please try again."
                            ]));
                        }

                    }
                }
                catch (PDOException $e) {
                    die(json_encode([
                        "code" => "Error: Unable to add the asset. Please try again."
                    ]));
                }
                catch (Exception $e) {
                    die(json_encode([
                        "code"=> "Error: Unable to add the asset. Please try again."
                    ]));
                }
            }
            else {
                die(json_encode([
                    "code"=> "Error: Unable to add the asset. Please try again."
                ]));
            }
        }
        else {
            die(json_encode([
                "code" => "Error: File upload failed. Please try again."
            ]));
        }
    }
    catch (Exception $e) {
        die(json_encode([
            "code" => "Error: An error occurred while uploading your file. "
        ]));
    }
}

?>