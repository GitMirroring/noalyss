<?php
/**
 * @brief display table of contact, called from Contact::summary
 *
 */
// From calling script : $step_contact,$bar,$offset,$page
$cn=Dossier::connect();
$http=new HttpInput();
$from=$http->request("ac","string","");
?>
<?php echo $bar;?>
<table id="contact_tb" class="sortable">
    <TR>
        <th><?=_('Code')?></th>
        <th><?=_('Nom')?></th>
        <th><?=_('Prénom')?></th>
        <th><?=_('Société')?></th>
        <th><?=_('Mobile')?></th>
        <th><?=_('email')?></th>
        <th><?=_('Téléphone')?></th>
        <th><?=_('Fax')?></th>
    </TR>
    <?php
    $idx=0;
    $dossier_id=Dossier::id();
    $ac=$http->request('ac');
    foreach ($step_contact as $contact):
        $even_odd=($idx%2==0)?'even':'odd';
        $idx++;
        $url=NOALYSS_URL."/do.php?".http_build_query(["gDossier"=>$dossier_id,"ac"=>$ac,"sb"=>"detail","f_id"=>$contact['f_id']]);
    ?>
    <tr class="<?=$even_odd?>">
        <td>
            <?php if (  strpos($from,'CONTACT') !== false ):?>
            <a href="<?=$url?>">
            <?=h($contact['contact_qcode'])?>
            </a>
            <?php else :?>
            <?php
            $fiche=new Fiche($cn,$contact['f_id']);
            echo \HtmlInput::card_detail($contact['contact_qcode']);
            ?>
            <?php endif;?>
        </td>

        <td>
            <?=$contact['contact_name']?>
        </td>
        <td>
            <?=$contact['contact_fname']?>
        </td>
        <td>
            <?php

            /* detail for the company */
            if ( !empty ($contact['contact_company']) ) {
                $l_company=new Fiche($cn);
                $l_company->get_by_qcode(trim($contact['contact_company']),false);
                $l_company_name=$l_company->get_attribute(ATTR_DEF_NAME,0);

                // add popup for detail if the company does exist
                if ( $l_company_name !="")
                {
                    echo HtmlInput::card_detail($contact['contact_company'],$l_company_name,'style="text-decoration:underline;"');
                }
            }

            ?>
        </td>
        <td>
             <?=phoneTo($contact['contact_mobile'])?>
        </td>
        <td>
            <?php
            echo mailTo($contact['contact_email']);
            ?>
        </td>
        <td>
            <?=phoneTo($contact['contact_phone'])?>
        </td>
        <td>
            <?=FaxTo($contact['contact_fax'])?>
        </td>
    </tr>
    <?php endforeach;?>
</table>

<?php echo $bar;?>