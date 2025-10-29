<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Configuración y lógica del test de personalidad MBTI
 * Sistema dinámico para procesar las 93 preguntas del test
 */
class PersonalityTestConfig {
    // Constantes para las secciones
    const SECTION_EI = 1; // Energía y Atención (1-26)
    const SECTION_SN = 2; // Información y Percepción (27-58)
    const SECTION_TF = 3; // Decisiones y Juicios (59-75)
    const SECTION_JP = 4; // Estructura y Organización (76-93)

    /**
     * Obtiene la sección a la que pertenece una pregunta
     */
    public static function get_question_section($question_number) {
        if ($question_number >= 1 && $question_number <= 26) {
            return self::SECTION_EI;
        } else if ($question_number >= 27 && $question_number <= 58) {
            return self::SECTION_SN;
        } else if ($question_number >= 59 && $question_number <= 75) {
            return self::SECTION_TF;
        } else if ($question_number >= 76 && $question_number <= 93) {
            return self::SECTION_JP;
        }
        return false;
    }

    /**
     * Procesa una respuesta y devuelve los puntos para cada rasgo
     * @param int $question_number Número de pregunta
     * @param string $answer Respuesta ('a' o 'b')
     * @return array Array asociativo con los rasgos afectados y sus puntos
     */
    public static function process_answer($question_number, $answer) {
        // Usar el mapa por pregunta si existe, si no, fallback a sección/paridad
        $assigned = self::get_assigned_trait($question_number, $answer);
        if ($assigned === null) {
            return [];
        }
        return [$assigned => 1];
    }

    /**
     * Calcula los porcentajes finales para cada dimensión MBTI
     * @param array $scores Array con los puntajes acumulados
     * @return array Array con los porcentajes finales
     */
    public static function calculate_percentages($scores) {
        $pairs = [
            ['E', 'I'],
            ['S', 'N'],
            ['T', 'F'],
            ['J', 'P']
        ];

        $percentages = [];
        foreach ($pairs as $pair) {
            $trait1 = $pair[0];
            $trait2 = $pair[1];
            
            $score1 = isset($scores[$trait1]) ? $scores[$trait1] : 0;
            $score2 = isset($scores[$trait2]) ? $scores[$trait2] : 0;
            $total = $score1 + $score2;

            if ($total > 0) {
                $percentages[$trait1] = round(($score1 / $total) * 100);
                $percentages[$trait2] = round(($score2 / $total) * 100);
            } else {
                // Si no hay puntos en ninguno, asumimos 50-50
                $percentages[$trait1] = 50;
                $percentages[$trait2] = 50;
            }
        }

        return $percentages;
    }

    /**
     * Determina el tipo MBTI basado en los porcentajes
     * @param array $percentages Array con los porcentajes de cada rasgo
     * @return string El tipo MBTI (ejemplo: "INTJ")
     */
    public static function determine_mbti_type($percentages) {
        $type = '';
        $type .= $percentages['E'] >= $percentages['I'] ? 'E' : 'I';
        $type .= $percentages['S'] >= $percentages['N'] ? 'S' : 'N';
        $type .= $percentages['T'] >= $percentages['F'] ? 'T' : 'F';
        $type .= $percentages['J'] >= $percentages['P'] ? 'J' : 'P';
        return $type;
    }

    

    /**
     * Calcula los porcentajes de cada dimensión MBTI basado en las respuestas
     * @param array $answers Array de respuestas donde la clave es el número de pregunta y el valor es 'a' o 'b'
     * @return array Array con los porcentajes de cada dimensión
     */
    public static function calculateTraits($answers) {
        $scores = [
            'E' => 0, 'I' => 0,  // Extraversión vs Introversión
            'S' => 0, 'N' => 0,  // Sensación vs Intuición
            'T' => 0, 'F' => 0,  // Pensamiento vs Sentimiento
            'J' => 0, 'P' => 0   // Juicio vs Percepción
        ];
        
        $totals = [
            'EI' => 0,  // Total para par E/I
            'SN' => 0,  // Total para par S/N
            'TF' => 0,  // Total para par T/F
            'JP' => 0   // Total para par J/P
        ];

        // Nuevo comportamiento: cada respuesta suma 1 al rasgo correspondiente
        foreach ($answers as $q => $ans) {
            $assigned = self::get_assigned_trait($q, $ans);
            if ($assigned === null) continue;

            // incrementar conteo para el rasgo asignado
            if (isset($scores[$assigned])) {
                $scores[$assigned] += 1;
            }

            // incrementar totales por dimensión
            if (in_array($assigned, ['E', 'I'])) {
                $totals['EI'] += 1;
            } elseif (in_array($assigned, ['S', 'N'])) {
                $totals['SN'] += 1;
            } elseif (in_array($assigned, ['T', 'F'])) {
                $totals['TF'] += 1;
            } elseif (in_array($assigned, ['J', 'P'])) {
                $totals['JP'] += 1;
            }
        }

        // Calcular porcentajes finales
        $percentages = [];
        foreach ($scores as $trait => $score) {
            $pair = self::getTraitPair($trait);
            $percentages[$trait] = ($totals[$pair] > 0) ? round(($score / $totals[$pair]) * 100) : 50;
        }

        return $percentages;
    }

