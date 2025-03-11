<?php
namespace App\Model\Response\Entity\Film;

class FilmList
{
  public function __construct(array $items)
  {
    $this->items = $items;
  }
  /**
   * @var FilmListItem[]
   */
  private array $items;

  /**
   * @param FilmListItem[] $items
   */

  /**
   * @return FilmListItem[]
   */
  public function getItems(): array
  {
    return $this->items;
  }

}