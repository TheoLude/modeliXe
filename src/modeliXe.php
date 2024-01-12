<?php
/*
Licence et conditions d'utilisations-----------------------------------------------------------------------------

-English---------------------------------------------------------------------------------------------------------
Copyright (C) 2001  - AUTHOR
- ANDRE thierry
- ADDING
- VILDAY Laurent.
- MOULRON Diogene.
- DELVILLE Romain.
- BOUCHERY Frederic.
- PERRICHOT Florian.
- RODIER Phillipe.
- HOUZE Sebastien.
- DECLEE Frederic.
- HORDEAUX Sebastien.
- LELARGE Guillaume.
- GAUTHIER Jeremy.
- CASANOVA Matthieu.
- KELLER Christophe.
- MARK HUMPHREYS Aidan.
- KELLUM Patrick.
- DE CORNUAUD Sebastien.
- PIEL Regis.
- LE LOARER Loec.

This library is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General
Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option)
any later version.

This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
details.

You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to :

Free Software Foundation,
Inc., 59 Temple Place,
Suite 330, Boston,
MA 02111-1307, Etats-Unis.
------------------------------------------------------------------------------------------------------------------

-Francais---------------------------------------------------------------------------------------------------------
ModeliXe est distribue sous licence LGPL, merci de laisser cette en-tete, gage et garantie de cette licence.
ModeliXe est un moteur de template destin+ e etre utilise par des applications +crites en PHP.
ModeliXe peut etre utilis+ dans des scripts vendus e des tiers aux titres de la licence LGPL. ModeliXe n'en reste
pas moins OpenSource et libre de droits en date du 23 Aoet 2001.

Copyright (C) 2001  - Auteur
- ANDRE thierry
- Ajouts
- VILDAY Laurent.
- MOULRON Diogene.
- DELVILLE Romain.
- BOUCHERY Frederic.
- PERRICHOT Florian.
- RODIER Phillipe.
- HOUZE Sebastien.
- DECLEE Frederic.
- HORDEAUX Sebastien.
- LELARGE Guillaume.
- GAUTHIER Jeremy.
- CASANOVA Matthieu.
- KELLER Christophe.
- MARK HUMPHREYS Aidan.
- KELLUM Patrick.
- DE CORNUAUD Sebastien.
- PIEL Regis.
- LE LOARER Loec.

Cette bibliotheque est libre, vous pouvez la redistribuer et/ou la modifier selon les termes de la Licence Publique
G+n+rale GNU Limitee publiee par la Free Software Foundation version 2.1 et ult+rieure.

Cette bibliotheque est distribu+e car potentiellement utile, mais SANS AUCUNE GARANTIE, ni explicite ni implicite,
y compris les garanties de commercialisation ou d'adaptation dans un but sp+cifique. Reportez-vous a la Licence
Publique Generale GNU Limitee pour plus de details.

Vous devez avoir reeu une copie de la Licence Publique Generale GNU Limitee en meme temps que cette bibliotheque;
si ce n'est pas le cas, ecrivez a:

Free Software Foundation,
Inc., 59 Temple Place,
Suite 330, Boston,
MA 02111-1307, Etats-Unis.

Pour tout renseignements mailez a modelixe@free.fr ou thierry.andre@freesbee.fr
--------------------------------------------------------------------------------------------------------------------
*/

/*------------------------------------------------------------------------------------------------------------------
Version realisee par Thierry ANDRE pour la societe Calitude dans le cadre de la licence L-GPL.

Version 1.0 - 28 mai 2002 - Suppression de la gestion du cache, de la compression, reduction du toolkit, suppression
du mod_rewrite, suppression du traceur de performance, suppression du parametrage du format de sortie, etc.

Version 1.?? historique perdu, MxHiddenPile, gestion des blocs mal fermes et autres

Version 1.0b - 17/03/2003 [Jojo]
Optimisation , remplacements des str_replace lents par du parcours de chaines (50s  -> 5s) ( ftl )
Gestion temporaire d'un fichier de parametrage eclate en plusieurs volumes	( .ext, .ext1, .ext2, etc.. ).
mod :: Modelixe -> GetParameterParsing()

/*------------------------------------------------------------------------------------------------------------------*/

/*------------------------------------------------------------------------------------------------------------------
Version realisee par Thierry ANDRE pour les développements de la société DEROMA France.

Version 1.2 - Différentes améliorations, dont notamment la possibilité de supprimer un champ quelconque (FormField,
Select, Image, etc.) avec l'opérateur delete

/*------------------------------------------------------------------------------------------------------------------*/

namespace eXtensia\dataBaseAbstractLayer;

class ModeliXe extends \eXtensia\errorManager\errorManager{

	var $template = '';
	var $absolutePath = '';
	var $relativePath = '';
	var $sessionParameter = '';
	var $mXParameterFile = '';
	var $mXUrlKey = '';
	var $mXChronoInfo = '';

	var $outputSystem = '/>';
	var $flagSystem = 'xml';
	var $adressSystem = 'relative';
	var $mXVersion = 'pre 1.1';

	var $mXsetting = false;
	var $mXoutput = false;
	var $MxEnd = false;
	var $mXChrono = false;

	var $mXsignature = true;
	var $isTemplateFile = true;

	var $templateContent = array();
	var $sheetBuilding = array();
	var $deleted = array();
	var $replacement = array();
	var $loop = array();
	var $IsALoop = array();
	var $xPattern = array();
	var $formField = array();
	var $checker = array();
	var $attribut = array();
	var $attributKey = array();
	var $htmlAtt = array();
	var $select = array();
	var $hidden = array();
	var $image = array();
	var $text = array();
	var $father = array();
	var $son = array();

	var $flagArray = array(0 => 'hidden', 1 => 'select', 2 => 'image', 3 => 'text', 4 => 'checker', 5 => 'formField');
	var $attributArray = array(0 => 'attribut');

	//MX Generator----------------------------------------------------------------------------------------------------

	//Constructeur de ModeliXe
	function ModeliXe ($template, $sessionParameter = '', $templateFileParameter = '', $cacheDelay = -1){

		$this -> ErrorManager();

		$time = explode(' ',microtime());
		$this -> debut = $time[1] + $time[0];

		//Gestion des parametres par d+faut

		//D+finition du r+pertoire de template
		if (defined('MX_TEMPLATE_PATH') && is_file(constant('MX_TEMPLATE_PATH').'/'.$template)) $this -> SetMxTemplatePath(MX_TEMPLATE_PATH);

		//Activation de la signature
		if (defined('MX_SIGNATURE')) $this -> SetMxSignature(MX_SIGNATURE);

		//D+finition du fichier de param+trage
		if (constant('MX_DEFAULT_PARAMETER') != '' && ! $templateFileParameter && @is_file(MX_DEFAULT_PARAMETER)){
			$this -> SetMxFileParameter(MX_DEFAULT_PARAMETER);
		}
		elseif ($templateFileParameter != ''){
			$this -> SetMxFileParameter($templateFileParameter);
		}

		//Gestion des parametres de sessions
		if ($sessionParameter) $this -> sessionParameter = $sessionParameter;

		//Instanciation de la ressource templates
		if (@is_file($this -> mXTemplatePath.$template)) $this -> template = $template;
		elseif (isset($template)) {
			$this -> template = $template;
			$this -> isTemplateFile = false;
		}
		else $this -> ErrorTracker (5, 'No template file defined.', 'ModeliXe', __FILE__, __LINE__);

		//Affectation du path d'origine
		if ($this -> ErrorChecker()) {
			$this -> absolutePath = substr(basename($this -> template), 0, strpos(basename($this -> template), '.'));
			$this -> relativePath = $this -> absolutePath;
		}
	}

