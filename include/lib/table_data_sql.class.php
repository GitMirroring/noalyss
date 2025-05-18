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
 * @file
 * @brief interface : for creating ORM Object
 */
/**
 * @class Table_Data_SQL
 * @brief Interface :  this wrapper is used to created easily a wrapper to a table (ORM)
 * You must create a class extending this one, in the constructor
 * these variables have to be defined
 * 
 *   - table = name of the view or empty
 *   - select = name of the select
 *   - name = array of column name, match between logic and actual name
 *   - type = array , match between column and type of data
 *   - default = array of column with a default value
 *   - date_format = format of the date
 * 
 * After you call the parent constructor
 * @note the view or the table must include an unique key, otherwise the load 
 * doesn't work.
 *
 * @class Table_Data_SQL
 * Match a table or a view into an object, you need to add the code for each table
 * @note : the primary key must be an integer
 *
 * @code
  class table_name_sql extends Table_Data_SQL
  {

  function __construct($p_id=-1)
  {
  $this->table = "schema.table";
  $this->primary_key = "o_id";

  $this->name=array(
  "id"=>"o_id",
  "program"=>"o_prog",
  "date"=>"o_date",
  "qcode"=>"o_qcode",
  "fiche"=>"f_id",


  );

  $this->type = array(
  "o_id"=>"numeric",
  "o_prog"=>"numeric",
  "o_date"=>"date",
  "o_qcode"=>"text",
  "f_id"=>"numeric",

  );

  $this->default = array(
  "o_id" => "auto",
  );
  $this->date_format = "DD.MM.YYYY";
  global $cn;

  parent::__construct($cn,$p_id);
  }

  }
 * @endcode
 *
 */
#[AllowDynamicProperties]
abstract class Table_Data_SQL extends Data_SQL
{

    
    function __construct($p_cn, $p_id=-1)
    {
        parent::__construct($p_cn, $p_id);
        
    }


    public function insert()
    {
        $this->verify();
        $sql="insert into ".$this->table." ( ";
        $sep="";
        $par="";
        $idx=1;
        $array=array();
        foreach ($this->name as $key=> $value)
        {
            if (isset($this->default[$value])&&$this->default[$value]=="auto"&&$this->$value==null)
                continue;
            // prob. with table when the pk is not auto.
            if ($value==$this->primary_key && $this->$value==-1 && isset($this->default[$value]) )
                continue;
            $sql.=$sep.$value;
            switch ($this->type[$value])
            {
                case "date":
                    if ($this->date_format=="")
                        throw new Exception('Format Date invalide');
                    $par .=$sep.'to_timestamp($'.$idx.",'".$this->date_format."')";
                    break;
                default:
                    $par .= $sep."$".$idx;
            }

            $array[]=$this->$value;
            $sep=",";
            $idx++;
        }
        $sql.=") values (".$par.") returning ".$this->primary_key;
        $pk=$this->primary_key;
        $returning=$this->cn->get_value($sql, $array);
        $this->$pk=$returning;
    }

    public function delete()
    {
        $pk=$this->primary_key;
        $sql=" delete from ".$this->table." where ".$this->primary_key."= $1";
        $this->cn->exec_sql($sql,array($this->$pk));
    }
    /**
     * @brief update the value of a column with an expression for $value for the current record
     * @param $column_exp string like column = $1 or column=function($1)
     * @param $value value replacing $1
     * @note you can use it for entering a date with the hour and minute like this
     *  $this->column_update("field_date = to_date($1,'DD.MM.YY HH24:MI'),date('d.m.Y H:i'))
     *
     * @return Table_Data_SQL
     */
    function column_update($column_expr,$value) {
        $pk=$this->primary_key;
        $sql="update ".$this->table." set {$column_expr} where {$this->primary_key} = $2";
        $this->cn->exec_sql($sql,[$value,$this->$pk]);
        return $this;

    }
    public function update()
    {
        $this->verify();
        $pk=$this->primary_key;
        $sql="update ".$this->table."  ";
        $sep="";
        $idx=1;
        $array=array();
        $set=" set ";
        foreach ($this->name as $key=> $value)        {
            if (isset($this->default[$value])&&$this->default[$value]=="auto")
                continue;
            switch ($this->type[$value])
            {
                case "date":
                    $par=$value.'=to_timestamp($'.$idx.",'".$this->date_format."')";
                    break;
                default:
                    $par=$value."= $".$idx;
            }
            $sql.=$sep." $set ".$par;
            $array[]=$this->$value;
            $sep=",";
            $set="";
            $idx++;
        }
        $array[]=$this->$pk;
        $sql.=" where ".$this->primary_key." = $".$idx;
        $this->cn->exec_sql($sql, $array);
    }
   /***
    * @brief load a row , corresponding to the primary key
    */
    public function load():bool
    {
        $sql=$this->build_query();
        $pk=$this->primary_key;
        // primary cannot be null or empty
        if (trim($this->$pk??"")==="" || $this->$pk===null)  {
            $this->$pk=-1;
            return false;
        }
       
        $result=$this->cn->get_array($sql,array ($this->$pk));
        if ($this->cn->count()==0)
        {
            $this->$pk=-1;
            return false;
        }

        foreach ($result[0] as $key=> $value)
        {
            $this->$key=$value;
        }
        return true;
    }


