<?php

$typo3Version = (new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion();
$tca = [
    'ctrl' => [
        'title' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tx_staffdirectory_domain_model_member',
        'label' => 'feuser_id',
        'label_userFunc' => \Causal\Staffdirectory\Backend\Tca\Member::class . '->getLabel',
        'irreHeaderStyle_userFunc' => \Causal\Staffdirectory\Backend\Tca\Member::class . '->getIrreHeaderStyle',
        'hideTable' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'default_sortby' => 'ORDER BY crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:staffdirectory/Resources/Public/Icons/tx_staffdirectory_domain_model_member.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => '
                    organization, feuser_id, position_function,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    hidden, starttime, endtime',
        ],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
        'hidden' => [
            'exclude' => false,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
            ],
        ],
        'organization' => [
            'exclude' => false,
            'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tx_staffdirectory_domain_model_member.organization',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => $typo3Version >= 12
                    ? [
                        [
                            'label' => '',
                            'value' => 0,
                        ],
                    ]
                    : [
                        ['', 0],
                    ],
                'foreign_table' => 'tx_staffdirectory_domain_model_organization',
                'foreign_table_where' => 'AND tx_staffdirectory_domain_model_organization.pid=###CURRENT_PID### AND tx_staffdirectory_domain_model_organization.sys_language_uid IN (0,-1) ORDER BY tx_staffdirectory_domain_model_organization.long_name',
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'feuser_id' => [
            'exclude' => false,
            'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tx_staffdirectory_domain_model_member.feuser_id',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users',
                'foreign_table' => 'fe_users', // MANDATORY for Extbase
                'foreign_table_where' => 'AND fe_users.tx_extbase_type=\'tx_staffdirectory\'',
                'size' => 1,
                'autoSizeMax' => 1,
                'maxitems' => 1,
                'multiple' => 0,
                'suggestOptions' => [
                    'default' => [
                        'receiverClass' => \Causal\Staffdirectory\Backend\Form\Wizard\MemberSuggestReceiver::class,
                        'maxItemsInResultList' => 20,
                    ],
                ],
            ],
        ],
        'position_function' => [
            'exclude' => false,
            'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tx_staffdirectory_domain_model_member.position_function',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'eval' => 'trim',
            ],
        ],
        'sorting' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
];

if ($typo3Version >= 12) {
    unset($tca['ctrl']['cruser_id']);
    unset($tca['columns']['feuser_id']['config']['internal_type']);
}

return $tca;
