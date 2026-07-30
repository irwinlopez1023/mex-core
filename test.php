<?php

declare(strict_types=1);

/**
 * Corre las dos suites en un solo proceso y devuelve un resumen unico.
 * Ejecutar con: php test.php
 *
 * La constante le dice a cerrarSuite() que no imprima ni termine el proceso
 * al final de cada archivo: aqui el resumen se imprime una sola vez.
 */

define('MEXCORE_SUITE_RUNNER', true);

require_once __DIR__ . '/tests/harness.php';

require __DIR__ . '/test_persona.php';
require __DIR__ . '/test_estados.php';

exit(resumen());
