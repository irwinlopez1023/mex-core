<?php

declare(strict_types=1);

namespace Irwinlopez1023\MexCore;

final class PersonaQuery
{
    /**
     * Palabras que actuan como "pegamento" bidireccional: se unen tanto a la
     * palabra anterior como a la siguiente ("MARIA DEL ROCIO").
     */
    private const CONECTORES_DEFAULT = [
        'DE', 'DEL', 'LA', 'LAS', 'LOS', 'Y', 'MAC', 'MC', 'VAN', 'VON',
    ];

    /**
     * Abreviaturas comunes en documentos oficiales. A diferencia de los
     * conectores, solo pegan hacia adelante: "MA. GUADALUPE" es un bloque,
     * pero "JOSE MA. DEL CARMEN" separa JOSE de MA. DEL CARMEN.
     */
    private const ABREVIATURAS_DEFAULT = [
        'MA', 'M', 'J', 'GPE', 'FCO', 'FCA', 'ANT',
    ];

    private const TIPO_NOMBRE      = 0;
    private const TIPO_CONECTOR    = 1;
    private const TIPO_ABREVIATURA = 2;

    /** @var array<string, true> */
    private array $conectores;

    /** @var array<string, true> */
    private array $abreviaturas;

    /**
     * @param list<string>|null $conectores
     * @param list<string>|null $abreviaturas
     */
    public function __construct(?array $conectores = null, ?array $abreviaturas = null)
    {
        $this->conectores   = self::indexar($conectores ?? self::CONECTORES_DEFAULT);
        $this->abreviaturas = self::indexar($abreviaturas ?? self::ABREVIATURAS_DEFAULT);
    }

    /**
     * Devuelve una copia con otro diccionario de conectores.
     *
     * @param list<string> $conectores
     */
    public function withConectores(array $conectores): self
    {
        $clone             = clone $this;
        $clone->conectores = self::indexar($conectores);

        return $clone;
    }

    /**
     * Devuelve una copia con otro diccionario de abreviaturas.
     *
     * @param list<string> $abreviaturas
     */
    public function withAbreviaturas(array $abreviaturas): self
    {
        $clone               = clone $this;
        $clone->abreviaturas = self::indexar($abreviaturas);

        return $clone;
    }

    public function fromData(
        string $curp,
        string $nombres,
        string $primerApellido,
        string $segundoApellido,
        bool   $mantenerPunto = true,
    ): Persona {
        $bloques = $this->separarNombres($nombres, $mantenerPunto);

        return new Persona(
            self::normalizeCurp($curp),
            $bloques[0] ?? '',
            count($bloques) > 1 ? implode(' ', array_slice($bloques, 1)) : '',
            self::normalizeText($primerApellido),
            self::normalizeText($segundoApellido),
            $bloques,
        );
    }

    /**
     * Igual que fromData() pero con llaves nombradas, para evitar invertir
     * apellido paterno y materno por accidente.
     *
     * @param array{
     *     curp?: string,
     *     nombres?: string,
     *     primerApellido?: string,
     *     segundoApellido?: string,
     *     mantenerPunto?: bool
     * } $datos
     */
    public function fromArray(array $datos): Persona
    {
        return $this->fromData(
            (string) ($datos['curp'] ?? ''),
            (string) ($datos['nombres'] ?? ''),
            (string) ($datos['primerApellido'] ?? ''),
            (string) ($datos['segundoApellido'] ?? ''),
            (bool) ($datos['mantenerPunto'] ?? true),
        );
    }

    /**
     * Aplica la logica de pegamento y devuelve los bloques de nombre
     * detectados, sin colapsarlos en primer/segundo nombre.
     *
     * "MARIA DEL ROCIO ALEJANDRA" => ['MARIA DEL ROCIO', 'ALEJANDRA']
     *
     * @return list<string>
     */
    public function separarNombres(string $nombres, bool $mantenerPunto = true): array
    {
        if (!$mantenerPunto) {
            $nombres = str_replace('.', '', $nombres);
        }

        $raw     = self::normalizeText($nombres);
        $tokens  = $raw === '' ? [] : explode(' ', $raw);

        /** @var list<list<string>> $bloques */
        $bloques = [];
        /** @var list<string> $actual */
        $actual = [];

        // $pendiente: el bloque termina en pegamento y espera un nombre real.
        // $yaPegado:  el bloque ya consumio su unico grupo de pegamento.
        $pendiente = false;
        $yaPegado  = false;

        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }

