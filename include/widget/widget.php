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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 10/08/24
/*! 
 * \file
 * \brief Main class for widget
 */

namespace Noalyss\Widget;

/*!
 *\class
 * \brief Main class for widget
 */
abstract class Widget
{

    public function __construct(protected int $user_widget_id=0,protected string $widget_code="",protected  $db=null)
    {
        if ($db == null) {
            $this->db=\Dossier::connect();
        }
    }

    public function get_user_widget_id(): int
    {
        return $this->user_widget_id;
    }

    public function set_user_widget_id(int $user_widget_id): Widget
    {
        $this->user_widget_id = $user_widget_id;
        return $this;
    }

    public function get_widget_code(): string
    {
        return $this->widget_code;
    }

    public function set_widget_code(string $widget_code):  Widget
    {
        $this->widget_code = $widget_code;
        return $this;
    }

    /**
     * @brief display the content for the current connected user of the widget with the parameter
     * @return mixed
     */
    abstract function display();

    /**
     * @brief display a description of the widget and allow to save it for the current user, call input_param function
     * of the widget if it exists
     * @return mixed
     */
    function input()
    {

        //read description from database
        $row=$this->db->get_row("
                    select 
                        wd_code
                        ,wd_name
                        ,wd_parameter,
                        wd_description 
                    from 
                        widget_dashboard 
                    where 
                        wd_code=$1",
        [$this->widget_code]);

        echo "<li id=\"elt_{$this->user_widget_id}\"> <span class='widget-name'>{$row['wd_name']}</span>{$row['wd_description']}";

        if ( $this->user_widget_id > 0) {
            if ( $row['wd_parameter'] == 1) {
                $this->display_parameter();
            }
            echo '<span style="float:right;color:red">'.\Icon_Action::trash(uniqid(),sprintf("widget.delete('%s')",$this->user_widget_id));
        }
        else {
            if ( $row['wd_parameter'] == 1) {
                $this->input_parameter();
            }
            echo '<span style="float:right;">'.\Icon_Action::icon_add(uniqid(),sprintf("widget.add('%s')",$this->widget_code));
        }

        echo '</span>';
        echo '</li>';
    }

