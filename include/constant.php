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

/*! \file
 * \brief Contains all the variable + the javascript
 * and some parameter
 */

// SVNVERSION
global $version_noalyss;
/*
 * Include path
 */
$inc_path=get_include_path();
$dirname=dirname(__FILE__);

/* Global variable of the include dir */
global $g_include_dir,$g_ext_dir,$g_template_dir;
$g_include_dir=$dirname;
$g_ext_dir = $dirname."/ext";
$g_template_dir = $dirname."/template";

if (file_exists($dirname.'/config.inc.php')) require_once $dirname.'/config.inc.php';

if ( !defined("NOALYSS_HOME")) define ("NOALYSS_HOME",dirname($dirname)."/html");
if ( !defined("NOALYSS_PLUGIN")) define ("NOALYSS_PLUGIN",$g_ext_dir);
if ( !defined("NOALYSS_INCLUDE")) define ("NOALYSS_INCLUDE",$g_include_dir);
if ( !defined("NOALYSS_TEMPLATE")) define ("NOALYSS_TEMPLATE",$g_template_dir);
// pdftk can deal with all the PDF , for some of them it is preferable to fix it
// with convert , see also PDF2PS and PS2PDF if yes
if ( !defined("FIX_BROKEN_PDF")) define ("FIX_BROKEN_PDF",'NO');

// version < 6.9.1.4 , the default administrator was phpcompta
if ( !defined('NOALYSS_ADMINISTRATOR')) {
    define ('NOALYSS_ADMINISTRATOR','phpcompta');
}
if (!defined ("SESSION_KEY")) {
    define ("SESSION_KEY","RtYu0uu");
}
require_once NOALYSS_INCLUDE.'/constant.security.php';

if ( strpos($inc_path,";") != 0 ) {
  $new_path=$inc_path.';'.$dirname;
  $os=0;			/* $os is 0 for windoz */
} else {
  $new_path=$inc_path.':'.$dirname;
  $os=1;			/* $os is 1 for unix */
}
set_include_path($new_path);
@ini_set ('default_charset',"UTF-8");
@ini_set ('session.use_cookies',1);
@ini_set ('magic_quotes_gpc','off');

if ( ! defined('OVERRIDE_PARAM')) {
    ini_set ('max_execution_time',240);
    ini_set ('memory_limit','256M');
}
@ini_set ('session.use_trans_sid','on');
@session_start();

/*
 * Ini session
 */

if (! defined('NOALYSS_CAPTCHA') ) 
{
    define ("NOALYSS_CAPTCHA",false);
}

global $g_failed,$g_succeed;
$g_failed="<span style=\"font-size:18px;color:red\">&#x2716;</span>";
$g_succeed="<span style=\"font-size:18px;color:green\">&#x2713;</span>";
define ('SMALLX','#xe816;');
define ('BUTTONADD',"&#10010;");

define ('SVNINFO',NOALYSS_VERSION);
if ( ! defined  ('DEBUGNOALYSS')) {
    define ("DEBUGNOALYSS",0);
}

if ( ! defined ('LOGINPUT')) {
    define ("LOGINPUT",false);
}

if ( ! defined ('DEBUGNOALYSS') ) {
	define ('DEBUGNOALYSS',0);
}
$version_noalyss=SVNINFO;

// If you don't want to be notified of the update
if ( !defined("SITE_UPDATE"))
    define ("SITE_UPDATE",'http://www.noalyss.eu/last_version.txt');
if ( !defined("SITE_UPDATE_PLUGIN"))
    define ("SITE_UPDATE_PLUGIN",'http://www.noalyss.eu/plugin_last_version.txt');
