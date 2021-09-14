<?php

declare(strict_types=1);


/**
 * Nextcloud - Backup
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Maxence Lange <maxence@artificial-owl.com>
 * @copyright 2021, Maxence Lange <maxence@artificial-owl.com>
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


use ArtificialOwl\MySmallPhpTools\Exceptions\SignatoryException;
use ArtificialOwl\MySmallPhpTools\Exceptions\SignatureException;
use OC\Core\Command\Base;
use OCA\Backup\Db\RemoteRequest;
use OCA\Backup\Model\RemoteInstance;
use OCA\Backup\Service\RemoteStreamService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class RemoteList
 *
 * @package OCA\Backup\Command
 */
class RemoteList extends Base {


	/** @var RemoteRequest */
	private $remoteRequest;

	/** @var RemoteStreamService */
	private $remoteStreamService;


	public function __construct(RemoteRequest $remoteRequest, RemoteStreamService $remoteStreamService) {
		$this->remoteRequest = $remoteRequest;
		$this->remoteStreamService = $remoteStreamService;

		parent::__construct();
	}


	/**
	 *
	 */
	protected function configure() {
		$this->setName('backup:remote:list')
			 ->setDescription('Listing configured remote instances');
	}


	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 *
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$output = new ConsoleOutput();
		$output = $output->section();
		$table = new Table($output);
		$table->setHeaders(
			['Address', 'Stored Uid', 'Current Uid', 'Href', 'Incoming data', 'Outgoing data']
		);
		$table->render();

		foreach ($this->remoteRequest->getAll() as $remoteInstance) {

			$color = 'error';
			/** @var RemoteInstance $current */
			try {
				$current = $this->remoteStreamService->retrieveSignatory($remoteInstance->getId());
				if ($remoteInstance->getUid(true) === $current->getUid(true)) {
					$color = 'info';
				}
			} catch (SignatoryException | SignatureException $e) {
			}

			$table->appendRow(
				[
					$remoteInstance->getInstance(),
					$remoteInstance->getUid(true),
					'<' . $color . '>' . $current->getUid(true) . '</' . $color . '>',
					$remoteInstance->getId(),
					($remoteInstance->isIncoming() ? '<info>yes</info>' : '<comment>no</comment'),
					($remoteInstance->isOutgoing() ? '<info>yes</info>' : '<comment>no</comment')
				]
			);
		}

		return 0;
	}

}