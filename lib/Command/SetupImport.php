<?php

declare(strict_types=1);


/**
 * Nextcloud - Backup
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Maxence Lange <maxence@artificial-owl.com>
 * @copyright 2019, Maxence Lange <maxence@artificial-owl.com>
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


namespace OCA\Backup\Command;


use ArtificialOwl\MySmallPhpTools\Traits\Nextcloud\nc23\TNC23Deserialize;
use ArtificialOwl\MySmallPhpTools\Traits\TArrayTools;
use Exception;
use OC\Core\Command\Base;
use OCA\Backup\Db\RemoteRequest;
use OCA\Backup\Model\RemoteInstance;
use OCA\Backup\Service\ConfigService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class SetupImport
 *
 * @package OCA\Backup\Command
 */
class SetupImport extends Base {


	use TArrayTools;
	use TNC23Deserialize;


	/** @var RemoteRequest */
	private $remoteRequest;

	/** @var ConfigService */
	private $configService;


	/**
	 * SetupImport constructor.
	 *
	 * @param RemoteRequest $remoteRequest
	 * @param ConfigService $configService
	 */
	public function __construct(RemoteRequest $remoteRequest, ConfigService $configService) {
		parent::__construct();

		$this->configService = $configService;
		$this->remoteRequest = $remoteRequest;
	}


	/**
	 *
	 */
	protected function configure() {
		$this->setName('backup:setup:import')
			 ->setDescription('Import your setup for easier restoration');
	}


	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 *
	 * @return int
	 * @throws Exception
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$json = '';
		while (!feof(STDIN)) {
			$json .= fgets(STDIN);
		}

		$setup = json_decode($json, true);
		if (!is_array($setup)) {
			throw new Exception('setup cannot be imported');
		}

		$this->configService->setAppValue('key_pairs', $this->get('signatory', $setup));


		/** @var RemoteInstance[] $remotes */
		$remotes = $this->deserializeArray($this->getArray('remote', $setup), RemoteInstance::class);

		foreach ($remotes as $remote) {
			$this->remoteRequest->insertOrUpdate($remote, true);
		}

		return 0;
	}

}

