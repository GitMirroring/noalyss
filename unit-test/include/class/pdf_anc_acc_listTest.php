<?php

/*
 * * Copyright (C) 2021 Dany De Bontridder <dany@alchimerys.be>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

 * 
 * Author : Dany De Bontridder danydb@noalyss.eu
 * 
 */

/**
 * @file
 * @brief 
 */
use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals enabled
 */
require DIRTEST.'/global.php';

class PDF_Anc_Acc_ListTest extends TestCase
{

    /**
     * @var Fiche
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test method is executed.
     */
    protected function setUp():void
    {
        include 'global.php';
        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test method is executed.
     */
    protected function tearDown():void
    {
        
    }

    /**
     * the setUpBeforeClass():void template methods is called before the first test of the test case
     *  class is run 
     */
    public static function setUpBeforeClass():void
    {
        include 'global.php';
        // insert data into operation_analytique
        $sql=<<<EOF
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(85, 2, 20.0000, 'Vente Service', false, 444, 887, '2019-01-02', 0, NULL, 'Y', NULL);
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(86, 3, 35.2000, 'Vente Service', false, 444, 887, '2019-01-02', 1, NULL, 'Y', NULL);
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(87, 1, 95.3600, 'Vente Service', false, 444, 887, '2019-01-02', 2, NULL, 'Y', NULL);
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(88, 4, 85.0000, 'Note Electricité janvier', true, 336, 888, '2019-01-02', 0, NULL, 'Y', NULL);
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(89, 1, 79.2500, 'Note Electricité janvier', true, 336, 888, '2019-01-02', 1, NULL, 'Y', NULL);
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(90, 2, 164.2500, 'Note Electricité janvier', true, 342, 889, '2019-03-02', 0, NULL, 'Y', NULL);
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(91, 1, 164.2500, 'Note Electricité janvier', true, 347, 890, '2019-04-02', 0, NULL, 'Y', NULL);
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(92, 2, 169.1800, 'Electricité', true, 352, 891, '2019-05-02', 0, NULL, 'Y', NULL);
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(93, 4, 50.0000, 'Frais de formation', true, 408, 892, '2019-07-02', 0, NULL, 'Y', NULL);
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(94, 1, 50.0000, 'Frais de formation', true, 408, 892, '2019-07-02', 1, NULL, 'Y', NULL);
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(95, 4, 750.0000, 'Frais de formation', true, 409, 893, '2019-07-02', 0, NULL, 'Y', NULL);
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(96, 1, 770.2000, 'Frais de formation', true, 409, 893, '2019-07-02', 1, NULL, 'Y', NULL);
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(97, 2, 100.0000, 'Frais de formation', true, 410, 894, '2019-07-02', 0, NULL, 'Y', NULL);
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(98, 4, 259.8000, 'Frais de formation', true, 411, 895, '2019-07-02', 0, NULL, 'Y', NULL);
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(99, 3, 1250.0000, 'Frais de formation', true, 412, 896, '2019-07-02', 0, NULL, 'Y', NULL);
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(100, 4, 183.0000, 'Eau', false, 380, 897, '2019-04-17', 0, NULL, 'N', NULL);
INSERT INTO public.operation_analytique (oa_id, po_id, oa_amount, oa_description, oa_debit, j_id, oa_group, oa_date, oa_row, oa_jrnx_id_source, oa_positive, f_id) VALUES(101, 2, 366.0000, 'Eau', true, 380, 897, '2019-04-17', 1, NULL, 'Y', NULL);

EOF;
        global $g_connection;
        $g_connection->exec_sql($sql);
    }

    /**
     *  tearDownAfterClass():void template methods is calleafter the last test of the test case class is run,
     *
     */
    static function tearDownAfterClass():void
    {
             include 'global.php';
             global $g_connection;
             $g_connection->exec_sql("delete from operation_analytique");
    }


    /**
     * @brief 
     * @testdox Export to PDF
     * @param type $p_param
     * @covers
     */
    public function testPdf_Card()
    {
        global $g_connection;
        
        // By card
        
        $anc_acc_list=new Anc_Acc_List($g_connection);
        $anc_acc_list->to='31.12.2019';
        $anc_acc_list->from='01.01.2019';
        $anc_acc_list->pa_id=1;
        $anc_acc_list->card_poste=1;
        $pdf_anc_acc=new PDF_Anc_Acc_List($anc_acc_list);
        $pdf_anc_acc->export_pdf()->Output(__DIR__."/file/pdf_anc_acc_list-card-activity.pdf","F");
        $filesize=filesize(__DIR__."/file/pdf_anc_acc_list-card-activity.pdf");
        $this->assertTrue($filesize==76920||$filesize==79619,
                __DIR__."/file/pdf_anc_acc_list-card-activity.pdf incorrect");
        
        // By Account / Activity
        $anc_acc_list=new Anc_Acc_List($g_connection);
        $anc_acc_list->to='31.12.2019';
        $anc_acc_list->from='01.01.2019';
        $anc_acc_list->pa_id=1;
        $anc_acc_list->card_poste=2;
        $pdf_anc_acc=new PDF_Anc_Acc_List($anc_acc_list);
        $pdf_anc_acc->export_pdf()->Output(__DIR__."/file/pdf_anc_acc_list-account-activity.pdf","F");
        $this->assertGreaterThan(77149,filesize(__DIR__."/file/pdf_anc_acc_list-account-activity.pdf"),
               __DIR__."/file/pdf_anc_acc_list-account-activity.pdf incorrect ");
        
        // By Activity / Card
        $anc_acc_list=new Anc_Acc_List($g_connection);
        $anc_acc_list->to='31.12.2019';
        $anc_acc_list->from='01.01.2019';
        $anc_acc_list->pa_id=1;
        $anc_acc_list->card_poste=3;
        $pdf_anc_acc=new PDF_Anc_Acc_List($anc_acc_list);
        $pdf_anc_acc->export_pdf()->Output(__DIR__."/file/pdf_anc_acc_list-activity-card.pdf","F");
        $this->assertGreaterThan(77600,filesize(__DIR__."/file/pdf_anc_acc_list-activity-card.pdf"),
                __DIR__."/file/pdf_anc_acc_list-activity-card.pdf incorrect");
        
          // By Activity / Account
         $anc_acc_list=new Anc_Acc_List($g_connection);
        $anc_acc_list->to='31.12.2019';
        $anc_acc_list->from='01.01.2019';
        $anc_acc_list->pa_id=1;
        $anc_acc_list->card_poste=4;
        $pdf_anc_acc=new PDF_Anc_Acc_List($anc_acc_list);
        $pdf_anc_acc->export_pdf()->Output(__DIR__."/file/pdf_anc_acc_list-activity-account.pdf","F");
        $this->assertGreaterThan(76400,filesize(__DIR__."/file/pdf_anc_acc_list-activity-account.pdf"),
                __DIR__."/file/pdf_anc_acc_list-activity-account.pdf incorrect");
    }

}
