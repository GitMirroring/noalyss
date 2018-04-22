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
// Copyright (2018) Author Dany De Bontridder <dany@alchimerys.be>

/**
 * @file
 * @brief Currency thanks the view v_currency_last_value
 */
require_once NOALYSS_INCLUDE.'/lib/manage_table_sql.class.php';
require_once NOALYSS_INCLUDE.'/database/currency_sql.class.php';
require_once NOALYSS_INCLUDE.'/database/currency_history_sql.class.php';

/**
 * Manage the configuration of currency , add currency, rate, remove  and update
 * Concerned tables are v_currency_last_value _SQL , Currency_SQL , Currency_History_SQL
 */
class Currency_MTable extends Manage_Table_SQL
{

    /**
     * constructor
     * @param Data $p_table
     * @example test_currency_mtable.php
     */
    function __construct(Data_SQL $p_table)
    {
        parent::__construct($p_table);

        // If currency , cannot be deleted
        if ($this->is_currency_used()==TRUE)
        {
            $this->set_delete_row(FALSE);
        }
        else
        {
            $this->set_delete_row(TRUE);
        }
        $this->set_property_visible("currency_id", FALSE);
        $this->set_property_visible("currency_history_id", FALSE);
        $this->set_col_label("cr_code_iso", "ISO");
        $this->set_col_label("cr_name", _("Nom"));
        $this->set_col_label('ch_value', _("Valeur"));
        $this->set_col_label('str_from', _("Date"));
        $this->set_col_type("str_from", "date");
        $this->a_order=array("cr_code_iso", "currency_id", "currency_history_id", "cr_name", "ch_value", "str_from");
        $this->set_icon_mod("first");
        $this->set_sort_column("cr_code_iso");
    }

