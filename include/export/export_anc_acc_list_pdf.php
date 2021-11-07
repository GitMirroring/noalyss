<?php

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
// Copyright (2002-2021) Author Dany De Bontridder <danydb@noalyss.eu>

if (!defined('ALLOWED'))     die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief export ANCBCC in PDF
 */

$bal=new Anc_Acc_List($cn);
$bal->get_request();
$pdf=new PDF_Anc_Acc_List($bal);
$fDate=date('dmy-Hi');

$pdf->export_pdf()->Output( "balance-acc-anc-$fDate.pdf", 'D');
