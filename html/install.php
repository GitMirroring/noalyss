<?php
session_start();
?>
<!doctype html>
<HTML><HEAD>
    <TITLE>Noalyss - Install</TITLE>
    <META http-equiv="Content-Type" content="text/html; charset=UTF8">
    </title>
<head>
<link rel="icon" type="image/ico" href="favicon.ico" />
 <META http-equiv="Content-Type" content="text/html; charset=UTF8">
 <script type="text/javascript" charset="utf-8" language="javascript" src="js/prototype.js"></script>
 <link type="text/css" REL="stylesheet" href="style-classic.css"/>
 <style>
     body {
         font : 100%;
         color:darkblue;
         margin-left : 50px;
         margin-right: 50px;
         background-color: #F8F8FF;
     }
     h1 {
         font-size: 120%;
         text-align: center;
         background-color: darkblue;
         color:white;
         text-transform: uppercase;
     }
     h2 {
         font-size: 105%;
         text-align: left;
         text-decoration: underline;
     }
     h3 {
         font-size : 102%;
         font-style: italic;
         margin-left: 3px;
     }
 
    .button {
        font-size:110%;
        color:white;
        font-weight: bold;
        border:0px;
        text-decoration:none;
        font-family: helvetica,arial,sans-serif;
        background-image: url("image/bg-submit2.gif");
        background-repeat: repeat-x;
        background-position: left;
        text-decoration:none;
        font-family: helvetica,arial,sans-serif;
        border-width:0px;
        padding:2px 4px 2px 4px;
        cursor:pointer;
        margin:1px 2px 1px 2px;
        -moz-border-radius:2px 2px;
        border-radius:2px 2px;
     }
    .button:hover {
    cursor:pointer;
    background-color:white;
    border-style:  solid;
    border-width:  0px;
    font-color:blue;
    margin:2px 2px 1px 2px;
    }
    .warning,.error {
        color:red;
    }
 </style>
</head>
<body>
<p align="center">
  <IMG SRC="image/logo6820.png" style="width: 365px;height: 150px" alt="NOALYSS">
</p>

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
/* $Revision*/
// Copyright Author Dany De Bontridder danydb@aevalys.eu
/*!\file
 * \brief This file permit to upgrade a version of NOALYSS , it should be
 *        used and immediately delete after an upgrade.
 *        This file is included in each release  for a new upgrade
 *
 */
if ( ! isset($_GET['lang'])){
?>
<p>
    Choisissez votre langue ,pour MacOS utilisez "Not Used"
</p>
<p>
    Select your language, for MacOS user please use "Not Used"
</p>
<form method="GET">
    Language : <select name="lang">
        <OPTION value="fr_FR.utf8">Français</option>
        <OPTION value="en_US.utf8">English</option>
        <OPTION value="none">Not Used</option>
    </select>
    <input type="submit" value="Continue">
</form>
<?php
    exit();
}
require_once '../include/constant.php';
include_once NOALYSS_INCLUDE.'/lib/ac_common.php';
include_once NOALYSS_INCLUDE.'/lib/class_html_input.php';
if ( $_GET['lang'] == "en_US.utf8" || $_GET['lang']=='fr_FR.utf8')
{
    $_SESSION['g_lang']=$_GET['lang'];
    set_language();
}
?>
 <script type="text/javascript" charset="utf-8" language="javascript" src="js/infobulle.js">
</script>
<script>
var content=new Array();    
content[200]="<?php echo _("Indiquez ici le récuterpertoire où les documents temporaires peuvent être sauvés exemple c:/temp, /tmp")?>";
content[201]="<?php echo _("Désactiver le changement de langue (requis pour MacOSX)")?>";
content[202]="<?php echo _("Le chemin vers le repertoire contenant psql, pg_dump...")?>";
content[203]="<?php echo _("Utilisateur de la base de donnée postgresql")?>";
content[204]="<?php echo _("Mot de passe de l'utilisateur de Postgresql")?>";
content[205]="<?php echo _("Port pour postgresql")?>";
content[206]="<?php echo _("En version mono dossier, le nom de la base de données doit être mentionné")?>";
content[207]="<?php echo _("Vous devez choisir si NOALYSS est installé sur l'un de vos servers ou sur un server mutualisé qui ne donne qu'une seule base de données")?>";
content[208]="<?php echo _("Serveur postgresql")?>";

