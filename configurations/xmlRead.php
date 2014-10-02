<?php
	function xmlRead($xml){
		$arbre = NULL;
		while ($xml->read()){
				if($xml->nodeType == XMLReader::END_ELEMENT)
					return $arbre;
				else if($xml->nodeType == XMLReader::ELEMENT){
					$noeud = array();
					$noeud['noeud'] = $xml->name;
					if(!$xml->isEmptyElement){
						$fils = xmlRead($xml);
						$noeud['fils'] = $fils;
					}
					$arbre[] = $noeud;
				}else if($xml->nodeType == XMLReader::TEXT){
					$noeud = array();
					$noeud['text'] = $xml->value;
					$arbre[] = $noeud;	
				}
				
		}
		return $arbre;
	}

	function xmlTabAssoc($tree){
		$assoc = new ArrayObject;
		for($i=0;$i<count($tree[0]['fils']);$i++){
			$assoc[$tree[0]['fils'][$i]['noeud']] = $tree[0]['fils'][$i]['fils'][0]['text'];
		}		
		return $assoc;
	}
	

$xml= new XMLReader();
$xml->open("./config.xml");
$tree = xmlRead($xml);
$xml->close();

var_dump(xmlTabAssoc($tree));
?>