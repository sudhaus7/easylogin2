<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'DIX.' . $_EXTKEY,
	'Easylogin',
	'Easylogin'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Easylogin');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_easylogin2_domain_model_identifiers', 'EXT:easylogin2/Resources/Private/Language/locallang_csh_tx_easylogin2_domain_model_identifiers.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_easylogin2_domain_model_identifiers');


