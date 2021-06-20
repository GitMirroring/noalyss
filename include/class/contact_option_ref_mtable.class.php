<?php

/*
 * Copyright (C) 2020 Dany De Bontridder <dany@alchimerys.be>
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
// Copyright (2002-2020) Author Dany De Bontridder <danydb@noalyss.eu>


/**
 * @file 
 * @brief
 * @todo to be implemented
 */
require_once NOALYSS_INCLUDE."/database/contact_option_ref_sql.class.php";

class Contact_Option_Ref_MTable extends Manage_Table_SQL{
    
     function __construct(Contact_Option_Ref_SQL $obj)
     {
         parent::__construct($obj);
         $this->set_col_label("cor_label", _("Nom"));
         $this->set_col_label("cor_type", _("Type "));
         $this->set_col_label("document_option_id", _("Action"));
         $this->set_col_label("cor_value_select",_("Options"));
         $this->set_property_visible("cor_id", false);
         $this->set_col_type("cor_type","select",[
             ["label"=>_("Texte"),"value"=>0],
             ["label"=>_("Nombre"),"value"=>1],
             ["label"=>_("Choix"),"value"=>3],
             ["label"=>_("Date"),"value"=>2],
             ["label"=>_("Coche"),"value"=>4]
         ]);
     }

}
