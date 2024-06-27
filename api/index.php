<?php
ob_start();
ini_set('session.gc_maxlifetime', 300);
session_start();

require 'http.php';
require 'oauth_client.php';

$client = new OAuth_Client_Class;
$client->redirect_uri  = 'http://localhost/newlogin/api/index.php';
$client->client_id     = '01hyqp54anefh9pam4vcbx5vkm';
$client->client_secret = 'P76WfAhkJreYu_4VGRtwbf_O_d6m6RiBhcNi_Zd_fcuRSEhnmTUyu8ZOukBSABqIv5_9iXvwT2qD7-N4';

$client->debug = 1;
$client->debug_http = 0;

$api_url = 'https://student.sbhs.net.au/api/';
$client->oauth_version = '2.0';
$client->dialog_url = 'https://student.sbhs.net.au/api/authorize?response_type=code&client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}&scope={SCOPE}&state={STATE}';
$client->access_token_url = 'https://student.sbhs.net.au/api/token';

$apiResponse = '';
if (($success = $client->Initialize())) {
    if (($success = $client->Process())) {
        if ($client->access_token) {
            $function = 'details/userinfo.json';
            $success = $client->CallAPI($api_url . $function,
                                        'GET', array(),
                                        array(
                                            'FailOnAccessError' => true,
                                            'ResponseContentType' => 'unspecified'),
                                        $apiResponse);

            if (!$success) {
                if ($client->response_status == 401) {
                    $client->ResetAccessToken();
                }
            }
        }
    }
    $client->Finalize($success);
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: home.html');
    exit;
}

if ($client->exit) {
    if ($client->debug) {
        echo $client->debug_output;
    }
    exit;
}

if ($success) {
    $decodedResponse = json_decode($apiResponse);
    $studentData = [
        'username' => $decodedResponse->username,
        'studentId' => $decodedResponse->studentId,
        'givenName' => $decodedResponse->givenName,
        'surname' => $decodedResponse->surname,
        'rollClass' => $decodedResponse->rollClass,
        'yearGroup' => $decodedResponse->yearGroup,
        'role' => $decodedResponse->role,
        'department' => $decodedResponse->department,
        'office' => $decodedResponse->office,
        'email' => $decodedResponse->email,
        'emailAliases' => $decodedResponse->emailAliases,
        'decEmail' => $decodedResponse->decEmail,
        'groups' => $decodedResponse->groups
    ];

    $studentJSON = json_encode($studentData);

    echo '<script>';
    echo 'localStorage.setItem("studentData", \'' . addslashes($studentJSON) . '\');';
    echo 'window.location.href = "../profile.html";';
    echo '</script>';
}

ob_end_flush();
?>
