<?php

$tmp_columns = array(
	'identifiers' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:easylogin2/Resources/Private/Language/locallang_db.xlf:tx_easylogin2_domain_model_identifiers',
		'config' => array(
			'type' => 'inline',
			'foreign_table' => 'tx_easylogin2_domain_model_identifiers',
			'foreign_field' => 'user',
			'maxitems' => 20,
			'appearance' => array(
				'collapseAll' => 1,
				'levelLinksPosition' => 'top',
				'showSynchronizationLink' => 0,
				'showPossibleLocalizationRecords' => 0,
				'showAllLocalizationLink' => 0
			),
		),
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tmp_columns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'identifiers');