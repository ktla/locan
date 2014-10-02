<?php 
if(isset($_POST["file"]) && !empty($_POST["file"]) && $_POST["file"] != "undefined"){
	if(file_exists("./curriculums/".$_POST["file"]))
		unlink("./curriculums/".$_POST["file"]);
	$tab = explode(".",$_POST["file"]);
	$id = $tab[0];
	try{
		require_once("../includes/database_inc.php");
		$pdo = Database::connect2db();
		$pdo->exec("UPDATE professeur SET CURRICULUM = '' WHERE ID = ".$id);
	}catch(PDOException $e){
		die($e->getMessage()." ".$e->getLine()." ".__LINE__." ".__FILE__);

	}
}else{
	$dir = dir('./curriculums/tmp/'); // debut
	while( $nom = $dir->read() ) { // on supprime les images contenues dans logo
		unlink("./curriculums/tmp/".$nom);
	}
	$dir->close();
	
	
}
?>