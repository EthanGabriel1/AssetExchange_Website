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
        if (isset($_POST['action']) && $_POST['action'] == 'add_reminder') {
            try {
                $conn = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
                // set the PDO error mode to exception to enable error reporting
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // prevent the PDO from using emulated prepared statements to reduce SQL injection risk
                $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                if ($conn->beginTransaction()) {
                    if (isset($_POST['task_owner_id']) && isset($_POST['priority']) && isset($_POST['task_title'])) {
                        $stmt = $conn->prepare("INSERT INTO `tasks` (task_title, task_description, task_owner_id, priority, due_date) VALUES (:task_title, :task_description, :task_owner_id, :priority, :due_date);");

                        $due_date = null;
                        if (isset($_POST["due_date"])) {
                            $due_date = $_POST["due_date"];
                        }

                        if ($stmt->execute([
                                ":task_title" => $_POST["task_title"],
                                ":task_description" => $_POST["task_description"],
                                ":task_owner_id" => $_POST["task_owner_id"],
                                ":priority" => ($_POST["priority"] == 'true' ? 1 : 0),
                                ":due_date" => $due_date
                            ])) {
                            if ($conn->commit()) {
                                die(json_encode([
                                    "code" => "Reminder added successfully."
                                ]));
                            }
                            else {
                                $conn->rollBack();
                                die(json_encode([
                                    "code" => "Error: Unable to add reminder."
                                ]));
                            }
                        }
                        else {
                            $conn->rollBack();
                            die(json_encode([
                                "code" => "Error: Unable to add reminder."
                            ]));
                        }
                    }
                    else {
                        $conn->rollBack();
                        die(json_encode([
                            "code" => "Error: Missing reminder details."
                        ]));
                    }
                }
                else {
                    die(json_encode([
                        "code" => "Error: Unable to add reminder."
                    ]));
                }
            }
            catch (PDOException $e) {
                die(json_encode([
                    "code" => "Error: Unable to add reminder."
                ]));
            }
            catch (Exception $e) {
                die(json_encode([
                    "code"=> "Error: Unable to add reminder."
                ]));
            }
        }
        else {
            die(json_encode([
                "code" => "Error: Action not supported"
            ]));
        }
    }
    catch (Exception $e) {
        die(json_encode([
            "code" => "Error: Unable to add reminder."
        ]));
    }
}

?>