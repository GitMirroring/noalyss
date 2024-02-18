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
 * \brief this file respond to an ajax request and return an object with the ctl and the html string
 * at minimum
 \verbatim
 {'ctl':'','html':''}
\endverbatim
 * The parameters are
 * - gDossier
 * - op
      - dc Detail of a card
      parameter : $qcode , optional ro for readonly and nohistory without the history button, nofollowup
      - bc Blank Card : display form for adding a card
      parameter fd_id (fiche_def:fd_id)
      - st Show Type : select type of card
      parameter fil : possible values of fd_id if empty it means all the fiche cat.
      - sc Save Card : insert a new card (call first bc)
      - upc  update a card
      specific parameter qcode
      - fs  Form to search card
          parameter like
	  - inp : the input text field to update
	  - str : current content of the input text field (inp)
	  - label : the element to put the name of the card
	  - price : the element to put the price of the card (if exists)
	  - tvaid : the element to put the tvaid of the card (if exists)
	  - jrn : the concerned ledger (or all)
	  - typecard : possible values are cred, deb, filter (list of fd_id)
      - ac Add Category
          - cat type of cat to add (FICHE_TYPE_CLIENT,...)
 * - ctl (to return)
 * - popup
 * - ref if we want to refresh the window
 * - acc is accounting is visible or not
 *\see fiche fiche::Save constant.php
 */
if ( ! defined('ALLOWED')) die (_('Accès non autorisé'));

require_once NOALYSS_INCLUDE.'/lib/function_javascript.php';
require_once NOALYSS_INCLUDE.'/lib/ac_common.php';

mb_internal_encoding("UTF-8");

$var=array('gDossier','op2','ctl');
$cont=0;
/*  check if mandatory parameters are given */
foreach ($var as $v)
{
    if ( ! isset ($_REQUEST [$v] ) )
    {
        echo "$v._(' is not set ')";
        $cont=1;
    }
}
extract($_REQUEST, EXTR_SKIP ); 

if ( $cont != 0 ) exit();

set_language();

