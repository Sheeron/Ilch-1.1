<?php
defined ('main') or die ( 'no direct access' );
defined ('admin') or die ( 'only admin access' );

$design = new design ( 'Admins Area', 'Admins Area', 2 );
$design->header();
$tpl = new tpl ( 'trains', 1);

if(!empty($_POST['send'])){
	$mon=str_replace('#','',$_POST['mon']);
	$die=str_replace('#','',$_POST['die']);
	$mit=str_replace('#','',$_POST['mit']);
	$don=str_replace('#','',$_POST['don']);
	$fre=str_replace('#','',$_POST['fre']);
	$sam=str_replace('#','',$_POST['sam']);
	$son=str_replace('#','',$_POST['son']);
	$new=$mon.'#'.$die.'#'.$mit.'#'.$don.'#'.$fre.'#'.$sam.'#'.$son;
	db_query("UPDATE `prefix_allg` SET t1 = '".$new."' WHERE k = 'trainzeiten'");
	wd('?trains','Daten erfolgreich ge�ndert',2);
} else {
	$row = db_fetch_object(db_query("SELECT t1 FROM `prefix_allg` WHERE k = 'trainzeiten'"));
	$dbe=explode('#',$row->t1);
	$ar = array (
	'MON' => $dbe[0],
	'DIE' => $dbe[1],
	'MIT' => $dbe[2],
	'DON' => $dbe[3],
	'FRE' => $dbe[4],
	'SAM' => $dbe[5],
	'SON' =>$dbe[6]
	);
	$tpl->set_ar_out($ar,0);
}
$design->footer();
?>