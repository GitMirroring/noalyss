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
abstract class XML_Reader
{

    protected \DOMDocument $domDocument;
    protected readonly \DOMXPath $xpath;

    public function __construct(\DOMDocument $domDocument)
    {
        $this->domDocument = $domDocument;
        $this->xpath = new \DOMXPath($this->domDocument);
    }
    /**
     * @brief before executing xpath->query the namespace must be registered
     * the NS are different for each type of doc
     * @param $xml (null , DOMXPath simpleXML) 
     */
    public function registerNS($xml=null)
    {
        $a_namespace=$this->get_namespace();
        if ($xml == null)
        {
           foreach ($a_namespace as $pf=>$ns) {
                $this->xpath->registerNamespace($pf,$ns);
            } 
        }
        elseif ( get_class($xml) == "SimpleXMLElement" )
        {
            foreach ($a_namespace as $pf=>$ns) {
                $xml->registerXPathNamespace($pf,$ns);
            }
        }
        elseif (get_class($xml) == "DOMXPath" ) 
        {
            foreach ($a_namespace as $pf=>$ns) {
                $xml->registerNamespace($pf,$ns);
            }
            
        }
        else 
        {
            throw new \Exception ("unknown object ".get_class($xml),57);
        }
    }
    /**
     * 
     * @return \DOMDocument
     */
    public function get_domDocument(): \DOMDocument
    {
        return $this->domDocument;
    }

    public function get_xpath(): \DOMXPath
    {
        return $this->xpath;
    }

    public function set_domDocument($domDocument)
    {
        $this->domDocument = $domDocument;
        return $this;
    }

    /**
     * @brief Build an XMLInvoice_Reader object from an XML file
     * @param $filename (string) file and path to the file
     * @return \Noalyss\XMLDocument\XMLInvoice_Reader
     * @throws \Exception if xml not valid
     */
    abstract static function build_from_file($filename);

    /**
     * @brief Build an XMLInvoice_Reader object from an XML string
     * @param $string (string) XML
     * @return \Noalyss\XMLDocument\XMLInvoice_Reader
     * @throws \Exception if xml not valid
     */
    abstract static  function build_from_string($string);



    /**
     * @brief returns the embedded document in an array (keys : filecontent (BYTES),mimecode , filename)
     * or false if there is no document
     * @return bool|array of Document_Reference
     * @throws \Exception if there are several documents
     * 
     */
    public function get_embedded_document()
    {
        $a_document_reference = array();
        foreach (array(
    "AdditionalDocumentReference",
    "ReceiptDocumentReference",
    "StatementDocumentReference",
    "OriginatorDocumentReference",
    "ContractDocumentReference"
        )
        as $document)
        {
            $document = $this->domDocument->getElementsByTagName($document);
            if ($document->length == 0)
            {
                continue;
            }
            for ($e = 0; $e < $document->count(); $e++)
            {
                $item = $document->item($e);

                if ($item->nodeType == XML_ELEMENT_NODE)
                {
                    $id = $item->getElementsByTagName("ID")[0]?->nodeValue;
                    $description = $item->getElementsByTagName("DocumentDescription")[0]?->nodeValue;
                    $embedded_document = $item->getElementsByTagName("EmbeddedDocumentBinaryObject");
                    if ($embedded_document->length == 0)
                    {
                        continue;
                    }
                    $binary_object = new Binary_Object;
                    $binary_object->filecontent = base64_decode($embedded_document[0]->nodeValue);
                    $binary_object->mimecode = $embedded_document[0]->getAttribute("mimeCode");
                    $binary_object->filename = $embedded_document[0]->getAttribute("filename");
                    $document_reference = new Document_Reference();
                    $document_reference->setId($id)
                            ->setDescription($description)
                            ->setBinary_object($binary_object);
                    $a_document_reference[] = clone $document_reference;
                }
            }
        }
        return $a_document_reference;
    }
    /**
     * @brief execute a XPATH query of the DOMDocument and return the result
     * or null if nothing found
     * @param $query (string) valid XPath Query
     * @return null or DOMNodeList or DOMElement
     */
    public function get_node($query)
    {
       
        $this->registerNS();
        if ($this->xpath->query($query)->length == 0)
        {
            return null;
        }
        return $this->xpath->query($query);
    }
    /**
     * @brief get the node value 
     * @param $query (string) valid XPath query 
     * @param $ix (int) the item number
     * @return string
     */
    function get_node_value($query, $ix = 0)
    {
        return $this->get_node($query)?->item($ix)?->nodeValue;
    }

