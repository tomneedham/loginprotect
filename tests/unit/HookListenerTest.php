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

namespace OCA\LoginProtect\Tests\Unit;

use OCA\LoginProtect\HookListener;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IUserSession;
use Test\TestCase;

class HookListenerTest extends TestCase {

	/** @var IUserSession | \PHPUnit_Framework_MockObject_MockObject */
	protected $userSession;
	/** @var IGroupManager | \PHPUnit_Framework_MockObject_MockObject */
	protected $groupManager;
	/** @var IConfig | \PHPUnit_Framework_MockObject_MockObject */
	protected $config;
	/** @var IRequest | \PHPUnit_Framework_MockObject_MockObject */
	protected $request;
	/** @var ILogger | \PHPUnit_Framework_MockObject_MockObject */
	protected $logger;
	/** @var IL10N | \PHPUnit_Framework_MockObject_MockObject */
	protected $l;
	/** @var HookListener | \PHPUnit_Framework_MockObject_MockObject */
	protected $listener;

	public function setUp() {
		$this->userSession = $this->createMock(IUserSession::class);
		$this->groupManager = $this->createMock(IGroupManager::class);
		$this->config = $this->createMock(IConfig::class);
		$this->request = $this->createMock(IRequest::class);
		$this->logger = $this->createMock(ILogger::class);
		$this->l = $this->createMock(IL10N::class);

		$this->listener = new HookListener(
			$this->userSession,
			$this->groupManager,
			$this->config,
			$this->request,
			$this->logger,
			$this->l
		);
	}

	/**
	 * @dataProvider ipRanges
	 * @param $config
	 * @param $remoteIp
	 * @param $loginAllowed
	 */
	public function testIpIsAllowed($config, $remoteIp, $loginAllowed) {
		$this->config->method('getAppValue')->willReturn($config);
		$this->assertEquals($loginAllowed, $this->listener->isIpAllowed($remoteIp));
	}

	public function ipRanges() {
		return [
			'nothing configured' => [null, '127.0.0.1', true],
			'localhost' => ['127.0.0.1/24', '127.0.0.1', true],
			'localhost, but remote access' => ['127.0.0.1/24', '10.0.0.1', false],
			'multiple ranges, miss' => ['10.0.0.1/24,10.0.1.1/24', '127.0.0.1', false],
			'multiple ranges, hit' => ['10.0.0.1/24,127.0.0.1/24', '127.0.0.1', true],
			'ipv6 hit' => ['2001:db8::/48', '2001:db8:0:0:0:0:0:0', true],
			'ipv6 miss' => ['2001:db8::/48', '2002:db8:0:0:0:0:0:0', false],
			'mixed hit v4' => ['127.0.0.1/24,2001:db8::/48', '127.0.0.1', true],
			'mixed hit v6' => ['127.0.0.1/24,2001:db8::/48', '2001:db8:0:0:0:0:0:0', true],
		];
	}




}