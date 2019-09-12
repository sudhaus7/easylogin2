<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'DIX.' . $_EXTKEY,
	'Easylogin',
	array(
		'Dixeasylogin' => 'index, providerselected, verify',
		
	),
	// non-cacheable actions
	array(
		'Dixeasylogin' => 'index, providerselected, verify',
		
	)
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
	$_EXTKEY,
	'auth' /* sv type */,
	'DIX\\Easylogin2\\Service\\AuthenticationService' /* sv key */,
	array(
		'title' => 'Authentication service',
		'description' => 'Authentication service for easylogin2',
		'subtype' => 'getUserFE,authUserFE',
		'available' => true,
		'priority' => 80,
		'quality' => 80,
		'os' => '',
		'exec' => '',
		'className' => 'DIX\\Easylogin2\\Service\\AuthenticationService',
	)
);

// http://resterland.ch/intern/sitenews/news/detail/News/request-parameters-could-not-be-validated-chash-comparison-failed.html
$GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError'] = 0;

// adding default realurl configuration (like in the realurl manual).
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration'][] = 'EXT:easylogin2/Classes/Helper/Realurl.php:tx_easylogin2_realurl->autoconfgen';




