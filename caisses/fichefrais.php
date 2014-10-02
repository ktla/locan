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
$codepage = "FRAIS_FICHE";
/********************************************************************************************************/
	$titre = "Fiche des Frais";
/****************************************************
*	Integration du modele de tete
*****************************************************/
	require_once("../includes/header_inc.php");
/*
	function pour afficher la fiche, si la classe
	n'est pas selectionner, rediriger vers la page frais
*/
if(isset($_GET['classe']))
	fichefrais();
else
	@header("Location:../caisses/frais.php");
/******************************************
*
*	Function propre a la page
*
********************************************/
function fichefrais(){?>
<div id = "zonetravail"><div class = "titre">FICHE FRAIS</div>
	<form action = "" name = "frm" enctype="multipart/form-data">
		<div class = "cadre">
			<?php 
				$print = new printlink("imprimerfrais.php?id=".$_GET['classe'], false);
				$print->display();
				try{
					$pdo = Database::connect2db();
					$res = $pdo->prepare("SELECT * FROM classe WHERE IDCLASSE = :classe");
					$res->bindValue("classe", $_GET['classe'], PDO::PARAM_STR);
					$res->execute();
					$row = $res->fetch(PDO::FETCH_BOTH);
				}catch(PDOException $ex){
					die($ex->getMessage()." ".$ex->getLine()." ".$ex->getFile());
				}
			?>
            <div class="fiche"><div class="titrefiche">CLASSE</div>
   				<div class="fichecontent">
            		<label>Identifiant : </label><?php  echo $row['IDCLASSE']; ?>.<br />
            		<label>Libell&eacute; : </label><?php  echo $row['LIBELLE']; ?>.<br />			
            		<label>Niveau : </label><?php  echo $row['NIVEAU']; ?>.<br />
         		</div>
         		<div class="titrefiche">FRAIS OFFICIELS</div>
         		<div class="fichecontent" style="padding-right:5px;">
                	<?php
					$query = "SELECT * FROM classe_frais WHERE IDCLASSE = :classe AND PERIODE = :periode AND TYPE = :type";
					/* 0 = Frais officiels, 1 = frais occasionnels */
					$grid = new Grid($query, 0);
					$grid->param = array("classe" => $_GET['classe'], "periode" => $_SESSION['periode'], "type" => "0");
					$grid->addcolonne(0, "ID", "ID", false);
					$grid->addcolonne(1, "CODE", "CODE", true);
					$grid->addcolonne(2, "LIBELLE", "LIBELLE", true);
					$grid->addcolonne(3, "DATEDEBUT", "DEBUT", true);
					$grid->addcolonne(4, "DATEFIN", "FIN", true);
					$grid->addcolonne(5, "MONTANT", "MONTANT", true);
					$grid->actionbutton = false;
					$grid->setColDate(3);
					$grid->setColDate(4);
					$grid->display();
					?>
         		</div>
                <div class="titrefiche">FRAIS OCCASIONNELS</div>
         		<div class="fichecontent" style="padding-right:5px;">
                	<?php
					$param = array("classe" => $_GET['classe'], "periode" => $_SESSION['periode'], "type" => "1");
					$grid->setQuery($query, $param);
					$grid->display();
					?>
         		</div>
                <div class="titrefiche">REDUCTION SUR LES FRAIS</div>
         		<div class="fichecontent" style = "padding-right:5px;">
                	<?php
					$query = "SELECT r.*, f.LIBELLE AS APPLIQUEA 
					FROM classe_reduction r 
					LEFT JOIN classe_frais f ON (r.IDFRAIS = f.ID) 
					WHERE r.IDFRAIS IN 
					(SELECT f2.ID FROM classe_frais f2 WHERE f2.IDCLASSE = :classe AND f2.PERIODE = :periode)";
					$grid = new Grid($query, 0);
					$grid->param = array("classe" => $_GET['classe'], "periode" => $_SESSION['periode']);
					$grid->addcolonne(0, "ID", "ID", false);
					$grid->addcolonne(1, "CODE", "CODE", true);
					$grid->addcolonne(2, "LIBELLE", "LIBELLE", true);
					$grid->addcolonne(3, "APPLIQUE A", "APPLIQUEA", true);
					$grid->addcolonne(4, "MONTANT", "MONTANT", true);
					$grid->actionbutton = false;
					$grid->display();
					?>
         		</div>
         	</div><!-- Fin de fiche -->
         </div><!-- Fin de cadre -->
		<div class = "navigation">
			<input type = "button" value = "Retour" onclick = "rediriger('frais.php')" />
			<input type = "button" value = "Terminer" onclick = "rediriger('../accueil/index.php')" />
		</div>
	</form>
</div>
<?php
}
/*******************************************
*	Integration du modele de bas de page
*********************************************/
	require_once("../includes/footer_inc.php");
?>