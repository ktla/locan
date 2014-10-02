<?php
/***********************************************************
*
*	PAGE UTILISER POUR CHARGER L ID DU COMPTE PAR LE MORATOIRE
*	AUTOMATIQUEMENT UTILISANT AJAX
*
************************************************************/
require_once('../includes/commun_inc.php');
/* Obtenir le compte du correspondant */
if(isset($_GET['id']) && isset($_GET['action'])){
	$pdo = Database::connect2db();
	$res = $pdo->prepare('SELECT * FROM compte WHERE CORRESPONDANT = :matel');
	$res->execute(array(
		'matel' => $_GET['id']
	));
	$row = $res->fetch(PDO::FETCH_ASSOC);
	if(!isset($row['IDCOMPTE']) || empty($row['IDCOMPTE']))
		print format("Aucun compte associ&eacute;", 2);
	else
		print format($row['IDCOMPTE'], 2);
}
?>