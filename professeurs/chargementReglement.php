<?php
function getExtension($type){
	$extension = "";
	switch($type){
		case 'application/pdf': $extension ='pdf';break;
		case 'application/msword': $extension = 'doc';break;
		case 'application/force-download': $extension = 'docx';break;
		case 'text/plain': $extension = 'txt';break;
		default: $extension = 'others';break;
	}
	return $extension;
}

$extensions = array('txt', 'doc', 'docx', 'pdf');
$extension = getExtension($_FILES["reglement"]["type"]);
if (in_array($extension,$extensions)){ // verification de l' extension du fichier
	if ($_FILES["reglement"]['size'] < 26214400){ // verification si la taille des fichier depasse la limite xxamp
			
			$dir = dir('./curriculums/tmp'); // debut
			while( $nom = $dir->read() ) { // on supprime les images contenues dans logo
				if(strlen($nom) > 2) 
					unlink("./curriculums/tmp/".$nom);
			}
			$dir->close() ; // fermeture du repertoire
	 		if(move_uploaded_file($_FILES["reglement"]['tmp_name'],"./curriculums/tmp/".$_FILES["reglement"]['name'])){
				$data['message']="0;";
				$data['message'].="".$_FILES["reglement"]['name'];
			}else{
					$data['message']="1;";
					$data['message'].="Erreur de chargement";
				 }
			
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
window.parent.CD.UploadAjax.callBack(\'{message}\');
</script>
</head>
<body>
</body>
</html>';
echo preg_replace('#\{([a-z0-9\-_]*?)\}#sie', '( ( isset($data[\'\1\']) ) ? $data[\'\1\'] : \'\' );', $data['page']);
?>