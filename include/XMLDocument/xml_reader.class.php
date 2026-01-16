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
    static function build_from_file($filename)
    {
         if (!file_exists($filename))
        {
            throw new \Exception("XR101: file not found $filename", 101);
        }
        $dm = new \DOMDocument();
        if ($dm->load($filename) == false)
        {
             throw new \Exception("XR106: cannot load file", 106);
        }
        if ( $dm->getElementsByTagName("CreditNote")->length == 1)
        {
            return new XMLCreditNote_Reader($dm);
        }
        if ( $dm->getElementsByTagName("Invoice")->length == 1)
        {
            return new XMLInvoice_Reader($dm);
        }
          throw new \Exception("XR116: unknown XML", 116);
    }

    /**
     * @brief Build an XMLInvoice_Reader object from an XML string
     * @param $string (string) XML
     * @return \Noalyss\XMLDocument\XMLInvoice_Reader
     * @throws \Exception if xml not valid
     */
     static  function build_from_string($string) 
     {
        $dm = new \DOMDocument();
        if ($dm->loadXML($string) == false)
        {
            throw new \Exception("XR130: cannot load XML", 130);
        }
        if ( $dm->getElementsByTagName("CreditNote")->length == 1)
        {
            return new XMLCreditNote_Reader($dm);
        }
        if ( $dm->getElementsByTagName("Invoice")->length == 1)
        {
            return new XMLInvoice_Reader($dm);
        }
          throw new \Exception("XR120: unknown XML", 120);
     }



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
        if ($result['name'] == '') {
            $result['name'] = $this->get_node_value("//cac:AccountingCustomerParty[1]/cac:Party[1]/cac:PartyLegalEntity[1]/cbc:RegistrationName[1]");
        }
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
        if ($result['name'] == '') {
            $result['name'] = $this->get_node_value("//cac:AccountingSupplierParty[1]/cac:Party[1]/cac:PartyLegalEntity[1]/cbc:RegistrationName[1]");
        }
        $result['street'] = $this->get_node_value("//cac:AccountingSupplierParty[1]/cac:Party[1]/cac:PostalAddress[1]/cbc:StreetName[1]");
        $result['city'] = $this->get_node_value("//cac:AccountingSupplierParty[1]/cac:Party[1]/cac:PostalAddress[1]/cbc:CityName[1]");
        $result['postcode'] = $this->get_node_value("//cac:AccountingSupplierParty[1]/cac:Party[1]/cac:PostalAddress[1]/cbc:PostalZone[1]");
        $result['country_code'] = $this->get_node_value("//cac:AccountingSupplierParty[1]/cac:Party[1]/cac:PostalAddress[1]/cac:Country[1]/cbc:IdentificationCode[1]");
        $result['company_id'] = $this->get_node_value("//cac:AccountingSupplierParty[1]/cac:Party[1]/cac:PartyTaxScheme[1]/cbc:CompanyID[1]");
        if ($result['company_id']  == "" ) {
            $result['company_id'] = $this->get_node_value("//cac:AccountingSupplierParty[1]/cac:Party[1]/cac:PartyLegalEntity[1]/cbc:CompanyID[1]");
        }
        $result['scheme'] = $this->get_node("//cac:AccountingSupplierParty[1]/cac:Party[1]/cbc:EndpointID[1]")[0]->getAttribute("schemeID");
        /**
         * get contact
            <cac:Contact>
                <cbc:Name>Facturation NOALYSS</cbc:Name>
                <cbc:ElectronicMail>invoice@noalyss</cbc:ElectronicMail>
            </cac:Contact>
         */
        $result['contact_name'] = $this->get_node_value("//cac:AccountingSupplierParty[1]/cac:Party[1]/cac:Contact[1]/cbc:Name[1]");
        $result['contact_mail'] = $this->get_node_value("//cac:AccountingSupplierParty[1]/cac:Party[1]/cac:Contact[1]/cbc:ElectronicMail");

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
                $result['note'][] = $note->item($i)->nodeValue;
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
        $node=$this->get_node("//cac:AllowanceCharge");
        if ( $node == null )
        {
            return $result;
        }
        for ($e = 0; $e  < $node->length;$e++)
        {
            $row=[];
            $row['ChargeIndicator'] = $this->get_node_value('//cac:AllowanceCharge/cbc:ChargeIndicator',$e);
            $row['AllowanceChargeReasonCode'] = $this->get_node_value('//cac:AllowanceCharge/cbc:AllowanceChargeReasonCode',$e);
            $row['AllowanceChargeReason'] = $this->get_node_value('//cac:AllowanceCharge/cbc:AllowanceChargeReason',$e);
            $row['amount'] = $this->get_node_value('//cac:AllowanceCharge/cbc:Amount',$e);
            $row['tva_id'] = $this->get_node_value('//cac:AllowanceCharge/cac:TaxCategory/cbc:ID',$e);
            $row['tva_percent'] = $this->get_node_value('//cac:AllowanceCharge/cac:TaxCategory/cbc:Percent',$e);
            $result[]=$row;
        }
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
        $result['OrderReference'] = $this->get_node_value('//cac:OrderReference[1]/cbc:ID[1]');
        $result['ActualDeliveryDate'] = $this->get_node_value('//cac:Delivery[1]/cbc:ActualDeliveryDate[1]');
        $result['info']=array();
        
        $result['info']['note']=$this->get_node_value("//cbc:Note");
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
    public function to_pdf(): PDF
    {
        //$pdf = new PDF($cn);
        
        $pdf = new PDF();
        $result = $this->get_info();
        $info=$result;
    //    $pdf->setDossierInfo(_(" id ") . " " . $result['id']);
        $pdf->AliasNbPages();
        $pdf->setAuthor("Noalyss");
        $pdf->AddPage();
        // 180 mm large
        $pdf->setFont("DejaVu", "B", 16);
        $pdf->line_new(2);
        $pdf->SetTextColor(0,0,127);
        $pdf->write_cell(10, 10, "");
        $pdf->write_cell(170, 10, _("Résumé document"),1,0,'C');
        $pdf->line_new(20);
        $pdf->Image(NOALYSS_HOME.'/image/logo10000.png', 10, 10, 20, 0, 'PNG');

        $pdf->setFont("DejaVu", "", 7);
        $pdf->SetTextColor(0,0,0);
        $pdf->write(4, sprintf(_("Document ID %s"), $result['id']));
        $pdf->ln();
        $pdf->setFont("DejaVu", "B", 8);
        $pdf->write_cell(20,4,_('Date'));
        $pdf->print_row();
        $pdf->setFont("DejaVuCond", "", 7);
        $pdf->write_multi(38,4, sprintf(_(" Facture %s"), $result['IssueDate']));
        $pdf->write_multi(38,4, sprintf(_("Echéance %s"), $result['DueDate']));
        $pdf->write_multi(38,4, sprintf(_("Livraison %s"), $result['ActualDeliveryDate']));
        $pdf->line_new();
        $pdf->write(4, sprintf(_("Type et code document %s"), $result['InvoiceTypeCode']));
        $pdf->ln();
        $pdf->write(4, sprintf(_("Devise document %s"), $result['DocumentCurrencyCode']));
        $pdf->ln();
        $pdf->write(4, sprintf(_("Référence client %s"), $result['BuyerReference']));
        $pdf->ln();
        $pdf->write(4, sprintf(_("Référence commande %s"), $result['OrderReference']));
        $pdf->ln(10);

        $pdf->setFont("DejaVu", "B", 12);
        $pdf->SetTextColor(0,0,127);
        $pdf->write_cell(60, 4, _("Fournisseur"));
        $pdf->line_new(6);
        $supplier = $this->get_supplier();
        $pdf->setFont("DejaVu", "B", 8);
        $pdf->write_multi(10, 4, "");
        $pdf->write_multi(150, 4, $supplier['name']);
        $pdf->line_new();
        $pdf->SetTextColor(0,0,0);
        $pdf->setFont("DejaVuCond", "", 7);
        $pdf->write_multi(70, 4, $supplier['street']);
        $pdf->write_multi(30, 4, $supplier['postcode']);
        $pdf->write_multi(50, 4, $supplier['city']);
        $pdf->write_cell(10, 4, $supplier['country_code']);
        $pdf->line_new();
        $pdf->write_cell(50, 4, $supplier['company_id']);
        $pdf->write_cell(50, 4,'PEPPOL ID:'.$supplier['scheme'].":". $supplier['ID']);
        $pdf->line_new();
        if ( $supplier['contact_name'] != '' ||  $supplier['contact_mail'] != '') {
            $pdf->write_multi(100, 4, "contact: ".$supplier['contact_name']." ". $supplier['contact_mail']);
            $pdf->line_new();
        }
        $pdf->line_new(4);

        $customer = $this->get_customer();
        $pdf->setFont("DejaVu", "B", 12);
        $pdf->SetTextColor(0,0,127);
        $pdf->write_cell(60, 4, _("Client"));
        $pdf->line_new(6);
        $pdf->setFont("DejaVu", "B", 8);
        $pdf->write_multi(10, 4, "");
        $pdf->write_multi(150, 4, $customer['name']);
        $pdf->line_new();
        $pdf->SetTextColor(0,0,0);
        $pdf->setFont("DejaVu", "", 7);
        $pdf->write_multi(70, 4, $customer['street']);
        $pdf->write_multi(30, 4, $customer['postcode']);
        $pdf->write_multi(50, 4, $customer['city']);
        $pdf->write_multi(10, 4, $customer['country_code']);
        $pdf->line_new();
        $pdf->write_cell(50, 4, $customer['company_id']);
        $pdf->write_cell(50, 4, 'PEPPOL ID:'.$customer['scheme'].":".$customer['ID']);
        $pdf->line_new(6);
        /**
         * note invoice
         */
         if ( $info['info']['note'] != "")
         {
            $pdf->SetTextColor(0,0,127);
            $pdf->setFont("DejaVu", "B", 12);
            $pdf->write_cell(60, 4, _("Notes"));
            $pdf->line_new(6);
            $pdf->SetTextColor(0,0,0);
            $pdf->setFont("DejaVu", "", 7);
            $pdf->write_multi(130, 8,$info['info']['note']);
            $pdf->line_new(10); 
         }
        /**
         * ITEM
         */
        $pdf->setFont("DejaVu", "B", 12);
        $pdf->SetTextColor(0,0,127);
        $pdf->write_cell(60, 4, _("Articles"));
        $pdf->line_new(6);
        $pdf->SetTextColor(0,0,0);
        $pdf->setFont("DejaVu", "", 7);
        $result = $this->get_invoiceLine();
        $pdf->write_cell(50, 4, _("Code"), border: 'B');
        $pdf->write_cell(60, 4, _("Description"), border: 'B');
        $pdf->write_cell(20, 4, _("TVA"), border: 'B', align: 'R');
        $pdf->write_cell(20, 4, _("Prix unitaire"), border: 'B', align: 'R');
        $pdf->write_cell(20, 4, _("Quantité"), border: 'B', align: 'R');
        $pdf->write_cell(20, 4, _("Montant HT"), border: 'B', align: 'R');
        $pdf->line_new();
        
        $nb_inline = count($result);
        for ($i = 0; $i < $nb_inline; $i++)
        {
            if ( $result[$i]['description'] == '') {
                $pdf->write_multi(110, 4, $result[$i]['name']);
            }else {
                $pdf->write_multi(50, 4, $result[$i]['name']);
                $pdf->write_multi(60, 4, $result[$i]['description']);
            }
            $pdf->write_cell(20, 4, $result[$i]['tva_percent'], align: 'R');
            //$pdf->write_cell(5, 4, $result[$i]['tva_id']);
            $pdf->write_cell(20, 4, $result[$i]['unit_price'], align: 'R');
            $pdf->write_cell(20, 4, $result[$i]['quantity'], align: 'R');
            $pdf->write_cell(20, 4, nbm($result[$i]['amount']), align: 'R');
            $pdf->line_new();
        }
        $pdf->line_new(10);
        
        $pdf->SetTextColor(0,0,127);
        $pdf->setFont("DejaVu", "B", 12);
        $pdf->write_cell(60, 4, _("Totaux"));
        $pdf->line_new(6);
        $pdf->SetTextColor(0,0,0);
        $pdf->setFont("DejaVu", "", 7);
        $result = $this->get_amount_summary();
//        @TODO DNY : Qu'est-ce que LineExtension Amount ??
        $pdf->write_cell(50, 4, _("Base taxe"));
        $pdf->write_cell(50, 4, nbm($result['LineExtensionAmount']),align:'R');
        $pdf->line_new();
        $pdf->write_cell(50, 4, _("Total Hors Taxe"));
        $pdf->write_cell(50, 4, nbm($result['TaxExclusiveAmount']),align:'R');
        $pdf->line_new();
        $pdf->write_cell(50, 4, _("Total avec Taxe"));
        $pdf->write_cell(50, 4, nbm($result['TaxInclusiveAmount']),align:'R');
        $pdf->line_new();
        $pdf->write_cell(50, 4, _("Total à payer"));
        $pdf->write_cell(50, 4, nbm($result['PayableAmount']),align:'R');
        $pdf->line_new();
        $pdf->write_cell(50, 4, _("Total réduction"));
        $pdf->write_cell(50, 4, nbm($result['AllowanceTotalAmount']),align:'R');
        $pdf->line_new();
        $pdf->write_cell(50, 4, _("Total charge"));
        $pdf->write_cell(50, 4, nbm($result['ChargeTotalAmount']),align:'R');
        $pdf->line_new(10);
        $result=$this->get_allowance();
        $nb_inline = count($result);
        if ( !empty ($result ))
        {
            $pdf->SetTextColor(0,0,127);
            $pdf->setFont("DejaVu", "B", 12);
            $pdf->write_cell(60, 4, _("Charge et déduction"));
            $pdf->line_new(6);
            $pdf->SetTextColor(0,0,0);
            $pdf->setFont("DejaVu", "", 7);
            $pdf->write_cell(20, 4, _("code"), align: 'L', border: '1');
            $pdf->write_cell(20, 4, _("Type"), border: '1');
            $pdf->write_cell(55, 4, _("Raison"), align: 'L', border: '1');
            $pdf->write_cell(30, 4, _("Montant"), align: 'R', border: '1');
            $pdf->write_cell(30, 4, _("TVA"), align: 'L', border: '1');
             $pdf->line_new();
            for ($i = 0; $i < $nb_inline; $i++)
            {
                $pdf->write_cell(20, 4, $result[$i]['AllowanceChargeReasonCode'], align: 'L');
                if ( $result[$i]['ChargeIndicator'] == 'false')
                {
                    $pdf->write_cell(20, 4, _("Déduction"), align: 'L');

                }else{
                    $pdf->write_cell(20, 4, _("Charge suppl."), align: 'L');

                }   
                $pdf->write_cell(55, 4, $result[$i]['AllowanceChargeReason'], align: 'L');
                $pdf->write_cell(30, 4,nbm( $result[$i]['amount']), align: 'R');
                $pdf->write_cell(5, 4, $result[$i]['tva_id']);
                $pdf->write_cell(25, 4, $result[$i]['tva_percent'], align: 'R');

                $pdf->line_new();
            }
        }
        $pdf->SetTextColor(0,0,127);
        $pdf->setFont("DejaVu", "B", 12);
        $pdf->write_cell(60, 4, _("TVA"));
        $pdf->line_new(6);
        $pdf->SetTextColor(0,0,0);
        $pdf->setFont("DejaVu", "", 7);
        $result = $this->get_taxes();
        $pdf->write_cell(25, 4, _("% Taxe"), align: 'R', border: 'B');
        $pdf->write_cell(30, 4, _("Code taxe"), border: 'B');
        $pdf->write_cell(50, 4, _("Base"), align: 'R', border: 'B');
        $pdf->write_cell(50, 4, _("Taxe"), align: 'R', border: 'B');
        $pdf->line_new();
        $nb_inline = count($result);
        for ($i = 0; $i < $nb_inline; $i++)
        {
            $pdf->write_cell(25, 4, $result[$i]['tax_percent'], align: 'R');
            $pdf->write_cell(5, 4, $result[$i]['tax_id']);
            $pdf->write_multi(25, 4, $result[$i]['vatex']);
            $pdf->write_cell(50, 4, nbm($result[$i]['taxable_amount']), align: 'R');
            $pdf->write_cell(50, 4, nbm($result[$i]['tax']), align: 'R');

            $pdf->line_new();
        }
        $pdf->line_new(10);
      
        
        $pdf->setFont("DejaVu", "B", 12);
        $pdf->SetTextColor(0,0,127);
        $pdf->write_cell(60, 4, _("Paiement"));
        $pdf->line_new(6);
        $pdf->SetTextColor(0,0,0);
        
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
         $pdf->line_new(6);
        /**
         * display name of embedded from files
         * @var $result(array of Document_Reference)
         */
        $result=$this->get_embedded_document();
        if ( count($result) > 0)
        {
            $pdf->SetTextColor(0,0,127);
            $pdf->setFont("DejaVu", "B", 12);
            $pdf->write_cell(60,4,_('Documents inclus'));
            $pdf->line_new();
            $pdf->SetTextColor(0,0,0);
            $pdf->setFont("DejaVu", "", 7);
            $nb_result=count($result);
            for ($i=0;$i< $nb_result;$i++)
            {
                $binary=$result[$i]->getBinary_object();
                $pdf->write_multi(50,4,$result[$i]->getId());
                $pdf->write_multi(50,4,$binary->filename);
                $pdf->write_multi(50,4,$result[$i]->getDescription());
                $pdf->line_new();
                
            }
            
        }
        return $pdf;
    }
}
