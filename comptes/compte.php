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
$codepage = "SHOW_COMPTE";
if(isset($_GET['action'])){
	if(!strcmp($_GET['action'], "edit"))
		$codepage = "EDIT_COMPTE";
	elseif(!strcmp($_GET['action'], "delete") || !strcmp($_GET['action'], "deleteall"))
		$codepage = "DEL_COMPTE";
}
/********************************************************************************************************/
	$titre = "Gestion des comptes";
	require_once("../includes/header_inc.php");
	if(isset($_GET['action'])){
		switch($_GET['action']){
			case "edit":edit();break;
			case "delete":delete();break;
			case "deleteall":deleteall();break;
		}
	}else
		afficher();
/**
	Function propre a la page
*/
function afficher(){?>
<div id="zonetravail"><div class = 'titre'>GESTION DES COMPTES.</div>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frmgrid" method="POST" enctype="multipart/form-data">
	<div class = 'cadre'><fieldset><legend>Liste des classes.</legend>
    <?php 
	$print = new printlink('imprimercompte.php', false);
	$print->display();
	$query = "SELECT c.IDCOMPTE, CONCAT(e.NOMEL, ' ', e.PRENOM) AS NOM, c.DATECREATION, c.AUTEUR 
	FROM compte c 
	INNER JOIN eleve e ON (e.MATEL = c.CORRESPONDANT) 
	UNION
	SELECT c.IDCOMPTE, CONCAT(p.NOMPROF, ' ', p.PRENOM) AS NOM, c.DATECREATION, c.AUTEUR 
	FROM compte c 
	INNER JOIN professeur p ON (p.IDPROF = c.CORRESPONDANT) 
	UNION 
	SELECT c.IDCOMPTE, CONCAT(s.NOM, ' ', s.PRENOM) AS NOM, c.DATECREATION, c.AUTEUR 
	FROM compte c 
	INNER JOIN staff s ON (s.IDSTAFF = c.CORRESPONDANT)";
	$grid = new grid($query, 0);
	$grid->addcolonne(0, "COMPTE", 'IDCOMPTE', true);
	$grid->addcolonne(1, "PROPRIETAIRE", 'NOM', TRUE);
	$grid->addcolonne(2, "DATE CREATION", 'DATECREATION', TRUE);
	$grid->addcolonne(3, "CREATEUR", 'AUTEUR', TRUE);
	$grid->setColDate(2);
	$grid->selectbutton = true;
	//On ajoute les cases a cocher que si l'utilisateur peut effectuer des suppressions en cascade
	if(is_autorized("DEL_COMPTE"))
		$grid->selectbutton = true;
	//Verifie si l'utilisateur a le droit d'effectuer une suppression de professeur
	if(is_autorized("DEL_COMPTE")){
		$grid->deletebutton = true;
		$grid->deletebuttontext = "Supprimer";
	}
	//Verifie si l'utilisateur a le droit d'effectuer une modification de professeur
	if(is_autorized("EDIT_COMPTE")){
		$grid->editbutton = true;
		$grid->editbuttontext = "Modifier";
	}
	$grid->display();
	?>
	</fieldset></div>
    <div class="navigation"><?php if(is_autorized("ADD_COMPTE")) 
			print "<input type = 'button' value = 'Ajouter' onclick=\"rediriger('ajouter.php');\" />";
		if(is_autorized("DEL_COMPTE"))
				print "<input type = 'button' onClick=\"deletecheck()\" value=\"Supprimer\"/>";
	?>
    </div>
    </form></div>
<?php }
/*
	Rediriger vers la page de modification
*/
function edit(){
	@header("Location:modifier.php?id=".$_GET['line']);
}
/*
	Suppression unique
*/
function delete(){
	try{
		$pdo = Database::connect2db();
		$res = $pdo->prepare("DELETE FROM compte WHERE IDCOMPTE = :idcompte");
		$res->execute(array(
			'idcompte' => $_GET['line']
		));
		if(!$res->rowCount())
			print "<p class = 'infos'>Aucune suppression effectu&eacute;e pour : ".$_GET['line']."<p>";
		else
			print "<p class = 'infos'>Supression effectu&eacute;e avec succ&egrave;s pour ".$_GET['line']."</p>";
		afficher();
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
	}
}
/*
	Suppression en cascade
*/
function deleteall(){
	try{
		$pdo = Database::connect2db();
		$res = $pdo->prepare("DELETE FROM compte WHERE IDCOMPTE = :idcompte");
		$i = 0;
		foreach($_POST['chk'] as $val){
			$res->execute(array('idcompte' => $val));
			if($res->rowCount()){
				if($i == 0) 
					print "<p class = 'infos'>";
				print "Compte : ".$val." supprim&eacute; avec succ&egrave;s.<br/>";
				$i++;
			}
		}
		if($i != 0) 
			print "</p>";
		afficher();
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
	}
}
/*****************************************
*
*	Integration du modele de bas de page
*
*******************************************/
require_once("../includes/footer_inc.php");
?>