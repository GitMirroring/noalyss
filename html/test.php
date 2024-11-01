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


html_page_start();
global $http;
define ('TEST_UNIT',1);
$http=new HttpInput();

if (  ini_get('zend.assertions') != 1 ) {
 echo_warning(_('Attention zend.assertions devrait être activé'));
 echo p('Il faut changer dans votre fichier .htaccess ou php.ini');
 echo 'voir <a href="https://www.php.net/manual/fr/ini.core.php#ini.zend.assertions">https://www.php.net/manual/fr/ini.core.php#ini.zend.assertions</a>';    
}
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
$g_user=new Noalyss_user($cn);

if (!file_exists('authorized_debug'))
{
    echo "Pour pouvoir utiliser ce fichier vous devez creer un fichier nomme authorized_debug
    dans le repertoire html du server";
    exit();
}
define('ALLOWED', 1);
load_all_script();
// To enable assert , set "zend.assertions" in the php.ini file
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
 * Loading of all scenario HTML + XML
 */
function retrieve_files($directory) :array
{
    $scan=scandir($directory);
    $maxscan=count($scan);
    $cnt_scenario=0;$scenario=array();

    for ($e_scan=0; $e_scan<$maxscan; $e_scan++)
        {
            if (is_file("{$directory}/".$scan[$e_scan])&&strpos($scan[$e_scan], '.php')==true)
            {
                $description="";
                $a_description=file("{$directory}/".$scan[$e_scan]);
                $max_description=count($a_description);
                for ($w=0; $w<$max_description; $w++)
                {
                    if (strpos($a_description[$w], '@description:')==true)
                    {
                        $description=$a_description[$w];
                        $description=noalyss_str_replace('//@description:', '', $description);
                    }
                }
                $scenario[$cnt_scenario]['file']="{$directory}/".$scan[$e_scan];
                $scenario[$cnt_scenario]['desc']=$description;
                $cnt_scenario++;


            }
        }
    return $scenario;
}

$html_files=retrieve_files(NOALYSS_BASE.'/scenario/HTML');
$xml_files=retrieve_files(NOALYSS_BASE.'/scenario/XML');
$lib_files=retrieve_files(NOALYSS_BASE.'/scenario/LIB');

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

    echo h1('HTML');

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
    $nb_html=count($html_files);
    for ($e=0; $e<$nb_html; $e++)
    {

            $get='test.php?'.http_build_query(array('script'=>$html_files[$e]['file'], 'gDossier'=>$gDossierLogInput, 'description'=>$html_files[$e]['desc']));
            echo '<tr>';
            echo '<td>';
            echo $e;
            echo '</td>';
            echo '<td>';
            echo '<a href="'.$get.'" target="_blank">';
            echo basename($html_files[$e]['file']);
            echo '</a>';
            echo '</td>';
            echo '<td>'.$html_files[$e]['desc'].'</td>';
            echo '</tr>';

    }
    echo '</table>';

    echo h1('XML');
    echo '<table>';

    $nb_xml=count($xml_files);
    for ($e=0; $e<$nb_xml; $e++)
    {

        $get='test.php?'.http_build_query(array('script'=>$xml_files[$e]['file'], 'gDossier'=>$gDossierLogInput, 'description'=>$xml_files[$e]['desc']));
        echo '<tr>';
        echo '<td>';
        echo $e;
        echo '</td>';
        echo '<td>';
        echo '<a href="'.$get.'" target="_blank">';
        echo basename($xml_files[$e]['file']);
        echo '</a>';
        echo '</td>';
        echo '<td>'.$xml_files[$e]['desc'].'</td>';
        echo '</tr>';

    }
    echo '</table>';


    echo h1('Libraries');
    echo '<table>';

    $nb_lib=count($lib_files);
    for ($e=0; $e<$nb_lib; $e++)
    {

        $get='test.php?'.http_build_query(array('script'=>$lib_files[$e]['file'], 'gDossier'=>$gDossierLogInput, 'description'=>$lib_files[$e]['desc']));
        echo '<tr>';
        echo '<td>';
        echo $e;
        echo '</td>';
        echo '<td>';
        echo '<a href="'.$get.'" target="_blank">';
        echo basename($lib_files[$e]['file']);
        echo '</a>';
        echo '</td>';
        echo '<td>'.$lib_files[$e]['desc'].'</td>';
        echo '</tr>';

    }
    echo '</table>';
}
else if ($script=='all')
{
    $nb=$http->get('nb_script', "number",0);
    
            $start_mem=memory_get_usage();
            $start_time=microtime(true);
            $script=noalyss_str_replace('../', '', $script);
    
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
    $description=$http->get("description","string", "aucune description");
    echo '<h1>'.$script."</h1>";
    echo '<p> description = '.$description.'<p>';
    include $script;

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
