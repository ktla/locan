<?php 
/***************************************************************************************************/
require_once("../includes/commun_inc.php");
/*
	Verification de la duree de la sesssion
*/
if(!isset($_SESSION['user'])) header("location:../utilisateurs/connexion.php");
$titre = "Edition des frais";
/*
	Verification du droit d'acces a cette page
	empeche l'acces par saisie d'url
*/
	$codepage = "SHOW_INSCRIT";
/***********************************************/
require_once("../includes/header_inc.php");
if(isset($_GET['classe']))
	details();
else
	lister();
/**********************************************
	Fonction relative de la page
	
/*********************************************/
function lister(){?>
<script>
	function lister(){
		var frm = document.forms['frm'];
		if(frm.classe.value == "")
			alert("Ce champ est obligatoire");
		else
			frm.submit();
	}
</script>
<div id="zonetravail"><div class="titre">Afficher les El&egrave;ves d'une classe</div>
<form action="inscrit.php" method="GET" name="frm" enctype="multipart/form-data" onSubmit="lister(); return false;">
	<div class="cadre">
    	<table>
        	<tr><td>Choisir une classe : </td>
            	<td><?php 
					$eleve = new Combo("SELECT IDCLASSE, LIBELLE FROM classe ORDER BY IDCLASSE", 'classe', 0, 1, false);
					$eleve->first = "--Choisir une classe--";
					$eleve->view();
				?></td>
               </tr>
        </table>
    </div>
    <div class="navigation">
    	<input type="button" value="Retour" onClick="home();" />
        <input type="submit" value="Valider" />
    </div>
</form>
</div>
<?php
}
/***************************************
Grille d'eleve inscrit a cette classe 
*****************************************/
function details(){?>
<div id="zonetravail"><div class="titre">El&egrave;ves inscrits de la classe : <?php echo $_GET['classe']; ?></div>
<form action="inscrit.php" method="POST" name="frmgrid" enctype="multipart/form-data" onSubmit="lister(); return false;">
	<div class="cadre">
	<?php 
		$print = new printlink("imprimerinscrit.php?classe=$_GET[classe]", false);
		$print->display();
		$query = "SELECT i.IDINSCRIPTION, e.* 
		FROM eleve e 
		INNER JOIN inscription i ON (e.MATEL = i.MATEL AND i.PERIODE = :periode AND i.IDCLASSE = :classe) 
		ORDER BY e.MATEL";
		$eleve = new Grid($query, 0);
		$eleve->param = array("periode" => $_SESSION['periode'], "classe" => $_GET['classe']);
		$eleve->addcolonne(0, "IDINSCRIPTION", "IDINSCRIPTION", false);
		$eleve->addcolonne(1, "MATRICULE", "MATEL", true);
		$eleve->addcolonne(2, "NOM", "NOMEL", true);
		$eleve->addcolonne(3, "PRENOM", "PRENOM", true);
		$eleve->addcolonne(4, "TELEPHONE", "TEL", true);
		$eleve->actionbutton = false;
		$eleve->selectbutton = true;
		$eleve->editbutton = "Modifier l'inscription";
		$eleve->deletebutton = "D&eacute;sinscrire cet &eacute;l&egrave;ve";
		$eleve->display("100%", "100%");
    ?>
    </div>
    <div class="navigation">
    	<input type="button" value="Retour" onClick="home();" />
        <input type="submit" value="Valider" />
    </div>
</form>
</div>

<?php }
/********************************************
Integration du modele de bas de page
*********************************************/
require_once("../includes/footer_inc.php");
?>