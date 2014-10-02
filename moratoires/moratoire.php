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
	$codepage = "SHOW_MORATOIRE";
	/* Varier si c'est un ajout ou supression */
	if(isset($_GET['action'])){
	if(!strcmp($_GET['action'], "edit"))
		$codepage = "EDIT_MORATOIRE";
	elseif(!strcmp($_GET['action'], "delete") || !strcmp($_GET['action'], "deleteall"))
		$codepage = "DEL_MORATOIRE";
}
/********************************************************************************************************/
	$titre = "Gestion des moratoires";
/**************************************************
*	Integration du modele de haut de page
*
**************************************************/
	require_once("../includes/header_inc.php");
/***************************************
*	Functions propres a la page
*
**********************************************/
	if(isset($_GET['action'])){
		switch($_GET['action']){
			case "edit":@header('Location:modifier.php?id='.$_GET['line']);
			break;
			case "delete":delete();break;
			case "deleteall":deleteall();break;
		}
	}else
		afficher();
/**********************************************
*	Functions propres a la page
*
************************************************/
function afficher(){?>
<div id="zonetravail">
<form name="frmgrid" method="post" enctype="multipart/form-data">
	<div class="cadre">
    <?php
		$query = "SELECT m.IDMORATOIRE, m.MONTANT, m.MONTANTUTILISE, 
		CONCAT(m.DATEDEBUT, ' Au ', m.DATEFIN) AS DUREE, CONCAT(e.NOMEL,' ', e.PRENOM) AS NOMEL 
		FROM moratoire m 
		LEFT JOIN eleve e ON (e.MATEL = m.MATEL) 
		ORDER BY m.MATEL";
		$grid = new grid($query, 0);
		$grid->addcolonne(0, "MORATOIRE", "IDMORATOIRE", true);
		$grid->addcolonne(1, "ELEVE", "NOMEL", true);
		$grid->addcolonne(2, "MOMTANT", "MONTANT", true);
		$grid->addcolonne(3, "DUREE", "DUREE", true);
		$grid->addcolonne(4, "MONTANT UTILISE", "MONTANTUTILISE", true);
		$grid->editbutton = true;
		$grid->editbuttontext = "Modifier ce moratoire";
		$grid->selectbutton = true;
		$grid->display();
	?>
    </div>
    <div class="navigation"><input type="button" value="Supprimer" onclick="deletecheck();"/>
    	<input type="button" value="Ajouter" onclick="rediriger('ajouter.php')"/>
    </div>
</form>
</div>
<?php }
function deleteall(){
	try{
		/* Empecher la suppression si le moratoire est deja engage */
		
		$i = 0;
		$pdo = Database::connect2db();
		$res = $pdo->prepare('SELECT * FROM moratoire WHERE IDMORATOIRE = :moratoire');
		foreach($_POST['chk'] as $val){
			$res->bindValue('moratoire', $val, PDO::PARAM_STR);
			$res->execute();
			if(!$res->rowCount()){
				print "<p class=\"infos\">Aucun moratoire ayant ID : $val</p>";
				afficher();
				return;
			}
			$row = $res->fetch(PDO::FETCH_ASSOC);
			if(intval($row['MONTANTUTILISE']) != 0){
				print "<p class = 'infos' >Impossible de supprimer $val. Il a d&eacute;j&agrave; &eacute;t&eacute; engag&eacute;</p>";
				afficher();
				return;
			}
			$del = $pdo->prepare('DELETE FROM moratoire WHERE IDMORATOIRE = :moratoire');
			$del->bindValue('moratoire', $val, PDO::PARAM_STR);
			$del->execute();
			if($del->rowCount()){
				if($i == 0) 
					print "<p class = 'infos'>";
				print "Moratoire : ".$val." supprim&eacute; avec succ&egrave;s.<br/>";
				$i++;
			}
			$del->closeCursor();
		}
		if($i != 0) 
			print "</p>";
		$res->closeCursor();
		afficher();
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." ".$e->getLine()." ".__LINE__." ".__FILE__);
	}
}
/*function edit(){
	print "<script>rediriger(\"modifier.php?id=".$_GET['line']."\");</script>";
}*/
/************************************************
*
*	Integration du modele de bas de page
*
************************************************/
require_once("../includes/footer_inc.php");
?>