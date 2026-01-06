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

// Copyright Author Dany De Bontridder danydb@aevalys.eu

/**
 * @file
 * @brief common utilities for a lot of procedure, classe
 */

require_once NOALYSS_INCLUDE.'/lib/function_javascript.php';

/**
 * \brief to protect again bad characters which can lead to a cross scripting attack
  the string to be diplayed must be protected. Side effects with htmlentities, especially for
 * the date (transform dot in &periode;) and number
 */
function h($p_string)
{
    return ( $p_string === null)?"":htmlspecialchars($p_string,ENT_QUOTES|ENT_HTML5,'UTF-8',true);
}
function p($p_string, $p_extra='')
{
    return '<p '.$p_extra.'>'.$p_string."</p.>";
}
function span($p_string, $p_extra='')
{
    return '<span ' . $p_extra . '>' . $p_string . '</span>';
}

function hi($p_string)
{
    return '<i>' . h($p_string) . '</i>';
}

function hb($p_string)
{
    return '<b>' . h($p_string) . '</b>';
}

function th($p_string, $p_extra='',$raw='')
{
    return '<th  ' . $p_extra . '>' . h($p_string).$raw . '</th>';
}

function h2info($p_string)
{
    return '<h2 class="info">' . h($p_string) . '</h2>';
}

function h2($p_string, $p_class="",$raw="")
{
    return '<h2 ' . $p_class . '>' . $raw.h($p_string) . '</h2>';
}
function h1($p_string, $p_class="")
{
    return '<h1 ' . $p_class . '>' . h($p_string) . '</h1>';
}
/**
 * \brief surround the string with td
 * \param $p_string string to surround by TD
 * \param $p_extra extra info (class, style, javascript...)
 * \return string surrounded by td
 */

function td($p_string='', $p_extra='')
{
    return '<td  ' . $p_extra . '>' . $p_string . '</td>';
}

function tr($p_string, $p_extra='')
{
    return '<tr  ' . $p_extra . '>' . $p_string . '</tr>';
}

/**
 * @brief escape correctly php string to javascript 
 */
function j($p_string)
{
    $a = preg_replace("/\r?\n/", "\\n", addslashes($p_string));
    $a = noalyss_str_replace("'", '\'', $a);
    return $a;
}

/**
 * format the number for the CSV export
 * @param $p_number number
 */
function nb($p_number)
{
    $r=trim($p_number);
    $r = sprintf('%.4f', $p_number);
    $r = noalyss_str_replace('.', ',', $r);

    return $r;
}

/**
 * return D if the number is smaller than 0 , C if bigger and an empty string if
 * equal to 0. Used for displaying saldo D / C (debit / credit )
 * @param float $p_number
 */
function findSide($p_number)
{
    $return ='';
    if ( $p_number > 0 ) {
        $return ='D';
    }else {
        $return =($p_number== 0)?"":"C";
    }
    return $return;
}

/**
 * format the number with a sep. for the thousand
 * @param $p_number number
 * @param $p_dec number of decimal to display
 */
function nbm($p_number,$p_dec = 2)
{

    if (noalyss_trim($p_number) == '')
	return '';
    if ($p_number == 0)
	return "0,00";
    
    $a = doubleval($p_number);
    $r = number_format($a, $p_dec, ",", ".");
    if (trim($r) == '')
    {
	var_dump($r);
	var_dump($p_number);
	var_dump($a);
	exit();
    }

    return $r;
}

/**
 * \brief  log error into the /tmp/noalyss_error.log it doesn't work on windows
 *
 * \param p_log message
 * \param p_line line number
 * \param p_message is the message
 *
 * \return nothing
 *
 */

function echo_error($p_log, $p_line="", $p_message="")
{
    $msg="ERREUR :" . $p_log . " " . $p_line . " " . $p_message;
    echo $msg;
    record_log($msg);

}

/**
 * \brief  Compare 2 dates
 * \param p_date
 * \param p_date_oth
 *
 * \return
 *      - == 0 les dates sont identiques
 *      - > 0 date1 > date2
 *      - < 0 date1 < date2
 */

function cmpDate($p_date, $p_date_oth)
{
    date_default_timezone_set('Europe/Brussels');

    $l_date = isDate($p_date);
    $l2_date = isDate($p_date_oth);
    if ($l_date == null || $l2_date == null)
    {
	throw new Exception("erreur date [$p_date] [$p_date_oth]");
    }
    $l_adate = explode(".", $l_date);
    $l2_adate = explode(".", $l2_date);
    $l_mkdate = mktime(0, 0, 0, $l_adate[1], $l_adate[0], $l_adate[2]);
    $l2_mkdate = mktime(0, 0, 0, $l2_adate[1], $l2_adate[0], $l2_adate[2]);
    // si $p_date > $p_date_oth return > 0
    return $l_mkdate - $l2_mkdate;
}

/***!
 * @brief check if the argument is a number
 *
 * @param $p_int number to test
 *
 * @return
 *        - 1 it's a number
 *        - 0 it is not
 */
function isNumber($p_int)
{
    if (strlen(noalyss_trim($p_int)) == 0)
	return 0;
    if (is_numeric($p_int) === true)
	return 1;
    else
	return 0;
}

/***
 * \brief Verifie qu'une date est bien formaté
 *           en d.m.y et est valable
 * \param $p_date
 *
 * \return
 * 	- null si la date est invalide ou malformaté
 *      - $p_date si tout est bon
 *
 */

function isDate($p_date)
{
    if (noalyss_strlentrim($p_date) == 0)
	return null;
    if (preg_match("/^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}$/", $p_date) == 0)
    {

	return null;
    }
    else
    {
	$l_date = explode(".", $p_date);

	if (sizeof($l_date) != 3)
	    return null;

	if ($l_date[2] > COMPTA_MAX_YEAR || $l_date[2] < COMPTA_MIN_YEAR)
	{
	    return null;
	}

	if (checkdate($l_date[1], $l_date[0], $l_date[2]) == false)
	{
	    return null;
	}
    }
    return $p_date;
}

/**
 * \brief Default page header for each page
 *
 * \param p_theme default theme
 * \param $p_script
 * \param $p_script2  another js script
 * Must be called only once
 * \return none
 */

