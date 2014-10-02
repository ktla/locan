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
if(!isset($_SESSION['user']))	@header("location:../utilisateurs/connexion.php");
/*
	Verification du droit d'acces de cette page
	Verifier que le codepage existe dans nos listedroit, ceci
	empeche de proceder par saisie de l'url et d'acceder a la page
*/
$codepage = "EDIT_COMPTE";
	$titre = "Modification de comptes";
	require_once("../includes/header_inc.php");
	if(isset($_POST['newcompte'])){
		valider();
	}else{
		modifier();
	}
/**
	Function propre a la page
*/
function modifier(){
	try{
		$pdo = Database::connect2db();
		$res = $pdo->prepare("SELECT * FROM compte WHERE IDCOMPTE = :idcompte");
		$res->bindValue('idcompte', $_GET['id'], PDO::PARAM_STR);
		$res->execute();
		if(!$res->rowCount()){
			$_SESSION['infos'] = "Aucun Compte existent sous ID ".parse($_GET['id']);
			@header("Location:compte.php");
			return;
		}
		$row = $res->fetch(PDO::FETCH_ASSOC);
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
	}
	?>
<script>
	function modifier(){
		var obj = document.forms['frm'];
		if(obj.newcompte.value == "" || obj.newcorrespondant.value == "")
			alert("Remplir tous les champs!!!");
		else
			obj.submit();
	}
</script>
<div id="zonetravail"><div class="titre">MODIFICATION DU COMPTE <?php echo $row['IDCOMPTE']; ?></div>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="modifier(); return false;" name="frm" method="post" enctype="multipart/form-data">
	<div class="cadre">
    	<fieldset><legend>Renseignements sur le nouveau compte.</legend>
        	<table cellspacing="10"><tr>
				<td class="lib">Correspondant du compte: </td><td>
                <?php 
					$query = "SELECT e.MATEL, CONCAT(e.NOMEL, ' ', e.PRENOM) 
					FROM eleve e 
					INNER JOIN compte c ON (c.CORRESPONDANT = e.MATEL) 
					UNION 
					SELECT p.IDPROF, CONCAT(p.NOMPROF, ' ', p.PRENOM) 
					FROM professeur p 
					INNER JOIN compte c ON (c.CORRESPONDANT = p.IDPROF) 
					UNION 
					SELECT s.IDSTAFF, CONCAT(s.NOM, ' ', s.PRENOM) 
					FROM staff s 
					INNER JOIN compte c ON (c.CORRESPONDANT = s.IDSTAFF)";
					$combo = new Combo($query, 'newcorrespondant', 0, 1, isset($row['CORRESPONDANT']) ? true : false);
					$combo->first = "--Choisir le propri&egrave;taire du compte--";
					$combo->selectedid = isset($row['CORRESPONDANT']) ? $row['CORRESPONDANT'] : "";
					$combo->view('200px');
				?>
                </td>
                <td title="Doit etre unique" class="lib">ID du compte:</td>
                <td><input type="text" name="newcompte" value="<?php echo isset($row['IDCOMPTE']) ? $row['IDCOMPTE'] : ""; ?>" /></td>
             </tr>
            </table>
        </fieldset>
    </div>
    <div class="navigation">
    	<input type="button" onClick="rediriger('compte.php')" value="Annuler"/>
        <input type="hidden" name="anciencompte" value="<?php echo $row['IDCOMPTE']; ?>"  />
        <input type="hidden" name="anciencorrespondant" value="<?php echo $row['CORRESPONDANT']; ?>"  />
        <input type="submit" value="Valider"/>
    </div>
</form>
</div>
<?php }
/**
	Validation des donnees dans la BD
*/
function valider(){
	try{
		$pdo = Database::connect2db();
		/* verifier que l'anciencompte existe */
		if(!id_exist($_POST['anciencompte'], 'IDCOMPTE', 'compte')){
			$_SESSION['infos'] = "Le compte &agrave; modifier n'existe pas dans la BD";
			@header("Location:compte.php");
			return;
		}
		/* Verifier l'unicite du nouveau compte */
		if(strcmp($_POST['newcompte'], $_POST['anciencompte'])){
			if(id_exist($_POST['newcompte'], 'IDCOMPTE', 'compte')){
				print "<p class='infos'>Compte ".parse($_POST['newcompte'])." existent dans la BD</p>";
				return;
			}
		}
		/* Update du compte */
		$query = "UPDATE compte SET IDCOMPTE = :newcompte, CORRESPONDANT = :newcorrespondant, AUTEUR = :newauteur 
		WHERE IDCOMPTE = :anciencompte";
		$res = $pdo->prepare($query);
		$res->execute(array(
			'newcompte' => $_POST['newcompte'],
			'anciencompte' => $_POST['anciencompte'],
			'newcorrespondant' => $_POST['newcorrespondant'],
			'newauteur' => $_SESSION['user']
		));
		if($res->rowCount()){
			$_SESSION['infos'] = 'Compte '.parse($_POST['anciencompte']).' modifi&eacute; avec succ&egrave;s';
		}else
			$_SESSION['infos'] = 'Compte '.parse($_POST['anciencompte']).' non modifi&eacute;';
		/* Rediriger vers la liste des comptes */
		@header("Location:compte.php");
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getFile()." : ".$e->getLine());
	}

}
/**************************************************
*
*	Integration du modele de bas de page
*
***************************************************/
require_once("../includes/footer_inc.php");
?>