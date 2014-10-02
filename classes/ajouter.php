<?php
	require_once("../includes/commun_inc.php");
	if(!isset($_SESSION['user']))	header("location:../utilisateurs/connexion.php");
	$titre = "Cr&eacute;ation et ajout des classes";
/*
	Verification des droit d'acces a cette page
*/
	$codepage = "ADD_CLASSE";
	require_once("../includes/header_inc.php");
	if(isset($_POST['identifiant']))
		step2();
	else
		step1();
	require_once("../includes/footer_inc.php");
function step1(){?>
<script>
	function step1(){
		var obj = document.forms['frm'];
		if(obj.identifiant.value == "" || obj.libelle.value == "" || obj.montantinscription.value == "" || obj.profprincipal.value == "")
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
<div id="zonetravail"><div class="titre">CREATION D'UNE CLASSE.</div>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" name="frm" onSubmit="step1(); return false;" method="POST">
        <div class="cadre">
            <fieldset><legend>Renseignements sur la nouvelle classe.</legend>
            <table cellspacing="5" style="margin:auto;">
                <tr><td class="lib">Identifiant : <span class="asterisque">*</span></td>
                    <td><input type="text" maxlength="8" value = "<?php if(isset($_POST['identifiant'])) echo $_POST['identifiant']; ?>" name="identifiant"/></td>
                    <td class="lib">Libell&eacute; : <span class="asterisque">*</span></td>
                    <td><input type="text" maxlength="250" value = "<?php if(isset($_POST['libelle'])) echo $_POST['libelle']; ?>" name="libelle"/></td>
                </tr>
                <tr><td class="lib">Capacit&eacute; maximale : </td>
                    <td><input value = "<?php if(isset($_POST['taillemax'])) echo $_POST['taillemax']; ?>" type="text" name="taillemax" maxlength="4" /></td>
                    <td class="lib">Prof. principale : <span class="asterisque">*</span></td>
                    <td><?php $query = "SELECT ID, CONCAT(NOMPROF,' ',PRENOM) FROM professeur ORDER BY NOMPROF";
                        $combo = new Combo($query, "profprincipal", 0, 1, (isset($_POST['profprincipal']))? true:false);
                        if(isset($_POST['profprincipal']))
                        		$combo->selectedid = $_POST['profprincipal'];
                        $combo->first = "-Professeur Principal-";
                        $combo->view();
                    ?>
                    </td>
                </tr>
                <tr>
                		<td class="lib">Montant d'inscription</td>
                		<td><input type="text" value = "<?php if(isset($_POST['montantinscription'])) echo $_POST['montantinscription']; ?>" name="montantinscription" maxlength="10" /></td>
                		<td class="lib">Niveau scolaire</td>
                		<td><?php
                				print "<select name = 'niveau' style = 'width:100%'>";
                			 	for($i = 1; $i < 10; $i++) {
                			 		if(isset($_POST['niveau']) && !strcmp($_POST['niveau'], $i))
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
        <div class="navigation"><input type="button" value="Annuler" onClick="rediriger('classe.php')"/>
        	<input type="submit" value="Suivant"/>
        </div>
    </form>
</div>
<?php }
/*
	Validation de classe et du professeur principal et insertion de montant des tranche
*/
function step2(){
	if(empty($_POST['profprincipal']) || !isset($_POST['profprincipal'])){
		print "<p class = 'infos'>Veuillez choisirlLe professeur principal. Champ obligatoire</p>";
		step1();
		return;
	}
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
	if(empty($_POST['identifiant']) || empty($_POST['libelle'])){
		print "<p class=\"infos\">Les champs marqu&eacute;s par * sont obligatoires</p>";
		step1();
		return;
	}
/*
	Validation de la classe
*/
	if(!id_exist($_POST['identifiant'], "IDCLASSE", "classe")){
		try{
			$pdo = Database::connect2db();
			$query = "INSERT INTO classe (IDCLASSE, LIBELLE, NIVEAU) VALUES(:idclasse, :libelle, :niveau)";
			$res = $pdo->prepare($query);
			$res->bindValue("idclasse", $_POST['identifiant'], PDO::PARAM_STR);
			$res->bindValue("libelle", $_POST['libelle'], PDO::PARAM_STR);
			$res->bindValue("niveau", $_POST['niveau'], PDO::PARAM_INT);
			$res->execute();
			/*
				Inserer le frais d'inscription dans classe_frais
			*/
			$res = $pdo->prepare("INSERT INTO classe_frais(CODE, IDCLASSE, LIBELLE, DATEDEBUT, DATEFIN, MONTANT, TYPE, PERIODE) 
			VALUES(:code, :idclasse, :libelle, :datedebut, :datefin, :montant, :type, :periode)");
			$d = substr($_SESSION['periode'], 0, 4);
			$f = substr($_SESSION['periode'], strpos($_SESSION['periode'], '-') + 1, 4);
			//print $d." et ".$f;
			$res->execute(array(
				'code' => 'DI',
				'idclasse' => $_POST['identifiant'],
				'libelle' => 'Montant Inscription',
				'datedebut' => $d."-09-01",
				'datefin' => $f."-06-30",
				'montant' => $_POST['montantinscription'], 
				'type' => 0,
				'periode' => $_SESSION['periode']
			));
			$idfrais = $pdo->lastInsertId();
			/*
				Validation des parametres de la classe
			*/
			$query = "INSERT INTO classe_parametre (IDCLASSE, MONTANTINSCRIPTION, PERIODE, TAILLEMAX, ACTIF, PROFPRINCIPAL) 
			VALUES (:idclasse, :montantinscription, :periode, :taillemax, :actif, :prof)";
			$res = $pdo->prepare($query);
			$res->bindValue("idclasse", $_POST['identifiant'], PDO::PARAM_STR);
			$res->bindValue("montantinscription", $idfrais, PDO::PARAM_INT);
			$res->bindValue("periode", $_SESSION['periode'], PDO::PARAM_STR);
			$res->bindValue("taillemax", $_POST['taillemax'], PDO::PARAM_INT);
			$res->bindValue("prof", $_POST['profprincipal'], PDO::PARAM_STR);
			$res->bindValue("actif", 1, PDO::PARAM_INT);
			$res->execute();
			$res->closeCursor();
			@header("location:classe.php");
			/*print "<script>rediriger('classe.php');</script>";*/
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." ".$e->getLine()." ".$e->getFile());
		}
	}else {
		print "<p class = 'infos'>Classe avec ID : ".$_POST['identifiant']." existe d&eacute;j&agrave; dans la BD</p>";
		step1();	
	}
}
?>
