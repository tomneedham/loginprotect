<?php
/**
 * @author Tom Needham <tom@owncloud.com>
 *
 * @copyright Copyright (c) 2018 Tom Needham <tom@owncloud.com>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\LoginProtect;

use \OCP\AppFramework\App;

class Application extends App {

	/** @var HookListener */
	protected $listener;

	public function __construct(array $urlParams = array()) {
		parent::__construct('loginprotect', $urlParams);
		$this->listener = new HookListener(
			$this->getContainer()->getServer()->getUserSession(),
			$this->getContainer()->getServer()->getGroupManager(),
			$this->getContainer()->getServer()->getConfig(),
			$this->getContainer()->getServer()->getRequest(),
			$this->getContainer()->getServer()->getLogger(),
			$this->getContainer()->getServer()->getL10N('loginprotect')
		);
	}

	public function registerHooks() {
		$this->getContainer()
			->getServer()
			->getUserSession()
			->listen(
				'\OC\User',
				'postLogin',
				[
					$this->listener,
					'handleLogin'
				]);
	}

}
