<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011-2026 Xavier Perseguers <xavier@causal.ch>
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

namespace Causal\Staffdirectory\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Department.
 *
 * @category    Model
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Department extends AbstractEntity implements \Stringable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var Member[]
     */
    protected $members;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Department
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Department
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Member[]
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    /**
     * @param Member[] $members
     */
    public function setMembers(array $members): Department
    {
        $this->members = $members;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
