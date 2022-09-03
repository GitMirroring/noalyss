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

/*!\file
 * \brief respond ajax request, the get contains
 *  the value :
 * - l for ledger
 * - gDossier
 * Must return 1 if quantity is visible or 0 if not
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
global $http;
$cn=Dossier::connect();
$ledger=new Acc_Ledger($cn,$http->get("l","number",0));
$ledger->load();
if ($ledger->id==0) throw new Exception("UPDVIS34.Invalide ledger");
echo $ledger->has_quantity();