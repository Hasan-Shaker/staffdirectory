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

namespace Causal\Staffdirectory\Backend\Tca;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Country
{
    /**
     * Returns the list of available countries.
     *
     * @param array<string, mixed> $conf
     * @return array<string, mixed>
     */
    public function getAll(array $conf = []): array
    {
        if ($conf === []) {
            $conf = ['items' => []];
        }

        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion();

        $items = [];
        $statement = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('static_countries')
            ->select(
                ['cn_iso_2', 'cn_short_en'],
                'static_countries',
                [], // where
                [], // group by
                [
                    'cn_short_en' => 'ASC',
                ]
            );
        while (($row = $statement->fetchAssociative()) !== false) {
            if ($typo3Version >= 12) {
                $items[] = [
                    'label' => $row['cn_short_en'],
                    'value' => $row['cn_iso_2'],
                ];
            } else {
                $items[] = [$row['cn_short_en'], $row['cn_iso_2']];
            }
        }

        $conf['items'] = array_merge($conf['items'], $items);

        return $conf;
    }
}
