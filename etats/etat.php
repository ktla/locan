<?php
require_once("../includes/commun_inc.php");
if(!isset($_SESSION['user']))	header("location:../utilisateurs/connexion.php");
$codepage =  "PRINT_ETAT";
$titre = "Impressions des Etats";
require_once("../includes/header_inc.php");
if(isset($_GET['action'])){
	switch($_GET['action']){
		case "edit":edit();break;
		case "delete":delete();break;
		case "deleteall":deleteall();break;
		case "rechercher":rechercher();break;
	}
}else
	afficher();
require_once("../includes/footer_inc.php");
function afficher(){?>
<div id="zonetravail"><div class="titre">CHOIX DE L'ETAT</div>
<form enctype="multipart/form-data" name="frm" target="_blank" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
	<div class="cadre">
    <fieldset><legend>Choisir l'Etat &agrave; imprimer</legend>
    <table cellspacing="5">
    	<tr><td><a href="../notes/carnet.php">Carnets de notes</a></td>
        	<td><a href="../administrations/statreussite.php">Statistique de r&eacute;ssite.</a></td>
        	<td><a href="../eleves/certificat.php">Certificats de scolarit&eacute;.</a></td>
        	<td><a href="../administrations/statreussite.php">Tabeau d'honneur.</a></td>
        </tr>
        <tr><td><a href="../notes/carnet.php">Carnets de notes</a></td>
        	<td><a href="../administrations/statreussite.php">Statistique de r&eacute;ssite.</a></td>
        </tr>
    </table>
    </fieldset>
    </div>
    <div class="navigation">
    </div>
</form>
</div>
<?php }
?>