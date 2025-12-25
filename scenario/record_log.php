<?php
//@description: Test record_log function for recording exception AC_COMMON.PHP
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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 21/02/25
/*! 
 * \file
 * \brief testing functions from ac_common
 */
function y()
{
    try {
        throw new Exception ("FUNCTION Y",1000);
    } catch (\Exception $e) {
        throw new Exception($e);
    }
}
function x()
{
    try {
        y();

    }catch(Exception $e) {
        throw new Exception("from X",2000,$e);
    }
}

try {
    x();
} catch (\Exception $e) {
record_log($e);

}
