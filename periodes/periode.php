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
	$codepage = "SHOW_PERIODE";
	if(isset($_GET['action'])){
		if(!strcmp($_GET['action'], "edit"))
			$codepage = "EDIT_PERIODE";
		elseif(!strcmp($_GET['action'], "delete") || !strcmp($_GET['action'], "deleteall"))
			$codepage = "DEL_PERIODE";
	}
/********************************************************************************************************/
	$titre = "Gestion des p&eacute;riodes";
	require_once("../includes/header_inc.php");
	if(isset($_GET['action'])){
		switch($_GET['action']){
			case "edit":modifier();break;
			case "delete":delete();break;
			case "deleteall":deleteall();break;
		}
	}else
		afficher();
	require_once("../includes/footer_inc.php");
function afficher(){?>
<script language="javascript">
	function voirdetails(){
		alert(this.firstChild.value);
	}
</script>
<div id="zonetravail"><div class = 'titre'>GESTION DES PERIODES.</div>
<form action="periode.php" name="frmgrid" method="POST" enctype="multipart/form-data">
	<div class = 'cadre'><fieldset><legend>Liste des p&eacute;riodes.</legend>
    <?php
	$query = "SELECT ANNEEACADEMIQUE, 
	CONCAT('<a href = \'#\' onclick=\'voirdetails();\' title = \'Afficher les details\'>', ANNEEACADEMIQUE, '</a>'), DATEDEBUT, DATEFIN, d.LIBELLE  
	FROM annee_academique INNER JOIN decoupage d 
	ON annee_academique.DECOUPAGE = d.ID
	ORDER BY ANNEEACADEMIQUE";
	$grid = new Grid($query);
	$grid->id = 0;
	$grid->addcolonne(0, "ANNNEE ACADEMIQUE", '0', FALSE);
	$grid->addcolonne(1, "ANNNEE ACADEMIQUE", '1', TRUE);
	$grid->addcolonne(2, "DEBUT", '2', TRUE);
	$grid->addcolonne(3, "FIN", '3', TRUE);
	$grid->addcolonne(4, "DECOUPAGE", '4', TRUE);
	$grid->formatdate = "long";
	$grid->setColDate(2); //DATE DEBUT
	$grid->setColDate(3); //DATE FIN
	/*
		Verifier les droit d'acces de modification et de suppression
		avant d'afficher les buttons de modification et de suppression
	*/
	if(is_autorized("EDIT_PERIODE")){
		$grid->editbutton = true;
		$grid->editbuttontext = "Modifier cette p&eacute;riode";
	}
	$grid->selectbutton = true;
	if(is_autorized("DEL_PERIODE")){
		$grid->deletebutton = true;
		$grid->deletebuttontext = "Supprimer cette p&eacute;riode";
	}
	$grid->display();?>
	</fieldset></div>
	<div class = 'navigation'>
    <?php
	if(is_autorized("DEL_PERIODE"))
		print "<input type = 'button' value = 'Supprimer' onclick = \"deletecheck();\" />";
	if(is_autorized("ADD_PERIODE"))
		print "<input type = 'button' onclick = \"rediriger('creer.php');\" value = 'Ajouter'/>";?>
    </div>
</form></div>
<?php
}
function modifier(){
	@header("Location:modifier.php?id=".$_GET['line']);
}
function delete(){
	try{
		$pdo = Database::connect2db();
		if(isset($_GET['true'])){
			$res = $pdo->prepare("DELETE FROM annee_academique WHERE ANNEEACADEMIQUE = :id");
			$res->execute(array(
				'id' => $_GET['line']
			));
			if($res->rowCount())
				print "<p class=\"infos\">Ann&eacute;e acad&eacute;mique : ".$_GET['line']." supprim&eacute;e avec succ&egrave;s .</p>";
			else
				print "<p class='infos'>Erreur de suppression de ".$_GET['line']."</p>";
			afficher();
		}else{?>
			<div id="zonetravail">
				<p style = 'color:red; font-size:12px;text-align:center;'>Etes-vous s&ucirc;r de vouloir supprimer cette p&eacute;riode ?<br/>
				<br/>Cela entra&icirc;nera des suppressions en cascade : Des trimestres, des s&eacute;quences, des notes, 
				des moratoires, des inscriptions et autres activit&eacute;s li&eacute;s &agrave; cette p&eacute;riode.<br/>";
				Il est plut&ocirc;t conseill&eacute; d'effectuer une modification de p&eacute;riode.</p>
				<div class="navigation">
					<input type = 'button' value = 'Annuler' onclick="rediriger('periode.php')" style = 'cursor:pointer;'/>
					<input type = 'button' value = 'Continuer' 
					onclick="rediriger('periode.php?action=delete&true=1&line=<?php echo $_GET['line']; ?>')" style = 'cursor:pointer;' />
				</div>
		<?php }
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
	}
}
function deleteall(){
	try{
		$pdo = Database::connect2db();
		$res = $pdo->prepare("DELETE FROM annee_academique WHERE ANNEEACADEMIQUE = :id");
		if(isset($_GET['true'])){
			$i = 0;
			foreach($_POST['chk'] as $val){
				$res->execute(array(
					'id' => $val
				));
				if($res->rowCount()){
					if($i == 0) 
						print "<p class = 'infos'>";
					print "<p class=\"infos\">Ann&eacute;e acad&eacute;mique : ".$_GET['line']." supprim&eacute;e avec succ&egrave;s .</p>";
					$i++;
				}else
					print "<p class = 'infos'>Erreur de suppression de ".$_GET['line']."</p>";
			}
			if($i != 0)
				print "</p>";
			afficher();
			$res->closeCursor();
		}else{?>
			<div id="zonetravail">
            	<p style = 'color:red; font-size:12px;text-align:center;'>Etes-vous s&ucirc;r de vouloir supprimer cette p&eacute;riode ?<br/>
				<br/>Cela entra&icirc;nera des suppressions en cascade : "
				Des trimestres, des s&eacute;quences, des notes, des moratoires, des inscriptions et autres activit&eacute;s li&eacute;s &agrave; cette p&eacute;riode.<br/>
				Il est plut&ocirc;t conseill&eacute; d'effectuer une modification de p&eacute;riode.</p>
				<form action = 'periode.php?action=deleteall&true=1' enctype="multipart/form-data" method="post"><?php 
					foreach($_POST['chk'] as $val)
						print "<input type=\"hidden\" name=\"chk[]\" value=\"".$val."\"/>";
					?>
					<div class="navigation">
						<input type = 'button' value = 'Annuler' onclick="rediriger('periode.php')" style = 'cursor:pointer;'/>
						&nbsp;&nbsp;&nbsp;&nbsp;<input type = 'submit' value = 'Continuer' style = 'cursor:pointer;' />
					</div>
                </form>
				<script>alert('-------Attention----------!!!');</script>
             </div>
	<?php		
		}
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
	}
}
?>