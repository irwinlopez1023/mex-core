<?php

declare(strict_types=1);

namespace Irwinlopez1023\MexCore;

final class MexCore
{
    private static ?EstadoQuery $estadoQuery = null;
    private static ?PersonaQuery $personaQuery = null;

    public static function Estado(): EstadoQuery
    {
        if (self::$estadoQuery === null) {
            self::$estadoQuery = new EstadoQuery();
        }

        return self::$estadoQuery;
    }

    public static function Persona(): PersonaQuery
    {
        if (self::$personaQuery === null) {
            self::$personaQuery = new PersonaQuery();
        }

        return self::$personaQuery;
    }

    private function __construct()
    {
    }
}
