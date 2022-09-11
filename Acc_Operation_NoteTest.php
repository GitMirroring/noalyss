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

class Acc_Operation_NoteTest extends TestCase
{

    public function testBuild_jrn_id()
    {
        $note=Acc_Operation_Note::build_jrn_id(165);
        $this->assertEquals(7,$note->getJrnNoteSql()->getp("n_id"),' cannot retrieve a note');

        $note=Acc_Operation_Note::build_jrn_id(686);
        $this->assertEquals(14,$note->getJrnNoteSql()->getp("n_id"),' cannot retrieve a note');

        $note=Acc_Operation_Note::build_jrn_id(1000);
        $this->assertEquals(-1,$note->getJrnNoteSql()->getp("n_id"),' cannot retrieve a note');
    }
    public function testSave()
    {
        $cn=Dossier::connect();
        $cn->exec_sql("delete from jrn_note where jr_id=251");
        // Add a new note
        $note=Acc_Operation_Note::build_jrn_id(251);
        $this->assertEquals(-1,$note->getJrnNoteSql()->getp("n_id"),"note already exist");

        $note->setNote("Test 1");
        $note->setOperation_id(251);
        $note->save();

        $compare=Acc_Operation_Note::build_jrn_id(251);
        $id=$note->getJrnNoteSql()->getp("n_id");
        $this->assertGreaterThan(0,$compare->getJrnNoteSql()->getp("n_id"),"note not created");
        $this->assertEquals($id,$compare->getJrnNoteSql()->getp("n_id"),"Note not loaded");
        $note->setNote("Test 2");
        $note->save();
         $compare->load();
        $this->assertEquals("Test 2",$compare->getJrnNoteSql()->getp("n_text"),"note not updated and loaded");
        $this->assertEquals("Test 2",$compare->getNote(),"note not updated, match incorrect");
    }
}
