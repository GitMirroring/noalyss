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

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief search cards , accounting or analytic account
 */
$http=new HttpInput();
try
{
    $query=$http->request("query","string","");
    $target=$http->request("target");
}
catch (Exception $exc)
{
    die ($exc->getMessage());
}

echo HtmlInput::title_box(_("Résultat"), "search_account_div") ;
echo '<form method="GET" onsubmit="search_account_card(this);return false;"> ';
$search_query=new IText("query",$query);
echo $search_query->input();
$select_limit=new ISelect("select_limit");
$select_limit->value=array(
        array("label"=>"25","value"=>25),
        array("label"=>"50","value"=>50),
        array("label"=>"100","value"=>100),
        array("label"=>_("Tout"),"value"=>-1)
);
$select_limit->selected=$http->request('select_limit',"number",25);
echo _("Max")." ".$select_limit->input();
echo HtmlInput::hidden("op", "search_account_card");
echo HtmlInput::hidden("target",$target);
echo Dossier::hidden();
echo HtmlInput::submit(uniqid(), _("Cherche"));
echo '</form>';
echo '<p class="text-muted">';
echo _("Vous pouvez faire des opérations arithmétiques entre des postes comptables, des comptes analytiques et des fiches")
;
echo '</p>';
$array=array();
if ($query != "") {
   
    $array=$cn->get_array("
        select quick_code as s_code,vw_name||' '
            ||coalesce(vw_first_name,'') ||' '
            ||coalesce(vw_description,'') as s_name,'C' as s_type from vw_fiche_attr vfa
        where quick_code like upper('%'||$1||'%') or lower(vw_name) like '%'||$1||'%'
        union all
        select pcm_val, pcm_lib ,'A' from tmp_pcmn tp
        where pcm_val like  '%'||$1||'%'
        or lower(pcm_lib) like '%'||$1||'%'
        union all
        select po_name ,po_description , 'N' from poste_analytique pa
        where 
        po_name like '%'||$1||'%'
        or po_description like '%'||$1||'%'
        order by 1
     
",[$query]);
}
echo '<div id="search_account_card_result_div">';
printf("Résultat %s",count($array));
$max=$select_limit->selected;
$idx=0;
echo '<ul class="">';
$close=";document.getElementById('search_account_div').remove();";
foreach ($array as $item) {
    
    if ($max <> -1 && $idx >= $max) {
        break;
    }
    $idx++;
    echo '<li class="list-group-item">';
    switch ($item['s_type'])
    {
        case 'N':
            // analytic account
            $js=sprintf('onclick="document.getElementById(\'%s\').value+=\'{{%s}}\' ;%s"',$target,$item['s_code'],$close);

            break;
        case 'A':
            // Accounting
            $js=sprintf('onclick="document.getElementById(\'%s\').value+=\'[%s]\' ;%s"',$target,$item['s_code'],$close);
                

            break;
        case 'C':
            // card
            $js=sprintf('onclick="document.getElementById(\'%s\').value+=\'{%s}\' ;%s"',$target,$item['s_code'],$close);

            break;
        default:
            throw Exception ("Invalid result");
    } 
      
      echo '<input type="checkbox" '.$js.'>';
        echo h($item['s_code']);
        echo "  ";
        echo h($item['s_name']);
    echo '</li>';
}
echo '</ul>';
echo '</div>';
?>
<ul class="aligned-block">
    <?=HtmlInput::button_close("search_account_div")?>
</ul>