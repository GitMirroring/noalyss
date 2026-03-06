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
// Copyright Author Dany dany  27 févr. 2026
/*
 * 
 */
if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');
/**
 * @file admin_logfile.inc
 * @brief Show log file and download them
 */
if ( ! defined ('ALLOWED_ADMIN')) { die (_('Non autorisé'));}
print '<div class="content">';
print '<h2 class="h-section">';
print _("Fichiers trace");
print "</h2>";
//print p(_("Fichiers trace"),' class="text-muted"');

$directory=dir( NOALYSS_BASE.DIRECTORY_SEPARATOR."log");
$a_file=[];
while ( $file = $directory->read()) {
    if (preg_match("/noalyss.*log/", $file)) {
        $a_file[]=$file;
    }
    
}
        
rsort($a_file);
$nb_file=count($a_file);
if ( $nb_file == 0) {
    echo p(_("Aucune donnée"),' class="notice"');
    return;
}
if ( $http->get("sa","string","x")=="dwn") 
{
    $file=$http->get("file");
    if (! in_array($file,$a_file ) )
    {
        echo_warning(sprintf("%s non trouvé",$file));
        record_log("admin_logfile : {$file} not found");
        return;
    }
    print "<ul class=\"aligned-block\">";
    print "<li>";
    print \HtmlInput::button_anchor(_("Retour"),  NOALYSS_URL."/admin-noalyss.php?action=logfile");
    print "</li>";
    print "<li>";
    print \HtmlInput::button_anchor(_("Télécharger").'<i class="icon icon-download"></i>'
            ,  NOALYSS_URL."/export.php?admin=1&action=logfile&file={$file}");
    
    print "</li>";
    print "</ul>";
    echo '<pre>';
    echo file_get_contents(NOALYSS_BASE.DIRECTORY_SEPARATOR."/log/{$file}");
    echo '</pre>';
    print "<ul class=\"aligned-block\">";
    print "<li>";
    print \HtmlInput::button_anchor(_("Retour"),  NOALYSS_URL."/admin-noalyss.php?action=logfile");
    print "</li>";
    print "<li>";
    print \HtmlInput::button_anchor(_("Télécharger").'<i class="icon icon-download"></i>'
            ,  NOALYSS_URL."/export.php?admin=1&action=logfile&file={$file}");
    
    print "</li>";
    print "</ul>";
    return;
}
echo '<ol>';
for ( $i=0;$i < $nb_file;$i++)
{
    $url=sprintf("%s?%s", NOALYSS_URL."/admin-noalyss.php"
            , http_build_query(["action"=>"logfile","sa"=>"dwn","file"=>$a_file[$i]]));
    printf ("<li><a href=\"%s\">%s</a></li>",$url,$a_file[$i]);
}
print "</ol>";