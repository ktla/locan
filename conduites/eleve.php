<?php
	require_once("../includes/commun_inc.php");
	if(!isset($_SESSION['user']))	header("location:../utilisateurs/connexion.php");
	$titre = "Gestion des &eacute;l&egrave;ves";
	require_once("../includes/header_inc.php");
	if(isset($_GET['action'])){
		switch($_GET['action']){
			case "edit":edit();break;
			case "delete":delete();break;
			case "deleteall":deleteall();break;
			case "rechercher":rechercher();break;
		}
	}else
		afficher();
	require_once("../includes/footer_inc.php");
function afficher(){
	print "<div id=\"zonetravail\">";
	print "<form name=\"frmgrid\" enctype=\"multipart/form-data\" method=\"POST\" action=\"".$_SERVER['PHP_SELF']."\"><div class=\"cadre\">";
	print "<div style=\"margin:5px 0 5px 0;\"><input type = 'text' size = '50' id = 'rech' name = 'rech' /><input type = 'button' value = 'Rechercher' onclick = \"rechercher('eleve');\"/>";
	print "<span class=\"imprimer\"><a href = 'imprimer.php?action=all' title = 'Cliquer pour imprimer' target = '_blank'>Imprimer</a></span></div>";
	$query = "SELECT e.MATEL, CONCAT(e.NOMEL,' ', e.PRENOM) AS NOMEL, e.TEL, 
	(SELECT c.LIBELLE FROM classe c WHERE c.IDCLASSE = (SELECT i.IDCLASSE FROM inscription i WHERE e.MATEL = i.MATEL AND i.PERIODE = '".$_SESSION['periode']."')) AS LIBELLE
	FROM eleve e ORDER BY MATEL";
	$grid = new grid($query);
	$grid->addcolonne(0, 'ID.', '0', TRUE);
	$grid->addcolonne(1, 'ELEVE', '1', TRUE);
	$grid->addcolonne(2, 'TELEPHONE', '2', TRUE);
	$grid->addcolonne(3, "CLASSE", '3', true);
	$grid->editbutton = true;
	$grid->editbuttontext = "Editer";
	$grid->deletebutton = true;
	$grid->deletebuttontext = "Supprimer";
	$grid->selectbutton = true;
	$grid->display();
	print "</div>";
	print "<div class = 'navigation'><input type = 'button' onclick = \"deletecheck();\" value = 'Supprimer' /></div></form></div>";
}
function edit(){
	print "<script>rediriger(\"modifier.php?id=".$_GET['line']."\");</script>";
}
function delete(){
	$query = "DELETE FROM eleve WHERE MATEL = '".parse($_GET['line'])."'";
	if(mysql_query($query)){
		if(mysql_affected_rows())
			print "<p class=\"infos\">Matricule &eacute;l&egrave;ve : ".$_GET['line']." supprim&eacute; avec succ&egrave;s.</p>";
		afficher();
	}else
		die("Erreur de suppression\n".mysql_error());
}
function deleteall(){
	$i = 0;
	foreach($_POST['chk'] as $val){
		if(mysql_query("DELETE FROM eleve WHERE MATEL = '".parse($val)."'")){
			if(mysql_affected_rows()){
				if($i == 0) print "<p class = 'infos'>";
				print "Matricule &eacute;l&egrave;ve : ".$val." supprim&eacute; avec succ&egrave;s.<br/>";
				$i++;
			}
		}else
			die("Erreur de suppression ".mysql_error());
		if($i != 0) print "</p>";
	}
	afficher();
}
function rechercher(){
	print "<div id=\"zonetravail\"><div class=\"titre\">LISTE DES ELEVES.</div>";
	print "<div class=\"cadre\">";
	print "<div style=\"margin:5px 0 5px 0;\"><input type = 'text' size = '70' id = 'rech' name = 'rech' />";
	print "<input accesskey=\"enter\" type = 'button' value = 'Rechercher' onclick = \"rechercher('eleve');\"/></div>";
	print "<fieldset><legend>Liste des &eacute;l&egrave;ves.</legend>";
	$query = "SELECT MATEL, NOMEL, TEL, ADRESSE, DATEAJOUT
	 FROM eleve e 
	 WHERE MATEL LIKE '%".parse($_GET['val'])."%' OR NOMEL LIKE '%".parse($_GET['val'])."%' OR TEL LIKE '%".parse($_GET['val'])."%' OR DATEAJOUT LIKE '%".parseDate($_GET['val'])."%'";
	$grid = new grid($query);
	$grid->addcolonne(0, 'ID.', '0', TRUE);
	$grid->addcolonne(1, 'NOM ELEVE', '1', TRUE);
	$grid->addcolonne(2, 'TELEPHONE', '2', TRUE);
	$grid->addcolonne(3, "ADRESSE", '3', false);
	$grid->addcolonne(4, 'DATE D\'AJOUT', '4', TRUE);
	$grid->setColDate(4);
	$grid->editbutton = true;
	$grid->editbuttontext = "Editer";
	$grid->deletebutton = true;
	$grid->deletebuttontext = "Supprimer";
	$grid->selectbutton = true;
	$grid->display();
	print "</fieldset></div>";
	print "<div class = 'navigation'><input type = 'button' onclick = \"deletecheck();\" value = 'Supprimer' /></div></div>";
}
?>