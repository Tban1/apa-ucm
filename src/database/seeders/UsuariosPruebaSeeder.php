<?php

namespace Database\Seeders;

use App\Models\Facultad;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        $fci = Facultad::where('codigo', 'FCI')->first();

        $usuarios = [
            [
                'email'       => 'admin@ucm.cl',
                'name'        => 'Administrador Sistema',
                'role'        => 'admin',
                'facultad_id' => null,
            ],
            [
                'email'       => 'analista@ucm.cl',
                'name'        => 'Analista CCDA',
                'role'        => 'analista_ccda',
                'facultad_id' => null,
            ],
            [
                'email'       => 'secretario@ucm.cl',
                'name'        => 'Secretario FCI',
                'role'        => 'secretario',
                'facultad_id' => $fci?->id,
            ],
            [
                'email'       => 'cca@ucm.cl',
                'name'        => 'Miembro CCA',
                'role'        => 'miembro_cca',
                'facultad_id' => $fci?->id,
            ],
            [
                'email'       => 'jefe@ucm.cl',
                'name'        => 'Jefe Académico FCI',
                'role'        => 'jefe_academico',
                'facultad_id' => $fci?->id,
            ],
            [
                'email'       => 'academico@ucm.cl',
                'name'        => 'Académico Prueba',
                'role'        => 'academico',
                'facultad_id' => $fci?->id,
                'rut'         => '11.111.111-1',
                'categoria_academica'   => 'adjunto',
                'linea_desarrollo'      => 'docente',
                'fecha_jerarquizacion'  => '2018-03-15',
                'horas_contrato_isem'   => 18,
                'horas_contrato_iisem'  => 18,
                'nota_anterior'         => 4.2,
                'concepto_anterior'     => 'Muy Bueno',
            ],
        ];

        foreach ($usuarios as $datos) {
            User::updateOrCreate(
                ['email' => $datos['email']],
                array_merge($datos, ['password' => Hash::make('password')])
            );
        }
    }
}
