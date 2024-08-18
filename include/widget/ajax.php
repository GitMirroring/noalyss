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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 12/08/24
/*! 
 * \file
 * \brief manage Ajax call for widget , called from html/ajax_misc.php.
 * \par Variables are :
 *   *  action = action to perform
 *   *  w is the widget code or if empty , the Widget class will be used
 *
 * \par for Widget Class, possible actions are
 *
 *    * widget.display : display the widget param : user_widget_id, user_widget_code
 *    * widget.refresh
 *    * widget.manage
 *    * widget.save
 *    * widget.insert
 *
 */
global $cn;
$http=new HttpInput();

require_once 'widget.php';

try {
    $action=$http->request("action");
    $w=$http->request("w","string","widget");
} catch (\Exception $e) {
    echo $e->getMessage();
}

// action = display,
if ($action == 'widget.display') {
    try {
        $widget=\Noalyss\Widget\Widget::build_user_widget($http->request('user_widget_id'),$http->request("widget_code"));
        $widget?->display();

    } catch (\Exception $e) {
        echo $e->getMessage();
    }

    return;
}
// call from a widget
if ( $w != "widget") {
    // security
    // widget exists ? Protect against attack when w is a relative path to something else
    $count=$cn->get_value("select count(*) from widget_dashboard where wd_code=$1",[$w]);
    if ($count == 1 && file_exists(NOALYSS_INCLUDE."/widget/$w/ajax.php")) {
        require NOALYSS_INCLUDE."/widget/$w/ajax.php";
        return;
    }
}
// action == manage , display a dialog box to add , remove or change parameter of widget
// if must possible to add several time the same widget , example mini-report
if ( $action == 'widget.manage') {
    echo \HtmlInput::title_box(_("Elements"), 'widget_box_id');
    echo '<div style="padding:0.3rem">';
    echo span(_('Organiser les éléments en utilisant la souris (Drag & Drop) puis sauver'),'class="text-muted text-center"');
    \Noalyss\Widget\Widget::display_available();


    echo '<ul class="aligned-block">';
    echo '<li>'.\HtmlInput::button_close('widget_box_id','button').'</li>';
    echo '<li>'.\HtmlInput::button_action(_('Sauver'),'widget.save()').'</li>';
    echo '<li>'.\HtmlInput::button_action(_('Ajouter'),'widget.input()').'</li>';

    echo '<ul>';

    echo create_script("widget.create_sortable()");
    echo '</div>';
    return;
}
/**************************************************************************
 *
// save the order of the widget
 *************************************************************************/
if ( $action == 'widget.save') {
    $query=$http->request("param","string","");
    try {
        parse_str($query, $aWigdet);
        if (isset ($aWigdet['contain_widget']))
            \Noalyss\Widget\Widget::save($aWigdet['contain_widget']);
        else
            \Noalyss\Widget\Widget::save(array());

    } catch (\Exception $e) {
        echo "NOK";
        echo $e->getMessage();
    }
    return;
}
/**************************************************************************
 * refresh dashboard
 *
 *************************************************************************/
if ($action == 'widget.refresh') {
    require NOALYSS_TEMPLATE."/dashboard.php";
    return;
}
if ($action == "widget.input") {
    echo \HtmlInput::title_box(_("Elements à ajouter"), 'widget_box_select_id');
    echo '<div style="padding:0.3rem">';
    \Noalyss\Widget\Widget::select_available();
    echo '</div>';
    echo '<ul class="aligned-block">';
    echo '<li>'.\HtmlInput::button_close('widget_box_select_id','button').'</li>';
    echo '<ul>';
    echo '</div>';
    return;
}
/*************************************************************************
 * insert a new widget with param if any
 ************************************************************************/
if ($action == 'widget.insert') {
    // insert new widget in user_widget + list "contain_widget"
    $param = $http->request("param","string",null);
    $widget_code = $http->request("widget_code");
    $widget_id=$cn->get_value("select wd_id from widget_dashboard where wd_code=$1 ",[$widget_code]);
    if (empty($widget_id)) return;

    $user_widget_id = $cn->get_value("insert into user_widget(use_login,dashboard_widget_id,uw_parameter,uw_order)
values ($1,$2,$3,1000) returning uw_id",[$g_user->getLogin(),$widget_id,$param]);
        $widget=\Noalyss\Widget\Widget::build_user_widget($user_widget_id, $widget_code);
        $widget->input();
    return;
}