<?php

declare(strict_types=1);

namespace Irwinlopez1023\MexCore;

final class Estado
{
    private int $id;
    private string $name;
    private string $curp;
    private string $abbr;

    public function __construct(int $id, string $name, string $curp, string $abbr)
    {
        $this->id   = $id;
        $this->name = $name;
        $this->curp = $curp;
        $this->abbr = $abbr;
    }

    public function toNumber(): int
    {
        return $this->id;
    }

    public function toCurp(): string
    {
        return $this->curp;
    }

    public function toAbbr(): string
    {
        return $this->abbr;
    }

    public function toName(): string
    {
        return $this->name;
    }
}
