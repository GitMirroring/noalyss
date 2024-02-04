<?php

//This file is part of NOALYSS and is under GPL 
//see licence.txt
$uniq=uniqid("tab",TRUE);
$dossier_id=Dossier::id();
?>
<div>
    <?php
    \Noalyss\Dbg::echo_file(__FILE__);
    ?>
<div class="row m-0">

<div class="col">
    <table>
        <tr class="highlight">
            <TD>
	    <?php echo _('N° document')?>
            </TD>
            <TD >
              <?php echo $this->ag_id;?>
            </TD>
          </TR>
			 <tr>
            <TD>
	    <?php echo _('Reference')?>
            </TD>
            <TD>
              <?php echo $str_ag_ref;
              ?>
            </TD>
          </TR>
   <tr>
            <TD>
	    <?php echo _('Type')?>
            </TD>
            <TD>
              <?php echo $str_doc_type;
              ?>
            </TD>
          </tr>
        <tr>

	<tr>
          <TD>
	    <?php echo _('Destinataire')?>
          </TD>
          <TD>
  <?php echo $w->search().$w->input();
            ?>
          </td>
        </tr>
        <tr class="font-weight-bolder" style="background: lightsteelblue;color:navy">
            <td style="width: auto" colspan="2" >
                <?=$sp->input()?>
            </td>
          </Tr>
	<tr>
          <TD>
	  <?php echo _('Contact')?>
          </TD>

        <TD>
  <?php 
  if  ($g_user->can_write_action($this->ag_id) == true ):
        if ( $ag_contact->extra != "" ):
        echo $ag_contact->search().$ag_contact->input();
      else:
          echo _('Pas de catégorie de contact');
      endif;
  endif;
  
            ?>
          </td>
    </tr>
        <tr class="font-weight-bolder" style="background: lightsteelblue;color:navy;opacity: 0.8">
            <td style="width: auto" colspan="2">
                <?php //nom autre contact
                echo $spcontact->input();
                ?>
            </td>

        </Tr>
<?php 
//----------------------- Video Conf --------------------------------------------------------------------------------
if (Document_Option::is_enable_video_conf($this->dt_id)):?>          
          <tr>
              <td><?=_("VideoConf")?></td>
              <td><A href="<?=Document_Option::option_video_conf($this->dt_id)?>" target="_blank">
    <?=_("Salle de réunion")?>
                  </a>
          </tr>
<?php endif;?>          
	<tr>
          <TD colspan="2">
             <?php echo $spcontact->input(); ?>
          </td>
          </Tr>
<?php 
//----------------------- Contact Multiple ----------------------------------------------------------------------------
if ($this->ag_id > 0 && Document_Option::is_enable_contact_multiple($this->dt_id)): 
    ?>
          <tr>
              <td>
                  <?php echo _('Autres concernés')?>
              </td>
              <td id="concerned_card_td">
              <?php 
                    $followup_other_concerned=new Follow_Up_Other_Concerned($this->db,$this->ag_id);
                    echo $followup_other_concerned->display_linked_count();
                     if  ($p_view != 'READ' && $g_user->can_write_action($this->ag_id) == true ):
                        echo $followup_other_concerned->button_action_add_concerned_card();
                     endif;
               ?>
              </td>
              <td>
                    <?php
                    $csv="export.php?".
                            http_build_query(["gDossier"=>Dossier::id(),
                                "act"=>"CSV:FollowUpContactOption",
                                "ag_id"=>$ag_id]);
                    echo HtmlInput::anchor(_("Export CSV"), $csv,"",' class="line" title="Export Contacts options"');
                    ?>
              </td>
          </tr>
          <?php endif; ?>
        </table>
 <?php if ($p_view != 'READ') echo $str_add_button;?>
</div>


