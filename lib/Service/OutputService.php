<?php

declare(strict_types=1);


/**
 * Nextcloud - Backup now. Restore later.
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


namespace OCA\Backup\Service;


use OCA\Backup\Model\ChunkPartHealth;
use OCA\Backup\Model\RestoringHealth;
use OCA\Backup\Model\RestoringPoint;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class OutputService
 *
 * @package OCA\Backup\Service
 */
class OutputService {


	/** @var OutputInterface */
	private $output;

	/** @var bool */
	private $debug = false;


	/**
	 * @param string $line
	 * @param bool $ln
	 */
	public function o(string $line, bool $ln = true): void {
		if ($this->isDebug()) {
			echo $line . (($ln) ? "\n" : '');
		}
		if (!$this->hasOutput()) {
			return;
		}

		if ($ln) {
			$this->output->writeln($line);
		} else {
			$this->output->write($line);
		}
	}


	/**
	 * @param bool $debug
	 */
	public function setDebug(bool $debug): void {
		$this->debug = $debug;
	}

	/**
	 * @return bool
	 */
	public function isDebug(): bool {
		return $this->debug;
	}


	/**
	 * @return bool
	 */
	private function hasOutput(): bool {
		return !is_null($this->output);
	}

	public function setOutput(OutputInterface $output): void {
		$this->output = $output;
	}


	/**
	 * @param RestoringPoint $point
	 *
	 * @return string
	 */
	public function displayHealth(RestoringPoint $point): string {
		$health = $point->getHealth();
		if ($health->getStatus() === RestoringHealth::STATUS_OK) {
			return '<info>ok</info>';
		}

		if ($health->getStatus() === RestoringHealth::STATUS_ORPHAN) {
			return '<comment>orphan</comment>';
		}

		$unknown = $good = $missing = $faulty = 0;
		foreach ($health->getParts() as $chunk) {
			switch ($chunk->getStatus()) {
				case ChunkPartHealth::STATUS_UNKNOWN:
					$unknown++;
					break;
				case ChunkPartHealth::STATUS_OK:
					$good++;
					break;
				case ChunkPartHealth::STATUS_MISSING:
					$missing++;
					break;
				case ChunkPartHealth::STATUS_CHECKSUM:
					$faulty++;
					break;
			}
		}

		return '<comment>'
			   . $good . ' correct, '
			   . $missing . ' missing and '
			   . $faulty . ' faulty files</comment>';
	}

}
