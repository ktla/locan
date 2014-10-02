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
$codepage = "SHOW_ELEVE";
if(isset($_GET['action'])){
	if(!strcmp($_GET['action'], "edit"))
		$codepage = "EDIT_ELEVE";
	elseif(!strcmp($_GET['action'], "delete") || !strcmp($_GET['action'], "deleteall"))
		$codepage = "DEL_ELEVE";
}
/********************************************************************************************************/
	$titre = "Gestion des &eacute;l&egrave;ves";
	require_once("../includes/header_inc.php");
/*
	Gestion des actions
*/
	if(isset($_GET['action'])){
		switch($_GET['action']){
			case "edit":edit();break;
			case "delete":delete($_GET['line']);break;
			case "deleteall":deleteall();break;
			case "rechercher":rechercher();break;
		}
	}else
		afficher();
	require_once("../includes/footer_inc.php");
function afficher($msg = ""){?>
	<div id="zonetravail"><div class="titre">Liste de tous les &eacute;l&egrave;ves</div>
        <form action="imprimer.php" target="_blank" name="frprint" method="POST" enctype="multipart/form-data" style = 'border:1px solid #f2f2f2;margin:3px;padding:2px;'>
		<?php $pdo = Database::connect2db(); /* Afficher les nom des colonnes */
			$res = $pdo->prepare("SELECT * FROM eleve LIMIT 0, 1");
			$res->execute();
			for($i = 2; $i < $res->columnCount(); $i++){
				$arr = $res->getColumnMeta($i);
				if(strcmp($arr['name'], "IMAGE"))
					print "<span><input type = 'checkbox' name = 'colonne[]' value='".$arr["name"]."'/>".ucfirst(strtolower($arr['name']))."&nbsp;&nbsp;|&nbsp;&nbsp;</span>";
			}
			?>
            <img src="../images/imprimer.png" style="cursor:pointer" onclick="document.forms['frprint'].submit();" />
        </form>
		<form name="frmgrid" enctype="multipart/form-data" method="POST" action="eleve.php">
        <div class="cadre">
            <div style="margin:5px 0 5px 0;">
                <input type = 'text' style="width:60%;" id = 'rech' name = 'rech' />
                <input type = 'button' value = 'Rechercher' onclick = "rechercher('eleve');"/>
             </div>
             <?php
                $query = "SELECT e.ID, CONCAT(e.NOMEL,' ', e.PRENOM) AS NOM, e.TEL,e.MATRICULE,
                CONCAT(\"<a href = 'fiche.php?matel=\",e.ID, \"'>\", 'Fiche', \"</a>\") AS FICHE 
                FROM eleve e 
				ORDER BY e.NOMEL ASC";
                $grid = new grid($query);
                $grid->addcolonne(3, 'MATRICULE', 'MATRICULE', TRUE);
                $grid->addcolonne(1, 'ELEVE', 'NOM', TRUE);
                $grid->addcolonne(2, 'TELEPHONE', 'TEL', TRUE);
                $grid->addcolonne(4, 'FICHE', 'FICHE', TRUE);
                $grid->editbutton = true;
                $grid->editbuttontext = "Editer";
                $grid->deletebutton = true;
                $grid->deletebuttontext = "Supprimer";
                $grid->selectbutton = true;
                $grid->display();
            ?>
		</div>
          <div align="center" style="text-transform:capitalize;color:#F00;text-decoration:overline"><span><?php echo !empty($msg)?$msg:""; ?></span></div>
		<div class = 'navigation'>
    		<input type = 'button' onclick = "rediriger('ajouter.php');" value = 'Ajouter'>
			<input type = 'button' onclick = "deletecheck();" value = 'Supprimer' />
     	</div>
  		</form>
      
  	</div>
<?php
}
function edit(){
	/*print "<script>rediriger(\"modifier.php?id=".$_GET['line']."\");</script>";*/
	header("location:./modifier.php?id=".$_GET["line"]);
}
function delete($id, $affiche_result = true){
	/* Suppression des fichiers associes a eleve */
try{
	$pdo = Database::connect2db();
	$query = "SELECT * FROM eleve WHERE ID = ".$id;
	$res = $pdo->query($query);
	$row = $res->fetch(PDO::FETCH_ASSOC);
	if(!empty($row['IMAGE']) && file_exists("./photos/".$row['IMAGE']))
		unlink("./photos/".$row['IMAGE']);
	/* Suppression de la ligne dans les classes liees */
	
	
	/* Suppression dans la table abscence */
	$query = "DELETE FROM absence WHERE MATEL = ".$id;
	$pdo->exec($query);
	/* Suppression dans la table frais_apayer */
	$query = "DELETE FROM frais_apayer WHERE MATEL = ".$id;
	$pdo->exec($query);
	/* Suppression dans la table reduction_obtenue */
	$query = "DELETE FROM reduction_obtenue WHERE MATEL = ".$id;
	$pdo->exec($query);
	/* Suppression dans la table inscription */
	$query = "DELETE FROM inscription WHERE MATEL = ".$id;
	$pdo->exec($query);
	/* Suppression dans la table moratoire */
	$query = "DELETE FROM moratoire WHERE MATEL = ".$id;
	$pdo->exec($query);
	/* Suppression dans la table solde */
	$query = "DELETE FROM solde WHERE MATEL = ".$id;
	$pdo->exec($query);
	/* Suppression dans la table note */
	$query = "DELETE FROM note WHERE MATEL = ".$id;
	$pdo->exec($query);
	
	//=====================================
	$query = "SELECT * FROM compte WHERE CORRESPONDANT = '".$row["MATRICULE"]."'";
	$result = $pdo->query($query);
	if($result->rowCount() > 0){
		$ligne = $result->fetch(PDO::FETCH_ASSOC);
	
		/* Suppression dans la table operation */
		$query = "DELETE FROM operation WHERE IDCOMPTE = ".$ligne["ID"];
		$pdo->exec($query);
	
	/* Suppression dans la table compte */
	$query = "DELETE FROM compte WHERE IDCOMPTE = ".$ligne["ID"];
	$pdo->exec($query);
	}
	/* Suppression dans la table eleve */
	$query = "DELETE FROM eleve WHERE ID = ".$id;
	if($pdo->exec($query))
		if($affiche_result)
			afficher("El&egrave;ve supprim&eacute; avec succ&egrave;s");
	
}catch(PDOException $e){
		die($e->getMessage()." ".$e->getLine()." ".__LINE__." ".__FILE__);
}
}
function deleteall(){	
		if(isset($_POST['chk']))
		foreach($_POST['chk'] as $val){
			delete($val,false);
		}
		afficher("El&egrave;ves supprim&eacute;s avec succ&egrave;s");
}
function rechercher(){
	print "<div id=\"zonetravail\"><div class=\"titre\">LISTE DES ELEVES.</div>";
	print "<div class=\"cadre\">";
	print "<div style=\"margin:5px 0 5px 0;\"><input type = 'text' size = '70' id = 'rech' name = 'rech' />";
	print "<input accesskey=\"enter\" type = 'button' value = 'Rechercher' onclick = \"rechercher('eleve');\"/></div>";
	print "<fieldset><legend>Liste des &eacute;l&egrave;ves.</legend>";
	$query = "SELECT ID, NOMEL, TEL, ADRESSE, DATE,MATRICULE
	 FROM eleve e 
	 WHERE ID LIKE '%".parse($_GET['val'])."%' OR NOMEL LIKE '%".parse($_GET['val'])."%' OR TEL LIKE '%".parse($_GET['val'])."%' OR DATE LIKE '%".parseDate($_GET['val'])."%'";
	$grid = new grid($query);
	$grid->addcolonne(0, 'MATRICULE.', '5', TRUE);
	$grid->addcolonne(1, 'NOM ELEVE', '1', TRUE);
	$grid->addcolonne(2, 'TELEPHONE', '2', TRUE);
	$grid->addcolonne(3, "ADRESSE", '3', false);
	$grid->addcolonne(4, 'DATE D\'AJOUT', '4', TRUE);
	$grid->setColDate(4);
	$grid->editbutton = true;
	$grid->editbuttontext = "Editer";
	$grid->deletebutton = true;
	$grid->deletebuttontext = "Supprimer";
	$grid->selectbutton = true;
	$grid->display();
	print "</fieldset></div>";
	print "<div class = 'navigation'><input type = 'button' onclick = \"deletecheck();\" value = 'Supprimer' /></div></div>";
}
?>