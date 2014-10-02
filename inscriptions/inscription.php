<?php
	require_once("../includes/commun_inc.php");
	if(!isset($_SESSION['user']))
		header("location:../utilisateurs/connexion.php");
	$titre = "Inscriptions et R&eacute;inscriptions";
	$codepage = "INSCRIPTION_ELEVE";
	require_once("../includes/header_inc.php");
		choose();
	require_once("../includes/footer_inc.php");
function choose(){?>
<script src="../SpryAssets/SpryTabbedPanels.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
<div id="zonetravail"><div class="titre">INSCRIPTIONS ET REINSCRIPTIONS</div>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" enctype="multipart/form-data">
    	<div id="TabbedPanels1" class="TabbedPanels" style="margin-top:5px;">
        	<ul class="TabbedPanelsTabGroup">
            	<li class="TabbedPanelsTab" tabindex="0">Nouvelle</li>
            	<li class="TabbedPanelsTab" tabindex="1">R&eacute;inscription</li>
            	<li class="TabbedPanelsTab" tabindex="2">D&eacute;sincription</li>
          	</ul>
          	<div class="TabbedPanelsContentGroup">
            	<div class="TabbedPanelsContent">
                	<?php require_once("nouvelle.php"); ?>
            	</div>
            	<div class="TabbedPanelsContent">
                	<?php require_once("reinscription.php"); ?>
            	</div>
            	<div class="TabbedPanelsContent">
                	<?php require_once("desinscription.php"); ?>
            	</div>
            </div>
       </div>
    </form>
</div>
<?php }
?>