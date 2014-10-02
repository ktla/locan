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
	$codepage = "REINSCRIPTION_ELEVE";
/********************************************************************************************************/
	$titre = "Nouvelle Inscription";
	require_once("../includes/header_inc.php");
	/* case study */
	if(isset($_POST['step'])){
		if(intval($_POST['step'] == 2))
			step2();
		elseif(intval($_POST['step'] == 3))
			step3();
	}else
		step1();
/**
	Functions propres a la page
*/
function step1(){?>
<script>
	function step1(){
		var frm = document.forms['frm'];
		if(frm.matel.value == "" || frm.classe.value == "")
			alert("Choisir un eleve et une classe");
		else
			frm.submit();
	}
</script>
<div id="zonetravail"><div class="titre">REINSCRIPTION D'UN ELEVE.</div>
<form action="reinscription.php" enctype="multipart/form-data" onSubmit="step1(); return false;" name = 'frm' method="POST"/>
	<div class="cadre">
    	<fieldset><legend>Renseignement sur la r&eacute;inscription.</legend>
            <table><tr>
            <td class="lib">Matricule : </td>
            <td><?php $q = "SELECT DISTINCT(e.MATEL), CONCAT(e.NOMEL, '-', e.PRENOM) AS NOM 
				FROM eleve e 
				INNER JOIN inscription i ON (e.MATEL = i.MATEL AND i.PERIODE != :periode) 
				ORDER BY MATEL";
				$combo = new Combo($q, "matel", 0, 1, (isset($_POST['matel']) ? true : false));
				$combo->first = "-Choisir un &eacute;l&egrave;ve-";
				$combo->param = array('periode' => $_SESSION['periode']);
				$combo->selectedid = isset($_POST['matel']) ? $_POST['matel'] : "";
				$combo->view('300px');
			?></td>
            <td class="lib">Classe : </td>
            <td><?php $q = "SELECT IDCLASSE, LIBELLE FROM classe";
				$combo = new Combo($q, "classe", 0, 1, (isset($_POST['classe']) ? true : false));
				$combo->first = "-Choisir une classe-";
				$combo->selectedid = isset($_POST['classe']) ? $_POST['classe'] : "";
				$combo->view('200px');
			?></td>
            </tr></table>
       </fieldset>
    </div>
   	<div class="navigation">
    	<input type="button" onClick="home();" value="Annuler" />
        <input type="hidden" name="step" value="2"  />
    	<input type="submit" value="Suivant" />
     </div>
</form>
</div>
<?php }
/****************************
*
*	Resume des informations
*
*****************************/
function step2(){?>
<div id="zonetravail"><div class="titre">R&eacute;inscription</div>
<form action="reinscription.php" method="post" enctype="multipart/form-data" name="frm" >
	<div class="cadre"><?php if(!afficherEntete()) return;
		/** verifier le compte */
	try{
		$pdo = Database::connect2db();
		$res = $pdo->prepare("SELECT c.*,  p.* 
		FROM classe c 
		LEFT JOIN classe_parametre p ON (p.IDCLASSE = c.IDCLASSE) 
		WHERE c.IDCLASSE = :classe 
		ORDER BY p.ID DESC 
		LIMIT 0, 1");
		$res->bindValue('classe', $_POST['classe'], PDO::PARAM_STR);
		$res->execute();
		if(!$res->rowCount()){
			print "<p class = 'infos'>Impossible d'obtenir des informations relatives &agrave; la classe ".$_POST['classe']."</p>";
			step1();
			return;
		}
		$classe = $res->fetch(PDO::FETCH_ASSOC);
		/* Obtenir la derniere inscription sur laquelle on va se baser */
		$res2 = $pdo->prepare("SELECT IDINSCRIPTION FROM inscription WHERE MATEL = :matel ORDER BY IDINSCRIPTION DESC LIMIT 0, 1");
		$res2->bindValue('matel', $_POST['matel'], PDO::PARAM_INT);
		$res2->execute();
		$inscr = $res2->fetch(PDO::FETCH_ASSOC);
		print $inscr['IDINSCRIPTION'];
		/* Obtenir les frais obligatoire et occasionnel d'avant */
		$res = $pdo->prepare("SELECT IDFRAIS FROM frais_apayer WHERE IDINSCRIPTION = :inscr");
		$res->bindValue('inscr', $inscr['IDINSCRIPTION'], PDO::PARAM_INT);
		$res->execute();
		$sumobligatoire = 0; $sumoccasionnel = 0;
		while($row = $res->fetch(PDO::FETCH_ASSOC)){
			$frais = new Frais($row['IDFRAIS']);
			if(intval($frais->type) == 0)
				$sumobligatoire += $frais->montant;
			elseif(intval($frais->type) == 1)
				$sumoccasionnel += $frais->montant;
		}
		/* Obtenir les reduction d'avant */
		$res = $pdo->prepare("SELECT IDREDUCTION FROM reduction_obtenue WHERE IDINSCRIPTION = :inscr");
		$res->bindValue('inscr', $inscr['IDINSCRIPTION'], PDO::PARAM_INT);
		$res->execute();
		$sumreduction = 0;
		while($row = $res->fetch(PDO::FETCH_ASSOC)){
			$reduc = new Reduction($row['IDREDUCTION']);
			if(!strcmp($reduc->type, 'pourcentage'))
				$sumreduction = $sumreduction + ($reduc->montant * $reduc->frais->montant)/100;
			else
				$sumreduction += $reduc->montant;
		}
		/* Afficher les informations */
		$total = intval($sumobligatoire) + intval($sumoccasionnel) - 
		intval($sumreduction) + intval($classe['MONTANTINSCRIPTION']);
		$solde = 0; $moratoire = 0;
		$account = checkaccount($_POST['matel'], $classe['MONTANTINSCRIPTION'], $solde, $moratoire); //Cf commun_lib.php
		?>
        <table width = "85%" border = '1' cellpadding="3" cellspacing="1" style="border-collapse:collapse">
			<tr><td width = '70%'>Classe</td><td width = '30%'><?php echo $classe['LIBELLE']; ?></td></tr>
			<tr><td width = '70%'>Montant Inscription</td><td width = '30%' align = 'right'><?php echo format($classe['MONTANTINSCRIPTION']); ?></td></tr>
			<tr><td>Montant Frais obligatoires</td><td align = 'right'><?php echo format($sumobligatoire);?></td></tr>
			<tr><td>Montant Frais occasionnels</td><td align = 'right'><?php echo format($sumoccasionnel);?></td></tr>
			<tr><td>Montant Frais r&eacute;ductions</td><td align = 'right'><?php echo format($sumreduction);?></td></tr>
			<tr><td>Montant Total &agrave; payer</td><td align = 'right'><?php echo format($total);?></td></tr>
			<tr><td><?php echo format("Solde actuelle de l'El&egrave;ve", 2);?></td><td align = 'right'><?php echo format($solde, 1);?></td></tr>
       </table>
     <?php
	/******* MORATOIRE *******************
		 Si le solde est inferieur au montant de l'inscription, suggerer le moratoire 	
	*/?>
        <p style = 'text-align:center;font-weight:bold;margin:0px;'>Votre compte sera d&eacute;bit&eacute; de : 
        <?php echo format($classe['MONTANTINSCRIPTION'], 1); ?></p><?php 
        if(!$account){?>
            <div style = 'border:1px solid #f2f2f2;margin:0px;padding:2px;'>
                <p style = 'text-align:center;margin:2px;'>Votre solde est insuffisant de <?php echo format($classe['MONTANTINSCRIPTION'] - $solde); ?>,
                 veuillez entrer un moratoire<br/><br/>
              <label style = 'width:250px;background-color:#f5f5f5;padding:5px;'>Entrer un moratoire : </label>
            <input type = 'text' name = 'moratoire' value = '' /></p></div><?php 
        }
        $res->closeCursor();
		$res2->closeCursor();
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());	
	}
	?>
    </div>
    <div class="navigation">
    	<input type="button" value="Retour" onclick="reinscription.php"  />
        <input type="hidden" name="step" value="3"  />
        <input type="hidden" value="<?php echo $_POST['matel']; ?>" name="matel"  />
        <input type="hidden" value="<?php echo $_POST['classe']; ?>" name="classe"  />
        <input type="hidden" value="<?php echo $classe['MONTANTINSCRIPTION']; ?>" name="montantinscription"  />
        <input type="hidden" value="<?php echo $inscr['IDINSCRIPTION']; ?>" name="ancienneinscription"  />
        <input type="submit" value="Terminer"  />
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
function step3(){
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
				step2();
				return;
			}
		}
		if(!checkaccount($_POST['matel'], $_POST['montantinscription'], $solde, $montantmor)){
			if($montantmor != 0)
				print "<p class = 'infos'>Montant moratoire : ".$montantmor." Ce qui est insuffisant au payement des frais</p>";
			else
				print "<p class=\"infos\">Votre solde actuelle $solde est insuffisant pour l'op&eacute;ration</p>";
			step2();
			return;
		}
		/**
			Finaliser la reinscription
		*/
		$q = "INSERT INTO inscription(MATEL, IDCLASSE, PERIODE, DATEINSCRIPTION, MORATOIRE) 
		VALUES(:matel, :classe, :periode, :inscrip, :moratoire)";
		$res = $pdo->prepare($q);
		$res->execute(array(
			"matel" => $_POST['matel'],
			"classe" => $_POST['classe'],
			"periode" => $_SESSION['periode'],
			"inscrip" => date("Y-m-d", time()),
			"moratoire" =>isset($_POST['moratoire']) ? $_POST['moratoire'] : ""
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
		$prev = $pdo->prepare("SELECT IDFRAIS FROM frais_apayer WHERE IDINSCRIPTION = :previnscr");
		$prev->execute(array(
			'previnscr' => $_POST['ancienneinscription']
		));
		while($row = $prev->fetch(PDO::FETCH_ASSOC)){
			$res->bindValue('idfrais', $row['IDFRAIS'], PDO::PARAM_STR);
			$res->execute();
		}
		/* sauvegarder les reductions */
		$prev = $pdo->prepare("SELECT IDREDUCTION FROM reduction_obtenue WHERE IDINSCRIPTION = :previnscr");
		$prev->execute(array(
			'previnscr' => $_POST['ancienneinscription']
		));
		if($prev->rowCount()){
			$query = "INSERT INTO reduction_obtenue (MATEL, IDREDUCTION, STATUT, DATEOP, IDINSCRIPTION) 
			VALUES(:matel, :idreduction, :statut, :dateop, :inscr)";
			$red = $pdo->prepare($query);
			$red->bindValue('matel', $_POST['matel'], PDO::PARAM_STR);
			$red->bindValue('statut', 0, PDO::PARAM_INT);//0 = non payer, 1 = payer
			$red->bindValue('dateop', date('Y-m-d', time()), PDO::PARAM_STR);
			$red->bindValue('inscr', $idinscription, PDO::PARAM_STR);
			while($row = $prev->fetch(PDO::FETCH_ASSOC)){
				$red->bindValue("idreduction", $row['IDREDUCTION'], PDO::PARAM_INT);
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
		$prev->closeCursor();
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
		return false;
	}
}
/***************************************
*
*	Integration du modele de bas de page
*
****************************************/
require_once("../includes/footer_inc.php");
?>