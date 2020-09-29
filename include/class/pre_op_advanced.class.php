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

// Copyright Author Dany De Bontridder danydb@aevalys.eu

/*!\file
 * \brief definition of the class Pre_Op_Advanced
 */
require_once  NOALYSS_INCLUDE.'/class/pre_operation.class.php';

/*---------------------------------------------------------------------- */
/*!\brief concerns the predefined operation for the operation from 'Ecriture direct'
 */
class Pre_Op_Advanced extends Pre_operation_detail
{
    function __construct($cn)
    {
        parent::__construct($cn);
    }
    /**
     * @brief get the post and stove them into data member , before saving them in the db
     * @see save
     */
    function get_post()
    {
        $http = new \HttpInput();
        $nb = $http->post("nb_item", "number");

        for ($i=0;$i<$nb;$i++)
        {
            $poste=$http->post("poste".$i,"string", null);
            $qcode=$http->post("qc_".$i,"string", null);
            
            if ( $poste == null && $qcode == null )                continue;
            
            if ($poste != null && trim ($poste) != "")
            {
                $this->{'poste'.$i}=$poste;
                 $this->{'isqc'.$i}='f';
            }
            
            if ( $qcode != null && trim ($qcode) != "") {
                $this->{'isqc'.$i}=(trim($http->post('qc_'.$i)) != "")?'t':'f';
                $this->{'poste'.$i}=trim ($qcode);
            }
            $http->set_empty(0);
            $this->{"amount".$i}=$http->post('amount'.$i);
            $http->set_empty("");
            $this->{"ld".$i}=$http->post("ld".$i);

            $this->{"ck".$i}=(isset($_POST['ck'.$i]))?'t':'f';

        }
    }
    /*!
     * \brief save the detail and op in the database
     *
     */
    function save($p_od_id,$p_nb_item)
    {
        try
        {
            // save the selling
            for ($i=0;$i<$p_nb_item;$i++)
            {
                if ( ! isset ($this->{"poste".$i}))
                    continue;

                $sql=sprintf('insert into op_predef_detail (opd_poste,opd_amount,'.
                             'opd_debit,od_id,opd_qc,opd_comment)'.
                             ' values($1,$2,$3,$4,$5,$6) '
                            );

                $this->db->exec_sql($sql,[$this->{"poste".$i},
                                        $this->{"amount".$i},
                                        $this->{"ck".$i},
                                        $p_od_id,
                                        $this->{'isqc'.$i},
                                        $this->{"ld".$i}]);

            }

        }
        catch (Exception $e)
        {
            record_log($e->getMessage().$e->getTraceAsString());
            throw $e;
        }

    }
    /*!\brief compute an array accordingly with the FormVenView function
     */
    function compute_array($p_od_id)
    {
        $count=0;
        $array=array();
        $p_array=$this->load($p_od_id);
		if (empty($p_array)) return array();
        foreach ($p_array as $row)
        {
            $tmp_array=array("qc_".$count=>'',
                             "poste".$count=>'',
                             "amount".$count=>$row['opd_amount'],
                             'ck'.$count=>$row['opd_debit'],
                              "ld".$count=>$row['opd_comment']
                            );

            if ( $row['opd_qc'] == 't' )
                $tmp_array['qc_'.$count]=$row['opd_poste'];
            else
                $tmp_array['poste'.$count]=$row['opd_poste'];


            if ( $row['opd_debit'] == 'f' )
                unset ($tmp_array['ck'.$count]);

            $array+=$tmp_array;
            $count++;

        }

        return $array;
    }
    /*!\brief load the data from the database and return an array
     * \return an array
     */
    function load($p_od_id)
    {
        $sql="select opd_id,opd_poste,opd_amount,opd_debit,opd_comment,".
             " opd_qc from op_predef_detail where od_id=$1 ".
             " order by opd_id";
        $res=$this->db->exec_sql($sql,[$p_od_id]);
        $array=Database::fetch_all($res);
        if ($array == false ) return array();
        return $array;
    }
    /**
     * Display the form for modifying or adding new predefined operation
     * @param array  $p_array is the result of compute_array or blank
     * @return string containing HTML code of the form
     * @throws Exception
     * @see compute_array
     * @see load
     *
     */
   function display($p_array)
    {
        global $g_parameter, $g_user;
        require_once NOALYSS_INCLUDE.'/class/acc_ledger.class.php';
        $legder=new Acc_Ledger($this->db,$p_array['p_jrn']);

        $legder->nb=$legder->get_min_row();

        $add_js = "";
       
        $ret = "";
        if ($g_user->check_action(FICADD) == 1)
        {
                /* Add button */
                $f_add_button = new IButton('add_card');
                $f_add_button->label = _('Créer une nouvelle fiche');
                $f_add_button->set_attribute('ipopup', 'ipop_newcard');
                $f_add_button->set_attribute('jrn', $legder->id);
                $f_add_button->javascript = " this.jrn=\$('p_jrn').value;select_card_type(this);";
                $f_add_button->input();
        }
       
        $nb_row = (isset($p_array['nb_item']) ) ? $p_array['nb_item' ]: $legder->nb;

        $ret.=HtmlInput::hidden('nb_item', $nb_row);
        $ret.=dossier::hidden();
        
        $ret.=dossier::hidden();

        $ret.=HtmlInput::hidden('jrn_type', "ODS");
        $info = Icon_Action::infobulle(0);
        $info_poste = Icon_Action::infobulle(9);
        if ($g_user->check_action(FICADD) == 1)
                $ret.=$f_add_button->input();
        $ret.='<table id="quick_item" style="width:100%">';
        $ret.='<tr>' .
                        '<th style="text-align:left">Quickcode' . $info . '</th>' .
                        '<th style="text-align:left">' . _('Poste') . $info_poste . '</th>' .
                        '<th style="text-align:left">' . _('Libellé') . '</th>' .
                        '<th style="text-align:left">' . _('Montant') . '</th>' .
                        '<th style="text-align:left">' . _('Débit') . '</th>' .
                        '</tr>';


        for ($i = 0; $i < $nb_row; $i++)
        {
                // Quick Code
                $quick_code = new ICard('qc_' . $i);
                $quick_code->set_dblclick("fill_ipopcard(this);");
                $quick_code->set_attribute('ipopup', 'ipopcard');

                // name of the field to update with the name of the card
                $quick_code->set_attribute('label', "ld" . $i);
                $quick_code->set_attribute('jrn', $legder->id);

                // name of the field to update with the name of the card
                $quick_code->set_attribute('typecard', 'filter');

                // Add the callback function to filter the card on the jrn
                $quick_code->set_callback('filter_card');
                $quick_code->set_function('fill_data');
                $quick_code->javascript = sprintf(' onchange="fill_data_onchange(\'%s\');" ', $quick_code->name);

                $quick_code->jrn = $legder->id;
                $quick_code->value = (isset($p_array['qc_' . $i])) ? $p_array['qc_' . $i]: "";

                $label = '';
                if ($quick_code->value != '')
                {
                        $Fiche = new Fiche($legder->db);
                        $Fiche->get_by_qcode($quick_code->value);
                        $label = $Fiche->strAttribut(ATTR_DEF_NAME);
                }


                // Account
                $poste = new IPoste();
                $poste->name = 'poste' . $i;
                $poste->set_attribute('jrn', $legder->id);
                $poste->set_attribute('ipopup', 'ipop_account');
                $poste->set_attribute('label', 'ld' . $i);
                $poste->set_attribute('account', 'poste' . $i);
                $poste->set_attribute('dossier', Dossier::id());

                $poste->value = (isset($p_array['poste' . $i])) ?$p_array['poste' . $i]: ''
                ;
                $poste->dbl_click_history();


                if ($poste->value != '')
                {
                        $Poste = new Acc_Account($legder->db);
                        $Poste->find_by_value($poste->value);
                        $label = $Poste->get_lib();
                }

                // Description of the line
                $line_desc = new IText();
                $line_desc->name = 'ld' . $i;
                $line_desc->size = 30;
                $line_desc->value = (isset($p_array["ld" . $i])) ? $p_array["ld" . $i] :
                                $label;

                // Amount
                $amount = new INum();
                $amount->size = 10;
                $amount->name = 'amount' . $i;
                $amount->value = (isset($p_array['amount' . $i])) ?$p_array['amount' . $i] : ''
                ;
                $amount->javascript = ' onChange="format_number(this);checkTotalDirect()"';
                // D/C
                $deb = new ICheckBox();
                $deb->name = 'ck' . $i;
                $deb->selected = (isset($p_array['ck' . $i])) ? true : false;
                $deb->javascript = ' onChange="checkTotalDirect()"';

                $ret.='<tr>';
                $ret.='<td>' . $quick_code->input() . $quick_code->search() . '</td>';
                $ret.='<td>' . $poste->input() .
                                '<script> document.getElementById(\'poste' . $i . '\').onblur=function(){ if (trim(this.value) !=\'\') {document.getElementById(\'qc_' . $i . '\').value="";}}</script>' .
                                '</td>';
                $ret.='<td>' . $line_desc->input() . '</td>';
                $ret.='<td>' . $amount->input() . '</td>';
                $ret.='<td>' . $deb->input() . '</td>';
                $ret.='</tr>';
                // If readonly == 1 then show CA
        }
        $ret.='</table>';
        $ret.=Html_Input_Noalyss::ledger_add_item("M");
        return $ret;
    }
}