	//Setting ModeliXe -------------------------------------------------------------------------------------------

	//Methode d'instanciation du template
	function SetModelixe($out = ''){

		if ($this -> mXsetting)  $this -> ErrorTracker(4, 'You can\'t re-use this method after instanciate ModeliXe once time.', 'SetModelixe', __FILE__, __LINE__);
		if ($out) $this -> mXoutput = true;

		//Test du cache et insertion eventuelle
		if ($this -> mXCacheDelay > 0){
			$this -> mXUrlKey = $this -> GetMD5UrlKey();

			if ($this -> MxCheckCache()){
				$this -> MxGetCache();
				return $this -> MxEnd = true;
			}
		}

		//Initialisation de la classe
		$this -> GetMxFile();
		if ($this -> ErrorChecker()) $this -> MxParsing($this -> templateContent[$this -> absolutePath]);
		$this -> mXsetting = true;
	}

	//Instanciation de la signature
	function SetMxSignature($arg = ''){
		if ($arg != 'on') $this -> mXsignature = false;
		else $this -> mXsignature = true;

		return $this -> mXsignature;
	}

	// Instanciation du chronometre
	function setMxChrono($str_infos = ''){
		$this -> mXChrono = true;
		$this -> mXChronoInfo .= $str_infos;
	}

	//Instanciation du template path
	function SetMxTemplatePath($arg = ''){

		if ($this -> mXsetting) $this -> ErrorTracker(1, 'You can\'t use this method after instanciate ModeliXe with setModeliXe method, it won\'t have any effects.', 'SetMxTemplatePath', __FILE__, __LINE__);
		else {
			if ($arg[strlen($arg) - 1] != '/' && $arg) $arg .= '/';

			if (! is_dir($arg)) $this -> ErrorTracker(5, 'The MX_TEMPLATE_PATH (<b>'.$arg.'</b>) is not a directory.', 'SetMxTemplatePath', __FILE__, __LINE__);
			else $this -> mXTemplatePath = $arg;
		}

		return $this -> mXTemplatePath;
	}

	function getMxTemplatePath(){
		return $this -> mXTemplatePath;
	}

	//Instanciation du fichier de parametre
	function SetMxFileParameter($arg = ''){
		if ($arg == '') $this -> ErrorTracker(4, 'Aucun profil valide n\'a &eacute;t&eacute; d&eacute;fini. La valeur de profil donn&eacute;e est vide.', 'SetMxFileParameter', __FILE__, __LINE__);

		if ($arg && ! @is_file($arg)) $this -> ErrorTracker(5, 'The parameter\'s file path (<b>'.$arg.'</b>) does not exist.', 'SetMxFileParameter', __FILE__, __LINE__);
		else $this -> mXParameterFile = $arg;

		return $this -> mXParameterFile;
	}

	//Instanciation des parametres de session
	function SetMxSession($arg){
		$this -> sessionParameter = $arg.'&refresh='.time(); // [Mod Theo 17/10/2002] pour forcer le rafraichissment.
	}

	// Methode qui renvoie vrai si le bloc existe
	function isMxBloc($index){
		if ($this -> MxEnd) return false;
		if ($this -> adressSystem == 'relative') {
			if ($index) $index = $this -> relativePath.'.'.$index;
			else $index = $this -> relativePath;
		}
		else $index = $this -> absolutePath.'.'.$index;

		$fat = $this -> father[$index];
		if (! $fat && $index != $this -> absolutePath)  return false;
		else return true;
	}

	//Setting tools -----------------------------------------------------------------------------------------------

	function MxEscapeQuote($str_val){
		return str_replace('"', '&quot;', $str_val);
	}

	// Permet de d'echapper les & utilises pour construire les hidden
	function MxEscapeEt($str_val){
		return str_replace('&', '~caliEt~', $str_val);
	}

	// Permet de desechapper les & utilises pour construire les hidden
	function MxUnescapeEt($str_val){
		return str_replace('~caliEt~', '&', $str_val);
	}

	//Recherche du fichier de template
	function GetMxFile($source = ''){

		global $GLOBAL_USING_HTML;

		$boolDirectHtml = false; //[MOD Théo 12/02/2015]
		if (! $source) {
			if (@is_file($this -> mXTemplatePath.$this -> template)) $source = $this -> mXTemplatePath.$this -> template; //[MOD Théo 12/02/2015]
			else {
				$boolDirectHtml = true; //[MOD Théo 12/02/2015]
				$source = 'directHtml'; //[MOD Théo 12/02/2015]
			}
		}
		$GLOBAL_USING_HTML[] = $source;

		if (! $boolDirectHtml){ //[MOD Théo 12/02/2015]
			if (! $read = @fopen($source, 'rb')) $this -> ErrorTracker (5, 'Can\'t open this template file (<b>'.$source.'</b>) in read, see for change the read modalities.', 'GetMxFile', __FILE__, __LINE__);
			else {
				if (! $result = @fread($read, filesize($source)))  $this -> ErrorTracker (5, 'Can\'t read the template file (<b>'.$source.'</b>), see for file format and integrity.', 'GetMxFile', __FILE__, __LINE__);
				fclose($read);
			}
		}
		else $result = $this -> template; //[MOD Théo 12/02/2015]

		if (empty($result)) $result = '[no parsing, template file <b>'.$source.'</b> not found or invalid]';
		if ($this -> mXsignature && $source != $this -> mXTemplatePath.$this -> template && ! $boolDirectHtml) $result = "\n<!--[ModeliXe ".$this -> mXVersion.']-- [StartOf'.$dyn.'Inclusion : '.$source."] -->\n\n".$result."\n\n<!--[ModeliXe ".$this -> mXVersion.']-- [EndOf'.$dyn.'Inclusion : '.$source."] -->\n";

		//Affectation du path d'origine, et du content du template
		if ($source == $this -> mXTemplatePath.$this -> template || $boolDirectHtml) $this -> templateContent[$this -> absolutePath] = $result;
		else return $result;
	}

