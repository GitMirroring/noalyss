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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 18/08/24
/*! 
 * \file
 * \brief install the widget event 
 */
global $cn;

$cn->exec_sql("insert into widget_dashboard (wd_code,wd_description,wd_parameter,wd_name) values ($1,$2,$3,$4)",
    array (
        'invoice'
        ,'Affiche les prochaines factures fournisseurs ou clients ou en retard'
        ,1
        ,'Factures'
        )
);
