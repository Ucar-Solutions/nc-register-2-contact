<?php
declare(strict_types=1);

namespace OCA\RegisterToContact\Event\Listener;

use OC\User\Manager;
use OCA\DAV\CardDAV\CardDavBackend;
use OCP\Accounts\IAccountManager;
use OCP\Contacts\IManager as IContactsManager;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IUser;

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

        if (!($event instanceof \OCP\User\Events\UserCreatedEvent)) {
            return;
        }

        if (!$this->contactsManager->isEnabled()) {
            return;
        }

        $newUser = $event->getUser();

        $self = $this;
        $this->userManager->callForAllUsers(
            function (IUser $registeredUser) use ($self, $newUser): void {
                $addressBookId = $self->getAddressBookIdForUser($registeredUser);
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
        return md5($user->getUID() + time());
    }

    private function getAddressBookIdForUser(IUser $user)
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
