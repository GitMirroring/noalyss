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
 * a specialization of Document used in Follow-UP
 */
class Acc_Document extends Document {

    /**
     * @brief create the invoice and saved it as attachment to the
     * operation,
     * @param  $internal is the internal code JRN.JR_INTERNAL
     * @param  $p_array is normally the $_POST
      @verbatim
      Array
      (
      [ledger_type] => VEN
      [ac] => COMPTA/VENMENU/VEN
      [sa] => p
      [action_gestion] =>
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
      )
     * @endverbatim
     * @todo rewrite code : remove extract and +SQL value 
     * @returns void
     */
    function create_document($internal, $p_array) {
        $this->f_id = $p_array['e_client'];
        $this->md_id = $p_array['gen_doc'];
        $this->ag_id = 0;
        $p_array['e_pj'] = $this->db->get_value("select jr_pj_number from jrn where jr_internal=$1", [$internal]);
        $filename = "";
        $this->generate($p_array, $p_array['e_pj']);

        // Move the document to accountancy (table JRN)
        $this->moveDocumentACC($internal);

        // Update the comment with invoice number, if the comment is empty
        if (!isset($p_array['e_comm']) || noalyss_strlentrim($p_array['e_comm']) == 0) {
            $sql = "update jrn set jr_comment=' document " . $this->d_number . "' where jr_internal=$1";
            $this->db->exec_sql($sql, [$internal]);
        }
    }

    /**
     * @brief export the file to the file system and complet $this->d_mimetype, d_filename and 
     * @param  $internal is the internal code JRN.JR_INTERNAL
     * @param $destination_file string  path
     * @return bool false for failure and true for success
     */
    function export_file($internal, $destination_file) {


        $row = $this->db->get_row("select jr_pj,jr_pj_name,jr_pj_type 
            from jrn 
           where
            jr_internal=$1", [$internal]);
        if ($row == null) {
            \record_log("ACD117. not row found for $internal");
            return false; 
        }
        $row = Database::fetch_array($ret, 0);

        $this->db->start();
        if ($this->db->lo_export($row['jr_pj'], $tmp) == false) {
            record_log("ACD122. cannot export");
            $this->db->commit();
            return false;
        }
        $this->db->commit();

        return true;
    }
}
