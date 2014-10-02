<?php
/******************************************************
*
*	Fichier appeler juste par un clic
*	pour effectuer le payerment, se conferer a la page
*	attente.php	
*
*******************************************************/
require_once("../includes/commun_inc.php");
if(is_autorized("PAYER_FRAIS")){
	if(isset($_GET['id'])){
		try{
			$pdo = Database::connect2db();
			$table = "";
			if(intval($_GET['type']) == 0)
				$table = 'frais_apayer';
			else
				$table = "reduction_obtenue";
			/* Update la table selon le type envoyee */
			$res = $pdo->prepare("UPDATE $table SET STATUT = IF(STATUT = 1, '0', '1'), DATEOP = :dateop WHERE ID = :id");
			$res->bindValue('id', $_GET['id'], PDO::PARAM_INT);
			$res->bindValue('dateop', date('Y-m-d', time()), PDO::PARAM_STR);
			$res->execute();
			@header("Location:attente.php?matel=".$_GET['matel']);
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
		}
	}
}else{
	print "<p class='infos'>Vous n'&ecirc;tes pas autoris&eacute; d'acc&egrave;der &agrave; cette page</p>";
}
?>