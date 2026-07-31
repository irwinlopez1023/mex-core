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
    private string $iso;

    public function __construct(
        int    $id,
        string $nombre,
        string $curp,
        string $abreviatura,
        string $iso = '',
    ) {
        $this->id           = $id;
        $this->nombre       = $nombre;
        $this->curp         = $curp;
        $this->abreviatura  = $abreviatura;
        $this->iso          = $iso;
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

    /**
     * Abreviatura de uso comun. OJO: el largo es variable, de dos a cinco
     * letras (BC, JAL, CDMX, TAMPS). Si se necesita un codigo de largo fijo
     * hay que usar toIso().
     */
    public function toAbreviatura(): string
    {
        return $this->abreviatura;
    }

    /**
     * Codigo ISO 3166-2:MX, siempre de tres letras: BCN, NLE, CMX, TAM.
     *
     * La norma solo cubre las 32 entidades federativas, asi que para Nacido
     * en el Extranjero se devuelve NE, que es el codigo que usa la CURP y el
     * que esperan los sistemas que aceptan este catalogo.
     */
    public function toIso(): string
    {
        return $this->iso;
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
     * @return array{numero: int, nombre: string, curp: string, abreviatura: string, iso: string}
     */
    public function toArray(): array
    {
        return [
            'numero'      => $this->id,
            'nombre'      => $this->nombre,
            'curp'        => $this->curp,
            'abreviatura' => $this->abreviatura,
            'iso'         => $this->iso,
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
