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

require_once NOALYSS_INCLUDE.'/lib/html_input.class.php';
require_once NOALYSS_INCLUDE.'/lib/iaction.class.php';
require_once NOALYSS_INCLUDE.'/lib/ibutton.class.php';
require_once NOALYSS_INCLUDE.'/lib/icard.class.php';
require_once NOALYSS_INCLUDE.'/lib/icheckbox.class.php';
require_once NOALYSS_INCLUDE.'/lib/iconcerned.class.php';
require_once NOALYSS_INCLUDE.'/lib/idate.class.php';
require_once NOALYSS_INCLUDE.'/lib/ifile.class.php';
require_once NOALYSS_INCLUDE.'/lib/ihidden.class.php';
require_once NOALYSS_INCLUDE.'/lib/inum.class.php';
require_once NOALYSS_INCLUDE.'/lib/iperiod.class.php';
require_once NOALYSS_INCLUDE.'/lib/iposte.class.php';
require_once NOALYSS_INCLUDE.'/lib/iradio.class.php';
require_once NOALYSS_INCLUDE.'/lib/irelated_action.class.php';
require_once NOALYSS_INCLUDE.'/lib/iselect.class.php';
require_once NOALYSS_INCLUDE.'/lib/ispan.class.php';
require_once NOALYSS_INCLUDE.'/lib/itext.class.php';
require_once NOALYSS_INCLUDE.'/lib/itextarea.class.php';
require_once NOALYSS_INCLUDE.'/lib/itva_popup.class.php';
require_once NOALYSS_INCLUDE.'/lib/input_switch.class.php';

$http=new HttpInput();
?>
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
    <h1>Submit</h1>
    <pre>
        echo HtmlInput::submit("submit", _("Envoi"));
    </pre>
    <p>
    <?php echo HtmlInput::submit("submit", _("Envoi"));?>
    </p>
</form>