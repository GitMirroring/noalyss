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
 * @brief answer to an inplace object
 */
/**
 * @class
 * @brief UBL2.1 Belgique
 * @note Doit contenir le PDF
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
        , 'COMPANY_UBL_ID'
        , 'MY_COUNTRY_CODE'
        , 'MY_NAME'
        , 'MY_STREET'
        , 'MY_CITY'
        , 'MY_TVA'
        ];
    protected $pdf_filename; //!< PDF file to insert into XML
    public function get_pdf_filename() {
        return $this->pdf_filename;
    }

    public function set_pdf_filename($pdf_filename) {
        $this->pdf_filename = $pdf_filename;
        return $this;
    }

    /**
     * @brief check that mandatory info are saved in the DB for company (seller)
     * @param $a_error (array) array of errors, empty if nothing found
     */
    function check_company_data(&$a_error) {
        $company = $this->load_noalyss_parameter();
        foreach (InvoiceUBL21::EXTRA_PARAMETER as $item) {
            if (!isset($company[$item]) || $company[$item] == '') {
                $a_error[]=$item;
            }
        }
        if (count($a_error)  == 0) {
            return true;
        }
        return false;
    }
     /**
     * @brief check that mandatory info are saved in the DB for customer
     * @param $customer_id (int) card of the customer  FICHE.F_ID
     * @param $a_error (array) array of errors, empty if nothing found
     * @todo : country code au lieu de country !! 
     */
    function check_customer_data($customer_id,&$a_error){
       $card=new \Fiche($this->cn,$customer_id);
        $a_needed=[ATTR_DEF_NAME=>_("Nom")
                ,ATTR_DEF_ADRESS=>_("Adresse")
                ,ATTR_DEF_POSTCODE=>_("Code postal")
                ,ATTR_DEF_CITY=>_("Localité")
                ,ATTR_DEF_COUNTRY_CODE=>_("Code pays")
                ,ATTR_DEF_NUMTVA=>_("Numéro de TVA")
            ];
        
        foreach ($a_needed as $item=>$value) {
            if (\noalyss_trim($card->strAttribut($item))=="") {
                printf (_("ATTENTION donnée manquante dans la fiche client [%s]"),$value);
            }
        }
        if (count($a_error)  == 0) {
            return true;
        }
        return false;
    }
    /**
     * @brief transform an operation ($jr_id) into an array, which contains
     * needed information for making an e-invoice
     * @param $jr_id (int) operation JRN.JR_ID
     * @return array with all info7
     * @param type $jr_id
     * @see XMLInvoice::build_data
     */
    function build_data($jr_id): array {
        $result = parent::build_data($jr_id);
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
            $percent = bcmul($acc_tva->tva_rate,100);
            // subtotal for VAT
            var_dump($VAT_SubTotal);
            $n = \Noalyss\Invoicing\Utility::find_idx($VAT_SubTotal,'percent',$percent);
            if ($n == -1 ) {
                $n=$idx_subtotal;
                $VAT_SubTotal[$idx_subtotal]=array();
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
        $result['PayableAmount']=bcadd( $result['TaxableAmount'],$result['TaxAmount']);;
        
        $this->data=$result;
        return $result;
    }
    /**
     * @brief Information customer
     */
    function build_customer()
    {
        
        $customer=$this->createElement('cac:AccountingCustomerParty');
        $customer_party=$customer->appendChild($this->createElement('cac:Party'));
        ///@todo EndPointID doit être dans les paramètres (voir upgrade.sql)
        $customer_party->appendChild($this->createElement('cbc:EndpointID',"ERROR"))->setAttribute('schemeID', 9956);
        $party_name=$this->createElement('cac:PartyName');
        $party_name->appendChild($this->createElement("cbc:Name", $this->data['customer']['name']));
        $customer_party->appendChild($party_name);
        $postal_address=$customer_party->appendChild($this->createElement('cac:PostalAddress'));
        $postal_address->appendChild($this->createElement("cbc:StreetName", $this->data['customer']['street']));
        $postal_address->appendChild($this->createElement("cbc:CityName", $this->data['customer']['city']));
        $postal_address->appendChild($this->createElement("cbc:PostalZone", $this->data['customer']['postalzone']));
        ///@todo customer = countryCode doit être dans les paramètres (voir upgrade.sql)
        $country_code ="ERROR";
        $country=$postal_address->appendChild($this->createElement("cac:Country"));
        $country->appendChild($this->createElement('cbc:IdentificationCode',$country_code??"ERROR:COUNTRY_CODE"));
        $postal_address->appendChild($country);
        
        // Tax Schem
        
        
        $tax=$this->createElement('cac:PartyTaxScheme');
        $tax->appendChild($this->createElement('cbc:CompanyID',$this->data["customer"]['customer_id']));
        
        $tax_scheme=$this->createElement('cac:TaxScheme');
        $tax_scheme->appendChild($this->createElement('cbc:ID',"VAT"));
        $tax->appendChild($tax_scheme);
        // LegalEntity
        $ple=$this->createElement('cac:PartyLegalEntity');
           ///@todo customer = name doit être fiche
        $ple->appendChild($this->createElement("cbc:RegistrationName", $this->data['customer']['name']??"ERROR"));
           ///@todo customer_id = numéro de TVA doit être dans fiche
        $ple->appendChild($this->createElement("cbc:CompanyID", $this->data['customer']['customer_id']??"ERROR"));
        
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
      $payment=$this->createElement("cac:PaymentMeans");
      $payment->appendChild($this->createElement('cbc:PaymentMeansCode',30));
      ///@note cbc:PaymentID est la communication lors du paiement
      $payment->appendChild($this->createElement('cbc:PaymentID',$this->data["id"]));
      $f=$this->createElement ('cac:PayeeFinancialAccount');
        ///@todo customer = IBAN doit être dans les paramètres (voir upgrade.sql)
      $f->appendChild($this->createElement("cbc:ID", "ERROR:IBAN"));
      $g=$this->createElement("cac:FinancialInstitutionBranch");
         ///@todo customer = BIC doit être dans les paramètres (voir upgrade.sql)
      $g->appendChild($this->createElement("cbc:ID", "ERROR:BIC"));
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
        $supplier_party->appendChild($this->createElement('cbc:EndpointID',$company['COMPANY_UBL_ID']??"ERROR"))->setAttribute('schemeID', 9956);
        $party_name=$this->createElement('cac:PartyName');
        $party_name->appendChild($this->createElement('cbc:Name', $this->data['supplier']['name']));
        $supplier_party->appendChild($party_name);
        $postal_address=$supplier_party->appendChild($this->createElement('cac:PostalAddress'));
        $postal_address->appendChild($this->createElement("cbc:StreetName", $this->data['supplier']['street']));
        $postal_address->appendChild($this->createElement("cbc:CityName", $this->data['supplier']['city']));
        $postal_address->appendChild($this->createElement("cbc:PostalZone", $this->data['supplier']['postalzone']));
        $country_code = $company['COUNTRY_CODE'];
        $country=$postal_address->appendChild($this->createElement("cac:Country"));
        $country->appendChild($this->createElement('cbc:IdentificationCode',$country_code??"ERROR"));
        $postal_address->appendChild($country);
        
        // Tax Schem
        $tax=$this->createElement('cac:PartyTaxScheme');
        $tax->appendChild($this->createElement('cbc:CompanyID',$this->data["supplier"]['supplier_id']));
        $tax_scheme=$this->createElement('cac:TaxScheme');
        $tax_scheme->appendChild($this->createElement('cbc:ID',"VAT"));
        $tax->appendChild($tax_scheme);
        // LegalEntity
        $ple=$this->createElement('cac:PartyLegalEntity');
        $ple->appendChild($this->createElement("cbc:RegistrationName", $company['COMPANY_LEGAL_REGISTRATION']??"ERROR"));
        $ple->appendChild($this->createElement("cbc:CompanyID", $this->data['supplier']['supplier_id']??"ERROR"));
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
        $taxTotal->appendChild($this->createElement('cbc:TaxAmount',$this->data['TaxAmount']))
                ->setAttribute("currencyID","EUR");
        // for subTotal
        $subTotal=$this->data['subTotalVAT'];
        $nb_sub=count($subTotal);
        for ($i=0;$i<$nb_sub;$i++) {
            $subTotalXML=$this->createElement("cac:TaxSubtotal");
            $subTotalXML->appendChild($this->createElement('cbc:TaxableAmount',$subTotal[$i]['amount']))
                    ->setAttribute("currencyID","EUR");
            $subTotalXML->appendChild($this->createElement('cbc:TaxAmount',$subTotal[$i]['vat']))
                    ->setAttribute("currencyID","EUR");
            $taxCategory=$this->createElement("cac:TaxCategory");
            $taxCategory->appendChild($this->createElement("cbc:ID","S"));
            $taxCategory->appendChild($this->createElement("cbc:Percent",$subTotal[$i]['percent']));
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
        $result->appendChild($this->createElement("cbc:LineExtensionAmount",$this->data['LineExtensionAmount']))
                ->setAttribute("currencyID","EUR");
        $result->appendChild($this->createElement("cbc:TaxExclusiveAmount",$this->data['TaxExclusiveAmount']))
                ->setAttribute("currencyID","EUR");
        $result->appendChild($this->createElement("cbc:TaxInclusiveAmount",$this->data['TaxInclusiveAmount']))
                ->setAttribute("currencyID","EUR");
        $result->appendChild($this->createElement("cbc:PayableAmount",$this->data['PayableAmount']))
                ->setAttribute("currencyID","EUR");
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
     * @parameter $i (int) idx f $this->data->operation[$i]
     *      
     */
    function build_invoiceLine($i)
    { 
        
        $result=$this->createElement('cac:InvoiceLine');
        $row=$this->data["operation"][$i];
        $result->appendChild($this->createElement("cbc:ID", $i));
        ///@todo , les unités de quantités devraient être ajoutés à NOALYSS
        /// il faut adapter les fiches
        $result->appendChild(
                $this->createElement("cbc:InvoicedQuantity", $row['quantity']))
                ->setAttribute("unitCode", "EA");
        $result->appendChild($this->createElement("cbc:LineExtensionAmount", $row['price']))
                ->setAttribute("currencyID","EUR");
        $item=$this->createElement("cac:Item");
        $card=new \Fiche($this->cn,$row['card_id']);
        $item->appendChild($this->createElement("cbc:Name", $card->strAttribut(ATTR_DEF_NAME)));
        $classifiedTaxCat=$this->createElement("cac:ClassifiedTaxCategory");
        ///@todo cbc:ID S  = standard rate et que se passe-t'il pour l'autoliquidation ???
        /// Il faut ajouter dans TVA_RATE , un code pour la TVA, 
        $classifiedTaxCat->appendChild($this->createElement("cbc:ID", "S"));
        $classifiedTaxCat->appendChild($this->createElement("cbc:Percent", $row['vat_percent']));
        $tax_scheme=$this->createElement('cac:TaxScheme');
        $tax_scheme->appendChild($this->createElement("cbc:ID", "VAT"));
        $classifiedTaxCat->appendChild($tax_scheme);
        $item->appendChild($classifiedTaxCat);
        $result->appendChild($item);
        $price=$result->appendChild($this->createElement("cac:Price"));
        $price->appendChild($this->createElement("cbc:PriceAmount", $row['price']))
                ->setAttribute("currencyID","EUR");
        $result->appendChild($price);
            
        return $result;
        
    }
    /**
     * @brief Insert a PDF in the XML
@code      
 <cac:AdditionalDocumentReference>
    <cbc:ID>P01</cbc:ID>
    <cbc:DocumentType>InvoicePDF</cbc:DocumentType>
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
    <cbc:DocumentType>OpenDocument</cbc:DocumentType>
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
        $result=$this->createElement("AdditionalDocumentReference");
      /**  $pdf_filename = 'chemin/vers/votre/fichier.pdf';*/

        // Lire le fichier PDF  
       // $pdfContent = file_get_contents($pdfPath);

        // Encoder le PDF en base64
      //  $base64Pdf = base64_encode($pdfContent);
        
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
        
        // HEADER
        $root=$this->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',"Invoice",);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/',"xmlns:cac", "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2");
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/',"xmlns:cbc", "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2");
        $root->appendChild($this->createElement('cbc:CustomizationID',"urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0"));
        $root->appendChild($this->createElement('cbc:ProfileID',"urn:fdc:peppol.eu:2017:poacc:billing:01:1.0"));
        
        $root->appendChild($this->createElement('cbc:ID',$this->data['id']));
        $root->appendChild($this->createElement('cbc:IssueDate',$this->data['issue_date']));
        if ($this->data ['due_date'] != '') {
            $root->appendChild($this->createElement('cbc:DueDate',$this->data['due_date']));
        }
        $root->appendChild($this->createElement('cbc:InvoiceTypeCode',380));
        $root->appendChild($this->createElement('cbc:DocumentCurrencyCode','EUR'));
        
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
        // add the payment 
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
