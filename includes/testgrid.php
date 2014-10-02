<html>
	<head>
    	<link rel="stylesheet" href="../css/blue/style.css" type="text/css" media="screen" />
		<script type="text/javascript" src="../js/jquery-1.9.1.js"></script>
		<script type="text/javascript" src="../js/jquery.tablesorter.js"></script>
        
    </head>
<body>
<?php
require_once(__DIR__."/commun_inc.php");
/**
	la classe grid prend en parametre la requete ainsi l'id qui est identifiant
	de la requete = primary key de la table principale, par defaut = 0
*/
$grid = new Grid("SELECT * FROM eleve ORDER BY MATEL");
/**
	parametre de addcolonne , $id, $text = telle afficher dans la colonne,
	$field = la colonne liee dans la bd peut etre numerique
*/
$grid->addcolonne(0, 'MATRICULE', 'MATEL', true); // ici le bind colonne est string
$grid->addcolonne(1, 'NOM ELEVE', '1', true); // ici le bind colonne est numeriqe
$grid->addcolonne(2, 'PRENOM ELEVE', 'PRENOM', true);
$grid->addcolonne(3, 'NAISSANCE', 'DATENAISS', true);
$grid->addcolonne(4, 'ADDRESSE', 'ADRESSE', false); // ici, on refuse d'afficher la colonne addresse
$grid->addcolonne(5, 'DATE AJOUT', 'DATEAJOUT', true);
/**
	puisque les colonnes id = 3 et 5 est une date, on peut utiliser le format date
	definit dans la classe dateFR
*/
$grid->setColDate(3);// le format de date specifier sera afficher aulieu du default AAAA-MM-JJ
$grid->setColDate(5);//Le format de mysql n'est pas adapter

$grid->display();
?>
</body>
</html>