<?php

/*
 * * Copyright (C) 2022 Dany De Bontridder <dany@alchimerys.be>
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
 * Author : Dany De Bontridder danydb@noalyss.eu $(DATE)
 */

/**
 * @file
 * @brief noalyss
 */

use PHPUnit\Framework\TestCase;

require DIRTEST . '/global.php';

/**
 * @testdox Class Package_RepositoryTest : used for ...
 * @backupGlobals enabled
 * @coversDefaultClass
 */
class File_CacheTest extends TestCase
{

    /**
     * @testdox test the content and cache it
     * @covers      \Noalyss\File_Cache::get_content
     */
    function testGet_content()
    {
        $web_xml = $_ENV['TMP'] . "/testcache.xml";
        if ( file_exists($web_xml) )        unlink($web_xml);

        \Noalyss\File_Cache::get_content(NOALYSS_PACKAGE_REPOSITORY."/web.xml",$web_xml,3);

        $this->assertTrue(file_exists($web_xml),"File not loaded");
        $time = filemtime($web_xml);
        sleep(4);
        clearstatcache(true, $web_xml);
        \Noalyss\File_Cache::get_content(NOALYSS_PACKAGE_REPOSITORY."/web.xml",$web_xml,30);
        $time2 = filemtime($web_xml);
        $this->assertEquals($time, $time2, " testcache.xml not cached");

        \Noalyss\File_Cache::get_content(NOALYSS_PACKAGE_REPOSITORY."/web.xml",$web_xml,3);
        sleep(6);
        clearstatcache(true, $web_xml);
        $time3 = filemtime($web_xml);
        $this->assertNotEquals($time, $time3, " testcache.xml not refreshed");
    }


}