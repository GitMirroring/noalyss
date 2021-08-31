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

/* ! \file
 * \brief default page where user access
 */
/* ! \mainpage NOALYSS
 * Documentation
 * - \subpage Francais
 * - \subpage English
 *
 * \page Francais
 * \section intro_sec Introduction
 *
 * Cette partie contient de la documentation pour les développeurs.
 *
 * \section convention_code Convention de codage
 * <p>
 * Quelques conventions de codage pour avoir un code plus ou moins
 * homogène
 * <ol>
 * <li>Tant que possible réutiliser ce qui existe déjà, </li>
 * <li>Améliorer ce qui existe déjà et vérifier que cela fonctionne toujours</li>
 * <li>Documenter avec les tags doxygen votre nouveau code,</li>
 * <li>Dans le répertoire include: Les noms de fichiers sont *.inc.php pour les fichiers à éxécuter</li>
 * <li>Dans le répertoire include: Les noms de fichiers sont *.php pour les fichiers contenant des fonctions uniquement</li>
 * <li>Dans le répertoire include: Les noms de fichier sont
 * class_*.php pour les fichiers contenant des classes.</li>
 * <li>Dans le répertoire include: Les noms de fichier ajax* correspondent aux fichiers appelé par une fonction javascript en ajax, 
 * normalement le nom de fichier est basé sur le nom de la fonction javascript
 * exemple pour la fonction javascript anc_key_choice le fichier correspondant est
 * ajax/ajax_anc_key_choice.php
 * <li>Dans le répertoire include/template: les fichiers de
 * présentation HTML </li>
 * <li>Utiliser sql/upgrade.sql comme fichier temporaire pour modifier la base de données, en général
 *  ce fichier deviendra l'un des patch </li>
 * <li>Faire de la doc </li>
 * </ol>
 *
 * </p>
 * \section conseil Conseils
 * <p>
 * Utiliser cette documentation, elle est générée automatiquement avec Doxygen,
 * <ul>
 * <li>Related contient tous les \\todo</li>
 * <li>Global -> function pour lire toute la doc sur les fonctions</li>
 * <li>Regarder dans dossier1.html et account_repository.html  pour la doc des base de données
 * </ul>
 *  et il ne faut connaître que ces tags
 * <ul>
 * <li> \\file en début de fichier</li>
 * <li> \\todo ajouter un todo </li>
 * <li> \\enum pour commenter une variable</li>
 * <li> \\param pour commenter le paramètre d'une fonction</li>
 * <li> \\brief Commentaire du fichier, de la fonction ou de la classe</li>
 * <li> \\note des notes, des exemples</li>
 * <li> \\throw or exception is a function can throw an exception
 * <li> \\par to create a new paragraph
 * <li> \\return ce que la fonction retourne</li>
 * <li> \\code et \\endcode si on veut donner un morceau de code comme documentation</li>
 * <li> \\verbatim et \\endverbatim si on veut donner une description d'un tableau,  comme documentation</li>
 * <li>  \\see xxxx Ajoute un lien vers un fichier, une fonction ou une classe </li>
 * </ul>
 * ----------------------------------------------------------------------
 * \page English
 * \section intro_sec Introduction
 *
 * This parts contains documentation for developpers
 *
 * \section convention_code Coding convention
 * <p>
 * Some coding conventions to have a homogeneous code
 * <ol>
 * <li>Reuse the existing code , </li>
 * <li>Improve and test that the function is still working</li>
 * <li>Make documentation thanks doxygen tag</li>
 * <li>In the folder include: filenames ending by  *.inc.php will be executer after being included</li>
 * <li>In the folder include: filenames end by  *.php if they contains only function</li>
 * <li>In the folder include: filenames starting with
 * class_*.php if it is related to a class.</li>
 * <li>In the folder include, files starting with ajax are executed by ajax call, usually, the file name is
 * based on the javascript function, example for the javascript function anc_key_choice the corresponding file is
 * ajax/ajax_anc_key_choice.php
 * 
 * <li>In the folder include/template: files for the HTML presentation
 * </li>
 * <li>Use sql/upgrade.sql as temporary file to modify the database,this file will be the base for a SQL patch
 *  </li>
 * <li>Write documentation </li>
 * </ol>
 *
 * </p>
 * \section advice Advices
 * <p>
 * Use this document, it is generated automatically by doxygen, check the documentation your made, read it first this
 * documentation before making changes
 * <ul>
 * <li>Related contains all the \\todo</li>
 * <li>Global -> all the functions</li>
 * <li>check into mod1.html and account_repository.html for the database design
 * </ul>
 *  You need to know only these tags
 * <ul>
 * <li> \\file in the beginning of a file</li>
 * <li> \\todo add a todo </li>
 * <li> \\enum comment a variable</li>
 * <li> \\param about the parameter of a function</li>
 * <li> \\brief Documentation of the file, function or class</li>
 * <li> \\note note exemple</li>
 * <li> \\throw or exception is a function can throw an exception
 * <li> \\par to create a new paragraph
 * <li> \\return what the function returns</li>
 * <li> \\code and \\endcode code snippet given as example</li>
 * <li> \\verbatim and \\endverbatim if we want to keep the formatting without transformation</li>
 * <li>  \\see xxxx create a link to the file, function or object xxxx </li>
 * </ul>
 */


if (!file_exists('..'.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'config.inc.php'))
{
    header("Location: install.php", true, 307);
    exit(0);
}


