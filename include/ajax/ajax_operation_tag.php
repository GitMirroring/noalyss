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
// Copyright (2002-2020) Author Dany De Bontridder <danydb@noalyss.eu>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief manage the ajax calls for adding , deleting tag on operations
 */
try
{
    $op=$http->request('op');
    $jrn_id=$http->request('jrn_id', "number");
}
catch (Exception $ex)
{
    echo $ex->getMessage();
}

// Security : check ledger accessible
global $g_user;
$ledger=$cn->get_value("select jr_def_id from jrn where jr_id=$1", [$jrn_id]);

if ($g_user->get_ledger_access($ledger)!='W')
{
    record_log("AJT0001");
    return;
}
switch ($op)
{
    case 'operation_tag_select':
        //--------------------------------------------------------------------------------------------------
        // Display a list of available tag
        //--------------------------------------------------------------------------------------------------
        ob_start();
        $tag_operation=new Tag_Operation($cn);
        echo $tag_operation->select($ctl);

        //------------------- Propose to add a tag

        $js=sprintf("onclick=\"show_tag('%s','%s','%s','j')\"", Dossier::id(), '', '-1');
        if ($g_user->check_action(TAGADD)==1)
        {
            echo HtmlInput::button("tag_add", _("Ajout d'un tag"), $js);
        }
        echo HtmlInput::button_close("tag_div");

        $response=ob_get_clean();
        if (headers_sent()&& DEBUGNOALYSS > 0 )
        {
            echo $response;
        }
        else
        {
            header('Content-type: text/xml; charset=UTF-8');
        }
        $html=escape_xml($response);
        echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
        <data>
        <ctl></ctl>
        <code>$html</code>
        </data>
EOF;
        return;
    case 'operation_tag_add':
        //--------------------------------------------------------------------------------------------------
        // Display all the tags attached to this operation
        //--------------------------------------------------------------------------------------------------
        $tag_operation=new Tag_Operation($cn);
        $tag_operation->set_jrn_id($jrn_id);
        $pref=$http->request("ctl");

        if ($http->request("isgroup")=='t')
        {
            $tag_operation->tag_add($http->request('t_id', "number"));
        }
        else
        {
            // Add all the tag from the group 
            $aTag=$cn->get_array("select t_id,t_tag ,t_color from jnt_tag_group_tag jtgt  join tags on (tag_id=t_id) where tag_group_id=$1 order by 2 ",
                    [$http->request("t_id", "number")]);
            $nb_atag=count($aTag);
            if ($nb_atag>0)
            {
                for ($i=0; $i<$nb_atag; $i++)
                {
                    $tag_operation->tag_add($aTag[$i]['t_id']);
                }
            }
        }

        ob_start();

        $tag_operation->tag_cell($ctl);

        $response=ob_get_clean();
        if (headers_sent()&& DEBUGNOALYSS > 0)
        {
            echo $response;
        }
        else
        {
            header('Content-type: text/xml; charset=UTF-8');
        }
        $html=escape_xml($response);
        echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl></ctl>
<code>$html</code>
</data>
EOF;
        return;
        break;
    case 'operation_tag_remove':
        $tag_operation=new Tag_Operation($cn);
        $tag_operation->set_jrn_id($jrn_id);
        $pref=$http->request("ctl");
        $tag_operation->tag_remove($http->request("t_id", "number"));

        ob_start();

        $tag_operation->tag_cell($pref);

        $response=ob_get_clean();
        if (headers_sent()&& DEBUGNOALYSS > 0)
        {
            echo $response;
        }
        else
        {
            header('Content-type: text/xml; charset=UTF-8');
        }
        $html=escape_xml($response);
        echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl></ctl>
<code>$html</code>
</data>
EOF;
        return;
        break;
}