if ( !defined ("NOALYSS_PACKAGE_REPOSITORY")) {
    define ("NOALYSS_PACKAGE_REPOSITORY","https://package.noalyss.eu/");
}
// If you don't want that the system information  is accessible
if ( ! defined ("SYSINFO_DISPLAY")) {
    define ("SYSINFO_DISPLAY",TRUE);
}
define ("DBVERSION",165);
define ("MONO_DATABASE",25);
define ("DBVERSIONREPO",20);
define ('NOTFOUND','--not found--');
define ("MAX_COMPTE",4);
define ('MAX_ARTICLE',5);
define ('MAX_ARTICLE_STOCK',10);
define ('MAX_CAT',15);
define ('MAX_CARD_SEARCH',550);
define ('MAX_FORECAST_ITEM',10);
define ('MAX_PREDEFINED_OPERATION',50);
define ('MAX_COMPTE_CARD',4);
define ('COMPTA_MAX_YEAR',2100);
define ('COMPTA_MIN_YEAR',1900);
define ('MAX_RECONCILE',25);
define ('MAX_QCODE',4);
if ( ! defined ('MAX_SEARCH_CARD') ) {
    define ('MAX_SEARCH_CARD',20);
}

define ('MAX_FOLDER_TO_SHOW',80);
define ('MAX_ACTION_SHOW',20);

if ( DEBUGNOALYSS == 0 ) {
	// PRODUCTION : nothing is displaid , report only errors and warning
        // Rapporte les erreurs d'exécution de script
        error_reporting(E_ERROR | E_WARNING );
        ini_set("display_errors",0);
	ini_set("html_errors",0);
        ini_set('log_errors',1);
        ini_set('log_errors_max_len',0);
}elseif (DEBUGNOALYSS==1) {
    /* DEVELOPPEMENT : display all errors warning notice deprecated ...*/
	error_reporting(2147483647);
	ini_set("display_errors",1);
	ini_set("display_startup_errors",1);
	ini_set("html_errors",1);
        ini_set('log_errors',1);
        ini_set('log_errors_max_len',0);
} elseif (DEBUGNOALYSS == 2 ) {
	// like level 1 plus extra  info (filename, ...)
	error_reporting(2147483647);
	ini_set("display_errors",1);
	ini_set("display_startup_errors",1);
	ini_set("html_errors",1);
        ini_set('log_errors',1);
        ini_set('log_errors_max_len',0);

}
// Erreur
define ("NOERROR",0);
define ("BADPARM",1);
define ("BADDATE",2);
define ("NOTPERIODE",3);
define ("PERIODCLOSED",4);
define ("INVALID_ECH",5);
define ("RAPPT_ALREADY_USED",6);
define ("RAPPT_NOT_EXIST",7);
define ("DIFF_AMOUNT",8);
define ("RAPPT_NOMATCH_AMOUNT",9);
define ("NO_PERIOD_SELECTED",10);
define ("NO_POST_SELECTED",11);
define ("LAST",1);
define ("FIRST",0);
define ("ERROR",12);

//!<ACTION  defines document_type for action
define('ACTION','1,5,6,7,8');

//valeurs standardd
define ("YES",1);
define ("NO",0);
define ("OPEN",1);
define ("CLOSED",0);
define ("NOTCENTRALIZED",3);
define ("ALL",4);

// Pour les ShowMenuComptaLeft
define ("MENU_FACT",1);
define ("MENU_FICHE",2);
define ("MENU_PARAM",3);

// for the fiche_inc.GetSqlFiche function
define ("ALL_FICHE_DEF_REF", 1000);

// fixed value for attr_def data
define ("ATTR_DEF_ACCOUNT",5);
define ("ATTR_DEF_NAME",1);
define ("ATTR_DEF_BQ_NO",3);
define ("ATTR_DEF_BQ_NAME",4);
define ("ATTR_DEF_PRIX_ACHAT",7);
define ("ATTR_DEF_PRIX_VENTE",6);
define ("ATTR_DEF_TVA",2);
define ("ATTR_DEF_NUMTVA",13);
define ("ATTR_DEF_ADRESS",14);
define ("ATTR_DEF_CP",15);
define ("ATTR_DEF_PAYS",16);
define ("ATTR_DEF_STOCK",19);
define ("ATTR_DEF_TEL",17);
define ("ATTR_DEF_EMAIL",18);
define ("ATTR_DEF_CITY",24);
define ("ATTR_DEF_COMPANY",25);
define ("ATTR_DEF_FAX",26);
define ("ATTR_DEF_NUMBER_CUSTOMER",30);
define ("ATTR_DEF_DEP_PRIV",31);
define ("ATTR_DEF_DEPENSE_NON_DEDUCTIBLE",20);
define ("ATTR_DEF_TVA_NON_DEDUCTIBLE",21);
define ("ATTR_DEF_TVA_NON_DEDUCTIBLE_RECUP",22);
define ("ATTR_DEF_QUICKCODE",23);
define ("ATTR_DEF_FIRST_NAME",32);

