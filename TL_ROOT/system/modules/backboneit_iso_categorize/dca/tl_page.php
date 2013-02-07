<?php

foreach($GLOBALS['TL_DCA']['tl_page']['palettes'] as $strKey => &$strPalette) if($strKey != '__selector__') {
	$strPalette .= ';{bbit_iso_categorize_legend},bbit_iso_categorize';
}

$GLOBALS['TL_DCA']['tl_page']['fields']['bbit_iso_categorize'] = array(
	'label'			=> &$GLOBALS['TL_LANG']['tl_page']['bbit_iso_categorize'],
	'exclude'		=> true,
	'inputType'		=> 'tableLookup',
	'eval'			=> array(
		'doNotSaveEmpty'=> true,
		'foreignTable'	=> 'tl_iso_products',
		'fieldType'		=> 'checkbox',
		'listFields'	=> array('name', 'sku'),
		'searchFields'	=> array('name', 'sku'),
		'sqlWhere'		=> 'pid=0',
		'searchLabel'	=> &$GLOBALS['TL_LANG']['MSC']['searchLabel'],
		'tl_class'		=> 'clr'
	),
	'load_callback'	=> array(
		'bbit_iso_relatt' => array('IsotopeCategorizeBackend', 'loadCategoryProducts'),
	),
	'save_callback'	=> array(
		'bbit_iso_relatt' => array('IsotopeCategorizeBackend', 'saveCategoryProducts'),
	),
	'xlabel'		=> array(
		'bbit_iso_relatt' => array('IsotopeCategorizeBackend', 'xlabelSortProductsView')
	),
);