<div class="col">
        <table>

         
            <TD>
   <?php echo _('Date')?>
            </TD>
            <TD>
              <?php echo $date->input();
              ?>
            </TD>
          </TR>
          <TR>
            <TD>
	    <?php echo _('Heure')?>
            </TD>
            <TD>
              <?php echo $str_ag_hour;
              ?>
            </TD>
          </TR>
          <tr>
		<TR>
            <TD>
	    <?php echo _('Date limite')?>
            </TD>
            <TD>
              <?php echo $remind_date->input();
              ?>
            </TD>
          </TR>
          <tr>
            <TD>
	    <?php echo _('Etat')?>
            </TD>
            <td>
              <?php echo $str_state;
              ?>
            <TD>
            </TD>
          </TR>
          <tr>
            <TD>
	    <?php echo _('Priorité')?>
            </TD>
            <td>
              <?php echo $str_ag_priority;
              ?>
            <TD>
            </TD>
          </TR>
          <tr>
            <TD>
	    <?php echo _('Groupe Gestion')?>
            </TD>
            <td>
              <?php echo $str_ag_dest;?>
          </tr>
<?php if ($this->ag_id > 0 ): ?>
          <tr>
            <TD>
                Dossier / Etiquette
            </TD>
            
            <td id="action_tag_td" style="max-width:35rem">
                <?php
                   $this->tag_cell($p_view);
                ?>
            </td>
          </TR>
<?php endif; ?>          
        </table>

</div>

<div id="choice_other_info_div" class="col-12 col-lg-6">
    <div class="row">

    <ul class="tabs noprint" >
        <li id="related_action_tab<?php echo $uniq?>" class="tabs_selected"><?php echo _("Actions concernées")?></li>
        <li id="related_operation_tab<?php echo $uniq?>" class="tabs"><?php echo _('Opérations concernées')?></li>
        <li id="dependant_action_tab<?php echo $uniq?>" class="tabs"><?php echo _('Dépendant')?></li>
    </ul>
    </div>
    <div class="row">
	<div id="related_operation_div<?php echo $uniq?>" style="display:none;" class="print concerned_action">
        <?php if ($p_view != 'READ')   echo '<span class="noprint">'.$iconcerned->input().'</span>';?>
		<ol>

		<?php
		for ($o=0;$o<count($operation);$o++)
		{
			if ( $p_view != 'READ')
				{
                                        $js  = HtmlInput::button_action_remove_operation($operation[$o]['ago_id']);
					echo '<li id="op'.$operation[$o]['ago_id'].'">'.$operation[$o]['str_date']." ".HtmlInput::detail_op($operation[$o]['jr_id'],$operation[$o]['jr_internal'])." ".h($operation[$o]['jr_comment'])." "
						.$js.'</li>';
				}
				else
				{
					echo '<li >'.$operation[$o]['str_date']." ".HtmlInput::detail_op($operation[$o]['jr_id'],$operation[$o]['jr_internal'])." ".h($operation[$o]['jr_comment'])." "
						.'</li>';
				}
		}
               
		

		?>
		</ol>

	</div>
        <div id="related_action_div<?php echo $uniq?>" class="print concerned_action"  style="width: 95%" >
            <div class="" style="display:block">
                <div>

                <?php if ( $p_view != 'READ') echo $iaction->input();?>
                <?=\Icon_Action::detail('SmallDetailActionChildren'.$uniq.'_bt', "$('related_action_div{$uniq}').addClassName('FullDetailActionChildren');$('FullDetailActionChildren{$uniq}_bt').show();$('SmallDetailActionChildren{$uniq}_bt').hide()")?>
                <?php
                echo \Icon_Action::hide_icon("FullDetailActionChildren{$uniq}_bt"
                    , "$('related_action_div{$uniq}').removeClassName('FullDetailActionChildren');$('FullDetailActionChildren{$uniq}_bt').hide();$('SmallDetailActionChildren{$uniq}_bt').show()");?>
                </div>
                <?php
                $this->display_children($p_view,$p_base);

                ?>
                <script>$('FullDetailActionChildren<?=$uniq?>_bt').hide()</script>
            </div>
	</div>
        <div id="dependant_action_div<?php echo $uniq?>" style="display:none" class="print concerned_action">
        <?php
        //@todo a enlever obsolete
            $this->display_parent($p_view,$p_base);
        ?>
        </div>
    </div>
