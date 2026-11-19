<?php
use Causal\Staffdirectory\Backend\Form\Element\ParentOrganizations;
use Causal\Staffdirectory\Hooks\DataHandler;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use Causal\Staffdirectory\Controller\PluginController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Backend\Form\Container\InlineRecordContainer;

defined('TYPO3') || die ();

(static function (string $_EXTKEY): void {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1701340648] = [
        'nodeName' => 'staffdirectoryParentOrganizations',
        'priority' => 70,
        'class' => ParentOrganizations::class,
    ];

    // Register hooks for \TYPO3\CMS\Core\DataHandling\DataHandler
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
        DataHandler::class;

    ExtensionUtility::configurePlugin(
        $_EXTKEY,
        'Plugin',
        [
            PluginController::class => implode(',', [
                'dispatch',
                'list',
                'organization',
                'person',
                'persons',
                'directory',
            ]),
        ],
        [],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    $typo3Version = GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion();

    // As of TYPO3 v13, the TSconfig is loaded automatically from
    // the extension's Configuration/page.tsconfig file.
    if ($typo3Version === 12) {
        ExtensionManagementUtility::addPageTSConfig('
            @import \'EXT:staffdirectory/Configuration/TSconfig/__loader_v12.tsconfig\'
        ');
    }

    if ($typo3Version >= 14) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][InlineRecordContainer::class] = [
            'className' => \Causal\Staffdirectory\Xclass\V14\Backend\Form\Container\InlineRecordContainer::class,
        ];
    } elseif ($typo3Version === 13) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][InlineRecordContainer::class] = [
            'className' => \Causal\Staffdirectory\Xclass\V13\Backend\Form\Container\InlineRecordContainer::class,
        ];
    } else {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][InlineRecordContainer::class] = [
            'className' => \Causal\Staffdirectory\Xclass\V12\Backend\Form\Container\InlineRecordContainer::class,
        ];
    }
})('staffdirectory');