    /**
     * @brief retrieve array of object thanks a condition
     * @param $cond condition (where clause) (optional by default all the rows are fetched)
     * you can use this parameter for the order or subselect
     * @param $p_array array for the SQL stmt
     * @see Database::exec_sql get_object  Database::num_row
     * @return the return value of exec_sql
     */
    function seek($cond='', $p_array=null)
    {
        $sql="select * from ".$this->table."  $cond";
        $ret=$this->cn->exec_sql($sql, $p_array);
        return $ret;
    }


    /**
     * @brief return the number of count in the table corresponding to the where condition
     * @param string $p_where the condition appended to the SQL select query , where must be given
     * @param array $p_array variable from the $p_where condition
     * @return type
     */
    public function count($p_where="",$p_array=null) {
        $count=$this->cn->get_value("select count(*) from $this->table ".$p_where,$p_array);
        return $count;
    }
    /**
     *@brief Count the number of record with the id ,
     * @return integer  0 doesn't exist , 1 exists
     */
    public function exist() {
        $pk=$this->primary_key;
        $count=$this->cn->get_value("select count(*) from ".$this->table." where ".$this->primary_key."=$1",array($this->$pk));
        return $count;
    }
    
    /**
     * @brief Build the SQL select statement for querying the object and returns it
     * @return string Query of the object 
     */
    public function build_query()
    {
        $sql=" select ";
        $sep="";
        foreach ($this->name as $key)       {
            switch ($this->type[$key])
            {
                case "date":
                    $sql .= $sep.'to_char('.$key.",'".$this->date_format."') as ".$key;
                    break;
                default:
                    $sql.=$sep.$key;
            }
            $sep=",";
        }
        $pk=$this->primary_key;
        $sql.=" from ".$this->table;
        
        $sql.=" where ".$this->primary_key." = $1";
        
        return $sql;
    }
    /**
     * @brief Get all the row and use the p_key_code are the key value of array. 
     * The key column is usually the primary key or any unique key. 
     * the returns looks like
     * @code
       [ID1]=>array( ["PRIMARYKEY"=>"ID1" 
                       , "VALUE" => 2]);
       [ID2]=>array( ["PRIMARYKEY"=>"ID2" 
                      , "VALUE" => 2]);
      @endcode
     * @note It should be used only for small tables: the array is build row by row
     * @param string $p_key_col existing and unique key 
     * @param string $p_cond sql cond 
     * @param array $p_array array of value for the SQL condition
     */
    public function get_all_to_array($p_key_col,$p_cond="",$p_array=NULL)
    {
        $ret=$this->seek($p_cond, $p_array);
        if ($ret==FALSE)
            return array();
        $a_array=Database::fetch_all($ret);
        $nb_array=count($a_array);
        $a_result=array();
        try
        {
            for ($i=0; $i<$nb_array; $i++)
            {
                if (!isset($a_array[$i][$p_key_col]))
                {
                    throw new Exception("col not found ".$p_key_col);
                }
                $key=$a_array[$i][$p_key_col];
                if ( isset ($a_result[$key]) ){
                    throw new Exception ("duplicate found col : $key");
                }
                $a_result[$key]=$a_array[$i];
            }
        }
        catch (Exception $exc)
        {
            echo $exc->getMessage();
            record_log($exc->getMessage());
            record_log($exc->getTraceAsString());
            throw $exc;
        }
        return $a_result;
    }

}

?>
