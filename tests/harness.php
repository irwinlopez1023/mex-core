<?php

declare(strict_types=1);

/**
 * Harness de aserciones compartido por las suites de MexCore.
 *
 * Cada suite lo incluye, llama a suite() para abrir una seccion, acumula
 * aserciones con check() y excepcion(), y termina con resumen(), que
 * devuelve el codigo de salida.
 *
 * Un archivo de suite se puede correr solo:
 *
 *     php test_estados.php
 *
 * o dejar que test.php incluya varias y saque un resumen unico. Para eso
 * test.php define MEXCORE_SUITE_RUNNER antes de incluirlas, y cada suite
 * respeta esa bandera en lugar de llamar a exit() por su cuenta.
 */

require_once __DIR__ . '/../vendor/autoload.php';

final class Aserciones
{
    private static int $pasadas = 0;

    /** @var list<string> */
    private static array $fallidas = [];

    private static string $suiteActual = 'sin nombre';

    /** @var array<string, array{pasadas: int, fallidas: int}> */
    private static array $porSuite = [];

    public static function suite(string $nombre): void
    {
        self::$suiteActual = $nombre;

        self::$porSuite[$nombre] ??= ['pasadas' => 0, 'fallidas' => 0];
    }

    /**
     * Comparacion estricta. El identity operator es intencional: un test que
     * acepta '0' == false no sirve para validar datos del INE.
     *
     * @param mixed $esperado
     * @param mixed $obtenido
     */
    public static function check(string $caso, $esperado, $obtenido): void
    {
        if ($esperado === $obtenido) {
            self::anotarExito();

            return;
        }

        self::anotarFallo(sprintf(
            "  [%s] %s\n    esperado: %s\n    obtenido: %s",
            self::$suiteActual,
            $caso,
            var_export($esperado, true),
            var_export($obtenido, true)
        ));
    }

    /**
     * Verifica que la llamada lance la excepcion esperada. Sustituye a los
     * bloques try/catch con echo, que pasaban aunque no se lanzara nada.
     *
     * @param class-string<\Throwable> $excepcion
     */
    public static function excepcion(string $caso, string $excepcion, callable $fn): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            if ($e instanceof $excepcion) {
                self::anotarExito();

                return;
            }

            self::anotarFallo(sprintf(
                "  [%s] %s\n    esperado: %s\n    obtenido: %s",
                self::$suiteActual,
                $caso,
                $excepcion,
                get_class($e) . ': ' . $e->getMessage()
            ));

            return;
        }

        self::anotarFallo(sprintf(
            "  [%s] %s\n    esperado: %s\n    obtenido: no se lanzo ninguna excepcion",
            self::$suiteActual,
            $caso,
            $excepcion
        ));
    }

    /**
     * Imprime el resumen y devuelve el codigo de salida: 0 si todo paso.
     */
    public static function resumen(): int
    {
        $total = self::$pasadas + count(self::$fallidas);

        echo PHP_EOL;

        foreach (self::$porSuite as $nombre => $conteo) {
            $suma = $conteo['pasadas'] + $conteo['fallidas'];
            $sello = $conteo['fallidas'] === 0 ? 'OK  ' : 'FALLA';

            echo sprintf("  %s  %-10s %d/%d", $sello, $nombre, $conteo['pasadas'], $suma) . PHP_EOL;
        }

        echo PHP_EOL;

        if (self::$fallidas === []) {
            echo "OK  {$total} aserciones pasaron." . PHP_EOL;

            return 0;
        }

        echo 'FALLOS (' . count(self::$fallidas) . " de {$total}):" . PHP_EOL . PHP_EOL;
        echo implode(PHP_EOL . PHP_EOL, self::$fallidas) . PHP_EOL . PHP_EOL;

        return 1;
    }

    private static function anotarExito(): void
    {
        self::$pasadas++;
        self::asegurarSuite();
        self::$porSuite[self::$suiteActual]['pasadas']++;
    }

    private static function anotarFallo(string $mensaje): void
    {
        self::$fallidas[] = $mensaje;
        self::asegurarSuite();
        self::$porSuite[self::$suiteActual]['fallidas']++;
    }

    /**
     * Permite usar check() sin haber llamado a suite() primero.
     */
    private static function asegurarSuite(): void
    {
        if (!isset(self::$porSuite[self::$suiteActual])) {
            self::$porSuite[self::$suiteActual] = ['pasadas' => 0, 'fallidas' => 0];
        }
    }
}

function suite(string $nombre): void
{
    Aserciones::suite($nombre);
}

/**
 * @param mixed $esperado
 * @param mixed $obtenido
 */
function check(string $caso, $esperado, $obtenido): void
{
    Aserciones::check($caso, $esperado, $obtenido);
}

/**
 * @param class-string<\Throwable> $excepcion
 */
function excepcion(string $caso, string $excepcion, callable $fn): void
{
    Aserciones::excepcion($caso, $excepcion, $fn);
}

function resumen(): int
{
    return Aserciones::resumen();
}

/**
 * Una suite se corre sola salvo que test.php la este orquestando.
 */
function cerrarSuite(): void
{
    if (!defined('MEXCORE_SUITE_RUNNER')) {
        exit(resumen());
    }
}
