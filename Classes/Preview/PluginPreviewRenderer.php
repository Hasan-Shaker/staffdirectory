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

namespace Causal\Staffdirectory\Preview;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PluginPreviewRenderer extends AbstractFlexFormPreviewRenderer
{
    public const PLUGIN_NAME = 'Plugin';

    protected function renderFlexFormPreviewContent(array $record, array &$out): void
    {
        $languageService = $this->getLanguageService();

        $label = $languageService->sL($this->labelPrefix . 'settings.displayMode');
        $displayMode = $this->getFieldFromFlexForm('settings.displayMode');
        if (empty($displayMode)) {
            $error = $languageService->sL($this->labelPrefix . 'settings.displayMode.errorEmpty');
            $description = $this->showError(htmlspecialchars($error));
        } else {
            $description = $languageService->sL($this->labelPrefix . 'settings.displayMode.' . $displayMode);
        }
        $out[] = $this->addTableRow($label, $description);

        if (in_array($displayMode, ['LIST', 'ORGANIZATION', 'DIRECTORY'])) {
            $label = $languageService->sL($this->labelPrefix . 'settings.organizations');
            $organizations = GeneralUtility::intExplode(',', $this->getFieldFromFlexForm('settings.organizations'), true);
            if ($organizations === []) {
                $info = $languageService->sL($this->labelPrefix . 'settings.organizations.empty');
                $description = $this->showInfo(htmlspecialchars($info));
            } else {
                $description = $this->getOrganizationNames($organizations);
            }
            $out[] = $this->addTableRow($label, $description);
        }

        if (in_array($displayMode, ['PERSON', 'PERSONS'])) {
            $label = $languageService->sL($this->labelPrefix . 'settings.persons');
            $persons = GeneralUtility::intExplode(',', $this->getFieldFromFlexForm('settings.persons'), true);
            if ($persons === []) {
                $info = $languageService->sL($this->labelPrefix . 'settings.persons.empty');
                $description = $this->showInfo(htmlspecialchars($info));
            } else {
                $description = $this->getPersonNames($persons);
            }
            $out[] = $this->addTableRow($label, $description);
        }
    }

    /**
     * @param int[] $organizations
     * @return string
     */
    protected function getOrganizationNames(array $organizations): string
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $table = 'tx_staffdirectory_domain_model_organization';

        $out = [];
        foreach ($organizations as $organizationUid) {
            $row = BackendUtility::getRecord($table, $organizationUid);
            if (empty($row)) {
                continue;
            }
            $size = enum_exists(IconSize::class) ? IconSize::SMALL : Icon::SIZE_SMALL;
            $description = $iconFactory->getIconForRecord($table, $row, $size)->render();
            $description .= ' ' . BackendUtility::getRecordTitle($table, $row);
            $out[] = $description;
        }

        return implode('<br>', $out);
    }

    /**
     * @param int[] $persons
     * @return string
     */
    protected function getPersonNames(array $persons): string
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $table = 'fe_users';

        $out = [];
        foreach ($persons as $personUid) {
            $row = BackendUtility::getRecord($table, $personUid);
            $size = enum_exists(IconSize::class) ? IconSize::SMALL : Icon::SIZE_SMALL;
            $description = $iconFactory->getIconForRecord($table, $row, $size)->render();
            $description .= ' ' . BackendUtility::getRecordTitle($table, $row);
            $out[] = $description;
        }

        return implode('<br>', $out);
    }
}
