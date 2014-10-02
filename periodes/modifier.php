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
	$codepage = "EDIT_PERIODE";
/******************************************************************************************************/
	$titre = "Modification des p&eacute;riodes";
	require_once("../includes/header_inc.php");
	if(isset($_POST['step'])){
		if($_POST['step'] == 1)
			add_decoupage();
		elseif($_POST['step'] == 2)
			trimestre();
	}else{
			if(isset($_GET["action"]) && isset($_GET["line"]) && !strcmp($_GET["action"],"edit") && !isset($_GET["periode"]))
				edit_trimestre();
			elseif(isset($_GET["action"]) && isset($_GET["line"]) && !strcmp($_GET["action"],"delete") && !isset($_GET["periode"]))
				delete_trimestre();
			elseif(isset($_GET["action"]) && isset($_GET["line"]) && !strcmp($_GET["action"],"delete") && isset($_GET["periode"]))
				delete_sequence();
			else
				proposer();
	}
/**
	Function propre a la page
*/
function proposer(){
	if(!id_exist($_GET['id'], "ANNEEACADEMIQUE", "annee_academique")){
		$_SESSION['infos'] = "Aucune p&eacute;riode existente sous ce nom : ".parse($_GET['id']);
	    @header("Location:periode.php");
		return;
	}
	$pdo = Database::connect2db();
	$res = $pdo->prepare("SELECT * FROM annee_academique WHERE ANNEEACADEMIQUE = :id");
	$res->execute(array('id' => $_GET['id']));
	$row = $res->fetch(PDO::FETCH_ASSOC);
?>
<script>
	function proposer(){
		var obj = document.forms['frm'];
		if(obj.datedebut.value == "" || obj.datefin.value == "" || obj.periode.value == "" || obj.decoupage.value == "")
			alert("-Tous les champs * sont obligatoires-");
		else
			obj.submit();
	}
	
	function verification(){
		document.getElementById("change").value = 1;	
	}
</script>
	<div id="zonetravail"><div class="titre">MODIFICATION PERIODE : <?php echo $_GET['id']; ?></div>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="proposer(); return false;" name="frm" method="POST" enctype="multipart/form-data">
	<div class="cadre">
    	<fieldset><legend>Renseignements sur la p&eacute;riode.</legend>
        	<table cellspacing="5"><tr>
            	<td class="lib">P&eacute;riode : <font style="font-size:9px; color:#777;">(Ex:2010-2011)</font>
                	<span class="asterisque">*</span>
                </td>
                <td><input value="<?php echo $row['ANNEEACADEMIQUE']; ?>" type="text" maxlength="13" name="periode"/></td>
                <td class="lib">D&eacute;coupage : <span class="asterisque">*</span></td>
                <td>
                	<?php 
						$query = "SELECT * FROM decoupage ORDER BY ID";
						$combo = new Combo($query, "decoupage", 0, 1, TRUE,$row["DECOUPAGE"]);
						$combo->param = array('periode' => $_SESSION['periode']);
						$combo->onchange = "verification()";
						$combo->view();
					?>
                </td>
             <tr><td class="lib">Date de d&eacute;but : <span class="asterisque">*</span></td>
             	<td><input value="<?php echo $row['DATEDEBUT']; ?>" type="text" onchange="verification();" name="datedebut" id="datedebut" /></td>
             	<td class="lib">Date de fin :  <span class="asterisque">*</span></td>
                <td><input type="text" name="datefin" onchange="verification();" value="<?php echo $row['DATEFIN']; ?>" id="datefin"/></td>
            </tr></table>
        </fieldset>
    </div>
    <div class="navigation">
    	<input type="button" onClick="rediriger('periode.php');" value="Annuler"/><input type="hidden" value="1" name="step"/>
        <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>"/><input type="submit" value="Suivant"/>
         <input type="hidden" name="change" id="change" value="0"/>
    </div>
	</form>
</div>
<?php }
function add_decoupage($id = 0){
	if($id != 0){
		$_POST["id"]=$id;
		$_POST["periode"]=$id;	
	}
	
	if(isset($_POST["id"])){
		if(!id_exist($_POST['id'], "ANNEEACADEMIQUE", "annee_academique")){
			$_SESSION['infos'] = "Aucune p&eacute;riode existente sous ce nom : ".parse($_POST['id']);
			@header("Location:periode.php");
			return;
		}
	}
	//Verifier s'il n'existe pas deja une periode sous ce nom (nouvelle nom de periode =)
	$periode = str_replace(" ", "", $_POST['periode']);
	if(strcmp($_POST['id'], $_POST['periode'])){
		if(id_exist($periode, "ANNEEACADEMIQUE", "annee_academique")){
			$_SESSION['infos'] = "P&eacute;riode : ".$periode." existent dans la base de donn&eacute;es.<br/>Veuillez changer de p&eacute;riode";
			@header("Location:modifier.php?id=".$_POST['id']);
			return;
		}
	}

	if (isset($_POST["change"])&& $_POST["change"]){ // en cas de modification
		try{
		$con = Database::connect2db();
		$con->exec("UPDATE annee_academique SET DATEDEBUT ='".parseDate($_POST["datedebut"])."',DATEFIN='".parseDate($_POST["datefin"])."',DECOUPAGE=".$_POST["decoupage"]." WHERE ANNEEACADEMIQUE ='".$_POST["id"]."'");
		
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
		}
	}
	
	if(isset($_POST["libelle"]) && isset($_POST["datefin"]) && isset($_POST["datedebut"]) && isset($_POST["ordre"])){
		// verification de la duplcation d' un trimestre uploade
		if(isset($_POST["periode"]) && isset($_POST["libelle"]) && isset($_POST["ordre"])){
		try{
			$pdo = Database::connect2db();
			$res2 = $pdo->prepare("SELECT COUNT(*) AS NB FROM trimestre WHERE ANNEEACADEMIQUE = :id AND (LIBELLE = :libelle OR ORDRE = :ordre)");
			$res2->execute(array('id' => $_POST['periode'],
								 'libelle'=> $_POST["libelle"],
								 'ordre'=> $_POST["ordre"]));
			$row = $res2->fetch(PDO::FETCH_ASSOC);
			if($row["NB"] == 0){
				try{
					$pdo->exec("INSERT INTO trimestre(ANNEEACADEMIQUE,DATEDEBUT,DATEFIN,ORDRE,LIBELLE) VALUES
					('".$_POST["id"]."','".parseDate($_POST["datedebut"])."','".parseDate($_POST["datefin"])."',".$_POST["ordre"].",'".$_POST["libelle"]."')");
				}catch(PDOException $e){
					var_dump($e->getTrace());
					die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());	
				}		
			}
			
		 }catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
		}
	}
				
	}
	
