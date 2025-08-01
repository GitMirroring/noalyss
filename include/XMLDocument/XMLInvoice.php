<?php
namespace Noalyss\XMLDocument;

use Noalyss\Utility;
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
 * @brief mother for e-invoice : ubl2.1 , Factur-X
 */
/**
 * @class XMLInvoice
 * @brief Mother class for e-invoice 
 *      - $data is 
 * * @code
(
    [id] => 25.822
    [issue_date] => 2025-06-10
    [due_date] => 
    [supplier] => Array
        (
            [name] => My company sprl
            [street] => Allée des Zoulons
            [postalzone] => 1080
            [city] => Molenbeek Saint Jean
            [country] => BE
            [supplier_id] => BE012345678
            [registration_name] => My Company sprl
        )

    [customer] => Array
        (
            [name] => This asbl
            [street] => 
            [postalzone] => 
            [city] => 
            [country] => 
            [customer_id] => 
            [registration_name] => This asbl
        )

    [currency] => 0
    [operation] => Array
        (
            [0] => Array
                (
                    [card_id] => 568
                    [quantity] => 1.0000
                    [price] => 10.0000
                    [vat] => 2.1000
                    [vat_id] => 1
                    [vat_reversed] => 0.0000
                )

            [1] => Array
                (
                    [card_id] => 164
                    [quantity] => 5.0000
                    [price] => 83.4500
                    [vat] => 17.5200
                    [vat_id] => 1
                    [vat_reversed] => 0.0000
                )

            [2] => Array
                (
                    [card_id] => 483
                    [quantity] => 1.0000
                    [price] => 72.0000
                    [vat] => 15.1200
                    [vat_id] => 5
                    [vat_reversed] => 15.1200
                )

        )

)
   

     * @endcode
 *  
 */
abstract class XMLInvoice extends \DOMDocument
{
    protected $cn; //!< Database conx , current folder
    protected $data; //! $data Array data retrieve from DB
    function __construct(\Database $conx)
    {
        parent::__construct("1.0", "UTF-8");
        $this->cn=$conx;
        $this->data=[];
    }
    /**
     * @brief return data
     * @return array
     */
    public function get_data() {
        return $this->data;
    }
    /**
     * @brief returns data
     * @param $data (array)
     * @return XMLInvoice
     */
    public function set_data($data): XMLInvoice {
        $this->data = $data;
        return $this;
    }

        /**
     * @brief get Database Connexion
     * @param $cn (\Database)
     * @return XMLInvoice
     */

    public function get_db_conx():\Database {
        return $this->cn;
    }
    /**
     * @brief set Database Connexion
     * @param $cn (\Database)
     * @return XMLInvoice
     */
    public function set_db_conx(\Database $cn): XMLInvoice {
        $this->cn = $cn;
        return $this;
    }

    /**
     * @brief transform an operation ($jr_id) into an array, which contains
     * needed information for making an e-invoice
     * @param $jr_id (int) operation JRN.JR_ID
     * @return array with all info7
     
     * 
     */
    function build_data($jr_id):array
    {
        global $g_parameter;
        $operation = new \Acc_Sold($this->cn,$jr_id);
        
        $operation->get();
        $result=array();
        $result["id"]= $operation->det->jr_pj_number;
        $result["issue_date"]=$operation->det->jr_date;
        $result["due_date"]=$operation->det->jr_ech;
        // supplier
        $result['supplier']=array();
        $result['supplier']['name']=$g_parameter->MY_NAME;
        $result['supplier']['street']=$g_parameter->MY_STREET;
        $result['supplier']['postalzone']=$g_parameter->MY_POSTCODE;
        $result['supplier']['city']=$g_parameter->MY_CITY;
        $result['supplier']['country']=$g_parameter->MY_COUNTRY;
        $result['supplier']['supplier_id']=$g_parameter->MY_TVA;
        // official name of the company 
        $result['supplier']['registration_name']=$g_parameter->MY_NAME;
        // official ID , like VAT
        $result['supplier']['supplier_id']=str_replace([" ",".","-","/"],"" ,$g_parameter->MY_TVA);
        //@todo 
        //Autre parametre comme email dans Parameter_Extra_SQL
        //
        //
        //  == $result['supplier']['supplier_email']=$g_parameter->;
       //@todo TESTER S'IL Y A QQ'CHOSE DE VENDU !
        //customer
        $customer=new \Fiche($this->cn,$operation->det->array[0]['qs_client']);
        $result['customer']=array();
        $result['customer']['card_id']=$operation->det->array[0]['qs_client'];
        $result['customer']['name']=$customer->strAttribut(ATTR_DEF_NAME);
        $result['customer']['street']=$customer->strAttribut(ATTR_DEF_ADRESS);
        $result['customer']['postalzone']=$customer->strAttribut(ATTR_DEF_POSTCODE);
        $result['customer']['city']=$customer->strAttribut(ATTR_DEF_CITY);
        
        // find country_code of this card
        
        $result['customer']['country']=$customer->strAttribut(ATTR_DEF_COUNTRY);
        
        $result['customer']['customer_id']=str_replace([" ",".","-","/"],"" ,$customer->strAttribut(ATTR_DEF_NUMTVA));
        // official name of the company 
        $result['customer']['registration_name']=$customer->strAttribut(ATTR_DEF_NAME);
        // official ID , like VAT
        $result['customer']['customer_id']=$customer->strAttribut(ATTR_DEF_NUMTVA);
        // +++TODO+++ adapt for all currency
        // currency must be EURO !
        $result['currency']=$operation->det->currency_id;
        // goods and services
        $result['operation']=array();
        $nb_operation= count($operation->det->array);
        for ($i=0;$i < $nb_operation;$i++) {
            $result['operation'][$i]['card_id']=$operation->det->array[$i]['qs_fiche'];
            $result['operation'][$i]['quantity']=$operation->det->array[$i]['qs_quantite'];
            $result['operation'][$i]['price']=$operation->det->array[$i]['qs_price'];
            $result['operation'][$i]['vat']=$operation->det->array[$i]['qs_vat'];
            $result['operation'][$i]['vat_id']=$operation->det->array[$i]['qs_vat_code'];
            $result['operation'][$i]['vat_reversed']=$operation->det->array[$i]['qs_vat_sided'];
        }
        print_r($result);
        return $result;
        
    }
    /**
     * @brief make an array of parameter_extra where pe_code as key and pe_value
     * as value
     * @return array
     */
    function load_noalyss_parameter()
    {
        $r=$this->cn->get_array('
                select pe_code, pe_value from parameter_extra 
                union all 
                select pr_id,pr_value 
                from parameter');
        
        return array_column($r,"pe_value","pe_code");
    }
    /**
     * @brief create an XML invoice based on JRN.JR_ID operation.
     * The PDF file could be added afterward.
     * @parameter $jr_id (int) operation JRN.JR_ID operation
     */
    abstract function make_xml($jr_id);
    /**
     * @brief check that mandatory info are saved in the DB for company (seller)
     * @param $a_error (array) array of errors, empty if nothing found
     */
    abstract function check_company_data(&$a_error) ;
    /**
     * @brief check that mandatory info are saved in the DB for customer
     * @param $customer_id (int) card of the customer  FICHE.F_ID
     * @param $a_error (array) array of errors, empty if nothing found
     */
    abstract function check_customer_data($customer_id,&$a_error) ;
    /**
     * @brief create the invoice in the right format, with PDF if any
     * @param $operation_id (int) JRN.JR_ID
     * @return string : XML or PDF format
     */
    abstract function create_invoice($operation_id) ;
}
