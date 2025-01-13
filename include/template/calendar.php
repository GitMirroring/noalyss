<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt

/**
 * @var $p_type type of display inherited , long or short
 */
$cn=Dossier::connect();
$p=new \Periode($cn);
try {
    $today_periode=$p->find_periode(date('d.m.Y'));
    $today_hidden=\HtmlInput::hidden('today', $today_periode);
} catch (\Exception $e) {
    $today_hidden='';
}
?>
<div class="pc_calendar" id="user_cal" style="width:100%">
<?php echo $month_year?>
<?php 
    $js=sprintf("calendar_zoom({gDossier:%d,invalue:'%s',outvalue:'%s',distype:'%s','notitle':%d})",
            dossier::id(),'per_div','calendar_zoom_div','list',$notitle);
    echo HtmlInput::anchor(_('Liste'),''," onclick=\"{$js}\"")   ;
    echo HtmlInput::button_action_add();
    echo $today_hidden;
 ?>
    <?php if ($today_hidden != '') :?>
<script>$('today').gDossier="<?=Dossier::id()?>"
    $('today').type_display="<?=$p_type?>";

</script>
   <button class="smallbutton" onclick="change_month($('today'))"> <?=_("Aujourd'hui")?></button>
    <?php endif;?>


<?php if ($zoom == 1 ): ?>    
<table style="width:100%;height:70%">
    <?php else: ?>
<table style="width:100%;height:70%">
    <?php endif; ?>
<tr>
<?php
$nFirstDay=$g_user->get_first_week_day();
$nDay=$nFirstDay;
for ($i=0;$i<=6;$i++){
	echo "<th>";
	$nDay=($nDay>6)?0:$nDay;
	echo '<span class="d-none d-sm-block ">'.$week[$nDay].'</span>';
        echo '<span class=" d-block d-sm-none">'.substr($week[$nDay],0,2).'</span>';
	$nDay++;
	echo "</th>";
}
?>
</tr>
<?php
$ind=1;
$week=$nFirstDay;
$nCol=0;
$today_month=date('m');
$today_day=date('j');
$height=($zoom == 1)?"15vh":"3rem";
while ($ind <= $this->day) {
     if ($nCol==0)
    {
        echo "<tr style='height:{$height} '>";
    }
    $class="workday";
    if ($week==0||$week==6)
    {
        $class="weekend";
    }
    // compute the date
    $timestamp_date=mktime(0,0,0,$this->month,$ind,$this->year);
    $date_calendar=date('w',$timestamp_date);
    $st="";
    if ($today_month==$this->month&&$today_day==$ind)
    {
        $st='  style="border:2px solid darkblue;background-color:hsl(199, 60%, 95%);" ';
    }
    if ( $date_calendar == $week ) {
        echo '<td class="'.$class.'" '.$st.'>'.'<span class="day">'.$ind."</span>";
        echo '<span class="d-none d-sm-block ">'.$cell[$ind].'<span>';
        echo '<span class="d-block d-sm-none">'.$cell[$ind].'<span>';
        echo '</td>';
        $ind++;$week++;$nCol++;
    } else {
       echo "<td></td>";
       $week++;$nCol++;
    }
    //if ( $ind > $this->day ) exit();
    if ( $nCol == 7 ) { echo "</tr>";$nCol=0;}
    if ( $week == 7 ) { $week=0;}
}
if ( $nCol != 0 ) { echo "</tr>";}
?>

</table>
</div>
