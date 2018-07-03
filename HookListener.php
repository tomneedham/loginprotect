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

use OC\User\LoginException;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IUserSession;

class HookListener {

	/** @var IUserSession  */
	protected $userSession;
	/** @var IGroupManager  */
	protected $groupManager;
	/** @var IConfig  */
	protected $config;
	/** @var IRequest  */
	protected $request;
	/** @var ILogger  */
	protected $logger;
	/** @var IL10N  */
	protected $l;

	public function __construct(
		IUserSession $userSession,
		IGroupManager $groupManager,
		IConfig $config,
		IRequest $request,
		ILogger $logger,
		IL10N $l
	) {
		$this->userSession = $userSession;
		$this->groupManager = $groupManager;
		$this->config = $config;
		$this->request = $request;
		$this->logger = $logger;
		$this->l = $l;
	}

	/**
	 * Hook handler after login
	 * @throws LoginException
	 */
	public function handleLogin() {
		// Get the current user
		$user = $this->userSession->getUser();
		if (!$this->groupManager->isAdmin($user->getUID())) {
			// We don't care about non-admins
			return;
		}
		$ip = $this->request->getRemoteAddress();
		if (!$this->isIpAllowed($ip)) {
			$this->logger->info("Blocking admin login from ip $ip because no matching ip range configured", ['app' => 'loginprotect']);
			// Kill this login
			$this->userSession->logout();
			throw new LoginException($this->l->t('Login not permitted from this IP.'));
		}
	}

	/**
	 * Returns boolean whether this IP is allowed
	 * @param string $ip V4 or V6 IPs allowed
	 * @return bool
	 */
	public function isIpAllowed($ip) {
		// Get configured ranges
		$ranges = $this->config->getAppValue('loginprotect', 'allowed_ranges', null);
		if ($ranges === null) {
			// Nothing configured, allow it
			$this->logger->info("No admin ip whitelist ranges configured, allowing login...", ['app' => 'loginprotect']);
			return true;
		}
		$ranges = explode(',', $ranges);
		foreach ($ranges as $range) {
			$result = \Symfony\Component\HttpFoundation\IpUtils::checkIp($ip, $range);
			if ($result === true) {
				return true;
			}
		}
		return false;
	}

}