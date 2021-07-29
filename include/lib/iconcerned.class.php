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

/*!
 * \file
 * \brief Html Input
 *  - name is the name and id of the input
 *  - extra amount of the operation to reconcile
 *  - extra2 ledger paid
 */
/*!
 * \class IConcerned
 * \brief Html Input
 *  - name is the name and id of the input
 *  - extra amount of the operation to reconcile
 *  - extra2 ledger paid
 */
class IConcerned extends HtmlInput
{

    private $hideOperation; //!< string of j_id to hide, separated by comma to avoid to reconcile an operation with itself
    private $singleOperation; //!< do not allow to select several operations

    public function __construct($p_name='', $p_value='', $p_id="")
    {
        $this->name=$p_name;
        $this->value=$p_value;
        $this->amount_id=null;
        $this->paid='';
        $this->id=$p_id;
        // id of the field for the tiers to be updated
        $this->tiers="";
        // Dom Element to show the search result
        $this->div="";
        // string of j_id to hide, separated by comma to avoid to reconcile an operation with itself
        $this->hideOperation="";
        // by default we can select several operation
        $this->singleOperation=0; 
    }

    /* !\brief show the html  input of the widget */

    public function input($p_name=null, $p_value=null)
    {
        $this->name=($p_name==null)?$this->name:$p_name;
        $this->value=($p_value==null)?$this->value:$p_value;
        if ($this->readOnly==true)
            return $this->display();

        $this->id=($this->id=="")?$this->name:$this->id;
        $javascript=sprintf("search_reconcile(".dossier::id().",'%s','%s','%s','%s','%s')", $this->name,
                $this->amount_id, $this->paid, $this->div, $this->tiers);
        $r=Icon_Action::icon_magnifier(uniqid(), $javascript);
        $r.=sprintf("
                   <INPUT TYPE=\"text\"  style=\"color:black;background:lightyellow;border:solid 1px grey;\"  NAME=\"%s\" ID=\"%s\" VALUE=\"%s\" SIZE=\"8\" hide_operation=\"%s\" readonly single_operation=\"%s\">
				   <INPUT class=\"smallbutton\"  TYPE=\"button\" onClick=\"$('%s').value=''\" value=\"X\">

                   ", $this->name, $this->id, $this->value, $this->hideOperation,$this->singleOperation,
                $this->id
        );
        return $r;
    }

    /**
     * setter
     * @param number $p_string  jrn.jr_id of the operation to hide
     */
    function set_hideOperation($p_string)
    {
        $this->hideOperation=strip_tags($p_string);
    }

    /**
     * Set the value of single operation, limit to one operation if TRUE
     * @param bool $p_value TRUE or false
     */
    function set_singleOperation($p_value){
        if ( $p_value == TRUE  ) {
            $this->singleOperation=1;
            return;
        }
        if ( $p_value == FALSE ) {
            $this->singleOperation=0;
            return;
        }
        throw new Exception (_("setSingleOperation failed"));
    }
    
    function get_singleOperation(){
        return $this->singleOperation;
    }
    
    /* !\brief print in html the readonly value of the widget */

    public function display()
    {
        $r=sprintf("<span><b>%s</b></span>", $this->value);
        $r.=sprintf('<input type="hidden" name="%s" value="%s">', $this->name, $this->value);
        return $r;
    }

    static public function test_me()
    {
        
    }

}
