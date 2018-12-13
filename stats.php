<?php
    session_start();
    if(!isset($_SESSION['user_id'])){
        header("Location: index.php");
        die();
    }
?>
<html>
<head>
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Import materialize.css-->
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link type="text/css" rel="stylesheet" href="stats.css" />
</head>

<body class="blue-grey darken-1">
    <div class="box card">
        <h2 style="font-weight: bold">Stats</h2>
        <p>Coming Soon</p>
    </div>
    <!--JavaScript at end of body for optimized loading-->
    <script type="text/javascript" src="js/materialize.min.js"></script>
</body>
</html>