define( 'ATTR_DEF_ACCOUNT_ND_TVA',50);
define('ATTR_DEF_ACCOUNT_ND_TVA_ND',51);
define ('ATTR_DEF_ACCOUNT_ND_PERSO',52);
define ('ATTR_DEF_ACCOUNT_ND',53);
define ('ATTR_DEF_ACTIF',54);

define ("FICHE_TYPE_CLIENT",9);
define ("FICHE_TYPE_VENTE",1);
define ("FICHE_TYPE_FOURNISSEUR",8);
define ("FICHE_TYPE_FIN",4);
define ("FICHE_TYPE_CONTACT",16);
define ("FICHE_TYPE_EMPL",25);
define ("FICHE_TYPE_ADM_TAX",14);
define ("FICHE_TYPE_ACH_MAR",2);
define ("FICHE_TYPE_ACH_SER",3);
define ("FICHE_TYPE_ACH_MAT",7);
define ("FICHE_TYPE_PROJET",26);
define ("FICHE_TYPE_MATERIAL",7);
// Max size is defined by default to 2MB,
if ( ! defined("MAX_FILE_SIZE")) {
    define ("MAX_FILE_SIZE",2097152);
}
/**
 * -- pour utiliser unoconv démarrer un server libreoffice 
 * commande
 * libreoffice --headless --accept="socket,host=127.0.0.1,port=2002;urp;" --nofirststartwizard 
 * ou
 *  unoconv -l -v -s localhost
 */
if ( ! defined ('OFFICE')) define ('OFFICE','');
if ( ! defined ('GENERATE_PDF') ) define ('GENERATE_PDF','NO');

/**
 * Pour conversion GIF en PDF
 */
$convert_gif_pdf='/usr/bin/convert';
if (file_exists($convert_gif_pdf))
{
    define ('CONVERT_GIF_PDF',$convert_gif_pdf);
} else {
    define ('CONVERT_GIF_PDF','NOT');
    
}
/**
 * PDF2PS is used when the PDF is broken , used with FIX_BROKEN_PDF
 */
$pdf2ps='/usr/bin/pdf2ps';

if ( ! file_exists($pdf2ps) ) 
    define ('PDF2PS','NOT');
 else
    define ('PDF2PS',$pdf2ps);
/**
 * PS2PDF is used when the PDF is broken , used with FIX_BROKEN_PDF
 */
$ps2pdf='/usr/bin/ps2pdf';

if ( ! file_exists($ps2pdf) ) 
    define ('PS2PDF','NOT');
 else
    define ('PS2PDF',$ps2pdf);

 
/**
 * Outil pour manipuler les PDF 
 */
if ( ! isset ($pdftk))
{
 $pdftk='/usr/bin/pdftk';
}
if (file_exists($pdftk))
{
    define ('PDFTK',$pdftk);  
} 
else
{
    define ('PDFTK','NOT');  
}

// If it is not a mono folder it is a multi one
if ( !defined('MULTI')) {
    define('MULTI',1);
}