	//Lecture du fichier de configuration et parsing
	function GetParameterParsing ($template){

		global $tab_global_profil_unique;

		if (is_array($tab_global_profil_unique)) $tab_config = $tab_global_profil_unique;
		else {
			if (! @is_file($this -> mXParameterFile)) $this -> ErrorTracker(5,  "Le fichier du profil contenant les param&eacute;tres d'affichage et le texte relatif au template en cours n'a pu etre pars+, veuillez v+rifier qu'il se trouve bien e cet emplacement <b>".$this -> mXParameterFile."</b>.");
			else {
				if (stristr(getenv('OS'), 'win')) {}
				else {
					if (filesize($this -> mXParameterFile) >= 16384) {
						print "fichier ".$this -> mXParameterFile." trop gros : ".filesize($this -> mXParameterFile);
						exit;
					}
				}
				$tab_config = @parse_ini_file($this -> mXParameterFile, true);
			}


			//ajout 17/03/2003 [Jojo] pour parse_ini_file qui plante sur le fichier de conf de l'ARS ( input buffer overflow REJECTS on scanner )
			//recherche simplement les morceaux eclates du mxp original, extensionnes ( ! ) nomoriginal1, 2,  etc
			$i = 1;

			//if (! defined('CST_ENV_MXP')) $this -> errorTracker(5, "La constante CST_ENV_MXP informant du path du profil n'est pas d&eacute;finie.");

			while ( @is_file($this -> mXParameterFile."$i")) {
				if ( stristr(getenv('OS'), 'win') ) {
				} else {
					if (filesize($this -> mXParameterFile.$i) >= 16384 ) {
						print "fichier ".$this -> mXParameterFile."$i trop gros : ".filesize($this -> mXParameterFile."$i");
						exit;
					}
				}

				$tmp_tab = array();
				$tmp_tab = @parse_ini_file($this -> mXParameterFile."$i",true);
				foreach ( $tmp_tab as $section => $section_info ) {
					foreach ($section_info as $key => $value ) {
						$tab_config[$section][$key] = $value;
					}
				}
				$i++;
			}
			$tab_global_profil_unique = $tab_config;
		}
		if (! is_array($tab_config['general']) || ! is_array($tab_config['images']) || ! is_array($tab_config['attributs'])) $this -> ErrorTracker(5,  "Le fichier de profil a +t+ corrompu et ne peut etre utilis+ par l'application, veuillez v+rifier sa structure. <b>".$this -> mXParameterFile."</b>.");


		// algo par parcours, moy 3*plus speed, bcp plus sur une tres grosse chaine *10 <Jojo>
		$resultat = '';
		$lng = strlen($template);
		$bool_debut = true;
		for ($i = 0; $i <= $lng - 8 ; $i++){
			if ($template{$i} == '<') {
				if ( $template{$i + 1} == 'm') {
					if ( $template{$i + 2} == 'x') {
						if ( $template{$i + 3} == ':') {
							if ( $template{$i + 4} == 'p') {
								if ( $template{$i + 5} == 'r') {
									if ( $template{$i + 6} == 'e') {
										if ( $template{$i + 7} == 'f') {
											if ( $template{$i + 8} == ' ') {
												$i_initial = $i;
												while ( $template{$i} != '"' ){
													$i++;
												}
												$i_signature = $i;
												$signature = '';
												while ( !($template{$i + 1} == '"') ){
													$signature .= $template{$i + 1};
													$i++;
												}
												while ($template{$i} != '>') {
													$i++;
												}
												$i_final_precedent = $i_final;
												$i_final = $i;
												if ($bool_debut ) {
													$resultat = substr($template,0,($i_initial)).$tab_config['general'][$signature];
												} else {
													$resultat .= substr($template,$i_final_precedent+1,($i_initial-$i_final_precedent-1)).$tab_config['general'][$signature];
												}
												$bool_debut = false;
							}}}}}
						}
					}
				}
			}
		}
		//si aucun mx:pref, $template ne change pas, 0 affectations
		//sinon, coller le dernier morceau
		if (!$bool_debut){
			$resultat .= substr($template,$i_final+1);
			$template = $resultat;
		}

		/*
		while (list($key, $value) = @each($tab_config['images'])){
		if (@constant('PROFIL_IMAGE_PATH') != '') $base_path = constant('PROFIL_IMAGE_PATH');
		elseif (@constant('DEFAULT_PROFIL_IMGPATH') != '') $base_path = constant('DEFAULT_PROFIL_IMGPATH');
		else $base_path = '';
		$path = $base_path.$value;

		if (! @is_file($path)) $path = $base_path.constant('DEFAULT_IMAGE_FILE');

		$template = str_replace('mXprefSrc['.$key.']', $path, $template);
		}
		*/

		$resultat = '';
		$lng = strlen($template);
		$bool_debut = true;
		if (@constant('CST_ENV_IMG') != '') $base_path = constant('CST_ENV_IMG');
		elseif (@constant('DEFAULT_PROFIL_IMGPATH') != '') $base_path = constant('DEFAULT_PROFIL_IMGPATH');
		else $base_path = '';

		for ($i = 0; $i <= $lng - 8 ; $i++){
			if ($template{$i} == 'm') {
				if ( $template{$i + 1} == 'X') {
					if ( $template{$i + 2} == 'p') {
						if ( $template{$i + 3} == 'r') {
							if ( $template{$i + 4} == 'e') {
								if ( $template{$i + 5} == 'f') {
									if ( $template{$i + 6} == 'S') {
										if ( $template{$i + 7} == 'r') {
											if ( $template{$i + 8} == 'c') {
												$i_initial = $i;
												while ( $template{$i} != '[' ){
													$i++;
												}
												$i_signature = $i;
												$signature = '';
												while ( !($template{$i + 1} == ']') ){
													$signature .= $template{$i + 1};
													$i++;
												}
												$i++;
												$i_final_precedent = $i_final;
												$i_final = $i;
												if ($bool_debut ) {
													$resultat = substr($template, 0, ($i_initial)).$base_path.$tab_config['images'][$signature];
												} else {
													$resultat .= substr($template, $i_final_precedent + 1, ($i_initial-$i_final_precedent - 1)).$base_path.$tab_config['images'][$signature];
												}
												$bool_debut = false;
							}}}}}
						}
					}
				}
			}
		}
		//si aucun mx:pref, $template ne change pas, 0 affectations
		//sinon, coller le dernier morceau
		if (!$bool_debut){
			$resultat .= substr($template,$i_final+1);
			$template = $resultat;
		}

		/*
		while (list($key, $value) = @each($tab_config['attributs'])){
		$template = str_replace('mXprefAtt['.$key.']', $value, $template);
		}
		*/

		$resultat = '';
		$lng = strlen($template);
		$bool_debut = true;

		for ($i = 0; $i <= $lng - 8 ; $i++){
			if ($template{$i} == 'm') {
				if ( $template{$i + 1} == 'X') {
					if ( $template{$i + 2} == 'p') {
						if ( $template{$i + 3} == 'r') {
							if ( $template{$i + 4} == 'e') {
								if ( $template{$i + 5} == 'f') {
									if ( $template{$i + 6} == 'A') {
										if ( $template{$i + 7} == 't') {
											if ( $template{$i + 8} == 't') {
												$i_initial = $i;
												while ( $template{$i} != '[' ){
													$i++;
												}
												$i_signature = $i;
												$signature = '';
												while ( !($template{$i + 1} == ']') ){
													$signature .= $template{$i + 1};
													$i++;
												}
												$i++;
												$i_final_precedent = $i_final;
												$i_final = $i;
												if ($bool_debut ) {
													$resultat = substr($template, 0, ($i_initial)).$tab_config['attributs'][$signature];
												} else {
													$resultat .= substr($template, $i_final_precedent + 1, ($i_initial-$i_final_precedent-1)).$tab_config['attributs'][$signature];;
												}
												$bool_debut = false;
							}}}}}
						}
					}
				}
			}
		}
		//si aucun mx:pref, $template ne change pas, 0 affectations
		//sinon, coller le dernier morceau
		if (!$bool_debut){
			$resultat .= substr($template, $i_final + 1);
			$template = $resultat;
		}

		return $template;
	}

