<?php

namespace Noalyss;

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
// Copyright (2002-2019) Author Dany De Bontridder <danydb@noalyss.eu>

/**
 * @file
 * @brief Set of function to use with phpunit , to save files and to visualize them later, to use tracefile...
 */
class Facility
{

    /**
     * include code for HTML to generate the display
     */
    static function page_start()
    {
        $noalyss_home=NOALYSS_HOME;
        $ret=<<<EOF
    <!doctype html>
<HTML><HEAD><meta charset="utf-8"><META http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <TITLE>PRINTJRN  NOALYSS</TITLE>
	<link rel="icon" type="image/ico" href="favicon.ico" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <LINK id="pagestyle" REL="stylesheet" type="text/css" href="{$noalyss_home}/style-classic7.css?version=7105" media="screen"/>
    <link rel="stylesheet" type="text/css" href="./style-print.css?version=7105" media="print"/>
    <script language="javascript" src="js/calendar.js"></script>
    <script type="text/javascript" src="js/lang/calendar-en.js"></script>
    <script language="javascript" src="js/calendar-setup.js"></script>
    <LINK REL="stylesheet" type="text/css" href="calendar-blue.css" media="screen">
    </HEAD>
    <body>
    <div class="content">
EOF;
        return $ret;
    }

    /**
     * Create a file with the content
     * @param string $p_dir usually __DIR__
     * @param string $p_file name of the file
     * @param string $p_content content
     */
    static function save_file($p_dir, $p_file, $p_content)
    {
        printf (" saving %s/%s please check this file \n ",$p_dir,$p_file);
        $hFile=fopen($p_dir."/".$p_file, "w+");
        fwrite($hFile, $p_content);
        fclose($hFile);
    }

}
