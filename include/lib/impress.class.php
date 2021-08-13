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
/* !
 * \file
 * \brief contains function for the parsing and computing formulae. Test are in scenario/test_parse_formula.php
 */

/**
 * @class Impress
 * @brief contains function for the parsing and computing formulae . Test are in scenario/test_parse_formula.php
 */
class Impress
{
    //!< string pattern to use
    const STR_PATTERN="([\[\{]{1,2}[[:alnum:]]*%*(-[c,d,s,S]){0,1}[\]\}]{1,2})";
    
    /* ! 
     * 
     * \brief   Purpose Parse a formula 
     *
     * \param $p_cn connexion
     * \param $p_label
     * \param $p_formula
     * \param $p_eval  true if we eval here otherwise the function returns
     *                 a string which must be evaluated
      \param $p_type_date : type of the date 0 for accountant period or 1
     * for calendar
     * \return array key [ desc , montant ]
     *
     *
     */

    static function parse_formula($p_cn, $p_label, $p_formula, $p_start, $p_end, $p_eval=true, $p_type_date=0, $p_sql="")
    {
        global $g_user;
        if (Impress::check_formula($p_formula)==false)
        {
            if ($p_eval==true)
                return array('desc'=>$p_label.'  Erreur Formule!',
                    'montant'=>0);
            else
                return $p_formula;
        }
        if ($p_type_date==0)
        {
            $cond=sql_filter_per($p_cn, $p_start, $p_end, 'p_id', 'j_tech_per');
            $cond_anc= "and ".transform_sql_filter_per($cond);
        }
        else {
            $cond="( j_date >= to_date('$p_start','DD.MM.YYYY') and j_date <= to_date('$p_end','DD.MM.YYYY'))";
            $cond_anc="and ( oa_date >= to_date('$p_start','DD.MM.YYYY') and oa_date <= to_date('$p_end','DD.MM.YYYY'))";
        }

        // ------------------- for accounting , analytic or card-------------------------------------
        if ( DEBUGNOALYSS > 1) 
        {
            tracedebug("impress.debug.log", "$p_formula ", 'parse_formula-71 $formula to parse' );
            tracedebug("impress.debug.log", "$p_label ", 'parse_formula-72 $p_label' );
            tracedebug("impress.debug.log", $p_start,'parse_formula-73 $p_start' );
            tracedebug("impress.debug.log", $p_end,'parse_formula-74 $p_end ' );
            tracedebug("impress.debug.log", $p_type_date,'parse_formula-75 $p_type_date' );
            tracedebug("impress.debug.log", $p_sql,'parse_formula-76 $p_sql' );
            tracedebug("impress.debug.log", $cond ,'parse_formula-77 $cond SQL accountancy' );
            tracedebug("impress.debug.log", $cond_anc,'parse_formula-78 $cond_anc SQL Analytic Acc' );
        }
        while (preg_match_all(Impress::STR_PATTERN, $p_formula, $e)==true)
        {
            $x=$e[0];
            foreach ($x as $line)
            {
               
                // If there is a FROM clause we must recompute
                // the time cond

                if ($p_type_date==0&&preg_match("/FROM=[0-9]+\.[0-9]+/", $p_formula, $afrom)==1)
                {
                    $from=str_replace('FROM=','',$afrom[0]);
                    $cond = \Impress::compute_periode($p_cn,$from,$p_end);
                    $cond_anc=" and ".transform_sql_filter_per($cond);
                    // We remove FROM out of the p_formula
                    $p_formula=substr_replace($p_formula, "", strpos($p_formula, "FROM"));
                }
                  if ($p_type_date==1&&preg_match("/FROM=[0-9]+\.[0-9]+/", $p_formula, $afrom)==1)
                {
                    // We remove FROM out of the p_formula
                    $p_formula=substr_replace($p_formula, "", strpos($p_formula, "FROM"));
                }
                $amount=\Impress::compute_amount($p_cn,$line,$cond." ".$p_sql,$cond_anc." ".$p_sql);
      

                $p_formula=str_replace($x[0], $amount, $p_formula);
            }
        }

        // $p_eval is true then we eval and returns result
        if ($p_eval==true)
        {
            /* -------------------------------------
             * Protect againt division by zero 
             */
            $p_formula=remove_divide_zero($p_formula);
            $p_formula="\$result=".$p_formula.";";
            
            eval("$p_formula");

            while (preg_match("/\[([0-9]+)(-[Tt]*)\]/", trim($p_label), $e)==1)
            {
                $nom="!!".$e[1]."!!";
                if (Impress::check_formula($e[0]))
                {
                    $nom=$p_cn->get_value("SELECT pcm_lib AS acct_name FROM tmp_pcmn WHERE pcm_val::text LIKE $1||'%' ORDER BY pcm_val ASC LIMIT 1",
                            array($e[1]));
                    if ($nom)
                    {
                        if ($e[2]=='-T')
                            $nom=strtoupper($nom);
                        if ($e[2]=='-t')
                            $nom=strtolower($nom);
                    }
                }
                $p_label=str_replace($e[0], $nom, $p_label);
            }

            $aret=array('desc'=>$p_label,
                'montant'=>$result);
            return $aret;
        }
        else
        {
            // $p_eval is false we returns only the string
            return $p_formula;
        }
    }

