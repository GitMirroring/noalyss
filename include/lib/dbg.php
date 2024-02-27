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
 * \brief This class contains utility for developpers
 */
/*!
 *
 * \class Dbg
 * \brief utilities for showing debug message
 */
namespace Noalyss;
require_once NOALYSS_INCLUDE.'/lib/ac_common.php';
/**
 *
 */
class Dbg
{
    /**
     * @brief Display the value of a var if DEBUGNOALYSS is greater than $n_level, the debugging info has a certain
     * formatting
     * @param $n_level integer if greater than DEBUGNOALYSS, the variable will be displaid
     * @param $msg value to display : could be an object array, ..
     * @param $print if true display , otherwise returns a string
     * @return string|void
     */
    public static function echo_var($n_level, $msg,$print=true)
    {
        if (DEBUGNOALYSS > $n_level) {
            $r= '<span style="font-size:12px;color:orangered;background-color:lightyellow;margin:1rem;border:1px black dashed">';
            $type = gettype($msg);
            if (in_array($type, ["string", "integer", "double"])) {
                $r.= $msg;
            } else {

                $r.= "<pre>DBG";
                $r.=print_r($msg,true);
                $r.='</pre>';
            }
            $r.='</span>';
            if ($print) { echo $r;}
            return $r;
        }
    }

    /**
     * @brief returns a string , for the function with a specific style
     * @param $msg function name __FUNCTION__ or __CLASS__
     * @param $print if true display , otherwise returns a string
     * @return string|void
     */
    public static function echo_function($msg,$print=true)
    {
        if (DEBUGNOALYSS > 1) {
            $r =  '<span style="font-size:12px;color:brown;background-color:bisque;">';
            $r.="[FUNC: $msg]";

            $r.= '</span>';
            if ($print) { echo $r;}
            return $r;
        }
    }

    /**
     * @brief display the file
     * @param $msg name of file , usually always __FILE__
     * @param $print if true display , otherwise returns a string
     * @return string|void
     */
    public static function echo_file($msg,$print=true)
    {
        if (DEBUGNOALYSS > 1) {
            $r =  '<span style="font-size:12px;color:brown;background-color:lightyellow;display:table-row">';
            $r.="[FILE: $msg]";

            $r.= '</span>';
            if ($print) { echo $r;}
            return $r;
        }
    }

    /**
     * @brief display a bar depending of the size of the screen , it helps for CSS to see the media-size
     * @return void
     */
    static function display_size()
    {
        echo <<<EOF
<div class="d-block d-sm-none  " style="background-color:lightblue">Xtra  Small</div>
<div class="d-none d-sm-block d-md-none d-lg-none d-xl-none " style="background-color:red">Small</div>
<div class="d-none d-md-block d-lg-none " style="background-color:orangered">Medium</div>
<div class="d-none d-lg-block d-xl-none " style="background-color:orange">Large</div>
<div class="d-none d-xl-block " style="background-color:wheat">X Large</div>
EOF;
    }

    /**
     * @brief for development , show request (POST, GET)
     * @return void
     */
    static function display_request()
    {
        $id = uniqid("debug");
        $title = \HtmlInput::title_box(_("REQUEST"), $id, "hide");
        ?>
        <div class=" col-10 inner_box" style="display:none" id="<?= $id ?>">
            <?= $title ?>

            <h2 style="margin-top:100px"> Memory Usage
                <?php echo round(memory_get_usage() / 1024.0, 2) . " kb \n"; ?>
            </h2>
            $_POST
            <pre><?php echo print_r($_POST) ?></pre>
            $_GET
            <pre><?= print_r($_GET) ?></pre>
            $_REQUEST
            <pre><?= print_r($_REQUEST) ?></pre>
            $_FILES
            <pre><?= print_r($_FILES) ?></pre>
            <?= \HtmlInput::button_hide($id) ?>
        </div>
        <input type="button" onclick="document.getElementById('<?= $id ?>').show();" value="Show request">
        <?php
    }

    /**
     * @brief for development , show GLOBAL and SESSION
     * @return void
     */
    static function display_global()
    {
        $id = uniqid("debug");
        $title = \HtmlInput::title_box(_("GLOBALS"), $id, "hide");
        ?>
        <div class=" col-10 inner_box" style="display:none" id="<?= $id ?>">
            <?= $title ?>
            $GLOBALS
            <pre><?= print_r($GLOBALS) ?></pre>

            <?= \HtmlInput::button_hide($id) ?>
        </div>
        <input type="button" onclick="document.getElementById('<?= $id ?>').show();" value="Show session">
        <?php
    }

    /**
     * @brief Show a icon to display large information on demand, when you click it , the content will be showned
     * @param string $p_title title of the dialog box
     * @param mixed $msg var_export of the msg
     * @return string HTML to display
     */
    public static function hidden_info($p_title,$msg)
    {
        $id=uniqid("debug");
        $title=\HtmlInput::title_box($p_title,"$id"."_infodiv","hide");
        $content=var_export($msg,true);

        $button_close=\HtmlInput::button_hide("$id"."_infodiv");
        $r=<<<EOF
<div id="{$id}_infodiv" style="display: none;background:rgba(234, 221, 114, 0.66);color:black;font-size:80%;width:auto;">
{$title}
<div>
<pre>
    {$content}

</pre>
</div>
{$button_close}
</div>
<a href="javascript:void(0)" onclick="$('{$id}_infodiv').show()">&#128374;</a>

EOF;
        return $r;
    }

    /**
     * @brief start a timer
     * @return void
     */
    public static function timer_start()
    {
        global $timer;
        $timer=hrtime(true);
    }

    /**
     * @brief stop the timer and show the elapsed time, it is used for optimising the code
     * @return void
     */
    public static function timer_show()
    {
        global $timer;
        // $delta=$timer-microtime(true) ;
        echo '<span style="font-size:80%;color:navy;background:lightgreen">';
        echo _("Temps écoulé : ");
        echo (hrtime(true)-$timer)/1e+6;
        echo '</span>';
    }
}
?>
