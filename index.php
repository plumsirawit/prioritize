<?php
    session_start();
    if(isset($_SESSION['user_id'])){
        header("Location: board.php");
        die();
    }
?>
<!DOCTYPE html>
<html>
<head>
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Import materialize.css-->
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link type="text/css" rel="stylesheet" href="index.css" />
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var elems = document.querySelectorAll('.modal');
        var instances = M.Modal.init(elems, {});
    });
    var showModal = function(ident) {
        if(ident == 'register'){
            var instance = M.Modal.getInstance(document.getElementById("modal-register"));
            instance.open();
        }else if(ident == 'login'){
            var instance = M.Modal.getInstance(document.getElementById("modal-login"));
            instance.open();
        }
    }
    const call = async (url, data) => {
        const response = await fetch(url, {
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        });
        const ret = await response.json();
        if(ret.hasOwnProperty('status') && ret['status'] == 'OK'){
            var text = document.getElementById("modal-error-header");
            text.innerHTML = "Success";
            var text = document.getElementById("modal-error-text");
            text.innerHTML = ret['output'];
            M.Modal.getInstance(document.getElementById("modal-register")).close();
            M.Modal.getInstance(document.getElementById("modal-login")).close();
            M.Modal.getInstance(document.getElementById("modal-error")).open();
            document.getElementById("modal-error-button").onclick = function() {
                window.location.replace("index.php");
            };
        }else if(ret.hasOwnProperty('error')){
            var text = document.getElementById("modal-error-text");
            text.innerHTML = ret['error'];
            M.Modal.getInstance(document.getElementById("modal-error")).open();
        }
    }
    var login = function(){
        M.Modal.getInstance(document.getElementById("modal-login")).close();
        var username = document.getElementById("login-username").value;
        var password = document.getElementById("login-password").value;
        call("api.php",{command: "authenticate", username: username, password: password});
    }
    var register = function(){
        M.Modal.getInstance(document.getElementById("modal-register")).close();
        var username = document.getElementById("register-username").value;
        var password = document.getElementById("register-password").value;
        var password_confirm = document.getElementById("register-password-confirm").value;
        if(password !== password_confirm){
            var text = document.getElementById("modal-error-text");
            text.innerHTML = "Password does not match with confirmation";
            M.Modal.getInstance(document.getElementById("modal-error")).open();
            return;
        }
        if(password.length < 8){
            var text = document.getElementById("modal-error-text");
            text.innerHTML = "Password length must be at least 8 characters";
            M.Modal.getInstance(document.getElementById("modal-error")).open();
            return;
        }
        if(username.length < 2){
            var text = document.getElementById("modal-error-text");
            text.innerHTML = "Username must be at least 2 characters";
            M.Modal.getInstance(document.getElementById("modal-error")).open();
            return;
        }
        if(username.length > 64){
            var text = document.getElementById("modal-error-text");
            text.innerHTML = "Username must not exceed 64 characters";
            M.Modal.getInstance(document.getElementById("modal-error")).open();
            return;
        }
        call("api.php",{command: "register", username: username, password: password});
    }
    </script>
</head>

<body class="deep-orange">
    <div id="modal-error" class="modal">
        <div class="modal-content">
            <h4 id="modal-error-header">Error</h4>
            <p id="modal-error-text"></p>
        </div>
        <div class="modal-footer">
            <a class="modal-close waves-effect waves-green btn-flat" id="modal-error-button">OK</a>
        </div>
    </div>
    <div id="modal-register" class="modal">
        <form id="form-register" action="api.php" method="post">
            <div class="modal-content">
                <h4>Register</h4>
                <div class="row">
                    <div class="col s12">
                        <div class="row modal-form-row">
                            <div class="input-field col s12">
                                <input id="register-username" type="text" class="validate">
                                <label id="register-username-label" for="register-username">Username</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <input id="register-password" type="password" class="validate">
                                <label id="register-password-label" for="register-password">Password</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <input id="register-password-confirm" type="password" class="validate">
                                <label id="register-password-confirm-label" for="register-password-confirm">Password Confirmation</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a class="waves-effect waves-green btn-flat" onclick="register()">Submit</a>
            </div>
        </form>
    </div>
    <div id="modal-login" class="modal">
        <form id="form-login" action="api.php" method="post">
            <div class="modal-content">
                <h4>Login</h4>
                <div class="row">
                    <div class="col s12">
                        <div class="row modal-form-row">
                            <div class="input-field col s12">
                                <input id="login-username" type="text" class="validate">
                                <label id="login-username-label" for="login-username">Username</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <input id="login-password" type="password" class="validate">
                                <label id="login-password-label" for="login-password">Password</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a class="waves-effect waves-green btn-flat" onclick="login()">Submit</a>
            </div>
        </form>
    </div>
    <div class="box card">
        <h2 style="font-weight: bold">Welcome to Prioritize!</h2>
        <div class="twoform">
            <div id="regisfork">
                <button id="regisbtn" class="white-text waves-effect waves-light blue accent-3 btn-large forkbtn" onclick=showModal(&quot;register&quot;)>Register</button>
            </div>
            <div id="loginfork">
                <button id="loginbtn" class="black-text waves-effect waves-light green accent-3 btn-large forkbtn" onclick=showModal(&quot;login&quot;)>Login</button>
            </div>
        </div>
    </div>
    <!--JavaScript at end of body for optimized loading-->
    <script type="text/javascript" src="js/materialize.min.js"></script>
</body>
</html>
