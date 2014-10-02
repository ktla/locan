<?php
require_once("../includes/commun_inc.php");
if(is_autorized("ACTIF_CLASSE")){
	if(isset($_GET['id'])){
		try{
			$query = "UPDATE classe_parametre 
			SET ACTIF = IF(ACTIF = 1, '0', '1') 
			WHERE IDCLASSE = :idclasse AND PERIODE = :periode";
			$pdo = Database::connect2db();
			$res = $pdo->prepare($query);
			$res->execute(array(
				'periode' => $_SESSION['periode'],
				'idclasse' => $_GET['id']
			));
			@header("Location:classe.php");
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getFile()." : ".$e->getLine());
		}
	}
}else{
	print "<p class=\"infos\">Vous n'&ecirc;tes pas autoris&eacute; d'acc&egrave;der &agrave; cette page</p>";
}
?>