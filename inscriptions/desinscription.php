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
	$codepage = "DESINSCRIPTION_ELEVE";
/********************************************************************************************************/
	require_once("../includes/header_inc.php");
	if(isset($_POST['classe'])){
		if($_POST['step'] == 1)
			checkeleve();
		else
			desinscription();
	}else
		selectionner();
/*******************************************************************
*
*	Function propre a la page
*
*********************************************************************/
function selectionner(){?>
<script>
	function selectionner(){
		var frm = document.forms['frm'];
		if(frm.classe.value == "")
			alert("Veuillez choisir une classe");
		else
			frm.submit();
	}
</script>
<div id="zonetravail"><div class="titre">DESINSCRIPTION DES ELEVES.</div>
	<form name="frm" action="desinscription.php" enctype="multipart/form-data" onSubmit="selectionner(); return false;" method="POST">
    	<div class="cadre">
        	<fieldset><legend>S&eacute;lection de la classe.</legend>
            	<table>
                	<tr><td>S&eacute;lectioner une classe.</td><td>
                    <?php $combo = new Combo("SELECT IDCLASSE, LIBELLE FROM classe ORDER BY LIBELLE", "classe", 0, 1); 
							$combo->first = "-Choisir une classe-";
							$combo->view();
					?></td><td><input type="hidden" value="1" name="step"></td></tr>
                </table>
            </fieldset>
        </div>
        <div class="navigation">
        	<input type="button" value="Annuler" onClick="document.location = '../accueil/index.php'"/>
        	<input type="submit" value="Valider"/>
        </div>
    </form>
</div>
<?php }
function checkeleve(){
	try{
		$pdo = Database::connect2db();
		$res = $pdo->prepare("SELECT * FROM classe WHERE IDCLASSE = :classe");
		$res->bindValue("classe", $_POST['classe'], PDO::PARAM_STR);
		$res->execute();
		$row = $res->fetch(PDO::FETCH_BOTH);
	?>
    <script>
		function checkeleve(){
			var obj = document.getElementsByName("chk[]");
			var trouver = false;
			var i = 0;
			while(i < obj.length && !trouver){
				if(obj.item(i).checked == true)
					trouver = true;
				i++;
			}
			if(!trouver)
				alert(decodeURIComponent("Cocher au moins un élément"));
			else{
				if(window.confirm(decodeURIComponent("Attention \n Vous êtes sur le point de désinscrire le(s) élt(s) cochés!!!"))){
					document.forms['frm'].submit();
				}
			}
		}
	</script>
	<div id="zonetravail"><div class="titre">DESINSCRIPTION DES ELEVES : <?php echo $row['LIBELLE']; ?></div>
		<form action="desinscription.php" enctype="multipart/form-data" method="post" name="frm" onsubmit="checkeleve();return false;">
			<div class="cadre">
				<fieldset><legend>Cocher les &eacute;l&egrave;ves &agrave; d&eacute;sinscrire de cette classe</legend>
				<?php
					$query = "SELECT i.IDINSCRIPTION, i.DATEINSCRIPTION, e.MATEL, CONCAT(e.NOMEL,' ', e.PRENOM) AS NOM, e.TEL 
					FROM inscription i 
					LEFT JOIN eleve e ON (e.MATEL = i.MATEL) 
					WHERE i.IDCLASSE = :classe AND i.PERIODE = :periode";
					$res = $pdo->prepare($query);
					$res->execute(array(
						"periode" => $_SESSION['periode'],
						"classe" => $_POST['classe'],
					));
					if(!$res->rowCount()){
						print "<p class = 'infos'>Aucun &eacute;l&egrave;ve inscrit &agrave; cette classe: $_POST[classe]</p>";
						return;
					}
					$grid = new grid($query);
					$grid->addcolonne(0, "IDINSCRIPTION", "IDINSCRIPTION", false);
					$grid->addcolonne(1, "MATRICULE", "MATEL", TRUE);
					$grid->addcolonne(2, "NOM & PRENOM", "NOM", TRUE);
					$grid->addcolonne(3, "TELEPHONE", "TEL", TRUE);
					$grid->addcolonne(4, "DATE INSCRIPTION", "DATEINSCRIPTION", true);
					$grid->param = array("periode" => $_SESSION['periode'], "classe" => $_POST['classe']);
					$grid->selectbutton = true;
					$grid->setColDate(4);
					$grid->display();
				?>
				</fieldset>
			</div>
			<div class="navigation">
            	<input type="button" value="Annuler" onClick="document.location = 'desincription.php'"/>
				<input type="submit" value="D&eacute;sinscrire" />
                <input type="hidden" value="<?php echo $_POST['classe']; ?>" name="classe"/>
                <input type="hidden" value="2" name="step" />
           </div>
		</form>
	</div>
	<?php 
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." ".__LINE__." ".__FILE__);
	}
}
function desinscription(){
	try{
		$i = 0;
		$pdo = Database::connect2db();
		/* Si un moratoire a ete utilise, remettre le montantutilise au nombre ancien moins montantinscription */
		$query = "SELECT * FROM inscription WHERE IDINSCRIPTION = :inscr";
		$res = $pdo->prepare($query);
		$attente = $pdo->prepare("DELETE FROM operation_attente WHERE CONCERNER = :concerner AND PERIODE = :periode");
		$attente->bindValue('periode', $_SESSION['periode'], PDO::PARAM_STR);
		foreach($_POST['chk'] as $val){
			$res->execute(array(
				'inscr' => $val
			));
			$row = $res->fetch(PDO::FETCH_ASSOC);//Stock infos sur l'inscription
			$attente->bindValue('concerner', $row['MATEL'], PDO::PARAM_STR);
			$attente->execute();
			if(isset($row['MORATOIRE']) && !empty($row['MORATOIRE'])){
				$query = "UPDATE moratoire m SET 
				m.MONTANTUTILISE = (SELECT (m.MONTANTUTILISE - p.MONTANTINSCRIPTION) FROM classe_parametre p 
									WHERE p.IDCLASSE = :classe AND p.PERIODE = :periode) 
				WHERE IDMORATOIRE = :idmor";
				$mor = $pdo->prepare($query);
				$mor->execute(array(
					'classe' => $row['IDCLASSE'],
					'periode' => $_SESSION['periode'],
					'idmor' => $row['MORATOIRE']
				));
				$mor->closeCursor();
			}
		}
		$query = "DELETE FROM inscription WHERE IDINSCRIPTION = :inscr";
		$res = $pdo->prepare($query);
		/* Suppression proprement dite */
		foreach($_POST['chk'] as $val){
			$i++;
			$res->bindValue('inscr', $val, PDO::PARAM_STR);
			$res->execute();
		}
		if($i == 0)
			$_SESSION['infos'] = 'Aucun &eacute;l&egrave;ve desincrit de la classe';
		else
			$_SESSION['infos'] = $i." &eacute;l&egrave;ve(s) d&eacute;sinscrit(s) de la classe ".parse($_POST['classe']);
		/* Rediriger */
		@header("Location:../classes/inscrit.php?classe=".$_POST['classe']);
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
	}
}
/*************************************************************
*
*	Integration du mode le de base de page
*
**************************************************************/
require_once("../includes/footer_inc.php");
?>