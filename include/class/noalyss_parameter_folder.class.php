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
/*! \file
 * \brief Class to manage the company parameter (address, name...)
 */
/*!
 * \brief Class to manage the company parameter (address, name...)
 */
#[AllowDynamicProperties]
class Noalyss_Parameter_Folder
{
    var $db;
    var $MY_NAME;
    var $MY_TVA;
    var $MY_STREET;
    var $MY_NUMBER;
    var $MY_POSTCODE;
    var $MY_PHONE;
    var $MY_COUNTRY;
    var $MY_CITY;
    var $MY_FAX;
    var $MY_ANALYTIC;
    var $MY_STRICT;
    var $MY_TVA_USE;
    var $MY_PJ_SUGGEST;
    var $MY_CHECK_PERIODE;
    var $MY_DATE_SUGGEST;
    var $MY_ALPHANUM;
    var $MY_UPDLAB;
    var $MY_STOCK;
    var $MY_DEFAULT_ROUND_ERROR_DEB;
    var $MY_DEFAULT_ROUND_ERROR_CRED;
    var $MY_ANC_FILTER;
    var $MY_CURRENCY;
    var $MY_COUNTRY_CODE;
    var $MY_REPORT; //!< In Belgium , we need a report on the beginning of the exercice , not in France,
    var $MY_INVOICE_FORMAT; //!< Default invoice format : BASIC, UBL21BEL, FACTURXFR
    
    
    const VALID_INVOICE_FORMAT=['BASIC','UBL21BEL','FACTURXFR'];
    // constructor
    function __construct($p_cn)
    {
        $this->db=$p_cn;
        $Res=$p_cn->exec_sql("select * from parameter where pr_id like 'MY_%'");
        for ($i = 0;$i < Database::num_row($Res);$i++)
        {
            $row=Database::fetch_array($Res,$i);
            $key=$row['pr_id'];
            $elt=$row['pr_value'];
            // store value here
            $this->{"$key"}=$elt;
        }

    }

    public function __toString(): string
    {
        $r = <<<EOF
MY_TVA = [	{$this->MY_TVA }]
MY_STREET = [ 	{$this->MY_STREET }]
MY_NUMBER= [	{$this->MY_NUMBER }]
MY_POSTCODE= [	{$this->MY_POSTCODE }]
MY_PHONE= [	{$this->MY_PHONE }]
MY_COUNTRY= [	{$this->MY_COUNTRY }]
MY_COUNTRY_CODE= [	{$this->MY_COUNTRY_CODE }]
MY_CITY= [	{$this->MY_CITY }]
MY_FAX= [	{$this->MY_FAX }]
MY_ANALYTIC= [	{$this->MY_ANALYTIC }]
MY_STRICT= [	{$this->MY_STRICT }]
MY_TVA_USE= [	{$this->MY_TVA_USE }]
MY_PJ_SUGGEST= [	{$this->MY_PJ_SUGGEST }]
MY_CHECK_PERIODE= [	{$this->MY_CHECK_PERIODE }]
MY_DATE_SUGGEST= [	{$this->MY_DATE_SUGGEST }]
MY_ALPHANUM= [	{$this->MY_ALPHANUM }]
MY_UPDLAB= [	{$this->MY_UPDLAB }]
MY_STOCK= [	{$this->MY_STOCK }]
MY_DEFAULT_ROUND_ERROR_DEB= [	{$this->MY_DEFAULT_ROUND_ERROR_DEB }]
MY_DEFAULT_ROUND_ERROR_CRED= [	{$this->MY_DEFAULT_ROUND_ERROR_CRED }]
MY_ANC_FILTER= [	{$this->MY_ANC_FILTER }]
MY_REPORT = [ {$this->MY_REPORT } ]
MY_INVOICE_FORMAT = [ {$this->MY_INVOICE_FORMAT} ]
EOF;
        return $r;
    }
    /**
     * @brief check that the filter for ANC operation is valid
     * @param $p_value (string)  list of digit for accounting separated by comma
     * @return void
     * @throws  if value contains a no-digit value
     */
    function check_anc_filter($p_value):void
    {
        $tmp_value=$p_value;

        $tmp_value=preg_replace("/[0-9]|,/", '', $p_value);
        if ( $tmp_value != "") {
            throw new Exception (sprintf(_("Valeur invalide %s"),$tmp_value),1000);
        }
        if (trim($p_value) == "") {
            throw new Exception (sprintf(_("Erreur Filtre analytique %s"),$tmp_value),1001);
            
        }
        
    }
    function check($p_attr, $p_value)
    {
        $ret_value=$p_value;
        switch ($p_attr)
        {
            case 'MY_STRICT':
                
                if (empty($p_value) ||($p_value!='Y'&&$p_value!='N'))
                {
                    $ret_value='N';
                }
                
                break;
            case 'MY_ANC_FILTER':
                try
                {
                    $p_value=noalyss_str_replace(" ", "", $p_value);
                    $this->check_anc_filter($p_value);
                    $ret_value=$p_value;
                }
                catch (Exception $exc)
                {
                    throw $exc;
                }

                break;
            case 'MY_INVOICE_FORMAT':
                if ( !in_array($this->MY_INVOICE_FORMAT, Noalyss_Parameter_Folder::VALID_INVOICE_FORMAT))
                {
                    throw new \Exception ('Format facture invalide');
                }
            default :
                $ret_value= strip_tags($p_value);
        }
        return $ret_value;
    }
    
