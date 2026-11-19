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

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Person extends AbstractEntity
{
    protected string $name = '';
    protected string $firstName = '';
    protected string $middleName = '';
    protected string $lastName = '';
    protected string $title = '';
    protected int $gender = 0;
    protected string $email = '';
    protected string $alternateEmail = '';
    protected string $telephone = '';
    protected string $mobilePhone = '';
    protected string $address = '';
    protected string $zip = '';
    protected string $city = '';
    protected string $country = '';

    /**
     * @var ObjectStorage<FileReference>
     */
    protected ?ObjectStorage $image;

    public function __construct()
    {
        $this->image = new ObjectStorage();
    }

    /**
     * Called again with initialize object, as fetching an entity from the DB does not use the constructor
     */
    public function initializeObject(): void
    {
        $this->image ??= new ObjectStorage();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getGender(): int
    {
        return $this->gender;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAlternateEmail(): string
    {
        return $this->alternateEmail;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function getMobilePhone(): string
    {
        return $this->mobilePhone;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getZip(): string
    {
        return $this->zip;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return ObjectStorage<FileReference>
     */
    public function getImage(): ObjectStorage
    {
        return $this->image;
    }
}
