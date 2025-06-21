<?php
/*
 * Copyright (C) 2017 Dany De Bontridder <dany@alchimerys.be>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * Part of Noalyss (2002-2020)
 */


/**
 * @file
 * @brief class Document_State_MTable
 * @see Manage_Table_SQL
 *
 */
require_once NOALYSS_INCLUDE.'/database/document_state_sql.class.php';
/**
 * @class Document_State_MTable
 * @brief this instance extends Manage_Table_SQL and aims to manage
 * the Table tmp_pcmn thanks a web interface (add , delete, display...)
 *
 * @see Document_State_SQL
 */

class Document_State_MTable extends Manage_Table_SQL
{
    function __construct(Document_State_SQL $p_table)
    {
        parent::__construct($p_table);
        $this->set_col_label("s_value", _("Label"));
        $this->set_col_label('s_status',_('Action'));
        $this->set_delete_row(false);
        $this->set_col_type('s_status','select',array(
                                array('value'=>'C','label'=>_('Ferme')),
                              array('value'=>'','label'=>_('Aucune')))
                            );
        $this->set_property_visible('s_id',false);
        $this->set_property_updatable('s_id',false);
    }
    function build()
    {
        $this->set_callback("ajax_misc.php");
        $this->add_json_param("op", "document_state");
    }
}