</div>

</div>
</div>
<div style="clear: both"></div>
<div id="div_action_description">
  
  <p>
<script language="javascript">
   function enlarge(p_id_textarea){
   $(p_id_textarea).style.height=$(p_id_textarea).style.height+250+'px';
   $('bt_enlarge').style.display="none";
   $('bt_small').style.display="inline";
 }
function small(p_id_textarea){
   $('bt_enlarge').style.display="inline";
   $('bt_small').style.display="none";

   }
</script>
    <p style="margin-left:5%;">
    <?php echo $title->input();
    ?>
</p>
    <div class="nicEdit-main">
        <?php 
/**********************************************************************************************************************
 * Start BLOCK Comment and description
 **********************************************************************************************************************/
?>
   <?php
   $style_enl='style="display:inline"';$style_small='style="display:none"';
   // description
   $description = new ITextarea("ag_description");
   $description->set_enrichText("enrich");
   $description->id="ag_description";
   $has_description = false;
    //---------------------------------- Description -------------------------------------------------------------------
    // if there are comments then the first one is the description
    if ( count($acomment)> 0) {
            $has_description = true;
            $editable_description = Document_Option::is_enable_editable_description($this->dt_id);
            // if editable and the description can be changed
            if ( $p_view != 'READ' && $editable_description == true){
                echo h2(_("Description"));
                $itDescription=new ITextarea("ag_description");
                $itDescription->style='class="input_text field_follow_up" style="height:21rem;width:98%;margin-left:1em;"';
                $itDescription->set_enrichText('enrich');
                $ag_description_id= $acomment[0]['agc_id'];
                $itDescription->value=$acomment[0]['agc_comment_raw'];
                $itDescription->id="ag_description";
                echo \HtmlInput::hidden("ag_description_id",$acomment[0]['agc_id']);

                echo $itDescription->input();

            }
            elseif ($p_view == 'READ' || $editable_description == false)
            {
                echo h2(_("Description"));
                echo '<div class="nicEdit-main field_follow_up" style="margin-left:5%;margin-bottom:1rem;width:90%;">';
                echo $acomment[0]['agc_comment_raw'];
                echo '</div>';
                echo '</pre>';
            }
    } elseif ( count($acomment)> 0 && $p_view != 'READ') {
        // there is no comments so there is no description, just input one
          echo h2(_("Description"));
          $description->set_enrichText('enrich');
          $description->style='style="height:250px;width:90%;"';
          echo '<div class="textarea" style="margin-left:5%;margin-right:5%;margin-bottom:1rem;width:90%;">';
          echo $description->input();
          echo '</div>';
   } elseif (  $p_view != 'READ' ) {
        // no description and no comment it is a new document

          echo h2(_("Description"));
          $description->set_enrichText('enrich');
          $description->style='style="height:250px;width:90%;"';
          echo '<div class="textarea" style="margin-left:5%;margin-right:5%;margin-bottom:1rem;width:90%;">';
          echo $description->input();
          echo '</div>';
    }
        // link to files to download
        $aFile=$this->db->get_array('select d_id,d_filename,d_description,d_mimetype
                from  action_comment_document 
                join document  on (d_id=document_id) where action_gestion_comment_id=$1'
            , array($this->ag_id));
        if ( ! empty ($aFile)) {
            echo '<div style="left:10%">';
            echo _("Fichiers :");
            foreach ($aFile as $file)
            {
                $url="export.php?".http_build_query(array("act"=>'RAW:document'
                    ,"gDossier"=>$dossier_id
                    ,"d_id"=>$file["d_id"]));
                printf('<a class="print_line" href="%s">%s</a>',
                    $url,h($file['d_filename']));

            }
            echo '</div>';
        }

        //---------------------------------- Comment -----------------------------------------------------------------------
   
   if (    Document_Option::can_add_comment($ag_id)  && 
           Document_Option::option_comment($this->dt_id) == "ONE_EDIT" ) 
   {
        if (count($acomment) > 1 )  {
            echo h2(_("Commentaire"));
            $comment=new ITextarea("ag_comment_edit");
            $comment->set_enrichText("plain");
            $comment->style='class="input_text field_follow_up" style="height:21rem;width:98%;margin-left:5%;"';

            $ag_comment_id= (count($acomment) > 1)?$acomment[1]['agc_id']:-1;
            $comment->value=(count($acomment) > 1 )?$acomment[1]['agc_comment']:'';
            $comment->id="ag_comment_edit";

            if ( $p_view != 'READ') {

                // One editable comment is available
                $editable_comment=new Inplace_Edit($comment);
                $editable_comment->add_json_param("op", "followup_comment_oneedit");
                $editable_comment->add_json_param("agc_id", $ag_comment_id);
                $editable_comment->add_json_param("ag_id", $ag_id);
                $editable_comment->add_json_param("gDossier", Dossier::id());
                $editable_comment->set_callback("ajax_misc.php");
                echo '<p></p>';
                echo $editable_comment->input();
            } else {
                echo '<p></p>';
                echo $comment->display();
            }
        } else {
            // new comment
            echo '<span class="noprint">';
            if (  $p_view == 'UPD' &&  $has_description && Document_Option::can_add_comment($ag_id) )  {
                echo h2(_("Commentaire"));
                echo '<p></p>';
                // if comment are editable it will be in
                if ( Document_Option::option_comment($this->dt_id) == "ONE_EDIT") $desc->set_enrichText("plain");
                echo $desc->input();

            }
            echo '</span>';
        }
   }

    if (  count($acomment) > 0
            &&  Document_Option::can_add_comment($ag_id)
            && Document_Option::option_comment($this->dt_id) == "SOME_FIXED")
    {
        echo h2(_("Commentaire"));

        for( $c=1;$c<count($acomment);$c++){
            $m_desc=_('Commentaire');
             $comment="";
             if ( $p_view != 'READ' && $c > 0)
            {
                $rmComment=sprintf("return confirm_box(null,'"._('Voulez-vous effacer ce commentaire').
                        " ?',function() {remove_comment('%s','%s');});",
                                                dossier::id(),
                                                $acomment[$c]['agc_id']);
                $js=Icon_Action::trash("accom".$acomment[$c]['agc_id'], $rmComment);
                $comment= h($m_desc.' '.$acomment[$c]['agc_id'].'('.$acomment[$c]['tech_user']." ".
                        $acomment[$c]['str_agc_date'].')').$js.
                                '<div class="nicEdit-main field_follow_up" style="margin-left:5%;margin-bottom:auto;width:90%" id="com'.$acomment[$c]['agc_id'].'"> '.
                                " ".$acomment[$c]['agc_comment_raw'].'</div>'
                                ;

            }
            else
            {
                    $comment=h($m_desc.' '.$acomment[$c]['agc_id'].'('.$acomment[$c]['tech_user']." ".
                            $acomment[$c]['str_agc_date'].')').
                                    '<div class="field_follow_up" id="com'.$acomment[$c]['agc_id'].'"> '.
                                    " ".$acomment[$c]['agc_comment_raw'].'</div>'
                                    ;


            }
            $comment=preg_replace('/ #([0-9]+)/','  <a class="line" href="javascript:void()" onclick="view_action(\1,'.
                    Dossier::id().',0)" >\1</a>',$comment);
            echo '<p></p>';
            echo $comment;
            // link to files to download
            $aFile=$this->db->get_array('select d_id,d_filename,d_description,d_mimetype
                from  action_comment_document 
                join document  on (d_id=document_id) where action_gestion_comment_id=$1'
                , array( $acomment[$c]['agc_id']));
            if ( ! empty ($aFile)) {
                echo '<div style="left:10%">';
                echo _("Fichiers :");
                foreach ($aFile as $file)
                {
                    $url="export.php?".http_build_query(array("act"=>'RAW:document'
                            ,"gDossier"=>$dossier_id
                        ,"d_id"=>$file["d_id"]));
                    printf('<a class="print_line" href="%s">%s</a>',
                    $url,h($file['d_filename']));

                }
                echo '</div>';
            }

        } // end for
        if (  $has_description &&  $p_view == 'UPD' && Document_Option::can_add_comment($ag_id))  {
            	echo '<span class="noprint">';
            	echo '<div style="margin-left:5%;margin-top:2.5rem">';
                echo $desc->input();
		echo '</div>';
            }
            echo '</span>';
            if  ($p_view == 'UPD') {

        }
    }
   

    

