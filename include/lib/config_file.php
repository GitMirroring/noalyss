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
 \file
 * \brief functions concerning the config file config.inc.php. The domain is not set into the form for security issues
 */


function is_unix()
{
    $inc_path=get_include_path();

    if ( strpos($inc_path,";") != 0 )
    {
        $os=0;			/* $os is 0 for windoz */
    }
    else
    {
        $os=1;			/* $os is 1 for unix */
    }
    return $os;
}


/*
 * !\brief
 *\param array with the index
 *  - ctmp temporary folder
 *  - cpath path to postgresql
 *  - cuser postgresql user
 *  - cpasswd password of cuser
 *  - cport port for postgres
 *\return string with html code
 */
function config_file_form($p_array=null)
{
	$os=is_unix();
    if ( $p_array == null )
    {

        /* default value */
        $ctmp=($os==1)?'/tmp':'c:/tmp';
        $cpath=($os==1)?'/usr/bin':'c:/noalyss/postgresql/bin';
        $cuser='noalyss_sql';
        $cpasswd='dany';
        $cport=5432;
        $cdomain='';
        $clocale=1;
	$multi="N";
	$cdbname="";
        $chost="localhost";
        $cadmin='admin';
        $cpassword_admin="";

    }
    else extract ($p_array, EXTR_SKIP);

    $ictmp=new IText('ctmp',$ctmp);
    $ictmp->size=25;

    $iclocale=new ISelect('clocale');
    $iclocale->value=array(
            array("value"=>1,"label"=>"Activé"),
            array("value"=>0,"label"=>"Désactivé")
    );
    $iclocale->selected=1;

    $icpath=new IText("cpath",$cpath);
    $icpath->size=30;
    
    $icuser=new IText('cuser',$cuser);
    $icpasswd=new IText('cpasswd',$cpasswd);
    $icport=new IText("cport",$cport);
    $ichost=new IText("chost",$chost);
    
    $icadmin=new IText('cadmin',$cadmin);
    $icadmin->set_require(true);
    /*
     * For version MONO
     */
    $smulti=new ICheckBox('multi');
    $smulti->javascript=' onchange="show_dbname(this)" ';
    $smulti->value = 'Y';
    if ( isset($multi) && $multi == 'Y') {
        $smulti->selected=true;

    }
    $icdbname=new IText('cdbname');
    $icdbname->value=$cdbname;
    
    $icpassword_admin=new IText('cpassword_admin');
    $icpassword_admin->value=$cpassword_admin;
    $icpassword_admin->set_require(true);
    
    require NOALYSS_TEMPLATE.'/template_config_form.php';
}
/**
 * @brief Display the  content of the config.inc.php with variables
 * @param type $p_array
 * @param type $from_setup
 * @param type $p_os
 */
