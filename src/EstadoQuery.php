<?php

declare(strict_types=1);

namespace Irwinlopez1023\MexCore;

final class EstadoQuery
{
    /**
     * curp: posiciones 11 y 12 de la CURP.
     * abbr: abreviatura de uso comun, de largo variable (BC, JAL, TAMPS).
     * iso:  ISO 3166-2:MX, siempre tres letras. Para el id 33, que la norma
     *       no cubre, se usa NE, igual que la CURP.
     */
    private const DATA = [
        ['id' => 1,  'curp' => 'AS', 'abbr' => 'AGS', 'iso' => 'AGU', 'name' => 'Aguascalientes'],
        ['id' => 2,  'curp' => 'BC', 'abbr' => 'BC',  'iso' => 'BCN', 'name' => 'Baja California'],
        ['id' => 3,  'curp' => 'BS', 'abbr' => 'BCS', 'iso' => 'BCS', 'name' => 'Baja California Sur'],
        ['id' => 4,  'curp' => 'CC', 'abbr' => 'CAMP','iso' => 'CAM', 'name' => 'Campeche'],
        ['id' => 5,  'curp' => 'CL', 'abbr' => 'COAH','iso' => 'COA', 'name' => 'Coahuila'],
        ['id' => 6,  'curp' => 'CM', 'abbr' => 'COL', 'iso' => 'COL', 'name' => 'Colima'],
        ['id' => 7,  'curp' => 'CS', 'abbr' => 'CHIS','iso' => 'CHP', 'name' => 'Chiapas'],
        ['id' => 8,  'curp' => 'CH', 'abbr' => 'CHIH','iso' => 'CHH', 'name' => 'Chihuahua'],
        ['id' => 9,  'curp' => 'DF', 'abbr' => 'CDMX','iso' => 'CMX', 'name' => 'Ciudad de México'],
        ['id' => 10, 'curp' => 'DG', 'abbr' => 'DGO', 'iso' => 'DUR', 'name' => 'Durango'],
        ['id' => 11, 'curp' => 'GT', 'abbr' => 'GTO', 'iso' => 'GUA', 'name' => 'Guanajuato'],
        ['id' => 12, 'curp' => 'GR', 'abbr' => 'GRO', 'iso' => 'GRO', 'name' => 'Guerrero'],
        ['id' => 13, 'curp' => 'HG', 'abbr' => 'HGO', 'iso' => 'HID', 'name' => 'Hidalgo'],
        ['id' => 14, 'curp' => 'JC', 'abbr' => 'JAL', 'iso' => 'JAL', 'name' => 'Jalisco'],
        ['id' => 15, 'curp' => 'MS', 'abbr' => 'MEX', 'iso' => 'MEX', 'name' => 'Estado de México'],
        ['id' => 16, 'curp' => 'MC', 'abbr' => 'MICH','iso' => 'MIC', 'name' => 'Michoacán'],
        ['id' => 17, 'curp' => 'MN', 'abbr' => 'MOR', 'iso' => 'MOR', 'name' => 'Morelos'],
        ['id' => 18, 'curp' => 'NT', 'abbr' => 'NAY', 'iso' => 'NAY', 'name' => 'Nayarit'],
        ['id' => 19, 'curp' => 'NL', 'abbr' => 'NL',  'iso' => 'NLE', 'name' => 'Nuevo León'],
        ['id' => 20, 'curp' => 'OC', 'abbr' => 'OAX', 'iso' => 'OAX', 'name' => 'Oaxaca'],
        ['id' => 21, 'curp' => 'PL', 'abbr' => 'PUE', 'iso' => 'PUE', 'name' => 'Puebla'],
        ['id' => 22, 'curp' => 'QT', 'abbr' => 'QRO', 'iso' => 'QUE', 'name' => 'Querétaro'],
        ['id' => 23, 'curp' => 'QR', 'abbr' => 'QR',  'iso' => 'ROO', 'name' => 'Quintana Roo'],
        ['id' => 24, 'curp' => 'SP', 'abbr' => 'SLP', 'iso' => 'SLP', 'name' => 'San Luis Potosí'],
        ['id' => 25, 'curp' => 'SL', 'abbr' => 'SIN', 'iso' => 'SIN', 'name' => 'Sinaloa'],
        ['id' => 26, 'curp' => 'SR', 'abbr' => 'SON', 'iso' => 'SON', 'name' => 'Sonora'],
        ['id' => 27, 'curp' => 'TC', 'abbr' => 'TAB', 'iso' => 'TAB', 'name' => 'Tabasco'],
        ['id' => 28, 'curp' => 'TS', 'abbr' => 'TAMPS','iso' => 'TAM','name' => 'Tamaulipas'],
        ['id' => 29, 'curp' => 'TL', 'abbr' => 'TLAX','iso' => 'TLA', 'name' => 'Tlaxcala'],
        ['id' => 30, 'curp' => 'VZ', 'abbr' => 'VER', 'iso' => 'VER', 'name' => 'Veracruz'],
        ['id' => 31, 'curp' => 'YN', 'abbr' => 'YUC', 'iso' => 'YUC', 'name' => 'Yucatán'],
        ['id' => 32, 'curp' => 'ZS', 'abbr' => 'ZAC', 'iso' => 'ZAC', 'name' => 'Zacatecas'],
        ['id' => 33, 'curp' => 'NE', 'abbr' => 'EXT', 'iso' => 'NE',  'name' => 'Nacido en el Extranjero'],
    ];

