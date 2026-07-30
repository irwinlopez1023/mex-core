<?php

declare(strict_types=1);

namespace Irwinlopez1023\MexCore;

final class Persona
{
    private string $curp;
    private string $primerNombre;
    private string $segundoNombre;
    private string $primerApellido;
    private string $segundoApellido;

    public function __construct(
        string $curp,
        string $primerNombre,
        string $segundoNombre,
        string $primerApellido,
        string $segundoApellido,
    ) {
        $this->curp            = $curp;
        $this->primerNombre    = $primerNombre;
        $this->segundoNombre   = $segundoNombre;
        $this->primerApellido  = $primerApellido;
        $this->segundoApellido = $segundoApellido;
    }

    public function toCurp(): string
    {
        return $this->curp;
    }

    public function toPrimerNombre(): string
    {
        return $this->primerNombre;
    }

    public function toSegundoNombre(): string
    {
        return $this->segundoNombre;
    }

    public function toPrimerApellido(): string
    {
        return $this->primerApellido;
    }

    public function toSegundoApellido(): string
    {
        return $this->segundoApellido;
    }

    public function toNombreCompleto(): string
    {
        return implode(' ', array_filter([
            $this->primerNombre,
            $this->segundoNombre,
            $this->primerApellido,
            $this->segundoApellido,
        ]));
    }

    public function toNombreUnico(): string
    {
        $nombres = array_filter([$this->primerNombre, $this->segundoNombre]);

        return implode(' ', $nombres);
    }

    public function combinar(): self
    {
        $nombreUnico = $this->toNombreUnico();

        return new self(
            $this->curp,
            $nombreUnico,
            '',
            $this->primerApellido,
            $this->segundoApellido,
        );
    }

    public function toArray(): array
    {
        return [
            'curp'            => $this->curp,
            'primerNombre'    => $this->primerNombre,
            'segundoNombre'   => $this->segundoNombre,
            'primerApellido'  => $this->primerApellido,
            'segundoApellido' => $this->segundoApellido,
        ];
    }

    public function toEstado(): Estado
    {
        return MexCore::Estado()->fromCurp($this->curp);
    }
}
