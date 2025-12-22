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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 13/05/24
/*! 
 * \file
 * \brief check the VAT Number with VIES,
 * \see ivatnumber.class.php
 */

if (!defined('ALLOWED')) die('Appel direct ne sont pas permis');

$http = new \HttpInput();

try {
    $vatnr = $http->get("vatnr");
    $p_domid = $http->get("p_domid");
} catch (\Exception $e) {
    echo $e->getMessage();
    record_log($e);
    return;
}
$vatnr = str_replace([" ",".","-"],"",strtoupper($vatnr??""));
$country = substr($vatnr, 0, 2);
$vatnr = preg_replace("/[[:^digit:]]/", '', $vatnr);
$array = array(
    "countryCode" => $country,
    "vatNumber" => $vatnr,
    "requesterMemberStateCode" => "",
    "requesterNumber" => "",
    "traderName" => "",
    "traderStreet" => "",
    "traderPostalCode" => "",
    "traderCity" => "",
    "traderCompanyType" => ""
);

$curl = curl_init(VATCHECK_URL . "/check-vat-number");
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "accept: application/json"
    )
);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($array));
$json = curl_exec($curl);
curl_close($curl);
if (empty($json)) {
    $obj = new stdClass;
    $obj->status = 'NOK';
    $obj->html=_('Erreur');
    echo json_response($obj);
    return;
}


$obj = json_decode($json);
if (isset ($obj->actionSucceed) && $obj->actionSucceed == false)
{
    $obj = new stdClass;
    $obj->status = 'NOK';
    $obj->html=_('Erreur');
    echo json_response($obj);
    return;
}

if ($obj->valid == true) {
    $obj->status = 'OK';
    $obj->vat = $country . $vatnr;
    $obj->name = str_replace("\n", ' ', $obj->name ?? "");
    $obj->address = str_replace("\n", ' ', $obj->address ?? "");

    $obj->html = <<<EOF
{$obj->name}  {$obj->address}
EOF;

} else {
    $obj->status = 'NOK';
    $obj->html=_('non valide');
}

echo json_response($obj);
?>