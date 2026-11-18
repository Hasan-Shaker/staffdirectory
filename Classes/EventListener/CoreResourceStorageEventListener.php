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
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\Staffdirectory\EventListener;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\Event\AfterFileReplacedEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CoreResourceStorageEventListener
{
    /**
     * CoreResourceStorageEventListener constructor.
     */
    public function __construct(protected CacheManager $pageCache) {}

    /**
     * A file has been added as a *replacement* of an existing one.
     */
    public function afterFileReplaced(AfterFileReplacedEvent $event): void
    {
        $rows = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_file_reference')
            ->select(
                ['uid_foreign'],
                'sys_file_reference',
                [
                    'uid_local' => $event->getFile()->getProperty('uid'),
                    'tablenames' => 'fe_users',
                    'fieldname' => 'image',
                ]
            )
            ->fetchAllAssociative();
        foreach ($rows as $row) {
            $this->pageCache->flushCachesByTag('tx_staffdirectory_person_' . $row['uid_foreign']);
        }
    }
}
