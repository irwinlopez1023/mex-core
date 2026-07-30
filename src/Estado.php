<?php

declare(strict_types=1);

namespace Irwinlopez1023\MexCore;

/**
 * Value object de una entidad federativa.
 *
 * La API principal esta en espanol, igual que Persona. Los metodos en ingles
 * (toName, toAbbr, toNumber) se conservan como alias delegados para no romper
 * el codigo que ya los usa.
 */
final class Estado implements \JsonSerializable
{
    private int $id;
    private string $nombre;
    private string $curp;
    private string $abreviatura;

    public function __construct(int $id, string $nombre, string $curp, string $abreviatura)
    {
        $this->id           = $id;
        $this->nombre       = $nombre;
        $this->curp         = $curp;
        $this->abreviatura  = $abreviatura;
    }

    /**
     * Clave numerica oficial del INEGI, del 1 al 32, mas 33 para nacidos en
     * el extranjero.
     */
    public function toNumero(): int
    {
        return $this->id;
    }

    /**
     * Codigo de dos letras que usa la CURP en las posiciones 11 y 12.
     */
    public function toCurp(): string
    {
        return $this->curp;
    }

    public function toAbreviatura(): string
    {
        return $this->abreviatura;
    }

    public function toNombre(): string
    {
        return $this->nombre;
    }

    /**
     * Numero con dos digitos, como aparece en las claves del INEGI: "09".
     */
    public function toNumeroFormateado(): string
    {
        return str_pad((string) $this->id, 2, '0', STR_PAD_LEFT);
    }

    public function esExtranjero(): bool
    {
        return $this->id === 33;
    }

    public function equals(self $otro): bool
    {
        return $this->toArray() === $otro->toArray();
    }

    /**
     * @return array{numero: int, nombre: string, curp: string, abreviatura: string}
     */
    public function toArray(): array
    {
        return [
            'numero'      => $this->id,
            'nombre'      => $this->nombre,
            'curp'        => $this->curp,
            'abreviatura' => $this->abreviatura,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // --- Alias en ingles (compatibilidad) -------------------------------

    public function toNumber(): int
    {
        return $this->toNumero();
    }

    public function toAbbr(): string
    {
        return $this->toAbreviatura();
    }

    public function toName(): string
    {
        return $this->toNombre();
    }
}
