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
	- $codepage : Code de page utile pour la verification des droits d'acces
		Les droit de modification commence par EDIT_ suivit du nom menu (EDIT_PROFESSEUR, EDIT_CLASSE, EDIT_PERIODE...)
		Les droit de supression commence par DEL_ suivit du nom menu (DEL_PROFESSEUR, DEL_CLASSE, DEL_PERIODE...)
*/
	$codepage = "ADD_RELIGION";
/********************************************************************************************************/
	$titre = "Ajouter une religion.";
	require_once("../includes/header_inc.php");

	if(isset($_POST["step"])){
		switch($_POST["step"]){
			case 0 : step0(); break;
			case 1 : step1(); break;
			case 2 : step2(); break;
		}	
	}else
		step0();	
	require_once("../includes/footer_inc.php");
	function step0(){
		if( isset($_GET["action"]) )
			if(!strcmp($_GET["action"],"edit"))	{
				step1();
				return;
			}else{
				$religion = new Input("religion","IDRELIGION","LIBELLE");
				$religion->delete($_GET["line"]);
				}
				
?>
	<div id="zonetravail"><div class="titre">GESTION RELIGION</div>
    	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frm" method="post" enctype="multipart/form-data">
  			<?php 
			print "<div class = 'cadre'><fieldset><legend>Liste des religions.</legend>"; 
			$query = "SELECT * FROM religion ORDER BY IDRELIGION";
			$grid = new Grid($query);
			$grid->id = 0;
			$grid->addcolonne(0, "ID.", '0', TRUE);
			$grid->addcolonne(1, "LIBELLE.", '1', TRUE);
			$grid->editbutton = true;
			$grid->editbuttontext = "Modifier";
			$grid->deletebutton = true;
			$grid->deletebuttontext = "Supprimer";
			$grid->display();
			print "</fieldset></div>";
			?>
           
            <div class="navigation">
               <input type="hidden" name="step" value="1"/>
               <input type="button" onClick="home();" value="Annuler"/>
               <input type="submit" value="Ajouter"/>
            </div>
        </form>
    </div>

<?php 
	}
	function step1(){
		$religion = new Input("religion","IDRELIGION","LIBELLE");
?>
<script>
	function addreligion(){
		var obj = document.forms['frm'];
		if(obj.libelle.value == ""){
			alert("-Les champs marques par * sont obligatoires-");
			return;
		}
		obj.submit();
	}
</script>
	<div id="zonetravail"><div class="titre">GESTION RELIGION</div>
    	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frm" method="post" enctype="multipart/form-data" onsubmit="addreligion(); return false;">
  			
			<div class = 'cadre'><fieldset><legend>Ajout et modification de religion.</legend> 
			<table cellspacing="5">
              <tr>
                  <td class="lib">Libelle : <span class="asterisque">*</span></td>
                  <td><input  type="text"  name="libelle" value="<?php print isset($_GET['action'])? utf8_decode($religion->getIndex($_GET['line'])) : "" ?>" size="40"/></td>
              </tr>
             </table>
			</fieldset></div>
            <div class="navigation">
            <?php 
				if(isset($_GET['action']))
					print "<input type=\"hidden\" name=\"action\" value=\"".$_GET['line']."\"/>";
			?>
               <input type="hidden" name="step" value="2"/>
               <input type="button" onClick="document.location = './religion.php';" value="Precedent"/>
               <input type="submit" value="Valider"/>
            </div>
        </form>
    </div>

<?php 
	}
	
	function step2(){
		$religion = new Input("religion","IDRELIGION","LIBELLE");
		if( isset($_POST["action"]) ){
			if(!$religion->exist($_POST["libelle"]))
			$religion->update($_POST["libelle"],$_POST["action"]);
		}else
			if(!$religion->exist($_POST["libelle"]))
				$religion->insert($_POST["libelle"]);
		step0();	
	}
?>