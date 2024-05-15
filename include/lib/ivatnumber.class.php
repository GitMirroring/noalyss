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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 13/05/24
/*! 
 * \file
 * \brief input of the VAT Number of a supplier / customer, propose a button to check VAT
 */

class IVATNumber extends HtmlInput
{
    private $itext;

    function __construct($name = '', $value = '', $p_id = "")
    {

        $p_id = ($p_id=="")?uniqid("vatnumber"):$p_id;

        $this->itext = new IText($name,$value,$p_id);
        $this->itext->title = _("Numéro TVA");
        $this->itext->placeholder = "CO9999999999";
        $this->itext->extra = "";
        $this->itext->style = ' class="input_text" ';
        $this->autofocus = false;
    }

    function input()
    {
        if ( $this->readOnly==true) return $this->display();

        $return = $this->itext->input();
        $return .= $this->button_check_vat();
        $return.=sprintf('<div id="info%s" class="notice" style="margin:0px;font-size:80%%;width:auto"></div>',$this->itext->id);
        return $return;
    }

    public function getIText(): IText
    {
        return $this->itext;
    }

    public function setIText(IText $itext): IVATNumber
    {
        $this->itext = $itext;
        return $this;
    }

    function button_check_vat()
    {
        $button=new \IButton(uniqid());
        $button->javascript=sprintf("category_card.check_vatnumber('%s')",
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
        $ivatnumber=new IVATNumber("av_text13");

        echo $ivatnumber->input();
    }
}