    /**
     * returns TRUE the currency is used otherwise FALSE. We cannot delete a currency which is used in a
     * operation
     * @returns boolean true if currency is used
     * @todo Currency_MTable.is_currency_used to implement
     */
    function is_currency_used()
    {
        return FALSE;
    }
    /**
     * Box to enter either a new currency or update a existing one
     */
    function input()
    {
        $record=$this->get_table();
        $cr_code_iso=new IText("cr_code_iso", $record->cr_code_iso);
        $cr_code_iso->size=10;
        $cr_name=new IText("cr_name", $record->cr_name);
        $cr_name->size=50;
        $a_histo=$record->cn->get_array("select id,to_char(ch_from,'DD.MM.YYYY') as str_from,ch_value 
               from 
                currency_history 
               where 
                currency_id=$1", array($record->currency_id));
        $new_rate_date=new IDate("new_rate_date");
        $new_rate_value=new INum("new_rate_value");
        $new_rate_value->prec=4;
        if ($record->currency_id!=-1)
        {
            require NOALYSS_TEMPLATE."/currency_mtable_input.php";
        }
        else
        {
            require NOALYSS_TEMPLATE."/currency_mtable_input_new.php";
        }
    }

    /**
     * Check that the value are correct : 
     *      - Code iso must be unique
     *      - Name cannot be empty
     *      - At least one rate 
     *      - Date of the rate 
     *      
     */
    function check()
    {
        global $cn;
        $table=$this->get_table();
        $is_error=0;
        // ------ cr_code_iso can not be empty
        if (trim($table->cr_code_iso)=="")
        {
            $is_error++;
            $this->set_error("cr_code_iso", _("Code iso ne peut pas être vide"));
        }
        // ------ cr_name can not be empty
        if (trim($table->cr_name)=="")
        {
            $is_error++;
            $this->set_error("cr_name", _("Nom ne peut pas être vide"));
        }
        // ----- for new record than we need at leat a date + value
        if ($table->currency_id==-1)
        {
            // -- for new record , we need a date + value
            if (isDate($table->str_from)==0||trim($table->str_from)=="")
            {
                $is_error++;
                $this->set_error("str_from", _("Date incorrecte, il faut au moins une valeur"));
            }
            if (isNumber($table->ch_value)==0||trim($table->ch_value)=="")
            {
                $is_error++;
                $this->set_error("ch_value", _("Valeur incorrecte, il faut au moins une valeur"));
            }
        }
        else
        {
            // -- for update, the date and value must be valid
            if (trim($table->str_from)!=""&&trim($table->ch_value)!="")
            {
                if (isDate($table->str_from)==0)
                {
                    $is_error++;
                    $this->set_error("str_from", _("Date incorrecte"));
                }
                if (isNumber($table->ch_value)==0)
                {
                    $is_error++;
                    $this->set_error("ch_value", _("Valeur incorrecte"));
                }
                // Date must be the greatest
                $count=$cn->get_value("select count(*) 
                        from 
                            currency_history 
                        where 
                            currency_id =$1 
                            and ch_from >= to_date($2,'DD.MM.YYYY') ", array($table->currency_id, $table->str_from));
                if ($count>0)
                {
                    $is_error++;
                    $this->set_error("str_from", _("Date doit être après la dernière valeur"));
                }
            }
            elseif (trim($table->str_from)!=""||trim($table->ch_value)!="")
            {
                $is_error++;
                $this->set_error("str_from", _("Valeur manquante"));
            }
        }
        //-- duplicate iso code
        $cnt=$cn->get_value("select count(*) from currency where id != $1 and cr_code_iso=$2",
                array($table->currency_id, $table->cr_code_iso));
        if ($cnt>0)
        {
            $is_error++;
            $this->set_error("cr_code_iso", _("Code ISO existe déjà"));
        }


        if ($is_error==0)
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Either insert a new currency + one rate or add a rate to an existing currency
     */
    function save()
    {
        $cn=Dossier::connect();
        try
        {
            $cn->start();
            $record=$this->get_table();

            if ($record->currency_id==-1)
            {
                // Append a new currency and a default value
                $currency=new Currency_SQL($cn);
                $currency->setp("cr_code_iso", mb_strtoupper($record->cr_code_iso));
                $currency->setp("cr_name", $record->cr_name);
                $currency->insert();

                $currency_history=new Currency_history_SQL($cn);
                $currency_history->setp("ch_value", $record->ch_value);
                $currency_history->setp("ch_from", $record->str_from);
                $currency_history->setp("currency_id", $currency->id);
                $currency_history->insert();

                $this->table->currency_id=$currency->id;
                $this->table->load();
            }
            else
            {
                // append a value in currency_historique and update currency
                $currency=new Currency_SQL($cn, $record->currency_id);
                $currency->setp("cr_code_iso", mb_strtoupper($record->cr_code_iso));
                $currency->setp("cr_name", $record->cr_name);
                $currency->update();

                if ($record->str_from!="")
                {
                    $currency_history=new Currency_history_SQL($cn);
                    $currency_history->setp("ch_value", $record->ch_value);
                    $currency_history->setp("ch_from", $record->str_from);
                    $currency_history->setp("currency_id", $currency->id);
                    $currency_history->insert();
                }
            }
            $cn->commit();
        }
        catch (Exception $ex)
        {
            $cn->rollback();
            throw ($ex);
        }
    }
    /**
     * Fill the object from request 
     * parameters : 
     *          - cr_code_iso
     *          - cr_name
     *          - p_id
     *          - new_rate_date
     *          - new_rate_value
     * 
     */
    function from_request()
    {
        $http=new HttpInput();
        $this->table->cr_code_iso=mb_strtoupper(strip_tags($http->request("cr_code_iso")));
        $this->table->cr_name=strip_tags($http->request("cr_name"));
        $this->table->currency_id=$http->request("p_id", "number");
        $this->table->ch_value=$http->request("new_rate_value");
        $this->table->str_from=$http->request("new_rate_date");
    }

}
