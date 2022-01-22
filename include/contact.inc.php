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
/**\brief include from client.inc.php and concerned only the contact card and
 * the contact category
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');

$http=new HttpInput();

$low_action = $http->request('sb','string','list');
/**
 * \file
 * \brief Called from the module "Gestion" to manage the contact
 */
$href=basename($_SERVER['PHP_SELF']);

// by default open liste
if ($low_action == "")
    $low_action = "list";


//-----------------------------------------------------
// Remove a card
//-----------------------------------------------------
if (isset($_POST['action_fiche']))
{
    
    if ( $_POST['action_fiche'] == 'delete_card') 
    {
        if ( $g_user->check_action(FICADD) == 0 )
        {
            alert(_('Vous  ne pouvez pas enlever de fiche'));
            return;
        }

        $f_id = $http->request('f_id','number');

        $fiche = new Contact($cn, $f_id);
        $fiche->remove();
        $low_action = "list";
    }
}

//-----------------------------------------------------
//    list of contact
//-----------------------------------------------------
if ($low_action == "list")
{
    ?>
    <div class="content">
        <div>
           
    	<form method="get" action="<?php echo $href;?>">
		<?php
		echo dossier::hidden();
		$a = $http->get("query","string","");
        echo _("Cherche ").HtmlInput::filter_table_form("contact_tb", '0,1,2,3,4,5,6', 1,"query",$a);

		$sel_card = new ISelect('cat');
		$sel_card->value = $cn->make_array('select fd_id, fd_label from fiche_def ' .
			' where  frd_id=' . FICHE_TYPE_CONTACT .
			' order by fd_label ', 1);
		$sel_card->selected = (isset($_GET['cat'])) ? $_GET['cat'] : -1;
		$sel_card->javascript = ' onchange="waiting_box();submit(this);"';

		echo _('Catégorie :') . $sel_card->input();

		$sl_company=new ISelect("sel_company");
		$sl_company->value = $cn->make_array('select distinct ad_value,ad_value from fiche_detail as fd' .
			' join fiche as f1 on (f1.f_id=fd.f_id) join fiche_def as fdf on (f1.fd_id=fdf.fd_id)
				where
				ad_id='.ATTR_DEF_COMPANY. " and frd_id= ".FICHE_TYPE_CONTACT.
			' order by 1', 1);
		$sl_company->selected = $http->get("sel_company","string","");
		echo _('Société :') . $sl_company->input();

		?>
    	    <input type="submit" class="button" name="submit_query" value="<?php echo  _('recherche')?>">
    	    <input type="hidden" name="ac" value="<?php echo $http->request('ac')?>">
    	</form>
             <p class="notice"><?=_("Si vous modifiez un contact, il faut recharger la page pour voir les changements")?></p>
        </div>
	<?php
	$client = new contact($cn);
	$search =$http->get("query","string","");
	$sql = "";
	if (isset($_GET['cat']))
	{
	    $cat=$http->get("cat","number");
            if ($cat!= -1 )     $sql = sprintf(" and fd_id = %s", $cat);
	}
	if (isset($_GET['sel_company']))
	{
            $sel_company=$http->get("sel_company");
	    if ($sel_company != '' && $sel_company != "-1")
            {

                $client->company=$sel_company;
            }
	}

	echo '<div class="content">';
	echo $client->Summary($search,"contact",$sql);


	echo '<br>';
	echo '<br>';
	echo '<br>';
	/* Add button */
	$f_add_button = new IButton('add_card');
	$f_add_button->label = _('Créer une nouvelle fiche');
	$f_add_button->set_attribute('win_refresh', 'yes');
	$f_add_button->set_attribute('type_cat', FICHE_TYPE_CONTACT);
	$f_add_button->javascript = " select_card_type(this);";
	echo $f_add_button->input();

    $f_cat_button=new IButton('add_cat');
    $f_cat_button->set_attribute('ipopup','ipop_cat');
    $f_cat_button->set_attribute('type_cat',FICHE_TYPE_CONTACT);
    $f_cat_button->label=_('Ajout d\'une catégorie');
    $f_cat_button->javascript='add_category(this)';
    echo $f_cat_button->input();

	echo '</div>';
    echo '</div>';


}
/*----------------------------------------------------------------------
 * Detail for a card, Suivi, Contact, Operation,... *
 * cc stands for contact card
 *----------------------------------------------------------------------*/
if ( $low_action == 'detail')
{
    /* Menu */
    require_once NOALYSS_INCLUDE.'/category_card.inc.php';
    return;
}

    html_page_stop();
?>
