<?php 
require_once("../includes/commun_inc.php");
if(isset($_POST["montant"]) && !empty($_POST['montant'])){	
	$compte = $_POST["operation"];
	$periode = $_SESSION["periode"];
	$date = $_POST["date"];
	$auteur = $_SESSION["user"];
	$montant = $_POST["montant"];
	$libelle = $_POST["libelle"];

	/*
		INSERTION DANS LA TABLE OPERATION
	
	*/

	$con = Database::connect2db();
	$sql = "INSERT INTO operation (IDCOMPTE,LIBELLE,ACTION,DATE,PERIODE,AUTEUR)
			VALUES('$compte','$libelle',$montant,'$date','$periode','$auteur')";
	$con->exec($sql) or die("Erreur Requete insertion table operation");	
	}
	if (isset($_POST["appel"]))
		header("location:../inscriptions/nouvelle.php");
	else
		header("location:./operation.php?operation=".$compte);
?>