	//MX Builder-----------------------------------------------------------------------------------------

	// Renvoie vrai si un bloc existe
	function isBlock($index){

		if ($this -> adressSystem == 'relative') {
			if ($index) $index = $this -> relativePath.'.'.$index;
			else $index = $this -> relativePath;
		}
		else $index = $this -> absolutePath.'.'.$index;

		$fat = $this -> father[$index];
		if (! $fat && $index != $this -> absolutePath) return false;
		return true;
	}


	// Opérations sur les blocs
	function MxBloc($index, $mod, $value = '', $bool_path = false, $bool_debug = false){
		if ($this -> MxEnd) return false;
		$mod = substr(strtolower($mod), 0, 4);

		if ($this -> adressSystem == 'relative') {
			if ($index) $index = $this -> relativePath.'.'.$index;
			else $index = $this -> relativePath;
		}
		else $index = $this -> absolutePath.'.'.$index;

		$fat = $this -> father[$index];
		if (! $fat && $index != $this -> absolutePath) $this -> ErrorTracker(2, 'The current path (<b>'.$index.'</b>) does not exist, or was deleted, him or his father, before.', 'MxBloc', __FILE__, __LINE__);

		if ($bool_path) $str_path = $value;
		else $str_path = $this -> mXTemplatePath.$value;

		switch ($mod){
			//Looping
			case 'loop':
			$this -> MxLoopBuilder($index, $bool_debug);
			break;
			//Deleting
			case 'dele':
			$this -> sheetBuilding[$index] = '   ';
			$this -> loop[$index] = '';
			$this -> deleted[$index] = true;
			break;
			//Concatenating
			case 'appe':
			if (@is_file($str_path)) $value = $this -> GetMxFile($str_path);
			elseif ($bool_path) $this -> ErrorTracker(3, 'You are specifying explictly a path (<b>'.$str_path.'</b>) for a template what does not exist.', 'MxBloc', __FILE__, __LINE__);
			$this -> templateContent[$index] .= $value;
			$this -> MxParsing($value, $index, $this -> father[$index]);
			break;
			//Replacing
			case 'repl':
			if (@is_file($str_path)) $value = $this -> GetMxFile($str_path);
			elseif ($bool_path) $this -> ErrorTracker(3, 'You are specifying explictly a path (<b>'.$str_path.'</b>) for a template what does not exist.', 'MxBloc', __FILE__, __LINE__);
			$this -> sheetBuilding[$index] = $value;
			$this -> replacement[$index] = true;
			break;
			//Modify template references of this bloc
			case 'modi':
			$this -> sheetBuilding[$index] = '';
			$this -> loop[$index] = '';
			if (@is_file($str_path)) $value = $this -> GetMxFile($str_path);
			elseif ($bool_path) $this -> ErrorTracker(3, 'You are specifying explictly a path (<b>'.$str_path.'</b>) for a template what does not exist.', 'MxBloc', __FILE__, __LINE__);
			$this -> templateContent[$index] = $value;
			$this -> MxParsing($value, $index, $this -> father[$index]);
			break;
			//Reset, destroy all references
			case 'rese':
			$this -> sheetBuilding[$index] = '';
			$this -> loop[$index] = '';
			$this -> templateContent[$index] = '';
			$ind = substr($index, strrpos($index, '.') + 1);
			$this -> templateContent[$fat] = str_replace('<mx:inclusion id="'.$ind.'"/>', '', $this -> templateContent[$fat]);
			$this -> deleted[$index] = true;
			$this -> xPattern['inclusion'][$index] = '';
			break;
		}
	}

	// Ajout de champs de formulaire
	function MxFormField($index, $type, $name = '', $value = '', $attribut = ''){
		if ($this -> MxEnd) return false;
		if ($this -> adressSystem == 'relative') $index = $this -> relativePath.'.'.$index;
		$end = $this -> outputSystem;

		// On escape les doubles quotes
		$value = $this -> MxEscapeQuote($value);
		$value = trim($value);

		switch (strtolower(trim($type))){
			//[ADD & MOD Théo 10/06/2013]
			case 'date':
			case 'datetime':
			case 'email':
			case 'month':
			case 'number':
			case 'tel':
			case 'time':
			case 'url':
			case 'text':
			$replace = '<input type="'.$type.'" name="'.$name.'" id="'.$name.'" value="'.$value.'" '.$attribut.' '.$this -> htmlAtt[$index].$end;
			break;
			case 'password':
			$replace = '<input type="password" name="'.$name.'" id="'.$name.'" value="'.$value.'" '.$attribut.' '.$this -> htmlAtt[$index].$end;
			break;
			case 'textarea':
			$replace = '<textarea name="'.$name.'" id="'.$name.'" '.$attribut.' '.$this -> htmlAtt[$index].' wrap="virtual">'.$value.'</textarea>';
			break;
			case 'file':
			$replace = '<input type="file" name="'.$name.'" id="'.$name.'" value="'.$value.'" '.$attribut.' '.$this -> htmlAtt[$index].$end;
			break;
			case 'submit':
			$replace = '<input type="submit" name="'.$name.'" id="'.$name.'" value="'.$value.'" '.$attribut.' '.$this -> htmlAtt[$index].$end;
			break;
			case 'reset':
			$replace = '<input type="reset" name="'.$name.'" id="'.$name.'" value="'.$value.'" '.$attribut.' '.$this -> htmlAtt[$index].$end;
			break;
			case 'button':
			$replace = '<input type="button" name="'.$name.'"  id="'.$name.'"value="'.$value.'" '.$attribut.' '.$this -> htmlAtt[$index].$end;
			break;
			case 'image':
			$replace = '<input type="image" name="'.$name.'" id="'.$name.'" '.$attribut.' '.$this -> htmlAtt[$index].$end;
			break;
			case 'delete':
			$replace = '';
			break;
			default:
			$this -> ErrorTracker(3, 'This type (<b>'.$type.'</b>) is unknown for this formField manager.', 'MxFormField', __FILE__, __LINE__);
		}

		$this -> formField[$index] = $replace;

		return $replace;
	}

	// Ajout d'images
	function MxImage($index, $imag, $title = '', $attribut = '', $size = false){
		if ($this -> MxEnd) return false;
		if ($this -> adressSystem == 'relative') $index = $this -> relativePath.'.'.$index;

		if (strtolower($imag) == 'delete'){
			$this -> image[$index] = '&nbsp;';
			return true;
		}

		$end = $this -> outputSystem;

		if (($ima = '<img src="'.$imag.'"') && ! $size) {
			$size = @getimagesize($imag);
			$ima .= ' '.$size[3];
		}

		if ($title == 'no') $ima .= ' ';
		elseif ($title) $ima .= ' alt="'.$title.'" title="'.$title.'" ';
		else $ima .= ''; //' alt="no title - source : '.basename($imag).'" ';

		if ($attribut) $ima .= $attribut;
		$ima .= ' '.$this -> htmlAtt[$index].$end;

		$this -> image[$index] = $ima;

		return $ima;
	}

