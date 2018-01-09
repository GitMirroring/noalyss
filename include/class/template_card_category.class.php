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
// Copyright (2016) Author Dany De Bontridder <dany@alchimerys.be>

require_once NOALYSS_INCLUDE.'/lib/manage_table_sql.class.php';
require_once NOALYSS_INCLUDE.'/database/fiche_def_ref_sql.class.php';

/**
 * @file
 * @brief  Manage the template of card category 
 */

/**
 * @class
 * @brief Manage the template of card category
 */
class Template_Card_Category extends Manage_Table_SQL
{
    function __construct(Fiche_def_ref_SQL $p_table)
    {
        $this->table=$p_table;
        parent::__construct($p_table);
        // Label of the columns
        $this->set_col_label("frd_text", _("Nom"));
        $this->set_col_label("frd_class_base", _("Poste comptable de base"));
        $this->set_col_label("frd_id", _("ID"));
        // Cannot update frd_id
        $this->set_property_updatable("frd_id", FALSE);
        $this->a_order=["frd_id","frd_text","frd_class_base"];
    }
    function delete()
    {
        $cn=Dossier::connect();
        
        if ( $cn->get_value("select count(*) from fiche_def where frd_id=$1",[$this->table->frd_id])>0)
        {
            throw new Exception(_("Effacement impossible : catégorie utilisée"));
        }
    }
    /**
     * Check before inserting or updating, return TRUE if ok otherwise FALSE.
     * @return boolean
     */
    function check()
    {
        $cn=Dossier::connect();
        $error=0;
        if ( trim($this->table->frd_text) == "" ) {
            $this->set_error("frd_text",_("Le nom ne peut pas être vide"));
            $error++;
        }
        if ( trim($this->table->frd_class_base) != "" ) {
            $cnt=$cn->get_value("select count(*) from tmp_pcmn where pcm_val=$1"
                    ,[$this->table->frd_class_base]);
            if ($cnt == 0) {
                $this->set_error("frd_class_base",_("Poste comptable n'existe pas"));
                $error++;
            }
        }
        
        if ( $error != 0) {
            return false;
        }
        return true;
        
    }
}