?>

  </div>
</div>
<?php 
/**********************************************************************************************************************
 * START BLOCK Display Detail of follow up
 *
 **********************************************************************************************************************/
?>
<?php
// Display detail if detail_operation is set
if ( $this->ag_id > 0 && Document_Option::is_enable_operation_detail($this->dt_id)) Follow_Up_Detail::display($this,$p_view);

?>
<?php 
/**********************************************************************************************************************
 * END BLOCK Display Detail of follow up
 **********************************************************************************************************************/
?>

<div style="clear:both"></div>
<?php if ($p_view != 'READ') : ?>
    <div class="noprint">
        <h3 >Fichiers à ajouter: </h3>
        <ol id='add_file'  >
            <li>
                <?php echo $upload->input();
                ?>

                <?php
                $js="document.getElementById('add_file').removeChild(this.parentNode)";
                echo Icon_Action::trash(uniqid(),$js);
                ?>
            </li>
        </ol>
        <span   >
 <input type="button" class="smallbutton"   onclick="addFiles();" value="<?php echo _("Ajouter un fichier")?>">
  </span>
<script language="javascript">
function addFiles() {
try {
	docAdded=document.getElementById('add_file');
	new_element=document.createElement('li');
	new_element.innerHTML='<input class="inp" type="file" value=""  multiple name="file_upload[]"/>';

    new_element.innerHTML+='<span id="<?=uniqid("file")?>" onclick="document.getElementById(\'add_file\').removeChild(this.parentNode)" class="icon">&#xe80f;</span>';



	docAdded.appendChild(new_element);
}
catch(exception) { alert('<?php echo j(_('Je ne peux pas ajouter de fichier'))?>'); alert(exception.message);}
}
</script>
    </div>
