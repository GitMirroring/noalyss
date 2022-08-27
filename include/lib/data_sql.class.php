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
 * @brief this a abstract class , all the SQL class, like noalyss_sql (table), Acc_Plan_SQL (based on a SQL not a table)
 */

/**
 * @brief this an abstract class , all the SQL class, like noalyss_sql (table), 
 * Acc_Plan_SQL (based on a SQL not a table) or a view.
 * 
 * You must create a class extending this one, in the constructor
 * these variables have to be defined
 * 
 *   - table = name of the view or empty
 *   - sql = sql statement
 *   - name = array of column name, match between logic and actual name
 *   - type = array , match between column and type of data
 *   - default = array of column with a default value
 *   - date_format = format of the date
 * 
 * After you call the parent constructor
 * @note the view or the table must include an unique key, otherwise the load 
 * doesn't work.
 * 
 * @pnote
 * Name is an array the key is the logical name and the value is the name of the column
 @code
  $this->name=array(
                "id"=>"o_id",
                "program"=>"o_prog",
                "date"=>"o_date",
                "qcode"=>"o_qcode",
                "fiche"=>"f_id",
  );
@endcode
 *
  the type is an array , key = column name , value = type

 * @code
  $this->type = array(
                "o_id"=>"numeric",
                "o_prog"=>"numeric",
                "o_date"=>"date",
                "o_qcode"=>"text",
                "f_id"=>"numeric",
                 );
@endcode
 * 
 */
abstract class Data_SQL
{
   var $cn;         //! Database connection
   var $name;           //! Array of logical and real name
   var $primary_key;    //! Column name of the primary key 
   var $type;           //! Type of the data
   var $date_format;    //! defaullt date format
   var $default;
   
   function __construct(DatabaseCore $p_cn, $p_id=-1)
    {
        $this->cn=$p_cn;
        $pk=$this->primary_key;
        $this->$pk=$p_id;
	// check that the definition is correct
	if (count($this->name) != count($this->type) ){
		throw new Exception (__FILE__." $this->table Cannot instantiate");
	}
        // forbid the use of a column named type , date_format,  name  or primary_key to avoid conflict
        
        /* Initialize an empty object */
        foreach ($this->name as $key)
        {
            if ( in_array($key,['name','type','format_date','cn','date_format','default'] ) ) {
                throw new Exception ('DATASQL-94 invalid column name'.$key);
            }
            $this->$key=null;
        }
        $this->$pk=$p_id;
        /* load it */
        if ($p_id != -1 )$this->load();
        if ( empty($this->date_format) )         $this->date_format="DD.MM.YYYY";
    }
/**
 * Insert or update : if the row already exists, update otherwise insert
 */
    public function save()
    {
       $count = $this->exist();
        
        if ($count == 0)
            $this->insert();
        else
            $this->update();
    }
    /**
     *@brief get the value thanks the colum name and not the alias (name). 
     *@see getp
     */
    public function get($p_string)
    {
        if (array_key_exists($p_string, $this->type)) {
            return $this->$p_string;
        }
        else
            throw new Exception(__FILE__.":".__LINE__.$p_string.'Erreur attribut inexistant '.$p_string);
    }

    /**
     *@brief set the value thanks the colum name and not the alias (name)
     *@see setp
     */
    public function set($p_string, $p_value)
    {
        if (array_key_exists($p_string, $this->type))    {
            $this->$p_string=$p_value;
            return $this;
        }        else
            throw new Exception(__FILE__.":".__LINE__.$p_string.'Erreur attribut inexistant '.$p_string);
    }

    /**
     *@brief set the value thanks the alias name instead of the colum name 
     *@see get
     */
    public function getp($p_string)
    {
        if (array_key_exists($p_string, $this->name)) {
            $idx=$this->name[$p_string];
            return $this->$idx;
        }
        else
            throw new Exception(__FILE__.":".__LINE__.$p_string.'Erreur attribut inexistant '.$p_string);
    }