define ('JS_INFOBULLE','
        <DIV id="bulle" class="infobulle"></DIV>
        <script type="text/javascript" language="javascript"  src="js/infobulle.js">
        </script>');


// Sql string
define ("SQL_LIST_ALL_INVOICE","");

define ("SQL_LIST_UNPAID_INVOICE","  (jr_rapt is null or jr_rapt = '') and jr_valid = true  "
       );


define ("SQL_LIST_UNPAID_INVOICE_DATE_LIMIT" ,"
        where (jr_rapt is null or jr_rapt = '')
        and to_date(to_char(jr_ech,'DD.MM.YYYY'),'DD.MM.YYYY') < to_date(to_char(now(),'DD.MM.YYYY'),'DD.MM.YYYY')
        and jr_valid = true" );

/**
 * Exception
 */
// Limit email exceeds parameter
define ('EMAIL_LIMIT',1002);
define ('EXC_PARAM_VALUE',1005);
define ('EXC_PARAM_TYPE',1006);
define ('EXC_DUPLICATE',1200);
define ('EXC_INVALID',1400);
define ("UNPINDG","&#xf047;");
define ("PINDG","&#xe809;");

// Url of NOALYSS (http://...) 
// 
if ( ! defined ("NOALYSS_URL")) {
    $protocol="http";
    if ( isset ($_SERVER['REQUEST_SCHEME'] ))  {
        $protocol=$_SERVER['REQUEST_SCHEME'];
    }
    $base=$protocol.'://'.
            $_SERVER['SERVER_NAME'].
            ":".$_SERVER['SERVER_PORT'].
            dirname($_SERVER['PHP_SELF']);
    define ("NOALYSS_URL",$base);
}
if (!defined ("DEFAULT_SERVER_VIDEO_CONF")) {
    define ("DEFAULT_SERVER_VIDEO_CONF","https://www.free-solutions.org/");
}
/**
 * @brief load automatically class
 * 
 * @param string $class classname to load
 */
function noalyss_class_autoloader($class) {
    $class=strtolower($class);
    $aClass = array(
        "database"=>"class/database.class.php",
        "user"=>"class/user.class.php",
        "acc_account" => "class/acc_account.class.php",
        "acc_account_ledger" => "class/acc_account_ledger.class.php",
        "acc_balance" => "class/acc_balance.class.php",
        "acc_bilan" => "class/acc_bilan.class.php",
        "acc_compute" => "class/acc_compute.class.php",
        "acc_currency" => "class/acc_currency.class.php",
        "acc_ledger" => "class/acc_ledger.class.php",
        "acc_ledger_fin" => "class/acc_ledger_fin.class.php",
        "acc_ledger_history" => "class/acc_ledger_history.class.php",
        "acc_ledger_history_financial" => "class/acc_ledger_history_financial.class.php",
        "acc_ledger_history_generic" => "class/acc_ledger_history_generic.class.php",
        "acc_ledger_history_purchase" => "class/acc_ledger_history_purchase.class.php",
        "acc_ledger_history_sale" => "class/acc_ledger_history_sale.class.php",
        "acc_ledger_info" => "class/acc_ledger_info.class.php",
        "acc_ledger_purchase" => "class/acc_ledger_purchase.class.php",
        "acc_ledger_search" => "class/acc_ledger_search.class.php",
        "acc_ledger_sale" => "class/acc_ledger_sale.class.php",
        "acc_operation" => "class/acc_operation.class.php",
        "acc_detail" => "class/acc_operation.class.php",
        "acc_sold" => "class/acc_operation.class.php",
        "acc_misc" => "class/acc_operation.class.php",
        "acc_purchase" => "class/acc_operation.class.php",
        "acc_fin" => "class/acc_operation.class.php",
        "acc_parm_code" => "class/acc_parm_code.class.php",
        "acc_payment" => "class/acc_payment.class.php",
        "acc_plan_mtable" => "class/acc_plan_mtable.class.php",
        "acc_reconciliation" => "class/acc_reconciliation.class.php",
        "acc_report" => "class/acc_report.class.php",
        "acc_report_mtable" => "class/acc_report_mtable.class.php",
        "acc_tva" => "class/acc_tva.class.php",
        "action_document_type_mtable" => "class/action_document_type_mtable.class.php",
        "admin" => "class/admin.class.php",
        "anc_acc_link" => "class/anc_acc_link.class.php",
        "anc_acc_list" => "class/anc_acc_list.class.php",
        "anc_account" => "class/anc_account.class.php",
        "anc_account_table" => "class/anc_account_table.class.php",
        "anc_balance_double" => "class/anc_balance_double.class.php",
        "anc_balance_simple" => "class/anc_balance_simple.class.php",
        "anc_grandlivre" => "class/anc_grandlivre.class.php",
        "anc_group" => "class/anc_group.class.php",
        "anc_group_operation" => "class/anc_group_operation.class.php",
        "anc_key" => "class/anc_key.class.php",
        "anc_listing" => "class/anc_listing.class.php",
        "anc_operation" => "class/anc_operation.class.php",
        "anc_plan" => "class/anc_plan.class.php",
        "anc_print" => "class/anc_print.class.php",
        "anc_table" => "class/anc_table.class.php",
        "anticipation" => "class/anticipation.class.php",
        "balance_age" => "class/balance_age.class.php",
        "bank" => "class/bank.class.php",
        "calendar" => "class/calendar.class.php",
        "card_attribut_mtable" => "class/card_attribut_mtable.class.php",
        "card_multiple" => "class/card_multiple.class.php",
        "class_acc_account_ledger.php~" => "class/class_acc_account_ledger.php~",
         "contact" => "class/contact.class.php",
        "contact_option_ref_mtable" => "class/contact_option_ref_mtable.class.php",
        "currency_mtable" => "class/currency_mtable.class.php",
        "customer" => "class/customer.class.php",
        "database" => "class/database.class.php",
        "data_currency_operation" => "class/data_currency_operation.class.php",
        "default_menu" => "class/default_menu.class.php",
        "document" => "class/document.class.php",
         "document_export" => "class/document_export.class.php",
        "document_modele" => "class/document_modele.class.php",
        "document_option" => "class/document_option.class.php",
        "document_state_mtable.php" => "class/document_state_mtable.php",
        "document_type" => "class/document_type.class.php",
        "dossier" => "class/dossier.class.php",
        "exercice" => "class/exercice.class.php",
        "extension" => "class/extension.class.php",
        "fiche_attr" => "class/fiche_attr.class.php",
        "fiche" => "class/fiche.class.php",
         "fiche_def" => "class/fiche_def.class.php",
        "fiche_def_ref" => "class/fiche_def_ref.class.php",
        "filter_data_currency_accounting" => "class/filter_data_currency_accounting.class.php",
        "filter_data_currency_card_category" => "class/filter_data_currency_card_category.class.php",
        "filter_data_currency_card" => "class/filter_data_currency_card.class.php",
        "follow_up" => "class/follow_up.class.php",
        "follow_up_detail" => "class/follow_up_detail.class.php",
        "follow_up_other_concerned" => "class/follow_up_other_concerned.class.php",
        "forecast_category_mtable" => "class/forecast_category_mtable.class.php",
        "forecast" => "class/forecast.class.php",
        "forecast_item_mtable" => "class/forecast_item_mtable.class.php",
        "lettering" => "class/lettering.class.php",
        "lettering_card" => "class/lettering.class.php",
        "lettering_account" => "class/lettering.class.php",
        "manager" => "class/manager.class.php",
        "menu_ref" => "class/menu_ref.class.php",
        "noalyss_parameter_folder" => "class/noalyss_parameter_folder.class.php",
         "operation_category_card" => "class/operation_category_card.class.php",
        "operation_category_currency_account" => "class/operation_category_currency_account.class.php",
        "operation_predef_mtable" => "class/operation_predef_mtable.class.php",
         "package_contrib" => "class/package_contrib.class.php",
        "package_core" => "class/package_core.class.php",
        "package_noalyss" => "class/package_noalyss.class.php",
        "package_plugin" => "class/package_plugin.class.php",
        "package_repository" => "class/package_repository.class.php",
        "package_template" => "class/package_template.class.php",
        "parameter_extra_mtable" => "class/parameter_extra_mtable.class.php",
        "payment_method_mtable" => "class/payment_method_mtable.class.php",
        "pdfbalance_simple" => "class/pdfbalance_simple.class.php",
        "pdf" => "class/pdf.class.php",
        "pdf_land" => "class/pdf_land.class.php",
        "pdf_operation" => "class/pdf_operation.class.php",
        "periode" => "class/periode.class.php",
        "periode_ledger" => "class/periode_ledger.class.php",
        "periode_ledger_table" => "class/periode_ledger_table.class.php",
        "periode_mtable.notused" => "class/periode_mtable.class.php.notused",
        "pre_op_ach" => "class/pre_op_ach.class.php",
        "pre_op_advanced" => "class/pre_op_advanced.class.php",
        "pre_operation" => "class/pre_operation.class.php",
        "pre_op_fin" => "class/pre_op_fin.class.php",
        "pre_op_ods" => "class/pre_op_ods.class.php",
        "pre_op_ven" => "class/pre_op_ven.class.php",
        "prepared_query" => "class/prepared_query.class.php",
        "print_ledger" => "class/print_ledger.class.php",
        "print_ledger_detail" => "class/print_ledger_detail.class.php",
        "print_ledger_detail_item" => "class/print_ledger_detail_item.class.php",
        "print_ledger_financial" => "class/print_ledger_fin.class.php",
        "print_ledger_misc" => "class/print_ledger_misc.class.php",
         "print_ledger_simple" => "class/print_ledger_simple.class.php",
        "print_ledger_simple_without_vat" => "class/print_ledger_simple_without_vat.class.php",
        "print_operation_currency" => "class/print_operation_currency.class.php",
        "profile_menu" => "class/profile_menu.class.php",
        "sendmail" => "class/sendmail.class.php",
        "stock" => "class/stock.class.php",
        "stock_goods" => "class/stock_goods.class.php",
        "supplier" => "class/supplier.class.php",
        "tag_action" => "class/tag_action.class.php",
        "tag" => "class/tag.class.php",
        "tag_group_mtable" => "class/tag_group_mtable.class.php",
        "tag_operation" => "class/tag_operation.class.php",
        "tax_summary" => "class/tax_summary.class.php",
        "template_card_category" => "class/template_card_category.class.php",
        "todo_list" => "class/todo_list.class.php",
        "tva_rate_mtable" => "class/tva_rate_mtable.class.php",
        "acc_plan_sql" => "database/acc_plan_sql.class.php",
        "action_gestion_comment_sql" => "database/action_gestion_comment_sql.class.php",
        "action_gestion_sql" => "database/action_gestion_sql.class.php",
        "anc_key_sql" => "database/anc_key_sql.class.php",
        "anc_key_ledger_sql" => "database/anc_key_sql.class.php",
        "anc_key_detail_sql" => "database/anc_key_sql.class.php",
        "anc_key_activity_sql" => "database/anc_key_sql.class.php",
        "attr_def_sql" => "database/attr_def_sql.class.php",
        "contact_option_ref_sql" => "database/contact_option_ref_sql.class.php",
        "currency_history_sql" => "database/currency_history_sql.class.php",
        "currency_sql" => "database/currency_sql.class.php",
        "default_menu_sql" => "database/default_menu_sql.class.php",
        "document_state_sql" => "database/document_state_sql.class.php",
        "document_type_sql" => "database/document_type_sql.class.php",
        "fiche_def_ref_sql" => "database/fiche_def_ref_sql.class.php",
        "forecast_category_sql" => "database/forecast_category_sql.class.php",
        "forecast_item_sql" => "database/forecast_item_sql.class.php",
        "forecast_sql" => "database/forecast_sql.class.php",
        "form_definition_sql" => "database/form_definition_sql.class.php",
        "form_detail_sql" => "database/form_detail_sql.class.php",
        "jrn_def_sql" => "database/jrn_def_sql.class.php",
        "jrn_periode_sql" => "database/jrn_periode_sql.class.php",
        "menu_ref_sql" => "database/menu_ref_sql.class.php",
        "operation_currency_sql" => "database/operation_currency_sql.class.php",
        "op_predef_sql" => "database/op_predef_sql.class.php",
        "parameter_extra_sql" => "database/parameter_extra_sql.class.php",
        "parm_periode_sql" => "database/parm_periode_sql.class.php",
        "payment_method_sql" => "database/payment_method_sql.class.php",
        "poste_analytique_sql" => "database/poste_analytique_sql.class.php",
        "profile_menu_sql" => "database/profile_menu_sql.class.php",
        "profile_sql" => "database/profile_sql.class.php",
        "stock_goods_sql" => "database/stock_goods_sql.class.php",
        "stock_sql" => "database/stock_sql.class.php",
        "tag_group_sql" => "database/tag_group_sql.class.php",
        "tag_sql" => "database/tag_sql.class.php",
        "tmp_pcmn_sql" => "database/tmp_pcmn_sql.class.php",
        "tva_rate_sql" => "database/tva_rate_sql.class.php",
        "user_filter_sql" => "database/user_filter_sql.class.php",
        "v_currency_last_value_sql" => "database/v_currency_last_value_sql.class.php",
        "v_tva_rate_sql" => "database/v_tva_rate_sql.class.php",
        "databasecore" => "lib/database_core.class.php",
        "data_sql" => "lib/data_sql.class.php",
        "export_data" => "lib/export_data.class.php",
        "export_data_pdf" => "lib/export_data_pdf.class.php",
        "filetosend" => "lib/filetosend.class.php",
        "htmlinput" => "lib/html_input.class.php",
        "html_tab" => "lib/html_tab.class.php",
        "html_table" => "lib/html_table.class.php",
        "httpinput" => "lib/http_input.class.php",
        "iaction" => "lib/iaction.class.php",
        "ianccard" => "lib/ianccard.class.php",
        "ibutton" => "lib/ibutton.class.php",
        "ismallbutton" => "lib/ibutton.class.php",
        "icard" => "lib/icard.class.php",
        "icheckbox" => "lib/icheckbox.class.php",
        "icon_action" => "lib/icon_action.class.php",
        "iconcerned" => "lib/iconcerned.class.php",
        "idate" => "lib/idate.class.php",
        "ifile" => "lib/ifile.class.php",
        "ihidden" => "lib/ihidden.class.php",
        "impress" => "lib/impress.class.php",
        "inplace_edit" => "lib/inplace_edit.class.php",
        "inplace_switch" => "lib/inplace_switch.class.php",
        "input_checkbox" => "lib/input_checkbox.class.php",
        "inputswitch" => "lib/input_switch.class.php",
        "inum" => "lib/inum.class.php",
        "iperiod" => "lib/iperiod.class.php",
        "ipopup" => "lib/ipopup.class.php",
        "iposte" => "lib/iposte.class.php",
        "iradio" => "lib/iradio.class.php",
        "irelated_action" => "lib/irelated_action.class.php",
        "iselect" => "lib/iselect.class.php",
        "ispan" => "lib/ispan.class.php",
        "itextarea" => "lib/itextarea.class.php",
        "itext" => "lib/itext.class.php",
        "itva_popup" => "lib/itva_popup.class.php",
        "manage_table_sql" => "lib/manage_table_sql.class.php",
        "noalyss_csv" => "lib/noalyss_csv.class.php",
        "noalyss_sql" => "lib/noalyss_sql.class.php",
        "output_html_tab" => "lib/output_html_tab.class.php",
        "pdf_core" => "lib/pdf_core.class.php",
        "progress_bar" => "lib/progress_bar.class.php",
        "select_box" => "lib/select_box.class.php",
        "select_dialog" => "lib/select_dialog.class.php",
        "sendmail_core" => "lib/sendmail_core.class.php",
        "single_record" => "lib/single_record.class.php",
        "sort_table" => "lib/sort_table.class.php",
        "table_data_sql" => "lib/table_data_sql.class.php",
        "zip_extended" => "lib/zip_extended.class.php",
        "document_state_mtable"=>"class/document_state_mtable.class.php",
        "noalyss\mobile"=>"class/mobile.class.php",
        "profile_mobile_sql"=>"database/profile_mobile_sql.class.php",
        "mobile_device_mtable"=>"class/mobile_device_mtable.class.php",
        "html_input_noalyss"=>"class/html_input_noalyss.class.php",
        "card_property"=>"class/card_property.class.php"
    );
    if ( isset ($aClass[$class]) ) {
        require_once NOALYSS_INCLUDE."/".$aClass[$class];
    }
    
}

spl_autoload_register('\noalyss_class_autoloader',true);