?>
<script type="text/javascript">
	function add_decoupage(){
		if(document.getElementById("init").value == 1){		
			var obj = document.forms['frm'];
			if(obj.datedebut.value == "" || obj.datefin.value == "" || obj.libelle.value == "" || obj.ordre.value == "")
				alert("-Tous les champs * sont obligatoires-");
			else{
				if(!isNaN(obj.ordre.value))
					obj.submit();
				else
					alert("-Le champs * ordre est un nombre-");		
			}
		}else
			alert("-Aucun trimestre a enregistré-");
	}
	function init(){
		document.getElementById("libelle").value = "";	
		document.getElementById("datedebut").value = "";
		document.getElementById("datefin").value = "";
		document.getElementById("ordre").value = "";
	}
	
	function ajout_trimestre(){
		if(document.getElementById("grid").style.display == "none"){
			document.getElementById("grid").style.display = "block";
			document.getElementById("init").value = 1;
		}else{
			 document.getElementById("grid").style.display = "none";
			 document.getElementById("init").value = 0;
			 init();
		}	
	}
</script>
<div id="zonetravail"><div class="titre">CONFIGURATION DES TRIMESTRES DE L' ANNEE COURANTE</div>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" name="frm" onsubmit="add_decoupage(); return false;">
	<div class="cadre">
    	<fieldset><legend>Ajouter un trimestre &agrave; l' annee <?php print $_POST["id"] ?>&nbsp;&nbsp;&nbsp;<img src="../images/add.gif" style="cursor:pointer; margin-top:-3px;" onclick="ajout_trimestre();" title="Ajout trimestre"/></legend>
            
            <table id="grid" cellspacing="5px" style="display:none">
            	<tr>
                	<td class="lib">Libelle :  <span class="asterisque">*</span></td>
                    <td><input value="" type="text" maxlength="50" name="libelle" id="libelle"/><td>
                    <td class="lib">Date Debut : <span class="asterisque">*</span></td>
                	<td><input value="" type="text" name="datedebut" id="datedebut" /></td>
                </tr>
                <tr>
                	<td class="lib">Ordre :  <span class="asterisque">*</span></td>
                    <td><input value="" type="text" maxlength="1" name="ordre" id="ordre"/><td>
                    <td class="lib">Date Fin : <span class="asterisque">*</span></td>
                	<td><input value="" type="text" name="datefin" id="datefin" /></td>
                </tr>
            </table>	
            <input type="hidden" name="init" id="init" value="0"/>
    <?php
			$query = "SELECT t.IDTRIMESTRE,t.LIBELLE,t.DATEDEBUT,t.DATEFIN,t.ORDRE
			FROM trimestre t INNER JOIN annee_academique a ON a.ANNEEACADEMIQUE = t.ANNEEACADEMIQUE 
			WHERE a.ANNEEACADEMIQUE ='".$_POST["id"]."' 
			ORDER BY t.IDTRIMESTRE";
			$grid = new Grid($query);
			$grid->id = 0;
			$grid->addcolonne(1, "LIBELLE", '1', TRUE);
			$grid->addcolonne(2, "DATE DEBUT", '2', TRUE);
			$grid->addcolonne(3, "DATE FIN", '3', TRUE);
			$grid->addcolonne(4, "ORDRE", '4', TRUE);
			$grid->setColDate(1);
			$grid->setColDate(2);
			$grid->formatdate = "long";
			
			/*
				Verifier les droit d'acces de modification et de suppression
				avant d'afficher les buttons de modification et de suppression
			*/
			if(is_autorized("EDIT_PERIODE")){
				$grid->editbutton = true;
				$grid->editbuttontext = "Modifier ce trimestre";
			}
			if(is_autorized("DEL_PERIODE")){
				$grid->deletebutton = true;
				$grid->deletebuttontext = "Supprimer ce trimestre";
			}
			$grid->display();
    ?>
    </div>
    <div class="navigation"><input type="button" onclick="rediriger('modifier.php?id=<?php echo $_POST['id']; ?>');" value="Retour" />
        <input type="hidden" value="1" name="step"/>
        <input type="hidden" value="<?php echo $_POST['id']; ?>" name="id"/>
        <input type="hidden" value="<?php echo $_POST['id']; ?>" name="periode"/>
        <input type="hidden" value="0" name="change"/>
        <input type="submit" value="Valider">
    </div>
