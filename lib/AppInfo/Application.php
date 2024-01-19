<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Ucar Solutions UG (haftungsbeschränkt) <info@ucar-solutions.de>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\RegisterToContact\AppInfo;

use OCA\RegisterToContact\Event\Listener\UserCreatedListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\User\Events\UserCreatedEvent;

class Application extends App {
	public const APP_ID = 'registertocontact';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerEventListener(
			UserCreatedEvent::class,
			UserCreatedListener::class
		);
	}
}
