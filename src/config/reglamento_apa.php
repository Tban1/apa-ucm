<?php

/**
 * Porcentaje de tiempo (%T) asignado por área APA según categoría académica.
 * Fuente: reglamento CAD UCM (valores representativos para el sistema).
 */
return [
    'titular' => [
        'docencia'      => 35,
        'investigacion' => 35,
        'vinculacion'   => 15,
        'gestion'       => 10,
        'formacion'     => 5,
    ],
    'adjunto' => [
        'docencia'      => 45,
        'investigacion' => 25,
        'vinculacion'   => 15,
        'gestion'       => 10,
        'formacion'     => 5,
    ],
    'auxiliar' => [
        'docencia'      => 55,
        'investigacion' => 15,
        'vinculacion'   => 15,
        'gestion'       => 10,
        'formacion'     => 5,
    ],

    'articulo' => [
        'titular'  => 'Art. 34° El Profesor Titular es el académico que ha demostrado plena madurez académica, reconocida trayectoria y aportes sustantivos a la misión de la Universidad. Tiene amplio dominio de su disciplina, ejerce con autoridad intelectual sus funciones y constituye un referente para la comunidad académica de la Universidad Católica del Maule.',
        'adjunto'  => 'Art. 35° El Profesor Adjunto es el académico que muestra solidez en su desempeño, con experiencia en docencia, investigación y vinculación con el medio. Contribuye activamente al desarrollo disciplinar de su unidad y mantiene un compromiso permanente con los valores institucionales de la Universidad Católica del Maule.',
        'auxiliar' => 'Art. 37° Profesor Auxiliar Docente es aquel académico/a al que se le reconoce calidad y relevancia en una de las demás funciones académicas universitarias. Muestra condiciones académicas y personales adecuadas a la misión de la Universidad Católica del Maule y los valores que la sustentan. Además mantiene una preocupación constante por su formación. Son funciones del Profesor Auxiliar Docente, colaborar con los/las profesores/as Titulares y Adjuntos, en las funciones que desempeñe, conforme a la programación de su unidad, o cuando lo disponga el/la Decano/a o Director/a de Instituto/Centro.',
    ],

    'concepto_definicion' => [
        'excelente'  => 'Que sobresale en bondad, mérito o estimación. Desarrolla las actividades académicas de manera plena, lo que ha realizado ha sido significativamente meritorio, colaborando en el desarrollo de la misión y clima de la Universidad Católica del Maule.',
        'muy_bueno'  => 'Desarrolla con eficiencia sus actividades académicas, siendo apreciable su contribución al desarrollo de la misión de la Universidad Católica del Maule.',
        'bueno'      => 'Cumple con suficiencia sus actividades académicas, alcanzando los estándares esperados para la etapa de desarrollo en que se encuentra.',
        'regular'    => 'Cumple de manera básica sus actividades académicas, siendo necesario establecer compromisos de mejora para avanzar en su desarrollo académico.',
        'deficiente' => 'Presenta debilidades significativas en el cumplimiento de sus actividades académicas, requiriendo un plan de mejora para alcanzar los estándares esperados.',
    ],
];
