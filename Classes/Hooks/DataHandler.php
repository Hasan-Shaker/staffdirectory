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

namespace Causal\Staffdirectory\Hooks;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hooks into \TYPO3\CMS\Core\DataHandling\DataHandler.
 *
 * @category    Hooks
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class DataHandler
{
    /**
     * Hooks into \TYPO3\CMS\Core\DataHandling\DataHandler before records get actually saved to the database.
     *
     * @param int|string $id
     * @param array<string, mixed> $fieldArray
     */
    public function processDatamap_postProcessFieldArray(
        string $operation,
        string $table,
        $id,
        array &$fieldArray,
        \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
    ): void {
        if ($table !== 'fe_users') {
            return;
        }

        if ($operation === 'update') {
            $record = BackendUtility::getRecord($table, $id);
            $pageCache = GeneralUtility::makeInstance(CacheManager::class);
            $pageCache->flushCachesByTag('tx_staffdirectory_person_' . $id);
        } else {
            $record = $fieldArray;
        }

        if (isset($fieldArray['title']) || isset($fieldArray['first_name']) || isset($fieldArray['last_name'])) {
            $fullNameParts = [];
            $fullNameParts[] = $fieldArray['title'] ?? $record['title'];
            $fullNameParts[] = $fieldArray['first_name'] ?? $record['first_name'];
            $fullNameParts[] = $fieldArray['last_name'] ?? $record['last_name'];

            $record['name'] = $fieldArray['name'] = trim(implode(' ', $fullNameParts));

            // Recompute the automatic path_segment
            $fieldArray['path_segment'] = $this->regeneratePathSegment(
                $table,
                $operation === 'update' ? $id : -1,
                $fieldArray
            );
        }

        $this->cleanupPhoneNumbers($record, $fieldArray);

        if ($record['tx_extbase_type'] === 'tx_staffdirectory') {
            $fieldArray['username'] = strtolower(GeneralUtility::makeInstance(CharsetConverter::class)->utf8_char_mapping(str_replace(' ', '.', (string)$record['name'])));
            if ($operation === 'new') {
                $fieldArray['password'] = base64_encode(GeneralUtility::makeInstance(Random::class)->generateRandomBytes(30));
            }
        }
    }

    /**
     * @param array<string, mixed> $record
     * @param array<string, mixed> $fieldArray
     */
    protected function cleanupPhoneNumbers(array $record, array &$fieldArray): void
    {
        $country = $fieldArray['country'] ?? $record['country'] ?? 'CH';
        $phoneNumberUtil = PhoneNumberUtil::getInstance();

        foreach (['telephone', 'tx_staffdirectory_mobilephone'] as $field) {
            $value = $fieldArray[$field] ?? $record[$field] ?? null;
            if (!empty($value)) {
                try {
                    $phoneNumber = $phoneNumberUtil->parse($value, $country);
                    if ($phoneNumberUtil->isValidNumber($phoneNumber)) {
                        $fieldArray[$field] = $phoneNumberUtil->format($phoneNumber, PhoneNumberFormat::NATIONAL);
                    } else {
                        // Invalid telephone number
                        $fieldArray[$field] = '';
                    }
                } catch (NumberParseException) {
                    // Nothing to do
                }
            }
        }
    }

    /**
     * Regenerates the slug.
     *
     * @param array<string, mixed> $fields
     */
    protected function regeneratePathSegment(string $table, int $uid, array $fields = []): string
    {
        if ($uid > 0) {
            $record = BackendUtility::getRecordWSOL($table, $uid);
            $record = array_merge($record, $fields);
        } else {
            $record = $fields;
        }
        $fieldConfig = $GLOBALS['TCA'][$table]['columns']['path_segment']['config'];

        $slugHelper = GeneralUtility::makeInstance(
            SlugHelper::class,
            $table,
            'path_segment',
            $fieldConfig
        );

        $slug = $slugHelper->generate($record, $record['pid']);

        if ($uid <= 0) {
            return $slug;
        }

        $state = RecordStateFactory::forName($table)->fromArray($record);

        if (str_contains((string)$fieldConfig['eval'], 'uniqueInSite')) {
            $slug = $slugHelper->buildSlugForUniqueInSite($slug, $state);
        }

        if (str_contains((string)$fieldConfig['eval'], 'uniqueInPid')) {
            $slug = $slugHelper->buildSlugForUniqueInPid($slug, $state);
        }

        return $slugHelper->sanitize($slug);
    }
}
