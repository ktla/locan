<?php
//sleep(5);
require_once("../includes/commun_inc.php");
/*
	Verifier les droit d'acces a cette page
*/
if(!is_autorized("ADD_ENSEIGNEMENT")){
	print "<p class = 'infos'>Vous n avez pas des droits d acces sur cette page</p>";
	return;
}
/*********************************************************************************************************/
/*
	Validation d'information dans la BD : un professeur - une matiere - une classe - et une periode
*/
if(isset($_GET['mat'])){
	/*
		Verifier les donnees
	*/
	if(empty($_GET['mat']) || empty($_GET['classe']) || empty($_GET['prof']) || empty($_GET['coeff'])){
		print "<p class = 'infos'>Tous les champs sont obligatoires</p>";
		return;
	}
	if(!is_numeric($_GET['coeff']) || intval($_GET['coeff']) < 0){
		print "<p class = 'infos'>Le coefficient doit etre un nombre positif</p>";
		return;
	}
	$query = "INSERT INTO enseigner(CODEMAT, CLASSE, PROF, PERIODE, COEFF, ACTIF) 
	VALUES('".parse($_GET['mat'])."', '".parse($_GET['classe'])."', '".parse($_GET['prof'])."', '".$_SESSION['periode']."', '".parse($_GET['coeff'])."', '1')";
	mysql_query($query) or die(mysql_error());
	/*
		Affichage de la zone montrant les nouveaux cours ou enseignement ajoute
	*/
	$query = "SELECT e.IDENSEIGNEMENT, e.PROF, 
					(SELECT m.LIBELLE FROM matiere m WHERE m.CODEMAT = e.CODEMAT) AS MATLIB,
					(SELECT CONCAT(p.NOMPROF,' ', p.PRENOM) FROM professeur p WHERE e.PROF = p.IDPROF) AS PROFESS,
					e.COEFF, IF(e.ACTIF = 1, 'ACTIF', 'NON ACTIF') AS ETAT 
					FROM enseigner e 
					WHERE e.CLASSE = '".parse($_GET['classe'])."' AND e.PERIODE = '".$_SESSION['periode']."' ORDER BY e.CODEMAT";
	/*
		Tableau de cours enseigner dans la classe
	*/
	$res = mysql_query($query) or die(mysql_error());
	$i = 1;
	while($row = mysql_fetch_array($res)){
		print "<tr><td>".($i++)."</td><td><a href = '../professeurs/fiche.php?prof=".$row['PROF']."'>".$row['PROFESS']."</a></td>";
		print "<td>".$row['MATLIB']."</td><td>".$row['COEFF']."</td><td>".$row['ETAT']."</td></tr>";
	}
}
/*
	Chargement des nouveaux cours (ensemble prives des cours deja choisis) dans le combo box
*/
if(isset($_GET['chargercours'])){
	$query = "SELECT m.CODEMAT, m.LIBELLE FROM matiere m 
	WHERE m.CODEMAT NOT IN 
	(SELECT e.CODEMAT FROM enseigner e WHERE e.CLASSE = '".parse($_GET['classe'])."' AND e.PERIODE = '".$_SESSION['periode']."') ORDER BY m.CODEMAT";
	$res = mysql_query($query) or die(mysql_error());
	print "<option value = ''>-Choisir une mati&egrave;re-</option>";
	while($row = mysql_fetch_array($res))
		print "<option value = \"".$row['CODEMAT']."\">".$row['LIBELLE']."</option>";
}
?>