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
$codepage = "ADD_COMPTE";
	$titre = "Gestion des comptes";
	require_once("../includes/header_inc.php");
	if(isset($_POST['idcompte'])){
		valider();
	}else{
		step1();
	}
/**
	Function propre a la page
*/
function valider(){
	try{
		$pdo = Database::connect2db();
		$res = $pdo->prepare("SELECT * FROM compte WHERE IDCOMPTE = :idcompte");
		$res->execute(array(
			'idcompte' => $_POST['idcompte']
		));
		if($res->rowCount()){
			print "<p class=\"infos\">Compte existant sous l'identifiant : ".$_POST['idcompte']."</p>";
			step1();
			return;
		}
		/*
			Valider les informations dans la BD
		*/
		/*
			Requete
		*/
		$query = "INSERT INTO compte(IDCOMPTE, CORRESPONDANT, DATECREATION, PERIODE, AUTEUR) 
					VALUES(:idcompte, :correspondant, :datecreation, :periode, :auteur)";
		$res = $pdo->prepare($query);
		$res->execute(array(
			'idcompte' => $_POST['idcompte'],
			'correspondant' => $_POST['correspondant'],
			'datecreation' => date("Y-m-d", time()),
			'periode' => $_SESSION['periode'],
			'auteur' => $_SESSION['user']
		));
		if($res->rowCount()){
			$_SESSION['infos'] = 'Compte '.parse($_POST['idcompte']).' cr&eacute;&eacute; avec succ&egrave;s';
		}
		@header("Location:compte.php");
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getFile()." : ".$e->getLine());
	}

}
function step1(){?>
<script>
	function step1(){
		var obj = document.forms['frm'];
		if(obj.idcompte.value == "" || obj.correspondant.value == "")
			alert("Remplir tous les champs!!!");
		else
			obj.submit();
	}
</script>
<div id="zonetravail"><div class="titre">CREATION D'UN COMPTE</div>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="step1(); return false;" name="frm" method="post" enctype="multipart/form-data">
	<div class="cadre">
    	<fieldset><legend>Renseignements sur le compte.</legend>
        	<table cellspacing="10"><tr>
				<td class="lib">Correspondant du compte: </td><td>
                <?php 
					$query = "SELECT e.MATEL, CONCAT(e.NOMEL, ' ', e.PRENOM) 
					FROM eleve e 
					WHERE e.MATEL NOT IN (SELECT c.CORRESPONDANT FROM compte c) 
					UNION 
					SELECT p.IDPROF, CONCAT(p.NOMPROF, ' ', p.PRENOM) 
					FROM professeur p 
					WHERE p.IDPROF NOT IN (SELECT c.CORRESPONDANT FROM compte c) 
					UNION 
					SELECT s.IDSTAFF, CONCAT(s.NOM, ' ', s.PRENOM) 
					FROM staff s 
					WHERE s.IDSTAFF NOT IN (SELECT c.CORRESPONDANT FROM compte c)";
					$combo = new Combo($query, 'correspondant', 0, 1, isset($_POST['correspondant']) ? true : false);
					$combo->first = "--Choisir le propri&egrave;taire du compte--";
					$combo->selectedid = isset($_POST['correspondant']) ? $_POST['correspondant'] : "";
					$combo->view('200px');
				?>
                </td>
                <td title="Doit etre unique" class="lib">ID du compte:</td>
                <td><input type="text" name="idcompte"/></td>
             </tr>
            </table>
        </fieldset>
    </div>
    <div class="navigation">
    	<input type="button" onClick="rediriger('ajouter.php')" value="Annuler"/>
        <input type="submit" value="Valider"/>
    </div>
</form>
</div>
<?php }
/**************************************************
*
*	Integration du modele de bas de page
*
***************************************************/
require_once("../includes/footer_inc.php");
?>