</form>
</div>
<?php 
}
/**
	Validation des donnees dans la BD
*/
function edit_trimestre(){
	$pdo = Database::connect2db();
	$res = $pdo->prepare("SELECT * FROM trimestre WHERE IDTRIMESTRE = :id");
	$res->execute(array('id' => $_GET['line']));
	$row = $res->fetch(PDO::FETCH_ASSOC);
	
?>
<script>
	function proposer(){
		var obj = document.forms['frm'];
		if(obj.datedebut.value == "" || obj.datefin.value == "" || obj.libelle.value == "" || obj.ordre.value == "")
			alert("-Tous les champs * sont obligatoires-");
		else
			obj.submit();
	}
	
	function verification(){
		document.getElementById("change").value = 1;	
	}
</script>
<div id="zonetravail"><div class="titre">MODIFICATION TRIMESTRE : <?php echo utf8_decode($row['LIBELLE']); ?></div>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="proposer(); return false;" name="frm" method="POST" enctype="multipart/form-data">
	<div class="cadre">
    	<fieldset><legend>Renseignements sur le trimestre.</legend>
        	<table cellspacing="5"><tr>
            	<td class="lib">Libelle : <font style="font-size:9px; color:#777;">(Ex:1er Trimestre)</font>
                	<span class="asterisque">*</span>
                </td>
                <td><input value="<?php echo $row['LIBELLE']; ?>" type="text" maxlength="30" name="libelle" id="libelle" onchange="verification();"/></td>
                <td class="lib">Ordre : <span class="asterisque">*</span></td>
                <td><input value="<?php echo $row['ORDRE']; ?>" type="text" maxlength="1" name="ordre" id="ordre" onchange="verification();"/></td>                ,.
             <tr><td class="lib">Date de d&eacute;but : <span class="asterisque">*</span></td>
             	<td><input value="<?php echo $row['DATEDEBUT']; ?>" type="text" onchange="verification();" name="datedebut" id="datedebut" /></td>
             	<td class="lib">Date de fin :  <span class="asterisque">*</span></td>
                <td><input type="text" name="datefin" onchange="verification();" value="<?php echo $row['DATEFIN']; ?>" id="datefin"/></td>
            </tr></table>
        </fieldset>
    </div>
    <div class="navigation">
    	<input type="button" onClick="rediriger('periode.php');" value="Annuler"/><input type="hidden" value="2" name="step"/>
        <input type="submit" value="Suivant"/>
        <input type="hidden" name="change" id="change" value="0"/>
        <input type="hidden" name="idtrimestre" value="<?php echo $_GET["line"]; ?>" />
		<input type="hidden" name="periode" value="<?php echo $row['ANNEEACADEMIQUE']; ?>" />

    </div>
	</form>
</div>




<?php	
}

