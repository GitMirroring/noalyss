<html>
<head>
    <script src="prototype.js"></script>

    <style>
      .select_box {
      border:solid 0.5px darkblue;
      background:white;
      width:455px;
      max-width:250px;
      position:relative;
      z-index:3;
      padding:3px;
      margin:0px;
      display:none;
      top:-17px;
      }
     div.select_box ul {
       list-style:none;
       padding:2px;
       margin:1px;
       width:100%;
     }
div.select_box ul li {
    padding-top:2px;
    padding-bottom:2px;
  margin:2px;
}
div.select_box a {
    text-decoration:none;
  color : darkblue;
}
div.select_box a:hover,div.select_box ul li:hover {
    background-color : blue;
  color:lightgrey;
}
    </style>

</head>
<body>
  <?php
     require NOALYSS_INCLUDE.'/lib/select_box.class.php';
     $a=new Select_Box("test","click me !");
     $a->add_url("List","?id=5");
     $a->add_javascript("Hello","alert('hello')");
     $a->add_value("Value = 10",10);
     $a->add_value("Value = 1",1);
     $a->add_value("Value = 15",15);

     echo $a->input();
     
     ?>
</body>
