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
// Copyright(2004) Dany De Bontridder danydb@aevalys.eu
/*!
 * \file
 * \brief lettering : included from include/category_card.inc.php, which is part of manager.inc.php, customer.inc.php,...
 *
 * some variable are already defined ($cn, $g_user ...)
 */
Noalyss\Dbg::echo_file(__FILE__);
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
global $g_user;
echo '<div class="content">';
$http=new HttpInput();
echo '<div id="search">';
echo '<FORM METHOD="GET">';
echo dossier::hidden();
echo HtmlInput::hidden('ac',$http->request('ac'));
echo HtmlInput::hidden('sb',$http->request('sb'));
echo HtmlInput::hidden('sc',$http->request('sc'));
echo HtmlInput::hidden('f_id',$http->request('f_id'));

echo '<table width="50%">';

// limit of the year
$exercice=$g_user->get_exercice();
$periode=new Periode($cn);
list($first_per,$last_per)=$periode->get_limit($exercice);

$start=new IDate('start');
$start->value=(isset($_GET['start']))?$http->get('start','date'):$first_per->first_day();
$r=td(_('Date début'));
$r.=td($start->input());
echo tr($r);

$end=new IDate('end');
$end->value=(isset($_GET['end']))?$http->get('end','date'):$last_per->last_day();
$r=td(_('Date fin'));
$r.=td($end->input());
echo tr($r);

// type of lettering : all, lettered, not lettered
$sel=new ISelect('type_let');
$sel->value=array(
                array('value'=>0,'label'=>_('Toutes opérations')),
                array('value'=>1,'label'=>_('Opérations lettrées')),
                array('value'=>2,'label'=>_('Opérations NON lettrées'))
            );
if (isset($_GET['type_let'])) $sel->selected=$http->get('type_let');
else $sel->selected=1;

$r= td("Filtre ").
    td($sel->input());

echo tr($r);
echo '</table>';
echo '<br>';
echo HtmlInput::submit("seek",_('Recherche'));
echo '</FORM>';
echo '</div>';

echo '<hr>';
//--------------------------------------------------------------------------------
// record the data
//--------------------------------------------------------------------------------
if ( isset($_POST['record']))
{
    $letter=new Lettering_Account($cn);
    $letter->save($_POST);
}
//--------------------------------------------------------------------------------
// Show the result
//--------------------------------------------------------------------------------
if ( isset($_GET['start']) && isset($_GET['end']))
  {
    if ( isDate($http->get('start') ) == null || isDate($http->get ('end') ) == null )
      {
	echo alert(_('Date malformée, désolé'));
	return;
      }
  }
echo '<div id="list">';
$fiche=new Fiche($cn, $http->request('f_id',"number"));
$quick_code=$fiche->get_quick_code();
$letter=new Lettering_Card($cn);
$letter->set_parameter('quick_code',$quick_code);
$letter->set_parameter('start',$start->value);
$letter->set_parameter('end',$end->value);

if ( $sel->selected == 0 )
    echo $letter->show_list('all');
if ( $sel->selected == 1 )
    echo $letter->show_list('letter');
if ( $sel->selected == 2 )
    echo $letter->show_list('unletter');

echo '</div>';
echo '<div id="detail" style="display:none">';
echo 'Un instant...';
echo '<IMG SRC=image/loading.gif>';
echo '</div>';
echo '</div>';
?>
