<?php

/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2002-2020) Author Dany De Bontridder <danydb@noalyss.eu>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');
require_once NOALYSS_INCLUDE.'/class/print_operation_currency.class.php';
require_once NOALYSS_INCLUDE."/lib/select_box.class.php";
/**
 * @file
 * @brief show all the operation in currency by accounting
 */
$http=new HttpInput();
$action=$http->get("action","string","no");



$search_type=$http->request("search_type","string","all");
$print_operation_currency=Print_Operation_Currency::build($search_type);

        
$from_date=new IDate("from_date",$print_operation_currency->getData_operation()->getFrom_date());
$to_date=new IDate("to_date",$print_operation_currency->getData_operation()->getTo_date());

$from_account=new IPoste("from_account",$http->request("from_account", "string", ""));

$to_account=new IPoste("to_account",$http->request("to_account", "string", ""));

$from_account->name='from_account';
$from_account->set_attribute('gDossier',Dossier::id());
$from_account->set_attribute('jrn',0);
$from_account->set_attribute('account','from_account');

$to_account->name='to_account';
$to_account->set_attribute('gDossier',Dossier::id());
$to_account->set_attribute('jrn',0);
$to_account->set_attribute('account','to_account');

$acc_currency=new Acc_Currency($cn);
$selCurrency=$acc_currency->select_currency();
$selCurrency->selected=$print_operation_currency->getData_operation()->getCurrency_id();



if (DEBUGNOALYSS > 1) {
 echo "print_operation";   var_dump($print_operation_currency->getData_operation());
}

$msg["all"]=_("Aucun filtre");
$msg["by_card"]=_("Par fiche");
$msg["by_accounting"]=_("Par poste comptable");
$msg["by_category"]=_("Par catégorie de fiche");
$select_box=new Select_Box("filter_type",$msg[$search_type]);
$select_box->add_javascript(_("Aucun filtre"),"show_currency_type_search('all')",true);
$select_box->add_javascript(_("Par fiche"),"show_currency_type_search('card_id')",true);
$select_box->add_javascript(_("Par poste comptable"),"show_currency_type_search('account_id')",true);
$select_box->add_javascript(_("Par catégorie de fiche"),"show_currency_type_search('card_category_div_id')",true);
?>
<div class="content">
<script>
        function show_currency_type_search(p_div)
        {
            let aDiv=["card_category_div_id","card_id","account_id"];
            
            aDiv.forEach(a=>$(a).hide());
            let showelt=document.getElementById(p_div);
            if ( showelt) {
               showelt.show();
            }
          
            $('search_type').value="all";
            
           
            
            if ( p_div == 'card_category_div_id') 
                { $('search_type').value="by_category";}
            
            if ( p_div == 'card_id') 
                { $('search_type').value="by_card";}
            if ( p_div == 'account_id') 
                { $('search_type').value="by_accounting";}
                
            $('select_boxfilter_type').hide();
              if ( document.getElementById("select_box_contentfilter_type")) {
                  document.getElementById("select_box_contentfilter_type").hide();
              }
            return true;
        }
</script>
<form method="get" action="<?=NOALYSS_URL?>/do.php" onsubmit="waiting_box();return true;">
    
      <div class="form-group ">
          <label for="from_date"><?=_("Depuis")?></label>
          <?=$from_date->input()?>
      
          <label for="to_date"><?=_("jusque")?></label>
          <?=$to_date->input()?>

          <label for="currency_code"><?=_("Devise")?></label>
        <?=$selCurrency->input()?>
      <?=$select_box->input()?>
    </div>
    
    
    <div id="account_id" style="display:<?=($search_type=="by_accounting")?"block":"none"?>"><!-- comment -->
      <div class="form-group">
          <label for="from_account"><?=_("plage de postes comptables")?></label>
          <?=$from_account->input()?>
      
          <label for="to_account"><?=_("jusque")?></label>
          <?=$to_account->input()?>
      </div>
    </div>
    
     <div id="card_id" style="display:<?=($search_type=="by_card")?"block":"none"?>"><!-- comment -->
      <div class="form-group">
          <label for="card"><?=_("Fiche")?></label>
          <?php 
          // --- card
          $card = new ICard("card");
          $card->set_attribute("typecard","all");
          $card->value=$http->request("card","string","");
          $card->extra='all';
          echo $card->input();
          echo $card->search();
          ?>
      </div>
     </div>

   <div id="card_category_div_id"  style="display:<?=($search_type=="by_category")?"block":"none"?>"><!-- comment -->
      <div class="form-group">
          <label for="card_category_id"><?=_("Fiche")?></label>
          <?php 
          // --- card_category
          $card_category=new ISelect ("card_category_id");
          $card_category->value=$cn->make_array("select fd_id , fd_label from fiche_def order by fd_label");
          $card_category->selected=$http->request("card_category_id","number",0);
          ?>
          <?=$card_category->input()?>
      </div>
    </div>
    
    <?=HtmlInput::hidden("action","print")?>
    <?=HtmlInput::hidden("search_type",$search_type)?>
    <?=HtmlInput::hidden("ac",$http->request("ac"))?>
    <?=Dossier::hidden()?>
    <?=HtmlInput::submit(uniqid(), _("Afficher"))?>
</form>
<hr><!-- comment -->
</div>

<?php
    if ( $action !=="print")     { return;}
///----- There is something to print here
    
    
?>
<div class="content" style="margin-top:1rem;margin-bottom: 1rem;">
    <?php
    try
    {
        if (DEBUGNOALYSS > 1) {
            echo "SQL = ".$print_operation_currency->getData_operation()->build_SQL();
        }
        echo $print_operation_currency->export_html();
        
    }
    catch (Exception $exc)
    {
        echo h2($exc->getMessage(),'class="error"');
        return;
    }

    
    ?>
</div>

<div class="content" style="margin-top:1rem;margin-bottom: 1rem;">
    <form method="get" id="export_csv" onsubmit="download_document_form(this);return false;" style="display:inline">
        <?=HtmlInput::hidden("ac", $http->request("ac"))?>
        <?=HtmlInput::hidden("gDossier", $http->request("gDossier","number"))?>
        <?=HtmlInput::hidden("act", "CSV:pcur01")?>
        <?=HtmlInput::array_to_hidden(["from_date","to_date","search_type","from_account","to_account","card","card_category_id","p_currency_code"],$_REQUEST)?>
        
        <?=HtmlInput::submit(uniqid(),_("Export CSV"))?>
    </form>
</div>