function html_page_start($p_theme="", $p_script="", $p_script2="")
{
    // check not called twiced
    static  $already_call=0;
    if ( $already_call==1)return;
    $already_call=1;

    $cn = new Database();
    if ($p_theme != "")
    {
	$Res = $cn->exec_sql("select the_filestyle from theme
                           where the_name=$1" ,[$p_theme]);
	if (Database::num_row($Res) == 0)
        {
	    $style = "style-classic7.css";
        }
	else
	{
	    $s = Database::fetch_array($Res, 0);
	    $style = $s['the_filestyle'];
	}
    }
    else
    {
	$style = "style-classic7.css";
    } // end if
	$title="NOALYSS";
        
	if ( isset ($_REQUEST['ac'])) {
            $ac=strip_tags($_REQUEST['ac']);
		if (strpos($ac,'/') <> 0)
		{
			$m=  explode('/',$ac);
			$title=$m[count($m)-1]."  ".$title;
		}
		else
			$title=$ac."  ".$title;
	}
    $is_msie=is_msie();
    
    if ($is_msie == 0 ) 
    {
        echo '<!DOCTYPE html>';
        printf("\n");
 
    }
    else {
        echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 FINAL//EN" >';
        printf("\n");
    }
    echo '<HTML>';
    
    if ($p_script2!="")
    {
        $p_script2='<script src="'.$p_script2.'" type="text/javascript"></script>';
    }
    $style=trim($style);
    echo "<HEAD>";
    echo '<meta charset="utf-8">';
    echo "<META http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">";
    if ($is_msie==1)
    {
        echo '      <meta http-equiv="x-ua-compatible" content="IE=edge"/>';
    }
    global $version_noalyss;
    echo <<<EOF
    <TITLE>$title</TITLE>
	<link rel="icon" type="image/ico" href="favicon.ico" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <LINK id="bootstrap" REL="stylesheet" type="text/css" href="css/bootstrap.min.css" media="screen"/>
    <LINK id="fontello" REL="stylesheet" type="text/css" href="css/font/fontello/css/fontello.css" media="screen"/>
    <LINK id="pagestyle" REL="stylesheet" type="text/css" href="css/$style?version=$version_noalyss" media="screen"/>
    <link rel="stylesheet" type="text/css" href="css/style-print.css?version=$version_noalyss" media="print"/>
    $p_script2 
EOF;
    // preload font
    echo    '<link rel="preload" href="css/font/OpenSansRegular.woff" as="font" crossorigin="anonymous" />';
    echo    '<link rel="preload" href="css/font/SansationLight/SansationLight.woff" as="font" crossorigin="anonymous" />';
    echo    '<link rel="preload" href="css/font/fontello/fontello.woff" as="font" crossorigin="anonymous" />';
    
    echo '<script language="javascript" src="js/calendar.js"></script>
    <script type="text/javascript" src="js/lang/calendar-en.js"></script>';

    if (isset($_SESSION[SESSION_KEY.'g_lang']) && $_SESSION[SESSION_KEY.'g_lang']=='fr_FR.utf8' )
    {
	echo '<script type="text/javascript" src="js/lang/calendar-fr.js"></script>';
    }
    if (isset($_SESSION[SESSION_KEY.'g_lang']) && $_SESSION[SESSION_KEY.'g_lang']=='nl_NL.utf8' )
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

    //  Retrieve colors for this folder
    if ( isset($_REQUEST['gDossier'])  ) {
        $noalyss_appearance=new Noalyss_Appearance();
        $noalyss_appearance->print_css();
    }
    echo '    </HEAD>    ';

    echo "<BODY $p_script>";
    echo '<div id="info_div"></div>';
    echo '<div id="error_div">'.
            HtmlInput::title_box(_("Erreur"), 'error_div','hide').
            '<div id="error_content_div">'.
            '</div>'.
            '<p style="text-align:center">'.
            HtmlInput::button_action('Valider','$(\'error_div\').style.visibility=\'hidden\';$(\'error_content_div\').innerHTML=\'\';').
            '</p>'.
            '</div>';

}

/**
 * \brief Minimal  page header for each page, used for small popup window
 *
 * \param p_theme default theme
 * \param $p_script
 * \param $p_script2  another js script
 *
 * \return none
 */

function html_min_page_start($p_theme="", $p_script="", $p_script2="")
{

    $cn = new Database();
    if ($p_theme != "")
    {
	$Res = $cn->exec_sql("select the_filestyle from theme
                           where the_name='" . $p_theme . "'");
	if (Database::num_row($Res) == 0)
	    $style = "style-classic7.css";
	else
	{
	    $s = Database::fetch_array($Res, 0);
	    $style = $s['the_filestyle'];
	}
    }
    else
    {
	$style = "style-classic7.css";
    } // end if
    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 FINAL//EN">';
    echo "<HTML>";


    if ($p_script2!="")
    {
        $p_script2='<script src="'.$p_script2.'" type="text/javascript"></script>';
    }

    echo "<HEAD>
    <TITLE>NOALYSS</TITLE>
    <META http-equiv=\"Content-Type\" content=\"text/html; charset=UTF8\">
    <LINK REL=\"stylesheet\" type=\"text/css\" href=\"css/$style\" media=\"screen\">
    <link rel=\"stylesheet\" type=\"text/css\" href=\"css/style-print.css\" media=\"print\">" .
    $p_script2 . "
    <script src=\"js/prototype.js\" type=\"text/javascript\"></script>
    <script src=\"js/noalyss_script.js\" type=\"text/javascript\"></script>
    <script src=\"js/acc_ledger.js\" type=\"text/javascript\"></script>
    <script src=\"js/smoke.js\" type=\"text/javascript\"></script>
    <script src=\"export.php?loadjs=message\"  type=\"text/javascript\" charset=\"utf-8\"></script>";
    
    echo "<LINK id=\"pagestyle\" REL=\"stylesheet\" type=\"text/css\" href=\"css/font/fontello/css/fontello.css\" media=\"screen\"/>";
    
    //  Retrieve colors for this folder
    if ( isset($_REQUEST['gDossier'])  ) {
        $noalyss_appearance=new Noalyss_Appearance();
        $noalyss_appearance->print_css();
    }
    echo '</HEAD>';
    echo "<BODY $p_script>";
    /* If we are on the user_login page */
    if (basename($_SERVER['PHP_SELF']) == 'user_login.php')
    {
	return;
    }
}

/**
 * \brief end tag
 *
 */

function html_page_stop()
{
    echo "</BODY>";
    echo "</HTML>";
}

/**
 * \brief Echo no access and stop
 *
 * \return nothing
 */

function NoAccess($js=1)
{
    if ($js == 1)
    {
	echo "<script>";
	echo "alert ('" . _('Cette action ne vous est pas autorisée Contactez votre responsable') . "');";
	echo "</script>";
    }
    else
    {
	echo '<div class="redcontent">';
	echo '<h2 class="error">' . _(' Cette action ne vous est pas autorisée Contactez votre responsable') . '</h2>';
	echo '</div>';
    }
    exit - 1;
}
/**
 * @brief replaced by sql_string
 * @deprecated
 */
function FormatString($p_string)
{
    return sql_string($p_string);
}
/**
 * \brief Fix the problem with the quote char for the database
 *
 * \param $p_string
 * \return a string which won't let strange char for the database
 */

function sql_string($p_string)
{
    $p_string = trim($p_string??"");
    if (strlen($p_string) == 0)
	return null;
    $p_string = noalyss_str_replace("'", "''", $p_string);
    $p_string = noalyss_str_replace('\\', '\\\\', $p_string);
    return $p_string;
}
/**
 * @brief Same menu for all extensions, with the right level, it calls 
 * ShowItem with the right parameters
 * @global $level (int) global variable of the menu level
 * @param $p_array (array)
 * @param $default (string) selected item
 * @param $p_extra (string) extra code for the table tag (CSS or javascript)
 * @see ShowItem
 */
function show_menu_extension($p_array,$default="",$p_extra="")
{
    global $level;
    
   
   $level++;
    switch ($level) {
        case 3:
            $p_dir='H';
            $class="nav-item nav-item-underline";
            $class_ref="nav-link";
            $p_extra="noprint nav nav-pills nav-level3";
            $class_div="menu3";
            break;
         case 2:
            $p_dir='H';
            $class="nav-item nav-item-underline";
            $class_ref="nav-link";
            $p_extra="noprint nav nav-pills nav-level2";
            $class_div="menu2";
            break;
        case 1:
            $p_dir='H';
            $class="nav-item nav-item-underline";
            $class_ref="nav-link";
            $p_extra='noprint nav nav-pills nav-fill  ';
            $class_div="top_menu";
            break;
        default:
            $p_dir='H';
            $class="nav-item nav-item-underline";
            $class_ref="nav-link";
            $p_extra="noprint nav nav-level4";
            $class_div="menu3";
            break;
    }
   return "<div class=\"$class_div\">"
           . ShowItem($p_array,$p_dir,$class,$class_ref,$default,$p_extra)
           ."</div>";
           

}
/**
* \brief store the string which print
 *           the content of p_array in a table
 *           used to display the menu
 * \param  $p_array array like ( 0=>HREF reference, 1=>visible item (name),2=>Help(opt),
 * 3=>selected (opt) 4=>javascript (normally a onclick event) (opt)
 * \param $p_dir direction of the menu (H Horizontal  V vertical)
 * \param $class CSS for li tag
 * \param $class_ref CSS for the A tag
 * \param $default selected item
 * \param $p_extra extra code for the table tag (CSS or javascript)
 */
  /* \return : string */

function ShowItem($p_array, $p_dir='V', $class="nav-item", $class_ref="nav-link", $default="", $p_extra="nav nav-pills nav-fill")
{
      
    $ret = '';
    // for comptability with old application  mtitle for anchor is replace by nav-link
    
    
    // direction Vertical
    if ($p_dir == 'V')
    { 
        $ret .= "<ul class=\"$p_extra  \"  style=\"display:flex;flex-direction:column\">";
    } else {
        $ret .= "<ul class=\"$p_extra \" >";
       
    }
    
    foreach ($p_array as $all => $href)
    {
        $javascript = (isset($href[4])) ? $href[4] : "";
        $title = "";
        $set = "XX";
        if (isset($href[2]))
        {
            $title=$href[2];
        }
        if (isset($href[3]))
        {
            $set=$href[3];
        }

        if ($set==$default)
        {
            $ret.='<li class="'.$class.' li-active "><A class="'.$class_ref.'  active'.'" HREF="'.$href[0].'" title="'.$title.'" '.$javascript.'>'.$href[1].'</A></li>';
        }
        else
        {
            $ret.='<li class="'.$class.'"><A class="'.$class_ref.'" HREF="'.$href[0].'" title="'.$title.'" '.$javascript.'>'.$href[1].'</A></li>';
        }
        
    }
 
    $ret.="</ul>";
    return $ret;
}

/**
 * \brief warns
 *
 * \param p_string error message
 * gen :
 * 	- none
 * \return:
 *      - none
 */

function echo_warning($p_string)
{
    echo '<span class="warning">' . $p_string . '</span>';
}

/**
 * \brief Show the periode which found thanks its id
 *
 *
 * \param  $p_cn database connection
 * \param p_id
 * \param pos Start or end
 *
 * \return: string
 */

function getPeriodeName($p_cn, $p_id, $pos='p_start')
{
    if ($pos != 'p_start' &&  $pos != 'p_end')
    {
        echo_error('lib/ac_common.php' . "-" . __LINE__ . '  UNDEFINED PERIODE');
        throw new Exception(_("paramètre invalide"));
    }
    if ( isNumber($p_id) == 0 )
	{
	throw new Exception("Paramètre invalide");
	return;
	}
    $ret = $p_cn->get_value("select to_char($pos,'Mon YYYY') as t from parm_periode where p_id=$1", 
           array( $p_id));
    return $ret;
}

/**
 * \brief Return the period corresponding to the
 *           date
 *
 * \param p_cn database connection
 * \param p_date the month + year 'MM.YYYY'
 *
 * \return:
 *       parm_periode.p_id
 */

function getPeriodeFromMonth($p_cn, $p_date)
{
    $R = $p_cn->get_value("select p_id from parm_periode where
                        to_char(p_start,'DD.MM.YYYY') = $1", array('01.'.$p_date));
    if ($R == "")
	return -1;
    return $R;
}

/**
 * \brief Decode the html for the widegt richtext and remove newline
 * \param $p_html string to decode
 * \return the html code without new line
 */

function Decode($p_html)
{
    $p_html = noalyss_str_replace('%0D', '', $p_html);
    $p_html = noalyss_str_replace('%0A', '', $p_html);
    $p_html = urldecode($p_html);
    return $p_html;
}
/**
 * @brief transform the SQL for ANALYTIC table operation_analytique
 * @see sql_filter_per
 * @param string $p_sql
 */
function transform_sql_filter_per($p_sql)
{
    $result=noalyss_str_replace("j_tech_per in (select p_id from parm_periode  where","",$p_sql);
    $result=noalyss_str_replace("jr_tech_per in (select p_id from parm_periode  where","",$result);
    $result=noalyss_str_replace("j_tech_per = (select p_id from parm_periode  where  p_start "," oa_date ",$result);
    $result=noalyss_str_replace("p_start >= to_date","oa_date >= to_date",$result);
    $result=noalyss_str_replace("p_end <= to_date","oa_date <= to_date",$result);
 
    $result="( $result";
    return $result;
}
/**
 * \brief Create the condition to filter on the j_tech_per
 *        thanks a from and to date.
 * \param $p_cn database conx
 * \param $p_from start date (date)
 * \param $p_to  end date (date)
 * \param $p_form if the p_from and p_to are date or p_id
 * \param $p_field column name
 * \return a string containg the query
 */

function sql_filter_per($p_cn, $p_from, $p_to, $p_form='p_id', $p_field='jr_tech_per')
{

    if ($p_form != 'p_id' && $p_form != 'date')
    {
	echo_error(__FILE__, __LINE__, 'Mauvais parametres ');
	exit(-1);
    }
    $p_from=  sql_string($p_from);
    $p_to=  sql_string($p_to);
    $p_field=  sql_string($p_field);
    if ($p_form == 'p_id')
    {
        if ( isNUmber($p_from)==0 || isNUmber($p_to)==0){
            throw new Exception("SFP1"._("Nombre invalide")."\$p_from=$p_from \$p_to=$p_to");
        }
	// retrieve the date
	$pPeriode = new Periode($p_cn);
	$a_start = $pPeriode->get_date_limit($p_from);
	$a_end = $pPeriode->get_date_limit($p_to);
	if ($a_start==null||$a_end==null)
        {
            throw new Exception(__FILE__.__LINE__.sprintf(_('Attention periode 
		     non trouvee periode p_from= %s p_to_periode = %s'), $p_from, $p_to));
        }


        $p_from = $a_start['p_start'];
	$p_to = $a_end['p_end'];
    }else {
        if ( isDate($p_from)==NULL || isDate($p_to)==NULL){
            throw new Exception("SFP2"._("Date invalide")."\$p_from=$p_from \$p_to=$p_to");
        }
    }
    if ($p_from==$p_to)
    {
        $periode=" $p_field = (select p_id from parm_periode ".
                " where ".
                " p_start = to_date('$p_from','DD.MM.YYYY')) ";
    }
    else
    {
        $periode="$p_field in (select p_id from parm_periode ".
                " where p_start >= to_date('$p_from','DD.MM.YYYY') and p_end <= to_date('$p_to','DD.MM.YYYY')) ";
    }
    return $periode;
}

/**
 * \brief alert in javascript
 * \param $p_msg is the message
 * \param $buffer if false, echo directly and execute the javascript, if $buffer is true, the alert javascript
 * is in the return string
 * \return string with alert javascript if $buffer is true
 */

function alert($p_msg, $buffer=false)
{
    $r = '<script>';
    $r.= 'alert_box(\'' . j($p_msg) . '\')';
    $r.= '</script>';

    if ($buffer)
    {
        return $r;
    }
    echo $r;
}

/**
 * @brief set the lang thanks the _SESSION['g_lang'] var.
 */
function set_language()
{
    // desactivate local check
    if (defined("LOCALE")&&LOCALE==0)
    {
        return;
    }
    if (!isset($_SESSION[SESSION_KEY.'g_lang']))
    {
        return;
    }

    /*
     * If translation is not supported by current
     */
    if (!function_exists("bindtextdomain"))
    {
        return;
    }

    $dir = "";
    // set differently the language depending of the operating system
    if (what_os() == 1)
    {
	$dir = setlocale(LC_MESSAGES, $_SESSION[SESSION_KEY.'g_lang']);
	if ($dir == "")
	{
	    $g_lang = 'fr_FR.utf8';
	    $dir = setlocale(LC_MESSAGES, $g_lang);
	   // echo '<span class="notice">' . $_SESSION[SESSION_KEY.'g_lang'] . ' domaine non supporté</h2>';
	}
	bindtextdomain('messages', NOALYSS_HOME.'/lang');
	textdomain('messages');
	bind_textdomain_codeset('messages', 'UTF8');

	return;
    }
    // for windows
    putenv('LANG=' . $_SESSION[SESSION_KEY.'g_lang']);
    $dir = setlocale(LC_ALL, $_SESSION[SESSION_KEY.'g_lang']);
    bindtextdomain('messages', '.\\lang');
    textdomain('messages');
    bind_textdomain_codeset('messages', 'UTF8');
}

/**
 * @brief try to determine on what os you are running the pĥpcompte
 * server
 * @return
 *  0 it is a windows
 *  1 it is a Unix like
 */
function what_os()
{
    $inc_path = get_include_path();

    if (strpos($inc_path, ";") != 0)
    {
	$os = 0;   /* $os is 0 for windoz */
    }
    else
    {
	$os = 1;   /* $os is 1 for unix */
    }
    return $os;
}

/**
 * @brief shrink the date, make a date shorter for the printing
 * @param $p_date format DD.MM.YYYY
 * @return date in the format DDMMYY (size = 13 mm in arial 8)
 */
function shrink_date($p_date)
{
    $date = noalyss_str_replace('.', '', $p_date);
    $str_date = substr($date, 0, 4) . substr($date, 6, 2);
    return $str_date;
}
/**
 * @brief shrink the date, make a date shorter for the printing
 * @param $p_date format DD.MM.YYYY
 * @return date in the format DD.MM.YY (size = 13 mm in arial 8)
 */
function smaller_date($p_date)
{
    if (empty ($p_date)) return "";
    $str_date = substr($p_date, 0, 6) . substr($p_date, 8, 2);
    return $str_date;
}

/**
 * @brief format the date, when taken from the database the format
 * is MM-DD-YYYY
 * @param $p_date format
 * @exception 1 if invalid format 
 * DOMEntity@param
 * @return date in the format DD.MM.YYYY
 */
function format_date($p_date, $p_from_format = 'YYYY-MM-DD',$p_to_format='DD.MM.YYYY')
{
    if (empty($p_date)) {return $p_date;}
    if ($p_from_format == 'YYYY-MM-DD')
    {
        $date = explode('-', $p_date);
        if (count($date) != 3)
            return $p_date;
    }
    if ($p_from_format == 'DD.MM.YYYY')
    {
        $temp_date = explode('.', $p_date);
        if (count($temp_date) != 3)
            return $p_date;
        $date[0] = $temp_date[2]; // 0 is year
        $date[1] = $temp_date[1]; // 1 for month
        $date[2] = $temp_date[0]; // 2 for day
    }

    switch ($p_to_format)
    {
        case 'DD.MM.YYYY':
            $str_date = $date[2] . '.' . $date[1] . '.' . $date[0];
            break;
        case 'DD-MM-YYYY':
            $str_date = $date[2] . '-' . $date[1] . '-' . $date[0];
            break;
        case 'YYYY-MM-DD':
            $str_date = $date[0] . '-' . $date[1] . '-' . $date[2];
            break;
       case 'YYYYMMDD':
            $str_date = $date[0] . $date[1] . $date[2];
            break;
        case 'YYYY/MM/DD':
            $str_date = $date[0] . '/' . $date[1] . '/' . $date[2];
            break;
        case "DD.MM.YY":
            $str_date = $date[2] . '.' . $date[1] . '.' . substr($date[0],2,2);
            break;
        case "DD-MM-YY":
            $str_date = $date[2] . '-' . $date[1] . '-' . substr($date[0],2,2);
            break;
        default:
            throw new Exception(_("Format Invalide"),1);
            
		}
    return $str_date;
}



/**
 *@brief Should a dialog box when you are disconnected from an ajax call
 * propose to reload or to connect in another tab
 */
function ajax_disconnected($p_div)
{

    echo HtmlInput::title_box(_("Déconnecté"), $p_div);
    echo h2(_('Données non disponibles'), 'class="error" ');
    echo h2(_('Veuillez vous reconnecter soit dans une autre fenêtre soit '
            . ' en cliquant sur le bouton'), 'class="error"');
    // Reload button
    $reload=new IButton("reload");
    $reload->value=_("Se connecter");
    $reload->class="button";
    $reload->javascript='window.location.reload()';
    // Link to log in another tab
    echo '<p style="text-align:center">';
    echo $reload->input();
    echo HtmlInput::button_close($p_div,'button');
    echo '</p>';


}

/**
 * @brief Show the modules
 * @param int $selected module selected profile_menu.pm_id
 */
function show_module($selected)
{
    global $g_user;
    $cn = Dossier::connect();
    $amodule = $cn->get_array("select
	me_code,me_menu,me_url,me_javascript,p_order,me_type,me_description
	from v_all_menu
	where
	p_id=$1
	and p_type_display='M'
	order by p_order", array($g_user->get_profile()));

    if ($selected != -1)
    {
        $selected_module=$cn->get_value('select me_code from profile_menu where'
                . ' pm_id = $1 ', array($selected));
	require_once NOALYSS_TEMPLATE.'/module.php';
	$file = $cn->get_array("select me_file,me_parameter,me_javascript,me_type,me_description from v_all_menu
	    where pm_id=$1 and p_id=$2", array($selected,$g_user->get_profile()));
	if ( count($file ) == 0 )
	{
		echo '</div>';
		echo '</div>';
		echo '<div class="content">';
		echo_warning(_("Module inexistant")."[ $selected ] ");
		echo '</div>';
		exit();
	}
	if ($file[0]['me_file'] != '')
	{
	    if ($file[0]['me_parameter'] != "")
	    {
		// if there are paramter put them in superglobal
		$array=compute_variable($file[0]['me_parameter']);
		put_global($array);
	    }

		// if file is not a plugin, include the file, otherwise
		// include the plugin launcher
		if ($file[0]['me_type'] != 'PL')
			{
				require_once $file[0]['me_file'];
			}
			else
			{
				// nothing  : direct call to plugin
			}
	}
	if ( $file[0]['me_javascript'] != '')
	{
		create_script($file[0]['me_javascript']);
	}
    }
}
/**
 * @brief Find the default module or the first one
 * @var $g_user $g_user
 * @return default module (string)
 */
function find_default_module()
{
    global $g_user;
    $cn = Dossier::connect();

    $default_module = $cn->get_array("select me_code
	    from profile_menu join profile_user using (p_id)
	    where
	    p_type_display='M' and
	    user_name=$1 and pm_default=1", array($g_user->login));

	/*
	 * Try to find the smallest order for module
	 */
    if (empty($default_module))
    {
		$default_module = $cn->get_array("select me_code
	    from profile_menu join profile_user using (p_id)
	    where
	    p_type_display='M' and
	    user_name=$1 order by p_order limit 1", array($g_user->login));

		// if no default try to find the default menu
		if ( empty ($default_module))
		{
			$default_module = $cn->get_array("select me_code
			 from profile_menu join profile_user using (p_id)
			   where
			   p_type_display='E' and
			   user_name=$1 and pm_default=1 ", array($g_user->login));
			/*
			 * Try to find a default menu by order
			 */
			if (empty ($default_module))
			{
				$default_module = $cn->get_array("select me_code
				from profile_menu join profile_user using (p_id)
				where
				user_name=$1 and p_order=(select min(p_order) from profile_menu join profile_user using (p_id)
				where user_name=$2) limit 1", array($g_user->login, $g_user->login));
			}

			/*
			* if nothing found, there is no profile for this user => exit
			*/
			if (empty ($default_module))
			{
                            /* 
                             * If administrateur, then we insert a default profile (1)
                             * for him
                             */
                            if ( $g_user->admin == 1 )
                            {
                                $cn->exec_sql('insert into profile_user(user_name,p_id) values ($1,1) ',array($g_user->login));
                                return find_default_module();
                            }
                            echo_warning(_("Utilisateur n'a pas de profil, votre administrateur doit en configurer un dans C0SEC"));
                            exit();
			}
		}
		return $default_module[0]['me_code'];
    }

    if (count($default_module) > 1)
    {
		// return the first module found
		return $default_module[0]['me_code'];
    }
    elseif (count($default_module) == 1)
    {
	return $default_module[0]['me_code'];
    }
}

/**
 * @brief show the module
 * @param $module the $_REQUEST['ac'] exploded into an array
 * @param  $idx the index of the array : the AD code is splitted into an array thanks the slash
 */
function show_menu($module)
{
    if ($module == 0)return;
    global $level, $g_user;
    $http=new HttpInput();
    $access_code=$http->request("ac");
    $cn = Dossier::connect();
    /**
     * Show the submenus
     */
    $amenu = $cn->get_array("
        select 
            pm_id,
            me_code,
            pm_id_dep,
            me_file,
            me_javascript,
            me_url,
            me_menu,
            me_description,
            me_description_etendue
            from profile_menu 
            join menu_ref using (me_code) 
            where pm_id_dep=$1 and p_id=$2
	 order by p_order", array($module, $g_user->get_profile()));
    
    // There are submenuS, so show them
    if (!empty($amenu) && count($amenu) > 1)
    {
        $a_style_menu=array('topmenu','menu2','menu3');
        if ( $level >= count($a_style_menu))
            $style_menu='menu3';
        else {
            $style_menu=$a_style_menu[$level];
        }
	require NOALYSS_TEMPLATE.'/menu.php';
          $level++;
           return;
    } elseif (count($amenu) == 1)
    {
        // there is only one submenu so we include the code or javascript 
        // or we show the submenu
        if ( trim($amenu[0]['me_url']??"") != "" ||
             trim ($amenu[0]['me_file']??"") != "" ||
             trim ($amenu[0]['me_javascript']??"") != "" )
        {
		echo '<div class="topmenu">';
		echo h2info(_($amenu[0]['me_menu']));
		echo '</div>';
		$module = $amenu[0]['pm_id'];
                display_menu($module);
                $level++;
                return;
        } else {
           $url=$access_code.'/'.$amenu[0]['me_code'];
           echo '<a href="do.php?gDossier='.Dossier::id().'&ac='.$url.'">';
           echo _($amenu[0]['me_menu']);
           echo '</a>';
           $level++;
           return;
        }
    }
    
    // !!! this point should never be reached 
    // There is no submenu or only one
    if (empty($amenu) || count($amenu) == 1)
    {
        display_menu($module);
                
    }
    $level++;
}
/**
 * @brief Display a menu
 * @global type $g_user
 * @param type $p_menuid
 * @return type
 */
function display_menu($p_menuid)
{
    if ($p_menuid == 0) return;
    global $g_user;
    $cn=Dossier::connect();
    
    $file = $cn->get_array("
        select me_file,me_parameter,me_javascript,me_type
        from menu_ref
        join profile_menu using (me_code)
        join profile_user using (p_id)
        where
        pm_id=$1 and
        user_name=$2 and
        (me_file is not null or trim(me_file) <>'' or
        me_javascript is not null or trim (me_javascript) <> '')", array($p_menuid,$g_user->login));

    if (count($file)==0)
    {
            return;
    }

    if ($file[0]['me_file'] != "")
    {
            if ($file[0]['me_parameter'] !== "")
            {
                    // if there are paramter put them in superglobal
                    $array=compute_variable($file[0]['me_parameter']);
                    put_global($array);
            }
            \Noalyss\Dbg::echo_var(1,$file[0]['me_file']." ".$file[0]['me_parameter']);
            /*
             * Log the file we input to put in the folder test-noalyss for replaying it
             */
            if (LOGINPUT) {
                    $file_loginput=fopen($_ENV['TMP'].'/scenario-'.$_SERVER['REQUEST_TIME'].'.php','a+');
                    fwrite($file_loginput, "include '".$file[0]['me_file']."';");
                    fwrite($file_loginput,"\n");
                    fclose($file_loginput);
            }
            // if file is not a plugin, include the file, otherwise
            // include the plugin launcher
            if ( $file[0]['me_type'] != 'PL') {
                if (file_exists ($file[0]['me_file']) )
                {
                    require_once $file[0]['me_file'];
                } elseif ( file_exists(NOALYSS_INCLUDE.'/'.$file[0]['me_file'])) {
                    require_once NOALYSS_INCLUDE.'/'.$file[0]['me_file'];
                }else {                            
                    echo echo_warning(_("Fichier non trouvé"));
                }
            } else {
                    require 'extension_get.inc.php';
            }

            exit();
    } elseif ( $file[0]['me_javascript'] != '')
    {
        $js=  noalyss_str_replace('<DOSSIER>', dossier::id(), $file[0]['me_javascript']);
        echo create_script($js);
    } 

}
/**
 * @brief Send an header CSV with a filename
 * @param string $p_filename , file name , caution , it must be sanitized BEFORE calling this function
 */
function header_csv($p_filename)
{
  
    header('Pragma: public');
    header('Content-type: application/csv');
    header("Content-Disposition: attachment;filename=\"{$p_filename}\"",
            FALSE);
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Expires: Sun, 1 Jan 2000 12:00:00 GMT');
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').'GMT');
}
/**
 * @brief Put in superglobal (get,post,request) the value contained in
 * the parameter field (me_parameter)
 * @param $array [key] [value]
 */
function put_global($array)
{
    for ($i=0;$i<count($array);$i++)
    {
	$key=$array[$i]['key'];
	$value=$array[$i]['value'];
	$_GET[$key]=$value;
	$_POST[$key]=$value;
	$_REQUEST[$key]=$value;
    }
}
/**
 * @brief the string has the format a=b&c=d, it is parsed and an array[][key,value]
 * is returned
 * @param $p_string
 * @return $array usable in put_global
 */
function compute_variable($p_string)
{
    $array=array();
    if ($p_string == '') return $array;

    $var=explode("&",$p_string);
    if (empty ($var))	return $array;
    for ($i=0;$i < count($var);$i++)
    {
	$var2=explode('=',$var[$i]);
	$array[$i]['key']=$var2[0];
	$array[$i]['value']=$var2[1];
    }
    return $array;
}
function ajax_xml_error($p_code,$p_string)
{
    $html = escape_xml($p_string);
    header('Content-type: text/xml; charset=UTF-8');
		echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<code>$p_code</code>
<value>$html</value>
</data>
EOF;
}

function get_array_column($p_array,$key)
{
    $array=array();
    for ($i=0;$i<count($p_array);$i++)
    {
        $r=$p_array[$i];
        if ( isset($r[$key])) {
            $array[]=$r[$key];
        }
    }
    return $array;
}

/**
 * @brief This function create a ledger object and return the right one.
 * It uses the factory pattern
 * @param Database $p_cn
 * @param type $ledger_id 
 * @return Acc_Ledger
 * @throws Exception
 */
function factory_Ledger(Database &$p_cn, $ledger_id)
{
    $ledger=new Acc_Ledger($p_cn, $ledger_id);
    $type=$ledger->get_type();

    switch ($type)
    {
        case 'VEN':
            $obj=new Acc_Ledger_Sale($p_cn, $ledger_id);
            break;
        case 'ACH':
            $obj=new Acc_Ledger_Purchase($p_cn, $ledger_id);
            break;
        case 'FIN':
            $obj= new Acc_Ledger_Fin($p_cn, $ledger_id);
            break;
        case 'ODS':
            $obj=$ledger;
            break;

        default:
            throw new Exception('Ledger type not found');
    }
    return $obj;
}
/**
 * @brief Check if we use IE 8 or 9
 * @return int 1 for IE8-9;0 otherwise
 */
function is_msie()
{
    if ( strpos ($_SERVER['HTTP_USER_AGENT'],'MSIE 8.0')  != 0 ||
         strpos ($_SERVER['HTTP_USER_AGENT'],'MSIE 9.0')  != 0 )
       $is_msie=1;
    else
        $is_msie=0;
    return $is_msie;
}
/**
 *@brief  Record an error message into the log file of the server or in the log folder of NOALYSS
 * Record also the GET and POST data
 * @param  $p_message string message to display
 */
function record_log($p_message)
{
    $date= date ('Y-m-d');
    // variable: $handle_log resource on log file ,
    $handle_log=fopen(NOALYSS_BASE."/log/noalyss-{$date}.log","a+");

    if ($handle_log == false )
    {
        if ( isset ($_SERVER['REMOTE_ADDR']) ) error_log(sprintf("origin %s\n",$_SERVER['REMOTE_ADDR']));
        if ( isset ($_SERVER['HTTP_USER_AGENT']) ) error_log(sprintf("agent  %s\n",$_SERVER['HTTP_USER_AGENT']));
        if ( gettype ($p_message) == "object" && method_exists($p_message,"getTraceAsString") == 1) {
            $exc=$p_message;
            do {
                error_log("noalyss exception File [".$exc->getFile().":".$exc->getLine()."]",0);
                error_log("noalyss exception Message [".$exc->getMessage()."]",0);
                error_log("noalyss exception Code [".$exc->getCode()."]",0);
                error_log("noalyss exception Trace ".$exc->getTraceAsString(),0);
                error_log("------ ",0);
                $exc=$exc->getPrevious();
                if ($exc != null )fwrite ($handle_log,"*********************** Previous  *********************** \n");
            } while ($exc != null);
        } else {
            error_log("noalyss".var_export($p_message,true),0);

        }
        error_log("noalyss GET [".json_encode($_GET,0,10)."]");
        error_log("_POST [".json_encode($_POST,0,10)."]",0);
    } else {
         if ( isset ($_SERVER['REMOTE_ADDR']) )    fwrite($handle_log,sprintf("origin %s\n",$_SERVER['REMOTE_ADDR']));
        if  ( isset ($_SERVER['HTTP_USER_AGENT']) ) fwrite($handle_log,sprintf("agent  %s\n",$_SERVER['HTTP_USER_AGENT']));
            
        if ( gettype ($p_message) == "object" && method_exists($p_message,"getTraceAsString") == 1) {

            error_log("noalyss exception ".$p_message->getMessage(),0);
            error_log("noalyss exception".$p_message->getTraceAsString(),0);
        } else {
            error_log("noalyss".var_export($p_message,true),0);

        }
        
        $now=date ('Y-m-d H:i:s');
        fwrite ($handle_log,str_repeat("=", 80)."\n");
        fwrite ($handle_log,"ERROR: {$now}\n");
        fwrite($handle_log,"noalyss GET [".var_export($_GET,true)."]");
        fwrite ($handle_log,"\n");
        fwrite($handle_log,"_POST [".var_export($_POST,true)."]");
        fwrite ($handle_log,"\n");
        if ( gettype ($p_message) == "object" && method_exists($p_message,"getTraceAsString") == 1) {
            $exc=$p_message;
            do {
                fwrite($handle_log,"noalyss exception File [".$exc->getFile().":".$exc->getLine()."]");
                fwrite ($handle_log,"\n");
                fwrite($handle_log,"noalyss exception Message [".$exc->getMessage()."]");
                fwrite ($handle_log,"\n");
                fwrite($handle_log,"noalyss exception Code [".$exc->getCode()."]");
                fwrite ($handle_log,"\n");
                fwrite($handle_log,"noalyss exception Trace \n".$exc->getTraceAsString());
                fwrite ($handle_log,"\n");
                $exc=$exc->getPrevious();
                if ($exc != null )fwrite ($handle_log,"*********************** Previous  *********************** \n");
            } while ($exc != null);
        } else {
            fwrite($handle_log,"noalyss".var_export($p_message,true));
            fwrite ($handle_log,"\n");

        }

        fwrite ($handle_log,str_repeat("=", 80)."\n");
        fclose($handle_log);

    }

}
if(!function_exists('tracedebug')) {
  function tracedebug($file,$var, $label = NULL) {

    $tmp_file = sys_get_temp_dir().DIRECTORY_SEPARATOR.$file;
    $file_loginput=fopen( $tmp_file,'a+');
    if ( $file_loginput == false) { return;}
    $output = '';
    $output .= date('d-m-y H:i');
    if(!is_null($label)) {
      $output .= $label . ': ';
    }
    if ( gettype ($var) == 'object' && get_class($var)=='DOMDocument')
    {
      $var->formatOutput=true;
      $output.=$var->saveXML() .PHP_EOL;
    } else
    {
      $output .= print_r($var, 1) . PHP_EOL;
    }

    file_put_contents($tmp_file, $output, FILE_APPEND);
  }
}
/**
 * @brief encode the string for RTF, return a string
 * @param $p_string string to convert
 * @return string
 */
function convert_to_rtf($p_string)
{
    $result="";
    $p_string2=mb_convert_encoding($p_string,'ISO-8859-1','UTF-8');
    $p_string2=iconv('UTF-8','ISO-8859-1//IGNORE',$p_string);
 
    
    $nb_result=strlen($p_string2);
    for ($i = 0 ; $i < $nb_result ; $i++ ){
        if (ord($p_string[$i]) < 127 ) {
            $result.=$p_string[$i];
        } else {
            $result.='\u'.ord($p_string[$i]).chr(92).chr(39).'3f';
        }
    }
    return $result;
}
/**
 * @brief When it is needed to eval a formula , this function prevent the divide by zero.
 * the formula is a math operation to evaluate like : 1.0+2.0/1 (...) , it is used in "report", 
 * it removes the operation "divide by 0 "
 * 
 * @param string $p_formula string containing a operation to evaluate
 * 
 * @see Impress::parse_formula
 */
function remove_divide_zero($p_formula)
{
    $test=noalyss_str_replace(" ","",$p_formula).";";
    $p_formula=preg_replace("![0-9]+\.*[0-9]*/0\.{0,1}0*(\+|-|\*|/|;){1}!","0$1",$test);
    $p_formula=trim($p_formula,';');
    return $p_formula;
}

/**
 * @brief Create randomly a string
 * @param int $p_length length of the generate string
 */
function generate_random_string($p_length,$special=1)
{
    $string="";
    if ($special == 1)
        $chaine="abcdefghijklmnpqrstuvwxyABCDEFGHIJKLMNPQRSTUVWXY0123456789*/+-=";
    if ($special == 0)
        $chaine="abcdefghijklmnpqrstuvwxyABCDEFGHIJKLMNPQRSTUVWXY0123456789";
    $microtime=microtime(true)*microtime(true)*100;
    srand(0);
    srand((int)$microtime);
    for ($i=0; $i<$p_length; $i++)
    {
        $string .= $chaine[rand()%strlen($chaine)];
    }
    return $string;
}

/**
 *@brief  generate a string of p_car character and a input text with name p_ctl_name
 * work like a kind of captcha.The control code for checking is ctlcode.
 * You compare the content of the variable p_ctl_name with ctlcode
 * @param $p_ctl_name name of the HTML input text
 * @param $p_car length of the string
 */
function confirm_with_string($p_ctl_name,$p_car)
{
    $code=generate_random_string($p_car ,0);
    $r =  HtmlInput::hidden("ctlcode",$code);
    $r.='<span style="margin-left:1.2em;margin-right:1.2em;font-size:120%;font-weight:bold;border:navy solid 1px ; padding:0.5rem">'. $code.'</span>';
    $ctl=new IText($p_ctl_name);
    $r.=$ctl->input();
    return $r;
}
/**
 * @brief Find the menu marked as default in the given profile
 * @param number $pn_menu (profile_menu.id)
 */
function find_default_menu($pn_menu)
{
    $cn=Dossier::connect();
    $sql = '  select pm_id from profile_menu where pm_default =1 and pm_id_dep = $1';
    $aresult=$cn->get_array($sql, [$pn_menu]);
    if (empty($aresult)) {
        return 0;
    }
    return $aresult[0]['pm_id'];
}

/**
 * @brief Check if there is a default menu for this user and add it. The array is filling from 1 to 3
 * @verbatim
 * 
 * COMPTA              0   -   0 - 173
 * COMPTA/MENUACH      0   - 173 -   3
 * COMPTA/MENUACH/ACH  173 -   3 -  85
 * 
 * @endverbatim
 * 
 *
 * @param array $pa_menu if the array of option ; index pm_id_v1 , pm_id_v2 and pm_id_v3
 * 
 */
function complete_default_menu($pa_menu)
{
    $a_result=$pa_menu;
    // find the first one which is null
    if ($pa_menu[0]['pm_id_v2'] == 0) {
        $tmp=find_default_menu($pa_menu[0]['pm_id_v1']);
        if ( $tmp <> 0 ) {
            $a_result[0]['pm_id_v2']=$pa_menu[0]['pm_id_v1'];
            $a_result[0]['pm_id_v1']=$tmp;
        }
    }
    if ($pa_menu[0]['pm_id_v3'] == 0) {
         $tmp=find_default_menu($a_result[0]['pm_id_v1']);
        if ( $tmp <> 0 ) {
            $a_result[0]['pm_id_v3']=$a_result[0]['pm_id_v2'];
            $a_result[0]['pm_id_v2']=$a_result[0]['pm_id_v1'];
            $a_result[0]['pm_id_v1']=$tmp;
        }
    }
    return $a_result;
}
/**
 * @brief rebuild the access code
 * @see complete_default_menu
 * @param array of number $pan_code index row [0] =  index pm_id_v1 , pm_id_v2 and pm_id_v3
 */
function rebuild_access_code($pan_code) 
{
    if ( empty ($pan_code)) {return;}
    $s_result="";
    $cn=Dossier::connect();
    $an_code=['pm_id_v3','pm_id_v2','pm_id_v1'];
    $sep="";
    for ($i=0;$i<3;$i++)
    {
        $ix=$an_code[$i];
        $s_result.=$sep.$cn->get_value("select me_code from profile_menu where pm_id=$1",[ $pan_code[0][$ix] ]);
        $sep=($s_result != "" )?"/":"";
    }
    return $s_result;
}

/***
 *@brief Transform a http link into a clickable link
 */
function add_http_link($text)
{
    
    $ret=preg_replace("!http[s]{0,1}://[[:graph:]*]*!",'<a href="\\0" target="_blank">\0</a>',$text);
    
    return $ret;

}
//---------------------------------------------------------------------------------------------------------------------
//
// PHP 8.2 fails with these functions when a NULL is given as argument
//
//---------------------------------------------------------------------------------------------------------------------

function noalyss_strlen($p_string) :int{
    if ($p_string ==null ) return 0;
    else return strlen($p_string);
}
function noalyss_trim($p_string) {
    if ($p_string===null) return "";
    else return trim($p_string);
}
function noalyss_strlentrim($p_string) :int {
    if ($p_string ==null ) return 0;
    return strlen(trim($p_string));
}
function noalyss_str_replace($search,$replace,$string) {
    if ($string===null) return "";
    else return str_replace($search,$replace??"",$string);
}
function noalyss_bcsub($p_first,$p_second,$p_decimal=4)
{
    $p_first=(empty($p_first))?0:$p_first;
    $p_second=(empty($p_second))?0:$p_second;
    return bcsub($p_first,$p_second,$p_decimal);
}
function noalyss_bcadd($p_first,$p_second,$p_decimal=4)
{
    $p_first=(empty($p_first))?0:$p_first;
    $p_second=(empty($p_second))?0:$p_second;
    return bcadd($p_first,$p_second,$p_decimal);
}
function noalyss_strip_tags($p_string)
{
    if ($p_string===null) return "";
    return strip_tags($p_string);
}
function noalyss_bcmul($p_first,$p_second)
{
    $p_first=(empty($p_first))?0:$p_first;
    $p_second=(empty($p_second))?0:$p_second;
      return bcmul($p_first??0,$p_second??0);
}
function noalyss_round($p_first,$p_second)
{
    $p_first=(empty($p_first))?0:$p_first;
    $p_second=(empty($p_second))?0:$p_second;
    return round($p_first??0,$p_second??0);
}

/**
 *  @brief to avoid deprecated in PHP8.1 : explode cannot use a null
 * @param $separator
 * @param $string
 * @return array | empty string
 */
function noalyss_explode($separator,$string) {
    if ($string===null) return '';
    return explode($separator,$string);
}
/**
 * @brief compose a HTML string with phone
 * @param string $p_tel
 * @return false|string returns false if $p_tel is empty
 */
function phoneTo($p_tel) {
     if (!empty($p_tel)) {
     $r=sprintf('<a href="tel:%s">%s</a>',h($p_tel),h($p_tel));
     return $r;
     }
    return false;
}

/**
 * @brief compose a HTML string with email
 * @param string $p_email email or emails separated by a comma
 * @return false|string returns false if email not valide
 */
function mailTo($p_email) {
    if (empty($p_email )) return "";
    $nComma=preg_match("/,/",$p_email);
    if ( $nComma > 0) {
        $aEmail=explode(",",$p_email);
    } else {
        $aEmail[0]=$p_email;
    }
    $r="";
    foreach ($aEmail as $email) {
        if ( filter_var(trim($email),FILTER_VALIDATE_EMAIL) ) {
            $r.=sprintf('<a href="mailto:%s">%s</a> ',h($email),h($email));
        } else {
            $r.=sprintf("%s",h($email));
            $r.=Icon_Action::warnbulle(83);

        }

    }
    return $r;
}

/**
 * @brief compose a HTML string with fax
 * @param string $p_fax fax number
 * @return false|string returns false if $p_fax is empty
 */
function FaxTo($p_tel) {
    if (!empty($p_tel)) {
        $r=sprintf('<a href="fax:%s">%s</a>',h($p_tel),h($p_tel));
        return $r;
    }
    return false;
}
function linkTo($p_url)
{
    if (empty($p_url) ) return "";
    if (filter_var(trim($p_url,FILTER_VALIDATE_URL)) ) {
        return sprintf('<a href="%s" target="_blank">%s</a>',$p_url,$p_url);
    } else {
        return $p_url;
    }
}
/**
 * @brief When you want to prevent users to connect, create a file in  noalyss/ (NOALYSS_BASE) with the
 * message in Html
 * @param string $p_file file in NOALYSS_BASE
 */
function MaintenanceMode($p_file)
{
    if ( file_exists(NOALYSS_BASE."/".$p_file )) {
        include NOALYSS_BASE."/".$p_file;
        exit;
    }
}

/**
 * @brief returns an double array with the error found and code , if the count is 0 then the password is very strong, 5 means it is
 * empty ,4 weak, ... the array contains the errors, [msg]=>array message [code] => array of code
 * Codes are
 *        - 1 : too short
 *        - 2 : missing digit
 *        - 3 : missing lowercase  letter
 *        - 4 : missing uppercase  letter
 *        - 5 : too many time same   letter or symbol..
 *        - 6 : missing special char
 *
 * If the password is strong returns an empty array
 *
 * @param $password string
 * @code

 $error =  check_password_strength($password);
  if ( count($error['msg']) > 0 ) {
    echo "password to weak";
    foreach ($error['msg'] as $item_error) {
          echo "error $item_error";
    }

  } else {
    echo "OK password strong";
  }

 * @endcode
 */
function check_password_strength($password) {
    $errors=array();
    $error_code=array();

    $len=strlen($password??"");
    if ( $len < 8) {
        $errors[] = _("mot de passe de 8 lettres minimum");
        $error_code[]=1;
    }

    if (!preg_match("#[0-9]+#", $password)) {
        $errors[] = _("mot de passe doit inclure au moins un chiffre");
        $error_code[]=2;
    }

    if (!preg_match("#[a-z]+#", $password)) {
        $errors[] = _("mot de passe doit inclure au moins une minuscule");
        $error_code[]=3;
    }
    if (!preg_match("#[A-Z]+#", $password)) {
        $errors[] = _("mot de passe doit inclure au moins une majuscule");
        $error_code[]=4;
    }

    if ( $len > 0 ) {
        $cnt_diff=count(count_chars($password,1));
        $ratio_diff=$len/$cnt_diff;

        if ($ratio_diff > 2) {
            $errors[] = _("Trop souvent le(s) même(s) symbole(s)");
            $error_code[]=5;
        }
        $special_char=preg_replace('/[[:alnum:]]/','',$password);
        if ( strlen($special_char??"")==0)
        {
            $errors[] = _("mot de passe doit inclure au moins un caractére spécial '+-/*[...'");
            $error_code[]=6;

        }
    }

    return  array( 'msg'=>$errors, 'code'=>$error_code);
}
/**
 * @brief generate a strong random password
 * @param $car int length of the password, minimum 8
 *
 */
function generate_random_password($car):string
{
    $string="";
    $car=($car < 8 )?8:$car;
    $max_loop=20;$loop=0;
    do
    {
        $loop++;
        $string="";
        $chaine="abcdefghijklmnpqrstuvwxy";
       // srand( (int)microtime()*1020030);
        for ($i=0; $i<$car; $i++)
        {
            $string .= $chaine[rand()%strlen($chaine)];
        }
        $chaine="ABCDEFGHIJKLMNPQRSTUVWXY";
        for ($i=0;$i<2;$i++) {
            $string[rand()%$car]=$chaine[rand()%strlen($chaine)];;
        }
        $chaine="0123456789";
        for ($i=0;$i<2;$i++) {
            $string[rand()%$car]=$chaine[rand()%strlen($chaine)];;
        }
        $special_set="+-/*;,.=:&()[]";
        $special_car=$special_set[rand()%strlen($special_set)];
        $string[rand()%$car]=$special_car;
     //   echo $string."\n";
    }while ( count(check_password_strength($string)['msg'])> 0 && $loop<$max_loop);
    return $string;
}

/**
 * @brief removed invalid character when computing a filename, the suffix is kept
 * @param $filename String filename to sanitize
 * @param $with_date (bool) true add the date in the filename, false do not add it
 * @return string without offending char
 */
function sanitize_filename($filename,$with_date=true)
{
    // save the suffix
    $pos_prefix=strrpos($filename, ".");
    if ($pos_prefix==0)
    {
        $filename_suff=".pdf";
        $filename.=$filename_suff;
        $pos_prefix=strrpos($filename, ".");
    }
    else
        $filename_suff=substr($filename, $pos_prefix, strlen($filename));

    $filename=str_replace(array('/', '*', '<', '>', ';', ',', '\\', '.', ':', '(', ')', ' ', '[', ']'), "-", $filename);

    $filename_no=substr($filename, 0, $pos_prefix);
    if ( $with_date)
        $new_filename=strtolower($filename_no)."-".date("Ymd-Hi").$filename_suff;
    else
        $new_filename=strtolower($filename_no).$filename_suff;
    
    return $new_filename;
}
/**
 * @brief generate an UUID
 * @param $data(string) if null use randow
 * @return string
 */
function guidv4($data = null) {
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $data = $data ?? random_bytes(16);
    

    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
/**
 * @brief retrieve the index for the key percent, returns -1 if nothing found
 * @param $array (array) SubTotal
 * @param $key (string) name of the key 
 * @param $value (string) value to look for
 * @return int
 */
function find_idx($array,$key,$value) {
    if ( count($array) == 0 ) { return -1; }
    $nb_array=count($array);
    for($i=0;$i <$nb_array;$i++) {
        if ($array[$i][$key] == $value) { 
            return $i; 
        }
    }
    return -1;
}

function compute_letter_value()
    {
        global $aLetter,$aLetterValue;

        static $make_string=null;
        if ( $make_string == null ) {
            for ($i=65;$i!=91;$i++) {
                $make_string[chr($i)]=$i-55;
            }
            $aLetter=array_keys($make_string);
            $aLetterValue=array_values($make_string);
        }
    }
/**
 * @brief check that an IBAN is valid
 * @param $iban string, this parameter will change:remove of space, comma,...
 * @return bool false the IBAN is invalid, true IBAN is VALID
 */
function check_iban(&$iban): bool
{
    global $aLetter, $aLetterValue;
    if (trim($iban ?? "") == "")
        return false;

    //------------------------------------------------
    // Make the letter
    //------------------------------------------------
    static $make_string,$aLetter, $aLetterValue=null;
    
    if ( $make_string == null ) 
    {
        for ($i=65;$i!=91;$i++) 
        {
            $make_string[chr($i)]=$i-55;
        }
        $aLetter=array_keys($make_string);
        $aLetterValue=array_values($make_string);
    }

    $iban = strtoupper($iban);
    $iban=str_replace([" ", ",", ".", "-"], '', $iban);

    $first = substr($iban, 0, 4);
    $chain = substr($iban, 4) . $first;

    $replaced = str_replace($aLetter, $aLetterValue, $chain);

    // computed by slice of 10: mod function is limited
    $start = 0;
    $slice = 10;
    $result = "";
    $length = strlen($replaced);
    while ($start < $length)
    {
        $slice_string = $result . substr($replaced, $start, $slice);
        $result = $slice_string % 97;
        $start += $slice;
    }

    if ($result == 1)
        return true;

    return false;
}

/**
* @brief convert a value in Mbytes, kb ... in byte 
* @param (string) $p_value containing K , M, G
* @return int in bytes
*/
function convert_ini_unit($p_value)
{
    if ($p_value=="") return 0;
   $a_convert=["k"=>1024,"m"=>1024**2,"g"=>1024**3];

   $p_value=trim($p_value);
   $last=strtolower($p_value[strlen($p_value)-1]);
   $p_value=substr($p_value,0,strlen($p_value)-1);
   if ( isset($a_convert[$last])) {
       $p_value=$p_value*$a_convert[$last];
   }

   return $p_value;
}
/**
 * @brief convert an array KEY=>VALUE into a double array useable by \ISelect
 * @param $array (array) array key=>value
 * @return double array array (array('value'=>'KEY','label'=>VALUE),...))
 * @see ISelect
 */
function convert_array_select($array)
{
    if (count($array) == 0 ) return null;
    $a_ret=array();$i=0;
    foreach ($array as $key=>$value) {
        $a_ret[$i]['value']=$key;
        $a_ret[$i]['label']=$value;
        $i++;
    }
    return $a_ret;
}