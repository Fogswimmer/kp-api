<?php

namespace App\Model\Query;

class QueryDto
{
    public const LIMIT = 10;
    public const OFFSET = 0;

    public ?int $limit = self::LIMIT;
    public ?int $offset = self::OFFSET;
    public ?string $search = '';

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function setOffset(?int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): static
    {
        $this->search = $search;

        return $this;
    }


}
