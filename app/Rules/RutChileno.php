<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RutChileno implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $rut = self::clean($value);

        if (strlen($rut) < 8 || strlen($rut) > 9) {
            $fail('El RUT debe tener entre 8 y 9 dígitos incluyendo el dígito verificador.');
            return;
        }

        $numero = substr($rut, 0, -1);
        $dv = strtoupper(substr($rut, -1));

        if (!self::validarRut($numero, $dv)) {
            $fail('El RUT ingresado no es válido.');
        }
    }

    public static function clean($rut)
    {
        return preg_replace('/[^0-9kK]/', '', $rut);
    }

    public static function calcularDv($numero)
    {
        $suma = 0;
        $multiplo = 2;

        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $suma += intval($numero[$i]) * $multiplo;
            $multiplo = $multiplo == 7 ? 2 : $multiplo + 1;
        }

        $resultado = 11 - ($suma % 11);

        if ($resultado == 11) {
            return '0';
        } elseif ($resultado == 10) {
            return 'K';
        } else {
            return strval($resultado);
        }
    }

    public static function formatRut($rut)
    {
        $rut = self::clean($rut);
        $dv = substr($rut, -1);
        $numero = substr($rut, 0, -1);
        return number_format($numero, 0, "", ".") . '-' . $dv;
    }

    public static function validarRut($numero, $dv)
    {
        if ($numero <= 0 || $numero > 50000000) {
            return false;
        }
        return strtoupper($dv) === self::calcularDv($numero);
    }

    public function message(): string
    {
        return 'El RUT ingresado no es válido.';
    }
}
