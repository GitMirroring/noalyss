<?php
/***
 * @file
 * @brief output a PDF with card info
 *
 */
try {
    $http=new \HttpInput();
    $card_id=$http->get("card_id");
} catch (\Exception $e) {
    echo $e->getMessage();
    die();
}

$card_pdf=new \Card_PDF($card_id);
$card_pdf->export();