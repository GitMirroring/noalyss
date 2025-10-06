<?php
namespace Noalyss\XMLDocument;

//use Noalyss\Utility;
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
    [info] => Array 
           [order] = order reference
           [communication] = communication added to the invoice
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
                    [vat_code]=> Code VAT for PEPPOL (S,K,...)
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
                    [vat_code]=> Code VAT for PEPPOL (S,K,...)
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
                    [vat_code]=> Code VAT for PEPPOL (S,K,...)
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
        $result["due_date"]=($operation->det->jr_ech=="")?$operation->det->jr_date:$operation->det->jr_ech;
        // supplier
        $result['supplier']=$this->fill_supplier();
       
       
        //customer
        $result['customer']=$this->fill_customer($operation->det->array[0]['qs_client']);
        
        // currency 
        $result['currency']=$this->cn->get_value("select cr_code_iso from currency where id=$1"
                ,array($operation->det->currency_id));
        
        // document description
        $result['description']=$operation->det->jr_comment;
        
        // goods and services
        $result['operation']=array();
        
        $nb_operation= count($operation->det->array);
        for ($i=0;$i < $nb_operation;$i++) {
            $result['operation'][$i]['card_id']=$operation->det->array[$i]['qs_fiche'];
            $result['operation'][$i]['quantity']=$operation->det->array[$i]['qs_quantite'];
            $card=new \Fiche($this->cn,$operation->det->array[$i]['qs_fiche']);
            $result['operation'][$i]['qcode']=$card->get_attribute(ATTR_DEF_QUICKCODE);
            $result['operation'][$i]['name']=$card->get_attribute(ATTR_DEF_NAME);
            $result['operation'][$i]['description']=$card->get_attribute(9);
            // get the type of unity, if not found then it will be EA
            $x= $card->get_attribute(ATTR_DEF_QUANTITY_TYPE,0);
            $result['operation'][$i]['code_quantity']=($x===false||$x=="")?"EA":$x;
            
            // $operation->det->currency_id == 0  default currency of the folder
            if ($operation->det->currency_id == 0 ) {
                $result['operation'][$i]['price']=$operation->det->array[$i]['qs_price'];
                $result['operation'][$i]['price_unit']=$operation->det->array[$i]['qs_unit'];
                $result['operation'][$i]['vat']=$operation->det->array[$i]['qs_vat'];
            } else {
                $result['operation'][$i]['price']=$operation->det->array[$i]['oc_amount'];
                $result['operation'][$i]['price_unit']=bcdiv(
                        $operation->det->array[$i]['oc_amount'],
                        $operation->det->array[$i]['qs_quantite'],
                        2);
                $result['operation'][$i]['vat']=$operation->det->array[$i]['oc_vat_amount'];
                
            }
            $result['operation'][$i]['vat_id']=$operation->det->array[$i]['qs_vat_code'];
//            // tva code for PEPPOL
            $x=$this->cn->get_row("select tva_peppol_code,tva_rate from tva_rate where tva_id=$1"
                    ,[ $result['operation'][$i]['vat_id']]);
            $result['operation'][$i]['vat_code']=($x['tva_peppol_code']=="")?"S":$x['tva_peppol_code'];
            $result['operation'][$i]['vat_rate']=$x['tva_rate'];
            
            $result['operation'][$i]['vat_reversed']=$operation->det->array[$i]['qs_vat_sided'];
        }
        //------------------------------------------------
        // retrieve order and comment
        //------------------------------------------------
        $a_row=$this->cn->get_array("select id_type,ji_value from jrn_info where jr_id=$1"
                ,[$jr_id]);
        $nb_row = count($a_row);
        $result['info']=[];
        $result['info']['order']='NA';
        $result['info']['communication']='';
        for($i=0;$i<$nb_row;$i++) {
            switch ($a_row[$i]['id_type']) {
                case 'BON_COMMANDE':
                    $result['info']['order']=$a_row[$i]['ji_value'];
                    break;
                case 'OTHER':
                    $result['info']['communication']=$a_row[$i]['ji_value'];
                    break;
                        
            }
        }
        $result['info']['communication']=($result['info']['communication']=="")?$result['id']:"";
         /**
         * Compute totals VAT and AMOUNT
         */
        $nb_operation = count($result['operation']);
        
        /// block cac:LegalMonetaryTotal
        $result['LineExtensionAmount']=0;
        $result['TaxExclusiveAmount']=0;
        $result['TaxInclusiveAmount']=0;
        $result['PayableAmount']=0;
        
        // block cac:TaxTotal
        $result['TaxableAmount']=0;
        $result['TaxAmount']=0;
        
        // array for TaxSubtotal
        $VAT_SubTotal=array();
        $idx_subtotal=0;
        bcscale(2);
        // for each operation 
        $VAT_SubTotal=array();
        for ($i=0;$i < $nb_operation;$i++) {
            $acc_tva=\Acc_TVA::build($this->cn,$result['operation'][$i]['vat_id'] );
            $percent = bcmul($acc_tva->tva_rate,100,2);
            $idx=sprintf("%s - %s",$percent,$result['operation'][$i]['vat_code'] );
            // subtotal for VAT
            $n = find_idx($VAT_SubTotal,'idx',$idx);
            if ($n == -1 ) {
                $n=$idx_subtotal;
                $VAT_SubTotal[$idx_subtotal]=array();
                $VAT_SubTotal[$idx_subtotal]['idx']=$idx;
                $VAT_SubTotal[$idx_subtotal]['vat_code']=$result['operation'][$i]['vat_code'] ;
                $VAT_SubTotal[$idx_subtotal]['percent']=$percent;
                $VAT_SubTotal[$idx_subtotal]['amount']=$VAT_SubTotal[$idx_subtotal]['vat']=0;
                $idx_subtotal++;
            }
            /**
             * @todo Pour les intracomm , quel taux utilisé ? 0 ou 21%
             */
            $VAT_SubTotal[$n]['amount']=bcadd($VAT_SubTotal[$n]['amount'],$result['operation'][$i]['price']);
            $VAT_SubTotal[$n]['vat']=bcadd($VAT_SubTotal[$n]['vat'],$result['operation'][$i]['vat']);
            $VAT_SubTotal[$n]['vat']=bcsub($VAT_SubTotal[$n]['vat'],$result['operation'][$i]['vat_reversed']);
            $result['TaxableAmount']=bcadd( $result['TaxableAmount'],$result['operation'][$i]['price']);
            $result['TaxAmount']=bcadd( $result['TaxAmount'],$result['operation'][$i]['vat']);
            $result['TaxAmount']=bcsub( $result['TaxAmount'],$result['operation'][$i]['vat_reversed']);
            $result['operation'][$i]['vat_percent']=$percent;
        }
        $result['subTotalVAT']=$VAT_SubTotal;
        $result['LineExtensionAmount']= $result['TaxableAmount'];
        $result['TaxExclusiveAmount']= $result['TaxableAmount'];
        $result['TaxInclusiveAmount']=bcadd( $result['TaxableAmount'],$result['TaxAmount']);
        $result['PayableAmount']=bcadd( $result['TaxableAmount'],$result['TaxAmount']);
        
        return $result;
        
    }
    

    /**
     * @brief make an array of parameter_extra where pe_code as key and pe_value
     * as value
     * @return array keys : pe_code,pe_value
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
     */
    abstract function check_company_data() ;
    /**
     * @brief check that mandatory info are saved in the DB for customer
     * @param $customer_id (int) card of the customer  FICHE.F_ID
     */
    abstract function check_customer_data($customer_id) ;
    /**
     * @brief create the invoice in the right format, with PDF if any
     * @param $operation_id (int) JRN.JR_ID
     * @return string : XML or PDF format
     */
    abstract function create_invoice($operation_id) ;
    
    /**
     * @brief display_error display a warning with all error
     */
    public function display_error()
    {
        $a_error=$this->verify();
        include NOALYSS_TEMPLATE."/xmlinvoice-display_error.php";
        
    }
    
    /**
     * @brief check that the VAT is using a PEPPOL Code
     */
    function check_VAT()
    {
        $a_error=array();
        $nb_operation=count($this->data['operation']);
        for ($i=0;$i <$nb_operation;$i++) 
        {
            if ( $this->data['operation'][$i]['vat_code'] == "" ) {
                $card=new \Fiche(
                        $this->cn
                        ,$this->data['operation'][$i]['card_id']
                        );
                $tva= \Acc_Tva::build($this->cn, $this->data['operation'][$i]['vat_id']);
                $a_error[]=sprintf(_("%s : %s code TVA pour PEPPOL non configuré code TVA [ %s %s ]")
                        ,   $i
                        , $card->get_quick_code()
                        ,$tva->tva_id
                        ,$tva->tva_code 
                        );
            }
        }
        return $a_error;
    }

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
     * @returns null : no errors,  string separated with comma of error code
     * @see get_message_error
     */
    public function verify()
    {
                // verify all VAT
        ///@var $a_error : array of error_code see check_company_error
        $a_error = array();
        $a_error['general'] =  [];
        $a_error['operation']=[];
       
        // verify that all needed data in PARAMETER are valid
        $a_error['company'] = $this->check_company_data();
        $a_error['customer'] = $this->check_customer_data($this->data['customer']['card_id']);
        
        return $a_error;
    } 
     
    /**
     * @brief  retrieve data from customer and return it into an array
     * @param $card_id (int) FICHE.F_ID
     * @return array keys : 
     *      - name
     *      - ,street
     *      - ,postalzone
     *      - ,city
     *      - ,country
     *      - ,customer_id => VAT Number
     *      - , registration_name,
     *      - card_id
     */
    function fill_customer($card_id):array
    {
        $customer =new \Fiche($this->cn,$card_id);
        $result=array();
        $result['card_id']=$card_id;
        $result['name']=$customer->get_attribute(ATTR_DEF_NAME,0);
        $result['street']=$customer->get_attribute(ATTR_DEF_ADRESS,0);
        $result['postalzone']=$customer->get_attribute(ATTR_DEF_POSTCODE,0);
        $result['city']=$customer->get_attribute(ATTR_DEF_CITY,0);
        
        // find country_code of this card
        $result['country']=$customer->get_attribute(ATTR_DEF_COUNTRY_CODE,0);
        
        // official ID , like VAT
        $result['customer_id']=str_replace([" ",".","-","/"],"" ,$customer->get_attribute(ATTR_DEF_NUMTVA,0));
        // official name of the company 
        $result['registration_name']=$customer->get_attribute(ATTR_DEF_NAME,0);
        $result['endpoint_id']=$customer->get_attribute(ATTR_DEF_PEPPOLID,0);
        return $result;
    }
    /**
     * @brief complete $this->data from $g_parameter (global variable) for 
     * Noalyss_Folder_Parameter
     * @return array keys : 
     *      - name
     *      - ,street
     *      - ,postalzone
     *      - ,city
     *      - ,country
     *      - supplier_id => VAT Number
     *      - registration_name,
     * 
     */
    function fill_supplier():array
    {
        $a_parameter=$this->load_noalyss_parameter();
        $result=array();
        $result['name']=$a_parameter['MY_NAME'];
        $result['street']=$a_parameter['MY_STREET'];
        $result['postalzone']=$a_parameter['MY_POSTCODE'];
        $result['city']=$a_parameter['MY_CITY'];
        $result['country']=$a_parameter['MY_COUNTRY'];
        // official name of the company 
        $result['registration_name']=$a_parameter['MY_NAME'];
        // official ID , like VAT
        $result['supplier_id']=str_replace([" ",".","-","/"],"" ,$a_parameter['MY_TVA']);
        $result['COUNTRY_CODE']=$a_parameter['COUNTRY_CODE']??"";
        $result['COMPANY_LEGAL_REGISTRATION']=$a_parameter['COMPANY_LEGAL_REGISTRATION']??"";
        $result['COMPANY_LEGAL_ENTITY']=$a_parameter['COMPANY_LEGAL_ENTITY']??"";
        $result['INVOICE_CONTACT_NAME']=$a_parameter['INVOICE_CONTACT_NAME']??"";
        $result['INVOICE_EMAIL_COMPANY']=$a_parameter['INVOICE_EMAIL_COMPANY']??"";
        $result['COMPANY_UBL_ID']=$a_parameter['COMPANY_UBL_ID']??"";
        return $result;
    }
    /**
     * @brief build operation from array 
     * key : 
     *      - [e_march0] =>  Quick code of the item
            - [e_march0_label] => Label of item
            - [e_march0_price] => Unit Price
            - [e_quant0] => Quantity
            - [htva_march0] => Price w/0 VAT
            - [e_march0_tva_id] => Code VAT
            - [e_march0_tva_amount] => Amount VAT 
            - [tva_march0] => Amount VAT (duplicate -> to remove)
            - [tvac_march0] => Total Amount Tax included
     * @param type $a_array
     * @return type
     */
    function fill_operation_from_array($a_array)
    {
        $result=array();
        $http=new \HttpInput();
        $http->set_array($a_array);
        
        $nb_item=$http->get_value("nb_item");
        for ($i=0;$i<$nb_item;$i++)
        {
           if ( $http->get_value("e_march{$i}_tva_id") == "") 
           {
               continue;
           }
           $operation=array();
           $card=\Fiche::from_qcode($this->cn,trim($http->get_value("e_march{$i}")));
           $operation['card_id']=$card->id;
           $operation['quantity']=$http->get_value("e_quant{$i}");
           $operation['price']=$http->get_value("e_march{$i}_price");
           $operation['vat']=$http->get_value("tvac_march{$i}");
           $tva= \Acc_Tva::build($this->cn, $http->get_value("e_march{$i}_tva_id"));
           $operation['vat_id']=$tva->tva_id;
           $operation['vat_reversed']=($tva->tva_both_side==1)?$operation['vat']:0;
           $operation['vat_code']=$tva->tva_peppol_code;
           
           $operation['code_quantity']=$card->get_attribute(ATTR_DEF_QUANTITY_TYPE,0);
           $operation['code_quantity']=($operation['code_quantity']=="")?"EA":$operation['code_quantity'];
           $result[$i]=$operation;
        }
        return $result;
    }
    
    /**
     * @brief set the PDF 
     * @param $pdf_filename (string) full path to the PDF
     * @return $this
     * @throws \Exception if the filename doesn't exist
     */
    public function set_pdf_filename($pdf_filename) {
        if ( !file_exists($pdf_filename)) {
            throw new \Exception("AD65 $pdf_filename doesn't not exist");
        }
        $this->pdf_filename = $pdf_filename;
        return $this;
    }

}
