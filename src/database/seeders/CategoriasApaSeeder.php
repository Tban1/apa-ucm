<?php

namespace Database\Seeders;

use App\Models\CategoriaApa;
use Illuminate\Database\Seeder;

class CategoriasApaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            [
                'slug'   => 'docencia',
                'nombre' => 'Docencia',
                'descripcion' => 'Actividades de enseñanza y formación de pregrado y postgrado.',
                'orden'  => 1,
            ],
            [
                'slug'   => 'investigacion',
                'nombre' => 'Investigación',
                'descripcion' => 'Proyectos de investigación, publicaciones y actividad científica.',
                'orden'  => 2,
            ],
            [
                'slug'   => 'vinculacion',
                'nombre' => 'Vinculación con el Medio',
                'descripcion' => 'Actividades de extensión, vinculación y transferencia tecnológica.',
                'orden'  => 3,
            ],
            [
                'slug'   => 'gestion',
                'nombre' => 'Gestión Académica',
                'descripcion' => 'Participación en comités, cargos directivos y gestión institucional.',
                'orden'  => 4,
            ],
            [
                'slug'   => 'formacion_continua',
                'nombre' => 'Formación Continua',
                'descripcion' => 'Capacitaciones, perfeccionamiento y estudios de postítulo.',
                'orden'  => 5,
            ],
        ];

        foreach ($categorias as $datos) {
            CategoriaApa::firstOrCreate(['slug' => $datos['slug']], $datos);
        }
    }
}
