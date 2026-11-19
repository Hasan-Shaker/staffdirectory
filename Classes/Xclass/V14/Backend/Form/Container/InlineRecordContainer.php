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

namespace Causal\Staffdirectory\Xclass\V14\Backend\Form\Container;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Extends \TYPO3\CMS\Backend\Form\Container\InlineRecordContainer to allow changing the
 * color of the whole inline record block color.
 *
 * Unlike the V12/V13 variants, this XCLASS does not duplicate the core rendering
 * logic but decorates the rendered HTML instead.
 *
 * @category    XCLASS
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   2025-2026 Causal Sàrl
 * @license     https://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class InlineRecordContainer extends \TYPO3\CMS\Backend\Form\Container\InlineRecordContainer
{
    /**
     * Entry method
     *
     * @return array As defined in initializeResultArray() of AbstractNode
     */
    public function render(): array
    {
        $resultArray = parent::render();

        $userFunc = $this->data['processedTca']['ctrl']['irreHeaderStyle_userFunc'] ?? '';
        if (empty($userFunc) || ($this->data['inlineParentConfig']['renderFieldsOnly'] ?? false)) {
            return $resultArray;
        }

        $parameters = [
            'tableName' => $this->data['tableName'],
            'row' => $this->data['databaseRow'],
            'style' => '',
        ];
        // callUserFunction requires a third parameter, but we don't want to give $this as reference!
        $null = null;
        GeneralUtility::callUserFunction($userFunc, $parameters, $null);

        $style = trim((string)$parameters['style']);
        if ($style !== '') {
            $resultArray['html'] = preg_replace(
                '/<div class="panel-heading"/',
                '<div class="panel-heading" style="' . htmlspecialchars($style) . '"',
                (string)$resultArray['html'],
                1
            );
        }

        return $resultArray;
    }
}
