<?php
    session_start();
    if(!isset($_SESSION['user_id'])){
        header("Location: index.php");
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
    <link type="text/css" rel="stylesheet" href="board.css"/>
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <script src="js/jscolor.js"></script>
    <script>
        var change = false;
        var mousePosition;
        var isMouseDown;
        var moved = false;
        var current_id = 0;
        var focused = {
            key: 0,
            state: false
        }
        var circles = [];
        document.addEventListener('DOMContentLoaded', function() {
            var elems = document.querySelectorAll('.modal');
            var instances = M.Modal.init(elems, {});
            var elems = document.querySelectorAll('.fixed-action-btn');
            var instances = M.FloatingActionButton.init(elems, {});
        });
        document.addEventListener('mousemove', function(e) {
            if(!isMouseDown) {
                return;
            }
            moved = true;
            getMousePosition(e);
            if(backscaleX(mousePosition.x) < 0 || backscaleX(mousePosition.x) > 100 || backscaleY(mousePosition.y) < 0 || backscaleY(mousePosition.y) > 100){
                return;
            }
            if(focused.state && focused.key != -1){
                circles[focused.key].x = backscaleX(mousePosition.x);
                circles[focused.key].y = backscaleY(mousePosition.y);
                redraw();
            }
        });
        document.addEventListener('mousedown', function(e) {
            isMouseDown = true;
            getMousePosition(e);
            focused.key = -1;
            for(var i = 0; i < circles.length; i++){
                if(intersects(circles[i])){
                    focused.key = i;
                    break;
                }
            }
            focused.state = true;
            moved = false;
        });
        document.addEventListener('mouseup', function(e) {
            if(focused.key != -1){
                if(!moved){
                    current_id = circles[focused.key].id;
                    call('api.php',{command: 'get', id: circles[focused.key].id});
                    var instance = M.Modal.getInstance(document.getElementById("modal-item"));
                    instance.open();
                }
                call('api.php',{command: 'move', id: circles[focused.key].id, x: circles[focused.key].x, y: circles[focused.key].y});
            }
            moved = false;
        });
        function Circle(id,x,y,r,fill,stroke,title) {
            this.startingAngle = 0;
            this.endAngle = 2 * Math.PI;
            this.x = x;
            this.y = y;
            this.r = r;
            this.id = id;
            this.fill = fill;
            this.stroke = stroke;
            this.title = title;
            this.draw = function(){
                var canvas = document.getElementById('board');
                var ctx = canvas.getContext('2d');
                ctx.beginPath();
                ctx.arc(rescaleX(this.x), rescaleY(this.y), this.r, this.startingAngle, this.endAngle);
                ctx.fillStyle = "#"+this.fill;
                ctx.linewidth = 1;
                ctx.fill();
                ctx.strokestyle = this.stroke;
                ctx.stroke();
                ctx.beginPath();
                ctx.fillStyle = "black";
                ctx.font = "15px monospace";
                ctx.textAlign = "center";
                ctx.fillText(this.title,rescaleX(this.x),rescaleY(this.y)-10);
            }
        }
        function drawCircles() {
            for(var i = circles.length - 1; i >= 0; i--){
                circles[i].draw();
            }
        }
        function getMousePosition(e) {
            var canvas = document.getElementById('board');
            var rect = canvas.getBoundingClientRect();
            mousePosition = {
                x: Math.round(e.x - rect.left),
                y: Math.round(e.y - rect.top)
            }
        }
        function intersects(circle) {
            var dx = mousePosition.x - rescaleX(circle.x);
            var dy = mousePosition.y - rescaleY(circle.y);
            return dx * dx + dy * dy <= circle.r * circle.r;
        }
        function arraysEqual(a, b) {
            if (a === b) return true;
            if (a == null || b == null) return false;
            if (a.length != b.length) return false;
            for (var i = 0; i < a.length; ++i) {
                if (a[i][0] !== b[i][0] || a[i][1] !== b[i][1] || a[i][2] !== b[i][2]) return false;
            }
            return true;
        }
        var request = function(cmd) {
            if(cmd == "insert"){
                M.textareaAutoResize(document.getElementById("insert-description"));
                var instance = M.Modal.getInstance(document.getElementById("modal-insert"));
                instance.open();
            }
        }
        var rescaleX = function(x){
            var canvas = document.getElementById('board');
            return x*canvas.width/100;
        }
        var rescaleY = function(y){
            var canvas = document.getElementById('board');
            return y*canvas.height/100;
        }
        var backscaleX = function(x){
            var canvas = document.getElementById('board');
            return x*100/canvas.width;
        }
        var backscaleY = function(y){
            var canvas = document.getElementById('board');
            return y*100/canvas.height;
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
            if(data["command"] == "list"){
                var res = JSON.parse(ret.output);
                var eq = true;
                if(res.length != circles.length){
                    eq = false;
                }else{
                    for(var i = 0; i < res.length; i++){
                        if(i == focused.key) continue;
                        if(res[i][0] != circles[i].id || res[i][1] != circles[i].x || res[i][2] != circles[i].y || res[i][3] != circles[i].fill || res[i][4] != circles[i].title){
                            eq = false;
                            break;
                        }
                    }
                }
                if(!eq){
                    circles = [];
                    for(var i = 0; i < res.length; i++){
                        circles.push(new Circle(res[i][0],res[i][1],res[i][2],5,res[i][3],"black",res[i][4]));
                    }
                    redraw();
                }
            }else{
                if(data["command"] == "move"){
                    isMouseDown = false;
                    focused.state = false;
                    focused.key = 0;
                }else if(data["command"] == "get"){
                    document.getElementById('item-title').value = ret['title'];
                    document.getElementById('item-description').value = ret['descs'];
                    document.getElementById('item-title-label').classList.add('active');
                    document.getElementById('item-description-label').classList.add('active');
                    document.getElementById('item-color').jscolor.fromString('#'+ret['color']);
                    M.textareaAutoResize(document.getElementById("item-description"));
                }
                call("api.php",{command: "list"});
            }
        }
        call("api.php",{command: "list"});
        var insert = function() {
            var title = document.getElementById("insert-title").value;
            var descs = document.getElementById("insert-description").value;
            var color = document.getElementById("insert-color").innerHTML;
            call("api.php",{command: "insert", title: title, descs: descs, color: color});
            document.getElementById("insert-title").value = "";
            document.getElementById("insert-title").classList.remove("valid");
            document.getElementById("insert-description").value = "";
            document.getElementById("insert-description").classList.remove("valid");
            document.getElementById("insert-title-label").classList.remove("active");
            document.getElementById("insert-description-label").classList.remove("active");
            document.getElementById("insert-color").jscolor.fromString("FFFFFF");
            var instance = M.Modal.getInstance(document.getElementById("modal-insert"));
            instance.close();
        }
        var remove = function() {
            var title = document.getElementById("item-title").value;
            call("api.php",{command: "remove", title: title});
            var instance = M.Modal.getInstance(document.getElementById("modal-item"));
            instance.close();
        }
        var edit = function() {
            var title = document.getElementById("item-title").value;
            var descs = document.getElementById("item-description").value;
            var color = document.getElementById("item-color").innerHTML;
            call("api.php",{command: "edit", title: title, descs: descs, color: color, id: current_id});
            document.getElementById("item-title").value = "";
            document.getElementById("item-title").classList.remove("valid");
            document.getElementById("item-description").value = "";
            document.getElementById("item-description").classList.remove("valid");
            document.getElementById("item-title-label").classList.remove("active");
            document.getElementById("item-description-label").classList.remove("active");
            document.getElementById("item-color").jscolor.fromString("FFFFFF");
            var instance = M.Modal.getInstance(document.getElementById("modal-item"));
            instance.close();
            
            current_id = 0;
        }
    </script>
</head>

<body onresize="redraw()">
    <div id="modal-item" class="modal">
        <form>
            <div class="modal-content">
                <h4>Edit Task</h4>
                <div class="row">
                    <div class="col s12">
                        <div class="row modal-form-row">
                            <div class="input-field col s12">
                                <input id="item-title" type="text" class="validate">
                                <label id="item-title-label" for="item-title">Task Title</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <textarea id="item-description" type="text" class="materialize-textarea validate"></textarea>
                                <label id="item-description-label" for="item-description">Description</label>
                            </div>
                        </div>
                        <div class="row" id="colorpicker">
                            <div class="input-field col s12">
                                <button id="item-color" class="btn-floating btn-large waves-effect waves-light jscolor {valueElement:'chosen-value', onFineChange:'setTextColor(this)'"></button>
                                <label id="item-color-label" for="item-color" class="active">Color Picker</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a class="modal-close waves-effect waves-red btn-flat">Cancel</a>
                <a class="waves-effect waves-orange btn-flat" onclick="remove()">Remove</a>
                <a class="waves-effect waves-green btn-flat" onclick="edit()">Save</a>
            </div>
        </form>
    </div>
    <div id="modal-insert" class="modal">
        <form>
            <div class="modal-content">
                <h4>Insert Task</h4>
                <div class="row">
                    <div class="col s12">
                        <div class="row modal-form-row">
                            <div class="input-field col s12">
                                <input id="insert-title" type="text" class="validate">
                                <label id="insert-title-label" for="insert-title">Task Title</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <textarea id="insert-description" type="text" class="materialize-textarea validate"></textarea>
                                <label id="insert-description-label" for="insert-description">Description</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <button id="insert-color" class="btn-floating btn-large waves-effect waves-light jscolor {valueElement:'chosen-value', onFineChange:'setTextColor(this)'"></button>
                                <label id="insert-color-label" for="insert-color" class="active">Color Picker</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a class="modal-close waves-effect waves-red btn-flat">Cancel</a>
                <a class="waves-effect waves-green btn-flat" onclick="insert()">Submit</a>
            </div>
        </form>
    </div>
    <div class="fixed-action-btn">
        <a class="btn-floating btn-large deep-orange">
            <i class="large material-icons">dehaze</i>
        </a>
        <ul>
            <li><a class="btn-floating blue-grey darken-2" href="stats.php"><i class="material-icons">insert_chart</i></a></li>
            <li><a class="btn-floating blue-grey darken-3" onclick="request(&quot;insert&quot;)"><i class="material-icons">add</i></a></li>
            <li><a class="btn-floating blue-grey darken-4" href="logout.php"><i class="material-icons">exit_to_app</i></a></li>
        </ul>
    </div>
    <canvas id="board"></canvas>
    <script>
    var fitToContainer = function(canvas) {
		canvas.style.width='100%';
		canvas.style.height='100%';
		canvas.width  = canvas.offsetWidth;
		canvas.height = canvas.offsetHeight;
	};
    var drawGrid = function(canvas) {
        var ctx = canvas.getContext("2d");
        var w = canvas.width;
        var h = canvas.height;
        var u = 40;
        var offsetcol = parseInt(w/2) % u;
        var offsetrow = parseInt(h/2) % u;
        // Draw vertical lines
        for(var i = offsetcol; i < w; i += u){
            ctx.beginPath();
            ctx.lineWidth = 1;
            ctx.strokeStyle = "#cfcfcf";
            ctx.moveTo(i,0);
            ctx.lineTo(i,h);
            ctx.stroke();
        }
        for(var i = offsetrow; i < h; i += u){
            ctx.beginPath();
            ctx.lineWidth = 1;
            ctx.strokeStyle = "#cfcfcf";
            ctx.moveTo(0,i);
            ctx.lineTo(w,i);
            ctx.stroke();
        }
    }
    var draw = function(canvas){
        fitToContainer(canvas);
        drawGrid(canvas);
        var w = canvas.width;
        var h = canvas.height;
        var ctx = canvas.getContext("2d");
        ctx.beginPath();
        ctx.lineWidth = 2;
        ctx.strokeStyle = "#888888";
        ctx.moveTo(w/2, 0);
        ctx.lineTo(w/2,h);
        ctx.stroke();
        ctx.moveTo(0,h/2);
        ctx.lineTo(w,h/2);
        ctx.stroke();
    }
    var redraw = function(){
        var c = document.getElementById("board");
        var ctx = c.getContext("2d");
        ctx.clearRect(0,0,c.width,c.height);
        draw(c);
        drawCircles();
    }
    setInterval(() => {
        call("api.php",{command: "list"});
    }, 2000);
    redraw();
    </script>
    <!--JavaScript at end of body for optimized loading-->
    <script type="text/javascript" src="js/materialize.min.js"></script>
</body>
</html>