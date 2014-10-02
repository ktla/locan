<?php 
/*
	Page utilisee par ajax pour charger les matieres
	lors de l'ajout des notes
*/
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
	$codepage = "ADD_ALL_NOTE";
if(!is_autorized($codepage)){
	$codepage = "ADD_NOTE";
	if(!is_autorized($codepage)){
		print "<p class = 'infos'>Vous n'avez pas les droits</p>";
		return;
	}
}
/***************************************************************************************************/
/*
	Charger les matieres enseigner dans cette classe a la periode $_SESSION['periode'];
*/
if(isset($_GET['classe'])){
	/*
		Requete des matieres a charger apres selection de la classe
		Si le droit est limite a ses cours enseigne ie droit = ADD_NOTE alors charger seulement ses cours enseigner
	*/
	$query = "SELECT m.CODEMAT, m.LIBELLE FROM matiere m 
		WHERE m.CODEMAT IN (SELECT e.CODEMAT FROM enseigner e WHERE e.PERIODE = '".$_SESSION['periode']."' AND e.CLASSE = '".parse($_GET['classe'])."'";
	$filtre = "";
/*
	S'il est autorise a ajouter les notes dans toutes les matiere
	alors on charge toutes les matiere
*/
	if(is_autorized("ADD_ALL_NOTE"))
		$filtre = " AND e.ACTIF = '1')";
/*
	S'il est juste autorise a ajouter des notes de sa matiere enseigner
*/
	elseif(is_autorized("ADD_NOTE"))
		$filtre = " AND e.ACTIF = '1' AND e.PROF = '".$_SESSION['user']."')";
	$query .= $filtre;
	$res = mysql_query($query) or die(mysql_error());
/*
	Combobox des matiere charger
*/
	//print $query;
	if(mysql_num_rows($res)){
		print "<select name = 'matiere'><option value = ''>-Choisir la mati&egrave;re-</option>";
		while($row = mysql_fetch_array($res)){
			print "<option value=\"".$row['CODEMAT']."\">".$row['LIBELLE']."</option>";
		}
		print "</select>";
	}else{
		print "<p style = 'color:red'>Aucune mati&egrave;re enseign&eacute;e en : $_GET[classe]</p>";
		print "<input type = 'hidden' value = '' name = 'matiere'/>";
	}
}