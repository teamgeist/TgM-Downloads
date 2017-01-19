<?php
namespace TGM\TgmDownloads\Utility\Indexer;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2017 Oliver Pfaff <op@teamgeist-medien.de>, Teamgeist Medien GbR
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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




class KeSearchDownloadIndexer {

	/**
	 * Adds the custom indexer to the TCA of indexer configurations, so that
	 * it's selectable in the backend as an indexer type when you create a
	 * new indexer configuration.
	 *
	 * @param array $params
	 * @param type $pObj
	 */
	function registerIndexerConfiguration(&$params, $pObj) {

			// add item to "type" field
		$newArray = array(
            'TgM Download indexer',
            'tgmdownloadindexer',
            ExtensionManagementUtility::extRelPath('tgm_downloads') . 'ext_icon.png'
        );
		$params['items'][] = $newArray;
	}

	/**
	 * Custom indexer for ke_search
	 *
	 * @param   array $indexerConfig Configuration from TYPO3 Backend
	 * @param   array $indexerObject Reference to indexer class.
	 * @return  string Output.
	 * @author  Christian Buelter <buelter@kennziffer.com>
	 * @since   Fri Jan 07 2011 16:01:51 GMT+0100
	 */
	public function customIndexer(&$indexerConfig, &$indexerObject) {
		if($indexerConfig['type'] == 'tgmdownloadindexer') {
		    $pids = $indexerConfig['sysfolder'];
		    if($indexerConfig['startingpoints_recursive']){
                /** @var \TYPO3\CMS\Core\Database\QueryGenerator $queryGenerator */
                $queryGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\QueryGenerator::class);
                $rGetTreeList = $queryGenerator->getTreeList(224, 99, 0, 1); //Will be a string
                $pids = $rGetTreeList;
            }
			$content = '';
			// get all the entries to index
			// don't index hidden or deleted elements, BUT
			// get the elements with frontend user group access restrictions
			// or time (start / stop) restrictions.
			// Copy those restrictions to the index.
			$fields = '*';
			$table = 'tx_tgmdownloads_domain_model_download';
			$where = 'pid IN (' . $pids . ') AND hidden = 0 AND deleted = 0';
			$groupBy = '';
			$orderBy = '';
			$limit = '';
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$groupBy,$orderBy,$limit);
			$resCount = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
			//DebuggerUtility::var_dump($indexerConfig);

				// Loop through the records and write them to the index.
			if($resCount) {
				while ( ($record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) ) {
					// compile the information which should go into the index
					// the field names depend on the table you want to index!
					$title = strip_tags($record['title']);
					$abstract = '';
					$content = '';
					$fullContent = $title . "\n" . $abstract . "\n" . $content;
					$params = '';
					$tags = 'Download';
					$additionalFields = array(
						'sortdate' => $record['crdate'],
						'orig_uid' => $record['uid'],
						'orig_pid' => $record['pid'],
						'sortdate' => $record['datetime'],
					);

						// add something to the title, just to identify the entries
						// in the frontend
					$title = '[Downloadcenter] ' . $title;

						// ... and store the information in the index
					$indexerObject->storeInIndex(
							$indexerConfig['storagepid'],   // storage PID
							$title,                         // record title
							'download',                  	// content type
							$indexerConfig['targetpid'],    // target PID: where is the single view?
							$fullContent,                   // indexed content, includes the title (linebreak after title)
							$tags,                          // tags for faceted search
							$params,                        // typolink params for singleview
							$abstract,                      // abstract; shown in result list if not empty
							$record['sys_language_uid'],    // language uid
							$record['starttime'],           // starttime
							$record['endtime'],             // endtime
							$record['fe_group'],            // fe_group
							false,                          // debug only?
							$additionalFields               // additionalFields
							);
				}
				$content = '<p><b>Indexer "' . $indexerConfig['title'] . '": ' . $resCount . ' Elements have been indexed.</b></p>';
			}
			return $content;
		}
	}
}
?>