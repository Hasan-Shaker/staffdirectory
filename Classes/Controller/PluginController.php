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

namespace Causal\Staffdirectory\Controller;

use Causal\Staffdirectory\Domain\Model\Member;
use Causal\Staffdirectory\Domain\Model\Organization;
use Causal\Staffdirectory\Domain\Model\Person;
use Causal\Staffdirectory\Domain\Repository\MemberRepository;
use Causal\Staffdirectory\Domain\Repository\OrganizationRepository;
use Causal\Staffdirectory\Domain\Repository\PersonRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheTag;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class PluginController extends ActionController
{
    private const ORGANIZATION_RECURSION_LIMIT = 10;

    public function __construct(
        protected OrganizationRepository $organizationRepository,
        protected MemberRepository $memberRepository,
        protected PersonRepository $personRepository
    ) {}

    public function dispatchAction(): ResponseInterface
    {
        return match ($this->settings['displayMode']) {
            'LIST' => new ForwardResponse('list'),
            'ORGANIZATION' => new ForwardResponse('organization'),
            'PERSON' => new ForwardResponse('person'),
            'PERSONS' => new ForwardResponse('persons'),
            'DIRECTORY' => new ForwardResponse('directory'),
            default => throw new \RuntimeException(
                sprintf('Invalid display mode "%s"', $this->settings['displayMode']),
                1701367726
            ),
        };
    }

    public function listAction(): ResponseInterface
    {
        $organizations = $this->fetchSelectedOrganizations();

        foreach ($organizations as $organization) {
            // Tag the page cache so that FAL signal operations may be listened to in
            // order to flush corresponding page cache
            $this->addCacheTagsForOrganization($organization);
        }

        $this->view->assignMultiple([
            'organizations' => $organizations,
            // Raw data for the plugin
            'plugin' => $this->getContentObjectData(),
        ]);

        return new HtmlResponse(
            $this->view->render()
        );
    }

    public function organizationAction(?Organization $organization = null): ResponseInterface
    {
        if (!$organization instanceof Organization) {
            // Get first selected organization in the list
            $uids = GeneralUtility::intExplode(',', $this->settings['organizations'], true);
            $organization = $this->organizationRepository->findByUid($uids[0] ?? 0);
        }

        // Tag the page cache so that FAL signal operations may be listened to in
        // order to flush corresponding page cache
        $this->addCacheTagsForOrganization($organization);

        $this->view->assignMultiple([
            'organization' => $organization,
            // Raw data for the plugin
            'plugin' => $this->getContentObjectData(),
        ]);

        return new HtmlResponse(
            $this->view->render()
        );
    }

    public function personAction(?Person $person = null): ResponseInterface
    {
        if (!$person instanceof Person) {
            // Get first selected person in the list
            $uids = GeneralUtility::intExplode(',', $this->settings['persons'], true);
            $person = $this->personRepository->findByUid($uids[0] ?? 0);
        }

        // Tag the page cache so that FAL signal operations may be listened to in
        // order to flush corresponding page cache
        $this->addCacheTagsForPerson($person);

        // Disable link to ourselves
        $this->settings['targets']['person'] = null;

        $this->view->assignMultiple([
            'person' => $person,
            // Raw data for the plugin
            'plugin' => $this->getContentObjectData(),
        ]);

        return new HtmlResponse(
            $this->view->render()
        );
    }

    public function personsAction(): ResponseInterface
    {
        $persons = $this->fetchSelectedPersons();

        foreach ($persons as $person) {
            // Tag the page cache so that FAL signal operations may be listened to in
            // order to flush corresponding page cache
            $this->addCacheTagsForPerson($person);
        }

        $this->view->assignMultiple([
            'persons' => $persons,
            // Raw data for the plugin
            'plugin' => $this->getContentObjectData(),
        ]);

        return new HtmlResponse(
            $this->view->render()
        );
    }

    public function directoryAction(): ResponseInterface
    {
        $organizations = $this->fetchSelectedOrganizations();

        $organizationPersons = [];
        foreach ($organizations as $organization) {
            $organizationPersons[] = $this->fetchPersonsRecursive($organization);
            // Tag the page cache so that FAL signal operations may be listened to in
            // order to flush corresponding page cache
            $this->addCacheTagsForOrganization($organization);
        }

        $persons = array_merge([], ...$organizationPersons);
        $this->view->assignMultiple([
            'persons' => array_values($persons),
            // Raw data for the plugin
            'plugin' => $this->getContentObjectData(),
        ]);

        return new HtmlResponse(
            $this->view->render()
        );
    }

    /**
     * @return Organization[]
     */
    protected function fetchSelectedOrganizations(): array
    {
        if (!empty($this->settings['organizations'])) {
            $uids = GeneralUtility::intExplode(',', $this->settings['organizations'], true);
            $organizations = [];
            foreach ($uids as $uid) {
                $organization = $this->organizationRepository->findByUid($uid);
                if ($organization !== null) {
                    $organizations[] = $organization;
                }
            }
        } else {
            $organizations = $this->organizationRepository->findAll()->toArray();
        }

        return $organizations;
    }

    /**
     * @return Person[]
     */
    protected function fetchSelectedPersons(): array
    {
        if (!empty($this->settings['persons'])) {
            $uids = GeneralUtility::intExplode(',', $this->settings['persons'], true);
            $persons = [];
            foreach ($uids as $uid) {
                $person = $this->personRepository->findByUid($uid);
                if ($person !== null) {
                    $persons[] = $person;
                }
            }
        } else {
            $persons = $this->personRepository->findAll()->toArray();
        }

        return $persons;
    }

    /**
     * @return array<string, Person>
     */
    protected function fetchPersonsRecursive(?Organization $organization, int $recursion = 0): array
    {
        if (!$organization instanceof Organization) {
            return [];
        }

        if ($recursion > self::ORGANIZATION_RECURSION_LIMIT) {
            throw new \RuntimeException(
                vsprintf('Recursion limit reached for organization uid=%s (%s)', [
                    $organization->getUid(),
                    $organization->getLongName(),
                ]),
                1720682694
            );
        }

        $persons = [];
        foreach ($organization->getMembers() as $member) {
            $person = $member->getPerson();
            if ($person !== null) {
                $personKey = vsprintf('%s-%s-%s-%d', [
                    $person->getLastName(),
                    $person->getFirstName(),
                    $person->getMiddleName(),   // Middle name is less important than first name
                    $person->getUid(),
                ]);
                $persons[$personKey] = $person;
            }
        }

        $suborganizationPersons = [];
        foreach ($organization->getSuborganizations() as $suborganization) {
            $suborganizationPersons[] = $this->fetchPersonsRecursive($suborganization, $recursion + 1);
        }

        $combinedPersons = array_merge($persons, ...$suborganizationPersons);
        ksort($combinedPersons);

        return $combinedPersons;
    }

    /**
     * Tags the page cache so that FAL signal operations may be listened to in
     * order to flush corresponding page cache.
     *
     * @param int $recursion internal recursion counter
     */
    protected function addCacheTagsForOrganization(?Organization $organization, int $recursion = 0): void
    {
        if (!$organization instanceof Organization) {
            return;
        }

        if ($recursion > self::ORGANIZATION_RECURSION_LIMIT) {
            throw new \RuntimeException(
                vsprintf('Recursion limit reached for organization uid=%s (%s)', [
                    $organization->getUid(),
                    $organization->getLongName(),
                ]),
                1706191961
            );
        }

        foreach ($organization->getMembers() as $member) {
            $this->addCacheTagsForMember($member);
        }

        foreach ($organization->getSuborganizations() as $suborganization) {
            $this->addCacheTagsForOrganization($suborganization, $recursion + 1);
        }
    }

    /**
     * Tags the page cache so that FAL signal operations may be listened to in
     * order to flush corresponding page cache.
     */
    protected function addCacheTagsForMember(?Member $member): void
    {
        if (!$member instanceof Member) {
            return;
        }

        $this->addCacheTagsForPerson($member->getPerson());
    }

    /**
     * Tags the page cache so that FAL signal operations may be listened to in
     * order to flush corresponding page cache.
     */
    protected function addCacheTagsForPerson(?Person $person): void
    {
        if (!$person instanceof Person) {
            return;
        }

        // Tag the page cache so that FAL signal operations may be listened to in
        // order to flush corresponding page cache
        $cacheTags = [
            'tx_staffdirectory_person_' . $person->getUid(),
        ];

        if ((new Typo3Version())->getMajorVersion() >= 13) {
            $this->request
                ->getAttribute('frontend.cache.collector')
                ->addCacheTags(...array_map(
                    static fn(string $tag): CacheTag => new CacheTag($tag),
                    $cacheTags
                ));
        } else {
            $this->getTypoScriptFrontendController()->addCacheTags($cacheTags);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getContentObjectData(): array
    {
        return $this->request->getAttribute('currentContentObject')->data;
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
