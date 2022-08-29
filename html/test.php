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
/*!\file
 * \brief this file let you debug and test the different functionnalities, there are 2 important things to do
 * It is only a quick and dirty testing. You should use a tool as PHPUNIT for the unit testing
 * 
 *  - first do not forget to create the authorized_debug file in the html folder
 *  - secund the test must be adapted to this page : if you do a post (or get) from a test, you won't get any result
 * if the $_REQUEST[test_select] is not set, so set it . 
 */



include_once("../include/constant.php");
include_once("lib/ac_common.php");
require_once('class/database.class.php');
require_once ('class/dossier.class.php');
require_once('lib/html_input.class.php');
require_once('lib/icon_action.class.php');
require_once ('lib/function_javascript.php');
require_once 'class/user.class.php';
require_once NOALYSS_INCLUDE.'/lib/http_input.class.php';
html_page_start();
global $http;

$http=new HttpInput();


$gDossier=$http->request('gDossier',"number", -1);
if ($gDossier==-1)
{
    echo " Vous devez donner le dossier avec paramètre gDossier dans l'url, exemple http://localhost/noalyss/html/test.php?gDossier=25";
    exit();
}
$gDossierLogInput=$gDossier;
global $cn, $g_user, $g_succeed, $g_failed;
$cn=Dossier::connect();

$g_parameter=new Noalyss_Parameter_Folder($cn);
$g_user=new User($cn);

if (!file_exists('authorized_debug'))
{
    echo "Pour pouvoir utiliser ce fichier vous devez creer un fichier nomme authorized_debug
    dans le repertoire html du server";
    exit();
}
define('ALLOWED', 1);
load_all_script();
// To enable assert , set "zend.assertions" in the php.ini file
ini_set("assert.active",1);
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 1);
assert_options(ASSERT_QUIET_EVAL, 1);
function my_assert_handler($file, $line, $code)
{
    echo "<hr>Assert Failed :
        File '$file'<br />
        Line '$line'<br />
        Code '$code'<br /><hr />";
}
assert_options(ASSERT_CALLBACK, 'my_assert_handler');
/******************************************************************************************************************/
/*  Utilities 
/******************************************************************************************************************/

/** 
 * Return the card this most activities
 * @return integer (fiche.f_id)
 */
function get_card_with_activity() {
    global $cn;
    $card_count=$cn->get_array("select count(*),f_id ". 
        " from jrnx ".
        " where ". 
        " f_id is not null ".
        "group by f_id order by count(*) desc");
    return $card_count[0]['f_id'];
}

/*
 * Loading of all scenario
 */
$scan=scandir('../scenario/');
$maxscan=count($scan);
$cnt_scenario=0;$scenario=array();

for ($e_scan=0; $e_scan<$maxscan; $e_scan++)
    {
        if (is_file('../scenario/'.$scan[$e_scan])&&strpos($scan[$e_scan], '.php')==true)
        {
            $description="";
            $a_description=file('../scenario/'.$scan[$e_scan]);
            $max_description=count($a_description);
            for ($w=0; $w<$max_description; $w++)
            {
                if (strpos($a_description[$w], '@description:')==true)
                {
                    $description=$a_description[$w];
                    $description=str_replace('//@description:', '', $description);
                }
            }
            $scenario[$cnt_scenario]['file']=$scan[$e_scan];
            $scenario[$cnt_scenario]['desc']=$description;
            $cnt_scenario++;
            
            
        }
    }
$script=$http->request('script', "string",'');
$min=$cn->get_value("select p_id from parm_periode order by p_start asc limit 1");
$max=$cn->get_value("select p_id from parm_periode order by p_start desc limit 1");
printf ("Max période %s Min période %s",$max,$min);

if ($script=="")
{
    echo "<h1>Test NOALYSS</h1>";
    /*
     * cherche pour fichier a include, s'il y en a alors les affiche
     * avec une description
     */
    

    echo '<table>';
    $get='test.php?'.http_build_query(array('script'=>"all", 'gDossier'=>$gDossierLogInput, 'description'=>"Tous les scripts"));
    echo '<tr>';
    echo '<td>';
    echo '<a href="'.$get.'" target="_blank">';
    echo "Tous ";
    echo '</a>';
    echo '</td>';
    echo '<td>Tous les scripts</td>';
    echo '</tr>';

    for ($e=0; $e<$cnt_scenario; $e++)
    {

            $get='test.php?'.http_build_query(array('script'=>$scenario[$e]['file'], 'gDossier'=>$gDossierLogInput, 'description'=>$scenario[$e]['desc']));
            echo '<tr>';
            echo '<td>';
            echo $e;
            echo '</td>';
            echo '<td>';
            echo '<a href="'.$get.'" target="_blank">';
            echo $scenario[$e]['file'];
            echo '</a>';
            echo '</td>';
            echo '<td>'.$scenario[$e]['desc'].'</td>';
            echo '</tr>';
        
    }
    echo '</table>';
}
else if ($script=='all')
{
    $nb=$http->get('nb_script', "number",0);
    
            $start_mem=memory_get_usage();
            $start_time=microtime(true);
            $script=str_replace('../', '', $script);
    
            echo '<h1>'.$nb." ".$scenario[$nb]['file']."</h1>";
            echo '<h2> description = '.$scenario[$nb]["desc"].'</h2>';
            include '../scenario/'.$scenario[$nb]['file'];
            echo '</div>';
            echo '</div>';
            $end_mem=memory_get_usage();
            $end_time=microtime(true);

            echo "<p>start mem : ".$start_mem;
            echo '</p>';
            echo "<p>end mem : ".$end_mem;
            echo '</p>';
            echo "<p>Diff = ".($end_mem-$start_mem)." bytes ";
            echo "<p>Diff = ".(round(($end_mem-$start_mem)/1024, 2))." kbytes ";
            echo "<p>Diff = ".(round(($end_mem-$start_mem)/1024/1024, 2))." Mbytes ";
            echo '</p>';
            echo "<p>Execution script ".$script." time = ".(round(($end_time-$start_time), 4))." secondes</p>";
            $nb++;
            if      ( $nb == $maxscan ) {
                echo "Dernier test";
            } else {
            $get='test.php?'.http_build_query(array('script'=>"all", 'gDossier'=>$gDossierLogInput, 'nb_script'=>$nb));
             echo '<a href="'.$get.'" target="_blank">';
            echo $scenario[$nb]['file'];
            }
}
else
{
    $start_mem=memory_get_usage();
    $start_time=microtime(true);
    $script=str_replace('../', '', $script);
    $description=$http->get("description","string", "aucune description");
    echo '<h1>'.$script."</h1>";
    echo '<p> description = '.$description.'<p>';
    include '../scenario/'.$script;

    $end_mem=memory_get_usage();
    $end_time=microtime(true);

    echo "<p>start mem : ".$start_mem;
    echo '</p>';
    echo "<p>end mem : ".$end_mem;
    echo '</p>';
    echo "<p>Diff = ".($end_mem-$start_mem)." bytes ";
    echo "<p>Diff = ".(round(($end_mem-$start_mem)/1024, 2))." kbytes ";
    echo "<p>Diff = ".(round(($end_mem-$start_mem)/1024/1024, 2))." Mbytes ";
    echo '</p>';
    echo "<p>Execution script ".$script." time = ".(round(($end_time-$start_time), 4))." secondes</p>";
}    