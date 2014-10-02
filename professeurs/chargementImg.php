<?php
function getExtension($type){
	$extension = "";
	switch($type){
		case 'image/png': $extension = 'png';break;
		case 'image/gif': $extension = 'gif';break;
		case 'image/jpeg': $extension = 'jpg';break;
		case 'image/bmp': $extension = 'bmp';break;
		default: $extension = 'others';break;
	}
	return $extension;
}


function getExtensio($name){
	$tab = explode(".",$name);
	return $tab[count($tab) - 1];	
}
$extensions = array('png', 'gif', 'jpg', 'bmp');
$extension = getExtension($_FILES["photos"]["type"]);
if (in_array($extension,$extensions)){ // verification de l' extension du fichier
	if ($_FILES["photos"]['size'] < 26214400){ // verification si la taille des fichier depasse la limite xxamp
		/*
		Ici j' ai eu un probleme , j' ai pas trouvé la fonction qui renvoie le chemin ou path complet d' un fichier.
		j' ai parcouru les fonctions realpath et dirname.
	    - realpath renvoi le chemin d' acces pour un fichier .php ou html contenu dans C:/wamp
		- dirname RAS
		
		Donc en cas de recherche de cette methode , [debut, fin] juste par le chemin du fichier.
		Moi j' ai pas encore de connaissance dessus alr j' ai opté pour la methode la plus longue :
			- je supprime le repertoire logo si celui contient une image
			- je copie l' image selectionnée dans le repertoire logo
			- ensuite de retourne le chemin
		
		*/	
	
		$dir = dir('./photos/tmp'); // debut
		while( $nom = $dir->read() ) { // on supprime les images contenues dans logo
			if(strlen($nom) > 2) 
				unlink("./photos/tmp/".$nom);
		}
		$dir->close() ; // fermeture du repertoire
		
	    if(move_uploaded_file($_FILES["photos"]['tmp_name'],"./photos/tmp/".$_FILES["photos"]['name'])){
			$data['message']="0;";
			$data['message'].="./photos/tmp/".$_FILES["photos"]['name'];
		}else{
				$data['message']="1;";
				$data['message'].="Erreur de chargement";
			 } // fin
	}else{
		$data['message']="2;";
		$data['message'].="Erreur taille fichier";
		}
}else{
	$data['message']="3;";
	$data['message'].="Extention non support&eacute;e";
}

$data['page'] = 
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<script type="text/javascript">
window.parent.CA.UploadAjax.callBack(\'{message}\');
</script>
</head>
<body>
</body>
</html>';
echo preg_replace('#\{([a-z0-9\-_]*?)\}#sie', '( ( isset($data[\'\1\']) ) ? $data[\'\1\'] : \'\' );', $data['page']);
unset($_FILES['logo']);
?>