</script>

<DIV id="bulle" class="infobulle"></DIV>

<?php

$failed="<span style=\"font-size:18px;color:red\">&#x2716;</span>";
$succeed="<span style=\"font-size:18px;color:green\">&#x2713;</span>";
$inc_path=get_include_path();
global $os;
$inc_path=get_include_path();
global $os;
if ( strpos($inc_path,";") != 0 ) {
  $new_path=$inc_path.';../../include;addon';
  $os=0;			/* $os is 0 for windoz */
} else {
  $new_path=$inc_path.':../../include:addon';
  $os=1;			/* $os is 1 for unix */
}

/**
 *@brief create correctly the htaccess file
 * @deprecated since version 6.9
 */
function create_htaccess_deprecated()
{
	global $os;


	/* If htaccess file doesn't exists we create them here
	 * if os == 1 then windows, 0 means Unix
	 */
	$file='..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'.htaccess';
	if (! file_exists($file))
	{
		$hFile=@fopen($file,'w+');
		if ( ! $hFile )     exit(_('Impossible d\'&eacute;crire dans le r&eacute;pertoire include'));
		fwrite($hFile,'order deny,allow'."\n");
		fwrite($hFile,'deny from all'."\n");
		fclose($hFile);
	}
	$file='..'.DIRECTORY_SEPARATOR.'.htaccess';
	if (! file_exists($file))
	{

		$hFile=@fopen($file,'w+');
		if ( ! $hFile )     exit(_('Impossible d\'&eacute;crire dans le r&eacute;pertoire html'));
		$array=array("php_flag  magic_quotes_gpc off",
				 "php_value max_execution_time 240",
				 "php_value memory_limit 20M",
				 "AddDefaultCharset utf-8",
				 "php_flag  register_globals off",
				 "php_value error_reporting 10239",
				 "php_value post_max_size 20M",
				 "php_flag short_open_tag on",
				 "php_value upload_max_filesize 20M",
				 "php_value session.use_trans_sid 1",
				 "php_value session.use_cookies 1",
				 "php_flag session.use_only_cookies on");

		if ( $os == 0 )
		  fwrite($hFile,'php_value include_path .;../../include;../include;addon'."\n");
		else
		  fwrite($hFile,'php_value include_path .:../../include:../include:addon'."\n");
		foreach ($array as $value ) fwrite($hFile,$value."\n");
		fclose($hFile);
	}

}
// Retrieve informations from the very screen
// 
$http=new HttpInput();
$db_user=$http->request("cuser","string", "");
$db_password=$http->request("cpasswd","string", "");
$db_host=$http->request("chost","string", "");
$db_port=$http->request("cport","string", "");
$multi=$http->request("multi","string", "N");
$locale=$http->request("clocale","string", "1");
$ctmp=$http->request("ctmp","string", "/tmp");
$cpath=$http->request("cpath","string", "/usr/bin");
$db_name=$http->request("cdbname", "string","");
$cadmin=$http->request("cadmin","string", "admin");
$cadmin=strtolower($cadmin);
//-------------------------------------------------------------------------
// warn only if we can not write in include 
//-------------------------------------------------------------------------
if ( is_writable ('install.php') == false ) {
    echo '<h2 class="notice"> '._("Ecriture non possible").' </h2>'.
            '<p class="warning"> '.
            _("On ne peut pas écrire dans le répertoire de NOALYSS, changez-en les droits ")
            .'</p>';
  }
//----------------------------------------------------------------------------
// We try to connect with the supplied information
// If we succeed we continue the check
// otherwise we turn back to the first screen
// The config file is created here 
//----------------------------------------------------------------------------

