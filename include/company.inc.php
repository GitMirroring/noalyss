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
/*!
 * \file
 * \brief Manage the company setting  : address, vat number, Check period, VAT,
 * CA ....
 */
if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

global $g_user;
$http=new HttpInput();
echo '<div class="content">';
if (isset($_POST['record_company']))
{
    $m=new Noalyss_Parameter_Folder($cn);
    $m->MY_NAME=$http->post("p_name");
    $m->MY_TVA=$http->post("p_tva");
    $m->MY_STREET=$http->post("p_street");
    $m->MY_NUMBER=$http->post("p_no");
    $m->MY_POSTCODE=$http->post("p_cp");
    $m->MY_CITY=$http->post("p_commune");
    $m->MY_PHONE=$http->post("p_tel");
    $m->MY_FAX=$http->post("p_fax");
    $m->MY_COUNTRY=$http->post("p_pays");
    $m->MY_CHECK_PERIODE=$http->post("p_check_periode");
    $m->MY_DATE_SUGGEST=$http->post("p_date_suggest");
    $m->MY_ANALYTIC=$http->post("p_compta");
    $m->MY_STRICT=$http->post("p_strict");
    $m->MY_TVA_USE=$http->post("p_tva_use");
    $m->MY_PJ_SUGGEST=$http->post("p_pj");
    $m->MY_ALPHANUM=$http->post("p_alphanum");
    $m->MY_UPDLAB=$http->post("p_updlab");
    $m->MY_STOCK=$http->post("p_stock");
    //$m->MY_CURRENCY=$http->post("p_currency");
    $m->MY_DEFAULT_ROUND_ERROR_DEB=$http->post("p_round_error_deb");
    $m->MY_DEFAULT_ROUND_ERROR_CRED=$http->post("p_round_error_cred");
    $m->MY_ANC_FILTER=$http->post("p_anc_filter");
    $m->MY_REPORT=$http->post("p_report");
    try
    {
        $m->update();
    }
    catch (Exception $e)
    {
        alert($e->getMessage());
    }
}

$my=new Noalyss_Parameter_Folder($cn);
///// Compta analytic
$array=array(
    array("value"=>"ob", 'label'=>_("obligatoire")),
    array("value"=>"op", 'label'=>_("optionnel")),
    array("value"=>"nu", 'label'=>_("non utilisé"))
);
$strict_array=array(
    array('value'=>'N', 'label'=>_('Non')),
    array('value'=>'Y', 'label'=>_('Oui'))
);

$alpha_num_array[0]=array('value'=>'N', 'label'=>_('Non'));
$alpha_num_array[1]=array('value'=>'Y', 'label'=>_('Oui'));

$updlab_array[0]=array('value'=>'N', 'label'=>_('Non'));
$updlab_array[1]=array('value'=>'Y', 'label'=>_('Oui'));

$compta=new ISelect();
$compta->selected=$my->MY_ANALYTIC;

$strict=new ISelect();
$strict->selected=$my->MY_STRICT;

$tva_use=new ISelect();
$tva_use->selected=$my->MY_TVA_USE;

$pj_suggest=new ISelect();
$pj_suggest->selected=$my->MY_PJ_SUGGEST;

$date_suggest=new ISelect();
$date_suggest->selected=$my->MY_DATE_SUGGEST;

$check_periode=new ISelect();
$check_periode->selected=$my->MY_CHECK_PERIODE;
$alpha_num=new ISelect();
$alpha_num->value=$alpha_num_array;
$alpha_num->selected=$my->MY_ALPHANUM;

$updlab=new ISelect();
$updlab->value=$updlab_array;
$updlab->selected=$my->MY_UPDLAB;

$stock=new ISelect('p_stock');
$stock->value=array(
    array('value'=>'N', 'label'=>_('Non')),
    array('value'=>'Y', 'label'=>_('Oui'))
);
$stock->selected=$my->MY_STOCK;