    /* !
     * \brief  Check if formula doesn't contain
     *           php injection
     * \param string
     *
     * \return true if the formula is good otherwise false
     */

    static function check_formula($p_string)
    {
        // the preg_match gets too complex if we want to add a test
        // for parenthesis, math function...
        // So I prefer remove them before testing



        $p_string=str_replace("round", "", $p_string);
        $p_string=str_replace("abs", "", $p_string);
        $p_string=str_replace("(", "", $p_string);
        $p_string=str_replace(")", "", $p_string);
        // for  the inline test like $a=(cond)?value:other;
        $p_string=str_replace("?", "+", $p_string);
        $p_string=str_replace(":", "+", $p_string);
        $p_string=str_replace(">=", "+", $p_string);
        $p_string=str_replace("<=", "+", $p_string);
        $p_string=str_replace(">", "+", $p_string);
        $p_string=str_replace("<", "+", $p_string);
        // eat Space + comma
        $p_string=str_replace(" ", "", $p_string);
        $p_string=str_replace(",", "", $p_string);
        // Remove D/C/S
        $p_string=str_replace("-c", "", $p_string);
        $p_string=str_replace("-d", "", $p_string);
        $p_string=str_replace("-s", "", $p_string);
        $p_string=str_replace("-S", "", $p_string);
        // Remove T,t
        $p_string=str_replace("-t", "", $p_string);

        // analytic accountancy (between {} )
        $p_string=preg_replace("/\{\{[[:alnum:]]*\}\}/", "", $p_string);

        // card (between {} )
        $p_string=preg_replace("/\{[[:alnum:]]*\}/", "", $p_string);

        // remove date
        $p_string=preg_replace("/FROM*=*[0-9]+/", "", $p_string);
        // remove comment
        $p_string=preg_replace("/#.*/", "", $p_string);
        // remove php variable $C=
        $p_string=preg_replace('/\$[a-z]*[A-Z]*[0-9]*[A-Z]*[a-z]*/', "", $p_string);
        $p_string=preg_replace('/=/', "", $p_string);

        // remove account
        $p_string=preg_replace("/\[[0-9]*[A-Z]*%*\]/", "", $p_string);

        $p_string=preg_replace("/\+|-|\/|\*/", "", $p_string);
        $p_string=preg_replace("/[0-9]*\.*[0-9]/", "", $p_string);

        //************************************************************************************************************
        // If the string is empty then formula should be good
        //
        //************************************************************************************************************
        if ($p_string=='')
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * with the handle of a successull query, echo each row into CSV and
     * send it directly
     * @param type $array of data
     * @param type $aheader  double array, each item of the array contains
     * a key type (num) and a key title
     */
    static function array_to_csv($array, $aheader, $p_filename)
    {
        $file_csv=new Noalyss_Csv($p_filename);
        for ($i=0; $i<count($aheader); $i++)
        {
            $file_csv->add($aheader[$i]['title']);
        }
        $file_csv->write();

        // fetch all the rows
        for ($i=0; $i<count($array); $i++)
        {
            $row=$array[$i];
            $e=0;
            // for each rows, for each value
            foreach ($array[$i] as $key=> $value)
            {
                if ($e>count($aheader))
                    continue;

                if (isset($aheader[$e]['type']))
                {
                    switch ($aheader[$e]['type'])
                    {
                        case 'num':
                            $file_csv->add($value, "number");
                            break;
                        default:
                            $file_csv->add($value);
                    }
                }
                else
                {
                    $file_csv->add($value);
                }
                $e++;
            }
            $file_csv->write();
        }
    }

    /**
     * return what to consider 
     *    - "deb" for the total of the debit , 
     *    - "cred" for total of credit, 
     *    - "signed" for tot. debit - tot. credit 
     *    -  "cdsigned" for  tot.credit - tot.debit
     *    - "all" is the balance of accounting in absolute value
     * 
     * @param string $p_formula
     * @return "all", "deb","cred","signed" or "cdsigned"
     */
    static function find_computing_mode($p_formula)
    {
        if (strpos($p_formula, '-d')!=0)
        {
            return 'deb';
        }
        elseif (strpos($p_formula, '-c')!=0)
        {
            return 'cred';
        }
        elseif (strpos($p_formula, '-s')!=0)
        {
            return 'signed';
        }
        elseif (strpos($p_formula, '-S')!=0)
        {
            return 'cdsigned';
        }
        return 'all';
    }
    /**
     * @brief make the condition SQL for filtering on the period
     * @param \DatabaseCore $p_cn 
     * @param int $p_from periode id
     * @param int $p_end until periode id
     * @throws Exception
     */
    static public function compute_periode($p_cn, $p_from,$p_end)
    {
        // There is a FROM clause
        // then we must modify the cond for the periode
        

        // Get the periode
        /* ! \note special value for the clause FROM=00.0000, we take the first day of the exercice of $p_end
         */
        if ($p_from=='00.0000')
        {
            $current_exercice=$p_cn->get_value('select p_exercice from parm_periode where p_id=$1',
                    [$p_end]);
            if ( $current_exercice=="") {
                throw new Execution(_('CP329'));
            }
            $first_day=$p_cn->get_value("select to_char(min(p_start),'DD.MM.YYYY') as p_start from parm_periode where p_exercice=$1",
                    [$current_exercice]);
            $last_day=$p_cn->get_value("select to_char(p_end,'DD.MM.YYYY') from parm_periode where p_id=$1",[$p_end]);
            // retrieve the first month of this periode
            if (empty($first_day))
                throw new Exception('Pas de limite à cette période', 1);
            $cond=sql_filter_per($p_cn, $first_day, $last_day, 'date', 'j_tech_per');
        }
        else
        {
            $oPeriode=new Periode($p_cn);
            try
            {
                $pfrom=$oPeriode->find_periode('01.'.$p_from);
                $cond=sql_filter_per($p_cn, $pfrom, $p_end, 'p_id', 'j_tech_per');
            }
            catch (Exception $exp)
            {
                /* if none periode is found
                  then we take the first periode of the year
                 */
               
               $first_day=$p_cn->get_value("select to_char(min(p_start),'DD.MM.YYYY') as p_start from parm_periode");
                $last_day=$p_cn->get_value("select to_char(p_end,'DD.MM.YYYY') from parm_periode where p_id=$1",[$p_end]);
                // retrieve the first month of this periode
                if (empty($first_day))
                    throw new Exception('Pas de limite à cette période', 1);
                $cond=sql_filter_per($p_cn, $first_day, $last_day, 'date', 'j_tech_per');
            }
        }
        return $cond;
    }
    /**
     * @brief compute the amount of the accounting ,analytic accounting or a card, the SQL condition
     * from sql_filter_per must be transformed for ANALYTIC ACCOUNT
     * @see  sql_filter_per
     * @param DatabaseCore $p_cn
     * @param string $p_expression part of a formula
     * @param string $p_cond_sql SQL cond  for accountancy 
     * @param string $p_cond_sql SQL cond  for analytic accountancy
     */
    static function compute_amount($p_cn, $p_expression, $p_cond_sql,$p_cond_anc_sql)
    {
        if ( DEBUGNOALYSS > 1) 
        {
            tracedebug("impress.debug.log", "$p_expression", '$p_expression' );
            tracedebug("impress.debug.log", "$p_cond_sql", '$p_cond_sql' );
        }
        $compute=\Impress::find_computing_mode($p_expression);
        // remove char for the mode
        $p_expression=str_replace("-d", "", $p_expression);
        $p_expression=str_replace("-c", "", $p_expression);
        $p_expression=str_replace("-s", "", $p_expression);
        $p_expression=str_replace("-S", "", $p_expression);
        // we have an account
        if (preg_match("/\[.*\]/", $p_expression)) {
            $p_expression=str_replace("[", "", $p_expression);
            $p_expression=str_replace("]", "", $p_expression);
            $P=new Acc_Account_Ledger($p_cn, $p_expression);
            $detail=$P->get_solde_detail($p_cond_sql);
        } elseif (preg_match("/\{\{.*\}\}/", $p_expression))
        { 
            $p_expression=str_replace("{", "", $p_expression);
            $p_expression=str_replace("}", "", $p_expression);
            $anc_account= new Anc_Account($p_cn);
            $anc_account->load_by_code($p_expression);
          
             if ( DEBUGNOALYSS > 1) 
            {
                tracedebug("impress.debug.log", $p_expression, 'code analytic account');
                tracedebug("impress.debug.log", $p_cond_anc_sql, 'condition SQL ');
            }
            /// Transform the $p_cond_sql , it comes from sql_filter_per
            // and looks like j_tech_per in (select p_id from parm_periode  where 
            
            $detail=$anc_account->get_balance($p_cond_anc_sql);
        
        } elseif (preg_match("/\{.*\}/", $p_expression))
        { // we have a card
            // remove useless char
            $p_expression=str_replace("{", "", $p_expression);
            $p_expression=str_replace("}", "", $p_expression);
            $fiche=new Fiche($p_cn);
            if ( DEBUGNOALYSS > 1) 
            {
                tracedebug("impress.debug.log", "$p_expression", 'search_card qcode =');
            }
            $fiche->get_by_qcode(strtoupper(trim($p_expression)));
            $detail=$fiche->get_solde_detail($p_cond_sql);
        } else {
            throw new \Exception ("Impress::compute_amount383.".
                    " Unknown expression \$p_expression [$p_expression]".
                    " \$p_cond_sql $p_cond_sql");
        }
        
        
        
      
        // Get sum of account

        switch ($compute)
        {
            case "all":
                $res=$detail['solde'];
                break;
            case 'deb':
                $res=$detail['debit'];
                break;
            case 'cred':
                $res=$detail['credit'];
                break;
            case 'signed':
                $res=bcsub($detail['debit'], $detail['credit'], 4);
                break;
            case 'cdsigned':
                $res=bcsub($detail['credit'], $detail['debit'], 4);
                break;
        }
        return $res;
    }

}
