<?php

defined('TYPO3') || die();

// Create a dedicated type of records to ease the management of those fe_users records in the context of this staffs

$GLOBALS['TCA']['fe_users']['columns']['tx_extbase_type']['config']['items'][] = [
    'label' => 'Staffdirectory Member',
    'value' => 'tx_staffdirectory',
];

$GLOBALS['TCA']['fe_users']['columns']['name']['exclude'] = false;
$GLOBALS['TCA']['fe_users']['columns']['name']['config']['readOnly'] = true;

$GLOBALS['TCA']['fe_users']['types']['tx_staffdirectory'] = [
    'showitem' => '
            --div--;LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tabs.personalData,
                tx_staffdirectory_gender,
                --palette--;;sd_name,
                path_segment,
                --palette--;;sd_contact,
                --palette--;;sd_address,
                image,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                disable,
                --palette--;;timeRestriction,
                --palette--;LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:palettes.gdpr;gdpr,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                username, password, usergroup, tx_extbase_type
        ',
];
$GLOBALS['TCA']['fe_users']['palettes'] += [
    'sd_name' => [
        'showitem' => '
            title, first_name, last_name,
            --linebreak--,
            name',
    ],
    'sd_contact' => [
        'showitem' => '
            telephone, tx_staffdirectory_mobilephone,
            --linebreak--,
            email, tx_staffdirectory_email2',
    ],
    'sd_address' => [
        'showitem' => '
            address,
            --linebreak--,
            zip, city,
            --linebreak--,
            country',
    ],
    'gdpr' => [
        'showitem' => '
            tx_staffdirectory_gdpr_date,
            --linebreak--,
            tx_staffdirectory_gdpr_proof',
    ],
];

$tempColumns = [
    'tx_staffdirectory_mobilephone' => [
        'exclude' => false,
        'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:fe_users.tx_staffdirectory_mobilephone',
        'config' => [
            'type' => 'input',
            'size' => 20,
            'max' => 20,
            'eval' => 'trim',
        ],
    ],
    'tx_staffdirectory_gender' => [
        'exclude' => false,
        'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:fe_users.tx_staffdirectory_gender',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [
                    'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:fe_users.tx_staffdirectory_gender.I.0',
                    'value' => '0',
                ],
                [
                    'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:fe_users.tx_staffdirectory_gender.I.1',
                    'value' => '1',
                ],
            ],
            'size' => 1,
            'maxitems' => 1,
        ],
    ],
    'tx_staffdirectory_email2' => [
        'exclude' => true,
        'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:fe_users.tx_staffdirectory_email2',
        'config' => [
            'type' => 'email',
        ],
    ],
    'tx_staffdirectory_gdpr_date' => [
        'exclude' => false,
        'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:fe_users.tx_staffdirectory_gdpr_date',
        'config' => [
            'type' => 'datetime',
            'format' => 'date',
        ],
    ],
    'tx_staffdirectory_gdpr_proof' => [
        'exclude' => false,
        'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:fe_users.tx_staffdirectory_gdpr_proof',
        'config' => [
            'type' => 'text',
            'cols' => 30,
            'rows' => 10,
        ],
    ],
    'path_segment' => [
        'exclude' => false,
        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:pages.slug',
        'config' => [
            'type' => 'slug',
            'size' => 50,
            'generatorOptions' => [
                'fields' => ['name'],
                'fieldSeparator' => '/',
            ],
            'fallbackCharacter' => '-',
            'eval' => 'uniqueInSite',
            'default' => '',
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns);

// Configure the field country to be an actual usable country
$GLOBALS['TCA']['fe_users']['columns']['country']['config'] = [
    'type' => 'select',
    'renderType' => 'selectSingle',
    'items' => [
        [
            'label' => '',
            'value' => '',
        ],
    ],
    'itemsProcFunc' => \Causal\Staffdirectory\Backend\Tca\Country::class . '->getAll',
    'size' => 1,
    'minitems' => 0,
    'maxitems' => 1,
];

// Configure the field image to support cropping
/**
 * @see \TYPO3\CMS\Backend\Form\Element\ImageManipulationElement::$defaultConfig
 */
$photoCropVariants = [
    'default' => [
        'title' => 'LLL:EXT:core/Resources/Private/Language/locallang_wizards.xlf:imwizard.crop_variant.default',
        'allowedAspectRatios' => [
            '1:1' => [
                'title' => 'LLL:EXT:core/Resources/Private/Language/locallang_wizards.xlf:imwizard.ratio.1_1',
                'value' => 1.0,
            ],
            'NaN' => [
                'title' => 'LLL:EXT:core/Resources/Private/Language/locallang_wizards.xlf:imwizard.ratio.free',
                'value' => 0.0,
            ],
        ],
        'selectedRatio' => '1:1',
        'cropArea' => [
            'x' => 0.0,
            'y' => 0.0,
            'width' => 1.0,
            'height' => 1.0,
        ],
    ],
];

$GLOBALS['TCA']['fe_users']['columns']['image']['config'] = [
    'type' => 'file',
    'allowed' => 'jpeg,jpg,webp',
    'maxitems' => 1,
    'minitems' => 0,
    'overrideChildTca' => [
        'columns' => [
            'crop' => [
                'config' => [
                    'cropVariants' => $photoCropVariants,
                ],
            ],
        ],
    ],
];

$GLOBALS['TCA']['fe_users']['ctrl']['label'] = 'last_name';
// BEWARE: "title" and GDPR fields are needed for label_userFunc in the context of FlexForm
$GLOBALS['TCA']['fe_users']['ctrl']['label_alt'] = 'first_name, title, tx_staffdirectory_gdpr_date, tx_staffdirectory_gdpr_proof';
$GLOBALS['TCA']['fe_users']['ctrl']['label_alt_force'] = 1;
$GLOBALS['TCA']['fe_users']['ctrl']['label_userFunc'] = \Causal\Staffdirectory\Backend\Tca\FeUser::class . '->getLabel';
$GLOBALS['TCA']['fe_users']['ctrl']['default_sortby'] = 'ORDER BY last_name, first_name';
