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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 28/08/25


/**
 * @file
 * @brief Document used in accountancy : invoice , credit note, ...It is 
 * a specialization of Document used in Follow-UP
 */

/**
 * @class
 * @brief Document used in accountancy : invoice , credit note, ... It is 
 * a specialization of Document used in Follow-UP.
 * property : 
 *    - d_id JRN.JR_ID
 *    - d_name name Receipt number
      - d_description Comment of the operation  
      - d_mimetype mimetype of the document  JRN.JR_PJ_TYPE
      - d_filename filename JRN.JR_PJ_NAME
      - document_xml oid of the XML invoice (including PDF) JRN.JR_DOCUMENT_XML
      - d_lob = JRN.JR_PJ
 * 
 */
class Acc_Document extends Document {
    private $document_xml; ///< $document_xml (oid) XML document e-invoice
    
    public function get_document_xml() {
        return $this->document_xml;
    }

    public function set_document_xml($document_xml) {
        $this->document_xml = $document_xml;
        return $this;
    }

        /**
     * @brief constructor
     * @param $cn \Database
     * @param $jr_id (int) JRN.JRID will be in d_id
     */
    function __construct($cn, $jr_id=0)
    {
        $this->db=$cn;
        $this->set_id($jr_id);
        // counter for MARCH_NEXT
        $this->counter=0;

    }
    /**
     * @brief set_id fill up d_filename, d_mimetype,d_lob,d_description,jr_pj_number
     */
    function set_id($jr_id) {
        $this->d_id=$jr_id;
        if ( $jr_id == 0 ){
            return $this;
        }
        $row = $this->db->get_row("select jr_comment
            ,jr_pj
            ,jr_pj_name
            ,jr_pj_type 
            ,jr_pj_number
            ,jr_document_xml
            from jrn 
           where
            jr_id=$1", [$this->d_id]);
        if ( empty ($row)) {
            return $this;   
        }
        $this->d_name=$row['jr_pj_number'];
        $this->d_description=$row['jr_comment'];
        $this->d_mimetype=$row['jr_pj_type'];
        $this->d_filename=$row['jr_pj_name'];
        $this->d_lob=$row['jr_pj'];
        $this->document_xml=$row['jr_document_xml'];
        return $this;
    }
    /**
     * @brief save the file into DB, will create a large object if there 
     * is no document to replace. It will change the d_filename, d_mimetype 
     *
     * @param $d_filename (string) full path to the file to load into DB
     * 
     * @returns false if d_id = 0 or the file doesn't exist, true for success
     */
    function update($filename) {
        if ($this->d_id == 0) return false;
        if ( ! file_exists($filename)) return false;
        $this->db->start();
        $this->d_mimetype= mime_content_type($filename);
        $this->d_filename= basename($filename);
        if ( $this->d_lob == "") {
            $this->db->lo_unlink($this->d_lob);
        } 
        
        $this->d_lob=$this->db->lo_import($filename);
        $this->db->exec_sql(
                "update jrn set jr_pj=$1,jr_pj_name=$2,jr_pj_type=$3 
                    where jr_id=$4",
                [$this->d_lob,$this->d_filename,$this->d_mimetype,$this->d_id]
                );
        $this->db->commit();
        return true;
    }
    /**
     * @brief save the Large Object $oid in the column JRN.JR_DOCUMENT_XML
     * @param $oid( OID) PostgreSQL Object ID
     */
    function update_document_xml($oid){
        $this->db->exec_sql("update jrn set jr_document_xml=$1 where 
            jr_id=$2",[
                $oid,
                $this->d_id
            ]);

    }
    /**
     * @brief create the invoice and saved it as attachment to the
     * operation,
     * @param  $p_array is normally the $_POST
      @verbatim
      Array
      (
      [ledger_type] => VEN
      [gDossier] => x
      [nb_item] => 1 (number of item used to numerate e_marchX, e_quantX ,...)
      [p_jrn] => 2
      [p_jrn_predef] => 2
      [jrn_type] => VEN or ACH
      [e_date] => 10.06.2025 (date)
      [e_ech] => limite date
      [e_client] => CLIENT
      [e_pj] => 25.827
      [e_pj_suggest] => 25.827
      [e_comm] => E-INVOICE
      [p_currency_code] => 0
      [p_currency_rate] => 1 (rate if 1 for EURO)
      [jrn_note_input] =>
      [e_march0] => 7DVINV
      [e_march0_label] => Label of the operation
      [e_march0_price] => 10.0000
      [e_quant0] => 1.0000
      [htva_march0] => 10
      [e_march0_tva_id] => 1
      [e_march0_tva_amount] => 2.1
      [tva_march0] => 2.1
      [tvac_march0] => 12.1
      ...
      [mp_date] => (dd.mm.yyyy date of payment)
      [acompte] => 0 (amount to deduce as 	advance payment)
      [e_comm_paiement] => (string : comment of the payment)
      [e_mp] => 0 (method of payment it is the XX in e_mp_qcode_XX)
      [e_mp_qcode_16] => Banque 1
      [e_mp_qcode_17] => Banque 2
      [view_invoice] => Enregistrer
      [gen_doc] => int DOCUMENT_MODELE.MD_ID , document template to use
      )
     * @endverbatim
     * @todo rewrite code : remove extract and +SQL value 
     * @returns void
     */
    function create_document($internal, $p_array) {
        $this->f_id = $p_array['e_client'];
        // var md_id (int) DOCUMENT_MODELE.MD_ID
        $this->md_id = $p_array['gen_doc'];
        // var ag_id == 0 fake follow-up 
        $this->ag_id = 0;
        // var e_pj (string) receipt nb
        $p_array['e_pj'] = $this->db->get_value("select jr_pj_number from jrn where jr_id=$1"
                , [$this->d_id]);
        $filename = "";
        //  generate the document and set d_lob,d_mimetype,
        $this->generate($p_array, $p_array['e_pj']);

        // Move the document to accountancy (table JRN),
        $this->moveDocumentACC($internal);

        // Update the comment with invoice number, if the comment is empty
        if (!isset($p_array['e_comm']) || noalyss_strlentrim($p_array['e_comm']) == 0) {
            $sql = "update jrn set jr_comment=' document " . $this->d_number . "' where jr_internal=$1";
            $this->db->exec_sql($sql, [$internal]);
        }
    }

