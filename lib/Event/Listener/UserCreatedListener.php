<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2023, Ucar Solutions UG (haftungsbeschraenkt)
 *
 * @author Dogan Ucar <info@ucar-solutions.de>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\RegisterToContact\Event\Listener;

use OC\User\Manager;
use OCA\DAV\CardDAV\CardDavBackend;
use OCP\Accounts\IAccountManager;
use OCP\Contacts\IManager as IContactsManager;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IUser;
use OCP\User\Events\UserCreatedEvent;

class UserCreatedListener implements IEventListener
{

    public const USERS_URI_ROOT = 'principals/users/';

    private IContactsManager $contactsManager;
    private IAccountManager $accountManager;
    private CardDavBackend $cardDavBackend;
    private Manager $userManager;

    public function __construct(
        IContactsManager $contactsManager,
        IAccountManager  $accountManager,
        CardDavBackend   $cardDavBackend,
        Manager          $userManager
    )
    {
        $this->contactsManager = $contactsManager;
        $this->accountManager = $accountManager;
        $this->cardDavBackend = $cardDavBackend;
        $this->userManager = $userManager;
    }

    public function handle(Event $event): void
    {

        if (!($event instanceof UserCreatedEvent)) {
            return;
        }

        if (!$this->contactsManager->isEnabled()) {
            return;
        }

        $newUser = $event->getUser();

        $self = $this;
        $this->userManager->callForAllUsers(
            function (IUser $existingUser) use ($self, $newUser): void {
                $addressBookId = $self->getAddressBookIdForUser($existingUser);
                $newUserAccount = $this->accountManager->getAccount($newUser);

                $this->contactsManager->createOrUpdate(
                    [
                        'FN'    => $newUserAccount->getProperty(IAccountManager::PROPERTY_DISPLAYNAME)->getValue(),
                        'EMAIL' => $newUserAccount->getProperty(IAccountManager::PROPERTY_EMAIL)->getValue(),
                        'TEL'   => $newUserAccount->getProperty(IAccountManager::PROPERTY_PHONE)->getValue()
                    ],
                    $addressBookId
                );
            }
        );


    }

    private function getPrincipalUri(IUser $user): string
    {
        return UserCreatedListener::USERS_URI_ROOT . $user->getUID();
    }

    private function getUniqueAddressBookUri(IUser $user): string
    {
        return md5((string)($user->getUID() + time()));
    }

    private function getAddressBookIdForUser(IUser $user): string
    {
        $principalUri = $this->getPrincipalUri($user);
        $addressBookUri = $this->getUniqueAddressBookUri($user);

        $addressBook = $this->cardDavBackend->getAddressBooksByUri($principalUri, $addressBookUri);
        if (null === $addressBook || 0 === count($addressBook)) {
            return (string)$this->cardDavBackend->createAddressBook(
                "principals/users/{$user->getUID()}",
                'RegisterToContact',
                []
            );
        }
        return (string)$addressBook['id'];
    }
}
