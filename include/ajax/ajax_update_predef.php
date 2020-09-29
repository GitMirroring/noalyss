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
 * \brief respond ajax request, the get contains
 *  the value :
 * - l for ledger 
 * - gDossier
 * Must return at least tva, htva and tvac
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');

require_once NOALYSS_INCLUDE.'/constant.php';
require_once NOALYSS_INCLUDE.'/class/database.class.php';
require_once NOALYSS_INCLUDE.'/class/dossier.class.php';
require_once NOALYSS_INCLUDE.'/class/pre_operation.class.php';

// Check if the needed field does exist
extract ($_GET, EXTR_SKIP);
foreach (array('l','t','d','gDossier') as $a)
{
    if ( ! isset (${$a}) )
    {
        echo "error $a is not set ";
        exit();
    }

}
$cn=Dossier::connect();
$op=new Pre_operation($cn);
$op->set_p_jrn($l);
$op->set_jrn_type($t);
$op->set_od_direct($d);
$url=http_build_query(array('action'=>'use_opd','p_jrn_predef'=>$l,'ac'=>$_GET['ac'],'gDossier'=>dossier::id()));
$html="";

$html.=HtmlInput::title_box(_("Modèle d'opérations"), 'modele_op_div', 'hide',"","n");
$html.=$op->display_list_operation('do.php?'.$url);
$html.=' <p style="text-align: center">'.
        HtmlInput::button_hide('modele_op_div').
        '</p>';
$html=escape_xml($html);
header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<code></code>
<value>$html</value>
</data>
EOF;

?>

