<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
 \Noalyss\Dbg::echo_var(1,"LEVEL MENU IS {$level}    access_code {$access_code}");


?><div class="">
    <?php 
  if ($level == 0) {
      echo '<ul  class="nav nav-pills nav-fill  flex-row noprint" >';
  }elseif ($level == 1) {
      echo '<ul class="nav nav-pills nav-level2 noprint">';
      
  } else {
      echo '<ul class="nav nav-pills nav-level3 noprint">';
  }
   ?>
   

	<?php
	global $g_user;
	// Display the menu
        $class="nav-item ";
	for($i=0;$i < count($amenu);$i++):
	    if ( (count($amenu)==1)) {
?>
	<li class="<?php echo $class?>">
            <a class="nav-link active" href="do.php?gDossier=<?php echo Dossier::id()?>&ac=<?php echo $access_code?>" title="<?php echo h(gettext($amenu[$i]['me_description']))?>" >
	    <?php echo gettext($amenu[$i]['me_menu'])?>
	    </a>
	</li>
<?php 
            }
	    else {
                    $js="";
                    $class_list_element="nav-item nav-item-underline";
                    $class_link="nav-link";

                    if ( $amenu[$i]['me_url']!='')
                    {
                            $url=$amenu[$i]['me_url'];
                    }
                    elseif ($amenu[$i]['me_javascript'] != '')
                    {
                            $url="javascript:void(0)";
                            $js=sprintf(' onclick="%s"',$amenu[$i]['me_javascript']);
                    }
                    else
                    {
                        $a_request=explode('/', $access_code);
                        if ( isset($a_request [$level+1]) && $a_request[$level+1]==$amenu[$i]['me_code']) {
                                    $class_link="nav-link active";
                                    $class_list_element="nav-item li-active";
                        }
                        if ( $level == 0) {
                            $url=$a_request[0];

                        } elseif ($level == 1)
                        {
                            $url=$a_request[0].'/'.$a_request[1];
                                                     
                        }
                        elseif ($level == 2)
                        {
                            $url=$a_request[0].'/'.$a_request[1].'/'.$a_request[2];
                                                     
                        }
                        $url.='/'.$amenu[$i]['me_code'];
                        if ($url == $access_code ) {  $class="nav-link active"; }
                        $url="do.php?gDossier=".Dossier::id()."&ac=".$url;
                    }
                    

?>	
<li class="<?=$class_list_element?>">
    <a class="<?=$class_link?>" href="<?php echo $url;?>" <?php echo $js?> title="<?php  if ( ! empty($amenu[$i]['me_description'])) echo h(gettext($amenu[$i]['me_description']))?>">
    <?php if ( ! empty($amenu[$i]['me_menu'])) echo gettext($amenu[$i]['me_menu'])?>
    </a>
</li>


<?php 
     } // end  elseif ($level==0) 

	?>
	<?php 
	    endfor;
    	?>


</ul>
</div>
