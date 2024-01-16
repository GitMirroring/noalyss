<?php
/**
 * @file
 * @brief to be included , show all the variables, only for development purpopse
 *
 */

if (DEBUGNOALYSS <= 1 ) return;

$variable = get_defined_vars();

echo \Noalyss\Dbg::hidden_info("All variables", $variable);