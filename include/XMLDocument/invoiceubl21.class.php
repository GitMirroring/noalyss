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

/**
 * @file
 * @brief UBL2.1 Belgique
 *   -  $pdf_filename PDF file to insert into XML, it is the file on the filesystem
 */
/**
 * @class
 * @brief UBL2.1 Belgique
 * @note Doit contenir le PDF.
 *      -  $pdf_filename PDF file to insert into XML, it is the file on the filesystem
 @code
<cac:Attachment>
  <cbc:EmbeddedDocumentBinaryObject mimeCode="application/pdf" filename="facture.pdf" encodingCode="Base64">
    [Contenu du PDF encodé en Base64]
  </cbc:EmbeddedDocumentBinaryObject>
</cac:Attachment>
@endcode
 * 
 */
class InvoiceUBL21 extends XMLInvoice {

    const EXTRA_PARAMETER = ["INVOICE_EMAIL_COMPANY"
        , 'INVOICE_CONTACT_NAME'
        , 'COMPANY_LEGAL_ENTITY'
        , 'COMPANY_LEGAL_REGISTRATION'
        , 'COMPANY_BANK_IBAN'
        , 'COMPANY_BANK_BIC'
        , 'MY_COUNTRY_CODE'
        , 'MY_NAME'
        , 'MY_STREET'
        , 'MY_CITY'
        , 'MY_TVA'
        , 'INVOICE_EMAIL_COMPANY'
        ];
    protected $pdf_filename; //!< PDF file to insert into XML,
                             //       it is the file on the filesystem
    public function get_pdf_filename() {
        return $this->pdf_filename;
    }
    /**
     * @brief display_error display a warning with all error
     */
    function display_error()
    {
        $a_error=$this->verify();
        include NOALYSS_TEMPLATE."/xmlinvoice-display_error.php";
    }
  

