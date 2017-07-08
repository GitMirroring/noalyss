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
 * \brief object to show a table: link between accountancy and analytic
 */
require_once NOALYSS_INCLUDE.'/class/anc_acc_link.class.php';

class Anc_Table extends Anc_Acc_Link
{
  /**
   *@brief display form to get the parameter 
   *  - card_poste 1 by card, 2 by account
   *  - from_poste 
   *  - to_poste
   *  - from from date
   *  - to until date
   *  - pa_id Analytic plan to use
   */
  function display_form($p_hidden='')
  {
    $r=parent::display_form($p_hidden);
    $icard=new ISelect('card_poste');
    $icard->value=array(
			array('value'=>1,'label'=>'Par fiche'),
			array('value'=>2,'label'=>'Par poste comptable')
			);
    $icard->selected=$this->card_poste;
    $r.=$icard->input();
    $r.=HtmlInput::request_to_hidden(array('ac'));
    return $r;
  }


  /**
   * load the data
   * does not return anything but give a value to this->aheader and this->arow
   */
  function load_poste()
  {
    $sql_from_poste=($this->from_poste!='')?" and  po_name >= upper('".Database::escape_string($this->from_poste)."')":'';
    $sql_to_poste=($this->to_poste!='')?" and  po_name <= upper('".Database::escape_string($this->to_poste)."')":'';
    $this->db->exec_sql('create temporary table table_analytic as select * from comptaproc.table_analytic_account(\''.$this->from.'\',\''.$this->to.'\')');

    $header="select distinct po_id,po_name  from table_analytic
		where
		pa_id=$1 ".$sql_from_poste.$sql_to_poste." order by po_name";
    $this->aheader=$this->db->get_array($header,array($this->pa_id));
    
    $this->arow=$this->db->get_array("select distinct card_account,name
		from table_analytic
		where
		pa_id=$1 ".$sql_from_poste.$sql_to_poste." order by card_account",array($this->pa_id));

    $this->sql='select sum_amount from  table_analytic where card_account=$1 and po_id=$2 and pa_id='.$this->pa_id.' '.$sql_from_poste.$sql_to_poste;
  }

  /**
   * load the data
   * does not return anything but give a value to this->aheader and this->arow
   */
  function load_card()
  {
    $sql_from_poste=($this->from_poste!='')?" and  po_name >= upper('".Database::escape_string($this->from_poste)."')":'';
    $sql_to_poste=($this->to_poste!='')?" and  po_name <= upper('".Database::escape_string($this->to_poste)."')":'';
    $this->db->exec_sql('create temporary table table_analytic as select * from comptaproc.table_analytic_card(\''.$this->from.'\',\''.$this->to.'\')');

    $header="select distinct po_id,po_name from table_analytic
		where
		pa_id=$1 ".$sql_from_poste.$sql_to_poste." order by po_name";
    $this->aheader=$this->db->get_array($header,array($this->pa_id));
    
    $this->arow=$this->db->get_array("select distinct f_id,card_account,name from  table_analytic 
			where
		pa_id=$1 ".$sql_from_poste.$sql_to_poste." order by name",array($this->pa_id));
    $this->sql='select sum_amount from table_analytic where f_id=$1 and po_id=$2 and pa_id='.$this->pa_id.' '.$sql_from_poste.$sql_to_poste;
  }
  /**
   *@brief display the button export CSV
   *@param $p_hidden is a string containing hidden items
   *@return html string
   */  
  function show_button($p_hidden)
  {
    $r="";
    $r.= '<form method="GET" action="export.php"  style="display:inline">';
    $r.= HtmlInput::hidden("act","CSV:AncTable");
    $r.= HtmlInput::hidden("to",$this->to);
    $r.= HtmlInput::hidden("from",$this->from);
    $r.= HtmlInput::hidden("pa_id",$this->pa_id);
    $r.= HtmlInput::hidden("from_poste",$this->from_poste);
    $r.= HtmlInput::hidden("to_poste",$this->to_poste);
    $r.= HtmlInput::hidden("card_poste",$this->card_poste);
    $r.= $p_hidden;
    $r.= dossier::hidden();
    $r.=HtmlInput::submit('bt_csv',"Export en CSV");
    $r.= '</form>';
    return $r;
  }
  function display_html()
  {
    bcscale(2);
    if ( $this->check()  != 0)
      {
	alert(_("Date invalide"));
	return;
      }

    if ( $this->card_poste=='1')
      {
	$this->load_card();

	echo '<table class="result">';
	echo '<tr>';
	echo th('Fiche');
	foreach ($this->aheader as $h)
	  {
	    echo '<th style="text-align:right">'.h($h['po_name']).'</th>';
	  }
	echo th('Total',' style="text-align:right"');
	echo '</tr>';
	/*
	 * Show all the result
	 */
	$tot_global=0;
	for ($i=0;$i<count($this->arow);$i++)
	  {
	    $tr=($i%2==0)?'<tr class="even">':'<tr class="odd">';
            echo $tr;
	    echo td(HtmlInput::history_card($this->arow[$i]['f_id'],$this->arow[$i]['card_account'].' '.$this->arow[$i]['name']));
	    $tot_row=0;
	    for ($x=0;$x<count($this->aheader);$x++)
	      {
		$amount=$this->db->get_value($this->sql,array($this->arow[$i]['f_id'],$this->aheader[$x]['po_id']));
		if ($amount==null)$amount=0;
		if ( isset($tot_col[$x]))
		  {
		    $tot_col[$x]=bcadd($tot_col[$x],$amount);
		  }
		else
		  {
		    $tot_col[$x]=$amount;
		  }
                $side=($amount < 0 ) ? 'D' : 'C';
                $side=($amount==0)?"=":$side;
		echo td(nbm(abs($amount))." ".$side,' class="num" ');
		$tot_row=bcadd($tot_row,$amount);
	      }
            $side=($tot_row < 0 ) ? 'D' : 'C';
            $side=($tot_row==0)?"=":$side;
	    echo td(nbm(abs($tot_row))." ".$side,' class="num"');
	    $tot_global=bcadd($tot_global,$tot_row);
	    echo '</tr>';
		    

	  }
	echo '<tr class="highlight">';
	echo td('Totaux');
	for ($i=0;$i<count($this->aheader);$i++)
	  {
            $side=($tot_col[$i]<0)?"D":"C";
            $side=($tot_global==0)?"=":$side;
	    echo td(nbm(abs($tot_col[$i]))." ".$side,' class="num"');
	  }
        $side=($tot_global>0)?"C":"D";
        $side=($tot_global==0)?"=":$side;
	echo td(nbm($tot_global)." ".$side,' class="num input_text notice" ');
	echo '</tr>';
	echo '</table>';
      }
    if ( $this->card_poste=='2')
      {
	$this->load_poste();

	echo '<table class="result">';
	echo '<tr>';
	echo th('poste comptable ');
	foreach ($this->aheader as $h)
	  {
	    echo '<th style="text-align:right">'.h($h['po_name']).'</th>';
	  }
	echo th('Total',' style="text-align:right"');
	echo '</tr>';
	/*
	 * Show all the result
	 */
	$tot_global=0;
	for ($i=0;$i<count($this->arow);$i++)
	  {
            $tr=($i%2==0)?'<tr class="even">':'<tr class="odd">';
            echo $tr;
            echo td(HtmlInput::history_account($this->arow[$i]['card_account'],$this->arow[$i]['card_account'].' '.$this->arow[$i]['name']));
	    $tot_row=0;
	    for ($x=0;$x<count($this->aheader);$x++)
	      {
		$amount=$this->db->get_value($this->sql,array($this->arow[$i]['card_account'],$this->aheader[$x]['po_id']));
		if ($amount==null)$amount=0;
		if ( isset($tot_col[$x]))
		  {
		    $tot_col[$x]=bcadd($tot_col[$x],$amount);
		  }
		else
		  {
		    $tot_col[$x]=$amount;
		  }
                $side=($amount < 0 ) ? 'D' : 'C';
                $side=($amount==0)?"=":$side;
		echo td(nbm(abs($amount))." ".$side,' class="num" ');
		$tot_row=bcadd($tot_row,$amount);
	      }
            $side=($tot_row < 0 ) ? 'D' : 'C';
            $side=($tot_row==0)?"=":$side;
	    echo td(nbm(abs($tot_row))." ".$side,' class="num"');
	    $tot_global=bcadd($tot_global,$tot_row);
	    echo '</tr>';
		    

	  }
        echo '<tr class="highlight">';


	echo td('Totaux');
	for ($i=0;$i<count($this->aheader);$i++)
	  {
            $side=($tot_col[$i]<0)?"D":"C";
	    echo td(nbm(abs($tot_col[$i]))." ".$side,' class="num"');
	  }
       $side=($tot_global>0)?"C":"D";
       $side=($tot_global==0)?"=":$side;
       
	echo td(nbm($tot_global)." ".$side,' class="num input_text" ');
	echo '</tr>';
	echo '</table>';

      }

  }
  function export_csv()
  {
   bcscale(2);
   if ( $this->check () != 0 ) {throw new Exception ( "DATE INVALIDE");}
   $csv=new Noalyss_Csv("Anc-table");
   $csv->send_header();
   //---------------
   // By Card
    if ( $this->card_poste=='1')
      {
	$this->load_card();

	$csv->add("Fiche");
	foreach ($this->aheader as $h)
	  {
	    $csv->add($h['po_name']);
	  }
	$csv->add("Total");
        $csv->write();
        
	/*
	 * Show all the result
	 */

	for ($i=0;$i<count($this->arow);$i++)
	  {

	    $csv->add($this->arow[$i]['card_account'].' '.$this->arow[$i]['name']);
	    $tot_row=0;
	    for ($x=0;$x<count($this->aheader);$x++)
	      {
		$amount=$this->db->get_value($this->sql,array($this->arow[$i]['f_id'],$this->aheader[$x]['po_id']));
		if ($amount==null)$amount=0;
		if ( isset($tot_col[$x]))
		  {
		    $tot_col[$x]=bcadd($tot_col[$x],$amount);
		  }
		else
		  {
		    $tot_col[$x]=$amount;
		  }
		$csv->add($amount,"number");
		$tot_row=bcadd($tot_row,$amount);
	      }
	    $csv->add($tot_row,"number");
            $csv->write();

	  }
          /* ----
           * Sum for each column
           */
          $csv->add("");
          $sum_col=0;
          for ($x=0;$x<count($this->aheader);$x++) {
              $csv->add($tot_col[$x],"number");
              $sum_col=bcadd($sum_col,$tot_col[$x]);
          }
          $csv->add($sum_col,"number");
          $csv->write();
      }
    //---------------
   // By Accounting
    if ( $this->card_poste=='2')
      {
	$this->load_poste();

	$csv->add(_("Poste"));
	$csv->add(_("Description"));
	foreach ($this->aheader as $h)
	  {
	    $csv->add($h['po_name']);
	  }
	$csv->add("Total");
        $csv->write();
	/*
	 * Show all the result
	 */

	for ($i=0;$i<count($this->arow);$i++)
	  {

	    $csv->add($this->arow[$i]['card_account']);
            $csv->add($this->arow[$i]['name']);
	    $tot_row=0;
	    for ($x=0;$x<count($this->aheader);$x++)
	      {
		$amount=$this->db->get_value($this->sql,array($this->arow[$i]['card_account'],$this->aheader[$x]['po_id']));
		if ($amount==null)$amount=0;
		if ( isset($tot_col[$x]))
		  {
		    $tot_col[$x]=bcadd($tot_col[$x],$amount);
		  }
		else
		  {
		    $tot_col[$x]=$amount;
		  }
		$csv->add($amount,"number");
		$tot_row=bcadd($tot_row,$amount);
	      }
              $csv->add($tot_row,"number");
              $csv->write();
              

	  }
          /* ----
           * Sum for each column
           */
          $csv->add("");
          $csv->add("");
          $sum_col=0;
          for ($x=0;$x<count($this->aheader);$x++) {
              $csv->add($tot_col[$x],"number");
              $sum_col=bcadd($sum_col,$tot_col[$x]);
          }
          $csv->add($sum_col,"number");
          $csv->write();
      }
   
  }

}