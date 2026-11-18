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

use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractFlexFormPreviewRenderer extends StandardContentPreviewRenderer
{
    // Self-referential 'abstract' declaration
    public const PLUGIN_NAME = self::PLUGIN_NAME;

    protected $flexFormData;

    protected $labelPrefix;

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $out = [];
        $languageService = $this->getLanguageService();
        $this->labelPrefix = 'LLL:EXT:staffdirectory/Resources/Private/Language/locallang_db.xlf:plugins.' . strtolower((string)static::PLUGIN_NAME) . '.';

        $pluginTitle = $languageService->sL($this->labelPrefix . 'title');
        $out[] = '<strong>' . htmlspecialchars($pluginTitle) . '</strong>';

        $record = $item->getRecord();
        $this->flexFormData = GeneralUtility::xml2array($record['pi_flexform']);
        if (is_array($this->flexFormData)) {
            $out[] = '<table class="table table-sm mt-3 mb-0">';
            $this->renderFlexFormPreviewContent($record, $out);
            $out[] = '</table>';
        }

        return implode(LF, $out);
    }

    /**
     * @param string[] $out
     */
    abstract protected function renderFlexFormPreviewContent(array $record, array &$out): void;

    protected function showError(string $text): string
    {
        $errorPattern = '<span class="badge badge-danger">%s</span>';
        return sprintf($errorPattern, $text);
    }

    protected function showInfo(string $text): string
    {
        $infoPattern = '<span class="badge badge-info">%s</span>';
        return sprintf($infoPattern, $text);
    }

    protected function getFieldFromFlexForm(string $key, string $sheet = 'sDEF'): ?string
    {
        $flexForm = $this->flexFormData;
        if (isset($flexForm['data'])) {
            $flexForm = $flexForm['data'];
            return $flexForm[$sheet]['lDEF'][$key]['vDEF'] ?? null;
        }

        return null;
    }

    protected function addTableRow(string $label, string $content): string
    {
        $out[] = '<tr>';
        $out[] = '<td class="align-top">' . htmlspecialchars($label) . '</td>';
        $out[] = '<td class="align-top" style="font-weight: bold">' . $content . '</td>';
        $out[] = '</tr>';

        return implode(LF, $out);
    }

    protected function describeBoolean(bool $value = true): string
    {
        $key = 'LLL:EXT:beuser/Resources/Private/Language/locallang.xlf:';
        $key .= $value ? 'yes' : 'no';

        return htmlspecialchars($this->getLanguageService()->sL($key));
    }
}
