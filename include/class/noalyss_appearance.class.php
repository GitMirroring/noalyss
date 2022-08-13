<?php
/*
 *   This file is part of NOALYSS.
 *    NOALYSS is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    NOALYSS is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with NOALYSS; if not, write to the Free Software
 *    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *    Copyright Author Dany De Bontridder danydb@noalyss.eu
 *
 */

/**
 * @file
 * @brief  Contains some parameters to change appearance of noalyss
 */

/**
 * @class
 * @brief Contains some parameters to change appearance of noalyss, the colors are saved
 * into the table parm_appearance
 */
class Noalyss_Appearance
{
    static private $aCSSColor = ['H2' => '#9fbcd6',
        'MENU1' => '#000074',
        'BODY' => '#ffffff',
        'MENU2' => '#3d3d87',
        'MENU1-SELECTED' => '#3d3d87',
        'TR-ODD'=>'#DCE7F5',
        'TR-EVEN'=>'#e4e7ed',
        'INNER-BOX'=>'#DCE1EF',
        'FONT-MENU1' => '#ffffff',
        'FONT-MENU2' => '#ffffff',
        'FONT-TABLE' => '#222bd0',
        'FONT-DEFAULT' => '#000074',
        'FOLDER' => '#ffffff',
        'FONT-TABLE-HEADER' =>'#0C106D',
        'FONT-FOLDER' => '#000074'];
    static private $aCSSColorName = array();
    private $aColor;

    function __construct()
    {
        self::$aCSSColorName = ['H2' => _("Titre"),
            'MENU1' => _("Menu principal"),
            'BODY' => _("Fond d'écran"),
            'MENU2' => _("Menu second"),
            'MENU1-SELECTED' => _("Menu choisi"),
            'TR-ODD'=>_("Ligne impaire"),
            'TR-EVEN'=>_("Ligne paire"),
            'INNER-BOX'=>_("Fond Boîte dialogue"),
            'FOLDER' => _("Couleur dossier"),
            'FONT-MENU1' => _("Couleur police menu principale"),
            'FONT-MENU2' => _("Couleur police menu second"),
            'FONT-TABLE' => _("Couleur police dans les tables"),
            'FONT-DEFAULT' => _("Couleur police par défaut"),
            'FONT-TABLE-HEADER' => _("Couleur police en-tête table"),
            'FONT-FOLDER' => _("Couleur police dossier")];
        $this->aColor=self::$aCSSColor;
    }

    function load()
    {
        $cn = Dossier::connect();
        $aColor = $cn->get_array("select a_code,a_value from parm_appearance");
        foreach ($aColor as $key => $value) {
            $this->aColor[$value['a_code']] = $value['a_value'];
        }

    }

