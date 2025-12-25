<html>
<head>
    <script src="prototype.js"></script>

    <style>
   
    </style>

</head>
<body>
    <div>
        <p>
            Le CSS est important , surtout la position, il faut qu'il soit dans 
            un élément positionné en absolu.
        </p>
            <h2>in-absolute</h2>
        <p style="float : static">
  <?php
     require_once NOALYSS_INCLUDE.'/lib/select_box.class.php';
     $a=new Select_Box("test","position in-absolute click me !");
     $a->set_position("in-absolute");

     $a->add_url("List (link)","?id=5&".Dossier::get());
     $a->add_javascript("Hello (Javascript)","alert('hello')");
     $a->add_value("Value = 10 (set value)",10);
     $a->add_value("Value = 1 (set value)",1);
     $a->add_value("Value = 15 (set value)",15);

     echo $a->input();
     
     ?>
        </p>
        <h2>normal and absolute</h2>
        <p>
     <?php
     $a=new Select_Box("test2","position normal click me !");
     $a->set_filter(_("recherche"));
      $a->set_position("normal");

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
