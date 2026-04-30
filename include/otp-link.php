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
 * @brief generate a QRCode for setting up freeOTP
 * 
 * 
 */
if (!file_exists($dirname . '/config.inc.php')) {
    die("system not installed");
}

require_once __DIR__ . '/constant.php';
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
#otp_code_span {
    font-size:160%;
    color:navy;
    background-color:white;
    padding:0.8rem;
}
</style>
   <img id="logo_id" src="image/logo10000.png" >
<div class="content" style="display:flex;flex-direction: row;justify-content: space-evenly;">
<?php
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\ValidationException;
use chillerlan\Authenticator\{
    Authenticator,
    AuthenticatorOptions
};
use chillerlan\Authenticator\Authenticators\AuthenticatorInterface;

$http = new HttpInput();
try {
    $uuid = $http->get('otp');
    $repository = new \Database(0);
// remove old request  (> 24 hours)
    $repository->exec_sql("delete from otp_send_secret where os_timestamp < now()-interval '12 hours'");

// check if UUID exist
    $id = $repository->get_value("select os_id from otp_send_secret where os_request=$1",
            [$uuid]);
// if UUID doesn't exist exit
    if ($repository->count() == 0) {
        echo _("Expiré : vous devez redemander le renvoi de la clef");
        return;
    }

// get email from id 
    $otp_send = new Otp_Send_Secret_SQL($repository, $id);
    $user = new Noalyss_User($repository, $otp_send->get('use_id'));
    $secret = $user->get_otp_secret();

    // OTP

    $authenticator = new \Noalyss\OTP();
    $authenticator->get_authenticator()->setSecret($secret);
    $data= $authenticator->get_authenticator()->getUri(label:"noalyss:".$user->getEmail(),issuer:"noalyss.eu");
// load secret for this id
//echo "use with php -S localhost:5000 puis ouvrir index.html ";
    $writer = new PngWriter();

// Create QR code
    $qrCode = new QrCode(
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 600,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
    );

    $result = $writer->write($qrCode);
    echo '<div  id="otp_qrcode" >';
    // generate the QRCode
    echo '<h1>',_("Scanner ceci avec votre application OTP"),'</h1>';
    
    ?>
   
    <?php
    echo '<p>';
    echo _("Scanner ce QRCode avec votre application OTP afin de l'ajouter");
    echo '</p>';
    echo '<p>';
   
    echo _("Dans votre application OTP vous devez avoir ce nombre ");
    
    ?>
    <span id="otp_code_span">
    <?=$authenticator->compute_code($secret)?>
    </span>
    
    <?php
    echo '</p>';
    ?>
    <?php
    printf('<img src="data:image/png;base64,%s">', base64_encode($result->getString()));
    
} catch (Exception $exc) {
    record_log($e);
    return;
}
?>
</div>
    <div id="otp_install">
          <h1><?=_("Installation d'une application OTP")?></h1>
     <p>
        <?=_("Si vous n'en avez pas installé sur votre smartphone ou PC, voici notre sélection")?>
    </p>
    <h2>
        <?=_("Android")?>
    </h2>
    <ol>
        <li>
            <a href="https://play.google.com/store/apps/details?id=org.fedorahosted.freeotp" target="_blank">FreeOTP (libre)</a>
        </li>
        <li>
            <a href="https://play.google.com/store/apps/details?id=com.beemdevelopment.aegis" target="_blank">AEgis Authenticator (libre)</a>
        </li>
        <li>
            <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">Google Authenticator</a>
        </li>
    </ol>
    <h2>
        <?=_('IPhone')?>
        
    </h2>
    <ol>
        <li>
            <a href="https://apps.apple.com/fr/app/freeotp-authenticator/id872559395" target="_blank">FreeOTP (libre)</a>
        </li>
    </ol>
    <h2>
        PC 
    </h2>
    <p>
        
        <?=_("Article à propos de otpclient")?>
        <a href="https://blog.apps.education.fr/articles/otpclient-un-client-otp-sur-gnulinux-tres-utile2023-01-08t165554043z">
            OTPClient , un outil très utile sous PC
        </a>
        <br/>
        <?=_('Debian , Ubuntu,...')?>
        <code>
              apt install otpclient
        </code>
    </p>
    <ol>
        <li>
            <a href="https://github.com/paolostivanin/OTPClient" target="_blank">Site OTPClient </a>
            
        </li>
    </ol>
    </div>
</div>


<script>
    function refresh_code() {
        new Ajax.Updater("otp_code_span","compute.php",{parameters:{secret:"<?=$secret?>",action:"qr_refresh"}});
    }
    setInterval(refresh_code, 500);
</script>