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
