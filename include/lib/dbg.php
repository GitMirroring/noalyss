<?php

namespace Noalyss;
require_once NOALYSS_INCLUDE.'/lib/ac_common.php';

class Dbg
{
    public static function echo_var($n_level, $msg)
    {
        if (DEBUGNOALYSS > $n_level) {
            echo '<span style="font-size:12px;color:orangered;background-color:lightyellow;">';
            $type = gettype($msg);
            if (in_array($type, ["string", "integer", "double"])) {
                echo $msg;
            } else {

                echo "<pre>DBG";
                var_export($msg);
                echo '</pre>';
            }
            echo '</span>';
        }
    }

    public static function echo_function($msg)
    {
        if (DEBUGNOALYSS > 1) {
            echo '<span style="font-size:12px;color:lightgreen;background-color:lightyellow;">';
            echo "[FILE: $msg]";

            echo '</span>';
        }
    }
    public static function echo_file($msg)
    {
        if (DEBUGNOALYSS > 1) {
            echo '<span style="font-size:12px;color:brown;background-color:lightyellow;">';
            echo "[FILE: $msg]";

            echo '</span>';
        }
    }

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
}
?>
