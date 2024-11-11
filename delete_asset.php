<?php

require_once 'config_assetexchange.php';

define('APP_PUBLIC_DIR', __DIR__. '/filegator/repository/user/');

$MAX_FILE_SIZE = 40; // 40 MB

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] == 'delete_asset') {
            try {
                $conn = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
                // set the PDO error mode to exception to enable error reporting
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // prevent the PDO from using emulated prepared statements to reduce SQL injection risk
                $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                if ($conn->beginTransaction()) {
                    if (isset($_POST['asset_id'])) {
                        $stmt = $conn->prepare("DELETE FROM `assets` WHERE asset_id = ?;");
                        
                        if ($stmt->execute([$_POST["asset_id"]])) {
                            if ($conn->commit()) {
                                die(json_encode([
                                    "code" => "Asset deleted successfully."
                                ]));
                            }
                            else {
                                $conn->rollBack();
                                die(json_encode([
                                    "code" => "Error: Unable to delete asset."
                                ]));
                            }
                        }
                        else {
                            $conn->rollBack();
                            die(json_encode([
                                "code" => "Error: Unable to delete asset."
                            ]));
                        }
                    }
                    else {
                        $conn->rollBack();
                        die(json_encode([
                            "code" => "Error: Missing asset ID."
                        ]));
                    }
                }
                else {
                    die(json_encode([
                        "code" => "Error: Unable to delete asset."
                    ]));
                }
            }
            catch (PDOException $e) {
                die(json_encode([
                    "code" => "Error: Unable to delete asset."
                ]));
            }
            catch (Exception $e) {
                die(json_encode([
                    "code"=> "Error: Unable to delete asset."
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
            "code" => "Error: Unable to delete asset."
        ]));
    }
}

?>