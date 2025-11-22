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
/**
 * \file
 * \brief  class for the table document_type
 */

/**
 * @class Document_Type
 *@brief class for the table document_type , a document_type is a kind of action in the follow up
 */
class Document_Type
{

    var $db;          //!< Database Connection
    var $dt_id;       //!< primary key see SQL DOCUMENT_TYPE.DT_ID
    var $dt_value;    //!< description of the document see SQL DOCUMENT_TYPE.DT_VALUE
    var $dt_prefix;   //!< prefix for numbering see SQL DOCUMENT_TYPE.DT_PREFIX

	/**
	 * \brief constructor document_type
	 * \param $p_cn database connx
         * \param $dt_id primary key see SQL DOCUMENT_TYPE.DT_ID
	 */
	function __construct(\Database $p_cn, int $dt_id = -1)
	{
		$this->db = $p_cn;
		$this->dt_id = $dt_id;
	}

	/**
	 * \brief Get all the data for this dt_id
	 */

	function get()
	{
		$sql = "select * from document_type where dt_id=$1";
		$r = $this->db->get_row($sql, array($this->dt_id));
		if ( $r == null ) return 1;

		$this->dt_id = $r['dt_id'];
		$this->dt_value = $r['dt_value'];
		$this->dt_prefix = $r['dt_prefix'];
		return 0;
	}

	/**
	 * @brief get a list
	 * @param $p_cn database connection
	 * @return array of data from document_type
	 */
	static function get_list($p_cn)
	{
		$sql = "select * from document_type order by dt_value";
		$r = $p_cn->get_array($sql);
		$array = array();
		for ($i = 0; $i < count($r); $i++)
		{
            $tmp=array();
			$tmp['dt_value'] = $r[$i]['dt_value'];
			$tmp['dt_prefix'] = $r[$i]['dt_prefix'];

			$bt = new IButton('M' . $r[$i]['dt_id']);
			$bt->label = _('Modifier');
			$bt->javascript = "cat_doc_change('" . $r[$i]['dt_id'] . "','" . Dossier::id() . "');";

			$tmp['js_mod'] = $bt->input();
			$tmp['dt_id'] = $r[$i]['dt_id'];

			$bt = new IButton('X' . $r[$i]['dt_id']);
			$bt->label = _('Effacer');
			$bt->javascript = "confirm_box('X{$r[$i]['dt_id']}','" . _('Vous confirmez') . "',";
                        $bt->javascript.="function () { cat_doc_remove('{$r[$i]['dt_id']}','" . Dossier::id() . "');})";

			$tmp['js_remove'] = $bt->input();


			$array[$i] = $tmp;
		}
		return $array;
	}

        /**
         * Restart the increment of the document
         * @param type $p_int
         */
	function set_number(int $p_int)
	{
		try
		{
			$this->db->exec_sql("alter sequence seq_doc_type_" . $this->dt_id . " restart " . $p_int);
		}
		catch (Exception $e)
		{
			alert("Erreur " . $e->getMessage());
		}
	}

    function __toString(): string
    {
      return sprintf("dt_id : [%d] \n dt_value [%s] \n dt_prefix [%s]\n",$this->dt_id,$this->dt_value,$this->dt_prefix);
    }

    /**
     * @brief unit test for Document_Type
     * @return void
     */
    static function test_me()
    {
       // if (! defined('TEST_UNIT')) return;
        $cn=Dossier::connect();
        function prv_echo_error($msg,int $lineno) {
            print '<p class="p-2 alert-danger">';
            print "ERROR : $lineno";
            print $msg;
            print '</p>';
        }
        // prepare test
        $old_value=[];
        try {
            $cn->start();
            $old_value[0]=$cn->get_row('select * from document_type where dt_id=$1',[8]);
            $old_value[1]=$cn->get_row('select * from document_type where dt_id=$1',[6]);

            $cn->exec_sql("update document_type set dt_value='Email' where dt_id=$1",[6]);
            $cn->exec_sql("update document_type set dt_prefix='PML' where dt_id=$1",[8]);
            $document_type=new Document_Type($cn,8);
            echo $document_type;

            if ( $document_type->get() == 1 ) {
            }

            if ( $document_type->dt_prefix!='PML') {
                prv_echo_error("PREFIX :". $document_type,__LINE__);
            }

            $document_type->set_number(6);
            $document_type->get();

            if ( $document_type->dt_value!='Email') {
                prv_echo_error("VALUE :". $document_type,__LINE__);
            }
            $list=Document_Type::get_list($cn);

            // list array > 1 and key =5
            if (count ($list) == 0) {
                prv_echo_error("GET_LIST :EMPTY",__LINE__);
            } else {
               foreach (['js_mod','dt_id','dt_value','dt_prefix','js_remove'] as $key) {
                   if ( ! isset ($list[0][$key])) {
                       prv_echo_error ('NOT SET '.$key,__LINE__);
                   }   else {
                       echo '<pre>';
                       var_dump( $list[0][$key]);
                       echo '</pre>';
                   }
               }
            }
        } catch (\Exception $e) {
            prv_echo_error("EXCEPTION :". $document_type,__LINE__);
            print_r($e->getTraceAsString());
        }
        $cn->rollback();
    }
}