            $tipo = $this->clasificar($token);

            if ($tipo === self::TIPO_CONECTOR) {
                if ($actual === []) {
                    $actual = [$token];
                } elseif ($pendiente) {
                    // Conectores consecutivos: "DE" + "LOS" siguen en el mismo bloque.
                    $actual[] = $token;
                } elseif ($yaPegado) {
                    // El bloque ya esta completo: un conector nuevo abre otro nombre.
                    $bloques[] = $actual;
                    $actual    = [$token];
                } else {
                    // Pegado hacia atras: "MARIA" + "DEL".
                    $actual[] = $token;
                }

                $pendiente = true;
                $yaPegado  = true;

                continue;
            }

            if ($tipo === self::TIPO_ABREVIATURA) {
                if ($actual === []) {
                    $actual = [$token];
                } elseif ($pendiente) {
                    $actual[] = $token;
                } else {
                    // Las abreviaturas no pegan hacia atras: abren bloque nuevo.
                    $bloques[] = $actual;
                    $actual    = [$token];
                }

                $pendiente = true;
                $yaPegado  = true;

                continue;
            }

            if ($actual === []) {
                $actual   = [$token];
                $yaPegado = false;
            } elseif ($pendiente) {
                // Este nombre cierra el grupo de pegamento abierto.
                $actual[] = $token;
            } else {
                $bloques[] = $actual;
                $actual    = [$token];
                $yaPegado  = false;
            }

            $pendiente = false;
        }

        if ($actual !== []) {
            // Pegamento colgante por datos truncados: "MARIA DE" => "MARIA".
            if ($pendiente && $this->contieneNombre($actual)) {
                while ($this->clasificar($actual[count($actual) - 1]) !== self::TIPO_NOMBRE) {
                    array_pop($actual);
                }
            }

            $bloques[] = $actual;
        }

        return array_map(
            static fn (array $tokens): string => implode(' ', $tokens),
            $bloques,
        );
    }

    private function clasificar(string $token): int
    {
        $key = str_replace('.', '', $token);

        if ($key === '') {
            return self::TIPO_ABREVIATURA;
        }

        if (isset($this->conectores[$key])) {
            return self::TIPO_CONECTOR;
        }

        if (isset($this->abreviaturas[$key])
            || mb_strlen($key, 'UTF-8') === 1
            || substr($token, -1) === '.'
        ) {
            return self::TIPO_ABREVIATURA;
        }

        return self::TIPO_NOMBRE;
    }

    /**
     * @param list<string> $tokens
     */
    private function contieneNombre(array $tokens): bool
    {
        foreach ($tokens as $token) {
            if ($this->clasificar($token) === self::TIPO_NOMBRE) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $palabras
     *
     * @return array<string, true>
     */
    private static function indexar(array $palabras): array
    {
        $index = [];

        foreach ($palabras as $palabra) {
            $key = str_replace('.', '', self::normalizeText((string) $palabra));

            if ($key !== '') {
                $index[$key] = true;
            }
        }

        return $index;
    }

    /**
     * Mayusculas, y colapso de cualquier separador unicode (incluido el espacio
     * duro U+00A0 que llega al copiar de PDFs) a un solo espacio simple.
     */
    private static function normalizeText(string $value): string
    {
        $collapsed = preg_replace('/[\s\p{Z}\p{Cc}]+/u', ' ', $value);

        if ($collapsed === null) {
            // El texto no es UTF-8 valido: caemos al colapso ASCII.
            $collapsed = preg_replace('/\s+/', ' ', $value) ?? $value;
        }

        return trim(mb_strtoupper($collapsed, 'UTF-8'));
    }

    /**
     * La CURP no lleva espacios, asi que se quitan todos en lugar de
     * colapsarlos. Mismo criterio que Curp y EstadoQuery, para que una CURP
     * copiada de un PDF con un salto en medio siga resolviendo el estado y
     * pasando la validacion.
     */
    private static function normalizeCurp(string $value): string
    {
        return str_replace(' ', '', self::normalizeText($value));
    }
}
