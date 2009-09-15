<?php
	/***************************************************************
	*  Copyright notice
	*
	*  (c) 2006 Peter Klein (peter@umloud.dk)
	*  All rights reserved
	*
	*  This script is part of the TYPO3 project. The TYPO3 project is
	*  free software; you can redistribute it and/or modify
	*  it under the terms of the GNU General Public License as published by
	*  the Free Software Foundation; either version 2 of the License, or
	*  (at your option) any later version.
	*
	*  The GNU General Public License can be found at
	*  http://www.gnu.org/copyleft/gpl.html.
	*
	*  This script is distributed in the hope that it will be useful,
	*  but WITHOUT ANY WARRANTY; without even the implied warranty of
	*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	*  GNU General Public License for more details.
	*
	*  This copyright notice MUST APPEAR in all copies of the script!
	***************************************************************/

	/**
	* Plugin 'Inline frame' for the 'ffiframe' extension.
	*
	* $Id: class.tx_ffiframe_pi1.php, v 1.0.2 2006/03/18 21:54:48 typo3 Exp $
	*
	* @author Peter Klein (peter@umloud.dk)
	*/

	/**
	* [CLASS/FUNCTION INDEX of SCRIPT]
	*
	*
	*
	*   59: class tx_ffiframe_pi1 extends tslib_pibase
	*   71:     function main($content,$conf)
	*  111:     function getPluginConfig($fields)
	*  127:     function addJSautoResize($id)
	*
	* TOTAL FUNCTIONS: 3
	* (This index is automatically created/updated by the extension "extdeveval")
	*
	*/

	require_once(PATH_tslib.'class.tslib_pibase.php');

	/**
	* plugin 'Inline frame' for the 'ffiframe' extension.
	*
	* @author Peter Klein (peter@umloud.dk)
	* @package TYPO3
	* @subpackage tx_ffiframe
	*/
	class tx_ffiframe_pi1 extends tslib_pibase {
		var $prefixId = 'tx_ffiframe_pi1';					// same as class name
		var $scriptRelPath = 'pi1/class.tx_ffiframe_pi1.php';	// path to this script relative to the extension dir
		var $extKey = 'ffiframe';								// the extension key

		/**
		* Generate html <iframe> tag
		*
		* @param string  content
		* @param array  plugin configuration values
		* @return string  content to display
		*/
		function main($content, $conf) {
			$this->conf = $conf;
			$this->pi_loadLL();
			// initialize field pi_flexform
			 $this->pi_initPIflexForm();

			// Get plugin configuration
			$conf_fields = 'src,getvars,width,height,marginwidth,marginheight,style,class,id,name,frameborder,scrolling,title,longdesc,noiframemessage,autoresize';
			$this->config = $this->getPluginConfig($conf_fields);

				// Add defined GET vars to URL
			$params = array();
			$getVars = explode( ',', $this->config['getvars'] );
			foreach( $getVars as $var ) {
				if ( t3lib_div::_GET( $var ) ) {
					$params[$var] = t3lib_div::removeXSS( t3lib_div::_GET( $var ) );
				}
			}
			if ( strstr( $this->config['src'], '?' ) ) {
				$additionalParams = t3lib_div::implodeArrayForUrl( '',$params );
			}else{
				$additionalParams = '?'.substr( t3lib_div::implodeArrayForUrl( '', $params), 1 );
			}

			// generate iframe tag
			if ($this->config['src']) {
				$content = '<iframe src="'.$this->cObj->getTypoLink_URL($this->config['src']).$additionalParams.'"'.
				(($this->config['width'])?' width="'.$this->config['width'].'"':'').
				(($this->config['height'])?' height="'.$this->config['height'].'"':'').
				(($this->config['marginwidth'])?' marginwidth="'.$this->config['marginwidth'].'"':'').
				(($this->config['marginheight'])?' marginheight="'.$this->config['marginheight'].'"':'').
				(($this->config['style'])?' style="'.$this->config['style'].'"':'').
				(($this->config['class'])?' class="'.$this->config['class'].'"':'').
				(($this->config['id'])?' id="'.$this->config['id'].'"':'').
				(($this->config['name'])?' name="'.$this->config['name'].'"':'').
				' frameborder="'.(($this->config['frameborder'])?'1':'0').'"'.
				(($this->config['scrolling'])?' scrolling="'.$this->config['scrolling'].'"':'').
				(($this->config['title'])?' title="'.htmlspecialchars($this->config['title']).'"':'').
				(($this->config['longdesc'])?' longdesc="'.$this->cObj->getTypoLink_URL($this->config['longdesc']).'"':'').
				'>'.htmlspecialchars($this->config['noiframemessage']).'</iframe>';
				if ($this->config['id'] && $this->config['autoresize']) {
					if (t3lib_div::getFileAbsFileName($this->cObj->getTypoLink_URL($this->config['src']))) {
						$this->addJSautoResize($this->config['id']);
					} else {
						$content = $this->pi_getLL('errormsg.notlocal', 'Error! File not local!');
					}
				}
			} else {
				$content = $this->pi_getLL('errormsg.nourl', 'Error! No URL specified!');
			}
			return $this->pi_wrapInBaseClass($content);
		}

		/**
		* Get config data from Flexform fields or Typoscript properties
		*
		* @param string  comma seperated list of FF fields/TS properties
		* @return array  config array
		*/
		function getPluginConfig($fields) {
			// Plugin configuration can be set by TS or FF with priority on FF
			$fields = explode(',', $fields);
			$config = array();
			while (list(, $field) = each($fields)) {
				$config[$field] = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_'.$field));
				$config[$field] = $config[$field] ? $config[$field] :
				 trim($this->cObj->stdWrap($this->conf[$field], $this->conf[$field.'.']));
			}
			return $config;
		}

		/**
		* Add Javascript Headerdata to page if "autoresize" is enabled
		*
		* @param string  CSS-Id
		* @return void
		*/
		function addJSautoResize($id) {
			if ($GLOBALS['TSFE']->additionalHeaderData[$this->extKey]) {
				// HeaderData already present, so we just add the id.
				$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = preg_replace('|(.*var iframeids = \[)(.*)|si', '\\1"'.$id.'",\\2', $GLOBALS['TSFE']->additionalHeaderData[$this->extKey]);
			} else {
				// Add Autoresize script to header.
				$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = '<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'iframeautoresize.js"></script>
					<script type="text/javascript">var iframeids = ["'.$id.'"];</script>';
				// Add action to the onLoad event handler.
				$GLOBALS['TSFE']->JSeventFuncCalls['onload'][$this->extKey] = 'resizeCaller();';
			}
			return;
		}
	}

	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ffiframe/pi1/class.tx_ffiframe_pi1.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ffiframe/pi1/class.tx_ffiframe_pi1.php']);
	}

?>
