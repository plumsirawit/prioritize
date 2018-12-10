<?php
    session_start();
    $response = array();
    if(isset($_SESSION['user_id'])){
        header("Location: board.php");
        die();
    }
    if($_SERVER['REQUEST_METHOD'] != 'POST'){
        $response['error'] = 'Invalid request: Method is not POST';
        die(json_encode($response));
    }
    if(!isset($_POST['username'])){
        $response['error'] = 'Invalid POST request: no username';
        die(json_encode($response));
    }
    if(!isset($_POST['password'])){
        $response['error'] = 'Invalid POST request: no password';
        die(json_encode($response));
    }
    
?>