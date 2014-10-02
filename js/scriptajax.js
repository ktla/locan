function getXMLHttpRequest() {
	if (window.XMLHttpRequest) {		//test pour navigateurs:Mozilla, opéra ...
		var xmlHttpReq = new XMLHttpRequest();   		// évite un bogue du navigateur Safari
		if (xmlHttpReq.overrideMimeType) 
			xmlHttpReq.overrideMimeType("text/xml");
		return xmlHttpReq;
	} 
	else if (window.ActiveXObject) {
		try {							//pour navigateur Internet Explorer sup 5.0
			return new ActiveXObject("Msxml2.XMLHTTP");
		} catch (err) {}
		try {							//pour navigateur Internet Explorer 5.0
			return new ActiveXObject("Microsoft.XMLHTTP");
		} catch (err) {}
	}
	throw new Error("Impossible de créer l'objet" + "XMLHttpRequest pour le navigateur. Changer de Navigateur");
}
function remove_content(obj){
	if(obj!=null){
		while(obj.firstChild)
			obj.removeChild(obj.firstChild)
	}
}
function execute(req, id, idloading){
	var obj = document.getElementById(id);
	if(req.readyState==4){
		if(req.status==200){
			remove_content(obj);
			obj.innerHTML = req.responseText;
			if(idloading != "")
				document.getElementById(idloading).style.visibility = 'hidden';
		}else{
			obj.innerHTML='Erreur server '+req.status+'-----'+req.statusText;
			req.abort();
			req = null;
			if(idloading != "")
				document.getElementById(idloading).style.visibility = 'hidden';
		}
	}
}
//Application ajax sans parametre
function callajax(url,id, idloading){
	if(idloading != "")
		document.getElementById(idloading).style.visibility = 'visible';
	var requete = getXMLHttpRequest();
	requete.onreadystatechange = function() {execute(requete,id, idloading);};
	requete.open('GET',url, true); //asynchrone
	requete.send(null);
}
//Application ajax avec envoie de parametre POST
//Identique a la derniere
function call2(url,param){
	document.getElementById('loading').style.visibility='visible';
	var requete=getXMLHttpRequest();
	requete.open('POST',url,true); //asynchrone
	requete.onreadystatechange=function() {execute(requete);};
	//Entete content-type pour les requete avec parametre envoyer avec la methode POST
	requete.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	requete.send(param);
}