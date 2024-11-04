<?php
//@description: Test the HtmlInput object it means the Inum, IText , ...

/*
 * * Copyright (C) 2019 Dany De Bontridder <dany@alchimerys.be>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

 * 
 */


/**
 * @file
 * @brief Test the HtmlInput object it means the Inum, IText , ...
 */


$http=new HttpInput();
?>
<script src="js/noalyss_checkbox.js"></script>
<pre>
    <?php var_dump($_GET);?>
</pre>
<form method="GET">
    <?php echo Dossier::hidden();?>
    <?php echo HtmlInput::hidden("script",basename(__FILE__));?>
    <h1>Input_switch.class.php</h1>
    <h2>Normal</h2>
    <pre>
        
    $input_switch=new InputSwitch('input_switch_value',<?php echo $http->get("input_switch_value","string","0")?>);
    echo $input_switch->input();
    </pre>
    input_switch 
    <?php
    
    $input_switch=new InputSwitch('input_switch_value',$http->get("input_switch_value","string","0"));
    
    echo $input_switch->input();
    ?>
    <h2>ReadOnly</h2>
    <pre>
    $input_switch=new InputSwitch('input_switch_readonly',0);
    $input_switch->readOnly=TRUE;
    echo  $input_switch->input();
    </pre>
    input_switch
    <?php
    $input_switch=new InputSwitch('input_switch_readonly',0);
    $input_switch->readOnly=TRUE;
    echo $input_switch->input();
    ?>
    <h2>EXPERIMENTAL InputCheckBox (Check Box)</h2>
    <?php
    require_once NOALYSS_INCLUDE.'/lib/input_checkbox.class.php';
    ?>
    <ul>
        
            
    <?php
    for ($i = 0 ; $i < 10 ; $i ++) :
        echo '<li>';
            $check[$i]=new InputCheckBox("check[]",1,"checkid".$i);
            $check[$i]->classrange="improvechkbox";
            echo $i, " - name = ",$check[$i]->value_container,",id = " , $check[$i]->id_icon,$check[$i]->input();
            
        echo '</li>';
    endfor;
    ?>
    </ul>
    <aside>les selections par plage  ne fonctionnent pas encore avec les CHECKBOX améliorés</aside>
    <script> activate_checkbox_range("improvechkbox")</script>
    <h1>Submit</h1>
    <pre>
        echo HtmlInput::submit("submit", _("Envoi"));
    </pre>
    <p>
    <?php echo HtmlInput::submit("submit", _("Envoi"));?>
    </p>
    <h2>ICheckBox</h2>
    <p>
        For using range all the checkbox must have the same name , like checkbox[]
    </p>
    <ol>
       <?php
       
       for ($i=0;$i<10;$i++) {
        $icheckbox=new ICheckBox('icheckbox[]',0);
        $icheckbox->id=uniqid();
        $icheckbox->set_range("icheckbox11");
        echo '<li>';
        printf ("%s ".$icheckbox->input(),$i);
        echo '</li>';
        
       }
       echo ICheckBox::javascript_set_range("icheckbox11");
    ?>
    </ol>
</form>
<h2>
    Fichier - IFile
</h2>
<form method="POST" enctype="application/x-www-form-urlencoded" onsubmit="check_size();return false;">
    <span id="file-info"></span>
<?php

    $file=new IFILE("file_to_upload");
    $file->id="file_to_upload";
    echo "fichier ",$file->input();
    
    echo HtmlInput::submit("file","Upload");
?>
    <p>Vérifier le changement de taille fichier dans la console JS</p>
</form>

<script>
    function check_size()
    {
        var aFile=document.getElementsByTagName("input");
        console.debug("afile");
        console.debug(aFile);
        var info=document.getElementById("file-info");
        for (var i = 0;i < aFile.length;i++) {
        if ( aFile[i].getAttribute("type")==="file" ) {
            
                console.debug("file"+aFile[i].files[0].size);
                info.innerHTML+="file : "+aFile[i].files[0].name+":"+aFile[i].files[0].size+" bytes <br>";
                
        }

    }
    }
    document.getElementById("file_to_upload").addEventListener("change",function() {

        if ( this.files[0] ) {
            console.debug("file"+this.files[0].size);
        }
    });
</script>
<h2>ICArd</h2>
<h3>Aucun param suppl.</h3>
<?php
    $icard=new ICard("test");
    echo $icard->input();
    echo $icard->search();
?>
    
<div id="debug_box"></div>

    