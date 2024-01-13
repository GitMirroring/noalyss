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
// Copyright (2018) Author Dany De Bontridder <dany@alchimerys.be>

/**
 * @file
 * @brief Utility , library of icon with javascript
 */

/**
 * @brief Utility , library of icon with javascript
 */
class Icon_Action
{

    /**
     * Display a icon with a magnify glass
     * @param string $id id of element
     * @param string $p_javascript
     * @param string $p_style optionnal HTML code
     * @return type
     */
    static function icon_magnifier($id, $p_javascript, $p_style="")
    {
        $r="";
        $r.=sprintf('<span  id="%s" class=" smallbutton icon" style="%s" onclick="%s">&#xf50d;</span>',
                $id, $p_style, $p_javascript);
        return $r;
    }
    /**
     * Display a icon with a magnify glass
     * @param string $id id of element
     * @param string $p_javascript
     * @param string $p_style optionnal HTML code
     * @return type
     */
    static function button_magnifier($id, $p_javascript, $p_style="")
    {
        $r="";
        $r.=sprintf('<input type="button"  id="%s" class=" smallbutton icon" style="%s" onclick="%s" value="&#xf50d;">',
                $id, $p_style, $p_javascript);
        return $r;
    }

    /**
     * 
     * @param type $id
     * @param type $p_javascript
     * @param type $p_style
     * @return type
     */
    static function icon_add($id, $p_javascript, $p_style="")
    {
        $r=sprintf('<input  class="smallbutton icon" onclick="%s" id="%s" type="button" %s value="&#xe828;">',
                $p_javascript, $id, $p_style);
        return $r;
    }

    /**
     * 
     * @param string $id
     * @param string $p_javascript
     * @param string opt $p_style
     * @return html string
     */
    static function clean_zone($id, $p_javascript, $p_style="")
    {
        $r=sprintf('<input class="smallbutton " onclick="%s" id="%s" value="X" %s type="button" style="">',
                $p_javascript, $id, $p_style
        );
        return $r;
    }

    /**
     * Display a info in a bubble, text is in message_javascript
     * @param integer $p_comment
     * @see message_javascript.php
     * @return html string
     */
    static function infobulle($p_comment)
    {
        $r='<span tabindex="-1" class="icon" style="cursor:pointer;display:inline;text-decoration:none;" onmouseover="showBulle(\''.$p_comment.'\')"  onclick="showBulle(\''.$p_comment.'\')" onmouseout="hideBulle(0)">';
        $r.="&#xf086;";
        $r.='</span>';

        return $r;
    }
    /**
     * Display a info in a bubble, text is given as parameter
     * @param string $p_comment
     * 
     * @return html string
     */
    static function tips($p_comment)
    {
        $p_comment=noalyss_str_replace("'",' ',$p_comment);
        $r='<span tabindex="-1" class="icon" style="cursor:pointer;display:inline;text-decoration:none;" onmouseover="displayBulle(\''.$p_comment.'\')"  onclick="displayBulle(\''.$p_comment.'\')" onmouseout="hideBulle(0)">';
        $r.="&#xf086;";
        $r.='</span>';

        return $r;
    }
    /**
     * Display a info in a bubble, text is given as parameter
     * @param string $p_comment
     * 
     * @return html string
     */
    static function comment($p_comment)
    {
        $p_comment=noalyss_str_replace("'",' ',$p_comment);
        $js=sprintf("displayBulle('%s')",$p_comment);
        
        $r=sprintf('<span tabindex="-1" class="icon" style="cursor:pointer;display:inline;text-decoration:none;" onmouseover="%s"  onclick="%s" onmouseout="hideBulle(0)">',
                $js,$js);
        $r.="&#xf0e5;";
        $r.='</span>';

        return $r;
    }
    /**
     * Display a icon ON
     * @param string $p_div id of  element
     * @param string $p_javascript
     * @param string $p_style optionnal HTML code
     * @return html string
     */
    static function iconon($p_id, $p_javascript, $p_style="")
    {
        $r=sprintf('<span  style="color:green;cursor:pointer" id="%s" class="icon" style="%s" onclick="%s">&#xf205;</span>',
                $p_id, $p_style, $p_javascript);
        return $r;
    }

