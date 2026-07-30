<?php

declare(strict_types=1);

namespace Irwinlopez1023\MexCore;

/**
 * Reglas del Instructivo Normativo de RENAPO para la CURP.
 *
 * Estructura de las 18 posiciones:
 *   0-3   letras derivadas de apellidos y nombre
 *   4-9   fecha de nacimiento AAMMDD
 *   10    sexo (H, M, X)
 *   11-12 entidad de nacimiento
 *   13-15 consonantes internas de apellidos y nombre
 *   16    homoclave: digito si nacio antes del 2000, letra a partir del 2000
 *   17    digito verificador
 */
final class Curp
{
    /** Incluye la Ñ porque el valor numerico de las letras posteriores depende de ella. */
    private const TABLA = '0123456789ABCDEFGHIJKLMNÑOPQRSTUVWXYZ';

    private const VOCALES = ['A', 'E', 'I', 'O', 'U'];

    /**
     * Particulas que RENAPO ignora al derivar las letras de un apellido.
     */
    private const PARTICULAS = [
        'DA', 'DAS', 'DE', 'DEL', 'DER', 'DI', 'DIE', 'DD', 'EL',
        'LA', 'LAS', 'LE', 'LES', 'LOS', 'MAC', 'MC', 'VAN', 'VON', 'Y',
    ];

    /**
     * Primer nombre que se omite cuando la persona tiene mas de un nombre:
     * "MARIA DEL ROCIO" deriva de ROCIO, y "MA. TERESA" deriva de TERESA.
     */
    private const NOMBRES_OMITIDOS = ['MARIA', 'MA', 'JOSE', 'J', 'M'];

    /**
     * Si las primeras cuatro letras forman una de estas palabras, la segunda
     * se sustituye por X.
     */
    private const INCONVENIENTES = [
        'BACA', 'BAKA', 'BUEI', 'BUEY', 'CACA', 'CACO', 'CAGA', 'CAGO', 'CAKA',
        'CAKO', 'COGE', 'COGI', 'COJA', 'COJE', 'COJI', 'COJO', 'COLA', 'CULO',
        'FALO', 'FETO', 'GETA', 'GUEI', 'GUEY', 'JOTO', 'KACA', 'KACO', 'KAGA',
        'KAGO', 'KAKA', 'KAKO', 'KOGE', 'KOGI', 'KOJA', 'KOJE', 'KOJI', 'KOJO',
        'KOLA', 'KULO', 'LILO', 'LOCA', 'LOCO', 'LOKA', 'LOKO', 'MAME', 'MAMO',
        'MEAR', 'MEAS', 'MEON', 'MIAR', 'MION', 'MOCO', 'MOKO', 'MULA', 'MULO',
        'NACA', 'NACO', 'PEDA', 'PEDO', 'PENE', 'PIPI', 'PITO', 'POPO', 'PUTA',
        'PUTO', 'QULO', 'RATA', 'ROBA', 'ROBE', 'ROBO', 'RUIN', 'SENO', 'TETA',
        'VACA', 'VAGA', 'VAGO', 'VAKA', 'VUEI', 'VUEY', 'WUEI', 'WUEY',
    ];

    private function __construct()
    {
    }

    /**
     * Digito verificador de las primeras 17 posiciones. Cadena vacia si no hay
     * suficientes caracteres para calcularlo.
     */
    public static function digitoVerificador(string $curp): string
    {
        $curp = self::normalizar($curp);

        if (strlen($curp) < 17) {
            return '';
        }

        $valores = self::valores();
        $suma    = 0;

        for ($i = 0; $i < 17; $i++) {
            $suma += ($valores[$curp[$i]] ?? 0) * (18 - $i);
        }

        return (string) ((10 - ($suma % 10)) % 10);
    }

    /**
     * Estructura, fecha real, entidad existente y digito verificador correcto.
     */
    public static function esValida(string $curp): bool
    {
        $curp = self::normalizar($curp);

        if (preg_match('/^[A-Z]{4}\d{6}[HMX][A-Z]{2}[A-Z]{3}[0-9A-Z]\d$/', $curp) !== 1) {
            return false;
        }

        if (self::fechaNacimiento($curp) === null) {
            return false;
        }

        if (self::digitoVerificador($curp) !== $curp[17]) {
            return false;
        }

        try {
            MexCore::Estado()->fromCurp($curp);
        } catch (InvalidStateException $e) {
            return false;
        }

        return true;
    }

    public static function sexo(string $curp): string
    {
        $curp = self::normalizar($curp);

        if (strlen($curp) !== 18) {
            return '';
        }

        return in_array($curp[10], ['H', 'M', 'X'], true) ? $curp[10] : '';
    }

