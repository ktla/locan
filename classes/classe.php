<?php
require_once("../includes/commun_inc.php");
if(!isset($_SESSION['user']))	header("location:../utilisateurs/connexion.php");
/*
	Verification du droit d'acces de cette page
	Verifier que le codepage existe dans nos listedroit, ceci
	empeche de proceder par saisie de l'url et d'acceder a la page
*/
$codepage = "SHOW_CLASSE";
if(isset($_GET['action'])){
	if(!strcmp($_GET['action'], "edit"))
		$codepage = "EDIT_CLASSE";
	elseif(!strcmp($_GET['action'], "delete") || !strcmp($_GET['action'], "deleteall"))
		$codepage = "DEL_CLASSE";
}
$titre = "Gestion des classes";
require_once("../includes/header_inc.php");
if(isset($_GET['action'])){
	switch($_GET['action']){
		case "edit": edit();break;
		case "delete":delete();break;
		case "deleteall" : deleteall();break;
	}
}else
	afficher();
require_once("../includes/footer_inc.php");
function afficher(){
	print "<div id=\"zonetravail\"><div class = 'titre'>GESTION DES CLASSES.</div>";
	print "<form action=\"".$_SERVER['PHP_SELF']."\" name=\"frmgrid\" method=\"POST\" enctype=\"multipart/form-data\"><div class = 'cadre'>";
	if(is_autorized("PRINT_CLASSE"))
		print "<div class = \"icon-pdf\"><a href = 'imprimer.php' target = '_blank'><img src = '../images/icon-pdf.gif' title = 'Imprimer cette liste'></a></div>";
	/*$query = "SELECT c.IDCLASSE, c.LIBELLE, (SELECT COUNT(MATEL) FROM inscription f WHERE c.IDCLASSE = f.IDCLASSE AND f.PERIODE = '".$_SESSION['periode']."') AS NBRE, 
	 c.TAILLE, c.NIVEAU, 
	 CONCAT(\"<a title = 'Cliquer pour changer d &eacute;tat' href = 'changeretat.php?id=\", c.IDCLASSE, \"'>\",IF(ACTIF = 1, 'Active', 'Bloqu&eacute;e'), \"</a>\") 
	FROM classe c ORDER BY c.NIVEAU";*/
	$query = "SELECT c.IDCLASSE, c.LIBELLE, 
	(SELECT COUNT(MATEL) FROM inscription i WHERE c.IDCLASSE = i.IDCLASSE AND i.PERIODE = '".$_SESSION['periode']."') AS NBRE,
	 c.NIVEAU, p.TAILLEMAX, 
	 CONCAT(\"<a title = 'Cliquer pour changer d &eacute;tat' href = 'changeretat.php?id=\", c.IDCLASSE, \"'>\",IF(p.ACTIF = 1, 'Active', 'Bloqu&eacute;e'), \"</a>\") AS ETAT, 
	 CONCAT(\"<a href = 'detail.php?id=\", c.IDCLASSE, \"'>\", 'D&eacute;tails', \"</a>\") AS DETAILS 
	 FROM classe c 
	 LEFT JOIN classe_parametre p ON (c.IDCLASSE = p.IDCLASSE AND p.PERIODE = '".$_SESSION['periode']."') 
	 ORDER BY c.NIVEAU";
	$grid = new Grid($query);
	$grid->id = 0;
	$grid->addcolonne(0, "ID.", 'IDCLASSE', TRUE);
	$grid->addcolonne(1, "LIBELLE", 'LIBELLE', TRUE);
	$grid->addcolonne(2, "NB.ELEVE", 'NBRE', TRUE);
	$grid->addcolonne(3, "NIVEAU", 'NIVEAU', false);
	$grid->addcolonne(4, "TAILLE MAX", 'TAILLEMAX', TRUE);
	$grid->addcolonne(5, "ETAT", 'ETAT', TRUE);
	$grid->addcolonne(6, "DETAILS", 'DETAILS', TRUE);
	if(is_autorized("EDIT_CLASSE")){
		$grid->editbutton = true;
		$grid->editbuttontext = "Modifier";
	}
	$grid->selectbutton = true;
	if(is_autorized("DEL_CLASSE")){
		$grid->deletebutton = true;
		$grid->deletebuttontext = "Supprimer";
	}
	$grid->actionbutton = false;
	$grid->display();
?>
	</div>
	<div class="navigation">
	<?php if(is_autorized("ADD_CLASSE")) echo "<input type=\"button\" value=\"Ajouter\" onclick=\"rediriger('ajouter.php');\" />"; ?>
    	<?php if(is_autorized("DEL_CLASSE")) echo "<input type=\"button\" value=\"Supprimer\" onclick=\"deletecheck();\"/>"; ?>
    </div></form></div>
<?php
}
function delete(){
	if(mysql_query("DELETE FROM classe WHERE IDCLASSE = '".parse($_GET['line'])."'")){
		if(mysql_affected_rows())
			print "<p class=\"infos\">Suppression effectu&eacute;e avec succ&egrave;s</p>";
		else
			print "<p class=\"infos\">Aucune suppression effectu&eacute;e</p>";
	}else
		die(mysql_error());
	afficher();
}
function deleteall(){
	$i = 0;
	foreach($_POST['chk'] as $val){
		if(mysql_query("DELETE FROM classe WHERE IDCLASSE = '".parse($val)."'")){
			if(mysql_affected_rows()){
				if($i == 0) 
					print "<p class = 'infos'>";
				print "Classe : ".$val." supprim&eacute; avec succ&egrave;s.<br/>";
				$i++;
			}
		}else
			die("Erreur de suppression $val ".mysql_error());
	}
	if($i != 0) print "</p>";
	afficher();
}
function edit(){
	print "<script>rediriger('modifier.php?id=".$_GET['line']."');</script>";
}?>