    /**
     * Display a icon OFF
     * @param string $p_div id of  element
     * @param string $p_javascript
     * @param string $p_style optionnal HTML code
     * @return html string
     */
    static function iconoff($p_id, $p_javascript, $p_style="")
    {
        $r=sprintf('<span  style="color:red;cursor:pointer" id="%s" class="icon" style="%s" onclick="%s">&#xf204;</span>',
                $p_id, $p_style, $p_javascript);
        return $r;
    }

    /**
     * Return a html string with an anchor which close the inside popup. (top-right corner)
     * @param name of the DIV to close
     */
    static function close($p_div)
    {
        $r='';
        $r.=sprintf('<A class="icon text-danger"  onclick="removeDiv(\'%s\')">&#xe816;</A>',
                $p_div);
        return $r;
    }
    
    /**
     * Display a icon for fix or move a div 
     * @param string $p_div id of  the div to fix/move
     * @param string $p_javascript
     * @return html string
     */
    static function draggable($p_div)
    {
        $drag=sprintf('<span id="pin_%s" style="" class="icon " onclick="pin(\'%s\')" >'.UNPINDG.'</span>',
                $p_div, $p_div);
        return $drag;
    }

    /**
     * Display a icon for zooming
     * @param string $p_div id of  the div to zoom
     * @param string $p_javascript
     * @return html string
     */
    static function zoom($p_div, $p_javascript)
    {
        $r=sprintf('<span  id="span_%s" class="icon" onclick="%s">
                &#xf08e;</span>', $p_div, $p_javascript);
        return $r;
    }

    /**
     * Display a warning in a bubble, text is in message_javascript
     * @param integer $p_comment
     * @see message_javascript.php
     * @return html string
     */
    static function warnbulle($p_comment)
    {
        $r=sprintf('<span tabindex="-1" onmouseover="showBulle(\'%s\')"  onclick="showBulle(\'%s\')" onmouseout="hideBulle(0)" style="color:red" class="icon">&#xe80e;</span>',
                $p_comment, $p_comment);

        return $r;
    }

    /**
     * Return a html string with an anchor to hide a div, put it in the right corner
     * @param $action action action to perform (message)
     * @param $javascript javascript
     * @note not protected against html
     * @see Acc_Ledger::display_search_form
     */
    static function hide($action, $javascript)
    {
        $r='';
        $r.='<span id="hide" style="" class="icon text-danger"   onclick="'.$javascript.'">'.$action.'</span>';
        return $r;
    }
        /**
     * Return a html string with an anchor to hide a div, put it in the right corner
     * @param $action action action to perform (message)
     * @param $javascript javascript
     * @note not protected against html
     * @see Acc_Ledger::display_search_form
     */
    static function hide_icon( $p_id,$javascript)
    {
        $r='';
        $r.='<span id="'.$p_id.'"  class="icon"   onclick="'.$javascript.'">'."&#xe83b;".'</span>';
        return $r;
    }
    /**
     * Return a html string with an eye
     * @param type $javascript
     * @return string
     */
    static function show_icon($p_id,$javascript) {
        $r='';
        $r.='<span id="'.$p_id.'" class="icon"   onclick="'.$javascript.'">&#xe803;</span>';
        return $r;
        
    }
    /**
     * Display the icon of a trashbin
     * @param string $p_id DOMid 
     * @param string $p_javascript
     * @return htmlString
     */
    static function trash($p_id,$p_javascript) 
    {
        $r='<span id="'.$p_id.'" onclick="'.$p_javascript.'" class="smallicon icon">&#xe80f;</span>';
        return $r;
    }
    /**
     * Display the icon to modify a idem
     * @param type $p_id
     * @param type $p_javascript
     * @return string
     */
    static function modify($p_id,$p_javascript)
    {
        $r='<span id="'.$p_id.'" onclick="'.$p_javascript.'" class="smallicon icon" style="margin-left:5px">&#xe80d;</span>';
        
        return $r;
    }
    /**
     * Display the icon to modify a idem
     * @param type $p_id
     * @param type $p_javascript
     * @return string
     */
    static function validate($p_id,$p_javascript)
    {
        $r='<span id="'.$p_id.'" onclick="'.$p_javascript.'" class="smallicon icon" style="background-color:lightgrey; border:1px solid blue;padding:2px;margin:0px 1px 0px 1px" >&#xe844;</span>';
        
        return $r;
    }
    /**
     * Display the icon to modify a idem
     * @param type $p_id
     * @param type $p_javascript
     * @return string
     */
    static function cancel($p_id,$p_javascript)
    {
        $r='<span id="'.$p_id.'" onclick="'.$p_javascript.'"  style="background-color:lightgrey; border:1px solid blue;padding:2px;margin:0px 1px 0px 1px" class="smallicon icon" >&#xe845;</span>';
        
        return $r;
    }
    static function detail($p_id,$p_javascript)
    {
        $r=sprintf('<span id="%s" onclick="%s" class="smallicon icon" style="margin-left:5px">&#xe803;</span>',
                $p_id,
                $p_javascript);
        return $r;
    }

    static function detail_anchor($p_id,$url)
    {
        $r=sprintf('
    <A HREF="%s">
    <span id="%s"  class="smallicon icon" style="margin-left:5px">&#xe803;</span></A>',
            $url
                ,$p_id);

        return $r;
    }
    static function more($p_id,$p_javascript)
    {
        $r=sprintf('<span id="%s" onclick="%s" class="smallicon icon" style="margin-left:5px">&#xe824;</span>',
                $p_id,
                $p_javascript);
        return $r;
    }
    static function less($p_id,$p_javascript)
    {
        $r=sprintf('<span id="%s" onclick="%s" class="smallicon icon" style="margin-left:5px">&#xe827;</span>',
                $p_id,
                $p_javascript);
        return $r;
    }
    /**
     * When a mouse is over this or if you click on it , it will trigger the javascript
     * @param domid $p_id
     * @param string $p_javascript 
     * @return html string
     */
    static function menu_click($p_id,$p_javascript)
    {
        
        $r=sprintf('<input type="button"  id="%s" onclick="%s" class="smallbutton icon" value="&#xf142;" style="font-weigth:bolder">',
                $p_id,
                $p_javascript);
        return $r;
    }
    /**
     * Display the icon of a padlock to lock or unlock element
     * @param string $p_id DOMid 
     * @param string $p_javascript
     * @return htmlString
     */
    static function lock($p_id,$p_javascript) 
    {
        $lock_cur="&#xe831;";
        $lock_next="&#xe832;";
        
        $r=sprintf( '<span id="%s" is_locked="1" onclick="toggle_lock(\'%s\');%s" class="icon smallicon">%s</span>',
                $p_id,
                $p_id,
                $p_javascript, 
                $lock_cur);
        return $r;
    }
    /**
     * Display the icon of a trashbin
     * @param string $p_id DOMid 
     * @param string $p_javascript
     * @return htmlString
     */
    static function unlock($p_id,$p_javascript) 
    {
        
        $lock_cur="&#xe832;";
        
        $r=sprintf( '<span id="%s" is_locked="0" onclick="toggle_lock(\'%s\');%s" class="icon smallicon">%s</span>',
                $p_id,
                $p_id,
                $p_javascript, 
                $lock_cur);
        return $r;
    }    
    /**
     * Display the icon of a slider
     * @param string $p_id DOMid 
     * @param string $p_javascript
     * @return htmlString
     */
    static function slider($p_id,$p_javascript) 
    {
        
        $lock_cur="&#xf1de;";
        
        $r=sprintf( '<span id="%s" onclick="%s" class="icon smallicon">%s</span>',
                $p_id,
                $p_javascript, 
                $lock_cur);
        return $r;
    }    
    
    /**
     * Display a icon ON is $p_value == 1 otherwise OFF
     * @param string $p_div id of  element
     * @param string $p_javascript
     * @param string $p_style optionnal HTML code
     * @param integer 0 or 1 , 0 means OFF and 1 means ON
     * @return html string
     */
    static function icon_onoff($p_id,$p_javascript,$p_style,$p_value)
    {
        if ( $p_value == 1 ) { return \Icon_Action::iconon($p_id, $p_javascript,$p_style);}
        if ( $p_value == 0 ) { return \Icon_Action::iconoff($p_id, $p_javascript,$p_style);}
    }
    
    static function checked ($p_id,$p_javascript="",$p_classrange="") {
        $lock_cur="&#xe741;";
        $r=sprintf( '<span id="%s" onclick="%s" class="icon smallicon %s" >%s</span>',
                $p_id,
                $p_javascript, 
                $p_classrange,
                $lock_cur);
        return $r;
    }
    static function unchecked ($p_id,$p_javascript="",$p_classrange="") {
        $lock_cur="&#xf096";
        
        $r=sprintf( '<span id="%s" onclick="%s" class="icon smallicon %s" >%s</span>',
                $p_id,
                $p_javascript, 
                $p_classrange,
                $lock_cur);
        return $r;
    }
    static function checkbox ($p_id,$p_javascript="",$p_value=0,$p_classrange="") {
        if ( $p_value == 0 ) { return \Icon_Action::checked($p_id, $p_javascript,$p_classrange); }
        if ( $p_value == 1 ) { return \Icon_Action::unchecked($p_id, $p_javascript,$p_classrange);}
    }
    static function full_size($p_div) {
        $js=sprintf("full_size('%s')",$p_div);
        $icon="&#xe80a;";
        $r=sprintf('<span id="size_%s" onclick="%s" class="icon smallicon">%s</span>',
                $p_div,$js,$icon);
        return $r;
    }
    static function duplicate($p_js) {
        $r=sprintf('<span id="%s" onclick="%s" class="icon smallicon">%s</span>',
            uniqid(),$p_js,"&#xf0c5;");
        return $r;
    }
    static function card($p_js) {
        $r=sprintf('<span id="%s" onclick="%s" class="icon smallbutton">%s</span>',
            uniqid(),$p_js,"&#xe843;");
        return $r;
    }
    static function option($p_js) {
        $r=sprintf('<span id="%s" onclick="%s" class="icon smallicon">%s</span>',
            uniqid(),$p_js,"&#xf142;");
        return $r;
    }

    /**
     * @brief Increase size of input_text (p_domid) with p_domid
     * @param $p_domid domid of the input "text" element
     * @param $p_size   size of the element
     * @return string HTML string
     */
    static function longer($p_domid,$p_size) {
        $r=sprintf('<span id="%s_longer" '.
        ' onclick="enlarge_text(\'%s\',\'%s\') "'.
        ' class="icon smallicon">%s</span>',
        $p_domid,$p_domid,$p_size,"&#xf138");
        return $r;
    }
    /**
     * @brief Increase size of input_text (p_domid) with p_domid
     * @param $p_domid domid of the input "text" element
     * @param $p_size   size of the element
     * @return string HTML string
     */
    static function show_note($p_domid) {
        $r='<span id="'.uniqid().'" class="smallicon icon"  style="background-color:yellow" onclick="document.getElementById(\''.$p_domid.'\').show()">&#xf0f6;</span>';
        return $r;
    }

    /**
     * @brief hide or display an element, to be used for an accordon
     * @param $p_id ip of the icon
     * @param $p_id_to_hide element to hide or show
     * @return HTML string
     */
    static function toggle_hide($p_id,$p_id_to_hide)
    {
        $javascript=sprintf("toggleHideShow('%s','%s',true)",$p_id_to_hide,$p_id);
        $r=sprintf('<i id="%s" onclick="%s" class="smallicon icon   icon-down-open-2" style="margin-left:5px"></i>',
            $p_id,$javascript
            );
        return $r;
    }
}