function display_file_config($p_array, $from_setup = 1, $p_os = 1)
{
    extract($p_array, EXTR_SKIP);
    print ('<?php ');
    echo PHP_EOL;
    print ('date_default_timezone_set (\'Europe/Brussels\');');
    echo PHP_EOL;
    print ("\$_ENV['TMP']='" . $ctmp . "';");
    echo PHP_EOL;
    print ('define("PG_PATH","' . $cpath . '");');
    echo PHP_EOL;
    if ($p_os == 1) {
        print ('define("PG_RESTORE","' . $cpath . DIRECTORY_SEPARATOR . 'pg_restore ");');
        echo PHP_EOL;
        print ('define("PG_DUMP","' . $cpath . DIRECTORY_SEPARATOR . 'pg_dump ");');
        echo PHP_EOL;
        print ('define ("PSQL","' . $cpath . DIRECTORY_SEPARATOR . 'psql");');
    } else {
        print ('define("PG_RESTORE","pg_restore.exe");');
        echo PHP_EOL;
        print ('define("PG_DUMP","pg_dump.exe");');
        echo PHP_EOL;
        print ('define ("PSQL","psql.exe");');
    }
    echo PHP_EOL;
    print ('define ("noalyss_user","' . $cuser . '");');
    echo PHP_EOL;
    print ('define ("noalyss_password","' . $cpasswd . '");');
    echo PHP_EOL;
    print ('define ("noalyss_psql_port","' . $cport . '");');
    echo PHP_EOL;
    print ('define ("noalyss_psql_host","' . $chost . '");');
    echo PHP_EOL;
    echo PHP_EOL;
    print ("// If you change the NOALYSS_ADMINISTRATOR , you will need to rerun http://..../noalyss/html/install.php");
    echo PHP_EOL;
    print ("// But it doesn't change the password");
    echo PHP_EOL;
    print ('define ("NOALYSS_ADMINISTRATOR","' . $cadmin . '");');
    echo PHP_EOL;
    print ("// For changing the password of admin, go to preference or update in db");
    echo PHP_EOL;
    print ("// this password is only used when executing install.php ");
    echo PHP_EOL;
    print ('define ("NOALYSS_ADMIN_PASSWORD","' . $cpassword_admin . '");');
    echo PHP_EOL;
    echo "
        /*
         *  LOCALE 0 means no language change , on some PHP installation
         * you cannot change the language 
         */
         ";
    echo PHP_EOL;
    print ('define ("LOCALE",' . $clocale . ');');
    echo PHP_EOL;
    echo "
    /* 
     * DEBUGNOALYSS let you see more information when you develop.
     * 0 = for production
     * 1 = display all errors
     * 2 = display all errors + more information 
     */
";

    echo PHP_EOL;
    print ('define ("DEBUGNOALYSS",0);');
    echo PHP_EOL;

    print ('define ("domaine","");');
    echo PHP_EOL;
    if (isset($multi)) {
        print ('define ("MULTI",0);');
    }
    if (!isset($multi)) {
        print ('define ("MULTI",1);');
    }
    echo PHP_EOL;
    print ('define ("dbname","' . $cdbname . '");');
    echo PHP_EOL;

    print (' // Uncomment to log your input');
    echo PHP_EOL;
    print ('// define ("LOGINPUT",TRUE);');
    echo PHP_EOL;
    echo PHP_EOL;
    echo PHP_EOL;
    print (' // Do not change below !!!');
    echo PHP_EOL;
    print (' // These variable are computed but could be changed in ');
    echo PHP_EOL;
    print (' // very special configuration');
    echo PHP_EOL;
    print ('// define ("NOALYSS_HOME","");');
    echo PHP_EOL;
    print ('// define ("NOALYSS_PLUGIN","");');
    echo PHP_EOL;
    print ('// define ("NOALYSS_INCLUDE","");');
    echo PHP_EOL;
    print ('// define ("NOALYSS_TEMPLATE","");');
    echo PHP_EOL;
    print ('// define ("NOALYSS_INCLUDE","");');
    echo PHP_EOL;
    print ('// define ("NOALYSS_TEMPLATE","");');
    echo PHP_EOL;
    print ("// Fix an issue with PDF when exporting receipt in PDF in ANCGL");
    print ('// define ("FIX_BROKEN_PDF","NO");');
    echo PHP_EOL;
    print ("// Uncomment if you want to convert to PDF");
    echo PHP_EOL;
    print ("// With the unoconv tool");
    echo PHP_EOL;
    print ("//define ('OFFICE','HOME=/tmp unoconv ');");
    echo PHP_EOL;
    print ("//define ('GENERATE_PDF','YES');");
    echo PHP_EOL;
    print ("// Uncomment if you don't want ");
    echo PHP_EOL;
    print ("// to be informed when a new release is ");
    echo PHP_EOL;
    print ("// published");
    echo PHP_EOL;
    print ('// define ("SITE_UPDATE","");');
    echo PHP_EOL;
    print ('// define ("SITE_UPDATE_PLUGIN","");');
    echo PHP_EOL;
    print ('// To allow to access the Info system');
    echo PHP_EOL;
    print ('// define ("SYSINFO_DISPLAY",true);');
    echo PHP_EOL;
    print ('// For developpement');
    echo PHP_EOL;
    print ('// define ("NOALYSS VERSION",9999);');
    echo PHP_EOL;
    print (' // If you want to override the parameters you have to define OVERRIDE_PARAM');
    echo PHP_EOL;
    print ('// and give your own parameters for max_execution_time and memory_limit');
    echo PHP_EOL;
    print ("// define ('OVERRIDE_PARAM',1);");
    echo PHP_EOL;
    print ("// ini_set ('max_execution_time',240);");
    echo PHP_EOL;
    print ("// ini_set ('memory_limit','256M');");
    echo PHP_EOL;
    print ("// In recent distribution linux, pdftk is a snap, you should set the path");
    echo PHP_EOL;
    print ("// for exporting document in PDF");
    echo PHP_EOL;
    print ("// \$pdftk = /usr/bin/pdftk ");
    echo PHP_EOL;
    print ("// \$pdftk = /snap/bin/pdftk ");
    echo PHP_EOL;
    print ("// uncomment to activate the captcha on login page");
    echo PHP_EOL;
    print ("// define('NOALYSS_CAPTCHA',true);");
    echo PHP_EOL;
    print ("// Uncomment if you want to activate the possibility to reinitialize;");
    echo PHP_EOL;
    print ("// password by email");
    echo PHP_EOL;
    print ("// define ('RECOVER','1');");
    echo PHP_EOL;
    print ("// Uncomment and define if you want to Name of the sender of the email ");
    echo PHP_EOL;
    print ("// if you activate the possibility to reinitialize password by email");
    echo PHP_EOL;
    print ("// define('ADMIN_WEB', 'www-data@localhost');");
    echo PHP_EOL;
    print ("// Define a random session key if you work with different version of NOALYSS");
    echo PHP_EOL;
    printf("define ('SESSION_KEY','%s');", generate_random_string(10));
    echo PHP_EOL;
    printf("// When sending an email, check if you can send with that domain, "
            . PHP_EOL
            . "// the domain of this email must be in comma separated list ,");
    echo PHP_EOL;
    print ("// if the list is an empty string then all the domain are allowed");
    echo PHP_EOL;
    printf("// define ('ALLOWED_EMAIL_DOMAIN','');");
    echo PHP_EOL;
    $a=PHP_EOL;
    echo <<<EOF
////////$a
// change NOALYSS_URL with your URL , $a
// with NGINX it is needed to set the NOALYSS_URL , it is the default URL of $a
// your installation, example with https://demo.noalyss.eu $a
// define ("NOALYSS_URL","https://demo.noalyss.eu"); $a
EOF;
}
/*!
 * \brief create the config file
 */
function config_file_create($p_array,$from_setup,$p_os=1)
{
    extract ($p_array, EXTR_SKIP);
    $hFile=  fopen(NOALYSS_INCLUDE.'/config.inc.php','w');
    ob_start();
    display_file_config($p_array,$from_setup,$p_os);
    $r=ob_get_clean();
    fputs($hFile, $r);
    fclose($hFile);
}
?>
