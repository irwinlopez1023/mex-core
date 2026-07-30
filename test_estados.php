<?php
require_once __DIR__ . '/vendor/autoload.php';

use Irwinlopez1023\MexCore\MexCore;

echo "==================================" . PHP_EOL;
echo "  MexCore - Prueba de Estados" . PHP_EOL;
echo "==================================" . PHP_EOL . PHP_EOL;

// ─── 1. LISTADO COMPLETO ───────────────────────────────────────────

echo "1. LISTADO DE LOS 33 ESTADOS" . PHP_EOL;
echo "--------------------------------------------" . PHP_EOL;

foreach (MexCore::Estado()->listar() as $e) {
    echo "  #{$e->toNumber()}\t{$e->toCurp()}\t{$e->toAbbr()}\t{$e->toName()}" . PHP_EOL;
}

// ─── 2. NAMED CONSTRUCTORS ─────────────────────────────────────────

echo PHP_EOL . "2. NAMED CONSTRUCTORS" . PHP_EOL;
echo "--------------------------------------------" . PHP_EOL;

echo "  Estado()->fromCurp('VARG740228HSPLSN07')  ->toName()  =>  " . MexCore::Estado()->fromCurp('VARG740228HSPLSN07')->toName() . PHP_EOL;
echo "  Estado()->fromCurp('NL')                  ->toName()  =>  " . MexCore::Estado()->fromCurp('NL')->toName() . PHP_EOL;
echo "  Estado()->fromCurp('sp')                  ->toName()  =>  " . MexCore::Estado()->fromCurp('sp')->toName() . "  (minusculas)" . PHP_EOL;
echo "  Estado()->fromNumber(24)                  ->toName()  =>  " . MexCore::Estado()->fromNumber(24)->toName() . PHP_EOL;
echo "  Estado()->fromAbbr('CDMX')                ->toName()  =>  " . MexCore::Estado()->fromAbbr('CDMX')->toName() . PHP_EOL;
echo "  Estado()->fromName('Nuevo Leon')          ->toCurp()  =>  " . MexCore::Estado()->fromName('Nuevo Leon')->toCurp() . PHP_EOL;
echo "  Estado()->fromName('Distrito Federal')    ->toAbbr()  =>  " . MexCore::Estado()->fromName('Distrito Federal')->toAbbr() . PHP_EOL;

// ─── 3. RESILIENCIA ─────────────────────────────────────────────────

echo PHP_EOL . "3. RESILIENCIA" . PHP_EOL;
echo "--------------------------------------------" . PHP_EOL;

echo "  fromAbbr('S.L.P.')              ->toName()  =>  " . MexCore::Estado()->fromAbbr('S.L.P.')->toName() . "  (tolera puntos)" . PHP_EOL;
echo "  fromAbbr('slp')                 ->toName()  =>  " . MexCore::Estado()->fromAbbr('slp')->toName() . "  (tolera minusculas)" . PHP_EOL;
echo "  fromName('NUEVO LEON')          ->toName()  =>  " . MexCore::Estado()->fromName('NUEVO LEON')->toName() . "  (sin acento)" . PHP_EOL;
echo "  fromName('san luis potosi')     ->toName()  =>  " . MexCore::Estado()->fromName('san luis potosi')->toName() . "  (minusculas + sin acento)" . PHP_EOL;
echo "  fromName('Mexico')              ->toName()  =>  " . MexCore::Estado()->fromName('Mexico')->toName() . "  (alias)" . PHP_EOL;
echo "  fromName('EDOMEX')              ->toName()  =>  " . MexCore::Estado()->fromName('EDOMEX')->toName() . "  (alias)" . PHP_EOL;
echo "  fromName('extranjero')          ->toName()  =>  " . MexCore::Estado()->fromName('extranjero')->toName() . "  (alias)" . PHP_EOL;

// ─── 4. FLUENT INTERFACE ───────────────────────────────────────────

echo PHP_EOL . "4. INTERFAZ FLUIDA" . PHP_EOL;
echo "--------------------------------------------" . PHP_EOL;

echo "  Estado()->fromName('Nuevo Leon')->toCurp()        =>  " . MexCore::Estado()->fromName('Nuevo Leon')->toCurp() . PHP_EOL;
echo "  Estado()->fromAbbr('CDMX')->toNumber()            =>  " . MexCore::Estado()->fromAbbr('CDMX')->toNumber() . PHP_EOL;
echo "  Estado()->fromNumber(15)->toCurp()                =>  " . MexCore::Estado()->fromNumber(15)->toCurp() . PHP_EOL;
echo "  Estado()->fromCurp('VARG740228HSPLSN07')->toAbbr()  =>  " . MexCore::Estado()->fromCurp('VARG740228HSPLSN07')->toAbbr() . PHP_EOL;

// ─── 5. EXCEPCION ──────────────────────────────────────────────────

echo PHP_EOL . "5. EXCEPCION" . PHP_EOL;
echo "--------------------------------------------" . PHP_EOL;

try {
    MexCore::Estado()->fromCurp('AAAA000101HZZXXX00');
} catch (\Irwinlopez1023\MexCore\InvalidStateException $e) {
    echo "  fromCurp('AAAA...HZZ...')  =>  {$e->getMessage()}" . PHP_EOL;
}

try {
    MexCore::Estado()->fromAbbr('XYZ');
} catch (\Irwinlopez1023\MexCore\InvalidStateException $e) {
    echo "  fromAbbr('XYZ')            =>  {$e->getMessage()}" . PHP_EOL;
}

echo PHP_EOL . "==================================" . PHP_EOL;
echo "  Prueba de Estados - Completa" . PHP_EOL;
echo "==================================" . PHP_EOL;
