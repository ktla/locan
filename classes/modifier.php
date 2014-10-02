<?php
	require_once("../includes/commun_inc.php");
	if(!isset($_SESSION['user']))	header("location:../utilisateurs/connexion.php");
	$titre = "Modification des classes";
/*
	Verification des droit d'acces a cette page
*/
	$codepage = "EDIT_CLASSE";
	require_once("../includes/header_inc.php");
	if(isset($_GET['id']))
		step1();
	elseif(isset($_POST['newid']))
		step2();
	/**********************************************
	*
	*	Fonction propres a la page
	*
	***********************************************/
function step1(){
	$query = "SELECT c.* , cp.TAILLEMAX, cp.ACTIF, cp.PROFPRINCIPAL, f.MONTANT AS MONTANTINSCRIPTION  
	FROM classe c 
	LEFT JOIN classe_parametre cp ON (cp.IDCLASSE = c.IDCLASSE AND cp.PERIODE = :periode) 
	LEFT JOIN classe_frais f ON (f.ID = cp.MONTANTINSCRIPTION) 
	WHERE c.IDCLASSE = :classe";
	$db = new Database($query, 0, array("classe" => $_GET['id'], "periode" => $_SESSION['periode']));
	if($db->select()){
		if(!$db->length){
			print "<p class = 'infos'>Aucune classe existent sous cet ID : ".$_GET['id']."</p>";
			return;
		}
		$row = $db->getRow(0);
		//print_r($row);
	}else
		die($db->getLog('error'));
?>
<script>
	function step1(){
		var obj = document.forms['frm'];
		if(obj.newid.value == "" || obj.libelle.value == "" || obj.montantinscription.value == "")
			alert("Remplir tous les champs obligatoires");
		else{
			if(!isFinite(obj.taillemax.value)){
				alert(decodeURIComponent("--Entrer un nombre positif pour la Capacité Max-.--"));
				return;
			}
			if(!isFinite(obj.montantinscription.value)){
				alert("--Entrer un nombre positif pour le Montant Inscription ");
				return;			
			}
			if(parseInt(obj.taillemax.value) < 0){
				alert(decodeURIComponent("--Définir une valeur > 0 pour Capacité Max---"));
				return;
			}
			if(parseInt(obj.montantinscription.value) < 0){
				alert(decodeURIComponent("--Définir une valeur > 0 pour le Montant Inscription --"));
				return;			
			}
			obj.submit();
		}
	}
</script>
<div id="zonetravail"><div class="titre">MODIFICATION CLASSE <?php echo $row->item("LIBELLE"); ?>.</div>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" name="frm" onSubmit="step1(); return false;" method="POST">
        <div class="cadre">
            <fieldset><legend>Nouvelle renseignements.</legend>
            <table cellspacing="5" style="margin:auto;">
                <tr><td class="lib">Identifiant : <span class="asterisque">*</span></td>
                    <td><input type="text" maxlength="8" value = "<?php echo $row->item("IDCLASSE"); ?>" name="newid"/></td>
                    <td class="lib">Libell&eacute; : <span class="asterisque">*</span></td>
                    <td><input type="text" maxlength="250" value = "<?php echo $row->item("LIBELLE"); ?>" name="libelle"/></td>
                </tr>
                <tr><td class="lib">Capacit&eacute; maximale : </td>
                    <td><input value = "<?php echo $row->item("TAILLEMAX"); ?>" type="text" name="taillemax" maxlength="4" /></td>
                    <td class="lib">Prof. principale : </td>
                    <td><?php $query = "SELECT  ID, CONCAT(NOMPROF,' ',PRENOM) FROM professeur ORDER BY NOMPROF";
                        $combo = new Combo($query, "profprincipal", 0, 1, true);
                        $combo->selectedid = $row->item("PROFPRINCIPAL");
                        $combo->first = "-Professeur Principal-";
                        $combo->view();
                    ?>
                    </td>
                </tr>
                <tr>
                		<td class="lib">Montant d'inscription</td>
                		<td><input type="text" value = "<?php echo $row->item("MONTANTINSCRIPTION"); ?>" name="montantinscription" maxlength="10" /></td>
                		<td class="lib">Niveau scolaire</td>
                		<td><?php
                				print "<select name = 'niveau' style = 'width:100%'>";
                			 	for($i = 1; $i < 10; $i++) {
                			 		if(!strcmp($row->item('NIVEAU'), $i))
                			 			print print "<option selected = 'selected' value = '".$i."'>Niveau ".$i."</option>";
                			 		else
                			 			print "<option value = '".$i."'>Niveau ".$i."</option>";
                			 	}
                			 	print "</select>";
                		?></td>
                </tr>
            </table>
          </fieldset>
         </div>
        <div style="color:red; font-size:10px; text-align:center; margin:0px; padding:0px;">Les champs marqu&eacute;s par * sont obligatoires</div>
        <div class="navigation">
        	<!-- Hidden values -->
            <input type="hidden" value="<?php echo $_GET['id']; ?>" name = 'ancienid' />
        	<input type="button" value="Annuler" onClick="rediriger('classe.php')"/>
        	<input type="submit" value="Terminer"/>
        </div>
    </form>
</div>
<?php }
/*
	Validation de classe et du professeur principal et insertion de montant des tranche
*/
function step2(){
	//Verification de la taille max
	if(!empty($_POST['taillemax'])){
		if(!is_numeric($_POST['taillemax']) || intval($_POST['taillemax']) <= 0){
			print "<p class=\"infos\">D&eacute;finir une valeur positive et > 0 pour Capacit&eacute; Maximale</p>";
			step1();
			return;
		}
	}else
		$_POST['taillemax'] = 0;
	if(!empty($_POST['montantinscription'])){
		if(!is_numeric($_POST['montantinscription'])){
			print "<p class=\"infos\">D&eacute;finir une valeur positive et > 0 pour le montant de l'inscription</p>";
			step1();
			return;
		}
	}else
		$_POST['montantinscription'] = 0;
/*
	Verification de  tous les parametre obligatoire
*/
	if(empty($_POST['newid']) || empty($_POST['libelle'])){
		print "<p class=\"infos\">Les champs marqu&eacute;s par * sont obligatoires</p>";
		step1();
		return;
	}
/*
	Validation de la classe
*/
	/* Verifier que l'ancien ID existe */
	if(!id_exist($_POST['ancienid'], "IDCLASSE", "classe")){
		print "<p class = 'infos'>Aucune classe avec l'identifiant : ".$_POST['ancienid']." existe dans la BD</p>";
		return;
	}
	/* Verifier que le nouveau ID n'existe si ancienid != newid */
	if(strcmp($_POST['ancienid'], $_POST['newid'])){
		if(id_exist($_POST['newid'], "IDCLASSE", "classe")){
			print "<p class = 'infos'>Une classe est dej&agrave; de&eacute;finie sous cet ID : ".$_POST['newid'];
			return;
		}
	}
	try{
		
		$pdo = Database::connect2db();
		$res = $pdo->prepare("SELECT c.*, cp.* 
		FROM classe c 
		LEFT JOIN classe_parametre cp ON (cp.IDCLASSE = c.IDCLASSE AND cp.PERIODE = :periode) 
		WHERE c.IDCLASSE = :classe");
		$res->execute(array(
			'classe' => $_POST['ancienid'],
			'periode' => $_SESSION['periode']
		));
		$row = $res->fetch(PDO::FETCH_ASSOC);
		/* update le montant inscription dans frais */
		$res = $pdo->prepare("UPDATE classe_frais SET MONTANT = :montantinscription WHERE ID = :id");
		$res->execute(array('montantinscription' => $_POST['montantinscription'], 'id' => $row['MONTANTINSCRIPTION']));
		/** update classe */
		$query = "UPDATE classe SET 
		IDCLASSE = :idclasse, LIBELLE = :libelle, NIVEAU = :niveau 
		WHERE IDCLASSE = :ancienid";
		$res = $pdo->prepare($query);
		$res->bindValue("ancienid", $_POST['ancienid'], PDO::PARAM_STR);
		$res->bindValue("idclasse", $_POST['newid'], PDO::PARAM_STR);
		$res->bindValue("libelle", $_POST['libelle'], PDO::PARAM_STR);
		$res->bindValue("niveau", $_POST['niveau'], PDO::PARAM_INT);
		$res->execute();
		/*
			Validation des parametres de la classe
		*/
		$query = "UPDATE classe_parametre 
		SET IDCLASSE = :idclasse, TAILLEMAX = :taillemax, PROFPRINCIPAL = :prof 
		WHERE IDCLASSE = :ancienid AND PERIODE = :periode";
		$res = $pdo->prepare($query);
		$res->bindValue("ancienid", $_POST['ancienid'], PDO::PARAM_STR);
		$res->bindValue("idclasse", $_POST['newid'], PDO::PARAM_STR);
		$res->bindValue("periode", $_SESSION['periode'], PDO::PARAM_STR);
		$res->bindValue("taillemax", $_POST['taillemax'], PDO::PARAM_INT);
		$res->bindValue("prof", $_POST['profprincipal'], PDO::PARAM_STR);
		$res->execute();
		$res->closeCursor();
		@header("location:detail.php?id=".$_POST['newid']);
	}catch(PDOException $e){
		die($e->getMessage()." ".$e->getLine()." ".$e->getFile());
	}
}
/******************************************
*	
*	Integration du modele de bas de page
*
*****************************************/
require_once("../includes/footer_inc.php");
?>
