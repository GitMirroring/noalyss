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

/*!\file
 * \brief  about the accountancy period (usually 1 year starting in January until december) = exercice
 */

/*!
 * \class Exercice
 * \brief about the accountancy period (usually 1 year starting in January until december) = exercice
 */
class Exercice
{
  function __construct($cn)
  {
    $this->cn=$cn;
  }
  /**
   *@brief return the number of different exercices into a folder
   *@param $cn is the database connexion object database
   *@return the count of exercice
   */
   function count()
  {
    $count=$this->cn->get_value('select count(distinct p_exercice) from parm_periode');
    return $count;
  }
   /**
    *@brief Show a ISelect with the different exercices
    *@param $name of the iselect
    *@param $selected the selected year  (default = '')
    *@param $js javascript (default = '')
    *@return ISelect object
    */
   function select($name,$selected='',$js='')
   {
     $iselect=new ISelect($name);
     $iselect->value=$this->cn->make_array('select distinct p_exercice,p_exercice_label from parm_periode order by 1 desc');
     $iselect->selected=$selected;
     $iselect->javascript=$js;
     return $iselect;
   }
   /**
    *@brief  Show a ISelect with the different exercices, display start and end date
    *@param $name of the iselect
    *@param $selected the selected year  (default = '')
    *@param $js javascript (default = '')
    *@return ISelect object
    */
   function select_date($name,$selected='',$js='')
   {
     $iselect=new ISelect($name);
     $iselect->value=$this->cn->make_array("select distinct p_exercice,to_char (min(p_start),'DD.MM.YY')
																	 ||' - '
																	 ||to_char (max(p_end),'DD.MM.YY')
											from parm_periode
											group by p_exercice order by 1");
     $iselect->selected=$selected;
     $iselect->javascript=$js;
     return $iselect;
   }
   /**
    * @brief retrieve the exercice from the exercice label
    * @param string $p_label
    */
   function exercice_from_label($p_label)
   {
       $value  = $this->cn->get_value("select distinct p_exercice from parm_periode where p_exercice_label=$1",
               [$p_label]);
       if ($value == "") return -1;
       return $value;
   }
}