if (isset($_POST['save_config'])) {
  require_once NOALYSS_INCLUDE.'/lib/config_file.php';
  // Try to connect , if it doesn't work that do not create the config file 
  if ($multi=="N") {
    $cnx = Database::connect($db_user, $db_password,'template1', $db_host, $db_port); 
  }else {
    $cnx = Database::connect($db_user, $db_password,$db_name, $db_host, $db_port); 
  }
  // ----- 
  // If conx successfull save the file or display it
  // -----
  if ( $cnx !== false ) {
       echo '<h1>'._('Important').'</h1>';
       echo '<p>'._('Utilisateur administrateur'),' ',$cadmin,'</p>';
       echo '<p>',_('Mot de passe'),' phpcompta','</p>';
      // Create the db
      if (is_writable(NOALYSS_INCLUDE)) { 
        $url=config_file_create($_POST,1,$os); 
        echo '
            <form method="post" action="?lang='.$_GET['lang'].'" >'.
           _('Les informations sont sauv&eacute;es vous pouvez continuer').
          '<input type="submit" class="button" value="'._('Continuer').'">
            </form>';
        return;
      } else {
          echo '<p class="warning">';
          echo _('Fichier non sauvé');
          echo '</p>';
          echo '<p>';
          printf ( _('Créez ce fichier %s avec les informations suivantes '),
                  NOALYSS_INCLUDE.'/config.inc.php');
          echo '</p>';
          echo '<p>';
          print (_('Puis cliquez sur ce lien'))." ";
          echo '<a href="install.php?lang='.$_GET['lang'].'">'._('Installation')."</a>";
          echo '</p>';
      
          echo '<textarea cols="80" rows="50" style="height:auto">';
          echo display_file_config($_POST,1,$os);
          echo '</textarea>';
          return;
      }
  } else {
      echo '<h2 class="warning">';
      echo _('Impossible de se connecter à Postgresql, vérifiez les informations de connection');
      echo '</h2>';
  }
 }


//------------------------------------------------------------------------
// Check that the file config.inc.php exists , if not then propose to 
// enter information and exit
//
//------------------------------------------------------------------------
if ( ! file_exists(NOALYSS_INCLUDE.'/config.inc.php')) {
  echo '<h1 class="info">'._('Entrez les informations nécessaires à noalyss').'</h1>';
  echo '<form method="post">';
  require_once NOALYSS_INCLUDE.'/lib/config_file.php';
  
  echo config_file_form($_POST);
  echo '<div style="position:float;float:left;"></div>';
  echo '<p style="text-align:center">',
        HtmlInput::submit('save_config',_('Continuer'),"","button"),
          '</p>';
  echo "</div>";
  echo '</form>';
  exit();
}

//----------------------------------------------------------------------
// End functions
//
//----------------------------------------------------------------------

// Verify some PHP parameters
// magic_quotes_gpc = Off
// magic_quotes_runtime = Off
// magic_quotes_sybase = Off
// include_path
require_once NOALYSS_INCLUDE.'/lib/config_file.php';
require_once NOALYSS_INCLUDE.'/lib/class_database.php';

// we shouldn't use it 
// if ( defined ("MULTI") && MULTI==1) { create_htaccess();}

echo '<h1 class="title">'._('Configuration').'</h1>';
?>
<h2>Info</h2>
<?php echo _('Vous utilisez le domaine'),domaine; ?>
<h2>PHP</h2>
<?php

$flag_php=0;

//ini_set("memory_limit","200M");
echo "<ul style=\"list-style-type: square;\">";
foreach (array('magic_quotes_gpc','magic_quotes_runtime') as $a) {
echo "<li>";
  if ( ini_get($a) == false ) print $a.': '.$succeed;
  else {
        print $a.': '.$failed;
	print ("<h2 class=\"error\">$a "._('a une mauvaise valeur')." !</h2>");
	$flag_php++;
  }

echo "</li>";
}
$module=get_loaded_extensions();