	// Ajout de texte simple
	function MxText($index, $att){
		$att = trim($att);
		if ($this -> MxEnd) return false;
		if ($this -> adressSystem == 'relative') $index = $this -> relativePath.'.'.$index;

		$this -> text[$index] = $att;

		return $att;
	}

	// Ajout d'attributs
	function MxAttribut($index, $att){
		if ($this -> MxEnd) return false;
		if ($this -> adressSystem == 'relative') $index = $this -> relativePath.'.'.$index;
		$marqueur = '';

		//Gestion des mailto et des javascripts dans les href
		if (strtolower($this -> attributKey[$index]) == 'mailto' || strtolower($this -> attributKey[$index]) == 'javascript') $marqueur = ' href="';


		//Gestion multi-attributs
		if (! ((isset($this -> attribut[$index]))? trim($this -> attribut[$index]) : false) ) {

			// Suppression de l'attribut
			if ($att == 'delete') {
				$this -> attribut[$index] = '';
				return true;
			}

			if ($marqueur) $this -> attribut[$index] = $marqueur.$this -> attributKey[$index].':'.$att.'"';
			else $this -> attribut[$index] = $this -> attributKey[$index].'="'.$att.'"';
		}
		else {

			// Suppression de l'attribut
			if ($att == 'delete') {

				//[MOD Théo 26/07/2017]
				if (empty($this -> attribut[$this -> attribut[$index]])) {
					if ($marqueur) $this -> attribut[$this -> attribut[$index]] = ' ';
					else $this -> attribut[$this -> attribut[$index]] = ' ';
				}
				else {
					if ($marqueur) $this -> attribut[$this -> attribut[$index]] .= ' ';
					else $this -> attribut[$this -> attribut[$index]] .= ' ';
				}

				return true;
			}

			if (empty($this -> attribut[$this -> attribut[$index]])) {
				if ($marqueur) $this -> attribut[$this -> attribut[$index]] = ' '.$marqueur.$this -> attributKey[$index].':'.$att.'"';
				else $this -> attribut[$this -> attribut[$index]] = ' '.$this -> attributKey[$index].'="'.$att.'"';
			}
			else {
				if ($marqueur) $this -> attribut[$this -> attribut[$index]] .= $marqueur.$this -> attributKey[$index].':'.$att.'"';
				else $this -> attribut[$this -> attribut[$index]] .= ' '.$this -> attributKey[$index].'="'.$att.'"';
			}
		}
	}

	// Construction des select
	function MxSelect($index, $name, $value = '', $arrayArg = '', $defaut = '', $multiple = '', $javascript = '', $tab_options = '') {
		if ($this -> MxEnd) return false;
		if ($this -> adressSystem == 'relative') $index = $this -> relativePath.'.'.$index;
		$sel = '';

		if ($name == 'delete') {
			$this -> select[$index] = '';
			return true;
		}

		if ($multiple && $multiple > 0) {
			$attribut = 'size="'.$multiple.'" multiple="multiple" ';
			$post = '[]';
		}
		else {
			$attribut = '';
			$post = '';
		}

		//Build of a select tag from an array
		if (is_array($arrayArg)){
			$sel = "\r\n".'<select name="'.$name.$post.'" id="'.$name.$post.'" ';
			if ($attribut) $sel .= $attribut.' ';
			if ($javascript) $sel .= $javascript;
			$sel .= ' '.$this -> htmlAtt[$index].' '.">\r\n";

			if (isset($defaut) && $defaut) {
				if ($value == '#') $add = ' selected="selected" ';
				$sel .= "\t".'<option value="#" '.$add.'>'.$defaut.'</option>'."\r\n";
			}

			$add = '';
			$debut = 0;
			$fin = count($arrayArg);

			reset($arrayArg);
			while (list($cle, $Avalue) = each($arrayArg)){
				$test = 0;
				$str_option = '';

				//Build of multiple choice select from a value array
				if (is_array($value) && $multiple > 0){
					reset($value);
					while (list($Vcle, $Vvalue) = each($value)){

						if ((string)$cle === (string)$Vvalue && (string)$Vvalue !== '') {
							$sel .= "\t".'<option value="'.$this -> MxEscapeQuote($cle).'" selected="selected" '.$str_option.'>'.$Avalue.'</option>'."\r\n";
							$test = 1;
							break;
						}
					}
					if ($test == 0) $sel .= "\t".'<option value="'.$this -> MxEscapeQuote($cle).'" '.$str_option.'>'.$Avalue.'</option>'."\r\n";
				}

				//Simple select
				else {
					if (isset($tab_options[$cle])) $str_option = $tab_options[$cle];

					if ((string)$value !== '' && (string)$cle === (string)$value) $sel .= "\t".'<option value="'.$this -> MxEscapeQuote($cle).'" selected="selected" '.$str_option.'>'.$Avalue.'</option>'."\r\n";
					else $sel .= "\t".'<option value="'.$this -> MxEscapeQuote($cle).'" '.$str_option.'>'.$Avalue.'</option>'."\r\n";
				}
			}
		}
		else {
			$this -> ErrorTracker(2, 'This function needs an Array as fourth argument to build the select <b>'.$index.'</b>.', 'MxSelect', __FILE__, __LINE__);
			$sel = '<select name="'.$name.'" id="'.$name.'" '.$this -> htmlAtt[$index].' >'."\n\t"; //.'<option value="null">No record found</option>'."\r\n";
		}

		$sel .= '</select>'."\r\n";

		$this -> select[$index] = $sel;

		return $sel;
	}

	function MxUrl($index, $urlArg, $param = '', $noSid = false, $attribut = '') {
		if ($this -> MxEnd) return false;
		if ($this -> adressSystem == 'relative') $index = $this -> relativePath.'.'.$index;

		$ok = false;

		//Ajout des param&eacute;tres de sessions en cas de cache ou non
		if ($this -> sessionParameter) {
			$urlArg .= '?'.$this -> sessionParameter;
			$ok = true;
		}

		//Construction du lien
		if (is_string($param) && $param && ! @is_array($param)) {
			$param = explode('&',$param);
			for($i = 0; $i < count($param) && $param[$i]; $i++){
				$cle = explode('=', $param[$i]);
				$urlArg .= ($i == 0 && !$ok) ? '?'.urlencode($cle[0]).'='.urlencode($cle[1]) : '&'.urlencode($cle[0]).'='.urlencode($cle[1]);
			}
		}
		elseif (is_array($param)){
			reset($param);

			while (list($cle, $valeur) = each($param)) {
				if (!$ok) {
					$urlArg .= '?'.urlencode($cle).'='.urlencode($valeur);
					$ok = true;
				}
				else $urlArg .= '&'.urlencode($cle).'='.urlencode($valeur);
			}
		}
		elseif ($param) $this -> ErrorTracker(3, 'The third argument must be a queryString or an array.', 'MxUrl', __FILE__, __LINE__);

		//Ajout d'+ventuels attributs suppl+mentaires en dynamique
		$lien = ($attribut)? ' href="'.$urlArg.'" '.$attribut : ' href="'.$urlArg.'"';

		//Gestion multi-attributs
		if (! ((isset($this -> attribut[$index]))? chop($this -> attribut[$index]): false)) $this -> attribut[$index] = ' href="'.$urlArg.'"';
		else {
			if (empty($this -> attribut[$this -> attribut[$index]])) $this -> attribut[$this -> attribut[$index]] = ' href="'.$urlArg.'"';
			else $this -> attribut[$this -> attribut[$index]] .= ' href="'.$urlArg.'"';
		}
	}