<?php endif;?>

<?php if (($p_view != 'READ' && $str_select_doc != '' )|| ! empty ($aAttachedFile)) : ?>
<div  id="div_action_attached_doc">
  <h2>
     <?php
     echo _('Pièces attachées');
     printf("(%s)",count($aAttachedFile));
     ?>
  </h2>
    <div class="noprint">
        <?php 
/**********************************************************************************************************************
 * start BLOCK generate document
 **********************************************************************************************************************/
?>

 <?php if ($p_view != 'READ' && $str_select_doc != '') : ?>
         <?php echo _('Document à générer')?>

  <?php echo $str_select_doc;
 echo $str_submit_generate;

endif; ?>
  
<?php 
/**********************************************************************************************************************
 * end BLOCK generate document
 **********************************************************************************************************************/
?>
    </div>
    <?php
    if ( ! empty ($aAttachedFile)) :
 ?>
    <div id="icon_show_file_div_id<?=$uniq?>">
    <?php
    /** Start Block Document **/
    printf ("Voir tous les fichiers");

    echo \Icon_Action::show_icon(uniqid(), "$('all_attached_files_div{$uniq}').show();$('icon_show_file_div_id{$uniq}').hide()");
    ?>
    </div>
  <div class="print" style="display: none" id="all_attached_files_div<?=$uniq?>">
      <?php
      echo "Cacher les fichiers";
      echo \Icon_Action::hide_icon(uniqid(), "$('all_attached_files_div{$uniq}').hide();$('icon_show_file_div_id{$uniq}').show()")
      ?>
      <table>
  <?php
