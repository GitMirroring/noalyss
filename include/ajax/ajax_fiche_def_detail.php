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
 * @brief show detail of a fiche_def (category of card) + Attribut
 *
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
global $g_user;

if ( $g_user->check_action(FICCAT) == 0 && $g_user->check_module("CCARD") == 0 )
    return;

$http=new HttpInput();
$id=$http->get("id","number");
$fd=new Fiche_Def($cn,$id);
$fd->input_new();
?>