    /**
     * @brief returns an array  of widget for the connected user, ordered
     * @return array [ uw_id,dashboard_widget_id,wd_code,wd_description
     */
    static function get_enabled_widget():array
    {
        global $g_user,$cn;
        return $cn->get_array("
        select 
	uw.uw_id,
	uw.dashboard_widget_id ,
	wd.wd_code,
	wd.wd_description
	from user_widget uw 
join widget_dashboard wd on (uw.dashboard_widget_id=wd.wd_id)
where use_login=$1 order by uw.uw_order
",[$g_user->login]);

    }

    /**
     * @brief Build a widget thank the user_widget_id (SQL :PK :  USER_WIDGET.UW_ID) and $widget_code
     * @param $user_widget_id integer (SQL :PK :  USER_WIDGET.UW_ID)
     * @param $widget_code string (SQL WIDGET_DASHBOARD.WD_CODE)
     * @return Widget
     */
    static function build_user_widget($user_widget_id,$widget_code):?Widget
    {
        // load the class if file (code/code.php) exists.
        if (file_exists(NOALYSS_INCLUDE."/widget/$widget_code/$widget_code.php")) {
            require_once NOALYSS_INCLUDE."/widget/$widget_code/$widget_code.php";
            $class=sprintf("\\Noalyss\\Widget\\%s",$widget_code);
            $obj= new $class;
            $obj->set_widget_code($widget_code);
            $obj->set_user_widget_id($user_widget_id);
            return $obj;
        }

        // return the object
        return null;
    }

    /**
     * @brief output the DIV HTML with class and id for the widget
     * @return void
     */
    function open_div() {
        printf( '<div id="%s_%s" class="box">',$this->widget_code,$this->user_widget_id);
    }
    function close_div() {
        echo '</div>';
    }

    /**
     * @brief display a box and fills it with the content of an ajax calls , the ajax calls Widget::display
     * @param Widget $widget
     * @return void
     */
    static function ajax_display(Widget $widget ){
        $box= sprintf( '%s_%s',$widget->get_widget_code(),$widget->get_user_widget_id());
        $widget->open_div();

        print '<div class="loading_msg"></div>';
        print '<div class="loading_msg"></div>';
        print '<div class="loading_msg"></div>';
        print '<div class="loading_msg"></div>';
        print '<div class="loading_msg"></div>';
        print '<div class="loading_msg"></div>';
        print '<div class="loading_msg"></div>';
        print '<div class="loading_msg"></div>';
        echo p(_("Un instant, on charge :-)"));
        $widget->close_div();

        $dossier_id=\Dossier::id();
        echo <<<EOF
<script>
var widget= new Widget('{$dossier_id}') 
widget.display('{$box}',{$widget->get_user_widget_id()},'{$widget->get_widget_code()}')
</script>


EOF;



    }

    /**
     * @brier display activated widgets
     * @return void
     */
    static function display_available()
    {

        $aWidget=Widget::get_enabled_widget();
        echo '<ul class="list-unstyled" id="contain_widget">';
        foreach ($aWidget as $item) {
            $widget=Widget::build_user_widget($item['uw_id'],$item['wd_code']);
            $widget->input();
        }
        echo '</ul>';
        echo \HtmlInput::hidden("order_widget_hidden", "");
        create_script("widget.create_sortable()");
    }

    /**
     * @brief save widget order from an array
     * @param $array array of USER_WIDGET.UW_ID
     * @return void
     * @exception DatabaseCore fails , cannot update
     */
    static function save($array) {
        if (empty($array)) return;
        global $cn,$g_user;
        try {
            $cn->start();
            $order=10;
            $cn->exec_sql("create temporary table tmp_widget(user_widget_id integer,tw_order integer )");
            foreach ($array as $item) {
                $cn->exec_sql('insert into tmp_widget(user_widget_id,tw_order ) values ($1,$2)',
                [$item,$order]);
                $order+=20;
            }
            $cn->exec_sql("delete from user_widget where use_login = $1 and uw_id not in (select user_widget_id from tmp_widget)",
                array($g_user->getLogin()));

            $cn->exec_sql("update user_widget set uw_order =tw_order from  tmp_widget where user_widget_id=uw_id");

            $cn->commit();

        } catch (\Exception $e) {
            throw ($e);
        }


    }

    /**
     * @brief show all the widget that can be added
     * @return void
     */
    public static function select_available()
    {
        global $cn;
        Widget::scanfolder();
        $aWidget=$cn->get_array("select wd_code,wd_name, wd_description,wd_parameter from widget_dashboard order by wd_name");
        echo '<ul id="widget_add" class="list-unstyled">';
        foreach ($aWidget as $item) {
            $widget=Widget::build_user_widget(-1,$item['wd_code']);
            $widget?->input();

        }
        echo '</ul>';
    }

    /**
     * @brief open a form with the DOMID "widget_code"_param, it appears once only for each widget in the dialog box
     * for adding widget to the dashboard
     * @param $html_input string HTML string with all the HTML  INPUT that will be enclosed by the FORM
     * @return void
     */
    function make_form($html_input)
    {
        printf ('<form id="%s_param" style="display:inline">',$this->widget_code);
        echo $html_input;
        printf ('</form>');
    }

    /**
     * @brief MUST BE overrided if the widget needs extra parameters, create a FORM to add extra-parameter
     * @return void
     * @throws \Exception
     */
    function input_parameter ()
    {
        throw new \Exception(__FUNCTION__." not implemented");
    }

    /**
     * @brief MUST BE overrided if the widget needs extra parameters, display the content of extra-parameter
     * @param $user_widget_id
     * @return void
     * @throws \Exception
     */

    function display_parameter() {
        throw new \Exception(__FUNCTION__." not implemented");
    }

    /**
     * @brief scan folder to find install.php file , include them if the code is not in DB
     * @return void
     */
    static   function scanfolder()
    {
        global $cn;
        $handle=opendir(NOALYSS_INCLUDE."/widget");
        while (($dir = readdir($handle)) != false ) {
            $directory=NOALYSS_INCLUDE."/widget".DIRECTORY_SEPARATOR.$dir;
            if (is_dir($directory) && $dir != "." && $dir != "..") {
                // code exists in DB ?
                $cnt=$cn->get_value("select count(*) from widget_dashboard where wd_code = $1",[$dir]);
                if ( $cnt > 0) {
                    continue;
                } else {
                    // include the install.php file if any and install the widget
                    if (file_exists($directory . DIRECTORY_SEPARATOR . "install.php")){
                        include $directory . DIRECTORY_SEPARATOR . "install.php";
                    }
                }
            }
        }

    }

    /**
     * @brief get the parameter of the widget and returns an array
     * @return array  key=>value
     */
    function get_parameter()
    {
        $param = $this->db->get_value("select uw_parameter from user_widget where uw_id=$1",[$this->user_widget_id]);
        if (empty ($param)) return [];
        parse_str($param,$aParam);
        return $aParam;
    }
}