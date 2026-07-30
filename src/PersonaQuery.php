<?php

declare(strict_types=1);

namespace Irwinlopez1023\MexCore;

final class PersonaQuery
{
    private const CONECTORES = [
        'DE', 'DEL', 'LA', 'LAS', 'LOS', 'Y', 'MAC', 'MC', 'VAN', 'VON',
    ];

    public function fromData(
        string $curp,
        string $nombres,
        string $primerApellido,
        string $segundoApellido,
        bool   $mantenerPunto = true,
    ): Persona {
        if (!$mantenerPunto) {
            $nombres = str_replace('.', '', $nombres);
        }

        $curpRaw            = self::normalizeText($curp);
        $nombresRaw         = self::normalizeText($nombres);
        $primerApellidoRaw  = self::normalizeText($primerApellido);
        $segundoApellidoRaw = self::normalizeText($segundoApellido);

        $primerNombre  = '';
        $segundoNombre = '';

        $palabras = preg_split('/\s+/', $nombresRaw);

        if (!empty($palabras)) {
            $nombresProcesados = [];
            $bloqueActual      = '';
            $enConector        = false;

            foreach ($palabras as $palabra) {
                if ($palabra === '') {
                    continue;
                }

                $palabraLimpia = str_replace('.', '', $palabra);

                if (in_array($palabraLimpia, self::CONECTORES, true)) {
                    $bloqueActual .= ($bloqueActual === '' ? '' : ' ') . $palabra;
                    $enConector = true;
                } else {
                    if ($bloqueActual === '') {
                        $bloqueActual = $palabra;
                        $enConector   = false;
                    } elseif ($enConector) {
                        $bloqueActual .= ' ' . $palabra;
                        $enConector = false;
                    } else {
                        $nombresProcesados[] = $bloqueActual;
                        $bloqueActual        = $palabra;
                    }
                }
            }

            if ($bloqueActual !== '') {
                $nombresProcesados[] = $bloqueActual;
            }

            $primerNombre  = $nombresProcesados[0] ?? '';
            $segundoNombre = count($nombresProcesados) > 1
                ? implode(' ', array_slice($nombresProcesados, 1))
                : '';
        }

        return new Persona(
            $curpRaw,
            $primerNombre,
            $segundoNombre,
            $primerApellidoRaw,
            $segundoApellidoRaw,
        );
    }

    private static function normalizeText(string $value): string
    {
        return trim(mb_strtoupper($value, 'UTF-8'));
    }
}
