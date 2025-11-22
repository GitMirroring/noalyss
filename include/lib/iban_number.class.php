<?php
/* 
 * Copyright (C) 2025 dany
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/*! 
 * \file
 * \brief input of the IBAN Number of a supplier / customer, propose a button to check 
 */

class IBan_Number extends \HtmlInput
{
    private $itext;

    function __construct($name = '', $value = '', $p_id = "")
    {

        $p_id = ($p_id=="")?uniqid("ibannumber"):$p_id;

        $this->itext = new IText($name,$value,$p_id);
        $this->itext->title = _("IBAN");
        $this->itext->placeholder = "9999999999";
        $this->itext->extra = "";
        $this->itext->style = ' class="input_text" ';
        $this->autofocus = false;
    }

    function input()
    {
        if ( $this->readOnly==true) return $this->display();

        $return = $this->itext->input();
        $return .= $this->button_check_iban();
        $return.=sprintf('<div id="info%s" class="notice" style="margin:0px;font-size:80%%;width:auto"></div>',$this->itext->id);
        return $return;
    }

    public function getIText(): IText
    {
        return $this->itext;
    }

    public function setIText(IText $itext): IBan_Number
    {
        $this->itext = $itext;
        return $this;
    }

    function button_check_iban()
    {
        $button=new \IButton(uniqid());
        $button->javascript=sprintf("category_card.check_ibannumber('%s')",
        $this->itext->id);
        $button->extra='style="padding-bottom:0px"';
        $button->label=_("Vérifie");
        return $button->input();


    }
    function display()
    {
        return $this->itext->display();

    }

    static function testme()
    {
        $iban_number=new IBan_Number("av_text13");

        echo $iban_number->input();
    }
}

