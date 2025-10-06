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
 * @brief export the XML invoice from JRN.JR_DOCUMENT_XML
 */
if ( ! defined ('ALLOWED')) die (_('Non autorisé'));

$http=new HttpInput();

try
{
    $jr_id=$http->get('jr_id',"number");
}
catch (Exception $exc)
{
    record_log($exc);
    return;
}

$cn=Dossier::connect();

$r=$cn->exec_sql("select jr_def_id from jrn where jr_id=$1",array($jr_id));

if ( Database::num_row($r) == 0 )
{
    echo_error("Invalid operation id jr_id=$jr_id");
    exit;
}
$a=Database::fetch_array($r,0);
$jrn=$a['jr_def_id'];
global $g_user;
if ($g_user->check_jrn($jrn) == 'X' )
{
    /* Cannot Access */
    NoAccess();
    exit -1;
}

$ret=$cn->exec_sql("select jr_pj_name,jr_pj_number ,jr_document_xml from jrn where jr_id=$1",
        array($jr_id));

if ( Database::num_row ($ret) == 0 )
    return;

$row=Database::fetch_array($ret,0);

if ( $row['jr_document_xml']==null )
{
    ini_set('zlib.output_compression','Off');
    header("Pragma: public");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: must-revalidate");
    header('Content-type: '.'text/plain');
    header('Content-Disposition: attachment;filename=vide.txt',FALSE);
    header("Accept-Ranges: bytes");
    echo "******************";
    echo _("Fichier effacé");
    echo "******************";
    exit();
}
$tmp=tempnam($_ENV['TMP'],'document_');

$new_name=$row['jr_pj_name'];
$receipt_number=clean_filename($row['jr_pj_number']);
$receipt_number=noalyss_str_replace('.','-',$receipt_number);
if ( ! empty($receipt_number) && strpos($new_name,$receipt_number) === false ) {

    $new_name=$receipt_number.'-'.$new_name;
}
// replace extension by xml (normally a PDF)
//@var $pos_ext (int) where is the last dot
$pos_ext=strrpos($new_name,'.');
if ( $pos_ext == 0) 
{ 
    // there is no extension
    $new_name.='.xml';
}else {
     $new_name=substr_replace($new_name,'.xml',$pos_ext);
}
$cn->start();

$cn->lo_export($row['jr_document_xml'],$tmp);
$cn->commit();

ini_set('zlib.output_compression','Off');
header("Pragma: public");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: must-revalidate");
header('Content-type: application/xml');
header('Content-Disposition: attachment;filename="'.$new_name.'"',FALSE);
header("Accept-Ranges: bytes");

$file=fopen($tmp,'r');
while ( !feof ($file) )
    echo fread($file,8192);

fclose($file);

unlink ($tmp);
