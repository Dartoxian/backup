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


namespace OCA\Backup\Model;


use ArtificialOwl\MySmallPhpTools\Exceptions\InvalidItemException;
use ArtificialOwl\MySmallPhpTools\Traits\Nextcloud\nc23\TNC23Deserialize;
use ArtificialOwl\MySmallPhpTools\Traits\TArrayTools;
use ArtificialOwl\MySmallPhpTools\Traits\TStringTools;
use JsonSerializable;


/**
 * Class RestoringChunk
 *
 * @package OCA\Backup\Model
 */
class RestoringData implements JsonSerializable {


	use TArrayTools;
	use TStringTools;
	use TNC23Deserialize;


	const ROOT_DISK = 1;
	const ROOT_NEXTCLOUD = 2;
	const ROOT_DATA = 3;
	const ROOT_APPS = 4;

	const FILE_CONFIG = 101;

	// value > 1000 is for content that are not 'file'
	const SQL_DUMP = 1001;

	const DATA = 'data';
	const APPS = 'apps';
	const CONFIG = 'config';


	/** @var string */
	private $name = '';

	/** @var int */
	private $type = 0;

	/** @var string */
	private $path = '';

	/** @var string */
	private $root = '';

	/** @var string */
	private $uniqueFile = '';

	/** @var RestoringChunk[] */
	private $chunks = [];

	/** @var string[] */
	private $files = [];


	/**
	 * RestoringChunk constructor.
	 *
	 * @param string $name
	 * @param int $type
	 * @param string $path
	 */
	public function __construct(int $type = 0, string $path = '', string $name = '') {
		$this->type = $type;
		$this->path = $path;

		$this->name = $name;
		if ($name === '') {
			$this->name = $this->uuid();
		}

	}


	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @param string $name
	 *
	 * @return RestoringData
	 */
	public function setName(string $name): RestoringData {
		$this->name = $name;

		return $this;
	}


	/**
	 * @return int
	 */
	public function getType(): int {
		return $this->type;
	}

	/**
	 * @param int $type
	 *
	 * @return RestoringData
	 */
	public function setType(int $type): RestoringData {
		$this->type = $type;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getPath(): string {
		return $this->path;
	}

	/**
	 * @param string $path
	 *
	 * @return RestoringData
	 */
	public function setPath(string $path): RestoringData {
		$this->path = $path;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getRoot(): string {
		return $this->root;
	}

	/**
	 * @param string $root
	 *
	 * @return RestoringData
	 */
	public function setRoot(string $root): RestoringData {
		$this->root = $root;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getAbsolutePath(): string {
		return $this->getRoot() . $this->getPath();
	}


	/**
	 * @param string $path
	 *
	 * @return RestoringData
	 */
	public function addFile(string $path): RestoringData {
		$this->files[] = $path;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getFiles(): array {
		return $this->files;
	}

	/**
	 * @param string[] $files
	 *
	 * @return RestoringData
	 */
	public function setFiles(array $files): RestoringData {
		$this->files = $files;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getUniqueFile(): string {
		return $this->uniqueFile;
	}

	/**
	 * @param string $file
	 *
	 * @return RestoringData
	 */
	public function setUniqueFile(string $file): RestoringData {
		$this->uniqueFile = $file;

		return $this;
	}


	/**
	 * @return RestoringChunk[]
	 */
	public function getChunks(): array {
		return $this->chunks;
	}

	/**
	 * @param RestoringChunk[] $chunks
	 *
	 * @return RestoringData
	 */
	public function setChunks(array $chunks): RestoringData {
		$this->chunks = $chunks;

		return $this;
	}

	/**
	 * @param RestoringChunk $chunk
	 *
	 * @return RestoringData
	 */
	public function addChunk(RestoringChunk $chunk): RestoringData {
		$this->chunks[] = $chunk;

		return $this;
	}


	/**
	 * @param array $data
	 *
	 * @return RestoringData
	 */
	public function import(array $data): RestoringData {
		$this->setType($this->getInt('type', $data, 0));
		$this->setName($this->get('name', $data, ''));
		$this->setRoot($this->get('root', $data, ''));
		$this->setPath($this->get('path', $data, ''));

		try {
			/** @var RestoringChunk[] $chunks */
			$chunks = $this->deserializeArray($this->getArray('chunks', $data), RestoringChunk::class);
			$this->setChunks($chunks);
		} catch (InvalidItemException $e) {
		}

		return $this;
	}


	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return [
			'name' => $this->getName(),
			'type' => $this->getType(),
			'root' => $this->getRoot(),
			'path' => $this->getPath(),
			'chunks' => $this->getChunks()
		];
	}

}