    /**
     * @brief export the file to the file system and complet $this->d_mimetype, d_filename and 
     * @param $destination_file (string) full path to document
     * @return bool false for failure and string (the full path_name) for success
     */
    function export_file($destination_file) {
        
        if (empty($this->d_filename)) {
            return false; 
        }

        $this->db->start();
        if ($this->db->lo_export($this->d_lob, $destination_file) == false) {
            record_log("ACD122. cannot export");
            $this->db->commit();
            return false;
        }
        $this->db->commit();

        return $destination_file;
    }
    /**
     * \brief Save a "piece justificative" , the name must be a receipt. If it 
     * is a XML document, split it into 2 parts : PDF and XML 
     *  the PDF be stored in JR_PJ and the XML into JR_DOCUMENT_XML
     * 
     * 
     * \return $oid of the lob file if success null if a error occurs
     *
     */
    function save_receipt()
    {
        $this->db->start();
        /**
         * pj is the $_FILES key
         */
        $oid = $this->db->upload('pj');
        if ($oid == false) {
            return false;
        }
        // Remove old document if any
        $old_oid = $this->db->get_value("select jr_pj from jrn where jr_id=$1"
                ,[$this->d_id]);
        
        if ( $old_oid != "")
        {
            $this->lo_unlink( $old_oid);
        }
        // save new document
        $this->db->exec_sql("update jrn set jr_pj=$1 , jr_pj_name=$2,
                                jr_pj_type=$3  where jr_id=$4",
                                array(
                                        $oid
                                    ,   $_FILES['pj']['name']
                                    ,   $_FILES['pj']['type']
                                    ,   $this->d_id 
                                    )
                                );
       $this->db->commit();
        // if there is a e-invoice in XML
//        if (   $_FILES['pj']['type'] == 'text/xml' 
//            || $_FILES['pj']['type'] == 'application/xml' 
//            ) 
//        {
//           $xmlreader= Noalyss\XMLDocument\XMLInvoice_Reader::build_from_file($_FILES['pj']['tmp_name']);
//     //      $xmlreader->save_xml
//        }
        $this->d_name=$_FILES['pj']['name'];
        $this->d_description=$_FILES['pj']['name'];
        $this->d_lob=$oid;
        $this->d_mimetype=$_FILES['pj']['type'];
        return $oid;
    }
    /**
     * @brief return a string with a link download XML or an empty string
     * if there is no XML to download
     */
    function link_download_xml():string
    {
        $xml_oid=$this->db->get_value("select jr_document_xml from jrn where jr_id=$1",
                [$this->d_id]);
        if ($xml_oid == "") { return "";}
         $url= "export.php?".http_build_query(
                        [
                            "gDossier"=>\Dossier::id(),
                            "jr_id"=>$this->d_id,
                            "act"=>'RAW:xml-invoice'
                        ]);
        $r = sprintf('<a class="mtitle line" href="%s">',$url);
        $r .=  _("XML")
                .'<i class="icon-download">'
                .'</i>'
                .'</a>';
        return $r;
    }
    
}