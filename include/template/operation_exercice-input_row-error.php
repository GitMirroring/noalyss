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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 11/01/24
/*! 
 * \file
 * \brief operation already transfered
 */

Noalyss\Dbg::echo_file(__FILE__);
echo \HtmlInput::title_box("", "operation_exercice_bx");

printf(_("Opération transférée le %s"),$operation->getp("oe_transfer_date") );
echo '<ul class="aligned-block">';
echo \HtmlInput::button_close("operation_exercice_bx") ;
echo '</li>';