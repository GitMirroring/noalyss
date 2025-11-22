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

// Copyright Author Dany De Bontridder danydb@noalyss.eu

/*!
 * \file
 * \brief Manage the note attached to an operation
 */

/*!
 * \class Acc_Operation_Note
 * \brief Manage the note attached to an operation
 */

class Acc_Operation_Note
{
    private $note;
    private $id;
    private $operation_id; //!< jrn.jr_id
    private $jrn_note_sql;
    
    function __construct(Jrn_Note_SQL $p_Jrn_Note_SQL)
    {
        $this->jrn_note_sql = $p_Jrn_Note_SQL;
        $this->id=$p_Jrn_Note_SQL->n_id;
        $this->note=$p_Jrn_Note_SQL->n_text;
        $this->operation_id=$p_Jrn_Note_SQL->jr_id;
    }

    /**
     * @return Jrn_Note_SQL
     */
    public function getJrnNoteSql(): Jrn_Note_SQL
    {
        return $this->jrn_note_sql;
    }

    /**
     * @param Jrn_Note_SQL $jrn_note_sql
     */
    public function setJrnNoteSql(Jrn_Note_SQL $jrn_note_sql): Acc_Operation_Note
    {
        $this->jrn_note_sql = $jrn_note_sql;
        return $this;
    }

    /**
     * @param $p_jr_id
     * @return void
     */
    static  function build_jrn_id($p_jr_id) {
        $cn=Dossier::connect();
        $n_id=$cn->get_value("select n_id from jrn_note where jr_id=$1",[$p_jr_id]);
        if ( $cn->count() == 0) {
            $n_id=-1;
        }

        return new Acc_Operation_Note(new Jrn_Note_SQL($cn,$n_id));

    }
    function save():Acc_Operation_Note
    {
        $cn=Dossier::connect();
        $this->jrn_note_sql->setp("jr_id",$this->operation_id);
        $this->jrn_note_sql->setp("n_text", strip_tags($this->note));

        if ( empty($this->jrn_note_sql->n_text) && $this->id > -1 ) {
            $this->jrn_note_sql->delete();
            return $this;
        }
        // forbid the tag script, iframe and A
        $n=str_ireplace('<script','<.script',$this->note);
        $n=str_ireplace('<iframe','<.iframe',$n);
        $n=str_ireplace('<a ','<.a',$n);
        
        $this->jrn_note_sql->setp("n_html", $n);
        $this->jrn_note_sql->save();
        $this->id=$this->jrn_note_sql->n_id;
        return $this;
    }
    function setNote($p_note) {
        $this->note=$p_note;

        return $this;
    }
    function setOperation_id($p_jrid) {
        $this->operation_id=$p_jrid;
        return $this;
    }
    function getNote() {
       return $this->note;
    }
    function getOperation_id() {
        return $this->operation_id;
    }
    function load():void
    {
        $this->jrn_note_sql->load();
        $this->operation_id=$this->jrn_note_sql->jr_id;
        $this->note=$this->jrn_note_sql->n_text;
        $this->id=$this->jrn_note_sql->n_id;
    }
    static function input($p_current)
    {
        require NOALYSS_TEMPLATE.'/acc_operation_note-input.php';
    }
    function fromPost()
    {

    }
    function print()
    {
        echo '<div id="print_note">'.h($this->note).'</div>';
    }
    /**
     * @brief create a TEXTAREA to show HTML Note
     * @param type $div_id
     * @returns ITextarea
     */
    static function build_textarea($div_id)
    {
        $inote = new ITextarea('jrn_note');
        $inote->id="{$div_id}_jrn_note";
        $inote->set_enrichText("no-toolbar");
        $inote->heigh=500;
        return $inote;
    }
}