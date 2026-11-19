<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\Staffdirectory\Backend\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ParentOrganizations extends AbstractFormElement
{
    /**
     * @return array<string, mixed>
     */
    public function render(): array
    {
        $table = 'tx_staffdirectory_domain_model_organization';
        $resultArray = $this->initializeResultArray();

        $organizationUid = (int)$this->data['databaseRow']['uid'];

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table);
        $statement = $queryBuilder
            ->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->inSet(
                    'suborganizations',
                    $queryBuilder->createNamedParameter($organizationUid, Connection::PARAM_INT)
                )
            )
            ->orderBy('long_name')
            ->executeQuery();

        $html = [];
        $html[] = '<div class="mt-2">';
        $html[] = '<ul class="list-unstyled tx-staffdirectory-organizations">';

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        $count = 0;
        while (($row = $statement->fetchAssociative()) !== false) {
            $html[] = '<li>';
            $html[] = $iconFactory->getIconForRecord($table, $row, enum_exists(IconSize::class) ? IconSize::SMALL : Icon::SIZE_SMALL)->render();

            $name = BackendUtility::getRecordTitle($table, $row);
            $editUrl = (string)$uriBuilder->buildUriFromRoute('record_edit', [
                'edit' => [
                    'tx_staffdirectory_domain_model_organization' => [
                        $row['uid'] => 'edit',
                    ],
                ],
                'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
            ]);
            $html[] = vsprintf('<a href="%s">%s</a>', [
                $editUrl,
                htmlspecialchars($name),
            ]);

            $html[] = '</li>';
            $count++;
        }

        if ($count === 0) {
            $html[] = '<li>';
            $html[] = '<em>' . $GLOBALS['LANG']->sL('LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:tx_staffdirectory_domain_model_organization.parent_organizations.none') . '</em>';
            $html[] = '</li>';
        }

        $html[] = '</ul>';
        $html[] = '</div>';

        $resultArray['html'] = implode("\n", $html);

        $resultArray['requireJsModules'][] = JavaScriptModuleInstruction::create('@causal/staffdirectory/follow-link-checker.js')
            ->invoke('init', [
                'selector' => '.tx-staffdirectory-organizations li a',
            ]);

        return $resultArray;
    }
}
