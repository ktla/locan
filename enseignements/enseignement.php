<?php
/*****************************************************************************************************/
require_once("../includes/commun_inc.php");
/*
	Verification de la duree de la session, deconnexion si l'utilisateur a ete inactif
*/
if(!isset($_SESSION['user'])) header("location:../utilisateurs/connexion.php");
/*
	Verification du droit d'acces de cette page
	Verifier que le codepage existe dans nos listedroit, ceci
	empeche de proceder par saisie de l'url et d'acceder a la page
*/
	$codepage = "SHOW_ENSEIGNEMENT";
	if(isset($_GET['action'])){
		if(!strcmp($_GET['action'], "edit"))
			$codepage = "EDIT_ENSEIGNEMENT";
		elseif(!strcmp($_GET['action'], "delete") || !strcmp($_GET['action'], "deleteall"))
			$codepage = "DEL_ENSEIGNEMENT";
	}
/*****************************************************************************************************/
$titre = "Gestion des enseignements";
require_once("../includes/header_inc.php");
if(isset($_GET['action'])){
	switch($_GET['action']){
		case "add": ajouter(); break;
		case "edit": modifier(); break;
		case "delete": delete();break;
		case "deleteall": deleteall(); break;
	}
}else
	afficher();
require_once("../includes/footer_inc.php");
/*****************************************************************************************************

		Fonction relative a cette page
		
*****************************************************************************************************/
/*
	Affiche la liste des enseignements
*/
function  afficher(){?>
<div id="zonetravail">
<form name="frmgrid" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" >
	<div class="cadre">
    <?php 
		if(is_autorized("PRINT_ENSEIGNEMENT"))
			print "<div class = \"icon-pdf\"><a href = 'imprimer.php' target = '_blank'><img src = '../images/icon-pdf.gif' title = 'Imprimer cette liste'></a></div>";
    	$query = "SELECT e.IDENSEIGNEMENT, 
		(SELECT m.LIBELLE FROM matiere m WHERE e.CODEMAT = m.CODEMAT) , 
		(SELECT c.LIBELLE FROM classe c WHERE c.IDCLASSE = e.IDCLASSE), 
		(SELECT CONCAT(p.NOMPROF,' ', p.PRENOM) FROM professeur p WHERE e.PROF = p.IDPROF), e.COEFF, 
		 IF(e.ACTIF = 1, 'ACTIF', 'NON ACTIF') AS ETAT 
		 FROM enseigner e 
		 WHERE e.PERIODE = '".$_SESSION['periode']."' ORDER BY e.CODEMAT";
			$grid = new grid($query, 0);
			$grid->addcolonne(0, 'ID', '0', true);
			$grid->addcolonne(1, 'MATIERE', '1', true);
			$grid->addcolonne(2, 'CLASSE', '2', true);
			$grid->addcolonne(3, 'PROFESSEUR', '3', true);
			$grid->addcolonne(4, 'COEFF', '4', true);
			$grid->addcolonne(5, 'ETAT', '5', true);
			if(is_autorized("DEL_ENSEIGNEMENT")){
				$grid->deletebutton = true;
				$grid->deletebuttontext = "Supprimer cet enseignement";
			}
			if(is_autorized("EDIT_ENSEIGNEMENT")){
				$grid->editbutton = true;
				$grid->editbuttontext = "Modifier cet enseignement";
			}
			$grid->selectbutton = true;
			$grid->display();
		?>
    </div>
    <div class="navigation">
    	<?php if(is_autorized("DEL_ENSEIGNEMENT"))
			print "<input type=\"button\" value=\"Supprimer\" onclick=\"deletecheck();\">";
		if(is_autorized("ADD_ENSEIGNEMENT"))
    		print "<input type=\"button\" value=\"Ajouter\" onclick=\"rediriger('ajouter.php');\"/>";
		?>
    </div>
</form>
</div>
<?php }
/*
	Fonction de suppression
*/
function delete(){
	$query = "DELETE FROM enseigner WHERE IDENSEIGNEMENT = '".parse($_GET['line'])."'";
	if(mysql_query($query)){
		if(mysql_affected_rows())
			print "<p class=\"infos\">Enseignement : ".$_GET['line']." supprim&eacute; avec succ&egrave;s.</p>";
		afficher();
	}else
		die("Erreur de suppression\n".mysql_error());
}
/*
	Fonction de suppression en cascade
*/
function deleteall(){
	$i = 0;
	foreach($_POST['chk'] as $val){
		if(mysql_query("DELETE FROM enseigner WHERE IDENSEIGNEMENT = '".parse($val)."'")){
			if(mysql_affected_rows()){
				if($i == 0) print "<p class = 'infos'>";
				print "Enseignement : ".$val." supprim&eacute; avec succ&egrave;s.<br/>";
				$i++;
			}
		}else
			die("Erreur de suppression ".mysql_error());
	}
	if($i != 0) print "</p>";
	afficher();
}
/*
	Fonction de modification, rediriger vers le fichier modifier.php
*/
function modifier(){
	print "<script>rediriger('modifier.php?id=".$_GET['line']."');</script>";
}
?>