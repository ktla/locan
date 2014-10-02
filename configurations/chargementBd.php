<?php
function getExtension($type){
	$extension = "";
	switch($type){
		case "text/x-sql": $extension = 'sql';break;
		default: $extension = 'others';break;
	}
	return $extension;
}

$extensions = array('sql');
$extension = getExtension($_FILES["bd"]["type"]);
if (in_array($extension,$extensions)){ // verification de l' extension du fichier
	if ($_FILES["bd"]['size'] < 26214400){ // verification si la taille des fichier depasse la limite xxamp
	 		$data['message']="0;";
			$data['message'].=$_FILES["bd"]['name'];
	}else{
		$data['message']="1;";
		$data['message'].="Erreur taille fichier";
		}
}else{
	$data['message']="2;";
	$data['message'].="Extention non support&eacute;e";
}

$data['page'] = 
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<script type="text/javascript">
window.parent.CS.UploadAjax.callBack(\'{message}\');
</script>
</head>
<body>
</body>
</html>';
echo preg_replace('#\{([a-z0-9\-_]*?)\}#sie', '( ( isset($data[\'\1\']) ) ? $data[\'\1\'] : \'\' );', $data['page']);
?>