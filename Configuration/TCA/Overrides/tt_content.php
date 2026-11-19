<?php

defined('TYPO3') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin([
    'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:plugins.plugin.title',
    'staffdirectory_plugin',
    'staffdirectory-default',
], 'CType', 'staffdirectory');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:staffdirectory/Configuration/FlexForms/flexform_plugin_v12.xml',
    'staffdirectory_plugin'
);

$GLOBALS['TCA']['tt_content']['types']['staffdirectory_plugin']['showitem'] = '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;headers,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.plugin,
            pi_flexform, pages, recursive,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
            --palette--;;frames,
            --palette--;;appearanceLinks,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
            --palette--;;language,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
            categories,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
            rowDescription,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
    ';

$GLOBALS['TCA']['tt_content']['types']['staffdirectory_plugin']['columnsOverrides'] = [
    'layout' => [
        'config' => [
            'items' => [
                [
                    'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tt_content.layout.0',
                    'value' => 0,
                ],
                [
                    'label' => 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tt_content.layout.1',
                    'value' >= 1,
                ],
            ],
        ],
    ],
];

/**
 * Configure a custom preview renderer for the plugins
 * @see https://docs.typo3.org/m/typo3/reference-coreapi/11.5/en-us/ApiOverview/ContentElements/CustomBackendPreview.html
 */
$GLOBALS['TCA']['tt_content']['types']['staffdirectory_plugin']['previewRenderer']
    = \Causal\Staffdirectory\Preview\PluginPreviewRenderer::class;
