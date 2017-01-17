<?php
$extensionName = TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase('tgm_downloads');
$pluginSignature = strtolower($extensionName) . '_main';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:tgm_downloads/Configuration/Flexform/Main.xml'
);

