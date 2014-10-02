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
	$codepage = "INSCRIPTION_ELEVE";
/********************************************************************************************************/
	$titre = "Nouvelle inscription";
	require_once("../includes/header_inc.php");
	if(isset($_POST['step'])){
		/* Le step suivant c est step actuel + 1*/
		switch(intval($_POST['step']) + 1){
			case 2:step2();break;
			case 3:step3();break;
			case 4:step4();break;
			default:step1();break;
		}
	}else
		step1();
		
/*
	Fonctions relatives a la page
*/
function step1(){
?>
<script>
	function step1(){
		var obj = document.forms["frm"];
		if(obj.matel.value == "" || obj.classe.value == "")
			alert("-Tous les champs sont obligatoires-");
		else
			obj.submit();
	}
</script>
<div id="zonetravail"><div class="titre">NOUVELLE INSCRIPTION</div>
    <form name="frm" action="nouvelle.php" enctype="multipart/form-data" onSubmit="step1(); return false;" method="POST">
    	<div class="cadre">
        	<fieldset><legend>Inscription</legend>
            	<?php
				try{
				/*
					Selectionner les eleves non inscrit dans une periode de l'etablissement, nouvo eleves
				*/
					$query = "SELECT e.MATEL, CONCAT(e.NOMEL, ' ', e.PRENOM) AS NOM  
					FROM eleve e 
					WHERE e.MATEL NOT IN (SELECT i.MATEL FROM inscription i) 
					ORDER BY e.MATEL";
					$pdo = Database::connect2db();
					$res = $pdo->query($query);
					if(!$res->rowCount()){
						print "<p class=\"infos\">Aucun nouvel El&egrave;ve &agrave; inscrire<br/><br/>";
						print "Cr&eacute;er une instance de cet &eacute;l&egrave;ve <a href = '../eleves/ajouter.php'> <blink>ICI</blink> </a> avant de proc&eacute;der &agrave; l'inscription</p>";
						return;
					}
					$res->closeCursor();
				}catch(PDOException $e){
					die($e->getMessage()." ".__LINE__." ".__FILE__);
				}
					print "<table><tr><td class = 'lib'>El&egrave;ve : </td><td>";
					$eleve = new Combo($query, "matel", 0, 1, false);
					$eleve->first = "-Choisir un &eacute;l&egrave;ve-";
					$eleve->view();
					print "</td><td class = 'lib'>Classe : </td><td>";
					$classe = new combo("SELECT IDCLASSE, LIBELLE FROM classe ORDER BY IDCLASSE", "classe", 0, 1, false);
					$classe->first = "-Choisir une classe-";
					$classe->view();
					print "</td></tr></table>";
				?>
            </fieldset>
        </div>
        <div class="navigation">
        	<input type="button" value="Annuler" onclick="home();"/>
            <input type="hidden" value="1" name="step" />
            <input type="submit" value="Valider"/>
        </div>
   </form>
</div>
<?php 
}
/***********************************************
*
*	AFFICHE LES INFOS DE L'ELEVE POUR SE 
*	RASSURER QUE C'EST LUI AINSI QUE LES
*	MONTANT A PAYER, PUIS SUGGERER UNE VALIDATION
*
********************************************/
function step2(){
	if(!isset($_POST['matel']) || !isset($_POST['classe'])){
		step1();
		return;
	}
	if(empty($_POST['matel']) || empty($_POST['classe'])){
		print "<p class = 'infos'>Matricule El&egrave;ve et Identifiant classe non d&eacute;finis&nbsp;&nbsp;";
		print "<a href = 'nouvelle.php' title = 'Definir ces valeurs ici' >Ici</a></p>";
		return;
	}
?>
<div id="zonetravail"><div class="titre" >Inscription | V&eacute;rification d'infos </div>
<form style="margin:0" name="frm" action="nouvelle.php" onsubmit="moratoire();return false;" method="POST" enctype="multipart/form-data">
	<div class="cadre"><?php if(!afficherEntete()) return; ?>
    <table cellspacing="2" width="100%">
    	<tr valign="top"><td width="60%"><fieldset><legend>Frais obligatoires</legend>
    	<?php
		try{
			$grid = new Grid("
			SELECT f.CODE, f.LIBELLE, f.MONTANT, f.DATEFIN  
			FROM classe_frais f 
			WHERE f.IDCLASSE = :classe AND f.PERIODE = :periode AND f.TYPE = 0", 0);
			$grid->param = array("classe" => $_POST['classe'], "periode" => $_SESSION['periode']);
			$grid->addcolonne(0, "CODE", "CODE", true);
			$grid->addcolonne(1, "LIBELLE", "LIBELLE", true);
			$grid->addcolonne(2, "MONTANT", "MONTANT", true);
			$grid->addcolonne(3, "ECHEANCE", "DATEFIN", true);
			$grid->actionbutton = false;
			$grid->display('100%', '100px');
			unset($grid);
	?></fieldset>
    </td><td width="40%"><fieldset><legend>Frais occasionnels</legend>
    <?php
		$occasionel = new Grid("SELECT f.ID, f.CODE, f.LIBELLE, f.MONTANT 
			FROM classe_frais f 
			WHERE f.IDCLASSE = :classe AND f.PERIODE = :periode AND f.TYPE = 1", 0);
		$occasionel->param = array("classe" => $_POST['classe'], "periode" => $_SESSION['periode']);
		$occasionel->addcolonne(0, "ID", "ID", false);
		$occasionel->addcolonne(1, "CODE", "CODE", true);
		$occasionel->addcolonne(2, "LIBELLE", "LIBELLE", true);
		$occasionel->addcolonne(3, "MONTANT", "MONTANT", true);
		$occasionel->actionbutton = false;
		$occasionel->selectbutton = true;
		$occasionel->display('100%', '100px');
	?></fieldset>
    </td></tr>
    <tr><td colspan="2"><fieldset style="max-height:100px; overflow:auto;"><legend>R&eacute;duction appliqu&eacute;e</legend>
   		<table class="grid" width="100%">
        <thead>
            <tr><th></th><th>CODE</th><th>Appliqu&eacute;e au</th><th>LIBELLE</th><th>Exprim&eacute; en</th><th>MONTANT</th></tr>
        </thead>
        <?php
		$param = array("classe" => $_POST['classe'], "periode" => $_SESSION['periode']);
		$db = new Database("SELECT r.*, f.LIBELLE AS APPLIQUEA 
		FROM classe_reduction r 
		LEFT JOIN classe_frais f ON (r.IDFRAIS = f.ID) 
		WHERE r.IDFRAIS IN 
		(SELECT f2.ID FROM classe_frais f2 WHERE f2.IDCLASSE = :classe AND f2.PERIODE = :periode)", 0, $param);
		if($db->select()){
			if($db->length){
				foreach($db->data as $row){
					print "<tr><td><input type=\"checkbox\"  name =\"reduction[]\" value = \"".$row->item('ID')."/></td>";
					print "<td>".$row->item("CODE")."</td><td>".$row->item("APPLIQUEA")."</td><td>".$row->item("LIBELLE")."</td>";
					print "<td>".$row->item("TYPE")."</td><td>".$row->item("MONTANT")."</td></tr>";
				}
			}else
				print "<tr><td colspan=\"6\" align=\"center\">AUCUNE REDUCTION ENREGISTREE...</td></tr>";
		}
		?>
        </tbody></table>
    </fieldset></td></tr>
    </table>
    </div>
    <div class="navigation">
    	<input type="hidden" value="<?php echo $_POST['classe']; ?>" name="classe" />
        <input type="hidden" value="<?php echo $_POST['matel']; ?>" name="matel" />
        <input type="button" value="Retour" onclick="rediriger('nouvelle.php');" />
        <input type="hidden" value="2" name="step" />
        <input type="submit" value="Suivant" />
    </div>
</form>
</div>
<?php
	}catch(PDOException $e){
		die("Error Step 3 ".$e->getMessage().__LINE__.__FILE__);
	}
}
/***********************************************************
*
*	Afficher les dernieres infos avec prise en compte des
*	montant a payer et evoquer un moratoire
*
************************************************************/
function step3(){
	if(!isset($_POST['matel']) || !isset($_POST['classe'])){
		step1();
		return;
	}
	if(empty($_POST['matel']) || empty($_POST['classe'])){
		print "<p class = 'infos'>Matricule El&egrave;ve et Identifiant classe non d&eacute;finis&nbsp;&nbsp;";
		print "<a href = 'nouvelle.php' title = 'Definir ces valeurs ici' >Ici</a></p>";
		return;
	}
?>
<div id="zonetravail"><div class="titre">Nouvelle Inscription | V&eacute;rifier les montants</div>
<form action="nouvelle.php" method="POST" enctype="multipart/form-data">
	<div class="cadre">
    <?php if(!afficherEntete()) return;
	try{
		/* Infos sur la classe en question */
		$pdo = Database::connect2db();
		$query = "SELECT c.*, p.* 
		FROM classe c 
		LEFT JOIN classe_parametre p ON (p.IDCLASSE = c.IDCLASSE) 
		WHERE c.IDCLASSE = :classe";
		$classe = $pdo->prepare($query);
		$classe->execute(array(
			"classe" => $_POST['classe']
		));
		$classerow = $classe->fetch(PDO::FETCH_BOTH);
		$inscription = $classerow["MONTANTINSCRIPTION"];
		/* Infos sur frais obligatoires */
		$obli = $pdo->prepare("SELECT SUM(MONTANT) AS MONTANT 
			FROM classe_frais f WHERE f.IDCLASSE = :classe AND f.PERIODE = :periode AND f.TYPE = '0'");
		$obli->execute(array(
			"periode" => $_SESSION['periode'], 
			"classe" => $_POST['classe']
		));
		$oblirow = $obli->fetch(PDO::FETCH_BOTH);
		$sumobligatoire = $oblirow['MONTANT'];
		/* Frais occasionnels */
		$sumoccasionnel = 0;
		if(isset($_POST['chk']) && !empty($_POST['chk'])) {
			$occasion = "(".implode(",", $_POST['chk']).")";
			$occa = $pdo->prepare("SELECT SUM(o.MONTANT) AS MONTANT 
			FROM classe_frais o 
			WHERE o.IDCLASSE = :classe AND o.PERIODE = :periode AND o.TYPE = '1' AND o.ID IN $occasion");
			$occa->execute(array(
				"periode" => $_SESSION['periode'], 
				"classe" => $_POST['classe']
			));
			$occarow = $occa->fetch(PDO::FETCH_BOTH);
			$sumoccasionnel = ($occarow['MONTANT']>0)?$occarow['MONTANT']:0;
		}
		/* 
			Selectionner les reduction, multiplier par montant frais si exprimer en pourcentage
			creer une table temp, y stocker montantfrais, montantreduction, typereduction 
			appliquer la formule reduction = IF( type = pourcentage, 
			multiplier montantfrais par montantreduction, sinon return montantreduction
			J'avais fai ca en une seule requete, mais ca marche pas, j'ai dc decouper
		*/
		$sumreduction = 0;
		if(isset($_POST['reduction']) && !empty($_POST['reduction'])){
			$reduction = "(".implode(",", $_POST['reduction']).")";
			$pdo->exec("-- Creation de la table temporaire -- 
			CREATE TEMPORARY TABLE tmpfrais(
				MONTANTREDUCTION double,
				MONTANTFRAIS double,
				TYPEREDUCTION varchar(15))");
			$pdo->exec("-- Verouiller les tables impliquees  
			LOCK TABLES classe_frais f READ, classe_reduction r READ;");
			$pdo->exec("-- Remplir la table tmp --
			INSERT INTO tmpfrais 
				SELECT r.MONTANT, f.MONTANT, r.TYPE 
				FROM classe_reduction r 
				LEFT JOIN classe_frais f ON (r.IDFRAIS = f.ID) WHERE r.ID IN $reduction");
			$reduction = $pdo->query("
			-- Selectionner et multiplier --
			SELECT IF( TYPEREDUCTION = 'pourcentage', (MONTANTREDUCTION*MONTANTFRAIS)/100, MONTANTREDUCTION) AS MONTANT 
				FROM tmpfrais");
			$pdo->exec("
			-- Deverouiller et tmpfrais se supprime directement 
			UNLOCK TABLES");
			$pdo->exec("DROP TABLE tmpfrais");
			$reductionrow = $reduction->fetch(PDO::FETCH_BOTH);
			$sumreduction = ($reductionrow['MONTANT']>0)?$reductionrow['MONTANT']:0;
		}
		/* Afficher les informations */
		$total = intval($sumobligatoire) + intval($sumoccasionnel) - intval($sumreduction);
		$solde = 0; $moratoire = 0;
		$account = checkaccount($_POST['matel'], $inscription, $solde, $moratoire); //Cf commun_lib.php
		print $inscription;
		?>
        <table width = "85%" border = '1' cellpadding="3" cellspacing="1" style="border-collapse:collapse">
			<tr><td width = '70%'>Classe</td><td width = '30%'><?php echo $classerow['LIBELLE']; ?></td></tr>
			<tr><td>Montant Frais obligatoires</td><td align = 'right'><?php echo format($sumobligatoire);?></td></tr>
			<tr><td>Montant Frais occasionnels</td><td align = 'right'><?php echo format($sumoccasionnel,0);?></td></tr>
			<tr><td>Montant Frais r&eacute;ductions</td><td align = 'right'><?php echo format($sumreduction,0);?></td></tr>
			<tr><td>Montant Total &agrave; payer</td><td align = 'right'><?php echo format($total,0);?></td></tr>
			<tr><td><?php echo format("Solde actuelle de l'El&egrave;ve", 2);?></td><td align = 'right'><?php echo format($solde);?></td></tr>
       </table>
     <?php
	/******* MORATOIRE *******************
		 Si le solde est inferieur au montant de l'inscription, suggerer le moratoire 	
	*/?>
	<p style = 'text-align:center;font-weight:bold;margin:0px;'>Votre compte sera d&eacute;bit&eacute; de : 
	<?php echo format($inscription, 1); ?></p><?php 
	if(!$account){?>
    	<div style = 'border:1px solid #f2f2f2;margin:0px;padding:2px;'>
			<p style = 'text-align:center;margin:2px;'>Votre solde est insuffisant de <?php echo format($inscription - $solde); ?>,
             veuillez entrer un moratoire ou effectuer une operation sur ce compte <?php 
			 print "<a href=\"../comptes/operation.php?operation=".$GLOBALS["compte"]."&classe=".$GLOBALS["classe"]."\">Ici</a>";?><br/><br/>
          <label style = 'width:250px;background-color:#f5f5f5;padding:5px;'>Entrer un moratoire : </label>
		<input type = 'text' name = 'moratoire' value = "<?php echo isset($_POST['moratoire'])?$_POST['moratoire']:"" ?>" /></p></div><?php 
    }
	$classe->closeCursor();
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());	
	}
	?>
	</div><!-- Fermeture du div cadre -->
    <div class="navigation">
    	<input type="button" value="Annuler" onclick="rediriger('nouvelle.php')" />
        <input type="submit" value="Terminer" />
       	<input type="hidden" value="3" name="step" />
        <input type="hidden" value="<?php echo $_POST['matel']; ?>" name="matel" />
        <input type="hidden" value="<?php echo $_POST['classe']; ?>" name="classe" />
        <?php
		/* Transmettre les variables au formulaire */
		if(isset($_POST['chk'])){
			foreach($_POST['chk'] as $val){
				print "<input type=\"hidden\" value='".$val."' name=\"occasionnel[]\" />";
				print "<input type=\"hidden\" value='".$val."' name=\"chk[]\" />";
			}
		}
		if(isset($_POST['reduction'])){
			foreach($_POST['reduction'] as $val)
				print "<input type=\"hidden\" value='".$val."' name=\"reduction[]\" />";
		}
		?>
        <input type="hidden" value="<?php echo $inscription; ?>" name="montantinscription" />
    </div>
</form>
</div>
<?php
}
/********************************************
*
*	validation de l'inscription et unlock des
*	table blocker pendant le traitement
*******************************************/
function step4(){
	try{
		$pdo = Database::connect2db();
		$query = "SELECT * FROM inscription WHERE MATEL = :matel AND PERIODE = :periode AND IDCLASSE = :classe";
		$res = $pdo->prepare($query);
		/* Attacher les parametre */
		$res->bindValue("matel", $_POST['matel'], PDO::PARAM_STR);
		$res->bindValue("periode", $_SESSION['periode'], PDO::PARAM_STR);
		$res->bindValue("classe", $_POST['classe'], PDO::PARAM_STR);
		$res->execute();
		if($res->rowCount()){
			print "<p class=\"infos\">Le matricule : ".$_POST['matel']." est deja inscrit dans la classe ".$_POST['classe']."
			 a la periode ".$_SESSION['periode'].".</p>";
			return;
		}
		/** 
			Check encore le solde, si c'est inferieure au montant a payer
			verifier si un moratoire est set sinon rediriger pour demander un
			moratoire, si moratoire set, valider et inserer dans la table frais_apayer
			une operation qui est egal au montant d'inscription.
			La tresoriere passera ensuite cette operation a la table operation
		*/
		$solde = 0;
		$msg = ""; $montantmor = 0;//variable modifies car passes par addresse
		if(isset($_POST['moratoire'])){
			if(!exists_moratoire($_POST['moratoire'], $_POST['matel'], $msg, $montantmor)){
				print "<p class = 'infos'>".$msg."</p>";
				step3();
				return;
			}
		}
		if(!checkaccount($_POST['matel'], $_POST['montantinscription'], $solde, $montantmor)){
			if($montantmor != 0)
				print "<p class = 'infos'>Montant moratoire : ".$montantmor." Ce qui est insuffisant au payement des frais</p>";
			else
				print "<p class=\"infos\">Votre solde actuelle $solde est insuffisant pour l'op&eacute;ration</p>";
			step3();
			return;
		}
		/**
			Finaliser l'inscription
		*/
		$q = "INSERT INTO inscription(MATEL, IDCLASSE, PERIODE, DATEINSCRIPTION, MORATOIRE) 
		VALUES(:matel, :classe, :periode, :inscrip, :moratoire)";
		$res = $pdo->prepare($q);
		$res->execute(array(
			"matel" => $_POST['matel'],
			"classe" => $_POST['classe'],
			"periode" => $_SESSION['periode'],
			"inscrip" => date("Y-m-d", time()),
			"moratoire" => isset($_POST['moratoire']) ? $_POST['moratoire'] : ""
		));
		$idinscription = $pdo->lastInsertId();
		/* Enregistrement des frais d'inscription pour le solde 
			Parametre de l'operation
		*/
		$res = $pdo->prepare("UPDATE moratoire SET MONTANTUTILISE = :mnt WHERE IDMORATOIRE = :moratoire");
		$res->execute(array(
			"mnt" => $_POST['montantinscription'],
			"moratoire" => isset($_POST['moratoire']) ? $_POST['moratoire'] : ""
		));
		/* Sauvegarder les frais obligatoires ou occasionnel a payer */
		$query = "INSERT INTO frais_apayer(MATEL, IDFRAIS, STATUT, DATEOP, IDINSCRIPTION) 
		VALUES(:matel, :idfrais, :statut, :dateop, :inscr)";
		
		$res = $pdo->prepare($query);
		$res->bindValue("matel", $_POST['matel'], PDO::PARAM_STR);
		$res->bindValue("dateop", date("Y-m-d", time()), PDO::PARAM_STR);//date actuelle
		$res->bindValue("statut", 0, PDO::PARAM_INT);//0 = non paye
		$res->bindValue("inscr", $idinscription, PDO::PARAM_INT);
		
		$obli = $pdo->prepare("SELECT ID FROM classe_frais 
		WHERE IDCLASSE = :classe AND PERIODE = :periode AND TYPE = :type");
		//Type 0 = frais obligatoires
		$obli->execute(array(
			"periode" => $_SESSION['periode'],
			"classe" => $_POST['classe'],
			'type' => 0
		));
		while($row = $obli->fetch(PDO::FETCH_ASSOC)){
			$res->bindValue("idfrais", $row['ID'], PDO::PARAM_INT);
			$res->execute();
		}
		/* sauvegarder les occasionels */
		if(isset($_POST['occasionnel'])){
			foreach($_POST['occasionnel'] as $val){
				$res->bindValue("idfrais", $val, PDO::PARAM_INT);
				$res->execute();
			}
		}
		/* sauvegarder les reductions */
		$query = "INSERT INTO reduction_obtenue (MATEL, IDREDUCTION, STATUT, DATEOP, IDINSCRIPTION) 
		VALUES(:matel, :idreduction, :statut, :dateop, :inscr)";
		if(isset($_POST['reduction']) && is_array($_POST['reduction'])){
			$red = $pdo->prepare($query);
			$red->bindValue('matel', $_POST['matel'], PDO::PARAM_STR);
			$red->bindValue('statut', 0, PDO::PARAM_INT);
			$red->bindValue('dateop', date('Y-m-d', time()), PDO::PARAM_STR);
			$red->bindValue('inscr', $idinscription, PDO::PARAM_STR);
			foreach($_POST['reduction'] as $val){
				$red->bindValue("idreduction", $val, PDO::PARAM_INT);
				$red->execute();
			}
			$red->closeCursor();
		}
		/* Mettre l'operation en cours de validation par comptabilite  et blocker ainsi 
		l'eleve de tout autre operation comptable
		*/
		$attente = $pdo->prepare("INSERT INTO operation_attente(CONCERNER, PERIODE) VALUES(:matel, :periode)");
		$attente->execute(array(
			"matel" => $_POST['matel'],
			"periode" => $_SESSION['periode']
		));
		$attente->closeCursor();
		$res->closeCursor();
		$obli->closeCursor();
		/* Rediriger vers la liste des eleves de cette classe et constater que le nouveau eleve y figure */
		@header("location:../classes/inscrit.php?classe=".$_POST['classe']);
	}catch(PDOException $e){ 
		var_dump($e->getTrace());
		die($e->getMessage()." ".$e->getLine()." ".$e->getFile());
	}
}
/********************************************************
*
*	Afficher les infos de l'eleve et du compte
* 	entete de chaque page
*
*********************************************************/
function afficherEntete(){
	try{
		$pdo = Database::connect2db();
		$query = "SELECT * FROM inscription WHERE MATEL = :matel AND PERIODE = :periode AND IDCLASSE = :classe";
		$res = $pdo->prepare($query);
		/* Attacher les parametre */
		$res->bindValue("matel", $_POST['matel'], PDO::PARAM_STR);
		$res->bindValue("periode", $_SESSION['periode'], PDO::PARAM_STR);
		$res->bindValue("classe", $_POST['classe'], PDO::PARAM_STR);
		$res->execute();
		if($res->rowCount()){
			print "<p class=\"infos\">Le matricule : ".$_POST['matel']." est deja inscrit dans la classe ".$_POST['classe']."
			 a la periode ".$_SESSION['periode'].".</p>";
			 step1();
			return false;
		}
		$GLOBALS["classe"] = $_POST['classe']; 
		$res = $pdo->prepare("SELECT e.*, c.* FROM eleve e 
		INNER JOIN compte c ON (c.CORRESPONDANT = e.MATEL) 
		WHERE MATEL = :eleve");
		$res->bindValue("eleve", $_POST['matel'], PDO::PARAM_STR);
		$res->execute();
		if(!$res->rowCount()){
			print "<p class = 'infos'>L'El&egrave;ve ".$_POST['matel']." ne poss&egrave;de pas de compte <br/>Veuillez lui associer un compte 
			<a href = '../comptes/ajouter.php' >ici</a></p>";
			die();
		}
		$row = $res->fetch(PDO::FETCH_BOTH);
		/* Afficher les infos de l'eleve */
	?>
    <table cellpadding="1" cellspacing="0" ><tr>
    	<td width="15%">
            <?php echo getImage($row['IMAGE'], "", "150", "100"); ?>
        </td>
        <td width="45%" valign="top">
            <label style="font-weight:bold">Matricule : </label><?php echo $row['MATEL']; ?><br/>
            <label style="font-weight:bold">Nom et Pr&eacute;nom : </label><?php echo $row['NOMEL']." ".$row['PRENOM']; ?><br/>
            <label style="font-weight:bold">Date ajout Syt&egrave;me : </label><?php echo $row['DATEAJOUT']; ?><br/>
            <label style="font-weight:bold">Date Naiss. : </label><?php echo $row['DATENAISS']; ?><br/>
            <label style="font-weight:bold">T&eacute;l&eacute;phone : </label><?php echo $row['TEL']; ?><br/>
            <label style="font-weight:bold">Sexe : </label><?php echo $row['SEXE']; ?><br/>
            <?php 
            if(isset($row['ANCETBS']) and !empty($row['ANCETBS'])){
                try{
                    $res2 = $pdo->prepare("SELECT * FROM ancien_etablissement WHERE IDETS = :id");
                    $res2->bindValue("id", $row['ANCETBS'], PDO::PARAM_INT);
                    $res2->execute();
                    $row2 = $res2->fetch(PDO::FETCH_ASSOC);
                    print "<label style=\"font-weight:bold\">Ancient Etbs : </label>".$row2['LIBELLE']."<br/>";
                    $res2->closeCursor();
                }catch(PDOException $e){
                    die($e->getMessage()." ".__LINE__." ".__FILE__);	
                }
            }?>
        </td>
        <td width="40%" valign="top">
        <!-- Info du compte et etat de solde -->
        	<?php
				/* Selectionner la derniere operation effectuer sur le compte */
				$op = $pdo->prepare("SELECT *, SUM(ACTION) AS FRAIS FROM operation 
				WHERE IDCOMPTE = :compte AND PERIODE = :periode  ORDER BY DATE DESC LIMIT 0, 1");
				$op->execute(array("compte" => $row['IDCOMPTE'], "periode" => $_SESSION['periode']));
				$oprow = $op->fetch(PDO::FETCH_BOTH);
				$GLOBALS["compte"] = $row['IDCOMPTE'];
				print "<label style=\"font-weight:bold;\">Compte Caisse Associ&eacute; : </label>".$row['IDCOMPTE']."<br/>";
				print "<label style=\"font-weight:bold;\">Date cr&eacute;ation : </label>".$row['DATECREATION']."<br/>";
				if(!$op->rowCount()){
					print "<label style=\"font-weight:bold;\">Derni&egrave;re Op&eacute;ration : </label>".$oprow['LIBELLE']."<br/>";
					print "<label style=\"font-weight:bold;\">Montant de l'op&eacute;ration : </label>".format($oprow['ACTION'], 1)."<br/>";
					print "<label style=\"font-weight:bold;\">Effectu&eacute; par : </label>".$oprow['AUTEUR']."<br/>";
				}else
					print format('Aucune op&eacute;ration comptable effectu&eacute;e &agrave; cette p&eacute;riode', 2); 	
				$op->closeCursor();
			?>
        </td>
    </tr></table><hr/>
<?php 
	return true;
	}catch(PDOException $e){
		die("Error AfficherEntete ".$e->getMessage().__FILE__.__LINE__);
	}
}
/***********************************************
*
*	Integration du modele de bas de page
*
***********************************************/
require_once("../includes/footer_inc.php");
?>