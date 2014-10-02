<?php 
/***************************************************************************************************/
require_once("../includes/commun_inc.php");
/*
	Verification de la duree de la sesssion
*/
if(!isset($_SESSION['user'])) header("location:../utilisateurs/connexion.php");
/*
	Verification du droit d'acces a cette page
	empeche l'acces par saisie d'url
*/
	$codepage = "ADD_NOTE";
/***************************************************************************************************/
$titre = "Visualiser les notes.";
require_once("../includes/header_inc.php");
/*
	Distribution des evenements : deux evenement (Choix des informations et validation)
*/
if(isset($_GET['matiere']) && isset($_GET['classe']))
	visualiser();
/*
	Modele de bas de page
*/
require_once("../includes/footer_inc.php");
/***************************************************************************************************
	Fonction relative de la page
	
/***************************************************************************************************/
function visualiser(){
	
}
?>