    function save()
    {
        $cn = Dossier::connect();
        foreach ($this->aColor as $key => $value) {
            $cn->exec_sql("insert into parm_appearance values ($1,$2) 
                    on conflict (a_code) do update set a_value =excluded.a_value", [$key, $value]);
        }
    }

    function set_color($p_code, $p_value)
    {
        $aKey = array_keys($this->aColor);
        if (!in_array($p_code, $aKey)) {
            throw new \Exception("NA63: Code invalide ");
        }
        if (!preg_match('/^#[a-f0-9]{6}$/i', $p_value)) //hex color is valid
        {
            throw new \Exception("NA67: Couleur invalide ");
        }
        $this->aColor[$p_code] = $p_value;
    }

    function print_css()
    {
        $this->load();
        $body = $this->aColor['BODY'];
        $h2 = $this->aColor['H2'];
        $menu1 = $this->aColor['MENU1'];
        $menu2 = $this->aColor['MENU2'];
        $menu1_selected = $this->aColor['MENU1-SELECTED'];
        $menu1_font = $this->aColor['FONT-MENU1'];
        $menu2_font = $this->aColor['FONT-MENU2'];
        $font_table = $this->aColor['FONT-TABLE'];
        $font_default = $this->aColor['FONT-DEFAULT'];
        $font_table_header = $this->aColor['FONT-TABLE-HEADER'];
        $folder_font = $this->aColor['FONT-FOLDER'];
        $folder = $this->aColor['FOLDER'];
        $tr_odd=$this->aColor['TR-ODD'];
        $tr_even=$this->aColor['TR-EVEN'];
        $inner_box=$this->aColor['INNER-BOX'];

        echo <<<EOF
    <style>
    #dossier {
        color:{$folder_font};
        background-color: {$folder};
    }
    body {
        background-color: {$body} !important;
        color:{$font_default} !important;
    }
    h2 , hr {
        background-color: {$h2} !important;
    }
    .nav-fill .nav-item {
         background: {$menu1};
        color: {$menu1_font};
    }
    .nav-level2 {
        background-color: {$menu2};
        color:{$menu2_font};
    }
    .nav-pills .nav-link.active {
        background-color: {$menu1_selected}  !important;  
    }
    table.sortable, table.table_large, table.result  ,table.resultfooter {
        color:{$font_table} !important;
    }
    table.sortable th, table.table_large th, table.result th ,table.resultfooter th {
        color:{$font_table_header};
    }
    tr.odd,div.inner_box tr.odd,div.box tr.odd {
         background-color: {$tr_odd};
    }
    #calc1 , div.inner_box  , div.box, #add_todo_list , div.add_todo_list,body.op_detail_frame, div.op_detail_frame {
    background-color:{$inner_box};
   }
    tr.even {
    background-color: {$tr_even} !important;
    }
    </style>

EOF;

    }

    function input_reset()
    {
        $r="";
        $label=_("Cocher pour remettre les couleurs d'origine");
        $chk=new ICheckBox("reset_color",1);

        $str_reset=$chk->input();
        $r.=<<<EOF
<div class="form-group">
    <label for="reset_color">
    {$label}
</label>
    {$str_reset}
</div>
EOF;
        return $r;

    }
    function input_form()
    {
        $str = "";
        $str = '<h3 style="text-align: center;border:1px solid black;">'._("Couleur de fond").'</h3>';
        $f=0;
        foreach (self::$aCSSColorName as $key => $value) {
            $value = $this->aColor[$key];
            $icolor = new IColor($key, $value);
            $str_icolor = $icolor->input();
            $label = self::$aCSSColorName[$key];
            $part=mb_strcut($key,0,4);
            if (  strcmp($part , "FONT")==0 && $f==0 ){
                $str .=  '<h3 style="text-align: center;border:1px solid black;">'._("Couleur police").'</h3>';
                $f=1;
            }
            $str .= <<<EOF
<div class="form-group">
    <label for="{$key}">
    {$label}
</label>
    {$str_icolor}
</div>
EOF;
        }
        $str.=$this->input_reset();
        return $str;
    }

    /**
     * @return string[]
     */
    public static function getACSSColor(): array
    {
        return self::$aCSSColor;
    }

    /**
     * @param string[] $aCSSColor
     */
    public static function setACSSColor(array $aCSSColor): void
    {
        self::$aCSSColor = $aCSSColor;
    }

    /**
     * @return array
     */
    public static function getACSSColorName(): array
    {
        return self::$aCSSColorName;
    }

    /**
     * @param array $aCSSColorName
     */
    public static function setACSSColorName(array $aCSSColorName): void
    {
        self::$aCSSColorName = $aCSSColorName;
    }

    function reset()
    {
        $this->aColor=self::$aCSSColor ;
    }
    /**
     *@brief retrieve data from POST and returns true, if nothing is retrieved , returns false
     */
    function from_post()
    {
        $http=new \HttpInput();
        if ( $http->post("reset_color","number",0) == 1 ) {
            $this->reset();
            return true;
        }
        foreach (self::$aCSSColorName as $key=>$value) {
            $color=$http->post($key,'string',"");
            if (empty($color) ) { return false;}
            $this->set_color($key,$color);
        }

        return true;
    }
}



