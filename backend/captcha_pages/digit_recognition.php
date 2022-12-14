<?php
require '../config.php';
require '../helpers/add_placeholder.php';
require '../helpers/add_switch_languge.php';
require '../helpers/add_switch_region.php';
require '../helpers/add_buttons.php';
session_start();
?>
<script type="text/javascript" src="../dependencies/pressure-master/dist/pressure.js"></script>
<script type="text/javascript" src="../dependencies/pressure-master/dist/jquery.pressure.js"></script>


<link rel="stylesheet" href=<?php echo $base_url . "css/questionnaire.css"?>>
<link rel="stylesheet" href=<?php echo $base_url . "css/common.css"?>>
<script src= <?php echo $base_url ."js/translate.js"?>/>
<script src= <?php echo $base_url ."js/changeRegion.js"?>/>
<script src= <?php echo $base_url ."js/record.js"?>/>
<script src= <?php echo $base_url ."js/keyhandlers.js"?>/>
<script src= <?php echo $base_url ."js/elementCheckers.js"?>/>
<script src= <?php echo $base_url ."js/get_audio.js"?>/>
<script src= <?php echo $base_url ."js/switch_captcha.js"?>/>
<script src= <?php echo $base_url ."js/play_initialaudio.js"?>/>
<script src= <?php echo $base_url ."js/buttons.js"?>/>

<div class='ca-container'>
<div class="ca-panel-body">
  <form method="post" id="captcha_form">
    <canvas id="ca-canvas"></canvas> 
    <!--<label class='ca-label'>Draw a </label>-->
    <div class='ca-img-container'>
      <img class='ca-img' src=<?php echo $base_url . "backend/image_operations/questionnaire_image.php?id=0&height=120&width=400"; ?> id="captcha_image" />
    </div>
    <?php 
      add_buttons();
    ?>
    <div>
      <audio id="valid">
        <source src=<?php echo $base_url . "assets/sounds/valid.mp3"; ?> type="audio/mp3">
      </audio> 
       
      <audio id="enter">
        <source src=<?php echo $base_url . "assets/sounds/enter.mpeg"; ?> type="audio/mp3">
      </audio> 

      <audio id="invalid">
        <source src=<?php echo $base_url . "assets/sounds/invalid.mp3"; ?> type="audio/mp3">
      </audio>       
      <div id="player">
      </div>
    </div>
  </form>
</div>
<?php 
error_log($_SESSION['is_open']);
put_placeholder();
?>
</div>


<script>
var base_url = "<?php echo $base_url; ?>";
var ended = true;
var validation_com = false;
var is_open = "<?php echo $_SESSION['is_open']; ?>";
var body = document.getElementsByClassName('ca-panel-body')[0];
console.log(body);
console.log(is_open);
console.log(!is_open);
if(is_open == '0') {
  console.log('hreer');
  body.style.display="none";
} else {
  $("#cap_open").show("1000");
  $("#cap_closed").hide("1000");
  audio = new Audio( base_url + 'assets/audio/guides/page_load.mp3');
  audio.play();
  ended = false;
}

var canvas = document.getElementById('ca-canvas');
var ctx = canvas.getContext('2d');
resize();
var pos = { x: 0, y: 0 };
var v = document.getElementById("valid"); 
var i = document.getElementById("invalid"); 
var e = document.getElementById("enter"); 

window.addEventListener('resize', resize);
document.addEventListener('mousemove', draw);
document.addEventListener('mousedown', setPositionAndClear);
document.addEventListener('mouseenter', setPosition);
document.addEventListener('mouseup', stopDrawing);
document.getElementsByClassName("ca-button")[0].addEventListener("click", function(){
  console.log('button pressed');
});

function setPosition(e) {
  pos.x = e.clientX;
  pos.y = e.clientY;
  //pos.x = e.offsetX;
  //pos.y = e.offsetY;
}
function setPositionAndClear(e) {
  setPosition(e);
  if(ended == true) {
    return;
  }
  ctx.clearRect(0, 0, canvas.width, canvas.height);
}
function bringCanvasFront() {
  canvas.style.zIndex = "99";
  console.log("bring canvas front");
}
function takeCanvasBack() {
  canvas.style.zIndex = "-1";
  console.log("take canbas back");
}
function stopDrawing(e) {
  console.log(e);
  if(ended == true) {
    return;
  }
  if(canvas.style.zIndex == "-1") {
    return;
  }
  takeCanvasBack();
  if(isAButton(e.target)) {
    return;
  }
  setPosition(e);
  var image = canvas.toDataURL("image/jpg");
  /*
  var link = document.createElement('a');
  link.setAttribute("type", "hidden"); // make it hidden if needed
  link.download = 'canvasimg';
  link.href = image;
  document.body.appendChild(link);
  link.click();
  link.remove();
  */
  $.ajax({
    url: base_url + "backend/validation/digit_recognition.php",
    method:"POST",
    data:{img:image},
    success:function(data)
    {
      console.log(data)
      if(data == 'success') {
        v.play();
        alert("Successful Validation");
        $('#canvas').hide();
        document.removeEventListener('mousemove', draw);
        document.removeEventListener('mousedown', setPositionAndClear);
        document.removeEventListener('mouseenter', setPosition);
        document.removeEventListener('mouseup', stopDrawing);
        var placeholder = document.getElementsByClassName("ca-placeholder-body")[0]; 
        placeholder.style.border = "4px solid green";
        $('.ca-panel-body').hide(1000);
        tick_img = "<img class='ca-val-image' src='" + base_url + "assets/images/tick.jpeg'/>";
        $('.ca-placeholder-body').html(tick_img + "<h3 class='ca-validated'> Captcha Validated</h3>");
        ended = true;
        validation_com = true;
        //$('#register').attr('disabled', false);
      } else {
        i.play();
        alert("Unsuccessful validation");
        //$('#register').attr('disabled', 'disabled');
        //$('#captcha_image').attr('src', 'image.php');
        console.log('Invalid Code');
      }
    }
  });
  ctx.clearRect(0, 0, canvas.width, canvas.height);
}
function resize() {
  //ctx.canvas.width = window.innerWidth;
  //ctx.canvas.height = window.innerHeight;
  //ctx.canvas.width = 28;
  //ctx.canvas.height = 28;
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
  console.log('resizing');

}

function draw(e) {
  if(ended == true) {
    return;
  }
  if (e.buttons !== 1) return;
  if(isAButton(e.target)) {
    return;
  }
  bringCanvasFront();
  ctx.beginPath(); // begin
  ctx.lineWidth = 5;
  ctx.lineCap = 'round';
  ctx.strokeStyle = 'rgb(255,0,0)';
  ctx.moveTo(pos.x, pos.y); // from
  setPosition(e);
  ctx.lineTo(pos.x, pos.y); // to
  ctx.stroke(); // draw it!
}
</script>

<div id='arrow'></div>
<script>
  function disp(str) {
    //alert(str);
    document.getElementById('arrow').innerHTML = str;
  }
</script>
