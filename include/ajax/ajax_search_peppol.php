<?php
/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
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
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2016) Author Dany De Bontridder <dany@alchimerys.be>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief search Peppol ID. 
 * 
 * Parameters : 
 *     - query : string to search
 *     - ctl   : DOMID to udpate (useless)
 *     - filter: query on name , VAT id  or Enterprise ID
 */
$http = new HttpInput();
try {
    /* @var $query (string) string to search */
    $query = $http->request('query', 'string', '');

    /* @$ctl_id (string) DOM ID of the element to update */
    $ctl_id = $http->request('ctl', 'string');

    /* @filter (string)  search on name, VAT ID or enterprise ID */
    $filter = $http->request('filter', 'string', '');
} catch (Exception $e) {
    echo $e->getMessage();
    return;
}

/**
 * @class
 * @brief Found PEPPOL Elements, inner class
 */
class PEPPOL_Match_Record
{

    var $participantID;
    var array $docTypeID;
    var $entity;
}

class PEPPOL_Entity_Record
{
    var $name;
    var $countryCode;
    var $regDate;
    var $additionalInfo;
    var array $website;
    var array $contact;
}
class PEPPOL_Entity_Contact {
    var $type;
    var $name;
    var $phone;
    var $email;
            
}
$msg = _("La recherche par nom et numéro de TVA sont limitées à la Belgique");

echo \HtmlInput::title_box(_("Recherche PEPPOL Directory"), 'peppol_id_search_div');
?>
<div class="content">
<form method="GET" id="peppol_id_search_div_frm" onsubmit="category_card.display_search_peppol('<?= $ctl_id ?>');return false;">
    <p class="text-muted">
    <?= $msg ?>
    </p>
    <?php
    echo \HtmlInput::hidden("op", "search_peppol");
    echo \HtmlInput::hidden("ctl_id", $ctl_id);

    $input_query = new IText("query", $query);
    $select_filter = new ISelect("filter");
    $select_filter->transform(["vatid" => _("Numéro de TVA")
                             , "entid" => _("Numéro entreprise")
                             , "peppolid" => _("Endpoint (PEPPOL ID)")
                             , "name" => _("Nom")
                            ]);
    $select_filter->selected = $filter
    ?>
<?= $select_filter->input() ?>
            <?= $input_query->input() ?>
    <ul class="aligned-block">
        <li>
            <?php
            echo \HtmlInput::submit("search", _("Chercher"));
            ?>
        </li>
        <li>
            <?php
            echo \HtmlInput::button_close('peppol_id_search_div');
            ?>
        </li>
    </ul>
</form>
<?php
if (trim($query) == '' || $filter=='')
{
    echo '</div>'; // div class content
    return;
}
//------------------------------------------------
// if a query has been submitted, show the result
//------------------------------------------------
$search = null;
switch ($filter)
{
    case 'name':
        $search = http_build_query(["name" => $query, 'countryCode' => 'BE']);
        break;
    case "vatid":
        if (stripos("x" . $query, "BE") == 0)
        {
            print _("Numéro de TVA invalide: doit commencer par BE");
            break;
        }
        $search = http_build_query(["participant" => 'iso6523-actorid-upis::9925:' . $query]);
        break;
    case 'entid':
        if (strlen(trim($query ?? "")) != 10)
        {
            print _("Numéro entreprise belge valide: 10 chiffres");
            break;
        }
        $search = http_build_query(["participant" => 'iso6523-actorid-upis::0208:' . $query]);
        break;
    case 'peppolid':
        $search = http_build_query(["participant" => 'iso6523-actorid-upis::' . $query]);
        break;
    default:
    throw new \Exception("ASP129 unknown filter",129);
}
try {
    $curl = curl_init("https://directory.peppol.eu/search/1.0/xml?{$search}");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $str = curl_exec($curl);
    if ( curl_errno($curl) != 0 ) {
        $info = "AJX147 connexion fails";
        if ($curl != null)
        {
            $info = curl_error($curl) . "\n----\n";
            $info .= var_export(curl_getinfo($curl), true);
        }
        throw new \Exception($info,147);
    }
        
   curl_close($curl);
} catch (\Exception $e) {
    \record_log($e);
    echo p(_("Vérification impossible"));
    return;
}

$xml = new DOMDocument();
$xml->loadXML($str);
file_put_contents("/tmp/alchimerys.xml",$xml->saveXML());
$root = $xml->getElementsByTagName("resultlist");
if (count($root) == 0)
{
    echo span(_("Aucun résultat"),' class="notice" ');
    echo '</div>'; // div class content
    return ;
} else
{
    printf(_("Résultat %d"), $root[0]->getAttribute("total-result-count"));
}

$a_match = $xml->getElementsByTagName("match");
$nb_match = $a_match->count();

