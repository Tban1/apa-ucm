<?php

namespace Database\Seeders;

use App\Models\Facultad;
use Illuminate\Database\Seeder;

class FacultadesSeeder extends Seeder
{
    public function run(): void
    {
        $facultades = [
            ['codigo' => 'FCI',  'nombre' => 'Ciencias de la Ingeniería'],
            ['codigo' => 'FCAF', 'nombre' => 'Ciencias Agrarias y Forestales'],
            ['codigo' => 'FCS',  'nombre' => 'Ciencias de la Salud'],
            ['codigo' => 'FCRF', 'nombre' => 'Ciencias Religiosas y Filosóficas'],
            ['codigo' => 'FCSE', 'nombre' => 'Ciencias Sociales y Económicas'],
        ];

        foreach ($facultades as $datos) {
            Facultad::firstOrCreate(['codigo' => $datos['codigo']], $datos);
        }
    }
}
