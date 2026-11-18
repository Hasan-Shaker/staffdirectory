<?php

$typo3Version = (new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion();
$tca = [
    'ctrl' => [
        'title' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tx_staffdirectory_domain_model_organization',
        'label' => 'long_name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'default_sortby' => 'ORDER BY long_name',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'long_name, short_name',
        'iconfile' => 'EXT:staffdirectory/Resources/Public/Icons/tx_staffdirectory_domain_model_organization.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => '
                    long_name, short_name, path_segment, description,
                --div--;LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tabs.members,
                    members,
                --div--;LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tabs.hierarchy,
                    parent_organizations, suborganizations,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    sys_language_uid, l10n_parent, l10n_diffsource,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                    categories,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    hidden, starttime, endtime',
        ],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'language',
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'group',
                'allowed' => 'tx_staffdirectory_domain_model_organization',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'hidden' => [
            'exclude' => false,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
            ],
        ],
        'categories' => [
            'config' => [
                'type' => 'category',
            ],
        ],
        'long_name' => [
            'exclude' => false,
            'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tx_staffdirectory_domain_model_organization.long_name',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '255',
                'eval' => $typo3Version >= 12 ? 'trim' : 'required,trim',
                'required' => true,
            ],
        ],
        'short_name' => [
            'exclude' => false,
            'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tx_staffdirectory_domain_model_organization.short_name',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '50',
                'eval' => $typo3Version >= 12 ? 'trim' : 'required,trim',
                'required' => true,
            ],
        ],
        'path_segment' => [
            'exclude' => false,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:pages.slug',
            'config' => [
                'type' => 'slug',
                'size' => 50,
                'generatorOptions' => [
                    'fields' => ['long_name'],
                    'fieldSeparator' => '/',
                ],
                'fallbackCharacter' => '-',
                'eval' => 'unique',
                'default' => '',
            ],
        ],
        'description' => [
            'exclude' => false,
            'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tx_staffdirectory_domain_model_organization.description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => 1,
                'richtextConfiguration' => 'default',
                'cols' => 30,
                'rows' => 5,
                'softref' => 'typolink_tag,images,email[subst],url',
            ],
        ],
        'members' => [
            'exclude' => false,
            'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tx_staffdirectory_domain_model_organization.members',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_staffdirectory_domain_model_member',
                'foreign_field' => 'organization',
                'foreign_sortby' => 'sorting',
                'maxitems' => 999,
                'appearance' => [
                    'collapse' => 0,
                    'useSortable' => 1,
                    'newRecordLinkPosition' => 'both',
                ],
            ],
        ],
        'parent_organizations' => [
            'exclude' => false,
            'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tx_staffdirectory_domain_model_organization.parent_organizations',
            'config' => [
                'type' => 'none',
                'renderType' => 'staffdirectoryParentOrganizations',
            ],
        ],
        'suborganizations' => [
            'exclude' => false,
            'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tx_staffdirectory_domain_model_organization.suborganizations',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_staffdirectory_domain_model_organization',
                'foreign_table_where' => 'AND tx_staffdirectory_domain_model_organization.uid<>###THIS_UID### AND tx_staffdirectory_domain_model_organization.pid=###CURRENT_PID### AND tx_staffdirectory_domain_model_organization.sys_language_uid IN (0,-1) ORDER BY tx_staffdirectory_domain_model_organization.long_name',
                'size' => 12,
                'minitems' => 0,
                'maxitems' => 30,
            ],
        ],
    ],
];

if ($typo3Version >= 12) {
    unset($tca['ctrl']['cruser_id']);
}

return $tca;
