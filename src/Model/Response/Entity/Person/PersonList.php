<?php

namespace App\Model\Response\Entity\Person;

class PersonList
{
	public function __construct(array $items)
	{
		$this->items = $items;
	}
	/**
	 * @var PersonListItem[]
	 */
	private array $items;

	/**
	 * @param PersonListItem[] $items
	 */

	/**
	 * @return PersonListItem[]
	 */
	public function getItems(): array
	{
		return $this->items;
	}
}
