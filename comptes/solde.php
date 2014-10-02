<?php
	require_once("../includes/commun_inc.php");
	if(!isset($_SESSION['user']))	header("location:../utilisateurs/connexion.php");
	$titre = "Gestion des soldes";
	require_once("../includes/header_inc.php");
	afficher();
	require_once("../includes/footer_inc.php");
function afficher(){?>
<div id="zonetravail"><div class="titre">COMPTE DES ELEVES.</div>
<form action="printsolde.php" name="frm1" onSubmit="frm1(); return false;" method="get" enctype="multipart/form-data">
	<div class="cadre">
    	<fieldset>
        	<table cellspacing="5"><tr><td>Entrer le matricule de l'&eacute;l&egrave;ve : </td>
            <td><input type="text" name="matel" size="30"></td><td><input type="hidden" name="view" value="1"/></td>
            <td><input type="submit" value="Valider"/></td></tr></table>
        </fieldset>
    </div>
</form>
<form action="printsolde.php" name="frm2" onSubmit="frm2(); return false;" method="get" enctype="multipart/form-data">
	<div class="cadre">
    	<fieldset>
        	<table cellspacing="5"><tr><td>Choisir la classe : </td>
            <td>
            	<?php $combo = new combo("SELECT * FROM classe", "classe", 0, 1, false);
					$combo->first = "-Choisir une classe-";
					$combo->view();
				 ?>
            </td><td><input type="submit" value="Valider"/> <input type="hidden" name="view" value="2"/></td></tr>
            </table>
        </fieldset>
    </div>
    <div class="navigation">
    </div>
</form>
</div>
<?php }?>