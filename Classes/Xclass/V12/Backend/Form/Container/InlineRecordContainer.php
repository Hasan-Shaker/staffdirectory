<?php

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

namespace Causal\Staffdirectory\Xclass\V12\Backend\Form\Container;

use TYPO3\CMS\Backend\Form\Exception\AccessDeniedContentEditException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Extends \TYPO3\CMS\Backend\Form\Container\InlineRecordContainer to allow changing the
 * color of the whole inline record block color.
 *
 * @category    XCLASS
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   2017-2026 Causal Sàrl
 * @license     https://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class InlineRecordContainer extends \TYPO3\CMS\Backend\Form\Container\InlineRecordContainer
{
    /**
     * Entry method
     *
     * @return array As defined in initializeResultArray() of AbstractNode
     * @throws AccessDeniedContentEditException
     */
    public function render(): array
    {
        $data = $this->data;
        $this->inlineData = $data['inlineData'];

        $inlineStackProcessor = $this->inlineStackProcessor;
        $inlineStackProcessor->initializeByGivenStructure($data['inlineStructure']);

        $record = $data['databaseRow'];
        $inlineConfig = $data['inlineParentConfig'];
        $foreignTable = $inlineConfig['foreign_table'];

        $resultArray = $this->initializeResultArray();

        // Send a mapping information to the browser via JSON:
        // e.g. data[<curTable>][<curId>][<curField>] => data-<pid>-<parentTable>-<parentId>-<parentField>-<curTable>-<curId>-<curField>
        $formPrefix = $inlineStackProcessor->getCurrentStructureFormPrefix();
        $domObjectId = $inlineStackProcessor->getCurrentStructureDomObjectIdPrefix($data['inlineFirstPid']);
        $this->inlineData['map'][$formPrefix] = $domObjectId;

        $resultArray['inlineData'] = $this->inlineData;

        // Get the current naming scheme for DOM name/id attributes:
        $appendFormFieldNames = '[' . $foreignTable . '][' . ($record['uid'] ?? 0) . ']';
        $objectId = $domObjectId . '-' . $foreignTable . '-' . ($record['uid'] ?? 0);
        $classes = [];
        $html = '';
        $combinationHtml = '';
        $isNewRecord = $data['command'] === 'new';
        $hiddenField = '';
        if (isset($data['processedTca']['ctrl']['enablecolumns']['disabled'])) {
            $hiddenField = $data['processedTca']['ctrl']['enablecolumns']['disabled'];
        }
        if (!$data['isInlineDefaultLanguageRecordInLocalizedParentContext']) {
            if ($isNewRecord || $data['isInlineChildExpanded']) {
                // Render full content ONLY IF this is an AJAX request, a new record, or the record is not collapsed
                $combinationHtml = '';
                if (isset($data['combinationChild'])) {
                    $combinationChild = $this->renderCombinationChild($data, $appendFormFieldNames);
                    $combinationHtml = $combinationChild['html'];
                    $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $combinationChild, false);
                }
                $childArray = $this->renderChild($data);
                $html = $childArray['html'];
                $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $childArray, false);
            } else {
                // This class is the marker for the JS-function to check if the full content has already been loaded
                $classes[] = 't3js-not-loaded';
            }
            if ($isNewRecord) {
                // Add pid of record as hidden field
                $html .= '<input type="hidden" name="data' . htmlspecialchars($appendFormFieldNames)
                    . '[pid]" value="' . htmlspecialchars((string)$record['pid']) . '"/>';
                // Tell DataHandler this record is expanded
                $ucFieldName = 'uc[inlineView]'
                    . '[' . $data['inlineTopMostParentTableName'] . ']'
                    . '[' . $data['inlineTopMostParentUid'] . ']'
                    . $appendFormFieldNames;
                $html .= '<input type="hidden" name="' . htmlspecialchars($ucFieldName)
                    . '" value="' . (int)$data['isInlineChildExpanded'] . '" />';
            } else {
                // Set additional field for processing for saving
                $html .= '<input type="hidden" name="cmd' . htmlspecialchars($appendFormFieldNames)
                    . '[delete]" value="1" disabled="disabled" />';
                if (!empty($hiddenField) && (!$data['isInlineChildExpanded'] || !in_array($hiddenField, $data['columnsToProcess'], true))) {
                    $checked = empty($record[$hiddenField]) ? '' : ' checked="checked"';
                    $html .= '<input type="checkbox" class="d-none" data-formengine-input-name="data'
                        . htmlspecialchars($appendFormFieldNames)
                        . '[' . htmlspecialchars((string)$hiddenField) . ']" value="1"' . $checked . ' />';
                    $html .= '<input type="input" class="d-none" name="data' . htmlspecialchars($appendFormFieldNames)
                        . '[' . htmlspecialchars((string)$hiddenField) . ']" value="' . htmlspecialchars((string)$record[$hiddenField]) . '" />';
                }
            }
            // If this record should be shown collapsed
            $classes[] = $data['isInlineChildExpanded'] ? 'panel-visible' : 'panel-collapsed';
        }
        $hiddenFieldHtml = implode("\n", $resultArray['additionalHiddenFields'] ?? []);

        if ($inlineConfig['renderFieldsOnly'] ?? false) {
            // Render "body" part only
            $html .= $hiddenFieldHtml . $combinationHtml;
        } else {
            // Render header row and content (if expanded)
            if ($data['isInlineDefaultLanguageRecordInLocalizedParentContext']) {
                $classes[] = 't3-form-field-container-inline-placeHolder';
            }
            if (!empty($hiddenField) && isset($record[$hiddenField]) && (int)$record[$hiddenField]) {
                $classes[] = 't3-form-field-container-inline-hidden';
            }
            if ($isNewRecord) {
                $classes[] = 'inlineIsNewRecord';
            }

            $originalUniqueValue = '';
            if (isset($record['uid'], $data['inlineData']['unique'][$domObjectId . '-' . $foreignTable]['used'][$record['uid']])) {
                $uniqueValueValues = $data['inlineData']['unique'][$domObjectId . '-' . $foreignTable]['used'][$record['uid']];
                // in case of site_language we don't have the full form engine options, so fallbacks need to be taken into account
                $originalUniqueValue = ($uniqueValueValues['table'] ?? $foreignTable) . '_';
                // @todo In what circumstance would $uniqueValueValues be an array that lacks a 'uid' key? Unclear, but
                // it breaks the string concatenation. This is a hacky workaround for type safety only.
                $uVV = ($uniqueValueValues['uid'] ?? $uniqueValueValues);
                if (is_array($uVV)) {
                    $uVV = implode(',', $uVV);
                }
                $originalUniqueValue .= $uVV;
            }

            // The hashed object id needs a non-numeric prefix, the value is used as ID selector in JavaScript
            $hashedObjectId = 'hash-' . md5($objectId);
            $containerAttributes = [
                'id' => $objectId . '_div',
                'class' => 'form-irre-object panel panel-default panel-condensed ' . trim(implode(' ', $classes)),
                'data-object-uid' => $record['uid'] ?? 0,
                'data-object-id' => $objectId,
                'data-object-id-hash' => $hashedObjectId,
                'data-object-parent-group' => $domObjectId . '-' . $foreignTable,
                'data-field-name' => $appendFormFieldNames,
                'data-topmost-parent-table' => $data['inlineTopMostParentTableName'],
                'data-topmost-parent-uid' => $data['inlineTopMostParentUid'],
                'data-table-unique-original-value' => $originalUniqueValue,
                'data-placeholder-record' => $data['isInlineDefaultLanguageRecordInLocalizedParentContext'] ? '1' : '0',
            ];

            $ariaExpanded = ($data['isInlineChildExpanded'] ?? false) ? 'true' : 'false';
            $ariaControls = htmlspecialchars($objectId . '_fields', ENT_QUOTES | ENT_HTML5);
            $ariaAttributesString = 'aria-expanded="' . $ariaExpanded . '" aria-controls="' . $ariaControls . '"';

            // XCLASS [begin]
            $parameters = [
                'tableName' => $data['tableName'],
                'row' => $data['databaseRow'],
                'style' => '',
            ];
            // callUserFunction requires a third parameter, but we don't want to give $this as reference!
            $null = null;
            if (!empty($data['processedTca']['ctrl']['irreHeaderStyle_userFunc'])) {
                GeneralUtility::callUserFunction($data['processedTca']['ctrl']['irreHeaderStyle_userFunc'], $parameters, $null);
            }

            $html = '
                <div ' . GeneralUtility::implodeAttributes($containerAttributes, true) . '>
                    <div class="panel-heading" data-bs-toggle="formengine-inline" id="' . htmlspecialchars($hashedObjectId, ENT_QUOTES | ENT_HTML5) . '_header" data-expandSingle="' . (($inlineConfig['appearance']['expandSingle'] ?? false) ? 1 : 0) . '">
                        <div class="form-irre-header" style="' . trim((string)$parameters['style']) . '">
                            <div class="form-irre-header-cell form-irre-header-icon">
                                <span class="caret"></span>
                            </div>
                            ' . $this->renderForeignRecordHeader($data, $ariaAttributesString) . '
                        </div>
                    </div>
                    <div class="panel-collapse" id="' . $ariaControls . '">' . $html . $hiddenFieldHtml . $combinationHtml . '</div>
                </div>';
            // XCLASS [end]
        }

        $resultArray['html'] = $html;
        return $resultArray;
    }
}
