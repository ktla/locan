<?php
if(isset($_POST["file"]) && !empty($_POST["file"]) && $_POST["file"] != "" && $_POST["file"] != "undefined"){
	if(file_exists("./photos/".$_POST["file"]))
		unlink("./photos/".$_POST["file"]);
	$tab = explode(".",$_POST["file"]);
	$id = $tab[0];
	try{
		require_once("../includes/database_inc.php");
		$pdo = Database::connect2db();
		$pdo->exec("UPDATE eleve SET IMAGE = '' WHERE ID = ".$id);
	}catch(PDOException $e){
		die($e->getMessage()." ".$e->getLine()." ".__LINE__." ".__FILE__);

	}
}else{ 
		$dir = dir('./photos/tmp/'); // debut
		while( $nom = $dir->read() ) { // on supprime les images contenues dans logo
			unlink("./photos/tmp/".$nom);
		}
		$dir->close() ;
}
?>