echo "<li>";
$str_error_message=_('Vous devez installer ou activer l\'extension').'<span style="font-weight:bold"> %s </span>';
if (  in_array('mbstring',$module) == false ){
  echo 'module mbstring '.$failed;
  echo '<span class="warning">',
        sprintf($str_error_message, "mbstring"),
        ' </span>';
  $flag_php++;
} else echo 'module mbstring '.$succeed;
echo "</li>";

echo "<li>";
if (  in_array('pgsql',$module) == false )
{
  echo 'module PGSQL '.$failed;
   echo '<span class="warning">',
        sprintf($str_error_message, "psql"),
        ' </span>';
  $flag_php++;
} else echo 'module PGSQL '.$succeed;
echo "</li>";

echo "<li>";
if ( in_array('bcmath',$module) == false )
{
  echo 'module BCMATH ok '.$failed;
  echo '<span class="warning">',
        sprintf($str_error_message, "bcmath"),
        ' </span>';
  $flag_php++;
} else echo 'module BCMATH '.$succeed;
echo "</li>";

echo "<li>";
if (in_array('gettext',$module) == false )
{
  echo 'module GETTEXT '.$failed;
   echo '<span class="warning">',
        sprintf($str_error_message, "gettext"),
        ' </span>';
  $flag_php++;
} else echo 'module GETTEXT '.$succeed;
echo "</li>";

echo "<li>";
if ( in_array('zip',$module) == false )
{
  echo 'module ZIP '.$failed;
   echo '<span class="warning">',
        sprintf($str_error_message, "zip"),
        ' </span>';
  $flag_php++;
} else echo 'module ZIP '.$succeed;
echo "</li>";
echo "<li>";
if ( in_array('gd',$module) == false )
{
  echo 'module GD '.$failed;
   echo '<span class="warning">',
        sprintf($str_error_message, "gd"),
        ' </span>';
  $flag_php++;
} else echo 'module GD '.$succeed;
echo "</li>";

if ( ini_get("max_execution_time") < 60 )  {
        echo "<li>";
        echo _('Avertissement').' : '.$failed;
	echo '<span class="info"> ',
                _("max_execution_time devrait être de 60 minimum"),
                '</span>';
        echo "</li>";
}

if ( ini_get("register_globals") == true)  {
        echo "<li>";
        echo _('Avertissement').' : '.$failed;
	print '<span class="warning"> '._('register_globals doit être à off').'</span>';
        echo "</li>";
	$flag_php++;
}
echo "</li>";

 echo "</ul>";
if ( $flag_php==0 ) {
	echo '<p class="info"> '._('php.ini est bien configuré ').$succeed.'</p>';
} else {
	echo '<p class="warning"> '._('php mal configuré ').$failed.' </p>';
}
/* check user */
if ( (defined("MULTI") && MULTI==1)|| !defined("MULTI"))
{

	$cn=new Database(-1,'template');
} else
{
	$cn=new Database();
}

?>
<h2><?php echo _('Base de données')?></h2>
<?php
 // Verify Psql version
 //--
$sql="select setting from pg_settings where name='server_version'";
$version=$cn->get_value($sql);

echo _("Version base de données :"),$version;

