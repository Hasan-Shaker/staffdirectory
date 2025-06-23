<?php
declare(strict_types = 1);

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

namespace Causal\Staffdirectory\ViewHelpers\Person;

use Causal\Staffdirectory\Domain\Model\Organization;
use Causal\Staffdirectory\Domain\Model\Person;
use Causal\Staffdirectory\Domain\Repository\OrganizationRepository;
use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class MembershipViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    protected OrganizationRepository $organizationRepository;

    public function __construct(OrganizationRepository $organizationRepository)
    {
        $this->organizationRepository = $organizationRepository;
    }

    public function initializeArguments()
    {
        $this->registerArgument('person', Person::class, 'Person to instantiate the memberships for', true);
        $this->registerArgument('name', 'string', 'Name of variable to create', true);
    }

    public function render(): string
    {
        /** @var Person $person */
        $person = $this->arguments['person'];
        $variableName = $this->arguments['name'];

        $membership = $this->getMembership($person);

        $this->renderingContext->getVariableProvider()->add($variableName, $membership);
        $html = $this->renderChildren();
        $this->renderingContext->getVariableProvider()->remove($variableName);

        return $html;
    }

    /**
     * @throws Exception
     */
    protected function getMembership(Person $person): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_staffdirectory_domain_model_member');

        $statement = $queryBuilder
            ->select('m.position_function', 'm.organization')
            ->from('tx_staffdirectory_domain_model_member', 'm')
            ->join(
                'm',
                'tx_staffdirectory_domain_model_organization',
                'o',
                $queryBuilder->expr()->eq('o.uid', $queryBuilder->quoteIdentifier('m.organization'))
            )
            ->where(
                $queryBuilder->expr()->eq('m.feuser_id', $queryBuilder->createNamedParameter(
                    $person->getUid(),
                    Connection::PARAM_INT
                ))
            )
            ->orderBy('o.long_name', 'ASC')
            ->addOrderBy('m.sorting', 'ASC')
            ->executeQuery();

        $membership = [];
        while (($row = $statement->fetchAssociative()) !== false) {
            $organization = $this->organizationRepository->findByUid((int)$row['organization']);
            $membership[] = [
                'positionFunction' => $row['position_function'],
                'organization' => $organization,
                'links' => $this->findPagesWithPlugin($person, $organization)
            ];
        }

        return $membership;
    }

    /**
     * @param Person $person
     * @param Organization|null $organization
     * @return array
     * @throws Exception
     */
    protected function findPagesWithPlugin(Person $person, ?Organization $organization): array
    {
        $organizationUids = [];
        if ($organization !== null) {
            $organizationUids[] = $organization->getUid();
            $this->addParentOrganizations($organization->getUid(), $organizationUids);
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');
        $rows = $queryBuilder
            ->select('c.pid', 'c.pi_flexform', 'p.title')
            ->from('tt_content', 'c')
            ->join(
                'c',
                'pages',
                'p',
                $queryBuilder->expr()->eq('p.uid', $queryBuilder->quoteIdentifier('c.pid'))
            )
            ->where(
                $queryBuilder->expr()->eq('CType', $queryBuilder->quote('staffdirectory_plugin')),
                $queryBuilder->expr()->eq('p.doktype', PageRepository::DOKTYPE_DEFAULT)
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $data = [];
        foreach ($rows as $row) {
            $flexform = GeneralUtility::xml2array($row['pi_flexform'])['data']['sDEF']['lDEF'];
            if ($flexform['settings.displayMode']['vDEF'] === 'ORGANIZATION' && $organization !== null) {
                $selectedOrganizations = GeneralUtility::intExplode(',', $flexform['settings.organizations']['vDEF'], true);
                if (array_intersect($selectedOrganizations, $organizationUids)) {
                    $data[$row['pid']] = [
                        'pageUid' => $row['pid'],
                        'title' => $row['title'],
                    ];
                }
            } elseif ($flexform['settings.displayMode']['vDEF'] === 'PERSONS') {
                if (!isset($flexform['settings.persons'])) {
                    continue;
                }

                $selectedPersons = GeneralUtility::intExplode(',', $flexform['settings.persons']['vDEF'], true);
                if (in_array($person->getUid(), $selectedPersons, true)) {
                    $data[$row['pid']] = [
                        'pageUid' => $row['pid'],
                        'title' => $row['title'],
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    protected function addParentOrganizations(int $organizationUid, array &$organizations): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_staffdirectory_domain_model_organization');
        $parentOrganisations = $queryBuilder
            ->select('uid')
            ->from('tx_staffdirectory_domain_model_organization')
            ->where(
                $queryBuilder->expr()->inSet(
                    'suborganizations',
                    $queryBuilder->createNamedParameter($organizationUid, Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($parentOrganisations as $parentOrganisation) {
            $organizations[] = (int)$parentOrganisation['uid'];
            $this->addParentOrganizations((int)$parentOrganisation['uid'], $organizations);
        }
    }
}