	function MxHidden ($index, $param, $bool_html = false){
		if ($this -> MxEnd) return false;
		if ($this -> adressSystem == 'relative') $index = $this -> relativePath.'.'.$index;
		$end = $this -> outputSystem;

		$hidden = '';

		if ($this -> mXCacheDelay == 0 && $this -> sessionParameter) $param .= '&'.$this -> sessionParameter;
		if (! $bool_html) $param = caliHtmlReverse($param);

		if (is_string($param)) $param = explode('&', $param);
		else $this -> ErrorTracker(3, 'The second argument must be a queryString.',  'MxHidden', __FILE__, __LINE__);

		if (! empty($param)){
			for($i = 0; $i < count($param); $i++){
				if ($param[$i]) {
					$int_pos = strpos($param[$i], '=');
					$str_cle = substr($param[$i], 0, $int_pos);
					$str_value = substr($param[$i], $int_pos + 1);
					$hidden .= '<input type="hidden" name="'.$str_cle.'" id="'.$str_cle.'"  value="'.$this -> MxUnescapeEt($this -> MxEscapeQuote($str_value)).'" '.$end."\r\n";
				}
			}
		}

		$this -> hidden[$index] = $hidden;
	}


	function MxHiddenPile($index, $str_name, $str_value, $bool_session = true){
		if ($this -> MxEnd) return false;
		if ($this -> adressSystem == 'relative') $index = $this -> relativePath.'.'.$index;
		$end = $this -> outputSystem;
		$param = explode('&', $this -> sessionParameter);

		if ($this -> sessionParameter && ! $this -> hidden[$index] && $bool_session) {
			// Integration des parametres de session au premier appel
			for($i = 0; $i < count($param); $i++){
				if ($param[$i]) {
					$int_pos = strpos($param[$i], '=');
					$str_cle = substr($param[$i], 0, $int_pos);
					$str_value = substr($param[$i], $int_pos + 1);
					$this -> hidden[$index] .= '<input type="hidden" name="'.$str_cle.'" id="'.$str_cle.'"  value="'.$this -> MxUnescapeEt($this -> MxEscapeQuote($str_value)).'" '.$end."\r\n";
				}
			}
		}

		// Je rajoute les parametres que si ils ne sont pas parametres de sessions.
		//if (! in_array($str_name.'='.$str_value, $param))
		$this -> hidden[$index] .= '<input type="hidden" name="'.$str_name.'" id="'.$str_name.'"  value="'.$this -> MxEscapeQuote($str_value).'" '.$end."\r\n";
	}

	function MxCheckerField($index, $type, $name = '', $value = '', $checked = false, $attribut = ''){
		if ($this -> MxEnd) return false;
		if ($this -> adressSystem == 'relative') $index = $this -> relativePath.'.'.$index;
		$end = $this -> outputSystem;

		$type = strtolower($type);
		if ($type != 'checkbox' && $type != 'radio' && $type != 'delete') $this -> ErrorTracker(2, 'This type (<b>'.$type.'</b>) is unknown for this CheckerField manager.', 'MxCheckerField', __FILE__, __LINE__);

		$replace = '<input type="'.$type.'" name="'.$name.'" id="'.$name.'" value="'.addslashes($value).'"';
		if ($checked) $replace .= ' checked="checked"';
		if ($attribut) $replace .= ' '.$attribut;
		$replace .= ' '.$this -> htmlAtt[$index].$end;

		if ($type != 'delete') $this -> checker[$index] = $replace;
		else $this -> checker[$index] = '';
	}

	//Construction d'une queryString
	function GetQueryString($keyString, $null = 1){
		$queryString = array();

		if (is_array($keyString)){
			reset($keyString);

			while (list($Akey, $value) = each($keyString)){
				if (is_array($value)) {
					while (list($k, $v) = each($value)) {
						array_push($queryString, urlencode($Akey.'['.$k.']').'='.urlencode($v));
					}
				}
				elseif ($null || strlen($value)) array_push($queryString, urlencode($Akey).'='.urlencode($value));
			}
			return implode('&',$queryString);
		}
		else $this -> ErrorTracker(3, 'The argument for this function must be an associative array.', 'GetQueryString', __FILE__, __LINE__);
	}

	//Adressage simplifi+
	function WithMxPath($path = '', $origine = ''){
		if ($this -> MxEnd) return false;
		if (! $origine) $origine = $this -> adressSystem;
		else {
			switch($origine){
				case 'relative':
				break;
				case 'absolute':
				break;
				default:
				$origine = 'relative';
				break;
			}
		}

		//Si on ne pr+cise pas de path on retourne au path origine
		if (empty($path)){
			$this -> relativePath = $this -> absolutePath;

			if ($origine == 'absolute') $this -> adressSystem = 'absolute';
			if ($origine == 'relative') $this -> adressSystem = 'relative';
		}

		//Sinon, en absolu on se situe dans ce path, en relatif on se situe par rapport au path relatif
		if ($path) {
			if ($origine == 'relative') {

				//On redescend dans la hi+rarchie jusqu'au path mentionn+
				if (($test = explode('../', $path)) && count($test) > 1) {
					$path = substr($path, strrpos($path, '/') + 1);
					$this -> relativePath = substr($this -> relativePath, 0, strlen($this -> relativePath) - strlen(strstr($this -> relativePath, $path)) - 1);
					if (! $this -> relativePath) $this -> ErrorTracker(3, 'This path (<b>'.$path.'</b>) does not exist, ModeliXe can\'t build relativePath.', 'WithMxPath', __FILE__, __LINE__);
				}

				$this -> relativePath .= '.'.$path;

				$this -> adressSystem = 'relative';
			}
			elseif ($origine == 'absolute') {
				$this -> relativePath = $path;
				$this -> adressSystem = 'absolute';
			}
		}
	}

	//MX Parsing Engine------------------------------------------------------------------------------------------------------------

