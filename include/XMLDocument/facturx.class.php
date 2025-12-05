<?php
namespace Noalyss\XMLDocument;
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

use horstoeko\zugferd\codelists\ZugferdCountryCodes;
use horstoeko\zugferd\codelists\ZugferdCurrencyCodes;
use horstoeko\zugferd\codelists\ZugferdElectronicAddressScheme;
use horstoeko\zugferd\codelists\ZugferdInvoiceType;
use horstoeko\zugferd\codelists\ZugferdReferenceCodeQualifiers;
use horstoeko\zugferd\codelists\ZugferdUnitCodes;
use horstoeko\zugferd\codelists\ZugferdVatCategoryCodes;
use horstoeko\zugferd\codelists\ZugferdVatTypeCodes;
use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdProfiles;

/**
 * @file
 * @brief FacturX French / German Standard  for invoicing
 */

/**
 * @class FacturX
 * @brief FacturX French / German Standard for invoicing
 */
class FacturX extends XMLInvoice
{
     const EXTRA_PARAMETER = [
         "INVOICE_EMAIL_COMPANY"
        , 'INVOICE_CONTACT_NAME'
        , 'COMPANY_LEGAL_ENTITY'
        , 'COMPANY_LEGAL_REGISTRATION'
        , 'COMPANY_BANK_IBAN'
        , 'COMPANY_BANK_BIC'
        , 'MY_NAME'
        , 'MY_STREET'
        , 'MY_CITY'
        , 'MY_COUNTRY_CODE'
        , 'MY_TVA'
        ,'SIREN'
     //   ,'SIRET'
        ];

    protected $pdf_filename;
    
    function build_data($jr_id): array {
        $this->data=parent::build_data($jr_id);
        return $this->data;
    }
     /**
     * @brief check that mandatory info are saved in the DB
     * @param $a_error (array) array of errors, empty if nothing found
     */
    function check_company_data() 
    {
        $a_error=array();
        $company = $this->load_noalyss_parameter();
        foreach (FacturX::EXTRA_PARAMETER as $item) {
            if (!isset($company[$item]) || trim($company[$item]) == '') {
                $a_error[]=$item;
            }
        }
        return $a_error;
    }
     /**
     * @brief check that mandatory info are saved in the DB for customer
     */
    function check_customer_data(){
        $a_error=array();
        $a_needed=[ATTR_DEF_NAME=>'name'
                ,ATTR_DEF_ADRESS=>'street'
                ,ATTR_DEF_POSTCODE=>'postalzone'
                ,ATTR_DEF_CITY=>'city'
                ,ATTR_DEF_COUNTRY_CODE=>'country'
                ,ATTR_DEF_NUMTVA=>'customer_vat_id'
            ];
        
        foreach ($a_needed as $item=>$value) {
             if ( $this->data['customer'][$value]=="") {
                 $a_error[]=$value;
             }
        }
      
        return $a_error;
    }
  
