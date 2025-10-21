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
// Copyright Author Dany De Bontridder danydb@noalyss.eu 21.10.2025

if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
/**
 * @file
 * @brief input parameter for Mail_Parameter
 * 
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;


$http=new HttpInput();
// var $show (string) show or not the FORM for the SMTP
$show=($this->smtp_type=="sendmail")?"none":"grid";
?>
<div class="content">
<div class="inner_box" id="smtp_test_div" style="display:none">
    <?= \HtmlInput::title_box(_("Test"), "smtp_test_div", "hide") ?>
    <div style="display:flex;padding:1rem">
        <div>
            <label for="email_test"><?= _("Email destination") ?></label>
        </div>
        <div>
            <input name="email_test_input" id="email_test_input" type="text" class="input_text"  >
        </div>
    </div>
    <div id="result_test_div" style="padding:1rem"></div>
        <div>
        <ul class="aligned-block">
            <li>

                <button class="smallbutton" onclick="noalyss.parameter_test_smtp();return false;"><?= _("Tester la configuration") ?></button>
            </li>
            <li>
                <?php echo \HtmlInput::button_hide("smtp_test_div") ?>
            </li>
        </ul>
        </div>
</DIV>
<FORM id="form_config_smtp" method="POST" onsubmit="noalyss.save_config_smtp();return false;">
    <div id="form_email_setting_div" >
        <?php
            print \HtmlInput::hidden("ac",$http->request("ac"));
            print \HtmlInput::hidden("gDossier",$http->request("gDossier"));
        ?>
    <div style="display: grid;  grid-template-rows: auto;align-content: center;" >
        <div>
            <p class="text-muted">
                <?= _("Voulez-vous envoyer les emails grâce à un serveur SMTP ou en local avec sendmail") ?>
            </p>
        </div>
        <div>
            <label for="smtp_type"><?= _("Serveur") ?></label>
            <select name="smtp_type" id="smtp_type" onchange="noalyss.parameter_display_smtp()">
                <option value="smtp"
                <?= ($this->smtp_type == 'smtp') ? "selected" : "" ?>   
                        >Serveur SMTP</option>
                <option
                <?= ($this->smtp_type == 'sendmail') ? "selected" : "" ?>   
                    value="sendmail">Sendmail</option>
            </select>
        </div>
         
        <div>
            <label for="smtp_from"><?= _("adresse  par défaut email  de l'expéditeur") ?></label>
        </div>
        <div>
         <input  name="smtp_from" type="text" class="input_text" value="<?= $this->smtp_from ?>">
        </div>
        <div>
            <label for="smtp_replyto"><?= _("adresse  par défaut de réponse") ?></label>
        </div>
        <div>
            <input  name="smtp_replyto" type="text" class="input_text" value="<?= $this->smtp_replyto ?>">
        </div>
         <div style="margin-top:1rem">
        <input type="SUBMIT" class="smallbutton" value="Sauver" id="btn_save1">
        </div>
    </div>
    <div style="display: <?=$show?>;  grid-template-rows: auto;align-content: center;" id="smtp_config_div">
        <div>
            <label for="smtp_user"><?= _("login") ?></label>
        </div>
        <div>
            <input name="smtp_user" type="text" class="input_text" value="<?= $this->smtp_user ?>" width="80">
        </div>
        <div>
            <label for="smtp_password"><?= _("Mot de passe") ?></label>
        </div>
        <div>
            <input name="smtp_password" type="text" class="input_text" value="<?= $this->smtp_password ?>">
        </div>
        <div>
            <label for="smtp_host"><?= _("Serveur") ?></label>
        </div>
        <div>
            <input name="smtp_host" type="text" class="input_text" value="<?= $this->smtp_host ?>">
        </div>
        <div>
            <label for="smtp_port"><?= _("Port") ?></label>
        </div>
        <div>
            <input name="smtp_port" type="text" class="input_text" value="<?= $this->smtp_port ?>" accept="[0-9]*" >
        </div>
        <div>
            <label for="smtp_auth_type"><?= _("Type authentification") ?></label>
        </div>
        <div>
            <select name="smtp_auth_type">
                <option value="CRAM-MD5"    
                <?= ($this->smtp_auth_type == 'CRAM-MD5') ? "selected" : "" ?>   
                        >
                    CRAM-MD5
                </option>

                <option value="LOGIN"    
                <?= ($this->smtp_auth_type == 'LOGIN') ? "selected" : "" ?>   
                        >
                    LOGIN
                </option>

                <option value="PLAIN"    
                <?= ($this->smtp_auth_type == 'PLAIN') ? "selected" : "" ?>   
                        >
                    PLAIN
                </option>



            </select>

        </div>
        <div>
            <label for="smtp_secure"><?= _("Encryption") ?></label>
        </div>
        <div>
            <select  name="smtp_secure" type="select"  >
                <option value="<?= PHPMailer::ENCRYPTION_STARTTLS ?>" 
                <?= ($this->smtp_type == PHPMailer::ENCRYPTION_STARTTLS) ? "selected" : "" ?>
                        >
                    ENCRYPTION_STARTTLS
                </option>
                <option value="<?= PHPMailer::ENCRYPTION_SMTPS ?>"
                <?= ($this->smtp_type == PHPMailer::ENCRYPTION_SMTPS) ? "selected" : "" ?>
                        >
                    ENCRYPTION_SMTPS
                </option>
            </select>
        </div>
        <div><!-- comment -->
            <button class="smallbutton" onclick="$('smtp_test_div').show();$('result_test_div').update('');return false"><?=_("Tester la configuration")?></button>
        </div>
    <div style="margin-top:1rem">
        <input type="SUBMIT" class="smallbutton" value="Sauver">
    </div>
    </div>
    </div>
</FORM>
<script>
    noalyss.parameter_display_smtp();
</script>
</div>