$http=new HttpInput();
$cn=Dossier::connect();
global $g_user;
$g_user=new Noalyss_User($cn);
$g_user->check(true);
$g_user->check_dossier($gDossier,true);
$html=var_export($_REQUEST,true);
// For storing extra information , example the HTML elt id to update
// after creating
$extra="";
$http=new \HttpInput();
switch($op2)
{
    case 'attribute':
        require_once "ajax/ajax_card_attribute.php";
        return ;
    /* ------------------------------------------------------------ */
    /* Display card detail */
    /* ------------------------------------------------------------ */

case 'dc':
    $f=new Fiche($cn);
    /* add title + close */
    $qcode=$http->request("qcode","string",false);
    // if there is no qcode then try to find it thanks the card id
    if ( $qcode == false ){
        $f->id=$http->get("f_id","number","0");
        if ( $f->id==0) {
            $html=HtmlInput::title_box(_("Fiche"), $ctl,"close","","y");
            $html.='<h2 class="error">'._('Aucune fiche demandée').'</h2>';
            break;
        }
        $qcode=$f->get_quick_code();
    } else {
        $f->get_by_qcode($qcode);

    }
    $title=$f->getLabelCategory();
    $html=HtmlInput::title_box($title, $ctl,"close","","y");

    // after save , we can either show a card in readonly or update a row
    $safter_save=$http->request("after_save","string","1");
    switch ($safter_save)
    {
        case "1":
            // show a card it readonly and fade it
            $after_save="update_card(this)";
            break;
        case "2":
            // update a row in the table X
            $after_save="card_update_row(this)";
            break;
        default:
            break;
    }

    if ( $qcode != null)
    {
        $can_modify=$g_user->check_action(FIC);
        if ( isset($ro) )
          {
            $can_modify=0;
          }
        if ( $can_modify==1)
          $card=$f->Display(false,$ctl);
        else
          $card=$f->Display(true);
        if ( $card == 'FNT' )
          {
            $html.='<h2 class="error">'._('Fiche non trouvée').'</h2>';
            $html.='<div style="text-align:center">'.HtmlInput::button_close($ctl).'</div>';
          }
	else
	  {

	    if ($can_modify==1)
	      {
		$html.='<form id="form_'.$ctl.'" method="get" onsubmit="'.$after_save.';return false;">';
		$html.=dossier::hidden();
		$html.=HtmlInput::hidden('f_id',$f->id);
		$html.=HtmlInput::hidden('ctl',$ctl);
	      }
	    $html.=$card;
            $html.='<p style="text-align:center">';
            $html.=HtmlInput::button_close($ctl);
	    if ( $can_modify==1)
	      {
		$html.=HtmlInput::submit('save',_('Sauver'));
	      }
	    if ( ! isset ($nohistory))$html.=HtmlInput::history_card_button($f->id,_('Historique'));
	    if ( ! isset ($nofollowup))$html.=HtmlInput::followup_card_button($f->id,_('Suivi'));
        $button_pdf=HtmlInput::button_anchor(_("PDF"),"export.php?".http_build_query([
                "act"=>"PDF:card",
                "card_id"=>$f->id,
                "gDossier"=>Dossier::id()
            ]));
        $html.=$button_pdf;
            // Display a remove button if not used and can modify card
            if ( $can_modify == 1 && $f->is_used()==FALSE)
            {
                $js=str_replace('"',"'",json_encode(["gDossier"=>Dossier::id(),'op'=>'card','op2'=>"rm_card","f_id"=>$f->id,'ctl'=>$ctl]));
                $html.=HtmlInput::button_action(_("Efface"), "delete_card($js)","x","smallbutton");
            }
            $html.='</p>';
	    if ($can_modify==1)
	      {
		$html.='</form>';
	      }
	  }
    }
    else
      {
      $html.='<h2 class="error">'._('Aucune fiche demandée').'</h2>';
      }
    break;
    /* ------------------------------------------------------------ */
    /* Blank card */
    /* ------------------------------------------------------------ */
case 'bc':
    if ( $g_user->check_action(FICADD)==1 || $g_user->check_action(FIC)==1)
    {
	    /* get cat. name */
	    $cat_name=$cn->get_value('select fd_label from fiche_def where fd_id=$1',
				 array($fd_id));
        $r=HtmlInput::title_box($cat_name, $ctl);
	        $f=new Fiche($cn);
        $r.='<form id="save_card" method="POST" onsubmit="this.ipopup=\''.$ctl.'\';save_card(this);return false;" >';
        $r.=dossier::hidden();
        $r.=(isset($ref))?HtmlInput::hidden('ref',1):'';
        $r.=HtmlInput::hidden('fd_id',$fd_id);
        $r.=HtmlInput::hidden('ctl',$ctl);
        $r.=$f->blank($fd_id);
        $r.='<p style="text-align:center">';
        $r.=HtmlInput::submit('sc',_('Sauve'));
        $r.=HtmlInput::button_close($ctl);
        $r.='</p>';
        if ( isset ($eltid)) {
            $r.=HtmlInput::hidden("eltid", $eltid);
        }
        // Action after save = 0, the card is display one second and fade out
        $after_save=$http->get("after_save","number",0);
        $r.=HtmlInput::hidden("after_save",$after_save);
        $r.='</form>';
        $html=$r;
    }
    else
    {
        $html=alert(_('Action interdite'),true);
    }
    break;
    /* ------------------------------------------------------------ */
    /* Show Type */
    /* Before inserting a new card, the type must be selected */
    /* ------------------------------------------------------------ */
case 'st':
    $sql="select fd_id,fd_label,fd_description from fiche_def";
    /*  if we filter  thanks the ledger*/
    if ( $ledger != -1 )
    {
        /* we want the card for deb or cred or both of this ledger */
        switch( $fil  )
        {
        case -1:
            $l=new Acc_Ledger($cn,$ledger);
            $array=$l->get_all_fiche_def();
            $array=(empty($array))?"-1":$array;
            $where='  where fd_id in ('.$l->get_all_fiche_def().')';
            break;
        case 'cred':
            $l=new Acc_Ledger($cn,$ledger);
            $prop=$l->get_propertie();
            if ( empty($prop) || empty($prop['jrn_def_fiche_cred']))
            {
                $where ="";
            }else {
                $where='  where fd_id in ('.$prop['jrn_def_fiche_cred'].')';
            }
            break;
        case 'deb':
            $l=new Acc_Ledger($cn,$ledger);
            $prop=$l->get_propertie();
            if ( empty($prop) || empty($prop['jrn_def_fiche_deb']) ) {
                $where = "" ;
            } else {
                $where='  where fd_id in ('.$prop['jrn_def_fiche_deb'].')';
            }
            break;
        }
    }
    else
    {
        /* we filter thanks a given model of card */
        if ( isset($cat) && ! empty($cat))
        {
            $where=sprintf(' where frd_id in ('.sql_string ($cat).')');
        }
        elseif ( isset($fil) && noalyss_strlentrim($fil) > 0 && $fil != -1 )
        {
            /* we filter thanks a given list of category of card
             */
            $where=sprintf(" where fd_id in (%s)",
                                  sql_string($fil));
        } else
        {
            // create any type of cards
            $where ="";
        }
    }
    if ( strpos($where," in ()") != 0)
    {
             $html=_("Aucune catégorie de fiche ne correspond à".
            " votre demande, le journal pourrait n'avoir accès à aucune fiche");
             break;
    }
    $sql.=" ".$where." order by fd_label";
    $array=$cn->get_array($sql);
    
    $list_fiche="";
    if ( empty($array))
    {
        $html=_("Aucune catégorie de fiche ne correspond  à votre demande");
        if ( DEBUGNOALYSS > 0 )        $html.=$sql;
    }
    else
    {
        $html=HtmlInput::title_box(_("Choix de la catégorie"), $ctl);
        $r='';
        
	$r.='<div dd>';
	$r.='<p  style="padding-left:2em">';
        $r.=_("Choisissez la catégorie de fiche à laquelle vous aimeriez ajouter une fiche").'</p>';
        if ( ! isset($eltid)) $eltid="";
        $msg=_('Choisissez une catégorie svp');
        $r.='<span id="error_cat" class="notice"></span>';
        $r.=dossier::hidden();
        $r.=(isset($ref))?HtmlInput::hidden('ref',1):'';
        $r.=_('Cherche').' '.HtmlInput::filter_table("cat_card_table", '0,1', 0);
        $r.='<table id="cat_card_table" class="result">';
        for ($i=0;$i<count($array);$i++)
        {
            $nb_count=$cn->get_value("select count(*) from fiche where fd_id=$1",[$array[$i]['fd_id']]);
            $list_fiche.=sprintf("<fiche_cat_item>%d</fiche_cat_item>",$array[$i]['fd_id']);
            $class=($i%2==0)?' class="even" ':' class="odd" ';
            $r.='<tr '.$class.' id="select_cat_row_'.$array[$i]['fd_id'].'">';
            $r.='<td >';
            $r.='<a href="javascript:void(0)" onclick="select_cat(\''.$array[$i]['fd_id'].'\','.$gDossier.',\''.$eltid.'\')">'.h($array[$i]['fd_label']).'</a>';
            $r.='</td>';
            $r.='<td>';
            $r.='<a href="javascript:void(0)" onclick="select_cat(\''.$array[$i]['fd_id'].'\','.$gDossier.',\''.$eltid.'\')">'.h($array[$i]['fd_description'])."($nb_count)".'</a>';
            $r.='</td>';
           
             $r.="</tr>";
        }
        
        $r.='</table>';
        $r.=HtmlInput::hidden('fd_id',0);
        $r.='<p style="text-align:center">';
	$r.=HtmlInput::button('Fermer',_('Fermer')," onclick=\"removeDiv('$ctl')\" ");
	$r.='</p>';
        $r.='</div>';
        $html.=$r;
        
    }
    $xml=escape_xml($html);
    header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl>$ctl</ctl>
<code>$xml</code>
<fiche_cat>{$list_fiche}</fiche_cat>        
</data>
EOF;
return;
    
    break;
    /*----------------------------------------------------------------------
     * SC save card
     * save the new card (insert)
     *
     ----------------------------------------------------------------------*/
case 'sc':
    
    if ( $g_user->check_action(FICADD)==1 )
    {
        $f=new Fiche($cn);
        $status="<status>OK</status>";
        try {
            $f->insert($fd_id,$_POST);
            $f->Get();
            $after_save=$http->post("after_save","number",0);

            // Action after save = 0, the card is display one second and fade out
            //
            if ( $after_save == 0 ) {
                $html=HtmlInput::title_box(_("Choix de la catégorie"), $ctl);
                $html.='<h2 class="notice">'._('Fiche sauvée').'</h2>';
                $html.=$f->Display(true);
                $js="";
                if ( isset( $_POST['ref'])) $js=create_script(' window.location.reload()');
                $html.=$js;
                if ( isset ($eltid)) {
                    // after adding a new card, we update some field
                    $extra="<eltid>$eltid</eltid>".
                            "<elt_value>{$f->get_quick_code ()}</elt_value>";
                            

                }
                $extra.=$status;
                $extra.="<after_save>0</after_save>";
            
            }
            // Action after save = 1 ;  after adding a card the table must be updated
            // see fiche.inc.php
            //
            if ( $after_save == 1 ){
                $f_id=$f->id;
                ob_start();
                $detail=Icon_Action::modify("mod".$f_id, sprintf("modify_card('%s')",$f_id)).
                "&nbsp;".
                Icon_Action::trash("del".$f_id,  sprintf("delete_card_id('%s')",$f_id));
                $html = td($detail);
                $html .= $f->display_row();
                $html.=ob_get_contents();
                ob_clean();
                $extra="<f_id>".$f_id."</f_id>";
                $ctl="row_card".$f_id;
                $extra.="<after_save>1</after_save>";
                
            }
        } catch (Exception $exc) {
            $html="<h2 class=\"error\">"._("Erreur sauvegarde")."</h2>";
            $html.=$exc->getMessage();
            $status="<status>NOK</status>";
            $extra=$status;
        }
	
    }
    else
    {
        $html.=alert(_('Action interdite'),true);
        $html.=HtmlInput::button_close($ctl);
    }
    break;
    /*----------------------------------------------------------------------
     * Search a card
     *
     *----------------------------------------------------------------------*/
case 'fs':
    $r=HtmlInput::title_box(_("Détail fiche"), 'search_card');
    $r.='<form method="GET" onsubmit="this.ctl=\'ipop_card\';search_get_card(this);return false;">';
    $q=new IText('query');
    $q->value=(isset($query))?$query:'';
	$r.='<span style="margin-left:50px">';
    $r.=_('Fiche contenant').Icon_Action::infobulle(19);
    $r.=$q->input();
    $r.=HtmlInput::submit('fs',_('Recherche'),"","smallbutton");
	$r.='</span>';
    $r.=dossier::hidden().HtmlInput::hidden('op','fs');
    $array=array();
 
    // to navigate
    $page_card=$http->get("page_card","number",0);
    $inactive=$http->get("inactive_card","string",0);
    if ($inactive=="undefined" || $inactive == "") $inactive=0;
    $is=new InputSwitch("inactive_card",$inactive);
    $is->value=$inactive;
    $r.=_("fiches inactives").$is->input();
    
    // save previous info
    $hidden="";
    foreach (array('accvis','inp','jrn','label','typecard','price','tvaid','amount_from_type') as $i)
    {
        if  (isset(${$i}) )
        {
            $r.=HtmlInput::hidden($i,${$i});
            $hidden.=HtmlInput::hidden($i,${$i});
            $sql_array[$i]=${$i};
        }
    }
    $r.="</form>";

    $sql_array["query"]=$query;
    $sql_array["inactive_card"]=$inactive;
    /* what is the type of the ledger */
    $type="GL";
    if (isset($jrn) && $jrn > 1)
    {
        $ledger=new Acc_Ledger($cn,$jrn);
        $type=$ledger->get_type();
    }
    // if jrn == -10 , the search is called from the detail operation from an action follow-up
    if ( isset($jrn) && $jrn == -10){
        $type=$http->request("amount_from_type","string","VEN");
    }
    $fiche=new Fiche($cn);
    /* Build the SQL and show result */
    $sql=$fiche->build_sql($sql_array);

    if ( strpos($sql," in ()") != 0)
    {
            $html="";
             $html.=HtmlInput::title_box(_('Recherche de fiche'), 'search_card');
             $html.='<h3 class="notice">';
             $html.=_("Aucune catégorie de fiche ne correspond à".
            " votre demande, le journal pourrait n'avoir accès à aucune fiche");
             $html.='</h3>';
             $html.=HtmlInput::button_close("search_card");
             break;
    }
    /**
     * if inactive == 0 , then only active card
     */
    if ( $inactive == 0 ) {
        $sql.=" and f_enable='1' ";
    }
    
     /* We limit the search to MAX_SEARCH_CARD records */
    $sql=$sql.' order by vw_name ';
   $total_card=$cn->get_value("select count(*) from ($sql) as c");
    
    $record_start=$page_card*MAX_SEARCH_CARD;
    $sql.=' limit '.MAX_SEARCH_CARD.' offset '.$record_start;
    
    $aFound=$cn->get_array($sql);
    $nb_found=count($aFound);
    for($i=0;$i<$nb_found;$i++)
     {
        $array[$i]['quick_code']=$aFound[$i]['quick_code'];
        $array[$i]['name']=h($aFound[$i]['vw_name']);
        $array[$i]['accounting']=$aFound[$i]['accounting'];
        $array[$i]['first_name']=h($aFound[$i]['vw_first_name']);
        $array[$i]['description']=h($aFound[$i]['vw_description']);
        $array[$i]['javascript']=sprintf("set_value('%s','%s');",
                                         $inp,$array[$i]['quick_code']);
        $array[$i]['javascript'].=sprintf("set_value('%s','%s');",
                       $label,j(strip_tags($aFound[$i]['vw_name'])));


        /* if it is a ledger of sales we use vw_buy
           if it is a ledger of purchase we use vw_sell*/
        
        if ( $type=="ACH" ){
            $amount=(isNumber($aFound[$i]['vw_buy']) == 1 )?$aFound[$i]['vw_buy']:0;
            $array[$i]['javascript'].=sprintf("set_value('%s','%s');",
                                              $price,$amount);
        }
        if ( $type=="VEN" ){
            $amount=(isNumber($aFound[$i]['vw_sell']) == 1 )?$aFound[$i]['vw_sell']:0;
            $array[$i]['javascript'].=sprintf("set_value('%s','%s');",
                                              $price,$amount);
        }
        $array[$i]['javascript'].=sprintf("set_value('%s','%s');",
                                          $tvaid,$aFound[$i]['tva_id']);
        $array[$i]['javascript'].="removeDiv('search_card');";

    }//foreach

    ob_start();
    require_once NOALYSS_TEMPLATE.'/card_result.php';
    $r.=ob_get_contents();
    $r.=HtmlInput::button_close("search_card");
    ob_end_clean();
    $ctl=$ctl.'_content';
    $html=$r;
    break;
    case 'action_add_concerned_card':
        require_once NOALYSS_INCLUDE.'/ajax/ajax_add_concerned_card.php';
        return;
    break;
// add several card to an action follow⁻up
    case 'link_concerned_card':
        require NOALYSS_INCLUDE.'/ajax/ajax_action_save_concerned.php';
        return;
// remove card from an action follow⁻up
    case 'action_remove_concerned':
        require NOALYSS_INCLUDE.'/ajax/ajax_action_remove_concerned.php';
        return;
case 'ac':
    if ( $g_user->check_action(FICCAT)==1 )
    {

        /*----------------------------------------------------------------------
         * Add a category, display first the form
         *
         *----------------------------------------------------------------------*/
        $ipopup=str_replace('_content','',$ctl);
        $msg="";$base="";
        switch($cat)
        {
        case FICHE_TYPE_CLIENT:
            $msg=_(' de clients');
            $base=$cn->get_value("select p_value from parm_code where p_code='CUSTOMER'");
            break;
        case FICHE_TYPE_FOURNISSEUR:
            $msg=_(' de fournisseurs');
            $base=$cn->get_value("select p_value from parm_code where p_code='SUPPLIER'");
            break;
        case FICHE_TYPE_ADM_TAX:
            $msg=_(' d\'administration');
            $base='';
            break;
	case FICHE_TYPE_CONTACT:
            $msg=_(' de contacts');
            $base='';
            break;
        case FICHE_TYPE_FIN:
            $msg=_(' Banque');
            $base=$cn->get_value("select p_value from parm_code where p_code='BANQUE'");
            break;
        case FICHE_TYPE_EMPL:
            $msg=_(' Employé ou administrateur');
            $base='';
            break;
         
        }

        $html='';
        /*  show the form */

        $search=new IPoste("class_base");
        $search->size=40;
        $search->value=$base;
        $search->label=_("Recherche poste");
        $search->set_attribute('gDossier',dossier::id());
        $search->set_attribute('account',$search->name);
        $search->set_attribute('ipopup','ipop_account');

	$nom_mod=new IText("nom_mod");
        $str_poste=$search->input();
        $submit=HtmlInput::submit('save',_('Sauve'));
        ob_start();
        require(NOALYSS_TEMPLATE.'/category_of_card.php');
        $html.=ob_get_contents();
        ob_end_clean();

    }
    else
    {
        $html=alert(_('Action interdite'),true);
    }
    break;
case 'scc':
    /*----------------------------------------------------------------------
     * Save card Category into the database and return a ok message
     *
     *----------------------------------------------------------------------*/
    $html='';
    $invalid=0;
    if ( $g_user->check_action(FICCAT) == 1 )
    {
        
        $html="";
        $nom_mod=$http->get("nom_mod");
        $class_base=$http->get("class_base");
        $fd_description=$http->get("nom_mod");
        if ( noalyss_strlentrim($nom_mod) != 0 )
        {
            $array=array("FICHE_REF"=>$cat,
                         "nom_mod"=>$nom_mod,
                         "class_base"=>$class_base,
                          "fd_description"=>$fd_description);
            
            if ( isset ($_POST['create'])) $array['create']=1;
            
            $catcard=new Fiche_Def($cn);
            
            ob_start();
            $result=$catcard->Add($array);
            
            $html.=ob_get_contents();
            ob_end_clean();
            
            if (  $result == 1)
            {
                $script="alert_box('"._('Catégorie existe déjà')."')";
                $invalid=1;
            }
            else{
                $script="alert_box('"._('Catégorie sauvée')."');removeDiv('$ctl')";
            }
                
            $html.=create_script($script);
        }
        else
        {
            $script="alert_box('"._("Le nom ne peut pas être vide")."')";
            $html.=create_script($script);

            $invalid=1;
        }
    }
    else
    {
        $html=alert(_('Action interdite'),true);
        $invalid=1;
    }
    if  ($invalid == 1) {
        $ctl="info_div";
    }
    break;
    
// Update a card and then display the result
// in a readonly box
case 'upc':
    $html=HtmlInput::title_box("Détail fiche", $ctl);

  if ( $g_user->check_action(FICADD)==0 )
    {
      $html.=alert(_('Action interdite'),true);
    }
  else
    {
      if ($cn->get_value('select count(*) from fiche where f_id=$1',array($_GET['f_id'])) == '0' )
	{
	  $html.=alert(_('Fiche non valide'),true);
	  }

      else
	{
	  $html=HtmlInput::title_box(_('Détail fiche (sauvée)'),$ctl);

	  $f=new Fiche($cn,$_GET['f_id']);
	  ob_start();
	  $f->update($_GET);
	  $html.=ob_get_contents();
	  ob_end_clean();
	  $html.=$f->Display(true);
	}
      }
      $html.='<p style="text-align:center">'.HtmlInput::button_close($ctl).'</p>';
      break;
// Update a card and then display the result
// in the table
case 'upr':
    $f_id=$http->get("f_id","number");
    $html="";
    if ( $g_user->check_action(FICADD)==0 )
    {
        $html.=alert(_('Action interdite'),true);
    }
  else
    {
      if ($cn->get_value('select count(*) from fiche where f_id=$1',array($f_id)) == '0' )
	{
	  $html.=alert(_('Fiche non valide'),true);
	  }

      else
	{

	  $f=new Fiche($cn,$f_id );
	  ob_start();
	  $f->update($_GET);
          $detail=Icon_Action::modify("mod".$f_id, sprintf("modify_card('%s')",$f_id)).
                "&nbsp;".
                Icon_Action::trash("del".$f_id,  sprintf("delete_card_id('%s')",$f_id));
          $html.=td($detail);
	  $html.=$f->display_row();
	  $html.=ob_get_contents();
	  ob_end_clean();
	}
      }
      break;
      
      
      
//------------------------------------------------------------------
// Unlink a card
//------------------------------------------------------------------
 case 'rm_card':
    $html=HtmlInput::title_box("Détail fiche", $ctl);
     
    if ( $g_user->check_action(FIC)==0 )
    {
      $html.=alert(_('Action interdite'),true);
    }
    else
    {
      if ($cn->get_value('select count(*) from fiche where f_id=$1',array($_GET['f_id'])) == '0' )
	{
	  $html.=alert(_('Fiche non valide'),true);
	  }

      else
	{

	  $f=new Fiche($cn,$_GET['f_id']);
          if ( $f->is_used()==0){
            $f->delete();
            $html="OK";
          } else {
            $html="";
            $html=_("Fiche non effacée");
          }

	}
      }
      break;
 //---------------------------------------------------------------------------------------------------------------
 // Display option of a contact in an action-followup       
 //---------------------------------------------------------------------------------------------------------------       
        case 'display_card_option':
            
            require_once NOALYSS_INCLUDE.'/ajax/ajax_display_card_option.php';
            return;
            break;
            
 //---------------------------------------------------------------------------------------------------------------
 // Save option of a contact in an action-followup       
 //---------------------------------------------------------------------------------------------------------------       
        case 'save_card_option':
            require_once NOALYSS_INCLUDE.'/ajax/ajax_save_card_option.php';
            return;

            break;
  // ----------------------------------------------------------------------------------------------------------------
  // Display a list of other card linked to the event / followup
  // ----------------------------------------------------------------------------------------------------------------
        case 'action_concerned_list':
            require_once NOALYSS_INCLUDE.'/ajax/ajax_action_concerned_list.php';
            return ;
            break;
            
} // switch
$xml=escape_xml($html);
if (DEBUGNOALYSS > 0 && headers_sent()) {
    echo $html;return;
}
header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl>$ctl</ctl>
<code>$xml</code>
$extra
</data>
EOF;
