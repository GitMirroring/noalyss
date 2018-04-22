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

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief Ajax response for currency related calls
 */
$err=0;
$a_answer=array();
$a_answer['status']="NOK";
$http=new HttpInput();
try
{
    $act=$http->request("op");
}
catch (Exception $ex)
{
    $a_answer['content']=$ex->getMessage();
    $jsson=json_encode($a_answer, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
    header('Content-Type: application/json;charset=utf-8');
    echo $jsson;
    return;
}

/* security check */
/**
 * @todo add module CFGCURRENCY
 */
if ($g_user->check_module('CFGCURRENCY')==0)
{
   // return;
}

/*
 * Select action
 */
switch ($act)
{
    case 'CurrencyRateDelete':
        try {
            $currency_rate_id=$http->get("currency_rate_id","number");
            // check that a least one rate is remaining for this currency
            
            // 1. get the currency
            $currency_id=$cn->get_value("select currency_id from currency_history where id=$1",[$currency_rate_id]);
            
            // 2. get the number of rate
            if ( $currency_id == "") {
                throw new Exception(_("Taux inexistant"));
            }
            $cnt=$cn->get_value("select count(*) from currency_history where currency_id=$1",[$currency_id]);
            
            // 3. if number of rate > 1 , then delete
            if ( $cnt > 1)
            {
                $cn->exec_sql("delete from currency_history where id=$1",[$currency_rate_id]);
                $a_answer['status']=_("OK");
                $a_answer['content']=_("Taux effacé");
            } else {
                $a_answer['content']=_("Non effacé : Il faut au moins un taux");
            }
        } catch (Exception $ex) {
            $a_answer['content']=$ex->getMessage();
        }
        break;
}

$jsson=json_encode($a_answer, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
if (!headers_sent())
{
    header('Content-Type: application/json;charset=utf-8');
}
echo $jsson;
