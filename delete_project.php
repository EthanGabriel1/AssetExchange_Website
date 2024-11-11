<?php

require_once 'config_assetexchange.php';

define('APP_PUBLIC_DIR', __DIR__. '/filegator/repository/user/');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] == 'delete_project') {
            try {
                $conn = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
                // set the PDO error mode to exception to enable error reporting
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // prevent the PDO from using emulated prepared statements to reduce SQL injection risk
                $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                if ($conn->beginTransaction()) {
                    if (isset($_POST['project_id'])) {
                        
                        // Check if the username already exists with a prepared statement
                        try {
                            $stmt = $conn->prepare("SELECT * FROM `projects` WHERE project_id = ?;");
                            $stmt->execute([$_POST['project_id']]);
        
                             // Executes if user doesn't exist
                            if ($stmt->rowCount() == 0) {
                                die(json_encode([
                                    'code'=> 'Error: Project does not exist.'
                                ]));
                            }
                        }
                        catch (PDOException $e) {
                            die(json_encode([
                                'code'=> 'Error: Project cannot be found.'
                            ]));
                        }
                        catch (Exception $e) {
                            die(json_encode([
                                'code'=> 'Error: Project cannot be found.'
                            ]));
                        }
                        
                        $stmt = $conn->prepare("DELETE FROM `projects` WHERE project_id = ?;");
                        
                        if ($stmt->execute([$_POST["project_id"]])) {
                            if ($conn->commit()) {
                                die(json_encode([
                                    "code" => "Project deleted successfully."
                                ]));
                            }
                            else {
                                $conn->rollBack();
                                die(json_encode([
                                    "code" => "Error: Unable to delete project."
                                ]));
                            }
                        }
                        else {
                            $conn->rollBack();
                            die(json_encode([
                                "code" => "Error: Unable to delete project."
                            ]));
                        }
                    }
                    else {
                        $conn->rollBack();
                        die(json_encode([
                            "code" => "Error: Missing project ID."
                        ]));
                    }
                }
                else {
                    die(json_encode([
                        "code" => "Error: Unable to delete project."
                    ]));
                }
            }
            catch (PDOException $e) {
                die(json_encode([
                    "code" => "Error: Unable to delete project."
                ]));
            }
            catch (Exception $e) {
                die(json_encode([
                    "code"=> "Error: Unable to delete project."
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
            "code" => "Error: Unable to delete project."
        ]));
    }
}

?>