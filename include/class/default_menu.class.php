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

/**
 * Description of class_default_menu
 *
 * @author dany
 */
require_once NOALYSS_INCLUDE.'/database/default_menu_sql.class.php';

class Default_Menu
{

    /**
     * $a_menu_def is an array of Default_Menu_SQL
     */
    private $a_menu_def;

    /**
     * Possible values code_follow,code_invoice,code_feenote
     */
    private $code; //!< array with the valid code

    function __construct()
    {
        global $cn;
        $menu = new Default_Menu_SQL($cn);
        $ret = $menu->seek();
        for ($i = 0; $i < Database::num_row($ret); $i++)
        {
            $tmenu = $menu->next($ret, $i);
            $idx = $tmenu->getp('md_code');
            $this->a_menu_def[$idx] = $tmenu->getp('me_code');
        }
        $this->code = explode(',', 'code_follow,code_invoice,code_feenote',);
    }

    function input_value()
    {
        $code_invoice = new IText('code_invoice', $this->a_menu_def['code_invoice']);
        $code_follow = new IText('code_follow', $this->a_menu_def['code_follow']);
        $code_feenote = new IText('code_feenote', $this->a_menu_def['code_feenote']);
        echo '<div class="form-group">';
        echo '<div class="form-text">' .'<label for="code_invoice">'._('Code AD pour création facture depuis gestion').
        "</label>"."</div>". '<div class="form-text">' .$code_invoice->input() . '</div>';

        echo '<div class="form-text">' .'<label for="code_follow">'._('Code AD pour appel gestion').
        "</label>"."</div>". '<div class="form-text">' .$code_follow->input() . '</div>';

        echo '<div class="form-text">' .'<label for="code_feenote">'._('Code AD pour création note de frais ou facture achat').
        "</label>"."</div>". '<div class="form-text">' .$code_feenote->input() . '</div>';
        echo '</div>';
    }

    private function check_code($p_string)
    {
        global $cn;
        $count = $cn->get_value('select count(*) from v_menu_description_favori where '
                . 'code = $1', array($p_string));
        if ($count != 0)
        {
            return ;
        }
        $count = $cn->get_value('select count(*) from menu_ref where '
                . 'me_code = $1', array($p_string));
        if ($count != 0)
        {
            return ;
        }
        throw new Exception('code_inexistant');
    }

    function verify()
    {
        foreach ($this->code as $code)
        {
            $this->check_code($this->a_menu_def[$code]);
        }
    }

    function set($p_string, $p_value)
    {
        if (in_array($p_string, $this->code) == false)
        {
            throw new Exception("code_invalid");
        }
        $this->a_menu_def[$p_string] = $p_value;
    }
    function get ($p_string)
    {
        return $this->a_menu_def[$p_string];
    }

    function save()
    {
        global $cn;
        try
        {
            $this->verify();
            foreach ($this->a_menu_def as $key => $value)
            {
                $cn->exec_sql('update menu_default set me_code=$1 where
                        md_code =$2', array($value,$key));
            }
        } catch (Exception $e)
        {
              record_log($e);
            echo $e->getMessage();
            throw $e;
        }
    }

   
}
