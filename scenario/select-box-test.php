<html>
<head>
    <script src="prototype.js"></script>

    <style>
        /* The container <div> - needed to position the dropdown content */
        .select_box{
            position: relative;
            display: inline-block;
        }

        /* Dropdown Content (Hidden by Default) */
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }

        /* Links inside the dropdown */
        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        /* Change color of dropdown links on hover */
        .dropdown-content a:hover {background-color: #f1f1f1}

        /* Show the dropdown menu on hover */
        .dropdown:hover .dropdown-content {
            display: block;
        }
      .select_box {
      border:solid 0.5px darkblue;
      background:white;
      width:455px;
      max-width:250px;
      padding:3px;
      margin:0px;
      display:none;
      top:-17px;
      position:absolute;
      }
     div.select_box ul {
       list-style:none;
       padding:2px;
       margin:1px;
       width:100%;
       top:10px;

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
    <div>
        <p>
            Le CSS est important , surtout la position, il faut qu'il soit dans 
            un élément positionné en absolu.
        </p>
        <p style="float : static">
  <?php
     require_once NOALYSS_INCLUDE.'/lib/select_box.class.php';
     $a=new Select_Box("test","click me !");
     $a->add_url("List (link)","?id=5&".Dossier::get());
     $a->add_javascript("Hello (Javascript)","alert('hello')");
     $a->add_value("Value = 10 (set value)",10);
     $a->add_value("Value = 1 (set value)",1);
     $a->add_value("Value = 15 (set value)",15);

     echo $a->input();
     
     ?>   
     <?php
     $a=new Select_Box("test2","click me !");
     $a->set_filter(_("recherche"));
     $a->add_value("Value = 10 (set value)",10);
     $a->add_value("Value = 1 (set value)",1);
     $a->add_value("Value = 17 (set value)",15);
     $a->add_value("Value = 18 (set value)",15);
     $a->add_value("Value = 19 (set value)",15);
     $a->add_value("Value = 20 (set value)",15);
     $a->add_value("Value = 25 (set value)",15);
     $a->add_value("Value = 30 (set value)",15);
     $a->add_value("Value = 40 (set value)",15);
     $a->add_value("Value = 50 (set value)",15);
     $a->add_value("Value = 51 (set value)",15);

     echo $a->input();
     
     ?>   
        </p>
        </div>
</body>