	function MxParsing($doc = '', $path = '', $father = ''){
		$countPath = Array();

		//Initialisation
		if (! $path) {
			$original = true;
			$path = $this -> absolutePath;
		}
		else $original = false;

		$this -> father[$path] = $father;
		$this -> IsALoop[$path] = false;

		//Parsing des balises de bloc, extraction des sous blocs
		$ok = true;

		switch ($this -> flagSystem){
			case 'xml':
			$blocRegexp = '/<mx:bloc(?:[ ]+ref="([^"]+)")?[ ]+id="([^"]+)"[ ]*>/S';
			break;
			case 'classical':
			$blocRegexp = '/{start(?:[ ]+ref="([^"]+)")?[ ]+id="([^"]+)"[ ]*}/S';
			break;
		}

		if (preg_match_all($blocRegexp, $doc, $inclusion)){

			for($i = 0; $ok; $i++){

				//Extraction des diff+rentes informations extraites par la regex
				$id = $inclusion[2][0];
				$ref = $inclusion[1][0];
				$pattern = $inclusion[0][0];

				//Calcul des limites du bloc trait+
				switch ($this -> flagSystem){
					case 'xml':
					$regexp = '</mx:bloc id="'.$id.'">';
					break;
					case 'classical':
					$regexp = '{end id="'.$id.'"}';
					break;
				}

				$startOfIntrons = strpos($doc, $pattern) + strlen($pattern);
				$endOfIntrons = strpos($doc, $regexp);
				$length = $endOfIntrons - $startOfIntrons;

				if (! is_integer($endOfIntrons) || $endOfIntrons === false) $this -> ErrorTracker(5, 'The end of the "<b>'.$id.'</b>" bloc is not found, this bloc can\'t be generate.<br>Verify that the end of bloc\'s flag exists and has a good form, like this pattern <b>'.htmlentities($regexp).'</b>.', 'MxParsing', __FILE__, __LINE__);

				//On teste si le bloc en cours possede une reference vers un autre template
				if (! $ref) $this -> templateContent[$path.'.'.$id] = substr($doc, $startOfIntrons, $length);
				else {
					if ($this -> mXTemplatePath) $ref = $this -> mXTemplatePath.$ref;
					$this -> templateContent[$path.'.'.$id] = $this -> GetMxFile($ref);
				}

				//Creation du pattern du bloc traite
				$this -> xPattern['inclusion'][$path.'.'.$id] = '<mx:inclusion id="'.$id.'"/>';
				$this -> deleted[$path.'.'.$id] = false;
				$this -> replacement[$path.'.'.$id] = false;

				//Extraction du contenu du bloc pour reconstruire le bloc en cours
				$doc = substr($doc, 0, $startOfIntrons - strlen($pattern)).'<mx:inclusion id="'.$id.'"/>'.substr($doc, $endOfIntrons + strlen($regexp));
				$this -> templateContent[$path] = $doc;

				//Construction de la reference a ce bloc pour la recursivite
				$countPath[$i] = $path.'.'.$id;

				//Incrementation du nbre de fils pour le bloc en cours
				if (! empty($this -> son[$path][0])) $compt = $this -> son[$path][0];
				else {
					$compt = 0;
					$this -> son[$path][0] = 0;
				}

				//Construction de la reference au fils du bloc parse pour le bloc en cours
				$this -> son[$path][++ $compt] = $path.'.'.$id;
				$this -> son[$path][0] ++;

				//Test de fin de boucle
				$ok = preg_match_all($blocRegexp, $doc, $inclusion);
			}
		}

		//Parsing des balises ModeliXe
		reset($this -> flagArray);
		while (list($Akey, $value) = each($this -> flagArray)){

			switch ($this -> flagSystem){
				case 'xml':
				$regexp = '/<mx:'.$value.'(?:[ ]+(?:ref|info)="(?:[^"]+)")?[ ]+id="([^"]+)"(([^>])*(?=\/>))\/>/S';
				break;
				case 'classical':
				$regexp = '/{'.$value.'(?:[ ]+(?:ref|info)="(?:[^"]+)")?[ ]+id="([^"]+)"[ ]*(?i:htmlAtt\[([^\]]*)\])?}/S';
				break;
			}

			if (preg_match_all($regexp, $doc, $flag)){
				for ($i = 0; ; $i++){
					if (empty ($flag[0][$i])) break;

					//Construction du pattern et des valeurs par d+faut de ces balises
					$this -> xPattern[$value][$path.'.'.$flag[1][$i]] = $flag[0][$i];

					$ref = &$this -> $value;
					$ref[$path.'.'.$flag[1][$i]] = '   ';
					$this -> htmlAtt[$path.'.'.$flag[1][$i]] = $flag[2][$i];
				}
			}
		}

		//Parsing des attributs de ModeliXe
		switch ($this -> flagSystem){
			case 'xml':
			$regexp = '/mXattribut="([^"]{3,})"/Si';
			$separateur = ':';
			break;
			case 'classical':
			$regexp = '/{attribut ([^\}]+)}/Si';
			$separateur = '=';
			break;
		}

		if (preg_match_all($regexp, $doc, $flag)){
			for ($i = 0, $k = 0; ; $i++){

				if (empty($flag[0][$i])) break;

				$pattern = $flag[0][$i];
				$motif = $flag[1][$i];
				$k = 0;

				//Gestion de plusieurs couples de cl+-valeurs dans les attributs
				$tabVal = explode(';', $motif);
				for ($j = 0; $j < count($tabVal); $j++) {

					$tabCle = explode($separateur, trim($tabVal[$j]));
					$patternKey[++ $k] = trim($tabCle[0]);
					$indexValue[$k] = trim($tabCle[1]);

					//Gestion multi-attributs
					if (count($tabVal) > 1) {
						$this -> attribut[$path.'.'.$indexValue[$k]] = $path.'.'.$indexValue[1].';';
						if ($k == 1) $this -> xPattern['attribut'][$path.'.'.$indexValue[1].';'] = $pattern;
					}
					else {
						$this -> attribut[$path.'.'.$indexValue[$k]] = '  ';
						$this -> xPattern['attribut'][$path.'.'.$indexValue[$k]] = $pattern;
					}

					if ($patternKey[$k] != 'url') $this -> attributKey[$path.'.'.$indexValue[$k]] = $patternKey[$k];
				}
			}
		}


		for ($i = 0; $i < count($countPath); $i++) $this -> MxParsing($this -> templateContent[$countPath[$i]], $countPath[$i], $path);
	}

	//MX Template Fusion Engine --------------------------------------------------------------------------------------------------

	//Remplace le contenu des templates passes en arguments
	function MxReplace($path){

		if (! empty($this -> sheetBuilding[$path])) $cible = $this -> sheetBuilding[$path];
		else $cible = $this -> templateContent[$path];

		//Remplacement de l'ensemble des attributs ModeliXe par les valeurs qui ont ete instanci+es ou leurs valeurs par defaut
		reset($this -> attributArray);
		while (list($cle, $Fkey) = each($this -> attributArray)){
			$Farray = &$this -> $Fkey;

			if (is_array($Farray)){
				reset($Farray);

				while (list($Pkey, $value) = each($Farray)){

					if ($path == substr($Pkey, 0, strrpos($Pkey, '.'))) {
						if (isset($this -> xPattern[$Fkey][$Pkey])){
							$pattern = $this -> xPattern[$Fkey][$Pkey];
							$cible = str_replace($pattern, $value, $cible);
							unset($Farray[$Pkey]);
						}
					}
				}
			}
		}

		//Remplacement de l'ensemble des balises ModeliXe par les valeurs qui ont ete instanciees ou leurs valeurs par defaut
		reset($this -> flagArray);
		while (list($cle, $Fkey) = each($this -> flagArray)){
			$Farray = &$this -> $Fkey;

			if (is_array($Farray)){
				reset($Farray);

				while (list($Pkey, $value) = each($Farray)){
					if ($path == substr($Pkey, 0, strrpos($Pkey, '.'))) {
						if (isset($this -> xPattern[$Fkey][$Pkey])){
							$pattern = $this -> xPattern[$Fkey][$Pkey];
							$cible = str_replace($pattern, $value, $cible);
							unset($Farray[$Pkey]);
						}
					}
				}
			}
		}

		return $cible;
	}

