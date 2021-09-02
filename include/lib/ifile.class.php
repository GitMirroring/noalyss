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
 * \brief Html Input 
 */

/*!
 * \class IFile
 * \brief Html Input
 */
class IFile extends HtmlInput
{
    // if true , the size is tested and a box is displaid
    private $alert_on_size;
    function __construct($p_name = "", $p_value = "", $p_id = "")
    {
        parent::__construct($p_name, $p_value, $p_id);
        $this->alert_on_size=false;
    }

    /**
     * @return false
     */
    public function getAlertOnSize()
    {
        return $this->alert_on_size;
    }

    /**
     *  if true , the size is tested and a box is displaid
     * @param false $alert_on_size
     */
    public function setAlertOnSize(bool $alert_on_size)
    {
        $this->alert_on_size = $alert_on_size;
    }

    /*!\brief show the html  input of the widget*/
    public function input($p_name=null,$p_value=null)
    {
        $this->name=($p_name==null)?$this->name:$p_name;
        $this->value=($p_value==null)?$this->value:$p_value;
        if ( $this->readOnly==true) return $this->display();
        if ($this->id=="") $this->id=uniqid("file_");
        $r=sprintf('<INPUT class="inp" TYPE="file" name="%s" id="%s" value="%s">',
            $this->name,
            $this->id,
            $this->value);
        if ( $this->alert_on_size)
        {
            $max_size=MAX_FILE_SIZE;
            $max_size_mb=round($max_size / 1024 /1024,2);
            $too_large=h(_("Fichier trop grand(max = $max_size_mb MB)"));

            $js_check_size=sprintf('
            document.getElementById("%s").addEventListener("change",function () 
            { 
                var fFile=document.getElementById("%s");
                if (fFile.files[0] && fFile.files[0].size>%s) { smoke.alert("%s");fFile.value="";} 
            });',$this->id,$this->id,$max_size,$too_large);
            $r.=create_script($js_check_size);

        }
        return $r;

    }
    /*!\brief print in html the readonly value of the widget*/
    public function display()
    {

    }
    static public function test_me()
    {
    }
}
