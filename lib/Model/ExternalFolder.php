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


namespace OCA\Backup\Model;


use ArtificialOwl\MySmallPhpTools\Db\Nextcloud\nc23\INC23QueryRow;
use ArtificialOwl\MySmallPhpTools\IDeserializable;
use ArtificialOwl\MySmallPhpTools\Traits\TArrayTools;
use ArtificialOwl\MySmallPhpTools\Traits\TStringTools;
use JsonSerializable;


/**
 * Class ExternalFolder
 *
 * @package OCA\Backup\Model
 */
class ExternalFolder implements JsonSerializable, INC23QueryRow, IDeserializable {


	use TArrayTools;
	use TStringTools;


	/** @var int */
	private $storageId = 0;

	/** @var string */
	private $root = '';


	/**
	 * ExternalFolder constructor.
	 */
	public function __construct() {
	}


	/**
	 * @param int $storageId
	 *
	 * @return ExternalFolder
	 */
	public function setStorageId(int $storageId): self {
		$this->storageId = $storageId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getStorageId(): int {
		return $this->storageId;
	}


	/**
	 * @param string $root
	 *
	 * @return ExternalFolder
	 */
	public function setRoot(string $root): self {
		$this->root = $root;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getRoot(): string {
		return $this->root;
	}


	/**
	 * @param array $data
	 *
	 * @return ExternalFolder
	 */
	public function importFromDatabase(array $data): INC23QueryRow {
		$this->setStorageId($this->getInt('storage_id', $data))
			 ->setRoot($this->get('root', $data));

		return $this;
	}


	/**
	 * @param array $data
	 *
	 * @return ExternalFolder
	 */
	public function import(array $data): IDeserializable {
		$this->setStorageId($this->getInt('storageId', $data))
			 ->setRoot($this->get('root', $data));

		return $this;
	}


	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return [
			'storageId' => $this->getStorageId(),
			'root' => $this->getRoot()
		];
	}

}