$anc_filter=new IText("p_anc_filter", $my->MY_ANC_FILTER);
$anc_filter->placeholder='6,7';
$anc_filter->title=_("Uniquement des chiffres séparés par des virgules");

$default_error_deb=new IPoste("p_round_error_deb", $my->MY_DEFAULT_ROUND_ERROR_DEB);
$default_error_deb->name='p_round_error_deb';
$default_error_deb->set_attribute('gDossier', Dossier::id());
$default_error_deb->set_attribute('jrn', 0);
$default_error_deb->set_attribute('account', 'p_round_error_deb');

$default_error_cred=new IPoste("p_round_error_cred", $my->MY_DEFAULT_ROUND_ERROR_CRED);
$default_error_cred->name='p_round_error_cred';
$default_error_cred->set_attribute('gDossier', Dossier::id());
$default_error_cred->set_attribute('jrn', 0);
$default_error_cred->set_attribute('account', 'p_round_error_cred');

$report=new ISelect('p_report');
$report->value = array(
    array('value'=>'N', 'label'=>_('Non')),
    array('value'=>'Y', 'label'=>_('Oui'))
);
$report->selected=$my->MY_REPORT;

// other parameters
$all=new IText();
$all->table=1;
$all->style=' class="input_text"';
?>
<form method="post" >
    <?= dossier::hidden(); ?>
    <div class="row">


        <div class="col">
            <h2 class="h-section">Société</h2>
            <div class="form-group">
                <?php
                $all=new IText();
                $all->table=1;
                $all->style=' class="input_text"';
                ?>
                <label class="w-20" for="p_name"><?= _("Nom Société") ?></label>
                <?= $all->input("p_name", $my->MY_NAME) ?>
            </div>
            <div class="form-group">
                <?php
                $all=new IText();
                $all->table=1;
                $all->style=' class="input_text"';
                ?>
                <label class="w-20" for="p_tel"><?= _("Téléphone") ?></label>
                <?= $all->input("p_tel", $my->MY_PHONE) ?>
            </div>
            <div class="form-group">
                <?php
                $all=new IText();
                $all->table=1;
                $all->style=' class="input_text"';
                ?>
                <label class="w-20" for="p_fax"><?= _("Fax") ?></label>
                <?= $all->input("p_fax", $my->MY_FAX) ?>
            </div>
            <div class="form-group">
                <?php
                $all=new IText();
                $all->table=1;
                $all->style=' class="input_text"';
                ?>
                <label class="w-20" for="p_street"><?= _("Rue") ?></label>
                <?= $all->input("p_street", $my->MY_STREET) ?>
            </div>
            <div class="form-group">
                <?php
                $all=new IText();
                $all->table=1;
                $all->style=' class="input_text"';
                ?>
                <label class="w-20" for="p_no"><?= _("Numéro") ?></label>
                <?= $all->input("p_no", $my->MY_NUMBER) ?>
            </div>
            <div class="form-group">
                <?php
                $all=new IText();
                $all->table=1;
                $all->style=' class="input_text"';
                ?>
                <label class="w-20" for="p_cp"><?= _("Code Postal") ?></label>
                <?= $all->input("p_cp", $my->MY_POSTCODE) ?>
            </div>
            <div class="form-group">
                <?php
                $all=new IText();
                $all->table=1;
                $all->style=' class="input_text"';
                ?>
                <label class="w-20" for="p_commune"><?= _("Localité") ?></label>
                <?= $all->input("p_commune", $my->MY_CITY) ?>
            </div>
            <div class="form-group">
                <?php
                $all=new IText();
                $all->table=1;
                $all->style=' class="input_text"';
                ?>
                <label class="w-20" for="p_pays"><?= _("Pays") ?></label>
                <?= $all->input("p_pays", $my->MY_COUNTRY) ?>
            </div>
            <div class="form-group">
                <?php
                $all=new IText();
                $all->table=1;
                $all->style=' class="input_text"';
                ?>
                <label class="w-20" for="p_tva"><?= _("Numéro de Tva") ?></label>
                <?= $all->input("p_tva", $my->MY_TVA) ?>
            </div>

            <div class="row">
                <div class="col">
                    <h2 class="h-section">Paramètre supplémentaire</h2>
                    <?php
                        $object=Parameter_Extra_MTable::build();
                        $object->create_js_script();
                        $object->display_table();
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-4"></div>
                <div class="col-4">
                    <?php
                    echo HtmlInput::submit("record_company", _("Sauve"), "", "button");
                    ?>

                </div>
                <div class="col-4"></div>
            </div>
        </div>

        <div class="col">
            <h2 class="h-section">Fonctionnement</h2>

            <div class="form-group">
                <label class="w-40" for="p_report">
                    <?= _("L'exercice commence par un report des soldes") ?></label>
                <?=Icon_Action::infobulle(84)?>
                <?= $report->input() ?>
            </div>

            <div class="form-group">
                <label class="w-20" for="p_compta">