    /**
     * @brief check that mandatory info are saved in the DB for company (seller)
     * @param $a_error (array) array of errors, empty if nothing found
     */
    function check_company_data() {
        // var $a_error (array) contains the errors for the company
        $a_error=array();
        $company = $this->load_noalyss_parameter();
        foreach (InvoiceUBL21::EXTRA_PARAMETER as $item) {
            if (!isset($company[$item]) || trim($company[$item]) == '') {
                $a_error[]=$item;
            }
        }
        /**
         * check that PEPPOL ID is valid
         */
        if ( isset($company['COMPANY_PEPPOL_ID'])) 
        {
            if ( strpos($company['COMPANY_PEPPOL_ID'],':') == 0 )
            {
                $a_error[]="COMPANY_PEPPOL_ID";
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
                ,ATTR_DEF_PEPPOLID=>'endpoint_id'
            ];
        
        foreach ($a_needed as $item=>$value) {
             if ( trim($this->data['customer'][$value])=="") {
                 $a_error[]=$value;
             }
        }
        if ( $this->data['customer']["endpoint_id"] != "")
        {
            // check if peppol id has the form 9999:9999...
            list($scheme_id,$peppol)=explode(":", $this->data['customer'][$value]);
            if (preg_replace('/[0-9]/', '', $scheme_id) != "") 
            {
                $a_error[]=ATTR_DEF_PEPPOLID;
            }elseif(\noalyss_trim($peppol) =="") 
            {
                $a_error[]=ATTR_DEF_PEPPOLID;
                
            }
        }
        return $a_error;
    }

    /**
     * @brief transform an operation ($jr_id) into an array, which contains
     * needed information for making an e-invoice
     * @param $jr_id (int) operation JRN.JR_ID
     * @return array with all info7
     * @param type $jr_id
     * @see XMLInvoice::build_data
     */
    function build_data($jr_id): array 
    {
        
        $this->data=parent::build_data($jr_id);
        return  $this->data;
    }
    /**
     * @brief Information customer
     */
    function build_customer()
    {
        
        $customer=$this->createElement('cac:AccountingCustomerParty');
        $customer_party=$customer->appendChild($this->createElement('cac:Party'));
        list($scheme_id,$peppol)=explode( ":",$this->data['customer']['endpoint_id']);
        $customer_party->appendChild($this->createElement('cbc:EndpointID',$peppol))
                ->setAttribute('schemeID', $scheme_id);
        
        $party_name=$this->createElement('cac:PartyName');
        $party_name->appendChild($this->createElement("cbc:Name", $this->data['customer']['name']));
        $customer_party->appendChild($party_name);
        $postal_address=$customer_party->appendChild($this->createElement('cac:PostalAddress'));
        $postal_address->appendChild($this->createElement("cbc:StreetName", $this->data['customer']['street']));
        $postal_address->appendChild($this->createElement("cbc:CityName", $this->data['customer']['city']));
        $postal_address->appendChild($this->createElement("cbc:PostalZone", $this->data['customer']['postalzone']));
        ///@todo customer = countryCode doit être dans les paramètres (voir upgrade.sql)
        $country_code =$this->data['customer']['country'];
        $country=$postal_address->appendChild($this->createElement("cac:Country"));
        
        $country->appendChild($this->createElement('cbc:IdentificationCode',$country_code));
        $postal_address->appendChild($country);
        
        // Tax Schem
        
        
        $tax=$this->createElement('cac:PartyTaxScheme');
        $tax->appendChild($this->createElement('cbc:CompanyID',$this->data["customer"]['customer_vat_id']));
        
        $tax_scheme=$this->createElement('cac:TaxScheme');
        $tax_scheme->appendChild($this->createElement('cbc:ID',"VAT"));
        $tax->appendChild($tax_scheme);
        // LegalEntity
        $ple=$this->createElement('cac:PartyLegalEntity');
           ///@todo customer = name doit être fiche
        $ple->appendChild($this->createElement("cbc:RegistrationName", $this->data['customer']['name']??"ERROR"));
           ///@todo customer_vat_id = numéro de TVA doit être dans fiche
        $ple->appendChild($this->createElement("cbc:CompanyID", $this->data['customer']['customer_vat_id']??"ERROR"));
        
        // assemble supplier
        $customer_party->appendChild($tax);
        $customer_party->appendChild($ple);
        $customer->appendChild($customer_party);
        return $customer;
    }
    /**
     * @brief Build XML Block for payment
     * @code
  <cac:PaymentMeans>
		<cbc:PaymentMeansCode>30</cbc:PaymentMeansCode>
                <cbc:PaymentID>Invoice 2019000005</cbc:PaymentID>
		<cac:PayeeFinancialAccount>
			<cbc:ID>BE54000000000097</cbc:ID>
			<cac:FinancialInstitutionBranch>
				<cbc:ID>BPOTBEB1</cbc:ID>
			</cac:FinancialInstitutionBranch>
		</cac:PayeeFinancialAccount>
	</cac:PaymentMeans>      
     * @endcode
     */
    function build_paymentInfo()
    {
      $company = $this->load_noalyss_parameter();
      $payment=$this->createElement("cac:PaymentMeans");
      $payment->appendChild($this->createElement('cbc:PaymentMeansCode',30));
      ///@note cbc:PaymentID est la communication lors du paiement
      $payment->appendChild($this->createElement('cbc:PaymentID',$this->data["info"]['communication']));
      $f=$this->createElement ('cac:PayeeFinancialAccount');
        ///@todo customer = IBAN doit être dans les paramètres (voir upgrade.sql)
      $f->appendChild($this->createElement("cbc:ID",$company['COMPANY_BANK_IBAN']));
      $g=$this->createElement("cac:FinancialInstitutionBranch");
      $g->appendChild($this->createElement("cbc:ID", $company['COMPANY_BANK_BIC']));
      $f->appendChild($g);
      
      $payment->appendChild($f);
      return $payment;
    }
    /**
     * @brief Information supplier
     */
    function build_supplier()
    {
        $company = $this->load_noalyss_parameter();
        
        $supplier=$this->createElement('cac:AccountingSupplierParty');
        $supplier_party=$supplier->appendChild($this->createElement('cac:Party'));
        list($scheme_id,$peppol)=explode( ":",$company['COMPANY_PEPPOL_ID']);
        $supplier_party->appendChild($this->createElement('cbc:EndpointID',$peppol))
                ->setAttribute('schemeID', $scheme_id);
        
        //$supplier_party->appendChild($this->createElement('cbc:EndpointID',$this->data["supplier"]['supplier_vat_id']))->setAttribute('schemeID', 9925);
        $party_name=$this->createElement('cac:PartyName');
        $party_name->appendChild($this->createElement('cbc:Name', $this->data['supplier']['name']));
        $supplier_party->appendChild($party_name);
        $postal_address=$supplier_party->appendChild($this->createElement('cac:PostalAddress'));
        $postal_address->appendChild($this->createElement("cbc:StreetName", $this->data['supplier']['street']));
        $postal_address->appendChild($this->createElement("cbc:CityName", $this->data['supplier']['city']));
        $postal_address->appendChild($this->createElement("cbc:PostalZone", $this->data['supplier']['postalzone']));
        $country_code = $company['MY_COUNTRY_CODE'];
        $country=$postal_address->appendChild($this->createElement("cac:Country"));
        $country->appendChild($this->createElement('cbc:IdentificationCode',$country_code??"ERROR"));
        $postal_address->appendChild($country);
        
        // Tax Schem
        $tax=$this->createElement('cac:PartyTaxScheme');
        $tax->appendChild($this->createElement('cbc:CompanyID',$this->data["supplier"]['supplier_vat_id']));
        $tax_scheme=$this->createElement('cac:TaxScheme');
        $tax_scheme->appendChild($this->createElement('cbc:ID',"VAT"));
        $tax->appendChild($tax_scheme);
        // LegalEntity
        $ple=$this->createElement('cac:PartyLegalEntity');
        $ple->appendChild($this->createElement("cbc:RegistrationName", $company['COMPANY_LEGAL_REGISTRATION']??"ERROR"));
        $ple->appendChild($this->createElement("cbc:CompanyID", $this->data['supplier']['supplier_vat_id']??"ERROR"));
        $ple->appendChild($this->createElement("cbc:CompanyLegalForm", $company['COMPANY_LEGAL_ENTITY']??"ERROR"));
        $contact=$this->createElement('cac:Contact');
        $contact->appendChild($this->createElement("cbc:Name",$company['INVOICE_CONTACT_NAME']??"ERROR"));
        $contact->appendChild($this->createElement("cbc:ElectronicMail",$company['INVOICE_EMAIL_COMPANY']??"ERROR"));
        
        // assemble supplier
        $supplier_party->appendChild($tax);
        $supplier_party->appendChild($ple);
        $supplier_party->appendChild($contact);
        $supplier->appendChild($supplier_party);
        
       
        return $supplier;
    }
    /**
     * @brief TaxTotal Block
     * @code
        <cac:TaxTotal>
		<cbc:TaxAmount currencyID="EUR">516</cbc:TaxAmount>
		<cac:TaxSubtotal>
			<cbc:TaxableAmount currencyID="EUR">2400</cbc:TaxableAmount>
			<cbc:TaxAmount currencyID="EUR">504</cbc:TaxAmount>
			<cac:TaxCategory>
				<cbc:ID>S</cbc:ID>
				<cbc:Percent>21</cbc:Percent>
				<cac:TaxScheme>
					<cbc:ID>VAT</cbc:ID>
				</cac:TaxScheme>
			</cac:TaxCategory>
		</cac:TaxSubtotal>
		<cac:TaxSubtotal>
			<cbc:TaxableAmount currencyID="EUR">200</cbc:TaxableAmount>
			<cbc:TaxAmount currencyID="EUR">12</cbc:TaxAmount>
			<cac:TaxCategory>
				<cbc:ID>S</cbc:ID>
				<cbc:Percent>6</cbc:Percent>
                               if ID !=Z and ID != S  <cbc:TaxExemptionReasonCode
				<cac:TaxScheme>
					<cbc:ID>VAT</cbc:ID>
				</cac:TaxScheme>
			</cac:TaxCategory>
		</cac:TaxSubtotal>
	</cac:TaxTotal>     
     * @endcode
     */
    function build_taxTotal()
    {
        $taxTotal=$this->createElement("cac:TaxTotal");
        $taxTotal->appendChild($this->createElement('cbc:TaxAmount',sprintf("%.2f",$this->data['TaxAmount'])))
                ->setAttribute("currencyID",$this->data['currency']);
        // for subTotal
        $subTotal=$this->data['subTotalVAT'];
        $nb_sub=count($subTotal);
        for ($i=0;$i<$nb_sub;$i++) {
            $subTotalXML=$this->createElement("cac:TaxSubtotal");
            $subTotalXML->appendChild($this->createElement('cbc:TaxableAmount',sprintf("%.2f",$subTotal[$i]['amount'])))
                    ->setAttribute("currencyID",  $this->data['currency']);
            $subTotalXML->appendChild($this->createElement('cbc:TaxAmount',sprintf("%.2f",$subTotal[$i]['vat'])))
                    ->setAttribute("currencyID",$this->data['currency']);
            $taxCategory=$this->createElement("cac:TaxCategory");
            $taxCategory->appendChild($this->createElement("cbc:ID",$subTotal[$i]['vat_code']));
            $taxCategory->appendChild($this->createElement("cbc:Percent",sprintf("%.2f",$subTotal[$i]['percent'])));
            if ($subTotal[$i]['vat_code'] != "S"
                    && $subTotal[$i]['vat_code'] != "Z")
            {
                // if cbc:ID not S and not Z then exemption VAT code is needed
                 $taxCategory->appendChild($this->createElement("cbc:TaxExemptionReasonCode",$subTotal[$i]['vatex']));
            }
            $taxScheme=$this->createElement("cac:TaxScheme");
            $taxScheme->appendChild($this->createElement("cbc:ID", "VAT"));
            $taxCategory->appendChild($taxScheme);
            $subTotalXML->appendChild($taxCategory);
            $taxTotal->appendChild($subTotalXML);
        }
        
        
        return $taxTotal;
    }
    /**
     * @brief legalMonetaryTotal
     * @code
   	<cac:LegalMonetaryTotal>
		<cbc:LineExtensionAmount currencyID="EUR">2600</cbc:LineExtensionAmount>
		<cbc:TaxExclusiveAmount currencyID="EUR">2600</cbc:TaxExclusiveAmount>
		<cbc:TaxInclusiveAmount currencyID="EUR">3116</cbc:TaxInclusiveAmount>
		<cbc:PayableAmount currencyID="EUR">3116</cbc:PayableAmount>
	</cac:LegalMonetaryTotal>   
     * @endcode
     */
    function build_legalMonetaryTotal()
    {
        $result=$this->createElement('cac:LegalMonetaryTotal' );
        $result->appendChild($this->createElement("cbc:LineExtensionAmount",sprintf("%.2f",$this->data['LineExtensionAmount'])))
                ->setAttribute("currencyID",$this->data['currency']);
        $result->appendChild($this->createElement("cbc:TaxExclusiveAmount",sprintf("%.2f",$this->data['TaxExclusiveAmount'])))
                ->setAttribute("currencyID",$this->data['currency']);
        $result->appendChild($this->createElement("cbc:TaxInclusiveAmount",sprintf("%.2f",$this->data['TaxInclusiveAmount'])))
                ->setAttribute("currencyID", $this->data['currency'] );
        $result->appendChild($this->createElement("cbc:PayableAmount",sprintf("%.2f",$this->data['PayableAmount'])))
                ->setAttribute("currencyID", $this->data['currency'] );
        return $result;
        
    }
    /**
     * @brief cac:InvoiceLine 
     * @code
	<cac:InvoiceLine>
		<cbc:ID>2</cbc:ID>
		<cbc:InvoicedQuantity unitCode="C62">10</cbc:InvoicedQuantity>
		<cbc:LineExtensionAmount currencyID="EUR">200</cbc:LineExtensionAmount>
		<cac:Item>
			<cbc:Name>Good X</cbc:Name>
			<cac:ClassifiedTaxCategory>
				<cbc:ID>S</cbc:ID>
				<cbc:Percent>6</cbc:Percent>
				<cac:TaxScheme>
					<cbc:ID>VAT</cbc:ID>
				</cac:TaxScheme>
			</cac:ClassifiedTaxCategory>
		</cac:Item>
		<cac:Price>
			<cbc:PriceAmount currencyID="EUR">20</cbc:PriceAmount>
		</cac:Price>
	</cac:InvoiceLine>     
     * @endcode
     * @param $i (int) idx f $this->data->operation[$i]
     *      
     */
    function build_invoiceLine($i)
    { 
        
        $result=$this->createElement('cac:InvoiceLine');
        $row=$this->data["operation"][$i];
        $amount=sprintf("%.2f",$row['price']);

        $result->appendChild($this->createElement("cbc:ID", $i));
        $amount=sprintf("%.2f",$row['price']);
        $result->appendChild(
                $this->createElement("cbc:InvoicedQuantity", sprintf("%.2f",$row['quantity'])))
                ->setAttribute("unitCode", $row["code_quantity"]);
        $result->appendChild($this->createElement("cbc:LineExtensionAmount", $amount))
                ->setAttribute("currencyID",$this->data['currency']);
        
        // ITEM
        $item=$this->createElement("cac:Item");
        $item->appendChild($this->createElement("cbc:Description",$row['name']));
        $item->appendChild($this->createElement("cbc:Name", $row['qcode']));
        $classifiedTaxCat=$this->createElement("cac:ClassifiedTaxCategory");
        
        //cbc:ID S  = standard rate 
        /// see TVA_RATE.TVA_PEPPOL_CODE & C0TVA
        $classifiedTaxCat->appendChild($this->createElement("cbc:ID", $row['vat_code']));
        $classifiedTaxCat->appendChild($this->createElement("cbc:Percent", sprintf("%.2f",$row['vat_percent'])));

        $tax_scheme=$this->createElement('cac:TaxScheme');
        $tax_scheme->appendChild($this->createElement("cbc:ID", "VAT"));
        $classifiedTaxCat->appendChild($tax_scheme);
        $item->appendChild($classifiedTaxCat);
        $result->appendChild($item);
        $price=$result->appendChild($this->createElement("cac:Price"));
        $price->appendChild($this->createElement("cbc:PriceAmount",sprintf("%.2f",abs($row['price_unit']))))
                ->setAttribute("currencyID",$this->data['currency']);
        $result->appendChild($price);
            
        return $result;
        
    }
    /**
     * @brief Insert a PDF in the XML
     * the document type is not needed for BELGIUM
     */
    /**
     * 
@code      
 <cac:AdditionalDocumentReference>
    <cbc:ID>P01</cbc:ID>
    <cbc:DocumentDescription>Facture PDF</cbc:DocumentDescription>
    <cac:Attachment>
      <cbc:EmbeddedDocumentBinaryObject
          mimeCode="application/pdf"
          filename="invoice.pdf">$base64Pdf</cbc:EmbeddedDocumentBinaryObject>
    </cac:Attachment>
  </cac:AdditionalDocumentReference>
     <!--     OU -->
      <cac:AdditionalDocumentReference>
    <cbc:ID>REF_ODT_001</cbc:ID>
    <cbc:DocumentDescription>Fichier OpenDocument</cbc:DocumentDescription>
    <cac:Attachment>
        <cbc:EmbeddedDocumentBinaryObject
            mimeCode="application/vnd.oasis.opendocument.text"
            filename="facture.odt">[base64-encodage du fichier]</cbc:EmbeddedDocumentBinaryObject>
    </cac:Attachment>
</cac:AdditionalDocumentReference>


@endcode
     * @return \DOMElement
     */
    function build_Invoice():\DOMElement
    {
        if ( $this->pdf_filename == "") return null;
      /**  $pdf_filename = 'chemin/vers/votre/fichier.pdf';*/
        static $i=0;
        $i++;
        if ( $this->pdf_filename == null ) {
            return null;
        }
        // Lire le fichier PDF  
        $pdfContent = file_get_contents( $this->pdf_filename   );

        $result=$this->createElement("cac:AdditionalDocumentReference");
        $id=$this->createElement("cbc:ID",$i);
        $document_description=$this->createElement("cbc:DocumentDescription"
                , $this->data['description']);
        
        // PDF in base64
        $base64Pdf = base64_encode($pdfContent);
        $embeddedDocument=$this->createElement("cbc:EmbeddedDocumentBinaryObject",$base64Pdf);
        $embeddedDocument->setAttribute("mimeCode", "application/pdf");
        $embeddedDocument->setAttribute("filename", "facture.pdf");
        $attachment=$this->createElement("cac:Attachment");
        $attachment->appendChild($embeddedDocument);
        
        $result->appendChild($id);
        $result->appendChild($document_description);
        $result->appendChild($attachment);
        
        return $result;
    }
     /**
     * @brief create an XML invoice(UBL2.1) based on JRN.JR_ID operation
     * @parameter $jr_id (int) operation JRN.JR_ID operation
     * @return XML String
     */
    function make_xml($jr_id)
    {
        

        $this->data = $this->build_data($jr_id);
        
        // var $company (array) all the parameters of the company
        $company = $this->load_noalyss_parameter();
        
        // HEADER
        $root=$this->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',"Invoice",);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/',"xmlns:cac", "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2");
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/',"xmlns:cbc", "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2");
        $root->appendChild($this->createElement('cbc:CustomizationID',"urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0"));
        $root->appendChild($this->createElement('cbc:ProfileID',"urn:fdc:peppol.eu:2017:poacc:billing:01:1.0"));
        
        $root->appendChild($this->createElement('cbc:ID',$this->data['id']));
        $root->appendChild($this->createElement('cbc:IssueDate',$this->data['issue_date']));
        if ($this->data ['due_date'] == '') 
        {
            $this->data ['due_date']=$this->data['issue_date'];
        }
        $root->appendChild($this->createElement('cbc:DueDate',$this->data['due_date']));
        $root->appendChild($this->createElement('cbc:InvoiceTypeCode',380));
        $root->appendChild($this->createElement('cbc:DocumentCurrencyCode',$this->data['currency']));
        $root->appendChild($this->createElement('cbc:BuyerReference',$this->data['info']['order']));
        /**
         * insert PDF in the XML
         */
        $x = $this->build_Invoice();
        if ($x != null  ) {
            $root->appendChild($x);
        }

        // add the supplier
        $root->appendChild($this->build_supplier());
        // add the customer
        $root->appendChild($this->build_customer());
        
        // add the payment  if there is a bank account
        if ( $company['COMPANY_BANK_IBAN'] != "")
            $root->appendChild($this->build_paymentInfo());
        
        // Add cac:TaxTotal
        $root->appendChild($this->build_taxTotal());
        
        // Add cac:LegalMonetatyTotal
        $root->appendChild($this->build_legalMonetaryTotal());
        
        // add all the invoiceline 
         // operation
        $nb_operation=count($this->data["operation"]);
        for ($i=0;$i < $nb_operation ; $i++) {
            $root->appendChild($this->build_invoiceLine($i));
            
        }

        $this->append($root);
        $this->formatOutput=true;
        return $this->saveXML();
    }
    /**
     * @brief create the invoice in the right format, with PDF if any
     * @param $operation_id (int) JRN.JR_ID
     * @return string XML Invoice
     */
    function create_invoice($operation_id) {
        return $this->make_xml($operation_id);
    }
}
