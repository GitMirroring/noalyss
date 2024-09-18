<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
?>
<div id="top">
      <div id="dossier">
	<?php echo h(dossier::name())?>
	</div>
    <div style="clear:both;"></div>
    <div class="name">

<?php
$http=new HttpInput();
if ( $cn->get_value("select count(*) from profile join profile_user using (p_id)
		where user_name=$1 and with_calc=true",array($_SESSION[SESSION_KEY.'g_user'])) ==1):
  echo '<div id="calc">';
	echo IButton::show_calc();
echo '</div>';
endif;

// show search card 
if ( $cn->get_value("select count(*) from profile join profile_user using (p_id)
		where user_name=$1 and with_search_card=1",array($_SESSION[SESSION_KEY.'g_user'])) ==1):
    $search_card=new IText('card_search');
    $search_card->css_size='97%';
    $search_card_js=sprintf('onclick="boxsearch_card(\'%d\')"',dossier::id());
    echo Icon_Action::card( "$('box_search_card').show()");
    echo '<div id="box_search_card" style="display:none;width:20rem" class="inner_box">';
    echo HtmlInput::title_box(_("recherche"), "box_search_card","hide");
    echo _('Recherche de fiche');
    echo '<p class="info">';
   echo _("Donnez une partie du nom, prénom, de la description, du poste comptable, du n° de TVA,quick code ... "
           . " de la fiche" )    ;
    echo '</p>';
    echo '<p class="ml-1">';
    echo $search_card->input();
    echo '</p>';
    $create_card_js='onclick="select_card_type({});"';
    echo '<ul class="aligned-block">';
    
    echo    '<li>'.
            HtmlInput::button_anchor(_("Chercher"),"javascript:void(0)","",$search_card_js,'button').
            '</li>';

    echo    '<li>'.
        HtmlInput::button_anchor(_("Créer fiche"),"javascript:void(0)","",$create_card_js,'button').
        '</li>';
            
    echo    '<li>'. 
            HtmlInput::button_hide("box_search_card").
            '</li>';
    echo '</ul>';
    echo '</div>';
endif;


if ( $cn->get_value("select count(*) from profile join profile_user using (p_id)
		where user_name=$1 and with_direct_form=true",array($_SESSION[SESSION_KEY.'g_user'])) ==1):
?>
	<div id="direct">
	<form method="get" onsubmit="return document.getElementById('ac').value.trim()!='';">
		<?php echo $http->request('ac',"string", '')?>
		<?php echo Dossier::hidden()?>
		<?php 

			$direct=new IText('ac');
			$direct->style='class="input_text"';
            $direct->placeholder=_('Accès direct ou menu');
			$direct->value='';
			$direct->size=20;
			echo $direct->input();
			$gDossier=dossier::id();
			?>
		<div id="ac_choices" class="autocomplete" style="width:150px;z-index:1"></div>
		<?php 
			echo HtmlInput::submit('go',_('Aller'));
			?>

	</form>
	<script>

		try {
			new Ajax.Autocompleter("ac","ac_choices","direct.php?gDossier=<?php echo $gDossier?>",
                            {paramName:"acs",minChars:1,indicator:null,
                            callback:null,
                             afterUpdateElement:null});} catch (e){$('info_div').innerHTML=e.message;};
        </script>
	</div>
<?php 
endif;?>
	
    </div>

    <div id="module">
	<div class="d-none d-md-block">
	  <ul class="nav nav-pills nav-fill  flex-row" >
		<?php
		foreach ($amodule as $row):
			$js="";
		    $style="";
		    if ( $row['me_code']=='new_line')
		    {
                        echo '</ul>';
			echo '<ul class="nav nav-pills nav-fill  flex-row" >';
			continue;
		    }
                    $style="nav-item-module nav-item-slide";
		    if ($row['me_code']==$selected_module)
		    {
			$style='nav-item-active';
		    }
		    if ( $row['me_url']!='')
		    {
			$url=$row['me_url'];
		    }
		    elseif ($row['me_javascript'] != '')
			{
				$url="javascript:void(0)";
                                $js_dossier=noalyss_str_replace('<DOSSIER>', Dossier::id(), $row['me_javascript']);
				$js=sprintf(' onclick="%s"',$js_dossier);
			}
			else
		    {
				$url="do.php?gDossier=".Dossier::id()."&ac=".$row['me_code'];
		    }
		    ?>
		<li class="<?php echo $style?>">
                    <a class="nav-link" href="<?php echo $url?>" title="<?php echo _($row['me_description']??''); ?>" <?php echo $js?> ><?php echo gettext($row['me_menu'])?></a>
                </li>
		<?php 
		    endforeach;
		?>
          </ul>

    </div>
        <div class="d-md-none navbar-light"  >
            <button id="showmodule" class="navbar-toggler" onclick="toggleHideShow('navbarToggleExternalContent','showmodule')">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div  style="display:none;position:absolute;top:2px;left:2px;z-index:10" id="navbarToggleExternalContent">
                <ul class="nav nav-pills nav-fill  flex-column bg-dark" >
                    <?php
                    foreach ($amodule as $row):
                            $js="";
                        $style="";

                        $style="nav-item-module ";
                        if ( $row['me_code']=='new_line')
                        {
                       			continue;
                        }
                        if ($row['me_code']==$selected_module)
                        {
                            $style='nav-item-active';
                        }
                        if ( $row['me_url']!='')
                        {
                            $url=$row['me_url'];
                        }
                        elseif ($row['me_javascript'] != '')
                            {
                                    $url="javascript:void(0)";
                                    $js_dossier=noalyss_str_replace('<DOSSIER>', Dossier::id(), $row['me_javascript']);
                                    $js=sprintf(' onclick="%s"',$js_dossier);
                            }
                            else
                        {
                                    $url="do.php?gDossier=".Dossier::id()."&ac=".$row['me_code'];
                        }
                        ?>
                    <li class="<?php echo $style?>">
                        <a class="nav-link" href="<?php echo $url?>" title="<?php echo _($row['me_description']??"")?>" <?php echo $js?> ><?php echo gettext($row['me_menu'])?></a>
                    </li>
                    <?php 
                        endforeach;
                    ?>
              </ul>
            </div>
            
        </div>
  
</div>
</div>
<div style="clear:both;"></div>