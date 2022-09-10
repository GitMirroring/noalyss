<?php 
//@description:IconAction , show all the possibilities for Icon_Action
$_GET=array (
  'gDossier' => '26'
);
$_POST=array (
  'gDossier' => '26'
);
$_POST['gDossier']=$gDossierLogInput;
$_GET['gDossier']=$gDossierLogInput;
 $_REQUEST=array_merge($_GET,$_POST);
include_once NOALYSS_INCLUDE."/lib/icon_action.class.php";

?>

<p>
   Cancel <?php echo Icon_Action::cancel(uniqid(), "alert('test')");?>
</p>
<p>
   icon_magnifier <?php echo Icon_Action::icon_magnifier(uniqid(), "alert('test')");?>
</p>
<p>
   icon_add <?php echo Icon_Action::icon_add(uniqid(), "alert('test')");?>
</p>
<p>
   clean_zone <?php echo Icon_Action::clean_zone(uniqid(), "alert('test')");?>
</p>
<p>
   infobulle <?php echo Icon_Action::infobulle(5);?>
</p>
<p>
   iconon <?php echo Icon_Action::iconon(uniqid(), "alert('test')");?>
</p>
<p>
   iconoff <?php echo Icon_Action::iconoff(uniqid(), "alert('test')");?>
</p>
<p>
   close <?php echo Icon_Action::close("close_span");?>
   <span id="close_span" class="inner_box">Close me !</span>
</p>
<p>
   draggable <?php echo Icon_Action::draggable("drag_span");?>
   <span id="drag_span" class="inner_box">Move me !</span>
</p>
<p>
   zoom <?php echo Icon_Action::zoom(uniqid(), "alert('test')");?>
</p>
<p>
   warnbulle <?php echo Icon_Action::warnbulle(1);?>
</p>
<p>
    
   hide <?php echo Icon_Action::hide("hide", "alert('test')");?>
</p>
<p>
    
   hide_icon <?php echo Icon_Action::hide_icon(uniqid(),"alert('test')");?>
</p>
<p>
   show_icon <?php echo Icon_Action::show_icon(uniqid(), "alert('test')");?>
</p>
<p>
   trash <?php echo Icon_Action::trash(uniqid(), "alert('test')");?>
</p>
<p>
   Cancel <?php echo Icon_Action::cancel(uniqid(), "alert('test')");?>
</p>
<p>
   modify <?php echo Icon_Action::modify(uniqid(), "alert('test')");?>
</p>
<p>
   validate <?php echo Icon_Action::validate(uniqid(), "alert('test')");?>
</p>

<p>
   more <?php echo Icon_Action::more(uniqid(), "alert('test')");?>
</p>
<p>
   less <?php echo Icon_Action::less(uniqid(), "alert('test')");?>
</p>

<p>
   Lock <?php echo Icon_Action::lock(uniqid(), "alert('test')");?>
</p>
<p>
   Unlock <?php echo Icon_Action::unlock(uniqid(), "alert('test')");?>
</p>
<p>
    Tips <?php echo Icon_Action::tips("Allo ?")?>
</p>
<p>
    Slider <?php echo Icon_Action::slider(uniqid(), "alert('test')")?>
</p>
<p>
    Comment<?php echo Icon_Action::comment("Allo ? l'heure d'été ?")?>
</p>
<p>
<div id="enlarge_it" class="inner_box">
    enlarge <?php echo Icon_Action::full_size("enlarge_it")?>
</div>
</p>
<p>
    Duplicate <?php echo Icon_Action::duplicate( "alert('test')")?>
</p>
<p>
    Card <?php echo Icon_Action::card( "alert('test')")?>
</p>
<p>
    Option <?php echo Icon_Action::option( "alert('test')")?>
</p>
<p>
    Increase INPUT TEXT Element
    <input type="text" id="text_element" size="20"><?php echo Icon_Action::longer("text_element",50)?>
</p>