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
	$codepage = "GESTION_MENU";
/********************************************************************************************************/
$titre = "Gestion des menus";
require_once("../includes/header_inc.php");
show_menu();
require_once("../includes/footer_inc.php");
/*
	Fonction propres a la page
*/
function show_menu(){?>
<div id="zonetravail"><div class="titre">GESTION MENU</div>
	<form name="frm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <div class="cadre">
    <?php 
		$query = "SELECT m.IDMENU, m.LIBELLE, m.SIGNIFICATION, h.LIBELLE AS LBL, 
		CONCAT(\"<a href = 'changeretat.php?id=\", IDMENU, \"'>\",IF(ACTIF = 1, 'Actif', 'Bloqu&eacute;'), \"</a>\") AS ETAT 
		FROM menu m 
		LEFT JOIN header_menu h ON (h.IDHEADER = m.HEADER) ORDER BY m.HEADER";
		$grid = new grid($query, 0);
		$grid->addcolonne(0, "ID", "IDMENU", true);
		$grid->addcolonne(1, "LIBELLE", "LIBELLE", true);
		$grid->addcolonne(2, "SIGNIFICATION", "SIGNIFICATION", true);
		$grid->addcolonne(3, "GROUPE", "LBL", true);
		$grid->addcolonne(4, "ETAT", "ETAT", true);
		$grid->selectbutton = true;
		$grid->display();
	?>
    </div>
    <div class="navigation">
    </div>
    </form>
</div>
<?php }
?>