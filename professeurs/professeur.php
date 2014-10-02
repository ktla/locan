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
$codepage = "SHOW_PROFESSEUR";
if(isset($_GET['action'])){
	if(!strcmp($_GET['action'], "edit"))
		$codepage = "EDIT_PROFESSEUR";
	elseif(!strcmp($_GET['action'], "delete") || !strcmp($_GET['action'], "deleteall"))
		$codepage = "DEL_PROFESSEUR";
}
/********************************************************************************************************/
$titre = "Gestion des professeurs";
require_once("../includes/header_inc.php");
if(isset($_GET['action'])){
	switch($_GET['action']){
		case "edit":edit();break;
		case "delete":delete($_GET['line']);break;
		case "deleteall":deleteall();break;
	}
}else
	afficher();
require_once("../includes/footer_inc.php");
function afficher($msg = ""){
	print "<div id=\"zonetravail\">";
	print "<form name = 'frmgrid' action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\"><div class = 'cadre'>";
	if(is_autorized("PRINT_PROFESSEUR"))
		print "<div class = \"icon-pdf\"><a href = 'imprimer.php' target = '_blank'><img src = '../images/icon-pdf.gif' title = 'Imprimer cette liste'></a></div>";
	$query = "SELECT p.ID,
		CONCAT(\"<a title = 'Cliquer pour voir la fiche' href = 'fiche.php?id=\", p.ID, \"'>\",MATRICULE, \"</a>\"), 
		CONCAT(NOMPROF,' ',PRENOM), 
		TEL ,SEXE
	 	FROM professeur p 
	    ORDER BY p.ID ASC";
	$grid = new Grid($query);
	$grid->addcolonne(0, "ID", "0", false);
	$grid->addcolonne(1, "MATRICULE", "1", true);
	$grid->addcolonne(2, "NOM", "2", true);
	//$grid->addcolonne(2, "PRENOM", "2", true);
	$grid->addcolonne(3, "TELEPHONE", "3", true);
	//$grid->addcolonne(4, "ETAT", "4", true);
	//On ajoute les cases a cocher que si l'utilisateur peut effectuer des suppressions en cascade
	if(is_autorized("DEL_PROFESSEUR"))
		$grid->selectbutton = true;
	//Verifie si l'utilisateur a le droit d'effectuer une suppression de professeur
	if(is_autorized("DEL_PROFESSEUR")){
		$grid->deletebutton = true;
		$grid->deletebuttontext = "Supprimer";
	}
	//Verifie si l'utilisateur a le droit d'effectuer une modification de professeur
	if(is_autorized("EDIT_PROFESSEUR")){
		$grid->editbutton = true;
		$grid->editbuttontext = "Modifier";
	}
	$grid->display();
	print "</div>";
	print "<div align=\"center\" style=\"text-transform:capitalize;color:#F00;text-decoration:overline\"><span>".(!empty($msg)?$msg:"")."</span></div>";
	print "<div class=\"navigation\">";
	if(is_autorized("ADD_PROFESSEUR"))
		print "<input type = 'button' onclick = \"document.location = 'ajouter.php'\" value = 'Ajouter'>";
	if(is_autorized("DEL_PROFESSEUR"))
		print "<input type = 'button' onClick=\"deletecheck()\" value=\"Supprimer\"/>";
	print "</form></div>";
}
function edit(){
	header("location:./modifier.php?id=".$_GET['line']);
}
function delete($id, $affiche_result = true){
	/* Suppression des fichiers associes au prof */
	try{
		$pdo = Database::connect2db();
		$prof = new Professeur($id);
		
		if(!empty($prof->reglement) && file_exists("./curriculums/".$prof->reglement))
			unlink("./curriculums/".$prof->reglement); // supression du curriculum 
		if(!empty($prof->image) && file_exists("./photos/".$prof->image))
			unlink("./photos/".$prof->image);
		
		// suppression les lignes du prof courant dans appartenance_professeur_periode
		//$pdo->exec("DELETE FROM appartenance_professeur_periode WHERE IDPROF = ".$prof->id);
		
		// mettre a jour les lignes du prof courant dans operation
		$pdo->exec("UPDATE classe_parametre SET PROFPRINCIPAL = NULL WHERE PROFPRINCIPAL =".$prof->id);
		
		//=====================================
		$query = "SELECT * FROM compte WHERE CORRESPONDANT = '".$prof->matricule."'";
		$result = $pdo->query($query);
		if($result->rowCount() > 0){
			$ligne = $result->fetch(PDO::FETCH_ASSOC);
		
			/* Suppression dans la table operation */
			$query = "DELETE FROM operation WHERE IDCOMPTE = ".$ligne["ID"];
			$pdo->exec($query);
		
			/* Suppression dans la table compte */
			$query = "DELETE FROM compte WHERE IDCOMPTE = ".$ligne["ID"];
			$pdo->exec($query);
		}
		
		// suppression les lignes du prof courant dans users
		$pdo->exec("DELETE FROM users WHERE LOGIN ='".$prof->matricule."'");
	
		// mettre a jour les lignes du prof courant dans enseigner
		$pdo->exec("UPDATE enseigner SET PROF = NULL WHERE PROF =".$prof->id);
		
		/* Suppression du prof */
		$pdo->exec("DELETE FROM professeur WHERE ID =".$prof->id);
		if($affiche_result)
			afficher("Professeur supprim&eacute; avec succ&egrave;s.");
	}catch(PDOException $e){
		die($e->getMessage()." ".$e->getLine()." ".__LINE__." ".__FILE__);
	}
}
function deleteall(){
		if(isset($_POST['chk']))
		foreach($_POST['chk'] as $val){
			delete($val,false);
		}
		afficher("Professeurs supprim&eacute;s avec succ&egrave;s");
}
?>