<?= _("Utilisation de la compta. analytique") ?></label>
                    <?= $compta->input("p_compta", $array) ?>
            </div>

            <div class="form-group">
                <label class="" for="p_anc_filter">
<?= _("Opération analytique uniquement pour les postes comptables commençant par") ?>
                </label>

<?php
echo $anc_filter->input();
echo Icon_Action::tips($anc_filter->title);
?>
            </div>

            <div class="form-group">
                <label class="w-20" for="p_stock"><?= _("Utilisation des stocks") ?></label>
<?= $stock->input() ?>
            </div>

            <div class="form-group">
                <label class="w-20" for="p_strict"><?= _("Utilisation du mode strict ") ?></label>
<?= $strict->input("p_strict", $strict_array) ?>
            </div>


            <div class="form-group">
                <label class="w-20" for="p_tva_use"><?= _("Assujetti à la tva") ?></label>
<?= $tva_use->input("p_tva_use", $strict_array) ?>
            </div>

            <div class="form-group">
                <label class="w-20" for="p_pj"><?= _("Le numéro de pièce justificative") ?>
                </label>
                <?php
                $receipt_array=array(["label"=>"Suggérer","value"=>"Y"],["label"=>"Automatique","value"=>"A"],["label"=>"Manuel","value"=>"N"]);
                ?>
                <?= $pj_suggest->input("p_pj", $receipt_array) ?>
            </div>

                <div class="form-group">
                <label class="w-20" for="p_date_suggest"><?= _("Suggérer la date") ?></label>
<?= $date_suggest->input("p_date_suggest", $strict_array) ?>
            </div>


            <div class="form-group">
                <label class="w-20" for="p_check_periode"><?= _('Afficher la période comptable pour éviter les erreurs de date') ?>
                </label>
<?= $check_periode->input('p_check_periode', $strict_array) ?>
            </div>


            <div class="form-group">
                <label class="w-20" for="p_alphanum">
<?= _('Utilisez des postes comptables alphanumériques') ?>
                </label>
                    <?= $alpha_num->input('p_alphanum') ?>
            </div>

            <div class="form-group">
                <label class="w-20" for="p_updlab">
<?= _('Changer le libellé des détails') ?>
                </label>
                    <?= $updlab->input('p_updlab') ?>
            </div>

            <div class="form-group">
                <label class="" for="p_round_error_deb">
<?= _("Poste comptable de CHARGE (D) pour les différences d'arrondi pour les opérations en devise") ?>
                </label>
                    <?= $default_error_deb->input() ?>
            </div>

            <div class="form-group">
                <label class="" for="p_round_error_cred">
<?= _("Poste comptable en PRODUIT (C) pour les différences d'arrondi pour les opérations en devise") ?>
                </label>
                    <?= $default_error_cred->input() ?>
            </div>


                <div class="col-4"></div>

                <div class="col-4">
<?php
echo HtmlInput::submit("record_company", _("Sauve"), "", "button");
?>

                </div>
                <div class="col-4"></div>
            </div>
        </div>
    </div>

</form>