$result=array();
for ($i = 0; $i < $nb_match; $i++)
{
    $node = $a_match->item($i);
    $obj = new PEPPOL_Match_Record();
    for ($e = 0; $e < $node->childElementCount; $e++)
    {
        if ($node->childNodes->item($e)->nodeType != XML_ELEMENT_NODE) 
        {
            continue;
        }
        if ($node->childNodes->item($e)->tagName == 'participantID')
        {
            $obj->participantID = $node->childNodes->item($e)->textContent;
        } elseif ($node->childNodes->item($e)->tagName == 'docTypeID')
        {
            $obj->docTypeID[] = $node->childNodes->item($e)->textContent;
        } elseif ($node->childNodes->item($e)->tagName == 'entity')
        {
            $obj->entity = new PEPPOL_Entity_Record();
            $obj->entity->contact=array();
             $obj->entity->website=array();
            for ($f=0;$f< $node->childNodes->item($e)->childElementCount;$f++)
            {
                if ( $node->childNodes->item($e)->childNodes->item($f)->tagName == 'name')
                    $obj->entity->name=$node->childNodes->item($e)->childNodes->item($f)->textContent;
                if ($node->childNodes->item($e)->childNodes->item($f)->tagName=='countryCode')
                    $obj->entity->countryCode=$node->childNodes->item($e)->childNodes->item($f)->textContent;
                if ($node->childNodes->item($e)->childNodes->item($f)->tagName=='regDate')
                    $obj->entity->regDate=$node->childNodes->item($e)->childNodes->item($f)->textContent;
                if ($node->childNodes->item($e)->childNodes->item($f)->tagName=='additionalInfo')
                    $obj->entity->additionalInfo=$node->childNodes->item($e)->childNodes->item($f)->textContent;
                if ($node->childNodes->item($e)->childNodes->item($f)->tagName=='additionalInfo')
                    $obj->entity->additionalInfo=$node->childNodes->item($e)->childNodes->item($f)->textContent;
                if ($node->childNodes->item($e)->childNodes->item($f)->tagName=='website')
                    $obj->entity->website[]=$node->childNodes->item($e)->childNodes->item($f)->textContent;
                if ($node->childNodes->item($e)->childNodes->item($f)->tagName=='contact')
                    {  $a=array();
                        $a['type']=$node->childNodes->item($e)->childNodes->item($f)->getAttribute("type");
                        $a['name']=$node->childNodes->item($e)->childNodes->item($f)->getAttribute("name");
                        $a['phone']=$node->childNodes->item($e)->childNodes->item($f)->getAttribute("phone");
                        $a['email']=$node->childNodes->item($e)->childNodes->item($f)->getAttribute("email");
                        
                        $obj->entity->contact[]=$a;
                    }
            }

        }
    }
    if ($obj->participantID != null)        $result[]=clone $obj;

}
if (empty($result)) 
{
    echo span(_("Aucun résultat"),' class="notice" ');
    return;
}

//------------------------------------------------
// display result
//------------------------------------------------
$nb_result=count($result);
?>
    
<?php
for ($i=0;$i < $nb_result;$i++)
{
    $str_url="";
    $nb_website=count ($result[$i]->entity->website);
  
    ($result[$i]->entity->website != "") ? :"";
?>
<div style="display:flex;align-content: space-evenly">
<div style="width:20rem;">
    <a href="javascript:void(0)" onclick="$('<?=$ctl?>').value='<?=$result[$i]->participantID?>';removeDiv('peppol_id_search_div')">
    <?=$result[$i]->participantID?>
    </a>
</div>
<div>
    <?=$result[$i]->entity->name?>
    (<?=$result[$i]->entity->countryCode?>)
</div>

    
</div>
    <div style="display:flex;align-content: space-evenly;gap:1rem">
     <div style="display:flex;flex-direction:column">
         <b> website :</b> 
        <?php  if ( $nb_website != 0)
            {
                for ($x=0;$x < $nb_website;$x++ ) {
                    $str_url.=sprintf('<div><a href="%s" target="_blank" class="line">%s</a></div>',$result[$i]->entity->website[$x],$result[$i]->entity->website[$x]);
                }
            }
    ?>
        <?=$str_url?>
</div>
<div style="display:flex;flex-direction:column">
    <b>contact : </b>
        <?php
        $nb_contact=count($result[$i]->entity->contact);
        for ($h=0;$h< $nb_contact;$h++) {
            $email=mailTo($result[$i]->entity->contact[$h]['email']);
        ?>
    <div>
            <?=$result[$i]->entity->contact[$h]['type']?>
            <?=$result[$i]->entity->contact[$h]['name']?>
            <?=$result[$i]->entity->contact[$h]['phone']?>
            <?=$email?>
    </div>
    <?php
        } // LOOP h : for ($h=0;$h< $nb_contact;$h++) {
    ?>
</div>
<div>
    <b>Info:</b>
        <?=$result[$i]->entity->additionalInfo?>
</div>   
    </div>
<?php
} // end for ($i)
echo '</div>'; // div class content
?>
    