    /**
     * @brief get the customer info from XML
     * @return array
     */
    function get_customer(): array
    {
        $result = [];
        $result['ID'] = $this->get_node_value("//cac:AccountingCustomerParty[1]/cac:Party[1]/cbc:EndpointID[1]");
        $result['name'] = $this->get_node_value('//cac:AccountingCustomerParty[1]/cac:Party[1]/cac:PartyName[1]/cbc:Name[1]');
        $result['street'] = $this->get_node_value('//cac:AccountingCustomerParty[1]/cac:Party[1]/cac:PostalAddress[1]/cbc:StreetName[1]');
        $result['city'] = $this->get_node_value('//cac:AccountingCustomerParty[1]/cac:Party[1]/cac:PostalAddress[1]/cbc:CityName[1]');
        $result['postcode'] = $this->get_node_value('//cac:AccountingCustomerParty[1]/cac:Party[1]/cac:PostalAddress[1]/cbc:PostalZone[1]');
        $result['country_code'] = $this->get_node_value("//cac:AccountingCustomerParty[1]/cac:Party[1]/cac:PostalAddress[1]/cac:Country[1]/cbc:IdentificationCode[1]");
        $result['company_id'] = $this->get_node_value("//cac:AccountingCustomerParty[1]/cac:Party[1]/cac:PartyTaxScheme[1]/cbc:CompanyID[1]");
        $result['scheme'] = $this->get_node("//cac:AccountingCustomerParty[1]/cac:Party[1]/cbc:EndpointID[1]")[0]->getAttribute("schemeID");
        return $result;
    }

    /**
     * @brief get the supplier info from XML
     * @return array
     */
    function get_supplier(): array
    {
        $result = [];
        $result['ID'] = $this->get_node_value("//cac:AccountingSupplierParty[1]/cac:Party[1]/cbc:EndpointID[1]");
        $result['name'] = $this->get_node_value("//cac:AccountingSupplierParty[1]/cac:Party[1]/cac:PartyName[1]/cbc:Name[1]");
        $result['street'] = $this->get_node_value("//cac:AccountingSupplierParty[1]/cac:Party[1]/cac:PostalAddress[1]/cbc:StreetName[1]");
        $result['city'] = $this->get_node_value("//cac:AccountingSupplierParty[1]/cac:Party[1]/cac:PostalAddress[1]/cbc:CityName[1]");
        $result['postcode'] = $this->get_node_value("//cac:AccountingSupplierParty[1]/cac:Party[1]/cac:PostalAddress[1]/cbc:PostalZone[1]");
        $result['country_code'] = $this->get_node_value("//cac:AccountingSupplierParty[1]/cac:Party[1]/cac:PostalAddress[1]/cac:Country[1]/cbc:IdentificationCode[1]");
        $result['company_id'] = $this->get_node_value("//cac:AccountingSupplierParty[1]/cac:Party[1]/cac:PartyTaxScheme[1]/cbc:CompanyID[1]");
        $result['scheme'] = $this->get_node("//cac:AccountingSupplierParty[1]/cac:Party[1]/cbc:EndpointID[1]")[0]->getAttribute("schemeID");
        return $result;
    }

   

    /**
     * @brief get the Payment Means info from XML
     * @return array
     */
    function get_payment_mean(): array
    {
        $result = [];
        $result['payment_code'] = $this->get_node_value("//cac:PaymentMeans[1]/cbc:PaymentMeansCode[1]");
        $result['label'] = $this->get_node_value("//cac:PaymentMeans[1]/cbc:PaymentID[1]");
        $result['iban'] = $this->get_node_value("//cac:PaymentMeans[1]/cac:PayeeFinancialAccount[1]/cbc:ID[1]");
        $result['bic'] = $this->get_node_value("//cac:PaymentMeans[1]/cac:PayeeFinancialAccount[1]/cac:FinancialInstitutionBranch[1]/cbc:ID[1]");
        $result['note'] = [];
        $note = $this->get_node("//cac:PaymentTerms/cbc:Note");
        if ($note != null)
        {
            $a = $note->length;
            for ($i = 0; $i < $a; $i++)
            {
                $result['note'][] = $note->item(0)->nodeValue;
            }
        }
        /**
          <cac:PaymentTerms>
          <cbc:Note>	In geval van betaling binnen 14 dagen is 2% (52.00€) betalingskorting van
          toepassing 	en het te betalen bedrag = 3053.68€
          En cas de paiement dans les 14 jours, l'escompte conditionnel de 2% (52.00€) est appliqué et le montant payable = 3053.68€
          In case of payment within 14 days, 2% (52.00€) conditional cash/payment discount applies and the payable amount = 3053.68€
          </cbc:Note>
          </cac:PaymentTerms>
         */
        return $result;
    }