    /**
     * Obtiene el par de rasgos al que pertenece un rasgo
     */
    private static function getTraitPair($trait) {
        $pairs = [
            'E' => 'EI', 'I' => 'EI',
            'S' => 'SN', 'N' => 'SN',
            'T' => 'TF', 'F' => 'TF',
            'J' => 'JP', 'P' => 'JP'
        ];
        return $pairs[$trait];
    }
    
    /**
     * Mapa explícito por pregunta con la letra asignada a cada opción
     * Si una pregunta no está en este mapa, caemos en el fallback por sección/paridad
     * Formato: question_number => ['a' => 'E', 'b' => 'I']
     * Este mapa se derivó de la configuración anterior de pesos (la clave con mayor peso se asigna a la opción 'a').
     */
    /**
     * Mapa explícito por pregunta con la letra asignada a cada opción
     * Si una pregunta no está en este mapa, caemos en el fallback por sección/paridad
     * Formato: question_number => ['a' => 'E', 'b' => 'I']
     * Este mapa se derivó de la configuración anterior de pesos (la clave con mayor peso se asigna a la opción 'a').
     */
    private static $question_map = [
        // Preguntas 1..26 — actualizado según el input del usuario (par: a, b)
        1 => ['a' => 'J', 'b' => 'P'],
        2 => ['a' => 'P', 'b' => 'J'],
        3 => ['a' => 'S', 'b' => 'N'],
        4 => ['a' => 'E', 'b' => 'I'],
        5 => ['a' => 'N', 'b' => 'S'],
        6 => ['a' => 'F', 'b' => 'T'],
        7 => ['a' => 'P', 'b' => 'J'],
        8 => ['a' => 'E', 'b' => 'I'],
        9 => ['a' => 'J', 'b' => 'P'],
        10 => ['a' => 'J', 'b' => 'P'],
        11 => ['a' => 'P', 'b' => 'J'],
        12 => ['a' => 'I', 'b' => 'E'],
        13 => ['a' => 'S', 'b' => 'N'],
        14 => ['a' => 'E', 'b' => 'I'],
        15 => ['a' => 'N', 'b' => 'S'],
        16 => ['a' => 'F', 'b' => 'T'],
        17 => ['a' => 'P', 'b' => 'J'],
        18 => ['a' => 'I', 'b' => 'E'],
        19 => ['a' => 'E', 'b' => 'I'],
        20 => ['a' => 'J', 'b' => 'P'],
        21 => ['a' => 'P', 'b' => 'J'],
        22 => ['a' => 'I', 'b' => 'E'],
        23 => ['a' => 'E', 'b' => 'I'],
        24 => ['a' => 'N', 'b' => 'S'],
        25 => ['a' => 'P', 'b' => 'J'],
        26 => ['a' => 'I', 'b' => 'E'],
    27 => ['a' => 'I', 'b' => 'E'],
    28 => ['a' => 'J', 'b' => 'P'],
    29 => ['a' => 'N', 'b' => 'S'],
    30 => ['a' => 'F', 'b' => 'T'],
    31 => ['a' => 'T', 'b' => 'F'],
    32 => ['a' => 'S', 'b' => 'N'],
    33 => ['a' => 'P', 'b' => 'J'],
    34 => ['a' => 'E', 'b' => 'I'],
    35 => ['a' => 'I', 'b' => 'E'],
    36 => ['a' => 'J', 'b' => 'P'],
    37 => ['a' => 'N', 'b' => 'S'],
    38 => ['a' => 'F', 'b' => 'T'],
    39 => ['a' => 'T', 'b' => 'F'],
    40 => ['a' => 'S', 'b' => 'N'],
    41 => ['a' => 'P', 'b' => 'J'],
    42 => ['a' => 'E', 'b' => 'I'],
    43 => ['a' => 'I', 'b' => 'E'],
    44 => ['a' => 'J', 'b' => 'P'],
    45 => ['a' => 'N', 'b' => 'S'],
    46 => ['a' => 'F', 'b' => 'T'],
    47 => ['a' => 'T', 'b' => 'F'],
    48 => ['a' => 'S', 'b' => 'N'],
    49 => ['a' => 'P', 'b' => 'J'],
    50 => ['a' => 'I', 'b' => 'E'],
    51 => ['a' => 'J', 'b' => 'P'],
    52 => ['a' => 'N', 'b' => 'S'],
    53 => ['a' => 'F', 'b' => 'T'],
    54 => ['a' => 'T', 'b' => 'F'],
    55 => ['a' => 'S', 'b' => 'N'],
    56 => ['a' => 'I', 'b' => 'E'],
    57 => ['a' => 'N', 'b' => 'S'],
    58 => ['a' => 'F', 'b' => 'T'],
    59 => ['a' => 'J', 'b' => 'P'],
    60 => ['a' => 'I', 'b' => 'E'],
    61 => ['a' => 'S', 'b' => 'N'],
    62 => ['a' => 'E', 'b' => 'I'],
    63 => ['a' => 'N', 'b' => 'S'],
    64 => ['a' => 'F', 'b' => 'T'],
    65 => ['a' => 'P', 'b' => 'J'],
    66 => ['a' => 'I', 'b' => 'E'],
    67 => ['a' => 'E', 'b' => 'I'],
    68 => ['a' => 'J', 'b' => 'P'],
    69 => ['a' => 'T', 'b' => 'F'],
    70 => ['a' => 'J', 'b' => 'P'],
    71 => ['a' => 'P', 'b' => 'J'],
    72 => ['a' => 'I', 'b' => 'E'],
    73 => ['a' => 'S', 'b' => 'N'],
    74 => ['a' => 'N', 'b' => 'S'],
    75 => ['a' => 'F', 'b' => 'T'],
    76 => ['a' => 'P', 'b' => 'J'],
    77 => ['a' => 'E', 'b' => 'I'],
    78 => ['a' => 'T', 'b' => 'F'],
    79 => ['a' => 'N', 'b' => 'S'],
    80 => ['a' => 'F', 'b' => 'T'],
    81 => ['a' => 'T', 'b' => 'F'],
    82 => ['a' => 'S', 'b' => 'N'],
    83 => ['a' => 'N', 'b' => 'S'],
    84 => ['a' => 'F', 'b' => 'T'],
    85 => ['a' => 'T', 'b' => 'F'],
    86 => ['a' => 'S', 'b' => 'N'],
    87 => ['a' => 'N', 'b' => 'S'],
    88 => ['a' => 'F', 'b' => 'T'],
    89 => ['a' => 'T', 'b' => 'F'],
    90 => ['a' => 'S', 'b' => 'N'],
    91 => ['a' => 'F', 'b' => 'T'],
    92 => ['a' => 'T', 'b' => 'F'],
    93 => ['a' => 'S', 'b' => 'N'],
    ];