    /**
     *@brief set the value thanks the alias name instead of the colum name 
     *@see set
     */
    public function setp($p_string, $p_value)
    {
        if (array_key_exists($p_string, $this->name))    {
            $idx=$this->name[$p_string];
            $this->$idx=$p_value;
            return $this;
        }        else
            throw new Exception(__FILE__.":".__LINE__.$p_string.'Erreur attribut inexistant '.$p_string);
    }

    abstract function insert();

    abstract function delete();

    abstract  function update();

    public function set_pk_value($p_value)
     {
         $pk=$this->primary_key;
           $this->$pk=$p_value;
     }
    public function get_pk_value()
    {
        $pk=$this->primary_key;
          return $this->$pk;
    }

    abstract function load();

    public function get_info()
    {
        return var_export($this, true);
    }
/**
 * @todo ajout vérification type (date, text ou numeric)
 * @return int
 */
    public function verify()
    {
        foreach ($this->name as $key)
        {
            if (noalyss_trim($this->$key)=='')
                $this->$key=null;
        }
        return 0;
    }

    /**
     * Transform an array into object
     * @param type $p_array
     * @return object
     */
    public function from_array($p_array)
    {
        foreach ($this->name as $key=> $value)
        {
            if (isset($p_array[$value]))
            {
                $this->$value=$p_array[$value];
            }
            else
            {
                $this->$value=null;
            }
        }
        return $this;
    }
    /**
     * 
     * Turn an object (row) into an array, and the key could be prefixed with $prefix
     * @param string $prefix before the key 
     * @return array
     */
    public function to_array($prefix="")
    {
        $array=array();
        foreach ($this->name as $key=> $value)
        {
            $nkey=$prefix.$key;
            $array[$key]=$this->$key;
        }
        return $array;
    }

    /**
     * @brief retrieve array of object thanks a condition
     * @param $cond condition (where clause) (optional by default all the rows are fetched)
     * you can use this parameter for the order or subselect
     * @param $p_array array for the SQL stmt
     * @see Database::exec_sql get_object  Database::num_row
     * @return the return value of exec_sql
     */
    abstract  function seek($cond='', $p_array=null);

    /**
     * get_seek return the next object, the return of the query must have all the column
     * of the object
     * @param $p_ret is the return value of an exec_sql
     * @param $idx is the index
     * @see seek
     * @return object
     */
    public function next($ret, $i)
    {
        $array=$this->cn->fetch_array($ret, $i);
        return $this->from_array($array);
    }

    /**
     * @see next
     */
    public function get_object($p_ret, $idx)
    {
        return $this->next($p_ret, $idx);
    }

    /**
     * @brief return an array of objects. 
     * Do not use this function if they are too many objects, it takes a lot of memory,
     * and could slow down your application.
     * @param $cond condition, order...
     * @param $p_array array to use for a condition
     * @note this function could slow down your application.
     */
    function collect_objects($cond='', $p_array=null)
    {
        if ($p_array != null && ! is_array($p_array) )
        {
            throw new Exception(_("Erreur : exec_sql attend un array"));
        }
        $ret=$this->seek($cond, $p_array);
        $max=Database::num_row($ret);
        $a_return=array();
        for ($i=0; $i<$max; $i++)
        {
            $a_return[$i]=clone $this->next($ret, $i);
        }
        return $a_return;
    }
    abstract function count($p_where="",$p_array=null) ;
    
    /**
     * Count the number of record with the id ,
     * @return integer  0 doesn't exist , 1 exists
     */
    abstract function exist() ;
    public function get_cn()
    {
        return $this->cn;
    }

    public function get_name()
    {
        return $this->name;
    }

    public function get_primary_key()
    {
        return $this->primary_key;
    }

    public function get_type()
    {
        return $this->type;
    }

    public function set_cn($cn)
    {
        $this->cn=$cn;
        return $this;
    }
    /**
     * 
     * @param string $name
     * @return $this
     */
    public function set_name($name)
    {
        $this->name=$name;
        return $this;
    }
    /**
     * 
     * @param string $primary_key
     * @return $this
     */
    public function set_primary_key($primary_key)
    {
        $this->primary_key=$primary_key;
        return $this;
    }
    /**
     * 
     * @param array $type
     * @return $this
     */
    public function set_type($type)
    {
        $this->type=$type;
        return $this;
    }


}

?>