    /**
     * @brief get the node value from simpleXML
     * @param $xml (\SimpleXMLElement) fragment of XML dom
     * @param $query (string) XPath query
     * @return string
     */
    protected function get_simple_xml_value(\SimpleXMLElement $xml, $query): string
    {
        $this->registerNS($xml);
        
        $x = $xml->xpath($query);
        $result = (count($x) == 0) ? "" : $x[0];
        return (string) $result;
    }

    /**
     * @brief get the amount summary info from XML
     * @return array
     */
    function get_amount_summary(): array
    {
        $result = [];
        $node = $this->get_node("//cac:LegalMonetaryTotal");
        $xml = simplexml_import_dom($node->item(0));
        $result['LineExtensionAmount'] = $this->get_simple_xml_value($xml, "cbc:LineExtensionAmount");
        $result['TaxExclusiveAmount'] = $this->get_simple_xml_value($xml, "cbc:TaxExclusiveAmount");
        $result['TaxInclusiveAmount'] = $this->get_simple_xml_value($xml, "cbc:TaxInclusiveAmount");
        $result['PayableAmount'] = $this->get_simple_xml_value($xml, "cbc:PayableAmount");
        $result['AllowanceTotalAmount'] = $this->get_simple_xml_value($xml, "cbc:AllowanceTotalAmount");
        $result['AllowanceTotalAmount'] = ($result['AllowanceTotalAmount'] == "") ? 0 : $result['AllowanceTotalAmount'];
        $result['ChargeTotalAmount'] = $this->get_simple_xml_value($xml, "cbc:ChargeTotalAmount");
        $result['ChargeTotalAmount'] = ($result['ChargeTotalAmount'] == "") ? 0 : $result['ChargeTotalAmount'];
        return $result;
    }

    /**
     * @brief get the customer info from XML
     * @return array
     */

    /**
     * 
      @code
      <cac:AllowanceCharge>
        <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
        <cbc:AllowanceChargeReasonCode>64</cbc:AllowanceChargeReasonCode>
        <cbc:AllowanceChargeReason>Conditional cash/payment discount | Korting contant | Escompte Conditionnel 2%</cbc:AllowanceChargeReason>
        <cbc:Amount currencyID="EUR">4.00</cbc:Amount>
        <cac:TaxCategory>
        <cbc:ID>S</cbc:ID>
        <cbc:Percent>6.00</cbc:Percent>
        <cac:TaxScheme>
        <cbc:ID>VAT</cbc:ID>
        </cac:TaxScheme>
        </cac:TaxCategory>
      </cac:AllowanceCharge>
      @encode
     */
    function get_allowance(): array
    {
        $result = [];
        return $result;
    }
    /**
     * @brief return the code of the document (cbc:"document"TypeCode)
     * @return string "document string + cbc:"document"TypeCode"
     */
    abstract function get_document_type_code();
    /**
     * 
     * @return array
     * @TODODNY
     * Implémenter les allowances
     */
    function get_info(): array
    {
        $result = [];
        $result['id'] = $this->get_node_value('cbc:ID');
        $result['IssueDate'] = $this->get_node_value('cbc:IssueDate');
        $result['DueDate'] = $this->get_node_value('cbc:DueDate');
        $result['InvoiceTypeCode'] =$this->get_document_type_code();
        $result['DocumentCurrencyCode'] = $this->get_node_value('cbc:DocumentCurrencyCode');
        $result['BuyerReference'] = $this->get_node_value('cbc:BuyerReference');
        $result['ActualDeliveryDate'] = $this->get_node_value('//cac:Delivery[1]/cbc:ActualDeliveryDate[1]');
        /*
          <cac:Delivery>
          <cbc:ActualDeliveryDate>2018-07-01</cbc:ActualDeliveryDate>
          </cac:Delivery>
         */
        return $result;
    }

