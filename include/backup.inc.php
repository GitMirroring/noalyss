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
/*!\file
 * \brief Make and restore backup
 */
if ( !defined("ALLOWED")) { die (_("Non autorisé")); }
 require_once NOALYSS_INCLUDE.'/lib/class_http_input.php';

 $http=new HttpInput();
 
// Copyright Author Dany De Bontridder danydb@aevalys.eu
 try {
    $dossier_number=$http->request("d", "number");
 } catch (Exception $e){
    echo span(_("Dossier invalide")," class=\"error\" ");
     exit();
 }
if ( isset ($_REQUEST['sa']) )
{
    if ( defined ('PG_PATH') )
        putenv("PATH=".PG_PATH);


    if ( ! isset($_REQUEST['t']))
    {
        echo _("Erreur : paramètre manquant ");
        exit();
    }

    $sa=$http->request("sa");
    // backup
    if ( $sa=='b')
    {
        $cmd=escapeshellcmd (PG_DUMP);
        if ( defined ("noalyss_user"))
        {
            putenv("PGPASSWORD=".noalyss_password);
            putenv("PGUSER=".noalyss_user);
            putenv("PGHOST=".noalyss_psql_host);
            putenv("PGPORT=".noalyss_psql_port);
        }else if (defined ("phpcompta_user"))
        {
            putenv("PGPASSWORD=".phpcompta_password);
            putenv("PGUSER=".phpcompta_user);
            putenv("PGHOST=".phpcompta_psql_host);
            putenv("PGPORT=".phpcompta_psql_port);
        } else  {
        die ('Aucune connection');
        }
        
        if ( $_REQUEST['t'] == 'd' )
        {
            $database=domaine."dossier".$dossier_number;
            $args= " -Fc -Z9 --no-owner -h ".getenv("PGHOST")." -p ".getenv("PGPORT")." ".$database;
            header('Content-type: application/octet');
            header('Content-Disposition:attachment;filename="'.$database.'.bin"',FALSE);

            passthru ($cmd.$args,$a);

        }

        if ( $_REQUEST['t'] == 'm' )
        {
            $database=domaine."mod".$dossier_number;
            $args= " -Fc -Z9 --no-owner -h ".getenv("PGHOST")." -p ".getenv("PGPORT")." ".$database;
            header('Content-type: bin/x-application');
            header('Content-Disposition: attachment;filename="'.$database.'.bin"',FALSE);
            $a=passthru ($cmd.$args);
        }
    }
}

