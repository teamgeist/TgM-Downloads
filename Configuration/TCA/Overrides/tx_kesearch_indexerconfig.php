<?php
if(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('ke_search')){
    $GLOBALS['TCA']['tx_kesearch_indexerconfig']['columns']['sysfolder']['displayCond'] .= ',tgmdownloadindexer';
    $GLOBALS['TCA']['tx_kesearch_indexerconfig']['columns']['startingpoints_recursive']['displayCond'] .= ',tgmdownloadindexer';
}