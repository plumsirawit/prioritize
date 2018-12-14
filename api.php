<?php
    include_once 'config/config.php';
    /*
    Method List:
    insert
    remove
    get
    edit
    list
    move
    authenticate
    register
    */
    error_reporting(0);
    ini_set('display_errors', 0);
    session_start();
    $response = array();
    if($_SERVER['REQUEST_METHOD'] != 'POST'){
        $response['error'] = 'Invalid request: Method is not POST';
        die(json_encode($response));
    }
    $_POST = json_decode(file_get_contents('php://input'),true);
    if(!isset($_POST['command'])){
        $response['error'] = 'Invalid POST request: no command' . print_r($_POST,true);
        die(json_encode($response));
    }
    $conn = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
    if(mysqli_connect_error()){
        $response['error'] = mysqli_connect_error();
        die(json_encode($response));
    }
    if($_POST['command'] == 'insert'){
        if(!isset($_SESSION['user_id'])){
            $response['error'] = 'Unauthorized Access';
            die(json_encode($response));
        }
        if(!isset($_POST['title']) || $_POST['title'] == ""){
            $response['error'] = 'Invalid POST request: no title';
            die(json_encode($response));
        }
        if(!isset($_POST['descs'])){
            $response['error'] = 'Invalid POST request: no descs';
            die(json_encode($response));
        }
        if(!isset($_POST['color'])){
            $response['error'] = 'Invalid POST request: no color';
            die(json_encode($response));
        }
        if($stmt = $conn->prepare("SELECT id FROM tasks WHERE title = ? AND user_id = ?")){
            $stmt->bind_param("sd",$_POST['title'],$_SESSION['user_id']);
            $stmt->execute();
            $stmt->bind_result($res);
            if($stmt->fetch()){
                $response['error'] = 'Invalid title: title has already been used';
                die(json_encode($response));
            }
            $stmt->close();
            if($stmt = $conn->prepare("INSERT INTO tasks (user_id,title,descs,color) VALUES (?,?,?,?)")){
                $stmt->bind_param("isss",$_SESSION['user_id'],$_POST['title'],$_POST['descs'],$_POST['color']);
                $stmt->execute();
                if($stmt->affected_rows){
                    $response['status'] = 'OK';
                    $response['output'] = 'Query completed: ' . $stmt->affected_rows . ' rows affected';
                    die(json_encode($response));
                }else{
                    $response['error'] = 'MySQL insertion error';
                    die(json_encode($response));
                }
            }else{
                $response['error'] = 'Cannot prepare MySQL statement (Insert)';
                die(json_encode($response));
            }
        }else{
            $response['error'] = 'Cannot prepare MySQL statement (Check Insert Duplication)';
            die(json_encode($response));
        }
    }else if($_POST['command'] == 'remove'){
        if(!isset($_SESSION['user_id'])){
            $response['error'] = 'Unauthorized Access';
            die(json_encode($response));
        }
        if(!isset($_POST['title'])){
            $response['error'] = 'Invalid POST request: no title';
            die(json_encode($response));
        }
        if($stmt = $conn->prepare("SELECT id FROM tasks WHERE title = ? AND user_id = ?")){
            $stmt->bind_param("si",$_POST['title'],$_SESSION['user_id']);
            $stmt->execute();
            $stmt->bind_result($res);
            if($stmt->fetch()){
                $stmt->close();
                if($stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?")){
                    $stmt->bind_param("s",$res);
                    $stmt->execute();
                    if($stmt->affected_rows){
                        $response['status'] = 'OK';
                        $response['output'] = 'Query completed: ' . $stmt->affected_rows . ' rows affected';
                        die(json_encode($response));
                    }else{
                        $response['error'] = 'MySQL deletion error';
                        die(json_encode($response));
                    }
                }else{
                    $response['error'] = 'Cannot prepare MySQL statement (Delete Tasks)';
                    die(json_encode($response));
                }
            }else{
                $response['error'] = 'No tasks found';
                die(json_encode($response));
            }
        }else{
            $response['error'] = 'Cannot prepare MySQL statement (Fetch Tasks on Removal)';
            die(json_encode($response));
        }
    }else if($_POST['command'] == 'get'){
        if(!isset($_SESSION['user_id'])){
            $response['error'] = 'Unauthorized Access';
            die(json_encode($response));
        }
        if(!isset($_POST['id'])){
            $response['error'] = 'Invalid POST request: no id';
            die(json_encode($response));
        }
        if($stmt = $conn->prepare("SELECT title, descs, color FROM tasks WHERE id = ? AND user_id = ? AND completed = FALSE")){
            $stmt->bind_param("ii",$_POST['id'],$_SESSION['user_id']);
            $stmt->execute();
            $stmt->bind_result($title, $descs, $color);
            if($stmt->fetch()){
                $response['status'] = 'OK';
                $response['title'] = $title;
                $response['descs'] = $descs;
                $response['color'] = $color;
                $stmt->close();
                $response['output'] = 'Successfully received data';
                die(json_encode($response));
            }else{
                $response['error'] = 'No result found (Get item)';
                die(json_encode($response));
            }
        }else{
            $response['error'] = 'Cannot prepare MySQL statement (Get info)';
            die(json_encode($response));
        }
    }else if($_POST['command'] == 'edit'){
        if(!isset($_SESSION['user_id'])){
            $response['error'] = 'Unauthorized Access';
            die(json_encode($response));
        }
        if(!isset($_POST['title'])){
            $response['error'] = 'Invalid POST request: no title';
            die(json_encode($response));
        }
        if(!isset($_POST['descs'])){
            $response['error'] = 'Invalid POST request: no descs';
            die(json_encode($response));
        }
        if(!isset($_POST['color'])){
            $response['error'] = 'Invalid POST request: no color';
            die(json_encode($response));
        }
        if(!isset($_POST['id'])){
            $response['error'] = 'Invalid POST request: no id';
            die(json_encode($response));
        }
        if($stmt = $conn->prepare("UPDATE tasks SET title = ?, descs = ?, color = ?, completed = ? WHERE id = ? AND user_id = ?")){
            $stmt->bind_param("sssiii",$_POST['title'],$_POST['descs'],$_POST['color'],$_POST['completed'],$_POST['id'],$_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
            $response['status'] = 'OK';
            $response['output'] = 'Data have been edited';
            die(json_encode($response));
        }else{
            $response['error'] = 'Cannot prepare MySQL statement (Edit)';
            die(json_encode($response));
        }
    }else if($_POST['command'] == 'list'){
        if(!isset($_SESSION['user_id'])){
            $response['error'] = 'Unauthorized Access';
            die(json_encode($response));
        }
        if($stmt = $conn->prepare("SELECT id, x, y, color, title FROM tasks WHERE user_id = ? AND completed = FALSE")){
            $stmt->bind_param("i",$_SESSION['user_id']);
            $stmt->execute();
            $stmt->bind_result($id, $x, $y, $color, $title);
            $ret = array();
            while($stmt->fetch()){
                $ret []= array($id, $x, $y, $color, $title);
            }
            $stmt->close();
            $response['status'] = 'OK';
            $response['output'] = json_encode($ret);
            die(json_encode($response));
        }else{
            $response['error'] = 'Cannot prepare MySQL statement (List)';
            die(json_encode($response));
        }
    }else if($_POST['command'] == 'move'){
        if(!isset($_SESSION['user_id'])){
            $response['error'] = 'Unauthorized Access';
            die(json_encode($response));
        }
        if(!isset($_POST['id'])){
            $response['error'] = 'Invalid POST request: Circle id not found';
            die(json_encode($response));
        }
        if(!isset($_POST['x'])){
            $response['error'] = 'Invalid POST request: x location not found';
            die(json_encode($response));
        }
        if(!isset($_POST['y'])){
            $response['error'] = 'Invalid POST request: y location not found';
            die(json_encode($response));
        }
        if($stmt = $conn->prepare("SELECT x,y FROM tasks WHERE id = ?")){
            $stmt->bind_param("i", $_POST['id']);
            $stmt->execute();
            $stmt->bind_result($x,$y);
            if($stmt->fetch()){
                $stmt->close();
                if($stmt = $conn->prepare("UPDATE tasks SET x = ?, y = ? WHERE id = ?")){
                    $stmt->bind_param("ddi", $_POST['x'], $_POST['y'], $_POST['id']);
                    $stmt->execute();
                    if($stmt->affected_rows){
                        $response['status'] = 'OK';
                        $response['output'] = 'Query completed: ' . $stmt->affected_rows . ' rows affected';
                        die(json_encode($response));
                    }else{
                        $response['error'] = 'Cannot update (Move)';
                        die(json_encode($response));
                    }
                }else{
                    $response['error'] = 'Cannot prepare MySQL statement (Move)';
                    die(json_encode($response));
                }
            }else{
                $response['error'] = 'Circle of given id not found';
                die(json_encode($response));
            }
        }else{
            $response['error'] = 'Cannot prepare MySQL statement (Circle Validity Checking on Move)';
            die(json_encode($response));
        }
    }else if($_POST['command'] == 'authenticate'){
        if(!isset($_POST['username'])){
            $response['error'] = 'Invalid POST request: no username';
            die(json_encode($response));
        }
        if(!isset($_POST['password'])){
            $response['error'] = 'Invalid POST request: no password';
            die(json_encode($response));
        }
        if($stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?")){
            $stmt->bind_param("s",$_POST['username']);
            $stmt->execute();
            $stmt->bind_result($uid,$res);
            if($stmt->fetch()){
                if(password_verify($_POST['password'], $res)){
                    $_SESSION['user_id'] = $uid;
                    $stmt->close();
                    $response['status'] = 'OK';
                    $response['output'] = 'Login Successful';
                    die(json_encode($response));
                }else{
                    $response['error'] = 'Access Denied (Wrong username or password)';
                    die(json_encode($response));
                }
            }else{
                $response['error'] = 'Access Denied (Wrong username or password)';
                die(json_encode($response));
            }
        }else{
            $response['error'] = 'Cannot prepare MySQL statement (Authentication)';
            die(json_encode($response));
        }
    }else if($_POST['command'] == 'register'){
        if(!isset($_POST['username'])){
            $response['error'] = 'Invalid POST request: no username';
            die(json_encode($response));
        }
        if(!isset($_POST['password'])){
            $response['error'] = 'Invalid POST request: no password';
            die(json_encode($response));
        }
        if($stmt = $conn->prepare("SELECT id FROM users WHERE username = ?")){
            $stmt->bind_param("s",$_POST['username']);
            $stmt->execute();
            $stmt->bind_result($res);
            if($stmt->fetch()){
                $response['error'] = 'Invalid username: username already has been registered';
                die(json_encode($response));
            }else{
                $stmt->close();
                if($stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?,?)")){
                    $stmt->bind_param("ss",$_POST['username'],password_hash($_POST['password'],PASSWORD_DEFAULT));
                    $stmt->execute();
                    if($stmt->affected_rows){
                        $response['status'] = 'OK';
                        $response['output'] = 'Registration Completed';
                        die(json_encode($response));
                    }else{
                        $response['error'] = 'Cannot insert user (Registration)';
                        die(json_encode($response));
                    }
                }else{
                    $response['error'] = 'Cannot prepare MySQL statement (Registration)';
                    die(json_encode($response));
                }
            }
        }else{
            $response['error'] = 'Cannot prepare MySQL statement (Check Register Duplication)';
            die(json_encode($response));
        }
    }else{
        $response['error'] = 'Invalid Command: \"'. $_POST['command'] . '\" not found.';
        die(json_encode($response));
    }
    $conn->close();
?>
