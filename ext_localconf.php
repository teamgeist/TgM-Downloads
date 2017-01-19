<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'TGM.' . $_EXTKEY,
	'Main',
	array(
		'Download' => 'list, download',
		
	),
	// non-cacheable actions
	array(
		'Download' => 'download',
		
	)
);

if(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('ke_search')){
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['registerIndexerConfiguration'][] = TGM\TgmDownloads\Utility\Indexer\KeSearchDownloadIndexer::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['customIndexer'][] = TGM\TgmDownloads\Utility\Indexer\KeSearchDownloadIndexer::class;
    $GLOBALS['TCA']['tx_kesearch_indexerconfig']['columns']['sysfolder']['displayCond'] .= ',tgmdownloadindexer';
}
