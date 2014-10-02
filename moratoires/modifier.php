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
	$codepage = "EDIT_MORATOIRE";
$titre = "Modification d'un moratoire";
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
	modifier();
/*****************************************
*
*	Functions propres a la page
*
******************************************/
function modifier(){
	$pdo = Database::connect2db();
	$res = $pdo->prepare('SELECT * FROM moratoire WHERE IDMORATOIRE = :moratoire');
	$res->bindValue('moratoire', $_GET['id'], PDO::PARAM_STR);
	$res->execute();
	if(!$res->rowCount()){
		print "<p class = 'infos'>Aucun moratoire avec l'ID".$_GET['id']." n'existe dans la BD</p>";
		return;
	}
	$row = $res->fetch(PDO::FETCH_ASSOC);
?>
<script>
	function loadIdCompte(){
		var frm = document.forms['frm'];
		var id = 'id=' + frm.matel.value + '&action=moratoire';
		callajax('ajouter2.php?' + id, 'compte', 'loader');
		document.getElementById('compte').setAttribute('class', 'lib');
	}
	/* Gerer les champs obligatoires */
	function modifierMoratoire(){
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
<div id="zonetravail"><div class="titre">MODIFICATION DE MORATOIRE <?php echo $_GET['id']; ?></div>
    <form name="frm" action="modifier.php" method="post" enctype="multipart/form-data" onsubmit="modifierMoratoire(); return false;">
        <div class="cadre">
            <fieldset><legend>Informations sur le moratoire</legend>
                <table style="margin:auto" cellspacing="5">
                	<tr><td colspan="5" align="center"><img src="../images/loader.gif" id="loader" style="visibility:hidden" /></td></tr>
                    <tr>
                        <td class="lib">Choisir l'&eacute;l&egrave;ve : <span class="asterisque">*</span></td>
                        <td><?php 
                            $combo = new Combo("SELECT MATEL, CONCAT(NOMEL,' ', PRENOM) FROM eleve ORDER BY MATEL", "matel", 0, 1, true);
                            $combo->first = "-Choisir le concerner-";
							$combo->selectedid = $row['MATEL'];
                            $combo->onchange = 'loadIdCompte();';
                            $combo->view('200px'); ?>
                         </td>
                         <td class="lib">Compte associ&eacute;</td>
                         <td><span id="compte" class="lib"><?php print format('Aucun compte associ&eacute;', 2);?></span></td>
                    </tr>
                    <tr>
                    	<td class="lib">Libell&eacute; : <span class="asterisque">*</span></td>
                        <td><input type="text" name="libelle" maxlength="150" value="<?php echo $row['LIBELLE'] ? $row['LIBELLE']:""; ?>" /></td>
                        <td class="lib">Nouveau Moratoire : <span class="asterisque">*</span></td>
                        <td><input type="text" name="idmor" maxlength="15"  value="<?php echo $row['IDMORATOIRE'] ? $row['IDMORATOIRE']:""; ?>" /></td>
                    </tr>
                    <tr>
                        <td class="lib">Date de d&eacute;but : <span class="asterisque">*</span></td>
                        <td><input type="text" name="datedebut" id="datedebut"  value="<?php echo $row['DATEDEBUT'] ? $row['DATEDEBUT']:""; ?>"/></td>
                        <td class="lib">Date de fin : <span class="asterisque">*</span></td>
                        <td><input type="text" name="datefin" id="datefin"  value="<?php echo $row['DATEFIN'] ? $row['DATEFIN']:""; ?>"/></td>
                    </tr>
                    <tr>
                    	<td class="lib">Montant du Moratoire <span class="asterisque">*</span></td>
                        <td><input type="text" name="montant" maxlength="30" value="<?php echo $row['MONTANT'] ? $row['MONTANT']:""; ?>"/></td>
                  		<td class="lib">Montant utilis&eacute;</td>
                        <td><input  type="text" name="montantutilise" readonly  value="<?php echo $row['MONTANTUTILISE'] ? $row['MONTANTUTILISE']:""; ?>"/></td>
                    </tr>
                    <tr>
                    	<td colspan="4" style="text-align:center; font-size:10px; color:red">Les champs marqu&eacute;s par * sont obligatoires</td>
                    </tr>
                </table>
            </fieldset>
        </div>
        <div class="navigation">
        	<input type="hidden" name="ancienmoratoire" value="<?php echo $_GET['id']; ?>" />
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
		/* Verifier que l'ancien moratoire existe */
		$pdo = Database::connect2db();
		$res = $pdo->prepare("SELECT * FROM moratoire WHERE IDMORATOIRE = :moratoire");
		$res->execute(array(
			'moratoire' => $_POST['ancienmoratoire']
		));
		if(!$res->rowCount()){
			print "<p class = 'infos'>ID Moratoire ".parse($_POST['ancienmoratoire'])." non existent dans la BD</p>";
			return;
		}
		/* Verifier que le nouveau id moratoire n'existe pas deja 
			Si le nouveau id moratoire est different de l'ancien, donc verifier
		*/
		if(strcmp($_POST['idmor'], $_POST['ancienmoratoire'])){
			$res->execute(array(
				'moratoire' => $_POST['idmor']
			));
			if($res->rowCount()){
				print "<p class = 'infos'>Ce nouveau ID moratoire ".$_POST['idmor']." existe d&eacute;j&agrave; dans la BD</p>";
				@header("Location:modifier.php?id=".$_POST['ancienmoratoire']);
				return;
			}
		}
		/* Update de ce moratoire si tout est okay */
		$query = "UPDATE moratoire SET IDMORATOIRE = :moratoire, MATEL = :matel, MONTANT = :montant, DATEDEBUT = :datedebut, 
		DATEFIN = :datefin, LIBELLE = :libelle, PERIODE = :periode 
		WHERE IDMORATOIRE = :ancienmoratoire";
		$res = $pdo->prepare($query);
		$res->execute(array(
			'ancienmoratoire' => $_POST['ancienmoratoire'],
			'moratoire' => $_POST['idmor'],
			'matel' => $_POST['matel'],
			'montant' => $_POST['montant'],
			'datedebut' => parseDate($_POST['datedebut']),
			'datefin' => parseDate($_POST['datefin']),
			'libelle' => $_POST['libelle'],
			'periode' => $_SESSION['periode']
		));
		if($res->rowCount()){
			$_SESSION['infos'] = 'Moratoire '.$_POST['ancienmoratoire'].' mis &agrave; jour avec succ&egrave;s</p>';
			@header('Location:moratoire.php');
		}else{
			$_SESSION['infos'] = "Ce moratoire n'a pas &eacute;t&eacute; mis &agrave; jour";
			@header("Location:modifier.php?id=".$_POST['ancienmoratoire']);
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