<?php
namespace App\Model\Response\Entity\Film;
use App\Model\Response\Entity\Film\FilmDetail;

class FilmPaginateList
{
    /**
     * @var FilmDetail[]
     */
    private array $items;

    private int $currentPage;

    private int $totalPages;

    /**
     * @param FilmDetail[] $items
     */
    public function __construct(array $items, ?int $totalPages = null, ?int $currentPage = null)
    {
        $this->items = $items;
        $this->totalPages = $totalPages ?? count($items);
        $this->currentPage = $currentPage ?? 1;
    }

    /**
     * @return FilmDetail[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalPages(): ?int
    {
        return $this->totalPages;
    }

    public function getCurrentPage(): ?int
    {
        return $this->currentPage;
    }
}
