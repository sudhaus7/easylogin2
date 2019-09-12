<?php
return array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:easylogin2/Resources/Private/Language/locallang_db.xlf:tx_easylogin2_domain_model_identifiers',
		'label' => 'connectionname',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'hideTable' => 1,

		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'connectionname,connectiontype,useridentifier,user,',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('easylogin2') . 'Resources/Public/Icons/tx_easylogin2_domain_model_identifiers.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'hidden, connectionname, connectiontype, useridentifier, user',
	),
	'types' => array(
		'1' => array('showitem' => 'hidden;;1, connectionname, connectiontype, useridentifier, user, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, starttime, endtime'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(

		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),

		'connectionname' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:easylogin2/Resources/Private/Language/locallang_db.xlf:tx_easylogin2_domain_model_identifiers.connectionname',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'connectiontype' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:easylogin2/Resources/Private/Language/locallang_db.xlf:tx_easylogin2_domain_model_identifiers.connectiontype',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'useridentifier' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:easylogin2/Resources/Private/Language/locallang_db.xlf:tx_easylogin2_domain_model_identifiers.identifier',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim, required'
			),
		),
		'user' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:easylogin2/Resources/Private/Language/locallang_db.xlf:tx_easylogin2_domain_model_identifiers.user',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'fe_users',
				'renderType' => 'selectMultipleSideBySide',
				'maxitems' => 1,
				'size' => 5,
			),

		),
		
	),
);