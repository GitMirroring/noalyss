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
// Copyright (2002-2020) Author Dany De Bontridder <danydb@noalyss.eu>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');
require_once NOALYSS_INCLUDE.'/class/print_operation_currency.class.php';

/**
 * @file
 * @brief show all the operation in currency by accounting
 */
$action=$http->get("action","string","no");
$print_operation_currency=new Print_Operation_Currency($cn);
if ( $action == "print")
{
    $print_operation_currency->from_request();
}
$from_date=new IDate("from_date",$print_operation_currency->getFrom_date());
$to_date=new IDate("to_date",$print_operation_currency->getTo_date());

$from_account=new IPoste("from_account",$print_operation_currency->getFrom_account());

$to_account=new IPoste("to_account",$print_operation_currency->getTo_account());

$from_account->name('from_account');
$from_account->set_attribute('gDossier',Dossier::id());
$from_account->set_attribute('jrn',0);
$from_account->set_attribute('account','from_account');

$to_account->name('to_account');
$to_account->set_attribute('gDossier',Dossier::id());
$to_account->set_attribute('jrn',0);
$to_account->set_attribute('account','to_account');

$acc_currency=new Acc_Currency($cn);
$selCurrency=$acc_currency->select_currency();
$selCurrency->selected=$print_operation_currency->getCurrency();

?>
<form method="get">
    
    
</form>