    /**
     * Nombres alternos que aparecen en documentos oficiales. Incluye los
     * nombres constitucionales largos, que son los que trae el acta de
     * nacimiento, y las formas coloquiales de uso comun.
     */
    private const NAME_ALIASES = [
        // Formas coloquiales y administrativas.
        'MEXICO'                          => 15,
        'EDO MEX'                         => 15,
        'EDO DE MEXICO'                   => 15,
        'EDOMEX'                          => 15,
        'DF'                              => 9,
        'CDMX'                            => 9,
        'DISTRITO FEDERAL'                => 9,
        'EXTRANJERO'                      => 33,
        'NACIDO EN EL EXTRANJERO'          => 33,
        'NACIDA EN EL EXTRANJERO'          => 33,

        // Nombres constitucionales completos.
        'COAHUILA DE ZARAGOZA'            => 5,
        'MICHOACAN DE OCAMPO'             => 16,
        'VERACRUZ DE IGNACIO DE LA LLAVE' => 30,
        'QUERETARO DE ARTEAGA'            => 22,
        'ESTADO LIBRE Y SOBERANO DE MEXICO' => 15,
    ];

    /**
     * El codigo de la CURP para la capital sigue siendo DF, aunque la entidad
     * se llame Ciudad de Mexico desde 2016.
     */
    private const CURP_ALIASES = [
        'DF' => 9,
    ];

    /**
     * DF y EDOMEX no son la abreviatura oficial de nadie, pero llegan en la
     * columna de abreviatura de muchos sistemas heredados.
     */
    private const ABBR_ALIASES = [
        'DF'     => 9,
        'EDOMEX' => 15,
        'MEXICO' => 15,
        'QROO'   => 23,
        'BCN'    => 2,
    ];

    /** Forma de las primeras diez posiciones de una CURP. */
    private const FORMA_CURP = '/^[A-Z]{4}\d{6}/';

    /** @var array<string, array<string, int>>|null */
    private static ?array $index = null;

    /** @var list<Estado>|null */
    private static ?array $catalogo = null;

    /**
     * Acepta una CURP completa de 18 posiciones o el codigo de dos letras.
     */
    public function fromCurp(string $curp): Estado
    {
        return $this->buildFromId(
            $this->resolverCurp($curp) ?? throw new InvalidStateException($curp)
        );
    }

    /**
     * Clave numerica del INEGI. Acepta int o cadena de digitos, incluida la
     * forma con cero a la izquierda: 9, '9' y '09' son la misma entidad.
     */
    public function fromNumero(int|string $numero): Estado
    {
        return $this->buildFromId(
            $this->resolverNumero($numero) ?? throw new InvalidStateException((string) $numero)
        );
    }

    public function fromAbreviatura(string $abreviatura): Estado
    {
        return $this->buildFromId(
            $this->resolverAbreviatura($abreviatura) ?? throw new InvalidStateException($abreviatura)
        );
    }

    public function fromNombre(string $nombre): Estado
    {
        return $this->buildFromId(
            $this->resolverNombre($nombre) ?? throw new InvalidStateException($nombre)
        );
    }

