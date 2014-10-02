<?php
	require_once("../includes/commun_inc.php");
	/*
		Verification de l'authencite de l'utilisateur, redirection si session expiree
	*/
	if(!isset($_SESSION['user']))	header("location:../utilisateurs/connexion.php");
	$titre = "Gestion des mati&egrave;res.";
/*
	Verification du droit d'acces de cette page
	Verifier que le codepage existe dans nos listedroit, ceci
	empeche de proceder par saisie de l'url et d'acceder a la page
*/
	$codepage = "SHOW_MATIERE";
	if(isset($_GET['action'])){
		if(!strcmp($_GET['action'], "edit"))
			$codepage = "EDIT_MATIERE";
		elseif(!strcmp($_GET['action'], "delete") || !strcmp($_GET['action'], "deleteall"))
			$codepage = "DEL_MATIERE";
	}
/****************************************************************************************************/
	require_once("../includes/header_inc.php");
	if(isset($_GET['action'])){
		switch($_GET['action']){
			case "edit":edit();break;
			case "delete":delete();break;
			case "deleteall":deleteall();break;
		}
	}else
		afficher();
	require_once("../includes/footer_inc.php");
function afficher(){
	print "<div id=\"zonetravail\">";
	print "<form name = 'frmgrid' action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\"><div class = 'cadre'>";
	if(is_autorized("PRINT_MATIERE"))
		print "<div class = \"icon-pdf\"><a href = 'imprimer.php' target = '_blank'><img src = '../images/icon-pdf.gif' title = 'Imprimer cette liste'></a></div>";
	//print "<fieldset><legend>Listes des mati&egrave;res.</legend>";
	$grid = new grid("SELECT CODEMAT, LIBELLE FROM matiere ORDER BY LIBELLE", 0);
	$grid->addcolonne(0, "CODE MATIERE", "0", true);
	$grid->addcolonne(1, "CODE MATIERE", "1", true);
	$grid->selectbutton = true;
	if(is_autorized("DEL_MATIERE")){
		$grid->deletebutton = true;
		$grid->deletebuttontext = "Supprimer";
	}
	if(is_autorized("EDIT_MATIERE")){
		$grid->editbutton = true;
		$grid->editbuttontext = "Modifier";
	}
	$grid->display();
	print "</div>";
	print "<div class=\"navigation\">";
	if(is_autorized("ADD_MATIERE"))
		print "<input type = 'button' onclick = \"document.location = 'ajouter.php'\" value = 'Ajouter'>";
	if(is_autorized("DEL_MATIERE"))
		print "<input type = 'button' onClick=\"deletecheck()\" value=\"Supprimer\"/>";
	print "</form></div>";
}
function delete(){
	if(mysql_query("DELETE FROM matiere WHERE CODEMAT = '".parse($_GET['line'])."'")){
		if(mysql_affected_rows())
			print "<p class=\"infos\">Mati&egrave;re supprim&eacute;e avec succ&egrave;s.</p>";
		else
			print "<p class=\"infos\">Aucune mati&egrave;re supprim&eacute;e.</p>";
		afficher();
	}else
		die(mysql_error());
}
function edit(){
	print "<script>rediriger(\"modifier.php?line=".parse($_GET['line'])."\");</script>";
}
function deleteall(){
	$i = 0;
	foreach($_POST['chk'] as $val){
		if(mysql_query("DELETE FROM matiere WHERE CODEMAT = '".parse($val)."'")){
			if(mysql_affected_rows()){
				if($i == 0) 
					print "<p class = 'infos'>";
				print "Mati&egrave;re : ".$val." supprim&eacute;e avec succ&egrave;s.<br/>";
				$i++;
			}
		}else
			die("Erreur de suppression ".mysql_error());
	}
	if($i != 0) print "</p>";
	afficher();
}
?>