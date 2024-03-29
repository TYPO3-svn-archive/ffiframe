<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// define fields to display in content element form
t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,pages,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

// add plugin to the TCA
t3lib_extMgm::addPlugin(Array('LLL:EXT:ffiframe/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

// add flexform definition
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:ffiframe/flexform_ds_pi1.xml');			

if (TYPO3_MODE=='BE')	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_ffiframe_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_ffiframe_pi1_wizicon.php';
?>