    /*!
     **************************************************
     * \brief  save the parameter into the database by inserting or updating
     *
     *
     * \param $p_attr give the attribut name
     *
     */
    function save($p_attr)
    {
        try {
            if ( trim($this->MY_ANC_FILTER) == "") $this->MY_ANC_FILTER="6,7";
            $value=$this->check($p_attr,$this->$p_attr);

            // check if the parameter does exist
            if ( $this->db->get_value('select count(*) from parameter where pr_id=$1',array($p_attr)) != 0 )
            {
                $Res=$this->db->exec_sql("update parameter set pr_value=$1 where pr_id=$2",
                                         array($value,$p_attr));
            }
            else
            {

                $Res=$this->db->exec_sql("insert into parameter (pr_id,pr_value) values( $1,$2)",
                                         array($p_attr,$value));

            }
        } catch (Exception $e) {
             throw $e;
        }

    }

    /*!
     **************************************************
     * \brief  save data
     *
     *
     */
    function update()
    {

        $this->save('MY_NAME');
        $this->save('MY_TVA');
        $this->save('MY_STREET');
        $this->save('MY_NUMBER');
        $this->save('MY_POSTCODE');
        $this->save('MY_PHONE');
        $this->save('MY_COUNTRY');
        $this->save('MY_COUNTRY_CODE');
        $this->save('MY_CITY');
        $this->save('MY_FAX');
        $this->save('MY_ANALYTIC');
        $this->save('MY_STRICT');
        $this->save('MY_TVA_USE');
        $this->save('MY_PJ_SUGGEST');
        $this->save('MY_CHECK_PERIODE');
        $this->save('MY_DATE_SUGGEST');
        $this->save('MY_ALPHANUM');
        $this->save('MY_UPDLAB');
        $this->save('MY_STOCK');
        $this->save('MY_DEFAULT_ROUND_ERROR_DEB');
        $this->save('MY_DEFAULT_ROUND_ERROR_CRED');
        $this->save("MY_ANC_FILTER");
        $this->save("MY_REPORT");
        $this->save("MY_INVOICE_FORMAT");

    }
    /**
     * Check if an accounting match the anc_filter
     * @param string $p_accounting 
     * @return boolean FALSE does not match , TRUE matches
     */
    function match_analytic($p_accounting)
    {
        $a_filter=explode(",",$this->MY_ANC_FILTER??"");
        if (empty($a_filter)) return false;
        foreach ($a_filter as $filter ) {
            $string="/^".$filter.".*/";

            if ( preg_match($string,$p_accounting) != 0 ) return TRUE;
        }
        return FALSE;
    }
    /**
     * @brief build a SELECT html input
     * @return \ISelect
     */
    function input_select_format() {
        $select_format_invoice=new \ISelect('invoice_format');
        $a_label=[_("Basic"),_("UBL21 Belgique"),_("FacturX France")];
        $select_format_invoice->transform(array_combine(Noalyss_Parameter_Folder::VALID_INVOICE_FORMAT,$a_label));
        $select_format_invoice->selected=$this->MY_INVOICE_FORMAT;
        return $select_format_invoice;
    }
}