     /**
     * @brief create an XML invoice(Factur-X) based on JRN.JR_ID operation
     * @parameter $jr_id (int) operation JRN.JR_ID operation
      *@return XML String
      *@note SIREN or SIRET is mandatory 
     */
    function make_xml($jr_id)
    {
        $this->data = $this->build_data($jr_id);
        $company = $this->load_noalyss_parameter();
      //  var_dump($this->data);
        $documentBuilder = ZugferdDocumentBuilder::createNew(ZugferdProfiles::PROFILE_XRECHNUNG_2_3);
        $documentBuilder->setDocumentInformation(
                $this->data['id']
                ,"380"
                ,\DateTime::createFromFormat( 'Y-m-d',$this->data["issue_date"])
                , $this->data['currency']
                );
        
        $documentBuilder->addDocumentPaymentTerm(
            sprintf("IBAN %s",$company['COMPANY_BANK_IBAN'])
            ,\DateTime::createFromFormat( 'Y-m-d',$this->data["due_date"])
            , $this->data['info']['communication']
        );
        //------------------------------------------------
        // SELLER
        //------------------------------------------------
        $documentBuilder->setDocumentSeller($company['MY_NAME'], );
        $documentBuilder->addDocumentSellerGlobalId($company['SIREN'], '0009');
        $documentBuilder->addDocumentSellerTaxNumber($company['MY_TVA']);
        $documentBuilder->addDocumentSellerVATRegistrationNumber($company['MY_TVA']);
        $documentBuilder->setDocumentSellerAddress(
                $company['MY_STREET']
                , '', ''
                , $company['MY_POSTCODE']
                , $company['MY_CITY']
                ,$company['MY_COUNTRY_CODE']);
        
        $documentBuilder->setDocumentSellerCommunication(ZugferdElectronicAddressScheme::UNECE3155_EM
                   , $company["INVOICE_EMAIL_COMPANY"]);
        
        //------------------------------------------------
        // BUYER
        //------------------------------------------------
        
        $documentBuilder->setDocumentBuyer($this->data['customer']['name'], $this->data['customer']['customer_vat_id']);
        $documentBuilder->setDocumentBuyerAddress(
                                                    $this->data['customer']['street']
                                                    , ''
                                                    , ''
                                                    , $this->data['customer']['postalzone']
                                                    , $this->data['customer']['city']
                                                    , $this->data['customer']['country']
                                                    );
//        $documentBuilder->setDocumentBuyerContact('H. Meier', 'Einkauf', '+49-333-4444444', '+49-333-5555555', 'hm@kunde.de');
//        $documentBuilder->setDocumentBuyerCommunication(ZugferdElectronicAddressScheme::UNECE3155_EM, 'purchase@kunde.de');
        
        $documentBuilder->setDocumentBuyerOrderReferencedDocument($this->data['info']['order']);
        
        //------------------------------------------------
        // Item & total
        //------------------------------------------------
       
        $base=0;$vat=0;
        $nb=count($this->data['operation']);

        for ($i=0;$i < $nb;$i++) {
            $documentBuilder->addNewPosition($i+1);
            $documentBuilder->setDocumentPositionProductDetails($this->data['operation'][$i]['qcode']
                        ,$this->data['operation'][$i]['name']
                        ,$this->data['operation'][$i]['description']
                    );
            $documentBuilder->setDocumentPositionNetPrice($this->data['operation'][$i]['price']);
            $documentBuilder->setDocumentPositionQuantity($this->data['operation'][$i]['quantity']
                    ,$this->data['operation'][$i]['code_quantity']
                    );
            $documentBuilder->addDocumentPositionTax(
                    $this->data['operation'][$i]['vat_code']
                    , ZugferdVatTypeCodes::VALUE_ADDED_TAX
                    , bcmul($this->data['operation'][$i]['vat_rate'],100,2)
                    );
            $documentBuilder->setDocumentPositionLineSummation($this->data['operation'][$i]['price']);
            
            
            $base=bcadd($base,$this->data['operation'][$i]['price'],2);
            $vat=bcadd($vat,$this->data['operation'][$i]['vat'],2);
            $vat=bcsub($vat,$this->data['operation'][$i]['vat_reversed'],2);
        }
        $tt  = bcadd($base,$vat,2);
        //------------------------------------------------
        // VAT Detail
        //------------------------------------------------
        $subTotal=$this->data['subTotalVAT'];
        $nb_sub=count($subTotal);
        for ($i=0;$i<$nb_sub;$i++) 
        {
            $documentBuilder->addDocumentTax(
                   $subTotal[$i]["vat_code"]
                 , ZugferdVatTypeCodes::VALUE_ADDED_TAX
                 ,sprintf("%.2f",$subTotal[$i]['amount'])
                 , sprintf("%.2f",$subTotal[$i]['vat'])
                 , sprintf("%.2f",$subTotal[$i]['percent'])
                 );
        }
        //------------------------------------------------
        // Total summary
        //------------------------------------------------
        $documentBuilder->setDocumentSummation(
                  sprintf("%.2f",$this->data['TaxInclusiveAmount'])
                , sprintf("%.2f",$this->data['PayableAmount'])
                , sprintf("%.2f",$this->data['TaxExclusiveAmount'])
                , 0.0
                , 0.0
                , sprintf("%.2f",$this->data['LineExtensionAmount'])
                , sprintf("%.2f",(bcsub($this->data['TaxInclusiveAmount'],
                                        $this->data['TaxExclusiveAmount'],
                                        2)
                                )
                        )
                , 0
                );
 
         
         return $documentBuilder;

         
    }
    /**
     * @brief create the invoice in the right format
     * @param $operation_id (int) JRN.JR_ID
     * @return string PDF Invoice including the XML
     */
    function create_invoice($operation_id) {
        $documentBuilder= $this->make_xml($operation_id);
        
        $invoice =  \horstoeko\zugferd\ZugferdDocumentPdfBuilder::fromPdfFile($documentBuilder, $this->pdf_filename);
        $invoice->generateDocument();
        $invoice->saveDocument($this->pdf_filename."-new.pdf");
        return $invoice->downloadString();
    }

    /**
     * @brief check that all the data are correct
     * @returns empty arry : no errors,  array with error code
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
        $a_error['customer'] = $this->check_customer_data();
        
        return $a_error;
    } 
     

}