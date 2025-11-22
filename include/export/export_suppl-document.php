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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 22/10/23


/**
 * @file
 * @brief download document
 */
if ( ! defined ('ALLOWED')) die (_('Non autorisé'));

include_once NOALYSS_INCLUDE.'/lib/ac_common.php';
$http=new HttpInput();

try
{
    $js_id=$http->get('js_id',"number");
    $operation_id=$http->get("operation_id",'number',0);
}
catch (Exception $exc)
{
    record_log($exc);
    return;
}
$cn=Dossier::connect();

//------------------------------------------------
// Download only one document
//------------------------------------------------
if ( $js_id > 0)
{
    $jrn_def_id=$cn->get_value("select jr_def_id from jrn_sup_document  join jrn using (jr_id) where js_id=$1",array($js_id));

    global $g_user;
    if ($jrn_def_id =="" || $g_user->check_jrn($jrn_def_id) == 'X' )
    {
        /* Cannot Access */
        NoAccess();
        exit -1;
    }

    $jrn=new Jrn_Sup_Document_SQL($cn,$js_id);
    $cn->start();
    ini_set('zlib.output_compression','Off');
    header("Pragma: public");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: must-revalidate");
    header('Content-type: '.$jrn->js_mimetype);
    header('Content-Disposition: attachment;filename="'.$jrn->js_filename.'"',FALSE);
    header("Accept-Ranges: bytes");
    echo $cn->lo_read($jrn->js_lob);
    $cn->commit();
    return;
}
//------------------------------------------------
// Download all documents
//------------------------------------------------
if ( $operation_id > 0)
{
    $jrn_def_id=$cn->get_value("select jr_def_id from jrn where jr_id=$1",array($operation_id));

    global $g_user;
    if ($jrn_def_id =="" || $g_user->check_jrn($jrn_def_id) == 'X' )
    {
        /* Cannot Access */
        NoAccess();
        exit -1;
    }
    $jr_internal=$cn->get_value("select jr_internal from jrn where jr_id=$1",array($operation_id));

    try {
        $cn->start();
        // find all the documents for this operation
        $a_file=$cn->get_array("select js_lob,js_filename from jrn_sup_document where jr_id=$1",
                [$operation_id]);
        // save them into a temp folder
        if ( count ($a_file) == 0 ) {
            // @TODO send an empty file
            return;
        }
        $a_file_dwn=array();
        $store = tempnam($_ENV['TMP'], 'pdf_');
        unlink($store);
        mkdir($store);
        $nb_file=count($a_file);
        for($i=0;$i < $nb_file;$i++)
        {
            $filename=sprintf("%s".DIRECTORY_SEPARATOR."%s",
                    $store,
                    $a_file[$i]['js_filename']);
            
            // avoid to overwrite a previous file with same name
            if (file_exists($filename)) {
                $dup=1;
                do {
                    $filename=sprintf("%s".DIRECTORY_SEPARATOR."%s-%s",
                        $store,
                        $dup,
                        $a_file[$i]['js_filename']);
                    $dup++;
                }while ( file_exists($filename));
                
            }
            $cn->lo_export($a_file[$i]['js_lob'], $filename);
            $a_file_dwn[]=$filename;
            
        }
        $cn->commit();
        // zip the folder
        $zip = new Zip_Extended();
        $zip_file=sprintf("%s".DIRECTORY_SEPARATOR."%s.zip"
                ,$store
                ,$jr_internal);
        
        chdir($store);
        $res=$zip->open($zip_file, ZipArchive::CREATE);
        foreach ($a_file_dwn as $item)
        {
            $zip->addFile(basename($item));
        }
        $zip->close();
        // send the folder to browser
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="document-'.$jr_internal.'.zip"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        echo file_get_contents($zip_file);
        
    } catch (Exception $exc) {
        record_log($exc);
    }
    
}