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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 22/10/23


/**
 * @file
 * @brief list of vatex code
 */
echo \HtmlInput::title_box(_("Choix VATEX"), $dgbox);
$a_vatex_code = $cn->get_array("select vx_code,vx_code_name,vx_description,vx_remark , vx_country from vatex_code order by 1");
$nb_vatex_code = count($a_vatex_code);


//@var $os_country (ISELECT) filter table by country
$os_country=new \ISelect("filter_country");
$os_country->transform(array( 
                        "countryeu"=>"Europe",
                        ""=>_("Tous"),
                        "countryfr"=>"France"
                        )
                        );
$os_country->javascript=' onchange="vat_code.filter_country()" ';
?>
<div class="content">
<?=$os_country->input()?>
<?=HtmlInput::filter_table_DOM('code_vatex_tb', '0,1,2,3', 1,'search_vatex','filter_country')?>
<table class="result" id="code_vatex_tb">
    <thead>
    <td>
        <?= _("Code") ?>
    </td>
    <td>
        <?= _("Pays") ?>
    </td>
    <td>
        <?= _("Article") ?>
    </td>
    <td>
        <?= _("Description") ?>
    </td>
</thead>
<tbody>

    <?php
    for ($i = 0; $i < $nb_vatex_code; $i++):
        $odd= ($i % 2 == 0) ? " odd " : " even ";
        $cnt=sprintf(" country%s",$a_vatex_code[$i]['vx_country']);
        $class=" $odd $cnt ";
        ?>
        <tr class="<?= $class ?>">
            <td>
                <a href="javascript:void(0)" onclick="vat_code.select_value('<?= $a_vatex_code[$i]['vx_code'] ?>')" class="mtitle line">
                    <?= $a_vatex_code[$i]['vx_code'] ?>
                </a>
            </td>
            <td>
                <?= $a_vatex_code[$i]['vx_country'] ?>
            </td>
            <td>
                <?= $a_vatex_code[$i]['vx_code_name'] ?>
            </td>
            <td>
                <?= $a_vatex_code[$i]['vx_description'] ?>
                <span class="text-muted">
                    <?= $a_vatex_code[$i]['vx_remark'] ?>
                </span>
            </td>

        </tr>
        <?php
    endfor;
    ?>
</tbody>
</table>
<ul class="aligned-block">
    <li>
        <?= \HtmlInput::button_close($dgbox) ?>
    </li>
</ul>
</div>