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
	$codepage = "ADD_MORATOIRE";
$titre = "Ajout d'un moratoire";
/******************************************
*
*	Integration du modele de haut de page
*
*******************************************/
require_once("../includes/header_inc.php");
/**
*
*/
if(isset($_POST['idmor']))
	valider();
else
	ajouter();
/*****************************************
*
*	Functions propres a la page
*
******************************************/
function ajouter(){?>
<div id="zonetravail"><div class="titre">AJOUT DE MORATOIRE</div>
	<script>
		function loadIdCompte(){
			var frm = document.forms['frm'];
			var id = 'id=' + frm.matel.value + '&action=moratoire';
			callajax('ajouter2.php?' + id, 'compte', 'loader');
			document.getElementById('compte').setAttribute('class', 'lib');
		}
		/* Gerer les champs obligatoires */
		function ajouterMoratoire(){
			var frm = document.forms['frm'];
			if(frm.matel.value == "" || frm.libelle.value == "" || frm.idmor.value == "" || frm.datedebut.value == "" || frm.datefin.value == "" || frm.montant.value == "")
				alert('Tous les champs marques par * sont obligatoires');
			else{
				if(isNaN(frm.montant.value) || parseInt(frm.montant.value) < 0){
					alert("-Entrer un nombre positif dans la zone montant-");
					return;
				}
				frm.submit();
			}
		}
	</script>
    <form name="frm" action="ajouter.php" method="post" enctype="multipart/form-data" onsubmit="ajouterMoratoire(); return false;">
        <div class="cadre">
            <fieldset><legend>Informations sur le moratoire</legend>
                <table style="margin:auto" cellspacing="5">
                	<tr><td colspan="5" align="center"><img src="../images/loader.gif" id="loader" style="visibility:hidden" /></td></tr>
                    <tr>
                        <td class="lib">Choisir l'&eacute;l&egrave;ve : <span class="asterisque">*</span></td>
                        <td><?php 
                            $combo = new Combo("SELECT MATEL, CONCAT(NOMEL,' ', PRENOM) FROM eleve ORDER BY MATEL", "matel", 0, 1, false);
                            $combo->first = "-Choisir le concerner-";
                            $combo->onchange = 'loadIdCompte();';
                            $combo->view('200px'); ?>
                         </td>
                         <td class="lib">Compte associ&eacute;</td>
                         <td><span id="compte" class="lib"><?php print format('Aucun compte associ&eacute;', 2);?></span></td>
                    </tr>
                    <tr>
                    	<td class="lib">Libell&eacute; : <span class="asterisque">*</span></td>
                        <td><input type="text" name="libelle" maxlength="150" /></td>
                        <td class="lib">Moratoire : <span class="asterisque">*</span></td>
                        <td><input type="text" name="idmor" maxlength="15" /></td>
                    </tr>
                    <tr>
                        <td class="lib">Date de d&eacute;but : <span class="asterisque">*</span></td>
                        <td><input type="text" name="datedebut" id="datedebut"/></td>
                        <td class="lib">Date de fin : <span class="asterisque">*</span></td>
                        <td><input type="text" name="datefin" id="datefin"/></td>
                    </tr>
                    <tr>
                    	<td class="lib">Montant du Moratoire <span class="asterisque">*</span></td>
                    	<td colspan="3"><input style="width:100%;" type="text" name="montant" maxlength="30" /></td>
                    </tr>
                    <tr>
                    	<td colspan="4" style="text-align:center; font-size:10px; color:red">Les champs marqu&eacute;s par * sont obligatoires</td>
                    </tr>
                </table>
            </fieldset>
        </div>
        <div class="navigation">
            <input type="button" onclick="home();" value="Retour" />
            <input type="submit" value="Valider" />
        </div>
    </form>
</div>
<?php }
/**
	Validation dans la bd
*/
function valider(){
	try{
		$pdo = Database::connect2db();
		$res = $pdo->prepare("SELECT * FROM moratoire WHERE IDMORATOIRE = :moratoire");
		$res->execute(array(
			'moratoire' => $_POST['idmor']
		));
		if($res->rowCount()){
			print "<p class = 'infos'>ID Moratoire ".parse($_POST['idmor'])." existent dans la BD</p>";
			ajouter();
			return;
		}
		/* Aucun moratoire pre existent sous cet id */
		$query = "INSERT INTO moratoire(IDMORATOIRE, MATEL, MONTANT, MONTANTUTILISE, DATEDEBUT, DATEFIN, LIBELLE, PERIODE) 
		VALUES(:moratoire, :matel, :montant, :montantutilise, :datedebut, :datefin, :libelle, :periode)";
		$res = $pdo->prepare($query);
		$res->execute(array(
			'moratoire' => $_POST['idmor'],
			'matel' => $_POST['matel'],
			'montant' => $_POST['montant'],
			'montantutilise' => 0,
			'datedebut' => parseDate($_POST['datedebut']),
			'datefin' => parseDate($_POST['datefin']),
			'libelle' => $_POST['libelle'],
			'periode' => $_SESSION['periode']
		));
		if($res->rowCount())
			@header('Location:moratoire.php');
		else{
			print "<p class = 'infos'>Ce moratoire n'a pas &eacute;t&eacute; ajout&eacute;</p>";
			ajouter();
		}
		$res->closeCursor();
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
	}
}
/*********************************************
*
*	Integration du modele de bas page
*
**********************************************/
require_once("../includes/footer_inc.php");
?>