	//Construit les blocs et associe les blocs fils aux blocs parents
	function MxBlocBuilder($path = '', $bool_debug = false){

		$ordre = array();
		$hierarchie = 1;

		if (! $path) $path = $this -> absolutePath;
		$chemin = $path;

		//Classement de tout les fils de path du plus proche au plus lointain
		$base = count(explode('.', $path));
		$k = 1;
		$l = 1;
		$j = 1;

		for (; ;){

			//Si il existe un fils on le prend
			if (! empty($this -> son[$chemin][$j])) $fils = $this -> son[$chemin][$j];
			else $fils = '';

			//Si il existe on considere le dernier enregistrement trouve precedant celui-ci
			if (! empty($ordre[$hierarchie])) $ancien = $ordre[$hierarchie][count($ordre[$hierarchie])];
			else $ancien = false;

			// Si les deux blocs sont identiques on s'arrête là (??? bug potentiel ???)
			//if ($fils === $ancien && ! $ancien) break;

			//Si il n'y a plus de fils, on passe au noeud suivant
			if (empty($fils)) {
				$j = 1;

				if (! empty($ordre[$k][$l])) {
					$chemin = $ordre[$k][$l];
					$l ++;
				}
				else {
					$l = 1;
					$k ++;

					if (! empty($ordre[$k][$l])) $chemin = $ordre[$k][$l ++];
					else break;
				}
			}
			else {
				$j ++;

				//Si le fils n'a pas ete detruit on le consid+re
				if ($this -> templateContent[$fils]) {

					//hierarchie compte le nombre de blocs a partir du bloc de base
					$hierarchie = count(explode('.', $fils)) - $base;

					if (empty($ordre[$hierarchie])) $ordre[$hierarchie] = array();
					$ordre[$hierarchie][count($ordre[$hierarchie]) + 1] = $fils;
				}
			}
		}

		// Débuggage
		if ($bool_debug) {
			print(__LINE__." -- DEBUG MODELIXE -- Tableau des ordres des blocs -- <br>\r\n");
			print_r($ordre);
			print("\r\n<br>\r\n");
		}

		//Insertion des fils les plus lointains dans les fils les plus proches jusqu'au path
		for ($i = count($ordre); $i > 0; $i --){

			for ($j = 1; $j <= count($ordre[$i]); $j++){

				$fils = $ordre[$i][$j];
				$pattern = $this -> xPattern['inclusion'][$fils];
				$pere = $this -> father[$ordre[$i][$j]];

				//Insertion du bloc fils dans le pere
				if ($pere == $path && $this -> IsALoop[$path]) {

					if ($this -> IsALoop[$fils]) {

						if ($this -> deleted[$fils]) {
							$rem = ' ';
							$this -> deleted[$fils] = false;
						}
						else $rem = $this -> loop[$fils];

						$this -> loop[$pere] = str_replace($pattern, $rem, $this -> loop[$pere]);
						$this -> loop[$fils] = '';
					}
					else {

						if ($this -> deleted[$fils]) {
							$rem = ' ';
							$this -> deleted[$fils] = false;
						}
						else $rem = $this -> MxReplace($fils);

						$this -> loop[$pere] = str_replace($pattern, $rem, $this -> loop[$pere]);
						$this -> sheetBuilding[$fils] = '';
					}
				}
				else {

					if (! empty($this -> sheetBuilding[$pere])) $source = $this -> sheetBuilding[$pere];
					else $source = $this -> templateContent[$pere];

					if ($this -> IsALoop[$fils]) {

						if ($this -> deleted[$fils]) {
							$rem = ' ';
							$this -> deleted[$fils] = false;
						}
						else $rem = $this -> loop[$fils];

						$this -> sheetBuilding[$pere] = str_replace($pattern, $rem, $source);
						$this -> loop[$fils] = '';
					}
					else {

						if ($this -> deleted[$fils]) {
							$rem = ' ';
							$this -> deleted[$fils] = false;
						}
						else $rem = $this -> MxReplace($fils);

						$this -> sheetBuilding[$pere] = str_replace($pattern, $rem, $source);
						$this -> sheetBuilding[$fils] = '';
					}
				}

			}
		}

	}

	//Associe les boucles
	function MxLoopBuilder($path = '', $bool_debug = false){
		if (! $path) $path = $this -> absolutePath;

		$father = $this -> father[$path];
		$pattern = $this -> xPattern['inclusion'][$path];

		//On saute les blocs detruits
		if ($pattern){
			$this -> IsALoop[$path] = true;
			if (empty($this -> loop[$path])) $this -> loop[$path] = '';

			//Gestion des blocs remplaces temporairement
			if ($this -> replacement[$path]) {
				$this -> loop[$path] .= $this -> MxReplace($path);
				$this -> replacement[$path] = false;
				$this -> sheetBuilding[$path] = '';
			}

			//Gestion des boucles classiques
			else {
				$this -> sheetBuilding[$path] = '';
				if (empty($this -> loop[$path])) $this -> loop[$path] = '';
				$this -> loop[$path] .= $this -> MxReplace($path);
			}
		}

		// Débuggage
		if ($bool_debug) print(__LINE__." -- DEBUG MODELIXE -- path = $path<br>\r\nloop content = <xmp>".$this -> loop[$path]."</xmp> <br>\r\n");

		//Insertion des fils de $path dans $path
		$this -> MxBlocBuilder($path, $bool_debug);
	}

	//Mx Output -------------------------------------------------------------------------------------------------------------------

	//Sortie du fichier HTML genere
	function MxWrite ($out = ''){

		if ($this -> MxEnd) return false;
		if (! $this -> mXsetting) $this -> ErrorTracker(5, 'You did not initialize ModeliXe with the setModeliXe method, there is no data to write.', 'MxWrite', __FILE__, __LINE__);

		//Assemblage de l'ensemble des blocs fils
		$this -> MxBlocBuilder();

		if ($this -> mXsignature) $entete = '<!--[ModeliXe '.$this -> mXVersion.'] -- '.(($this -> isTemplateFile)? '[TemplateFile : '.$this -> mXTemplatePath.$this -> template.']' : '[Template : '.$this -> template.']').' -- [date '.date('j/m/Y H:i:s')."]-->\n";
		else $entete = '';

		if ($this -> ErrorChecker()) {
			$filecontent = (($entete)? str_replace('<head>', '<head>'."\n".$entete,$filecontent = $this -> MxReplace($this -> absolutePath)) : $filecontent = $this -> MxReplace($this -> absolutePath));

			//Remplacement des balises de param&eacute;tres
			if ($this -> mXParameterFile) $filecontent = $this -> GetParameterParsing($filecontent);

			// Chronometrage
			if ($this -> mXChrono) $filecontent = str_replace('<mx:chrono />', caliChrono().' - '.$this -> mXChronoInfo, $filecontent);

			global $GLOBAL_HTML_SIZE;
			$GLOBAL_HTML_SIZE = strlen($filecontent);

			if ($this -> mXoutput || $out) return $filecontent;
			else echo $filecontent;
		}
	}
}
?>