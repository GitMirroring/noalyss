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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 22/10/23


/**
 * @file
 * @brief Contains function used by object Customer , Supplier, Bank, ... that
 * are derivated from Fiche
 */

/**
 * @class
 * @brief Contains function used by object Customer , Supplier, Bank, ... that
 * are derivated from Fiche
 */
trait Trait_Card {
    /**
     * @brief For the follow-up module for customer, supplier, ... display a 
     * filter 
     * @param $url (string URL) url with folder id, access code ,...
     * @param $type_card (int) category of card see FICHE_TYPE_* in 
     * include/constant.php
     */
    public static function form_search($url,$type_card)
    {
        require_once NOALYSS_TEMPLATE."/trait_card-form_search.php";
    }
}