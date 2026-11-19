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

namespace Causal\Staffdirectory\Domain\Model;

use Causal\Staffdirectory\Domain\Repository\OrganizationRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Organization extends AbstractEntity
{
    protected string $longName = '';
    protected string $shortName = '';
    protected string $description = '';
    protected string $children = '';
    /** @var Organization[] */
    protected array $suborganizationsArray = [];

    /**
     * @var ObjectStorage<Member>|null
     * @Lazy
     */
    protected ?ObjectStorage $members;

    public function __construct()
    {
        $this->members = new ObjectStorage();
    }

    /**
     * Called again with initialize object, as fetching an entity from the DB does not use the constructor
     */
    public function initializeObject(): void
    {
        $this->members ??= new ObjectStorage();
    }

    public function getLongName(): string
    {
        return $this->longName;
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return ObjectStorage<Member>
     */
    public function getMembers(): ObjectStorage
    {
        return $this->members;
    }

    /**
     * @return Organization[]
     */
    public function getSuborganizations(): array
    {
        // This business logic is a trick to get the suborganizations in the same order as
        // they are defined in the TCA as Extbase doesn't seem to do it properly on its own.

        if ($this->suborganizationsArray === []
            && !empty($this->children)) {

            $organizationRepository = GeneralUtility::makeInstance(OrganizationRepository::class);
            foreach (GeneralUtility::intExplode(',', $this->children, true) as $childUid) {
                $child = $organizationRepository->findByUid($childUid);
                if ($child !== null) {
                    $this->suborganizationsArray[] = $child;
                }
            }
        }

        return $this->suborganizationsArray;
    }
}