function delete_trimestre(){
	try{		
		$con = Database::connect2db();
		$stmt = $con->prepare("SELECT * FROM trimestre WHERE IDTRIMESTRE=:idtrimestre");
		$stmt->execute(array("idtrimestre"=>$_GET["line"]));
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		// select des sequences du semestre courant
		$stmt2 = $con->query("SELECT * FROM sequence WHERE IDTRIMESTRE=".$_GET["line"]);
		if($stmt2->rowCount() > 0){
			while( $ligne = $stmt2->fetch(PDO::FETCH_ASSOC) ){
				$con->exec("DELETE FROM note WHERE IDSEQUENCE=".$ligne["IDSEQUENCE"]);		
			}
			$con->exec("DELETE FROM sequence WHERE IDTRIMESTRE=".$_GET["line"]);
		}
		$con->exec("DELETE FROM trimestre WHERE IDTRIMESTRE=".$_GET["line"]);
		$con = NULL;
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());	
	}
	add_decoupage($row["ANNEEACADEMIQUE"]);
}

function trimestre(){
	if(isset($_POST["change"]))
		if($_POST["change"])
			if(isset($_POST["libelle"]) && isset($_POST["datefin"]) && isset($_POST["datedebut"]) && isset($_POST["ordre"])){
				// verification de la duplcation d' un trimestre uploade		
				try{
					
					$pdo = Database::connect2db();
					$res2 = $pdo->prepare("SELECT COUNT(*) AS NB FROM trimestre WHERE ANNEEACADEMIQUE = :id AND (LIBELLE = :libelle AND ORDRE = :ordre)");
					$res2->execute(array('id' => $_POST['periode'],
								 'libelle'=> $_POST["libelle"],
								 'ordre'=> $_POST["ordre"]));
					$row = $res2->fetch(PDO::FETCH_ASSOC);
					if($row["NB"] == 0)
						$pdo->exec("UPDATE trimestre SET
						DATEDEBUT='".parseDate($_POST["datedebut"])."',DATEFIN = '".parseDate($_POST["datefin"])."',ORDRE=".$_POST["ordre"].",LIBELLE='".$_POST["libelle"]."'
						WHERE IDTRIMESTRE=".$_POST["idtrimestre"]);
					$pdo =NULL;
					}catch(PDOException $e){
					var_dump($e->getTrace());
					die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());	
					}		
						
			}
			
			
			
	if(isset($_POST["libel"]) && isset($_POST["ordre"])){
		try{
			$pdo = Database::connect2db();
			$res2 = $pdo->prepare("SELECT COUNT(*) AS NB FROM sequence WHERE IDTRIMESTRE = :id AND (LIBELLE = :libelle OR ORDRE = :ordre)");
			$res2->execute(array('id' => $_POST['idtrimestre'],
								 'libelle'=> $_POST["libel"],
								 'ordre'=> $_POST["ordre"]));
			$row = $res2->fetch(PDO::FETCH_ASSOC);
			if($row["NB"] == 0){
				try{
					$pdo->exec("INSERT INTO sequence(IDTRIMESTRE,ORDRE,LIBELLE) VALUES
					(".$_POST["idtrimestre"].",".$_POST["ordre"].",'".$_POST["libel"]."')");
				}catch(PDOException $e){
					var_dump($e->getTrace());
					die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());	
				}		
			}
			
		 }catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
		}
	}
