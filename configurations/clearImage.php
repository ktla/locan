<?php 
$dir = dir('./logo'); // debut
		while( $nom = $dir->read() ) { // on supprime les images contenues dans logo
			unlink("./logo/".$nom);
		}
		$dir->close() ;

?>