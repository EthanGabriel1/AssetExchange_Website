<?php

require_once 'config_assetexchange.php';

define('APP_PUBLIC_DIR', __DIR__. '/filegator/repository/user/');

$MAX_FILE_SIZE = 40; // 40 MB

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] == 'add_comment') {
            try {
                $conn = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
                // set the PDO error mode to exception to enable error reporting
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // prevent the PDO from using emulated prepared statements to reduce SQL injection risk
                $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                if ($conn->beginTransaction()) {
                    if (isset($_POST['asset_id']) && isset($_POST['revision_comment_user_id']) && isset($_POST['revision_comment_text'])) {
                        $stmt = $conn->prepare("INSERT INTO `revision_comments` (asset_id, revision_comment_user_id, revision_comment_text) VALUES (?, ?, ?);");
                        
                        if ($stmt->execute([$_POST["asset_id"], $_POST["revision_comment_user_id"], $_POST["revision_comment_text"]])) {
                            if ($conn->commit()) {
                                die(json_encode([
                                    "code" => "Comment added successfully."
                                ]));
                            }
                            else {
                                $conn->rollBack();
                                die(json_encode([
                                    "code" => "Error: Unable to add comment."
                                ]));
                            }
                        }
                        else {
                            $conn->rollBack();
                            die(json_encode([
                                "code" => "Error: Unable to add comment."
                            ]));
                        }
                    }
                    else {
                        $conn->rollBack();
                        die(json_encode([
                            "code" => "Error: Missing comment details."
                        ]));
                    }
                }
                else {
                    die(json_encode([
                        "code" => "Error: Unable to add comment."
                    ]));
                }
            }
            catch (PDOException $e) {
                die(json_encode([
                    "code" => "Error: Unable to add comment."
                ]));
            }
            catch (Exception $e) {
                die(json_encode([
                    "code"=> "Error: Unable to add comment."
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
            "code" => "Error: Unable to add comment."
        ]));
    }
}

?>