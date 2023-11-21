<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011-2020 Xavier Perseguers <xavier@causal.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

namespace Causal\Staffdirectory\Domain\Repository;

use Causal\Staffdirectory\Domain\Model\Department;
use Causal\Staffdirectory\Domain\Model\Member;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Member repository.
 *
 * @category    Repository
 * @package     TYPO3
 * @subpackage  tx_staffdirectory
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class MemberRepository extends AbstractRepository
{

    /**
     * Finds all members (without including duplicated persons).
     *
     * @return Member[]
     */
    public function findAll(): array
    {
        $membersDao = $this->dao->getMembers();
        return $this->dao2business($membersDao);
    }

    /**
     * Finds a member by its uid.
     *
     * @param int $uid
     * @return Member|null
     */
    public function findByUid(int $uid): ?Member
    {
        $memberDao = $this->dao->getMemberByUid($uid);
        if ($memberDao) {
            $members = $this->dao2business([$memberDao]);
            return $members[0];
        }
        return null;
    }

    /**
     * Finds a member by an underlying person uid.
     *
     * @param int $uid
     * @return Member|null
     */
    public function findOneByPersonUid(int $uid): ?Member
    {
        $membersDao = $this->dao->getMembersByPersonUid($uid);
        if ($membersDao) {
            $members = $this->dao2business($membersDao);
            return $members[0];
        }
        return null;
    }

    /**
     * Instantiates a skeleton member by an underlying person uid.
     *
     * @param int $uid
     * @return Member|null
     */
    public function instantiateFromPersonUid(int $uid): ?Member
    {
        $memberDao = $this->dao->instantiateMemberByPersonUid($uid);
        if ($memberDao) {
            $members = $this->dao2business([$memberDao]);
            return $members[0];
        }
        return null;
    }

    /**
     * Finds all members of a given list of staffs.
     *
     * @param string $staffs comma-separated list of staffs
     * @return Member[]
     */
    public function findByStaffs(string $staffs): array
    {
        $membersDao = $this->dao->getMembersByStaffs($staffs);
        return $this->dao2business($membersDao);
    }

    /**
     * Finds all members of a given department.
     *
     * @param Department $department
     * @return Member[]
     */
    public function findByDepartment(Department $department): array
    {
        $membersDao = $this->dao->getMembersByDepartment($department->getUid());
        return $this->dao2business($membersDao);
    }

    /**
     * Loads the staffs of a given member.
     *
     * @param Member $member
     * @return void
     */
    public function loadStaffs(Member $member): void
    {
        /** @var StaffRepository $staffRepository */
        $staffRepository = Factory::getRepository('Staff');
        $staffs = $staffRepository->findByPerson($member);
        $member->setStaffs($staffs);
    }

    /**
     * Converts DAO members into business objects.
     *
     * @param array $dao
     * @return Member[]
     */
    protected function dao2business(array $dao): array
    {
        $ret = [];
        foreach ($dao as $data) {
            /** @var Member $member */
            $member = GeneralUtility::makeInstance(Member::class, $data['uid']);
            $member
                ->setPersonUid($data['person_id'])
                ->setPositionFunction($data['position_function'])
                ->setTitle($data['title'])
                ->setName($data['name'])
                ->setFirstName($data['first_name'])
                ->setLastName($data['last_name'])
                ->setAddress(trim($data['address']))
                ->setPostalCode($data['zip'])
                ->setCity($data['city'])
                ->setCountry($data['country'])
                ->setTelephone($data['telephone'])
                ->setFax($data['fax'])
                ->setEmail($data['email'])
                ->setWebsite($data['www'])
                ->setGender($data['tx_staffdirectory_gender'])
                ->setMobilePhone($data['tx_staffdirectory_mobilephone'])
                ->setEmail2($data['tx_staffdirectory_email2']);
            if (!empty($data['image'])) {
                // Very basic handling of FAL resource
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getQueryBuilderForTable('sys_file');
                $image = $queryBuilder
                    ->select('f.identifier')
                    ->from('sys_file', 'f')
                    ->join(
                        'f',
                        'sys_file_reference',
                        'fr',
                        $queryBuilder->expr()->eq('fr.uid_local', $queryBuilder->quoteIdentifier('f.uid'))
                    )
                    ->where(
                        $queryBuilder->expr()->eq('fr.tablenames', $queryBuilder->quote('fe_users')),
                        $queryBuilder->expr()->eq('fr.fieldname', $queryBuilder->quote('image')),
                        $queryBuilder->expr()->eq('fr.uid_foreign', $queryBuilder->createNamedParameter($data['person_id'], \PDO::PARAM_INT))
                    )
                    ->execute()
                    ->fetchOne();
                if (!empty($image)) {
                    $member->setImage('fileadmin' . $image);
                }
            }
            $ret[] = $member;
        }
        return $ret;
    }

}
