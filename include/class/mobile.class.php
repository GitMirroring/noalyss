<?php

/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2002-2021) Author Dany De Bontridder <danydb@noalyss.eu>

namespace Noalyss;

use \HtmlInput;
use \Dossier;
use \User;

/**
 * @file
 * @brief Main class for Mobile device 
 */

/**
 * @class Mobile
 * @brief Main class for Mobile device
 */
class Mobile
{
    /**
     * @brief Load a module with all its data from database thanks the access_code
     * @global \Noalyss\type $g_user
     * @param type $p_access_code
     * @return type
     */

    function load_module($p_access_code)
    {
        global $g_user;
        $cn=Dossier::connect();
        $aModule=$cn->get_row("
        select 
            me_file,me_parameter,me_javascript,me_type,menu_ref.me_code,
            pmo_id,pmo_order,pmo_default
        from menu_ref
        join profile_mobile using (me_code)
        where
            me_code=$1 and
            (me_file is not null or trim(me_file) <>'' or
            me_javascript is not null or trim (me_javascript) <> '')
            and profile_mobile.p_id=$2", 
                array($p_access_code,$g_user->get_profile()));

        if (count($aModule)==0)
        {
            return [];
        }
        return $aModule;
    }
    /**
     * @brief HTML Page 
     * @staticvar int $already_call
     * @return type
     */
    function page_start()
    {
        // check not called twiced
        static $already_call=0;
        if ($already_call==1)
            return;
        $already_call=1;

        $style="style-classic7.css";

        $title="NOALYSS";
        echo '<!doctype html>';
        printf("\n");

        echo "<HTML>";

        echo "<HEAD>";
        echo '<meta charset="utf-8">';
        echo "<META http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">";
        echo "
        <TITLE>$title</TITLE>
	<link rel=\"icon\" type=\"image/ico\" href=\"favicon.ico\" />
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <LINK id=\"bootstrap\" REL=\"stylesheet\" type=\"text/css\" href=\"css/bootstrap.min.css\" media=\"screen\"/>
    <LINK id=\"fontello\" REL=\"stylesheet\" type=\"text/css\" href=\"css/font/fontello/css/fontello.css\" media=\"screen\"/>
    <LINK id=\"pagestyle\" REL=\"stylesheet\" type=\"text/css\" href=\"css/".$style."?version=".SVNINFO."\" media=\"screen\"/>
    <link rel=\"stylesheet\" type=\"text/css\" href=\"css/style-print.css?version=".SVNINFO."\" media=\"print\"/>";

        // preload font
        echo '<link rel="preload" href="./css/font/OpenSansRegular.woff" as="font" crossorigin="anonymous" />';
        echo '<link rel="preload" href="./css/font/SansationLight/SansationLight.woff" as="font" crossorigin="anonymous" />';
        echo '<link rel="preload" href="./css/font/fontello/fontello.woff" as="font" crossorigin="anonymous" />';

        echo '<script language="javascript" src="js/calendar.js"></script>
        <script type="text/javascript" src="js/lang/calendar-en.js"></script>';

        if (isset($_SESSION[SESSION_KEY.'g_lang'])&&$_SESSION[SESSION_KEY.'g_lang']=='fr_FR.utf8')
        {
            echo '<script type="text/javascript" src="js/lang/calendar-fr.js"></script>';
        }
        elseif (isset($_SESSION[SESSION_KEY.'g_lang'])&&$_SESSION[SESSION_KEY.'g_lang']=='nl_NL.utf8')
        {
            echo '<script type="text/javascript" src="js/lang/calendar-nl.js"></script>';
        }

        echo '
        <script language="javascript" src="js/calendar-setup.js"></script>
        <LINK REL="stylesheet" type="text/css" href="css/calendar-blue.css" media="screen">
        ';
        // language
        if (isset($_SESSION[SESSION_KEY.'g_lang']))
        {
            set_language();
        }

        echo load_all_script();
        echo '    </HEAD>    ';

        echo "<BODY>";
        echo '<div id="info_div"></div>';
        echo '<div id="error_div">'.
        HtmlInput::title_box(_("Erreur"), 'error_div', 'hide').
        '<div id="error_content_div">'.
        '</div>'.
        '<p style="text-align:center">'.
        HtmlInput::button_action('Valider',
                '$(\'error_div\').style.visibility=\'hidden\';$(\'error_content_div\').innerHTML=\'\';').
        '</p>'.
        '</div>';
    }

    /**
     * @brief Display the menu available for this folder 
     * 
     */
    public function display_menu()
    {
        if (DEBUGNOALYSS>1)
        {
            echo __CLASS__."&rarr;".__FUNCTION__;
        }
        $cn=Dossier::connect();
        $user=new \Noalyss_user($cn);

        $aModule=$cn->get_array("select * 
            from profile_mobile pm 
            join menu_ref mr on (pm.me_code=mr.me_code)
                where p_id=$1 order by pmo_order", [$user->get_profile()]);
        require_once NOALYSS_TEMPLATE."/mobile-display_menu.php";
    }

    /**
     * @brief execute the menu
     * @global type $g_user
     * @staticvar int $level
     * @param type $p_access_code
     * @return type
     */
    public function execute_menu($p_access_code)
    {
        global $g_user,$g_parameter,$cn;
        $aModule=$this->load_module($p_access_code);
        if ( empty($aModule)) {
            return;
        }
        /*-- Load the standard headers if needed -- */
        if ( $aModule['pmo_default'] == 1) {
            $this->page_start();
        }
        
        echo HtmlInput::anchor("&#10094;"._("Retour"), "mobile.php?".http_build_query(["gDossier"=>Dossier::id()]));
        
        if ($aModule['me_file']!="")
        {
            if ($aModule['me_parameter']!=="")
            {
                // if there are paramter put them in superglobal
                $array=compute_variable($aModule['me_parameter']);
                put_global($array);
            }
            if (DEBUGNOALYSS==2)
            {
                echo $aModule['me_file'], " param : ", $aModule['me_parameter'];
            }
            /*
             * Log the file we input to put in the folder test-noalyss for replaying it
             */
            if (LOGINPUT)
            {
                $file_loginput=fopen($_ENV['TMP'].'/scenario-'.$_SERVER['REQUEST_TIME'].'.php', 'a+');
                fwrite($file_loginput, "include '".$aModule['me_file']."';");
                fwrite($file_loginput, "\n");
                fclose($file_loginput);
            }
            // if file is not a plugin, include the file, otherwise
            // include the plugin launcher
            if ($aModule['me_type']!='PL')
            {
                if (file_exists($aModule['me_file']))
                {
                    require_once $aModule['me_file'];
                }
                elseif (file_exists(NOALYSS_INCLUDE.'/'.$aModule['me_file']))
                {
                    require_once NOALYSS_INCLUDE.'/'.$aModule['me_file'];
                }
                else
                {
                    echo echo_warning(_("Fichier non trouvé"));
                }
            }
            else
            {
                require 'extension_get.inc.php';
            }

            exit();
        }
        elseif ($aModule['me_javascript']!='')
        {
            $js=noalyss_str_replace('<DOSSIER>', dossier::id(), $aModule['me_javascript']);
            echo create_script($js);
        }
    }

}