for ($i=0;$i<sizeof($aAttachedFile);$i++) :
  ?>

      <tr>
          <td>
              <A class="print line" style="display:inline" id="<?php echo "doc".$aAttachedFile[$i]['d_id'];?>" href="<?php echo $aAttachedFile[$i]['link']?>">
          <?php echo $aAttachedFile[$i]['d_filename'];?>         </a>
          </td>
          <td>
        <label> : </label>
        <?php
        // Description of the file
        if ($p_view != 'READ') :
            $description=new IText("value");
            $description->id="input_desc_txt".$aAttachedFile[$i]['d_id'];
            $description->value=h($aAttachedFile[$i]['d_description']);
            $inplace_description=new Inplace_Edit($description);
            $inplace_description->set_callback("ajax_misc.php");
            $inplace_description->add_json_param("d_id", $aAttachedFile[$i]['d_id']);
            $inplace_description->add_json_param("gDossier", Dossier::id());
            $inplace_description->add_json_param("op", "update_comment_followUp");
            echo $inplace_description->input();
        else:
                echo h($aAttachedFile[$i]['d_description']);
        endif;
        ?>
<?php $rmDoc=sprintf("return confirm_box(null,'"._('Voulez-vous effacer le document')." %s' , function(){remove_document('%s','%s');});",
	$aAttachedFile[$i]['d_filename'],
	dossier::id(),
	$aAttachedFile[$i]['d_id']);
    ?>
        </td>
        <td>
  <?php if ($p_view != 'READ') : ?>  <span class="icon"  id="<?php echo "ac".$aAttachedFile[$i]['d_id'];?>" href="javascript:void(0)" onclick="<?php echo $rmDoc;?>">&#xe80f;</span><?php endif;?>
        </td>
  </tr>
  <?php
endfor;

  ?>
  </table>

      </div>
          <div>
<?php
    /*** Propose to download all document in only one step */
    $url="export.php?".http_build_query([ 
        'ac'=>"FOLLOW",
        "act"=>"RAW:document",
        "gDossier"=>Dossier::id(),
        "d_id"=>0,
        "ag_id"=>$this->ag_id,
        "a"=>"dwnall"]);
    echo HtmlInput::button_anchor(_("Télécharger toutes les documents"), "", uniqid(),sprintf("onclick=\"download_document('%s')\"",$url));
    
endif;?>
  </div>

</div>
<?php endif;?>
<?php if  ($p_view != 'NEW') :  ?>
Document créé le <?php echo $this->ag_timestamp ?> par <?php echo $this->ag_owner?>
<?php endif; ?>

</div>

<script>
  $('related_action_tab<?php echo $uniq?>').onclick=function() {
      $('related_action_tab<?php echo $uniq?>').className='tabs_selected';
      $('related_operation_tab<?php echo $uniq?>').className='tabs';
      $('dependant_action_tab<?php echo $uniq?>').className='tabs';
      $('related_operation_div<?php echo $uniq?>').hide();
      $('dependant_action_div<?php echo $uniq?>').hide();
      $('related_action_div<?php echo $uniq?>').show();
  }  ;
  $('related_operation_tab<?php echo $uniq?>').onclick=function() {
      $('related_operation_tab<?php echo $uniq?>').className='tabs_selected';
      $('related_action_tab<?php echo $uniq?>').className='tabs';
      $('dependant_action_tab<?php echo $uniq?>').className='tabs';
      $('related_action_div<?php echo $uniq?>').hide();
      $('dependant_action_div<?php echo $uniq?>').hide();
      $('related_operation_div<?php echo $uniq?>').show();
  }  ;
    $('dependant_action_tab<?php echo $uniq?>').onclick=function() {
      $('dependant_action_tab<?php echo $uniq?>').className='tabs_selected';
      $('related_action_tab<?php echo $uniq?>').className='tabs';
      $('related_operation_tab<?php echo $uniq?>').className='tabs';
      $('related_operation_div<?php echo $uniq?>').hide();
      $('related_action_div<?php echo $uniq?>').hide();
      $('dependant_action_div<?php echo $uniq?>').show();
  }  ;
</script>
