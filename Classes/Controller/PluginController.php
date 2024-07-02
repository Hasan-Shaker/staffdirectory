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

namespace Causal\Staffdirectory\Controller;

use Causal\Staffdirectory\Domain\Model\Member;
use Causal\Staffdirectory\Domain\Model\Organization;
use Causal\Staffdirectory\Domain\Model\Person;
use Causal\Staffdirectory\Domain\Repository\MemberRepository;
use Causal\Staffdirectory\Domain\Repository\OrganizationRepository;
use Causal\Staffdirectory\Domain\Repository\PersonRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class PluginController extends ActionController
{
    protected OrganizationRepository $organizationRepository;
    protected MemberRepository $memberRepository;
    protected PersonRepository $personRepository;

    public function __construct(
        OrganizationRepository $organizationRepository,
        MemberRepository $memberRepository,
        PersonRepository $personRepository
    )
    {
        $this->organizationRepository = $organizationRepository;
        $this->memberRepository = $memberRepository;
        $this->personRepository = $personRepository;
    }

    public function dispatchAction(): ResponseInterface
    {
        switch ($this->settings['displayMode']) {
            case 'LIST':
                return new ForwardResponse('list');
            case 'ORGANIZATION':
                return new ForwardResponse('organization');
            case 'PERSON':
                return new ForwardResponse('person');
            case 'PERSONS':
                return new ForwardResponse('persons');
            case 'DIRECTORY':
                return new ForwardResponse('directory');
            default:
                throw new \RuntimeException(
                    sprintf('Invalid display mode "%s"', $this->settings['displayMode']),
                    1701367726
                );
        }
    }

    public function listAction(): ResponseInterface
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
            $organizations = $this->organizationRepository->findAll();
        }

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
        if ($organization === null) {
            // Get first selected organization in the list
            $uids = GeneralUtility::intExplode(',', $this->settings['organizations'], true);
            $organization = $this->organizationRepository->findByUid($uids[0] ?? 0);
        }

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
        if ($person === null) {
            // Get first selected person in the list
            $uids = GeneralUtility::intExplode(',', $this->settings['persons'], true);
            $person = $this->personRepository->findByUid($uids[0] ?? 0);
        }

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
            $persons = $this->personRepository->findAll();
        }

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
            $organizations = $this->organizationRepository->findAll();
        }

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

    /**
     * Tags the page cache so that FAL signal operations may be listened to in
     * order to flush corresponding page cache.
     *
     * @param Organization|null $organization
     */
    protected function addCacheTagsForOrganization(?Organization $organization, int $recursion = 0): void
    {
        if ($organization === null) {
            return;
        }

        if ($recursion > 10) {
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
     *
     * @param Member|null $member
     */
    protected function addCacheTagsForMember(?Member $member): void {
        if ($member === null) {
            return;
        }

        $this->addCacheTagsForPerson($member->getPerson());
    }

    /**
     * Tags the page cache so that FAL signal operations may be listened to in
     * order to flush corresponding page cache.
     *
     * @param Person|null $person
     */
    protected function addCacheTagsForPerson(?Person $person): void {
        if ($person === null) {
            return;
        }

        // Tag the page cache so that FAL signal operations may be listened to in
        // order to flush corresponding page cache
        $cacheTags = [
            'tx_staffdirectory_person_' . $person->getUid(),
        ];

        $this->getTypoScriptFrontendController()->addCacheTags($cacheTags);
    }

    protected function getContentObjectData(): array
    {
        $typo3Version = (new Typo3Version())->getMajorVersion();
        if ($typo3Version >= 12) {
            return $this->request->getAttribute('currentContentObject')->data;
        } else {
            return $this->configurationManager->getContentObject()->data;
        }
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
