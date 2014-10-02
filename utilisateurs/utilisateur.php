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
	$codepage = "SHOW_USER";
	if(isset($_GET['action'])){
	if(!strcmp($_GET['action'], "edit"))
		$codepage = "EDIT_USER";
	elseif(!strcmp($_GET['action'], "delete") || !strcmp($_GET['action'], "deleteall"))
		$codepage = "DEL_USER";
}
/********************************************************************************************************/
	$titre = "Gestion des utilisateurs";
	require_once("../includes/header_inc.php");
	if(isset($_GET['action'])){
		switch($_GET['action']){
			case "edit": edit();break;
			case "delete":delete();break;
		}
	}else
		afficher();
	require_once("../includes/footer_inc.php");
function afficher(){
	print "<div id=\"zonetravail\"><div class = 'titre'>GESTION DES UTILISATEURS.</div>";
	print "<form action=\"".$_SERVER['PHP_SELF']."\" name=\"frm\" method=\"POST\" enctype=\"multipart/form-data\">";
	print "<div class = 'cadre'><fieldset><legend>Liste des classes.</legend>"; 
	$query = "SELECT * FROM users ORDER BY LOGIN";
	$grid = new Grid($query);
	$grid->id = 0;
	$grid->addcolonne(0, "IDENTIFIANT.", '1', TRUE);
	$grid->addcolonne(1, "MOT DE PASSE.", '2', TRUE);
	$grid->addcolonne(2, "NOM.", '3', TRUE);
	$grid->addcolonne(3, "PRENOM.", '4', TRUE);
	$grid->addcolonne(4, "PROFILE", '5', TRUE);
	$grid->editbutton = false;
	//$grid->selectbutton = true;
	//$grid->editbuttontext = "Modifier";
	$grid->deletebutton = true;
	$grid->deletebuttontext = "Supprimer";
	$grid->display();
	print "</fieldset></div>";
	print "<div class = 'navigation'><input type=\"button\" value = 'Annuler' onclick = \"home();\"/>";
	print "<input type=\"button\" value=\"Ajouter\" onclick = \"document.location = '../utilisateurs/ajouter.php'\" /></div></form></div>";
}
function delete(){
	if(!strcasecmp($_GET['line'], $_SESSION['user'])){
		print "<p class=\"infos\">Impossible de supprimer le login avec lequel on est connect&eacute;!!!</p>";
		return;
	}
	try{
		$pdo = Database::connect2db();
		$res = $pdo->prepare("DELETE FROM users WHERE LOGIN = :login");
		$res->execute(array(
			'login' => $_GET['line']
		));
		if($res->rowCount())
			print "<p class=\"infos\">Utilisateur :".$_GET['line']." supprim&eacute; avec succ&egrave;s.</p>";
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
	}
	afficher();
}
?>