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

class Follow_Up_Filter
{
    protected $name;
    protected $json_content;

    public function __construct($name,$json_content)
    {
        $this->name=$name;
        $this->json_content=$json_content;
    }

    /**
     * @return mixed
     */
    public function getName():string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): Follow_Up_Filter
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getaContent():array
    {
        return $this->json_content;
    }

    /**
     * @param mixed $json_content
     */
    public function setaContent($json_content): Follow_Up_Filter
    {
        $this->json_content = $json_content;
        return $this;
    }

    public function save() {
        global $cn,$g_user;
        $this->name=trim($this->name);

        $exist=$cn->get_value("select count(*) from action_gestion_filter where af_user=$1 and 
             af_name=$2" , [$g_user->getLogin(),$this->name]);
        if ( $exist == 0 ) {
            $cn->exec_sql("insert into action_gestion_filter(af_user,af_name,af_search) values ($1,$2,$3)",
            [$g_user->getLogin(),$this->name,$this->json_content]);
        } else {
            $cn->exec_sql("update  action_gestion_filter set af_search=$3 where 
                af_user=$1 and 
                 af_name=$2" ,
                [$g_user->getLogin(),$this->name,$this->json_content]);
        }

    }

    /**
     * @brief display the list of recorded search
     * @param $login string Login of the user
     * @return void
     */
    public static function display_list($login):void
    {

        require NOALYSS_TEMPLATE."/follow_up_filter-display_list.php";
    }
}