?>
<script type="text/javascript">
	function add_decoupage(){
		if(document.getElementById("init").value == 1){		
			var obj = document.forms['frm'];
			if(obj.libelle.value == "" || obj.ordre.value == "")
				alert("-Tous les champs * sont obligatoires-");
			else{
				if(!isNaN(obj.ordre.value))
					obj.submit();
				else
					alert("-Le champs * ordre est un nombre-");		
			}
		}else
			alert("-Aucune Sequence a enregistré-");
	}
	function init(){
		document.getElementById("libelle").value = "";	
		document.getElementById("ordre").value = "";
	}
	
	function ajout_trimestre(){
		if(document.getElementById("grid").style.display == "none"){
			document.getElementById("grid").style.display = "block";
			document.getElementById("init").value = 1;
		}else{
			 document.getElementById("grid").style.display = "none";
			 document.getElementById("init").value = 0;
			 init();
		}	
	}
</script>
<div id="zonetravail"><div class="titre">CONFIGURATION DES SEQUENCES AU TRIMESTRE COURANT</div>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" name="frm" onsubmit="add_decoupage(); return false;">
	<div class="cadre">
    	<fieldset><legend>Ajouter une sequence au trimestre : <?php print $_POST["libelle"] ?>&nbsp;&nbsp;&nbsp;<img src="../images/add.gif" style="cursor:pointer; margin-top:-3px;" onclick="ajout_trimestre();" title="Ajout trimestre"/></legend>
            
            <table id="grid" cellspacing="5px" style="display:none">
            	<tr>
                	<td class="lib">Libelle :  <span class="asterisque">*</span><font style="font-size:9px; color:#777;">(Ex:Seq 1)</font></td>
                    <td><input value="" type="text" maxlength="50" name="libel" id="libel"/><td>
                    <td class="lib">Ordre : <span class="asterisque">*</span></td>
                	<td><input value="" type="text" maxlength="1" name="ordre" id="ordre" /></td>
                </tr>
            </table>	
            <input type="hidden" name="init" id="init" value="0"/>
    <?php
			$query = "SELECT s.IDSEQUENCE,s.IDTRIMESTRE,s.LIBELLE,s.ORDRE
			FROM sequence s INNER JOIN trimestre t ON s.IDTRIMESTRE = t.IDTRIMESTRE 
			WHERE t.IDTRIMESTRE ='".$_POST["idtrimestre"]."' 
			ORDER BY s.IDSEQUENCE";
			$grid = new Grid($query);
			$grid->id = 0;
			$grid->addcolonne(2, "LIBELLE", '2', TRUE);
			$grid->addcolonne(3, "ORDRE", '3', TRUE);
			$grid->target = $_POST["periode"];
			/*
				Verifier les droit d'acces de modification et de suppression
				avant d'afficher les buttons de modification et de suppression
			*/
			if(is_autorized("DEL_PERIODE")){
				$grid->deletebutton = true;
				$grid->deletebuttontext = "Supprimer cette sequence";
			}
			$grid->display();
    ?>
    </div>
    <div class="navigation"><input type="button" onclick="rediriger('modifier.php?id=<?php echo $_POST['periode']; ?>');" value="Retour" />
        <input type="hidden" value="2" name="step"/>
        <input type="hidden" value="<?php echo $_POST['periode']; ?>" name="id"/>
        <input type="hidden" value="<?php echo $_POST['periode']; ?>" name="periode"/>
        <input type="hidden" value="<?php echo $_POST['libelle']; ?>" name="libelle"/>
        <input type="hidden" value="<?php echo $_POST['idtrimestre']; ?>" name="idtrimestre"/>
        <input type="hidden" value="0" name="change"/>
        <input type="submit" value="Valider">
    </div>
</form>
</div>

<?php
}

function delete_sequence(){
	try{		
		$con = Database::connect2db();
		$con->exec("DELETE FROM note WHERE IDSEQUENCE=".$_GET["line"]);			
		$con->exec("DELETE FROM sequence WHERE IDSEQUENCE=".$_GET["line"]);
		$con = NULL;
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());	
	}
	add_decoupage($_GET["periode"]);
}
/******************************************
*
*	Integration du modele de bas de page
*
******************************************/
require_once("../includes/footer_inc.php");
?>