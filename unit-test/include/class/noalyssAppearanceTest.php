<?php
/*
 *   This file is part of NOALYSS.
 *    NOALYSS is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    NOALYSS is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with NOALYSS; if not, write to the Free Software
 *    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *    Copyright Author Dany De Bontridder danydb@noalyss.eu
 *
 */

use PHPUnit\Framework\TestCase;

class noalyssAppearanceTest extends TestCase
{
    protected $object;
    protected function setUp(): void
    {
        include 'global.php';
        $this->object=new Noalyss_Appearance();
    }

    public function testSetColor()
    {
        $this->object->set_color("FOLDER","#554433");
        $this->assertEquals("#554433",$this->object->get_color("FOLDER"));

    }

    /**
     * @covers Noalyss_Appearance::set_color Noalyss_Apperance::save  Noalyss_Apperance::reset  Noalyss_Apperance::get_color
     */
    public function testReset()
    {
        $this->object->set_color("FOLDER","#554433");
        $this->assertEquals("#554433",$this->object->get_color("FOLDER"));
        $this->object->save();
        $this->object->load();
        $this->assertEquals("#554433",$this->object->get_color("FOLDER"));
        $this->object->reset();
        $this->assertEquals("#ffffff",$this->object->get_color("FOLDER"));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testValidateColor()
    {

        $this->object->set_color("FOLDER","#554411");
        $this->expectException(Exception::class );
        $this->object->set_color("FOLDER","#white");
    }


}
