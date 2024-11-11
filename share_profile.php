<?php

require_once 'config_assetexchange.php';

define('APP_PUBLIC_DIR', __DIR__. '/filegator/repository/user/');

$MAX_FILE_SIZE = 40; // 40 MB

function generateRandomString() {
    $characters = '23456789abcdefghjkmnpqrstuvwxyz';
    $randomString = '';

    for ($i = 0; $i < 8; $i++) {
        $randomString .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return $randomString;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] == 'share_profile') {
            try {
                $conn = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
                // set the PDO error mode to exception to enable error reporting
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // prevent the PDO from using emulated prepared statements to reduce SQL injection risk
                $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                if ($conn->beginTransaction()) {
                    if (isset($_POST['user_id'])) {
                        $stmt = $conn->prepare("SELECT share_link FROM `profile_shares` WHERE user_id = ?;");
                        if ($stmt->execute([$_POST["user_id"]])) {
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($result) {
                                if ($conn->commit()) {
                                    die(json_encode([
                                        "code" => "Profile has already been shared.",
                                        "share_link" => $result['share_link']
                                    ]));
                                }
                                else {
                                    die(json_encode([
                                        "code" => "Error: Unable to share profile."
                                    ]));
                                }
                            }
                            else {
                                $share_link = generateRandomString();
                                $stmt = $conn->prepare("INSERT INTO `profile_shares` (user_id, share_link) VALUES (?, ?);");
                                
                                if ($stmt->execute([$_POST["user_id"], $share_link])) {
                                    $stmt = $conn->prepare("SELECT share_link FROM `profile_shares` WHERE user_id = ?;");
                                    if ($stmt->execute([$_POST["user_id"]])) {
                                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                        if ($result) {
                                            if ($conn->commit()) {
                                                die(json_encode([
                                                    "code" => "Profile shared successfully.",
                                                    "share_link" => $result['share_link']
                                                ]));
                                            }
                                            else {
                                                die(json_encode([
                                                    "code" => "Error: Unable to share profile."
                                                ]));
                                            }
                                        }
                                        else {
                                            $conn->rollBack();
                                            die(json_encode([
                                                "code" => "Error: Unable to retrieve share link."
                                            ]));
                                        }
                                    }
                                    else {
                                        $conn->rollBack();
                                        die(json_encode([
                                            "code" => "Error: Unable to retrieve share link."
                                        ]));
                                    }
                                }
                                else {
                                    $conn->rollBack();
                                    die(json_encode([
                                        "code" => "Error: Unable to share profile."
                                    ]));
                                }
                            }
                        }
                        else {
                            $conn->rollBack();
                            die(json_encode([
                                "code" => "Error: Unable to access profile data."
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
                    die(json_encode([
                        "code" => "Error: Unable to share profile."
                    ]));
                }
            }
            catch (PDOException $e) {
                die(json_encode([
                    "code" => "Error: Unable to share profile."
                ]));
            }
            catch (Exception $e) {
                die(json_encode([
                    "code"=> "Error: Unable to share profile."
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
            "code" => "Error: Unable to share profile."
        ]));
    }
}

?>