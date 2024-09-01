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
 *@file
 *@brief Menu_Ref let you manage the available menu
 */
/**
 *@class Menu_Ref
 *@brief Menu_Ref let you manage the available menu
 */
require_once NOALYSS_INCLUDE.'/database/menu_ref_sql.class.php';
class Menu_Ref extends Menu_Ref_SQL
{
    function format_code()
    {
        $this->me_code=strtoupper($this->me_code);
        $this->me_code=trim($this->me_code);
        $this->me_code=noalyss_str_replace('<','',$this->me_code);
        $this->me_code=noalyss_str_replace('>','',$this->me_code);
        
    }
    function verify()
    {
		try
		{
        parent::verify();
        if ( $this->me_code == -1)
        {
             throw new Exception(_("le code ne peut être vide"));
        }

            $this->format_code();
            if (isset ($_POST['create_menu'])
                && $this->cn->get_value("select count(*) from menu_ref where me_code=$1",array($this->me_code)) > 0)
                    throw new Exception ('Doublon');
            if (trim($this->me_code)=='')
                    throw new Exception ('Ce menu existe déjà');
            if (empty($this->me_code)  ) {
               throw new Exception(_("le code ne peut être vide"));
            }
        
        if ( ! file_exists('../include/'.$this->me_file)) throw new Exception ('Ce menu fichier '.$this->me_file." n'existe pas");

        return 0;
		} catch (Exception $e)
		{
			alert($e->getMessage());
			return -1;
		}
    }

}

?>
