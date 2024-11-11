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
        try {
            $conn = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
            // set the PDO error mode to exception to enable error reporting
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // prevent the PDO from using emulated prepared statements to reduce SQL injection risk
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            if ($conn->beginTransaction()) {
                //  && isset($_POST['previous_revision_id']) for multi-version revert
                if (isset($_POST['asset_revision_id'])) {
                    $stmt = $conn->prepare("SELECT revisions.revision_id, revisions.previous_revision_id, files.* FROM `revisions` INNER JOIN files ON revisions.revision_id_file_path = files.file_id WHERE revisions.revision_id = (SELECT revisions.previous_revision_id FROM `revisions` INNER JOIN assets ON revisions.revision_id = assets.latest_revision AND assets.asset_id = ?);");
                    
                    if ($stmt->execute([$_POST['asset_revision_id']])) {
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($result) {
                            $stmt = $conn->prepare("UPDATE `assets` SET date_modified = CURRENT_TIMESTAMP(), latest_revision = ?, latest_revision_file_path = ? WHERE asset_id = ?;");
                            if ($stmt->execute([$result['revision_id'], $result['file_id'], $_POST['asset_revision_id']])) {
                                if ($conn->commit()) {
                                    echo json_encode([
                                        "code" => "Asset reverted to the previous version successfully."
                                    ]);
                                }
                                else {
                                    die(json_encode([
                                        "code" => "Error: Unable to revert the asset version. Please try again."
                                    ]));
                                }
                            }
                            else {
                                $conn->rollBack();
                                die(json_encode([
                                    "code" => "Error: Unable to revert the asset version. Please try again."
                                ]));
                            }
                        }
                        else {
                            $conn->rollBack();
                            die(json_encode([
                                "code" => "Nothing to revert."
                            ]));
                        }
                    }
                    else {
                        $conn->rollBack();
                        die(json_encode([
                            "code" => "Error: Unable to revert the asset version. Please try again."
                        ]));
                    }
                }
                else {
                    $conn->rollBack();
                    die(json_encode([
                        "code" => "Error: Missing version details. Please try again."
                    ]));
                }
            }
        }
        catch (PDOException $e) {
            die(json_encode([
                "code" => "Error: Unable to revert the asset version. Please try again."
            ]));
        }
        catch (Exception $e) {
            die(json_encode([
                "code"=> "Error: Unable to revert the asset version. Please try again."
            ]));
        }
    }
    catch (Exception $e) {
        die(json_encode([
            "code" => "Error: An error occurred while reverting your asset."
        ]));
    }
}

?>