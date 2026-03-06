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
 * @brief extract information from UBL21
 * 
 */

/**
 * @class
 * @brief Get information from an XML
 * Exception code : 
 *    - 55 : XML Invalid
 *    - 62 : filename don't exist
 *    - 140: not a credit note
 * Namespace standard (from XSD)
 * 
 * Array
  (
 * 
  'cbc' => string 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2'
  'ns2' => string 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2'
  'cac' => string 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2'
  'ns4' => string 'urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2'
  )
 * 
 */
class XMLCreditNote_Reader extends XML_Reader
{

    public function __construct(\DOMDocument $domDocument)
    {
        parent::__construct($domDocument);
    }

    /**
     * @brief Build an XMLInvoice_Reader object from an XML string
     * @param $string (string) XML
     * @return \Noalyss\XMLDocument\XMLInvoice_Reader
     * @throws \Exception if xml not valid
     */
    static function build_from_string($string)
    {
        $dm = new \DOMDocument();
        if ($dm->loadXML($string) != false)
        {
            return new XMLCreditNote_Reader($dm);
        } else
        {
            throw new \Exception("XC55: not a valid XML", 55);
        }
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
            throw new \Exception("XC62: file not found $filename", 62);
        }
        $dm = new \DOMDocument();
        if ($dm->load($filename) != false)
        {
            return new XMLCreditNote_Reader($dm);
        } else
        {
            throw new \Exception("XC55: not a valid XML", 55);
        }
    }

    /**
     * @brief retrieve InvoiceLines
      @code
      <ns3:CreditNoteLine>
      <ID>2</ID>
      <CreditedQuantity unitCode="DAY">-3</CreditedQuantity>
      <LineExtensionAmount currencyID="EUR">-1500</LineExtensionAmount>
      <ns3:OrderLineReference>
      <LineID>123</LineID>
      </ns3:OrderLineReference>
      <ns3:Item>
      <Description>Description 2</Description>
      <Name>item name 2</Name>
      <ns3:StandardItemIdentification>
      <ID schemeID="0088">21382183120983</ID>
      </ns3:StandardItemIdentification>
      <ns3:OriginCountry>
      <IdentificationCode>NO</IdentificationCode>
      </ns3:OriginCountry>
      <ns3:CommodityClassification>
      <ItemClassificationCode listID="SRV">09348023</ItemClassificationCode>
      </ns3:CommodityClassification>
      <ns3:ClassifiedTaxCategory>
      <ID>S</ID>
      <Percent>25.0</Percent>
      <ns3:TaxScheme>
      <ID>VAT</ID>
      </ns3:TaxScheme>
      </ns3:ClassifiedTaxCategory>
      </ns3:Item>
      <ns3:Price>
      <PriceAmount currencyID="EUR">500</PriceAmount>
      </ns3:Price>
      </ns3:CreditNoteLine>
      @endcode
     */
    function get_invoiceLine(): array
    {
        $result = [];
        $node = $this->get_node("//cac:CreditNoteLine");
        if ($node == null )
        {
            throw new \Exception ("XC140 invalide document",140);
        }
        for ($e = 0; $e < $node->length; $e++)
        {
            $row = [];
            $row ['quantity'] = $node->item($e)->getElementsByTagName("CreditedQuantity")->item(0)->textContent;
            $row ['code_quantity'] = $node->item($e)->getElementsByTagName("CreditedQuantity")->item(0)->getAttribute('unitCode');
            $row ['amount'] = $node->item($e)->getElementsByTagName("LineExtensionAmount")->item(0)->textContent; 
            $row ['description'] = $node->item($e)->getElementsByTagName("Description")->item(0)?->textContent; 
            $row ['name'] = $node->item($e)->getElementsByTagName("Name")->item(0)->textContent; 
            $row ['unit_price'] = $node->item($e)->getElementsByTagName("PriceAmount")->item(0)->textContent; 
            $row ['tva_id'] = $node->item($e)->getElementsByTagName("ID")->item(0)->textContent; 
            $row ['tva_percent'] = $node->item($e)->getElementsByTagName("Percent")->item(0)?->textContent; 
            $result[] = $row;
        }
        return $result;
    }
    /**
     * @brief return the code of the document
     * @return string credit_node + code
     */

    function get_document_type_code()
    {
       return "credit note ".$this->get_node_value('cbc:CreditNoteTypeCode');
    }
    /**
     * @brief before executing xpath->query the namespace must be registered
     * the NS are different for each type of doc
     */

    public function get_namespace()
    { 
        
        $a_namespace=array(
                "cac"=> 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2'
                ,"cbc"=> 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2'
                ,"ns4"=>'urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2'
                ,"ns2"=>'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2'
        );
        return $a_namespace;
    }
     /**
     * @brief get the Taxes info from XML
     * @return array
     */
    function get_taxes(): array
    {
        $result = [];
        $node = $this->get_node("//cac:TaxTotal/cac:TaxSubtotal");

        for ($e = 0; $e < $node->length; $e++)
        {
            $row = [];
            $xml = simplexml_import_dom($node->item($e));
            // /Invoice/cac:TaxTotal[1]/cac:TaxSubtotal[1]/cbc:TaxableAmount[1]
            $this->registerNS($xml);

            $row ['taxable_amount'] = $xml->xpath("//cbc:TaxableAmount")[$e] . "";
            $row ['tax'] = $xml->xpath("//cbc:TaxAmount")[$e] . "";
            $row ['tax_id'] = $xml->xpath("//cac:TaxCategory/cbc:ID")[$e] . "";
            $row ['tax_percent'] = $xml->xpath("//cac:TaxCategory/cbc:Percent")[$e] . "";
            if (isset($xml->xpath("//cac:CreditNoteLine/cac:Item/cbc:Name")[$e]))
                $row ['name'] =$xml->xpath("//cac:CreditNoteLine/cac:Item/cbc:Name")[$e]."";
            else
                $row['name']="";

            if ( isset ($xml->xpath("//cbc:TaxExemptionReasonCode")[$e]))
            {
                $row['vatex']=$xml->xpath("//cbc:TaxExemptionReasonCode")[$e];
            }else {
                $row['vatex']="";
            }
            $result[] = $row;
        }
        return $result;
    }

}
