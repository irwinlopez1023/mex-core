<?php

declare(strict_types=1);

namespace Irwinlopez1023\MexCore;

final class EstadoQuery
{
    private const DATA = [
        ['id' => 1,  'curp' => 'AS', 'abbr' => 'AGS', 'name' => 'Aguascalientes'],
        ['id' => 2,  'curp' => 'BC', 'abbr' => 'BC',  'name' => 'Baja California'],
        ['id' => 3,  'curp' => 'BS', 'abbr' => 'BCS', 'name' => 'Baja California Sur'],
        ['id' => 4,  'curp' => 'CC', 'abbr' => 'CAMP','name' => 'Campeche'],
        ['id' => 5,  'curp' => 'CL', 'abbr' => 'COAH','name' => 'Coahuila'],
        ['id' => 6,  'curp' => 'CM', 'abbr' => 'COL', 'name' => 'Colima'],
        ['id' => 7,  'curp' => 'CS', 'abbr' => 'CHIS','name' => 'Chiapas'],
        ['id' => 8,  'curp' => 'CH', 'abbr' => 'CHIH','name' => 'Chihuahua'],
        ['id' => 9,  'curp' => 'DF', 'abbr' => 'CDMX','name' => 'Ciudad de México'],
        ['id' => 10, 'curp' => 'DG', 'abbr' => 'DGO', 'name' => 'Durango'],
        ['id' => 11, 'curp' => 'GT', 'abbr' => 'GTO', 'name' => 'Guanajuato'],
        ['id' => 12, 'curp' => 'GR', 'abbr' => 'GRO', 'name' => 'Guerrero'],
        ['id' => 13, 'curp' => 'HG', 'abbr' => 'HGO', 'name' => 'Hidalgo'],
        ['id' => 14, 'curp' => 'JC', 'abbr' => 'JAL', 'name' => 'Jalisco'],
        ['id' => 15, 'curp' => 'MS', 'abbr' => 'MEX', 'name' => 'Estado de México'],
        ['id' => 16, 'curp' => 'MC', 'abbr' => 'MICH','name' => 'Michoacán'],
        ['id' => 17, 'curp' => 'MN', 'abbr' => 'MOR', 'name' => 'Morelos'],
        ['id' => 18, 'curp' => 'NT', 'abbr' => 'NAY', 'name' => 'Nayarit'],
        ['id' => 19, 'curp' => 'NL', 'abbr' => 'NL',  'name' => 'Nuevo León'],
        ['id' => 20, 'curp' => 'OC', 'abbr' => 'OAX', 'name' => 'Oaxaca'],
        ['id' => 21, 'curp' => 'PL', 'abbr' => 'PUE', 'name' => 'Puebla'],
        ['id' => 22, 'curp' => 'QT', 'abbr' => 'QRO', 'name' => 'Querétaro'],
        ['id' => 23, 'curp' => 'QR', 'abbr' => 'QR',  'name' => 'Quintana Roo'],
        ['id' => 24, 'curp' => 'SP', 'abbr' => 'SLP', 'name' => 'San Luis Potosí'],
        ['id' => 25, 'curp' => 'SL', 'abbr' => 'SIN', 'name' => 'Sinaloa'],
        ['id' => 26, 'curp' => 'SR', 'abbr' => 'SON', 'name' => 'Sonora'],
        ['id' => 27, 'curp' => 'TC', 'abbr' => 'TAB', 'name' => 'Tabasco'],
        ['id' => 28, 'curp' => 'TS', 'abbr' => 'TAMPS','name' => 'Tamaulipas'],
        ['id' => 29, 'curp' => 'TL', 'abbr' => 'TLAX','name' => 'Tlaxcala'],
        ['id' => 30, 'curp' => 'VZ', 'abbr' => 'VER', 'name' => 'Veracruz'],
        ['id' => 31, 'curp' => 'YN', 'abbr' => 'YUC', 'name' => 'Yucatán'],
        ['id' => 32, 'curp' => 'ZS', 'abbr' => 'ZAC', 'name' => 'Zacatecas'],
        ['id' => 33, 'curp' => 'NE', 'abbr' => 'EXT', 'name' => 'Nacido en el Extranjero'],
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

    private function buildFromId(int $id): Estado
    {
        return self::construir(self::getIndex()['rows'][$id]);
    }

    /**
     * @param array{id: int, curp: string, abbr: string, name: string} $fila
     */
    private static function construir(array $fila): Estado
    {
        return new Estado($fila['id'], $fila['name'], $fila['curp'], $fila['abbr']);
    }

    /**
     * @return array{
     *     rows: array<int, array{id: int, curp: string, abbr: string, name: string}>,
     *     byNumber: array<int, int>,
     *     byCurp: array<string, int>,
     *     byAbbr: array<string, int>,
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
            $index['byName'][self::normalizeName($fila['name'])] = $id;
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
