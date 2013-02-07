<?php

class IsotopeCategorizeBackend extends IsotopeBackend {

	public function xlabelSortProductsView($objDC) {
		return sprintf(
			' <a href="contao/main.php?do=iso_products&amp;table=tl_iso_product_categories&amp;id=%s" title="%s" onclick="window.open(this.href); return false;">%s</a>',
			$objDC->id,
			$GLOBALS['TL_LANG']['tl_page']['bbit_iso_categorize_xlabel'],
			$this->generateImage(
				'system/modules/isotope/html/store-open.png',
				$GLOBALS['TL_LANG']['tl_page']['bbit_iso_categorize_xlabel']
			)
		);
	}
	
	protected function aggregateProductCategories($intProductID) {
		$arrCategories = $this->Database->prepare(
			'SELECT	page_id
			FROM	tl_iso_product_categories
			WHERE	pid = ?'
		)->execute($intProductID)->fetchEach('page_id');
		$this->Database->prepare(
			'UPDATE	tl_iso_products
			SET		pages = ?
			WHERE	id = ?'
		)->execute(serialize($arrCategories), $intProductID);
	}

	public function loadCategoryProducts($varValue, $objDC) {
		return $this->Database->prepare(
			'SELECT	pid
			FROM	tl_iso_product_categories
			WHERE	page_id = ?
			ORDER BY sorting'
		)->executeUncached($objDC->id)->fetchEach('pid');
	}
	
	public function saveCategoryProducts($varValue, $objDC) {
		$varValue = array_filter(deserialize($varValue, true));
		
		if($varValue) {
			$intTime = time();
			
			$arrCurrent = $this->Database->prepare(
				'SELECT	pid
				FROM	tl_iso_product_categories
				WHERE	page_id = ?'
			)->execute($objDC->id)->fetchEach('pid');
			
			$arrRemove = array_diff($arrCurrent, $varValue);
			$arrInsert = array_diff($varValue, $arrCurrent);
			
			if($arrRemove) {
				$blnTruncateCache = true;
				$this->Database->prepare(
					'DELETE
					FROM	tl_iso_product_categories
					WHERE	page_id = ?
					AND		pid IN (' . implode(',', $arrRemove) . ')'
				)->execute($objDC->id);
				foreach($arrRemove as $intProductID) {
					$this->aggregateProductCategories($intProductID);
				}
			}
			if($arrInsert) {
				$intSorting = $this->Database->prepare(
					'SELECT	sorting
					FROM	tl_iso_product_categories
					WHERE	page_id = ?
					ORDER BY sorting DESC
					LIMIT 1'
				)->execute($objDC->id)->sorting;
					
				foreach($arrInsert as $intProductID) {
					$blnTruncateCache = true;
					$intSorting += 128;
					$this->Database->prepare(
						'INSERT INTO tl_iso_product_categories %s'
					)->set(array(
						'pid'		=> $intProductID,
						'tstamp'	=> $intTime,
						'page_id'	=> $objDC->id,
						'sorting'	=> $intSorting
					))->execute();
					$this->aggregateProductCategories($intProductID);
				}
			}
		} else {
			$blnTruncateCache = $this->Database->prepare(
				'DELETE
				FROM	tl_iso_product_categories
				WHERE	page_id = ?'
			)->execute($objDC->id)->affectedRows;
		}
		
		$blnTruncateCache && $this->truncateProductCache();
		
		return null;
	}
	
	protected function __construct() {
		parent::__construct();
	}
	
}
