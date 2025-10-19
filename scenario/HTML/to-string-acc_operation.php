<?php
//@description: Check that Acc_Operation and his children has implemented properly the _toString function
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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 22/10/23


/**
 * @file
 * @brief answer to an inplace object
 */
echo h1(_("Vente"));
$obj=new Acc_Sold($cn,911);
$obj->get();
print '<pre>';
print $obj;
print '</pre>';

echo h1(_("Opération diverse"));
$obj=new Acc_Misc($cn,316);
$obj->get();
print '<pre>';
print $obj;
print '</pre>';

echo h1(_("Financier"));
$obj=new Acc_Fin($cn,142);
$obj->get();
print '<pre>';
print $obj;
print '</pre>';

echo h1(_("Achat"));
$obj=new Acc_Purchase($cn,141);
$obj->get();
print '<pre>';
print $obj;
print '</pre>';