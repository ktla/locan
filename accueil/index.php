<?php
	require_once("../includes/commun_inc.php");
	if(!isset($_SESSION['user']))
		@header("location:../utilisateurs/connexion.php");
	else{
		$titre = "Accueil";
		$codepage = "ACCUEIL";
		require_once("../includes/header_inc.php");
		?>
        <style>

</style>
        <div>
        	<div><p class = 'titleconnexion' style="margin:0px; border-bottom:1px #EEE; border-bottom-style:ridge;"> <img src="../images/b_home.png" /> Accueil </p>
            </div>			
            <div class="bloc_publication">
            <form>
				<fieldset class="publication" style="border:none">
                	<legend style="font-size:11px; color:#777; font-style:italic;">Publier une Information !</legend>
					<div style="position:relative; height:60px; width:550px; max-height:60px; max-width:550px; overflow:hidden; border:#CCC solid 1px; margin-left:2px;margin-bottom:2px;">
						<textarea name="publication" id="publication" style="position:relative; margin:0px; border:none; overflow-x:hidden; " rows="2000" cols="79">
                        </textarea>
					</div>	
					<span class="button_publier" onclick="publier()"><font color="#FFF">Publier</font></span>
				</fieldset>
             </form>
			</div>
        </div>
<?php 
		require_once("../includes/footer_inc.php");
	}
?>


