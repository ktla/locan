<?php
/********************************************************************************************************/
	require_once("../includes/commun_inc.php");
/*
	Redirection si la session d l'utilisateur est acheve : inactif pendant $_SESSION['timeconnect']
	Confere fichier commun_inc.php pour retrouver la variable de temps de connexion
*/
	if(!isset($_SESSION['user']))	@header("location:../utilisateurs/connexion.php");
/********************************************************************************************************/
/*
	Verification des droits d'acces sur cette page
	Empeche l'acces a cet page par saisie d'url si on n'a pas les droits
*/
	$codepage = "ADD_PERIODE";
/********************************************************************************************************/
	$titre = "Gestion des p&eacute;riodes";
	require_once("../includes/header_inc.php");
	if(isset($_POST['step'])){
		if($_POST['step'] == 1)
			valider();
	}else
		proposer();
	require_once("../includes/footer_inc.php");
function proposer(){?>
<script>
	function proposer(){
		var obj = document.forms['frm'];
		if(obj.datedebut.value == "" || obj.datefin.value == "" || obj.periode.value == "" || obj.decoupage.value == "")
			alert("-Tous les champs * sont obligatoires-");
		else
			obj.submit();
	}
</script>
	<div id="zonetravail"><div class="titre">CREATION D'UNE PERIODE</div>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="proposer(); return false;" name="frm" method="POST" enctype="multipart/form-data">
	<div class="cadre">
    	<fieldset><legend>Renseignements sur la p&eacute;riode.</legend>
        	<table cellspacing="10"><tr>
            	<td class="lib">P&eacute;riode : <font style="font-size:9px; color:#777;">(Ex:2010-2011)</font>
                	<span class="asterisque">*</span></td>
                <td><input type="text" style="width:220px" maxlength="13" name="periode"/></td>
                <td class="lib">D&eacute;coupage : <span class="asterisque">*</span></td>
                <td>
                <?php 
						$query = "SELECT * FROM decoupage ORDER BY ID";
						$combo = new Combo($query, "decoupage", 0, 1, false);
						$combo->param = array('periode' => $_SESSION['periode']);
						$combo->onchange = "";
						$combo->first = '-Choisir un decoupage-';
						$combo->view();
					?>
                </td>
             <tr><td class="lib">Date de d&eacute;but : <span class="asterisque">*</span></td>
             	<td><input type="text" style="width:220px" name="datedebut" id="datedebut"/></td>
             	<td class="lib">Date de fin : <span class="asterisque">*</span></td>
                <td><input type="text" style="width:220px" name="datefin" size="20" id="datefin"/></td>
            </tr></table>
        </fieldset>
    </div>
    <div class="navigation">
    	<input type="button" onClick="home();" value="Annuler"/><input type="hidden" value="1" name="step"/>
        <input type="submit" value="Suivant"/>
    </div>
	</form>
</div>
<?php }

function valider(){
	try{
		if(id_exist($_POST['periode'], "ANNEEACADEMIQUE", "annee_academique")){
			echo "<p class=\"infos\">P&eacute;riode : ".$_POST['periode']." existent dans la base de donn&eacute;es.<br/>Veuillez changer de p&eacute;riode.</p>";
			proposer();
			return;
		}
	
		/*
			Insertion de l'annee academque
		*/
		$pdo = Database::connect2db();
		$query = "INSERT INTO annee_academique(ANNEEACADEMIQUE, DATEDEBUT, DATEFIN, DECOUPAGE) 
		 VALUES(:periode, :datedebut, :datefin, :decoupage)";
		 $res = $pdo->prepare($query);
		 $res->execute(array(
		 	'periode' => $_POST['periode'],
			'datedebut' => parseDate($_POST['datedebut']),
			'datefin' => parseDate($_POST['datefin']),
			'decoupage' => $_POST['decoupage']));
		$res->closeCursor();
		/*
			Rediriger vers la liste des periodes
		*/
		@header("Location:periode.php");
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
	}
}
?>