    /**
     * @brief make a PDF with the information from XMLInvoice
     * @param \Database $cn
     * @returns \PDF
     */
    public function to_pdf(\Database $cn): \PDF
    {
        $pdf = new \PDF($cn);
        $result = $this->get_info();

        $pdf->setDossierInfo(_(" id ") . " " . $result['id']);
        $pdf->AliasNbPages();
        $pdf->setAuthor("Noalyss");
        $pdf->AddPage();
        // 180 mm large
        $pdf->setFont("DejaVu", "B", 12);
        $pdf->write_cell(60, 4, _("Information facture"));
        $pdf->line_new(10);
        $pdf->setFont("DejaVu", "", 7);
        $pdf->write(4, sprintf(_("Document ID %s"), $result['id']));
        $pdf->ln();
        $pdf->write(4, sprintf(_("Date facture %s"), $result['IssueDate']));
        $pdf->ln();
        $pdf->write(4, sprintf(_("Date échéance %s"), $result['DueDate']));
        $pdf->ln();
        $pdf->write(4, sprintf(_("Type et code document %s"), $result['InvoiceTypeCode']));
        $pdf->ln();
        $pdf->write(4, sprintf(_("Devise document %s"), $result['DocumentCurrencyCode']));
        $pdf->ln();
        $pdf->write(4, sprintf(_("Référence client %s"), $result['BuyerReference']));
        $pdf->ln();
        $pdf->write(4, sprintf(_("Date Livraison %s"), $result['ActualDeliveryDate']));
        $pdf->ln(10);

        $pdf->setFont("DejaVu", "B", 12);
        $pdf->write_cell(60, 4, _("Fournisseur"));
        $pdf->line_new(12);
        $supplier = $this->get_supplier();
        $pdf->setFont("DejaVu", "", 7);
        $pdf->write_cell(60, 4, $supplier['name']);
        $pdf->write_cell(60, 4, $supplier['company_id']);
        $pdf->write_cell(60, 4, $supplier['ID']);
        $pdf->line_new();
        $pdf->write_cell(60, 4, $supplier['street']);
        $pdf->write_cell(30, 4, $supplier['postcode']);
        $pdf->write_cell(60, 4, $supplier['city']);
        $pdf->write_cell(20, 4, $supplier['country_code']);
        $pdf->line_new(10);

        $customer = $this->get_customer();
        $pdf->setFont("DejaVu", "B", 12);
        $pdf->write_cell(60, 4, _("Client"));
        $pdf->line_new(10);
        $pdf->setFont("DejaVu", "", 7);
        $pdf->write_cell(60, 4, $customer['name']);
        $pdf->write_cell(60, 4, $customer['company_id']);
        $pdf->write_cell(60, 4, $customer['ID']);
        $pdf->line_new();
        $pdf->write_cell(60, 4, $customer['street']);
        $pdf->write_cell(40, 4, $customer['postcode']);
        $pdf->write_cell(60, 4, $customer['city']);
        $pdf->write_cell(20, 4, $customer['country_code']);
        $pdf->line_new(10);

        $pdf->setFont("DejaVu", "B", 12);
        $pdf->write_cell(60, 4, _("Articles"));
        $pdf->line_new(10);
        $pdf->setFont("DejaVu", "", 7);
        $result = $this->get_invoiceLine();
        $pdf->write_cell(50, 4, _("Code"), border: 'B');
        $pdf->write_cell(50, 4, _("Description"), border: 'B');
        $pdf->write_cell(30, 4, _("TVA"), border: 'B', align: 'R');
        $pdf->write_cell(25, 4, _("Quantité"), border: 'B', align: 'R');
        $pdf->write_cell(25, 4, _("Montant HT"), border: 'B', align: 'R');
        $pdf->line_new();

        $nb_inline = count($result);
        for ($i = 0; $i < $nb_inline; $i++)
        {
            $pdf->write_cell(50, 4, $result[$i]['name']);
            $pdf->write_cell(50, 4, $result[$i]['description']);
            $pdf->write_cell(25, 4, $result[$i]['tva_percent'], align: 'R');
            $pdf->write_cell(5, 4, $result[$i]['tva_id']);
            $pdf->write_cell(25, 4, $result[$i]['quantity'], align: 'R');
            $pdf->write_cell(25, 4, $result[$i]['amount'], align: 'R');
            $pdf->line_new();
        }
        $pdf->line_new(10);

        $pdf->setFont("DejaVu", "B", 12);
        $pdf->write_cell(60, 4, _("Totaux"));
        $pdf->line_new(10);
        $pdf->setFont("DejaVu", "", 7);
        $result = $this->get_amount_summary();
//        @TODO DNY : Qu'est-ce que LineExtension Amount ??
        $pdf->write_cell(50, 4, _("Base taxe"));
        $pdf->write_cell(50, 4, $result['LineExtensionAmount']);
        $pdf->line_new();
        $pdf->write_cell(50, 4, _("Total Hors Taxe"));
        $pdf->write_cell(50, 4, $result['TaxExclusiveAmount']);
        $pdf->line_new();
        $pdf->write_cell(50, 4, _("Total avec Taxe"));
        $pdf->write_cell(50, 4, $result['TaxInclusiveAmount']);
        $pdf->line_new();
        $pdf->write_cell(50, 4, _("Total à payer"));
        $pdf->write_cell(50, 4, $result['PayableAmount']);
        $pdf->line_new();
        $pdf->write_cell(50, 4, _("Total réduction"));
        $pdf->write_cell(50, 4, $result['AllowanceTotalAmount']);
        $pdf->line_new();
        $pdf->write_cell(50, 4, _("Total charge"));
        $pdf->write_cell(50, 4, $result['ChargeTotalAmount']);
        $pdf->line_new();

        $pdf->line_new(10);
        $pdf->setFont("DejaVu", "B", 12);
        $pdf->write_cell(60, 4, _("TVA"));
        $pdf->line_new(10);
        $pdf->setFont("DejaVu", "", 7);
        $result = $this->get_taxes();
        $pdf->write_cell(25, 4, _("% Taxe"), align: 'R', border: 'B');
        $pdf->write_cell(5, 4, _("Code taxe"), border: 'B');
        $pdf->write_cell(50, 4, _("Base"), align: 'R', border: 'B');
        $pdf->write_cell(50, 4, _("Taxe"), align: 'R', border: 'B');
        $pdf->line_new();
        $nb_inline = count($result);
        for ($i = 0; $i < $nb_inline; $i++)
        {
            $pdf->write_cell(25, 4, $result[$i]['tax_percent'], align: 'R');
            $pdf->write_cell(5, 4, $result[$i]['tax_id']);
            $pdf->write_cell(50, 4, $result[$i]['taxable_amount'], align: 'R');
            $pdf->write_cell(50, 4, $result[$i]['tax'], align: 'R');

            $pdf->line_new();
        }
        $pdf->line_new(10);
        $pdf->setFont("DejaVu", "B", 12);
        $pdf->write_cell(60, 4, _("Paiement"));
        $pdf->line_new(10);
        $pdf->setFont("DejaVu", "", 7);
        $result = $this->get_payment_mean();
        $pdf->write_cell(50, 4, _("Code"));
        $pdf->write_cell(50, 4, $result['payment_code']);
        $pdf->line_new();
        $pdf->write_cell(50, 4, _("IBAN"));
        $pdf->write_cell(50, 4, $result['iban']);
        $pdf->line_new();
        $pdf->write_cell(50, 4, _("BIC"));
        $pdf->write_cell(50, 4, $result['bic']);
        $pdf->line_new();
        $pdf->write_cell(50, 4, _("Communication"));
        $pdf->write_cell(50, 4, $result['label']);
        $pdf->line_new();
        $nb_note = count($result['note']);
        for ($i = 0; $i < $nb_note; $i++)
        {
            $pdf->write_cell(40, 4, _("note") . " " . $i);
            $pdf->write_multi(140, 4, str_replace(["\n", "\r", "\t"], " ", $result['note'][$i]));
            $pdf->line_new();
        }
        /**
         * display name of embedded from files
         * @var $result(array of Document_Reference)
         */
        $result=$this->get_embedded_document();
        if ( count($result) > 0)
        {
            $pdf->setFont("DejaVu", "B", 12);
            $pdf->write_cell(50,4,_('Documents'));
            $pdf->line_new();
            $pdf->setFont("DejaVu", "", 7);
            $nb_result=count($result);
            for ($i=0;$i< $nb_result;$i++)
            {
                $binary=$result[$i]->getBinary_object();
                $pdf->write_cell(50,4,$result[$i]->getId());
                $pdf->write_cell(50,4,$binary->filename);
                $pdf->write_cell(50,4,$result[$i]->getDescription());
                $pdf->line_new();
                
            }
            
        }
        return $pdf;
    }
}