if ( $version[0] < 8 ||
     ($version[0]=='8' && $version[2]<4)
     )
  {
?>
  <p><?php echo $failed . _(" Vous devez absolument utiliser au minimum une version 8.4 de PostGresql, si votre distribution n'en
offre pas, installez-en une en la compilant. Lisez attentivement la notice sur postgresql.org pour migrer
vos bases de donn&eacute;es")?>
</p>
<?php exit(); //'
} else {
    echo " ",$g_succeed;
}

?>
<h3><?php echo _('Paramètre base de données')?></h3>
<?php
// Language plsql is installed
//--
$sql="select lanname from pg_language where lanname='plpgsql'";
$Res=$cn->count_sql($sql);
if ( $Res==0) { ?>
<p><?php echo $failed._("Vous devez installer le langage plpgsql pour permettre aux fonctions SQL de fonctionner.")?></p>
<p><?php echo _("Pour cela, sur la ligne de commande en tant qu\'utilisateur postgres, faites createlang plpgsql template1")?>
</p>

<?php exit(); }

include_once('lib/ac_common.php');
require_once('class/class_dossier.php');

// Memory setting
//--
$sql="select name,setting
      from pg_settings
      where
      name in ('effective_cache_size','shared_buffers')";
$cn->exec_sql($sql);
$flag=0;
for ($e=0;$e<$cn->size();$e++) {
  $a=$cn->fetch($e);
  switch ($a['name']){
  case 'effective_cache_size':
    if ( $a['setting'] < 1000 ){

      printf ('<p class="warning">'.$failed._('Attention le paramètre effective_cache_size est de %s'.
	" au lieu de 1000")."</p>",$a['setting']);
      $flag++;
    }
    break;
  case 'shared_buffers':
    if ( $a['setting'] < 640 ){
      print '<p class="warning">'.$failed;
      printf('Attention le paramètre shared_buffer est de %s
	au lieu de 640',$a['setting']);
      print "</p>";
      $flag++;
    }
    break;
  }
 }
if ( $flag == 0 ) {
  echo '<p class="info">'._('La base de données est bien configurée ').$succeed.'</p>';
 } else {
  echo '<p class="warning">'.$failed;
  printf (_('Il y a %s param&egrave;tre qui sont trop bas'),$flag);
  echo '</p>';
 }
if ( ! isset($_POST['go']) ) {
?>
<span style="text-align: center">
    <FORM METHOD="post" action="install.php?lang=<?php echo $_GET['lang']?>">
<input type="submit" class="button" name="go" value="<?php echo _("Commencer la mise à jour ou l'installation");?>">
</form>
</span>
<?php
}
if ( ! isset($_POST['go']) )
	exit();
// Check if account_repository exists
if (!defined("MULTI") || (defined("MULTI") && MULTI == 1))
        $account = $cn->count_sql("select * from pg_database where datname=lower('" . domaine . "account_repository')");
else
        $account=1;

// Create the account_repository
if ($account == 0 ) {

  echo "Creation of ".domaine."account_repository";
  if ( ! DEBUG) ob_start();
  $cn->exec_sql("create database ".domaine."account_repository encoding='utf8'");
  $cn=new Database();
  $cn->start();
  $cn->execute_script(NOALYSS_INCLUDE."/sql/account_repository/schema.sql");
  $cn->execute_script(NOALYSS_INCLUDE."/sql/account_repository/data.sql");
  $cn->execute_script(NOALYSS_INCLUDE."/sql/account_repository/constraint.sql");
  /* update name administrator */
  $cadmin=NOALYSS_ADMINISTRATOR;
  $cn->exec_sql("update ac_users set use_login=$1 where use_id=1",
              array(strtolower($cadmin)));

  $cn->commit($cn);

 if ( ! DEBUG) ob_end_clean();

  echo _("Creation of Modele 1");
  if ( ! DEBUG) ob_start();
  $cn->exec_sql("create database ".domaine."mod1 encoding='utf8'");

  $cn=new Database(1,'mod');
  $cn->start();
  $cn->execute_script(NOALYSS_INCLUDE.'/sql/mod1/schema.sql');
  $cn->execute_script(NOALYSS_INCLUDE.'/sql/mod1/data.sql');
  $cn->execute_script(NOALYSS_INCLUDE.'/sql/mod1/constraint.sql');
  $cn->commit();

  if ( ! DEBUG) ob_end_clean();

  echo _("Creation of Modele 2");
  $cn->exec_sql("create database ".domaine."mod2 encoding='utf8'");
  $cn=new Database(2,'mod');
  $cn->start();
  if ( ! DEBUG) { ob_start();  }
  $cn->execute_script(NOALYSS_INCLUDE.'/sql/mod1/schema.sql');
  $cn->execute_script(NOALYSS_INCLUDE.'/sql/mod2/data.sql');
  $cn->execute_script(NOALYSS_INCLUDE.'/sql/mod1/constraint.sql');
  $cn->commit();

 if ( ! DEBUG) ob_end_clean();
echo '<h1>'._('Important').'</h1>';
echo '<p>'._('Utilisateur  administrateur'),' ',NOALYSS_ADMINISTRATOR,'</p>';
echo '<p>',_('Mot de passe'),' phpcompta','</p>';
 }// end if
// Add a french accountancy model
//--
$cn=new Database();

echo "<h1>"._('Mise à jour du systeme')."</h1>";
echo "<h2 >"._("Mise  à jour dossier")."</h2>";
/**
 * Update or install MONO
 */
if  (defined("MULTI") && MULTI == 0)
{
	$db = new Database();
	if ($db->exist_table("repo_version") == false) 
	{
            if ( ! DEBUG) { ob_start();  }
            $db->execute_script(NOALYSS_INCLUDE.'/sql/mono/mono.sql');
                     
            if ( ! DEBUG) ob_end_clean();
	}
       
        
        if ($db->exist_table("version") == false)
	{
		echo '<p class="warning">' . $failed ;
                printf (_('La base de donnée %s est vide, 
                    veuillez vous y connecter
                    avec phpPgAdmin ou pgAdmin3 ou en commande en ligne
                    puis faites un seul de ces choix : '),dbname);
                echo '<ul>';
                echo '<li>'._("soit noalyss/contrib/mono-dossier/mono-france.sql pour la comptabilité française").'</li>';
                echo '<li>'._("soit noalyss/contrib/mono-dossier/mono-belge.sql pour la comptabilité belge").'</li>';
                echo '<li>'._("soit y restaurer un backup ou un modèle")."</li>
                    </ul>
				</p>";
		exit();
	}
	echo "<h3>Patching " . dbname . '</h3>';
	$db->apply_patch(dbname);
	echo "<p class=\"info\">"._("Tout est install&eacute;"). $succeed;
        
         echo "<h2>"._("Mise à jour Repository")."</h2>";
         if ( DEBUG == false ) ob_start();
        $MaxVersion=DBVERSIONREPO-1;
        for ($i=4;$i<= $MaxVersion;$i++)
        {
            if ( $db->get_value (' select val from repo_version') <= $i ) {
                $db->execute_script(NOALYSS_INCLUDE.'/sql/patch/ac-upgrade'.$i.'.sql');
            }
        }
        
        $db->exec_sql("update ac_users set use_login=$1 where use_id=1",
              array(strtolower(NOALYSS_ADMINISTRATOR)));
        echo '<h1>'._('Important').'</h1>';
        echo '<p>'._('Utilisateur administrateur'),' ',NOALYSS_ADMINISTRATOR,'</p>';
        echo '<p>',_('Mot de passe par défaut à l\'installation'),' phpcompta','</p>';
        echo "<h2 class=\"warning\">";
        printf (" VOUS DEVEZ EFFACER CE FICHIER %s",__FILE__);
        echo "</h2>";
	?>
<p style="text-align: center">
		<A style="" class="button" HREF="./index.php"><?php echo _('Connectez-vous à NOALYSS')?></A>
                </p>
	<?php
	exit();
}

/*
 * If multi folders
 */
define ('ALLOWED',1);
$_GET['sb']="upg_all";
$rep=new Database();
if (defined (NOALYSS_ADMINISTRATOR) )
        $rep->exec_sql("update ac_users set use_login=$1 where use_id=1",
              array(strtolower(NOALYSS_ADMINISTRATOR)));
require NOALYSS_INCLUDE."/upgrade.inc.php";
echo '<h1>'._('Important').'</h1>';
echo '<p>'._('Utilisateur administrateur'),' ',NOALYSS_ADMINISTRATOR,'</p>';
        
echo "<h2 class=\"warning\">";
printf (" VOUS DEVEZ EFFACER CE FICHIER %s",__FILE__);
echo "</h2>";

 echo "<p class=\"info\">"._("Tout est install&eacute;")." ". $succeed;
?>
</p>
<p style="text-align: center">
<A style="display:inline;margin:10px;padding:10px;" class="button" HREF="index.php"><?php echo _('Connectez-vous à NOALYSS')?></A>
</p>