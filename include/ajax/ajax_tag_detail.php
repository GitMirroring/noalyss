<?php
/**
 *@file
 * @brief display a window with the content of a tag
 */
//This file is part of NOALYSS and is under GPL 
//see licence.txt

if ( !defined ('ALLOWED') )  die('Appel direct ne sont pas permis');
ob_start();
$tag=new Tag($cn);
$http=new HttpInput();
$data=$tag->get_data();
$data->t_id=$http->get("tag","number");
if ($data->t_id == -1 &&  $g_user->check_action(TAGADD) == 0 ) {
    header('Content-type: text/xml; charset=UTF-8');
       $html=escape_xml(sprintf(
'%s
    <p class="error">
%s                   
</p>
%s
', HtmlInput::title_box(_("Etiquette"), "tag_div","close","","y")
               ,_("Désolé, vous n'êtes pas autorisé à créer des étiquettes")
               , HtmlInput::button_close("tag_div")
            ));
               
        echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl></ctl>
<code>$html</code>
</data>
EOF;
    return;
}
echo HtmlInput::title_box(_("Etiquette"), "tag_div","close","","y");
$data->load();
?>
<?php
// save via POST and reload page 
if ($_GET['form']=='p') :    ?>
    <form id="tag_detail_frm" method="POST" >
<?php 
/*
 * save via javascript and don't reload page
 */
else :
    ?>
    <form id="tag_detail_frm" method="POST" onsubmit="return save_tag();">
<?php        endif; ?>        
    <?php
    echo dossier::hidden();
    echo HtmlInput::hidden('t_id', $http->get('tag') );
    echo HtmlInput::hidden('ac',$http->get('ac'));


    require_once NOALYSS_TEMPLATE.'/tag_detail.php';
    echo HtmlInput::submit("save_tag_sb", "Valider");
    echo HtmlInput::button_close("tag_div");

    ?>
</form>
<?php
    $response=  ob_get_clean();
if (headers_sent() && DEBUGNOALYSS > 0 )    {
    echo $response;
}else {
    header('Content-type: text/xml; charset=UTF-8');
}
    $html=escape_xml($response);
    echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl></ctl>
<code>$html</code>
</data>
EOF;
    exit();
    ?>