    /**
     * El siglo se deduce del homoclave: digito para el 1900, letra para el 2000.
     */
    public static function fechaNacimiento(string $curp): ?\DateTimeImmutable
    {
        $curp = self::normalizar($curp);

        if (strlen($curp) !== 18) {
            return null;
        }

        $anio = substr($curp, 4, 2);
        $mes  = substr($curp, 6, 2);
        $dia  = substr($curp, 8, 2);

        if (!ctype_digit($anio . $mes . $dia)) {
            return null;
        }

        $siglo    = ctype_digit($curp[16]) ? '19' : '20';
        $esperado = $siglo . $anio . '-' . $mes . '-' . $dia;

        $fecha = \DateTimeImmutable::createFromFormat('!Y-m-d', $esperado);

        // createFromFormat rueda las fechas inexistentes ("02-31" => 3 de marzo),
        // asi que comparamos contra la cadena original para descartarlas.
        if ($fecha === false || $fecha->format('Y-m-d') !== $esperado) {
            return null;
        }

        return $fecha;
    }

    /**
     * Las cuatro primeras letras derivadas de los apellidos y el nombre.
     */
    public static function prefijoDesde(string $nombres, string $primerApellido, string $segundoApellido): string
    {
        $ap1 = self::palabraClave($primerApellido);
        $ap2 = self::palabraClave($segundoApellido);
        $nom = self::palabraClave($nombres, true);

        $letras = ($ap1 === '' ? 'X' : $ap1[0])
            . self::vocalInterna($ap1)
            . ($ap2 === '' ? 'X' : $ap2[0])
            . ($nom === '' ? 'X' : $nom[0]);

        if (in_array($letras, self::INCONVENIENTES, true)) {
            $letras[1] = 'X';
        }

        return $letras;
    }

    /**
     * Las tres consonantes internas (posiciones 13 a 15).
     */
    public static function consonantesDesde(string $nombres, string $primerApellido, string $segundoApellido): string
    {
        return self::consonanteInterna(self::palabraClave($primerApellido))
            . self::consonanteInterna(self::palabraClave($segundoApellido))
            . self::consonanteInterna(self::palabraClave($nombres, true));
    }

    /**
     * Palabra de la que se derivan las letras: descarta particulas y, para los
     * nombres, omite MARIA o JOSE cuando hay un nombre posterior.
     */
    private static function palabraClave(string $texto, bool $omitirPrimero = false): string
    {
        $limpio   = self::limpiar($texto);
        $palabras = $limpio === '' ? [] : explode(' ', $limpio);

        $utiles = [];

        foreach ($palabras as $palabra) {
            if ($palabra !== '' && !in_array($palabra, self::PARTICULAS, true)) {
                $utiles[] = $palabra;
            }
        }

        if ($omitirPrimero
            && count($utiles) > 1
            && in_array($utiles[0], self::NOMBRES_OMITIDOS, true)
        ) {
            array_shift($utiles);
        }

        return $utiles[0] ?? '';
    }

    private static function vocalInterna(string $palabra): string
    {
        $largo = strlen($palabra);

        for ($i = 1; $i < $largo; $i++) {
            if (in_array($palabra[$i], self::VOCALES, true)) {
                return $palabra[$i];
            }
        }

        return 'X';
    }

    private static function consonanteInterna(string $palabra): string
    {
        $largo = strlen($palabra);

        for ($i = 1; $i < $largo; $i++) {
            if (!in_array($palabra[$i], self::VOCALES, true)) {
                return $palabra[$i];
            }
        }

        return 'X';
    }

    /**
     * Mayusculas sin acentos, con Ñ convertida en X y cualquier otro caracter
     * convertido en separador. El resultado es ASCII puro.
     */
    private static function limpiar(string $texto): string
    {
        $texto = mb_strtoupper($texto, 'UTF-8');

        $texto = str_replace(
            ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'À', 'È', 'Ì', 'Ò', 'Ù', 'Ñ'],
            ['A', 'E', 'I', 'O', 'U', 'U', 'A', 'E', 'I', 'O', 'U', 'X'],
            $texto
        );

        // Si el texto no es UTF-8 valido, preg_replace con /u devuelve null y
        // caemos al reemplazo ASCII sobre la cadena original.
        $solo = preg_replace('/[^A-Z]+/u', ' ', $texto);

        if ($solo === null) {
            $solo = preg_replace('/[^A-Z]+/', ' ', $texto) ?? '';
        }

        return trim($solo);
    }

    /**
     * Mayusculas y sin ningun separador. Una CURP no contiene espacios, asi
     * que se eliminan todos en lugar de solo recortar los extremos: eso
     * rescata las cadenas partidas al copiar de un PDF, y mantiene el mismo
     * criterio que EstadoQuery, para que una entrada que resuelve el estado
     * tambien pase por tieneCurpValida().
     */
    private static function normalizar(string $curp): string
    {
        $limpio = preg_replace('/[\s\p{Z}\p{Cc}]+/u', '', $curp);

        if ($limpio === null) {
            $limpio = preg_replace('/\s+/', '', $curp) ?? $curp;
        }

        return mb_strtoupper($limpio, 'UTF-8');
    }

    /**
     * @return array<string, int>
     */
    private static function valores(): array
    {
        static $map = null;

        if ($map === null) {
            $map = [];

            foreach (mb_str_split(self::TABLA, 1, 'UTF-8') as $i => $caracter) {
                $map[$caracter] = $i;
            }
        }

        return $map;
    }
}
