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
                    [code_quantity]=> EA
                )

            [1] => Array
                (
                    [card_id] => 164
                    [quantity] => 5.0000
                    [price] => 83.4500
                    [vat] => 17.5200
                    [vat_id] => 1
                    [vat_reversed] => 0.0000
                    [code_quantity]=> EA
                )

            [2] => Array
                (
                    [card_id] => 483
                    [quantity] => 1.0000
                    [price] => 72.0000
                    [vat] => 15.1200
                    [vat_id] => 5
                    [vat_reversed] => 15.1200
                    [code_quantity]=> EA
                )

        )

)
   

     * @endcode
 *  
 */
abstract class XMLInvoice extends \DOMDocument
{
    protected $cn; //!< Database conx , current folder
    protected $data; //! $data (Array) data retrieve from DB
    protected $jr_id; //! $jr_id (int) is JRN.JR_ID
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
        $this->jr_id=$jr_id;
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
        $result['customer']['name']=$customer->get_attribute(ATTR_DEF_NAME);
        $result['customer']['street']=$customer->get_attribute(ATTR_DEF_ADRESS);
        $result['customer']['postalzone']=$customer->get_attribute(ATTR_DEF_POSTCODE);
        $result['customer']['city']=$customer->get_attribute(ATTR_DEF_CITY);
        
        // find country_code of this card
        
        $result['customer']['country']=$customer->get_attribute(ATTR_DEF_COUNTRY);
        
        $result['customer']['customer_id']=str_replace([" ",".","-","/"],"" ,$customer->get_attribute(ATTR_DEF_NUMTVA));
        // official name of the company 
        $result['customer']['registration_name']=$customer->get_attribute(ATTR_DEF_NAME);
        // official ID , like VAT
        $result['customer']['customer_id']=$customer->get_attribute(ATTR_DEF_NUMTVA);
        
        // currency 
        $result['currency']=$this->cn->get_value("select cr_code_iso from currency where id=$1"
                ,array($operation->det->currency_id));
        
        // goods and services
        $result['operation']=array();
        $nb_operation= count($operation->det->array);
        for ($i=0;$i < $nb_operation;$i++) {
            $result['operation'][$i]['card_id']=$operation->det->array[$i]['qs_fiche'];
            $result['operation'][$i]['quantity']=$operation->det->array[$i]['qs_quantite'];
            
            // get the type of unity, if not found then it will be EA
            $x= \Card_Property::get_attribute($this->cn,$operation->det->array[$i]['qs_fiche'], ATTR_DEF_QUANTITY_TYPE);
            $result['operation'][$i]['code_quantity']=($x===false||$x=="")?"EA":$x;
            
            // $operation->det->currency_id == 0  default currency of the folder
            if ($operation->det->currency_id == 0 ) {
                $result['operation'][$i]['price']=$operation->det->array[$i]['qs_price'];
                $result['operation'][$i]['vat']=$operation->det->array[$i]['qs_vat'];
            } else {
                $result['operation'][$i]['price']=$operation->det->array[$i]['oc_amount'];
                $result['operation'][$i]['vat']=$operation->det->array[$i]['oc_vat_amount'];
                
            }
            $result['operation'][$i]['vat_id']=$operation->det->array[$i]['qs_vat_code'];
            $result['operation'][$i]['vat_reversed']=$operation->det->array[$i]['qs_vat_sided'];
        }
        // retrieve currency 
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
    
    /**
     * @brief thanks MY_INVOICE_FORMAT , create the corresponding object  
     *      - UBL21BEL => InvoiceUBL21
     *      - FacturX => FACTURXFR
     * @returns null  MY_INVOICE_FORMAT is BASIC
     */
    static function build_xmlinvoice(\Database $conx) {
        global $g_parameter;
        if ($g_parameter->MY_INVOICE_FORMAT == 'UBL21BEL') {
            return new \Noalyss\XMLDocument\InvoiceUBL21($conx);
        }
        if ($g_parameter->MY_INVOICE_FORMAT == 'FACTURXFR') {
            return new \Noalyss\XMLDocument\FacturX($conx);
        }
        return null;
    }
    
     /**
     * @brief check that all the data are correct
     * @returns int 0 : no errors,  int separated value
     * @see InvoiceUBL21::get_message_error()
     */
    function verify() 
    {
        
    }
}
