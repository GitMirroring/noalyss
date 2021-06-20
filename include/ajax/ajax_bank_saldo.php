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

/* !\file
 * \brief respond ajax request, the get contains
 *  the value :
 * - l for ledger
 * - gDossier
 * Must return at least tva, htva and tvac

 */

/* !\file
 * \brief get the saldo of a account
 * the get variable are :
 *  - l the jrn id
 *  - ctl the ctl where to get the quick_code
 */
if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');
/* check the parameters */
foreach (array('j', 'ctl') as $a)
{
    if (!isset(${$a}))
    {
        echo "missing $a";
        return;
    }
}
$http=new HttpInput();
try
{
    $ledger_id=$http->get('j', "number");

    if ($g_user->check_jrn($ledger_id)=='X')
        return '{"saldo":"0"}';

    $id=$cn->get_value('select jrn_def_bank from jrn_def where jrn_def_id=$1', array($ledger_id));
    if ($id=='')
    {
        echo '{"saldo":"ERR"}';
        return;
    }

    $acc=new Fiche($cn, $id);

    $ledger=new Acc_Ledger_Fin($cn, $ledger_id);
    $ledger->load();
    // Is ledger in Default currency
    if ($ledger->currency_id==0)
    {
        /*  make a filter on the exercice */

        $filter_year="  j_tech_per in (select p_id from parm_periode ".
                "where p_exercice='".$g_user->get_exercice()."')";





        $res=$acc->get_bk_balance($filter_year." and ( trim(jr_pj_number) != '' and jr_pj_number is not null)");


        if (empty($res))
            return '{"saldo":"0"}';
        $solde=$res['solde'];
        if ($res['debit']<$res['credit'])
            $solde=$solde*(-1);

        echo '{"saldo":"'.$solde.'"}';
    } else
    {
        $solde=$acc->get_bk_balance_currency();
         echo '{"saldo":"'.$solde.'"}';
    }
}
catch (Exception $e)
{
    record_log(__FILE__.":".__LINE__);
    record_log($e->getMessage());
    echo '{"saldo":"ERR"}';
}