<?php

/*
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
 * 
 * Author : Dany De Bontridder danydb@noalyss.eu
 * Copyright (C) 2025 Dany De Bontridder <dany@alchimerys.be>
 * 
 */

/**
 * @file
 * @brief noalyss
 */

use PHPUnit\Framework\TestCase;

require DIRTEST . '/global.php';

/**
 * @testdox test http input
 * @coversDefaultClass
 */
class Http_InputTest extends TestCase {

   

    /**
     * @brief Data simulation

     * @return array
     */
    function dataHttpInput() {
        return array(
            [ ['value'=>'<script'], '<.script']
            , [['value'=>'Test <script'], 'Test <.script']
            , [['value'=>'<Script>'], '<.Script>']
            , [['value'=>6], 6]
            , [['value'=>'<iframe src'], '<.iframe src']
            , [['value'=>'Test <IFRAME src'], 'Test <.IFRAME src']
        );
    }

    

    /**
     * @testdox Check the removal of bad tags
     * @covers security
     * @dataProvider dataHttpInput
     * @global $g_connection
     */
    function testRemoveHarmingString($array,$result) {
        $http=new \HttpInput();
//        print "value = ".print_r($value,true);
        $http->set_array($array);
        $this->assertEquals($result,$http->get_value('value','string')
                ," can get [$result] from ".print_r($array,true)."\n");
        
    }
}