echo '<!doctype html><HTML>
<head>
<TITLE> NOALYSS </TITLE>
<link rel="shortcut icon" type="image/ico" href="favicon.ico" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="screen">
<link rel="stylesheet" type="text/css" href="css/index.css" media="screen">
<script src="js/prototype.js" type="text/javascript"></script>
<script src="js/noalyss_script.js" type="text/javascript"></script>
</head>
<BODY>';
$my_domain="";
require_once '../include/constant.php';
require_once '../include/config.inc.php';
require_once NOALYSS_INCLUDE.'/lib/ac_common.php';
if ( file_exists("install.php")&& DEBUGNOALYSS == 0 )
{
    // At the end of the installation procedure , the install file must be removed
    if (isset($_GET['remove_install']))
    {
        if (is_writable(__DIR__."/install.php"))
        {
            unlink(__DIR__."/install.php");
        }
    }
    // if removed failed then
    if (file_exists("install.php"))
    {
        /*
         * This file shouldn't exist
         */
        echo "<h1>";
        printf(_("Le fichier %s/install.php est encore présent, après l'avoir exécuté pour vous mettre à jour, vous devez l'effacer."),
                __DIR__);
        echo "<br>";
        echo _("Tant que ce n'est pas vous fait vous ne pouvez pas utiliser NOALYSS");
        echo "</h1>";
        return;
    }
}

/**
 * Debug Design
 */
if ( DEBUGNOALYSS == 2 )
{
    echo <<<EOF
<div class="d-none d-sm-block d-md-none d-lg-none d-xl-none bg-info">Small</div>
<div class="d-none d-md-block d-lg-none bg-info">Medium</div>
<div class="d-none d-lg-block d-xl-none bg-info">Large</div>
<div class="d-none d-xl-block bg-info">X Large</div>
EOF;
}

if (strlen(domaine)>0)
{
    $my_domain=sprintf(_("Domaine")." : %s", domaine);
}

if (defined("RECOVER")&&isset($_REQUEST['recover']))
{
    require_once '../include/recover.php';
}
// reconnect , create a variable to reconnect properly in login.php
$goto="";
if (isset($_REQUEST['reconnect'])&&isset($_REQUEST['backurl']))
{
    $goto='<input type="hidden" value="'.strip_tags($_REQUEST['backurl']).'" name="backurl">';
}
?>
<div >
    <div class="d-sm-block">
        <a href="https://www.noalyss.eu"><IMG SRC="image/logo9000.png" id="logo_id" alt="NOALYSS"></a>
     
    </div>    
    <div class="container">

        <div class="mx-auto" id="login_div" style="margin-top:10%"> 
        <h1 style="text-align: center;color:darkblue;font-weight: 800">NOALYSS</h1>
            <form id="login_frm" action="login.php" method="post" name="loginform" class="p-sm-3" >
                <?php echo $goto; ?>


                <div class="form-group row ">
                    <input type="text"  class="input_text " value="" id="p_user" name="p_user" autofocus tabindex="1" placeholder="User" style="padding:10px;margin:15px" >
                </div>

                <div class="form-group row">
                    <INPUT TYPE="PASSWORD"  class="input_text" value=""  id="p_pass" NAME="p_pass"  tabindex="2" placeholder="*******" style="padding:10px;margin:15px;">
                </div>

                <?php
// if captcha is used
                if (defined('NOALYSS_CAPTCHA') && NOALYSS_CAPTCHA==true) :
                    ?>
                    Indiquer le code que vous lisez dans l'image
                    <img id="captcha" src="securimage/securimage_show.php" alt="CAPTCHA Image" border=1/>

                    <input type="text" class="input_text" name="captcha_code" size="10" maxlength="6" autocomplete="off"/>
                    <a href="#" onclick="document.getElementById('captcha').src = 'securimage/securimage_show.php?' + Math.random(); return false">Reload Image</a>

                    <?php
                endif;
                ?>  
                <div class="form-group  row  justify-content-center">
                    <INPUT TYPE="SUBMIT"  class="button" style="width:91%;height:53px" NAME="login" value="se connecter">
                </div>

            </form>
            <div>
                <?php if (defined("RECOVER")) : ?>
                    <a id="recover_link" href="#">Mot de passe oublié ? </a>

                    <div id="recover_box">
                        <span style="display:block;font-size:120%;background-color: white;margin:0px">Recouvrement identifiant
                            <span style="cursor: pointer;float: right;position:relative;right:0px" id="close">
                                <a ref="#" id="close_link">&times;</a></span>
                        </span>
                        Indiquez votre login ou votre email
                        <form method="POST" style="padding:20px">
                            <input type="hidden" value="send_email" name="id">
                            <input type="hidden" value="recover" name="recover" >
                            <div class="form-group row ">
                                <label for="login">Login</label>
                                <input type="text"  class="input_text " value="" name="login" placeholder="login" nohistory >
                            </div>
                           
                            <div class="mx-auto info" style="background-color: darkgray;text-align: center;">OU </div>
                            <div class="form-group row ">
                                 <label for="email">e-mail</label>
                                
                                 <input type="text"  class="input_text" name="email" nohistory placeholder="email@domain.eu">
                            </div>
                            <input type="submit" class="button" name="send_email" value="Envoi email">
                            <input type="button" class="button" id="close_link_bt" value="Annuler">
                            </div>
                            </div>    
                            <script>
                                document.getElementById('recover_link').onclick = function () {
                                    document.getElementById('recover_box').style.display = "block";
                                }
                                document.getElementById('close_link').onclick = function () {
                                    document.getElementById('recover_box').style.display = "none";
                                }
                                document.getElementById('close_link_bt').onclick = function () {
                                    document.getElementById('recover_box').style.display = "none";
                                }
                            </script>
                        <?php endif; ?>

                        <span id="info_noalyss">
                            version  NOALYSS_VERSION - <?php echo $my_domain; ?>
                        </SPAN>

                </div>
    </div>
                <script> SetFocus('p_user');</script>

                </body>
                </html>

