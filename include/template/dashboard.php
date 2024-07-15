<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
?><!-- left div -->
<div class="row" style="margin-right: 0px;margin-left:0px">
<div id="calendar_box_div" class="box">
<?php echo HtmlInput::title_box(_('Calendrier'),'cal_div','zoom',"calendar_zoom($obj)",'n');?>
<?php echo $cal->display('short',0); ?>
</div>

<div id="todo_listg_div" class="box"> <?php echo HtmlInput::title_box(_('Pense-Bête'),"todo_listg_div",'zoom',"zoom_todo()",'n')?>

<?php
/*
 * Todo list
 */
echo dossier::hidden();
$todo=new Todo_List($cn);
$array=$todo->load_all();
$a_todo=Todo_List::to_object($cn,$array);

echo HtmlInput::button('add',_('Ajout'),'onClick="add_todo()"','smallbutton');
  echo '<table id="table_todo" class="sortable" width="100%">';
  echo '<tr><th class=" sorttable_sorted_reverse" id="todo_list_date">Date</th><th>Titre</th><th></th>';
if ( ! empty ($array) )  {
  $nb=0;
  $today=date('d.m.Y');

  foreach ($a_todo as $row) {
    if ( $nb % 2 == 0 ) $odd='odd '; else $odd='even ';
    $nb++;
    echo $row->display_row($odd);
  }
}
  echo '</table>';
?>
</div>

<div id="situation_div" class="box"> 
  <?=Status_Operation_Event::main_display($cn)?>
</div>

<!-- Mini rapport -->
<?php
/*
 * Mini Report
 */
$report=$g_user->get_mini_report();

$rapport=new Acc_Report($cn,$report);

if ( $rapport->exist() == false ) {
  $g_user->set_mini_report(0);
  $report=0;
}

if ( $report != 0 ) : ?>
<div id="report_div" class="box"><?php echo HtmlInput::title_box($rapport->get_name(),'report_div','none','','n');?>
<?php    
  $exercice=$g_user->get_exercice();
  if ( $exercice == 0 ) {
    alert(_('Aucune periode par defaut'));
  } else {
    $periode=new Periode($cn);
    $limit=$periode->limit_year($exercice);

    $result=$rapport->get_row($limit['start'],$limit['end'],'periode');
    $ix=0;
    if ( !empty ($result ) && count ($result) >  0)
    {
        echo '<table class="result">';
        foreach ($result as $row) {
          $ix++;
              $class=($ix%2==0)?' class="even" ':' class="odd" ';
          echo '<tr '.$class.'>';

          echo '<td> '.$row['desc'].'</td>';
            $style='style="text-align:right;"';
            if ($row['montant']<0) { $style='style="color:red;text-align:right;"';}
            echo  "<td $style>".nbm($row['montant'])."</td>";
          echo '</tr>';
        }
        echo '</table>';
    } else {
        echo _('Aucun résultat');
    }
  }
  ?>
  </div>
<?php
  else :
?>
  <div id="report_div" class="box"> <?php echo HtmlInput::title_box(_('Aucun rapport défini'),'report_div','none','','n')?>
<p>
  <a href="javascript:void(0)" class="cell" onclick="set_preference('<?php echo dossier::id()?>')"><?php echo _('Cliquez ici pour mettre à jour vos préférences')?></a>
<p>
</div>
<?php
endif;
?>





<div id="last_operation_box_div" class="box">
<?php echo HtmlInput::title_box(_('Dernières opérations'),"last_operation_box_div",'zoom','popup_recherche('.dossier::id().')','n')?>

<table class="result" >
<?php
$Ledger=new Acc_Ledger($cn,0);
$last_ledger=array();
$last_ledger=$Ledger->get_last(20);

for($i=0;$i<count($last_ledger);$i++):
	$class=($i%2==0)?' class="even" ':' class="odd" ';
?>
<tr <?php echo $class ?>>
	<td class="box">
            <?php echo   smaller_date($last_ledger[$i]['jr_date_fmt'])?>
	</td>
	<td class="box">
		<?php echo $last_ledger[$i]['jr_pj_number']?>
            
        </td>
<td class="box">
   <?php echo h(mb_substr($last_ledger[$i]['jr_comment']??"",0,40,'UTF-8'))?>
</td>
<td class="box">
<?php echo HtmlInput::detail_op($last_ledger[$i]['jr_id'], $last_ledger[$i]['jr_internal'])?>
</td>
<td class="num box">
<?php echo nbm($last_ledger[$i]['jr_montant'])?>
</td>

</tr>
<?php endfor;?>
</table>
    
</div>
<div id="last_operation_management_div" class="box">
    <?php 
     echo HtmlInput::title_box(_('Suivi'),"last_operation_management_div",'zoom','action_show('.dossier::id().')','n');
    ?>
    <?php
    $gestion=new Follow_Up($cn);
    $array=$gestion->get_last(MAX_ACTION_SHOW);
    $len_array=count($array);
    ?>
    <table class="result" >
    <?php
    for ($i=0;$i < $len_array;$i++) :
    ?>
        <tr class=" <?php echo ($i%2==0)?'even':'odd'?>">
            <td class="box">
                <?php echo smaller_date($array[$i]['ag_timestamp_fmt']) ;?>
            </td>
            <td class="box">
                <?php echo HtmlInput::detail_action($array[$i]['ag_id'], $array[$i]['ag_ref'], 1)  ?>
            </td>
            <td class="box">
                <?php echo mb_substr(h($array[$i]['quick_code']),0,15)?>
            </td>
            <td class="box cut">
                <?php echo h($array[$i]['ag_title'])?>
            </td>
        </tr>
    <?php
    endfor;
    ?>
    </table>
</div>
</div><!-- class="row" -->
<div id="add_todo_list" class="box" style="display:none">
	
<form method="post">
<?php
$wDate=new IDate('p_date_todo');
$wDate->id='p_date_todo';
$wTitle=new IText('p_title');
$wDesc=new ITextArea('p_desc');
$wDesc->heigh=5;
$wDesc->width=40;
echo HtmlInput::title_box(_("Note"),"add_todo_list","hide",'',"n");
echo _("Date")." ".$wDate->input().'<br>';
echo _("Titre")." ".$wTitle->input().'<br>';
echo _("Description")."<br>".$wDesc->input().'<br>';
echo dossier::hidden();
echo HtmlInput::hidden('tl_id',0);
echo HtmlInput::submit('save_todo_list',_('Sauve'),'onClick="Effect.Fold(\'add_todo_list\');return true;"');
echo HtmlInput::button('hide',_('Annuler'),'onClick="Effect.Fold(\'add_todo_list\');return true;"');
?>
</form>
</div>
</div>


