<?php

declare(strict_types=1);

namespace Irwinlopez1023\MexCore;

final class Persona implements \JsonSerializable
{
    private string $curp;
    private string $primerNombre;
    private string $segundoNombre;
    private string $primerApellido;
    private string $segundoApellido;

    /**
     * Bloques de nombre detectados por la logica de pegamento. Se conservan
     * para que combinar() sea reversible con separar().
     *
     * @var list<string>
     */
    private array $nombres;

    /**
     * @param list<string>|null $nombres Bloques ya separados. Si es null se
     *                                   derivan de primerNombre y segundoNombre.
     */
    public function __construct(
        string $curp,
        string $primerNombre,
        string $segundoNombre,
        string $primerApellido,
        string $segundoApellido,
        ?array $nombres = null,
    ) {
        $this->curp            = $curp;
        $this->primerNombre    = $primerNombre;
        $this->segundoNombre   = $segundoNombre;
        $this->primerApellido  = $primerApellido;
        $this->segundoApellido = $segundoApellido;

        $this->nombres = array_values(array_filter(
            $nombres ?? [$primerNombre, $segundoNombre],
            static fn (string $bloque): bool => $bloque !== '',
        ));
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

    /**
     * Todos los bloques de nombre, sin perder el tercero y siguientes.
     *
     * "JUAN CARLOS ALBERTO" => ['JUAN', 'CARLOS', 'ALBERTO']
     *
     * @return list<string>
     */
    public function toNombres(): array
    {
        return $this->nombres;
    }

    public function toNombreCompleto(): string
    {
        return implode(' ', array_filter([
            $this->primerNombre,
            $this->segundoNombre,
            $this->primerApellido,
            $this->segundoApellido,
        ], static fn (string $parte): bool => $parte !== ''));
    }

    /**
     * Formato de documento oficial: "APELLIDOS, NOMBRES".
     */
    public function toNombreCompletoInvertido(): string
    {
        $apellidos = implode(' ', array_filter(
            [$this->primerApellido, $this->segundoApellido],
            static fn (string $parte): bool => $parte !== '',
        ));

        $nombres = $this->toNombreUnico();

        if ($apellidos === '' || $nombres === '') {
            return $apellidos . $nombres;
        }

        return $apellidos . ', ' . $nombres;
    }

    public function toNombreUnico(): string
    {
        return implode(' ', $this->nombres);
    }

    /**
     * Inicial de cada bloque de nombre mas la de cada apellido: "MRHG".
     */
    public function toIniciales(): string
    {
        $partes   = array_merge($this->nombres, [$this->primerApellido, $this->segundoApellido]);
        $iniciales = '';

        foreach ($partes as $parte) {
            if ($parte !== '') {
                $iniciales .= mb_substr($parte, 0, 1, 'UTF-8');
            }
        }

        return $iniciales;
    }

    /**
     * Fusiona todos los nombres en primerNombre. Reversible con separar().
     */
    public function combinar(): self
    {
        return new self(
            $this->curp,
            $this->toNombreUnico(),
            '',
            $this->primerApellido,
            $this->segundoApellido,
            $this->nombres,
        );
    }

    /**
     * Restaura la separacion original en primer y segundo nombre.
     */
    public function separar(): self
    {
        return new self(
            $this->curp,
            $this->nombres[0] ?? '',
            count($this->nombres) > 1 ? implode(' ', array_slice($this->nombres, 1)) : '',
            $this->primerApellido,
            $this->segundoApellido,
            $this->nombres,
        );
    }

    public function estaCombinado(): bool
    {
        return $this->segundoNombre === '' && count($this->nombres) > 1;
    }

    public function equals(self $otra): bool
    {
        return $this->toArray() === $otra->toArray();
    }

    /**
     * H, M, X segun la CURP. Cadena vacia si la CURP no lo permite deducir.
     */
    public function toSexo(): string
    {
        return Curp::sexo($this->curp);
    }

    public function toFechaNacimiento(): ?\DateTimeImmutable
    {
        return Curp::fechaNacimiento($this->curp);
    }

    public function toEdad(?\DateTimeImmutable $referencia = null): ?int
    {
        $nacimiento = $this->toFechaNacimiento();

        if ($nacimiento === null) {
            return null;
        }

        $referencia ??= new \DateTimeImmutable('today');

        if ($referencia < $nacimiento) {
            return null;
        }

        return $nacimiento->diff($referencia)->y;
    }

    /**
     * Estructura, fecha real, entidad existente y digito verificador correcto.
     */
    public function tieneCurpValida(): bool
    {
        return Curp::esValida($this->curp);
    }

    /**
     * Verifica que las letras derivables de la CURP correspondan a los nombres
     * y apellidos capturados. Sirve para detectar campos invertidos o mal
     * transcritos en cargas masivas del INE.
     *
     * Es una heuristica: puede dar falso negativo en casos exoticos (apellidos
     * compuestos con guion, homonimias resueltas a mano por RENAPO), asi que
     * conviene usarla para marcar registros a revisar, no para rechazarlos.
     */
    public function coincideConCurp(): bool
    {
        if (strlen($this->curp) !== 18) {
            return false;
        }

        $nombres = $this->toNombreUnico();

        $prefijo     = Curp::prefijoDesde($nombres, $this->primerApellido, $this->segundoApellido);
        $consonantes = Curp::consonantesDesde($nombres, $this->primerApellido, $this->segundoApellido);

        return substr($this->curp, 0, 4) === $prefijo
            && substr($this->curp, 13, 3) === $consonantes;
    }

    /**
     * Digito verificador que le corresponde a las primeras 17 posiciones.
     */
    public function toDigitoVerificador(): string
    {
        return Curp::digitoVerificador($this->curp);
    }

    public function toEstado(): Estado
    {
        return MexCore::Estado()->fromCurp($this->curp);
    }

    /**
     * @return array{
     *     curp: string,
     *     primerNombre: string,
     *     segundoNombre: string,
     *     primerApellido: string,
     *     segundoApellido: string
     * }
     */
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

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
