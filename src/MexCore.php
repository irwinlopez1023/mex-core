<?php

declare(strict_types=1);

namespace Irwinlopez1023\MexCore;

final class MexCore
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

    private const NAME_ALIASES = [
        'MEXICO'          => 15,
        'EDO MEX'         => 15,
        'EDOMEX'          => 15,
        'DF'              => 9,
        'DISTRITO FEDERAL'=> 9,
        'EXTRANJERO'      => 33,
    ];

    private const CURP_ALIASES = [
        'DF' => 9,
    ];

    private static ?array $index = null;

    private int $id;
    private string $name;
    private string $curp;
    private string $abbr;

    private function __construct(int $id, string $name, string $curp, string $abbr)
    {
        $this->id   = $id;
        $this->name = $name;
        $this->curp = $curp;
        $this->abbr = $abbr;
    }

    public static function fromCurp(string $curp): self
    {
        $normalized = self::normalizeCurp($curp);

        $key = strlen($normalized) >= 18
            ? substr($normalized, 11, 2)
            : $normalized;

        $index = self::getIndex();
        $id    = $index['byCurp'][$key] ?? null;

        if ($id === null) {
            throw new InvalidStateException($curp);
        }

        return self::buildFromId($id);
    }

    public static function fromNumber(int|string $number): self
    {
        $id = (int) $number;

        if ($id < 1 || $id > 33) {
            throw new InvalidStateException((string) $number);
        }

        return self::buildFromId($id);
    }

    public static function fromAbbr(string $abbr): self
    {
        $index = self::getIndex();
        $key   = self::normalizeAbbr($abbr);

        $id = $index['byAbbr'][$key] ?? null;

        if ($id === null) {
            throw new InvalidStateException($abbr);
        }

        return self::buildFromId($id);
    }

    public static function fromName(string $name): self
    {
        $index = self::getIndex();
        $key   = self::normalizeName($name);

        $id = $index['byName'][$key] ?? null;

        if ($id === null) {
            throw new InvalidStateException($name);
        }

        return self::buildFromId($id);
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

    private static function buildFromId(int $id): self
    {
        $state = self::DATA[$id - 1];

        return new self($state['id'], $state['name'], $state['curp'], $state['abbr']);
    }

    private static function getIndex(): array
    {
        if (self::$index !== null) {
            return self::$index;
        }

        self::$index = [
            'byNumber' => [],
            'byCurp'   => [],
            'byAbbr'   => [],
            'byName'   => [],
        ];

        foreach (self::DATA as $state) {
            $id = $state['id'];

            self::$index['byNumber'][$id] = $id;
            self::$index['byCurp'][self::normalizeCurp($state['curp'])] = $id;

            $abbrKey = self::normalizeAbbr($state['abbr']);
            self::$index['byAbbr'][$abbrKey] = $id;

            $nameKey = self::normalizeName($state['name']);
            self::$index['byName'][$nameKey] = $id;
        }

        foreach (self::NAME_ALIASES as $alias => $id) {
            self::$index['byName'][self::normalizeName($alias)] = $id;
        }

        foreach (self::CURP_ALIASES as $alias => $id) {
            self::$index['byCurp'][self::normalizeCurp($alias)] = $id;
        }

        return self::$index;
    }

    private static function normalizeCurp(string $value): string
    {
        return strtoupper(trim($value));
    }

    private static function normalizeAbbr(string $value): string
    {
        return str_replace('.', '', strtoupper(trim($value)));
    }

    private static function normalizeName(string $value): string
    {
        return self::removeAccents(mb_strtoupper(trim($value), 'UTF-8'));
    }

    private static function removeAccents(string $value): string
    {
        $from = ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'];
        $to   = ['A', 'E', 'I', 'O', 'U', 'U', 'N'];

        return str_replace($from, $to, $value);
    }
}