    /**
     * Codigo ISO 3166-2:MX de tres letras, mas NE para el extranjero. A
     * diferencia de fromAbreviatura(), no acepta las formas de uso comun:
     * 'BC' falla aqui y 'BCN' es lo unico valido.
     */
    public function fromIso(string $iso): Estado
    {
        return $this->buildFromId(
            $this->resolverIso($iso) ?? throw new InvalidStateException($iso)
        );
    }

    /**
     * Detecta el formato y resuelve. Pensado para columnas de origen mixto,
     * donde una misma celda puede traer 24, 'SP', 'SLP' o 'San Luis Potosi'.
     *
     * El orden es numero, CURP, abreviatura, nombre. Los codigos de dos letras
     * que tambien son abreviatura (BC, NL, QR) apuntan a la misma entidad en
     * ambos catalogos, asi que la precedencia no cambia el resultado.
     */
    public function desde(int|string $valor): Estado
    {
        return $this->intentarDesde($valor)
            ?? throw new InvalidStateException((string) $valor);
    }

    /**
     * Igual que desde() pero devuelve null en lugar de lanzar. Evita un
     * try/catch por renglon al procesar cargas masivas.
     */
    public function intentarDesde(int|string $valor): ?Estado
    {
        $id = $this->resolverNumero($valor)
            ?? $this->resolverCurp((string) $valor)
            ?? $this->resolverAbreviatura((string) $valor)
            ?? $this->resolverNombre((string) $valor);

        return $id === null ? null : $this->buildFromId($id);
    }

    public function existe(int|string $valor): bool
    {
        return $this->intentarDesde($valor) !== null;
    }

    /**
     * Las 32 entidades mas Nacido en el Extranjero, en orden de clave.
     *
     * @return list<Estado>
     */
    public function listar(): array
    {
        if (self::$catalogo !== null) {
            return self::$catalogo;
        }

        $estados = [];

        foreach (self::DATA as $fila) {
            $estados[] = self::construir($fila);
        }

        return self::$catalogo = $estados;
    }

    // --- Resolucion -----------------------------------------------------

    private function resolverCurp(string $curp): ?int
    {
        $normalizado = self::normalizeCurp($curp);

        // Solo se leen las posiciones 11 y 12 cuando la cadena tiene de veras
        // forma de CURP. Antes bastaba con medir 18 caracteres, asi que una
        // frase larga cuyos bytes 11 y 12 coincidieran con un codigo de
        // entidad se resolvia por accidente.
        if (strlen($normalizado) >= 18 && preg_match(self::FORMA_CURP, $normalizado) === 1) {
            $normalizado = substr($normalizado, 11, 2);
        }

        return self::getIndex()['byCurp'][$normalizado] ?? null;
    }

    private function resolverNumero(int|string $numero): ?int
    {
        if (is_string($numero)) {
            $numero = trim($numero);

            // ctype_digit descarta '24abc', '24.9' y '-5', que (int) habria
            // convertido en 24, 24 y -5 sin avisar.
            if ($numero === '' || !ctype_digit($numero)) {
                return null;
            }
        }

        $id = (int) $numero;

        return isset(self::getIndex()['byNumber'][$id]) ? $id : null;
    }

    private function resolverAbreviatura(string $abreviatura): ?int
    {
        return self::getIndex()['byAbbr'][self::normalizeAbbr($abreviatura)] ?? null;
    }

    private function resolverNombre(string $nombre): ?int
    {
        return self::getIndex()['byName'][self::normalizeName($nombre)] ?? null;
    }

    private function resolverIso(string $iso): ?int
    {
        return self::getIndex()['byIso'][self::normalizeAbbr($iso)] ?? null;
    }

    private function buildFromId(int $id): Estado
    {
        return self::construir(self::getIndex()['rows'][$id]);
    }

    /**
     * @param array{id: int, curp: string, abbr: string, iso: string, name: string} $fila
     */
    private static function construir(array $fila): Estado
    {
        return new Estado($fila['id'], $fila['name'], $fila['curp'], $fila['abbr'], $fila['iso']);
    }

