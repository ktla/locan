<?php
/******************************************************************************************************/
	require_once("../includes/commun_inc.php");
/******************************************************************************************************/
/*
	Verifie l'authencite de l'utilisateur
	- Redirection si utilisateur non autorise
	- Redirection si la duree de la session est terminer 
	- Confere commun_in.php pour la duree de la session dans la variable $_SESSION['timeconnect'];
*/
if(!isset($_SESSION['user']))	header("location:../utilisateurs/connexion.php");
/*
	Verification du droit d'acces de cette page
	Verifier que le codepage existe dans nos listedroit, ceci
	empeche de proceder par saisie de l'url et d'acceder a la page
*/
	$codepage = "CURSUS_ELEVE";
/********************************************************************************************************/
	require_once("../includes/header_inc.php");
/***********************************************************

	Function propres a la pages
************************************************************/
echo "<p class = 'infos'>page non encore implementee</p>";
/************************************************************
*
*	Integration du modele de base de page
*
************************************************************/
require_once("../includes/footer_inc.php");
?>