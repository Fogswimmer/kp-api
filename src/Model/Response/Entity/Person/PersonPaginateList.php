<?php
namespace App\Model\Response\Entity\Person;
use App\Model\Response\Entity\Person\PersonDetail;

class PersonPaginateList
{
	/**
	 * @var PersonDetail[]
	 */
	private array $items;
	private int $currentPage;
	private int $totalPages;

	/**
	 * @param PersonDetail[] $items
	 */
	public function __construct(array $items, int $totalPages = null, ?int $currentPage = null)
	{
		$this->items = $items;
		$this->totalPages = $totalPages ?? count($items);
		$this->currentPage = $currentPage ?? 1;
	}

	/**
	 * @return PersonDetail[]
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	public function getTotalPages(): int
	{
			return $this->totalPages;
	}

	public function getCurrentPage(): ?int
	{
			return $this->currentPage;
	}
}
