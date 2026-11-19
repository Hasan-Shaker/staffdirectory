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

use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class Person extends AbstractRecordFetcher
{
    /**
     * @param array<string, mixed> $conf
     * @return array<string, mixed>
     */
    public function fetchAvailable(array $conf = []): array
    {
        $this->table = 'fe_users';
        $this->orderBy = 'last_name';

        return $this->fetchRecords($conf);
    }

    /**
     * @return array<int, string>
     */
    protected function getAdditionalConditions(QueryBuilder $queryBuilder): array
    {
        return [
            $queryBuilder->expr()->eq('tx_extbase_type', $queryBuilder->quote('tx_staffdirectory')),
        ];
    }
}
