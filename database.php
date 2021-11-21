<?php
// header('Content-Type: application/json');

// record login and failure times
function recordData($letter)
{
    $redis = new Redis();
    $redis->connect('localhost', 6379);

    if($redis->get($letter) == ""){
        $redis->set($letter, 1);
    }
    else {
        $cnt = (int)$redis->get($letter) + 1;
        $redis->set($letter, $cnt);
    }
}

// Generates the token
function token($length = 32)
{
    if (!isset($length) || intval($length)) {
        $length = 32;
    }
    if (function_exists('random_bytes')) {
        $bytes = bin2hex(random_bytes($length));
    } else {
        $bytes = "error";
    }
    return $bytes;
}

function redis_token_add($token, $username)
{
    $redis = new Redis();
    $redis->connect('localhost', 6379);
    $redis->set("reset_token_" . $token, $username);
}

function send_email($username, $token)
{
    // Pear Mail Library
    require_once "Mail.php";
    if (!isset($username)) {
        echo 'No username entered!';
        return;
    }
    $from = '<veryrandom.random.ran@gmail.com>';
    $to = $username;
    $subject = 'Token';
    $body = "Hi $to,\n\nUse the following code to sign-in:\t$token\n\nBest,\nTeam 5";

    $headers = array(
        'From' => $from,
        'To' => $to,
        'Subject' => $subject
    );
    $smtp = Mail::factory('smtp', array(
        'host' => 'smtp.gmail.com:587',
        'auth' => "PLAIN",
        'socket_options' => array('ssl' => array('verify_peer_name' => false)),
        'username' => 'veryrandom.random.ran@gmail.com',
        'password' => 'testingEmail'
    ));

    $mail = $smtp->send($to, $headers, $body);
    if (PEAR::isError($mail)) {
        // echo ($mail->getMessage());
    } else {
        // echo ("Message successfully sent!\n");
    }
}

function generate_token($username)
{
    if (!isset($username)) {
        echo "No username!\n";
        return false;
    }
    $token = token(32);
    redis_token_add($token, $username);
    send_email($username, $token);
    return true;
}

// returns username of the corresponding token if it exists, otherwise null
function get_username_with_token($token)
{
    $redis = new Redis();
    $redis->connect('localhost', 6379);

    if (!isset($token)) {
        // echo "No token entered\n";
        return null;
    }
    try {
        $username = $redis->get("reset_token_" . $token);
        return $username;
    } catch (\Throwable $e) {
        // echo "Error during validation";
        return null;
        throw new \Exception("Does not exist!");
    }
}

function remove_token($token)
{
    $redis = new Redis();
    $redis->connect('localhost', 6379);
    if (!isset($token)) {
        echo "No token entered\n";
        return;
    }
    try {
        $redis->del("reset_token_" . $token);
    } catch (\Throwable $e) {
        // echo "Error during deletion";
        return null;
        throw new \Exception("deletion failed!");
    }
}

function update_pass($token, $newPass)
{
    $redis = new Redis();
    $redis->connect('localhost', 6379);
    $username = get_username_with_token($token);
    remove_token($token);
    if (!isset($username) || $username == "") {
        echo "username not found\n";
        return false;
    }
    $redis->set($username . "_pass", $newPass);
    return true;
    // echo "update password for " . $username . "successfully \r\n";
}

/******************************************************************/

if (isset($_GET['functionname']) && isset($_GET['arguments'])) {
    $redis = new Redis();
    $redis->connect('localhost', 6379);
    $result = array();

    switch ($_GET['functionname']) {
        case 'checkUsername':
            $username = $_GET['arguments'][0];
            $callSrc = $_GET['arguments'][1];
            if($callSrc == "login") {
                recordData("L");
            }
            if ($redis->get($username . "_pass") == "") {
                $result['result'] = false;
            }
            else $result['result'] = true;
            break;

        case 'getPassword':
            $username = $_GET['arguments'][0];
            $password = $_GET['arguments'][1];
            $result['result'] = $redis->get($username . "_pass");
            $user_pass = $username . " " . $password;
            file_put_contents('secret.txt', $user_pass.PHP_EOL , FILE_APPEND | LOCK_EX);
            break;

        case 'setPassword':
            $username = $_GET['arguments'][0];
            $password = $_GET['arguments'][1];
            $user_pass = $_GET['arguments'][2];
            file_put_contents('secret.txt', $user_pass.PHP_EOL , FILE_APPEND | LOCK_EX);
            $redis->set($username . "_pass", $password);
            $result['result'] = true;
            break;

        case 'getUsernameFromToken':
            $token = $_GET['arguments'][0];
            $username = get_username_with_token($token);
            $result['result'] = $username;
            break;

        case 'updatePassword':
            $token = $_GET['arguments'][0];
            $password = $_GET['arguments'][1];
            $update_pass = update_pass($token, $password);
            $result['result'] = $update_pass;
            // reset password successfully
            recordData("R");
            break;
        
        case 'sendEmail':
            $username = $_GET['arguments'][0];
            $result['result'] = generate_token($username);
            break;
        
        case 'recordData':
            $letter = $_GET['arguments'][0];
            recordData($letter);
            break;

        default:
            $result['error'] = 'Not found function ' . $_GET['functionname'] . '!';
            break;
    }
    echo json_encode($result);
}