    /**
     * Devuelve la letra asignada para una pregunta y opción ('a' o 'b').
     * Si no hay entrada explícita en el mapa, se usa el fallback por sección/paridad.
     */
    private static function get_assigned_trait($question_number, $option) {
        $option = strtolower($option) === 'b' ? 'b' : 'a';
        if (isset(self::$question_map[(int)$question_number])) {
            return self::$question_map[(int)$question_number][$option];
        }

        // Fallback: usar la lógica por sección/paridad (compatibilidad)
        $section = self::get_question_section($question_number);
        if (!$section) {
            return null;
        }
        $is_odd = ($question_number % 2 == 1);
        switch ($section) {
            case self::SECTION_EI:
                if ($is_odd) {
                    return $option === 'a' ? 'E' : 'I';
                } else {
                    return $option === 'b' ? 'E' : 'I';
                }
            case self::SECTION_SN:
                if ($is_odd) {
                    return $option === 'a' ? 'S' : 'N';
                } else {
                    return $option === 'b' ? 'S' : 'N';
                }
            case self::SECTION_TF:
                if ($is_odd) {
                    return $option === 'a' ? 'T' : 'F';
                } else {
                    return $option === 'b' ? 'T' : 'F';
                }
            case self::SECTION_JP:
                if ($is_odd) {
                    return $option === 'a' ? 'J' : 'P';
                } else {
                    return $option === 'b' ? 'J' : 'P';
                }
        }
        return null;
    }
}