    /**
     * @return array{
     *     rows: array<int, array{id: int, curp: string, abbr: string, iso: string, name: string}>,
     *     byNumber: array<int, int>,
     *     byCurp: array<string, int>,
     *     byAbbr: array<string, int>,
     *     byIso: array<string, int>,
     *     byName: array<string, int>
     * }
     */
    private static function getIndex(): array
    {
        if (self::$index !== null) {
            return self::$index;
        }

        $index = [
            'rows'     => [],
            'byNumber' => [],
            'byCurp'   => [],
            'byAbbr'   => [],
            'byIso'    => [],
            'byName'   => [],
        ];

        // Se indexa por la clave declarada en cada fila, no por su posicion en
        // el arreglo, para que reordenar DATA no desplace las entidades.
        foreach (self::DATA as $fila) {
            $id = $fila['id'];

            $index['rows'][$id]     = $fila;
            $index['byNumber'][$id] = $id;

            $index['byCurp'][self::normalizeCurp($fila['curp'])] = $id;
            $index['byAbbr'][self::normalizeAbbr($fila['abbr'])] = $id;
            $index['byIso'][self::normalizeAbbr($fila['iso'])]   = $id;
            $index['byName'][self::normalizeName($fila['name'])] = $id;

            // El codigo ISO tambien se acepta donde se espera una abreviatura,
            // asi que fromAbreviatura('BCN') y desde('BCN') resuelven. No hay
            // colision: donde el ISO coincide con la abreviatura (JAL, SLP,
            // MOR) apuntan a la misma entidad.
            $index['byAbbr'][self::normalizeAbbr($fila['iso'])] = $id;
        }

        foreach (self::NAME_ALIASES as $alias => $id) {
            $index['byName'][self::normalizeName((string) $alias)] = $id;
        }

        foreach (self::CURP_ALIASES as $alias => $id) {
            $index['byCurp'][self::normalizeCurp((string) $alias)] = $id;
        }

        foreach (self::ABBR_ALIASES as $alias => $id) {
            $index['byAbbr'][self::normalizeAbbr((string) $alias)] = $id;
        }

        return self::$index = $index;
    }

    // --- Normalizacion --------------------------------------------------

    /**
     * Una CURP no contiene espacios, asi que se quitan todos. Eso rescata las
     * cadenas partidas al copiar de un PDF: 'SOSR650222 MPLSNC03' vuelve a
     * alinear sus posiciones 11 y 12.
     */
    private static function normalizeCurp(string $value): string
    {
        return str_replace(' ', '', mb_strtoupper(self::colapsar($value), 'UTF-8'));
    }

    /**
     * Se quitan puntos y espacios, asi que 'S.L.P.', 'S. L. P.' y 'slp'
     * llegan todos a SLP.
     */
    private static function normalizeAbbr(string $value): string
    {
        return str_replace(
            ['.', ' ', '-'],
            '',
            mb_strtoupper(self::colapsar($value), 'UTF-8')
        );
    }

    private static function normalizeName(string $value): string
    {
        return self::removeAccents(mb_strtoupper(self::colapsar($value), 'UTF-8'));
    }

    /**
     * Colapsa cualquier separador unicode a un espacio simple. Cubre el
     * espacio duro U+00A0, que es lo que llega al copiar de un PDF, y los
     * espacios dobles de 'Baja  California'.
     */
    private static function colapsar(string $value): string
    {
        $colapsado = preg_replace('/[\s\p{Z}\p{Cc}]+/u', ' ', $value);

        if ($colapsado === null) {
            // El texto no es UTF-8 valido: se cae al colapso ASCII.
            $colapsado = preg_replace('/\s+/', ' ', $value) ?? $value;
        }

        return trim($colapsado);
    }

    private static function removeAccents(string $value): string
    {
        return str_replace(
            ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'À', 'È', 'Ì', 'Ò', 'Ù', 'Ñ'],
            ['A', 'E', 'I', 'O', 'U', 'U', 'A', 'E', 'I', 'O', 'U', 'N'],
            $value
        );
    }

    // --- Alias en ingles (compatibilidad) -------------------------------

    public function fromNumber(int|string $number): Estado
    {
        return $this->fromNumero($number);
    }

    public function fromAbbr(string $abbr): Estado
    {
        return $this->fromAbreviatura($abbr);
    }

    public function fromName(string $name): Estado
    {
        return $this->fromNombre($name);
    }
}
