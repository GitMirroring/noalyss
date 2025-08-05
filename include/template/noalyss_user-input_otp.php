<?php

/*
 *   This file is part of NOALYSS.
 *
 *   NOALYSS is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   NOALYSS is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with NOALYSS; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
// Copyright Author Dany De Bontridder danydb@aevalys.eu 22/10/23


/**
 * @file
 * @brief input the 6 digit
 */
/**
 * var $this (Noalyss_User)
 */
global $g_user;
$repository = new \Database();

// $uuid is set only when a message with the code
$str="";
if ( $uuid != "") 
{
    $request=new IHidden("rq", $uuid);
    $str=$request->input();
}

$date=new \DateTime();

?>
<style>

/***
 * LOGO
*************************************/
#logo_id {
    position:static;
    top : 0px;
    left:0px;
    width:60px;
     height:auto;
     margin-left:10px;
}
/** small **/
@media (min-width : 576px) {
    #logo_id {
     top : 10px;
    left:20px;
       width: 90px;
     margin-left:20px;
      

    }
}
/** medium**/
@media (min-width : 768px) {
    #logo_id {
    
    }
      
}
/** large**/
@media (min-width : 992px) {
    #logo_id {
     
    }      
}
/** XL **/
@media (min-width : 1200px) {
    #logo_id {
      
       
    }
} 
div.content {
  
}
@media (min-width: 576px) {
    div.content {
      margin-left: 16px;
    }
}
/* MeDium */
@media (min-width: 768px) {
     div.content {
      margin-left: 32px;
    }
}
/* LarGe */
@media (min-width: 992px) {
     div.content {
      margin-left: 64px;
    }
}
/* eXtraLarge */
@media (min-width: 1200px) {
     div.content {
      margin-left: 128px;
    }

}
#vrf_code {
    font-size:200%;
    width:9rem;
    padding:0.5rem;
}

input[type=submit]{
    font-size:200%;
    width:9rem;
    padding:0.5rem;
}
form {
    width:70%;
    margin-left:15%;
}
</style>
   <img id="logo_id" src="image/logo9000.png" >
<div class="content">
    <h1>Double authentification</h1>
    <p>
        Date et heure : 
        <?=$date->format('d/m/Y h:i')?>
    </p>
    <p>
        Entrez le code que vous avez reçu par email ou sur votre application OTP.
    </p>
    <p>
        Rafraichissez la page pour recevoir un nouveau code.
    </p>
    <FORM method="post" action="login.php"  >
        <input type="text" placeholder="999999" id="vrf_code" name="vrf_code" autocomplete="off" autofocus>
        <input type="hidden" name="auth" value="to_validate" >
        <?=$str?>
        <input type="hidden" name="backurl" value="<?=$url?>">
        <p>
        <input type="submit" value="valider">
            
        </p>

    </FORM>
</div>