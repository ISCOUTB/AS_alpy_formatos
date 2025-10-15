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
        $section = self::get_question_section($question_number);
        if (!$section) {
            return [];
        }

        $is_odd = ($question_number % 2 == 1);
        $points = 1; // Punto base por respuesta

        // Preguntas con peso especial basado en su importancia en el test
        $important_questions = [
            4, 19, 36, 50, 56, 60, 70, 83, 92 // Preguntas clave con más peso
        ];
        if (in_array($question_number, $important_questions)) {
            $points = 2; // Doble peso para preguntas importantes
        }

        $traits = [];
        switch ($section) {
            case self::SECTION_EI:
                if ($is_odd) {
                    $traits[$answer === 'a' ? 'E' : 'I'] = $points;
                } else {
                    $traits[$answer === 'b' ? 'E' : 'I'] = $points;
                }
                break;

            case self::SECTION_SN:
                if ($is_odd) {
                    $traits[$answer === 'a' ? 'S' : 'N'] = $points;
                } else {
                    $traits[$answer === 'b' ? 'S' : 'N'] = $points;
                }
                break;

            case self::SECTION_TF:
                if ($is_odd) {
                    $traits[$answer === 'a' ? 'T' : 'F'] = $points;
                } else {
                    $traits[$answer === 'b' ? 'T' : 'F'] = $points;
                }
                break;

            case self::SECTION_JP:
                if ($is_odd) {
                    $traits[$answer === 'a' ? 'J' : 'P'] = $points;
                } else {
                    $traits[$answer === 'b' ? 'J' : 'P'] = $points;
                }
                break;
        }

        return $traits;
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
        4 => ['E' => 1.0, 'I' => 0.0],  // Extrovertido vs Introvertido
        5 => ['N' => 0.7, 'S' => 0.3],  // Imaginativo vs Realista
        6 => ['F' => 0.7, 'T' => 0.3],  // Corazón vs Cabeza
        7 => ['P' => 0.7, 'J' => 0.3],  // Impulso vs Plan
        8 => ['E' => 0.7, 'I' => 0.3],  // Fácil vs Difícil de conocer
        9 => ['J' => 0.7, 'P' => 0.3],  // Agenda vs Libertad
        10 => ['J' => 1.0, 'P' => 0.0], // Organización vs Descubrimiento
        11 => ['P' => 0.7, 'J' => 0.3], // Fluir vs Planear
        12 => ['I' => 0.7, 'E' => 0.3], // Reservado vs Abierto
        13 => ['S' => 0.7, 'N' => 0.3], // Práctico vs Ingenioso
        14 => ['E' => 0.7, 'I' => 0.3], // Presentador vs Presentado
        15 => ['N' => 0.7, 'S' => 0.3], // Ideas nuevas vs Realidad
        16 => ['F' => 0.7, 'T' => 0.3], // Sentimientos vs Lógica
        17 => ['P' => 0.7, 'J' => 0.3], // Esperar vs Planear
        18 => ['I' => 0.7, 'E' => 0.3], // Soledad vs Compañía
        19 => ['E' => 1.0, 'I' => 0.0], // Energía social vs Drenaje
        20 => ['J' => 0.7, 'P' => 0.3], // Organización vs Libertad
        21 => ['P' => 0.7, 'J' => 0.3], // Momento vs Plan
        22 => ['I' => 0.5, 'E' => 0.5], // Aburrimiento vs Diversión
        23 => ['E' => 0.7, 'I' => 0.3], // Compañía vs Soledad
        24 => ['N' => 0.7, 'S' => 0.3], // Inteligencia vs Sentido común
        25 => ['J' => 0.7, 'P' => 0.3], // Planificación vs Presión
        26 => ['I' => 0.7, 'E' => 0.3], // Tiempo para conocer
        
        // Sección 2: Palabras representativas
        27 => ['I' => 0.5, 'E' => 0.5], // Cerrado vs Abierto
        28 => ['J' => 0.7, 'P' => 0.3], // Planificado vs No planificado
        29 => ['N' => 0.7, 'S' => 0.3], // Abstracto vs Concreto
        30 => ['F' => 0.3, 'T' => 0.7], // Gentil vs Firme
        31 => ['T' => 0.7, 'F' => 0.3], // Pensamiento vs Sentimiento
        32 => ['S' => 0.7, 'N' => 0.3], // Hechos vs Ideas
        33 => ['P' => 0.7, 'J' => 0.3], // Impulsos vs Decisiones
        34 => ['E' => 0.5, 'I' => 0.5], // Acalorado vs Tranquilo
        35 => ['I' => 0.7, 'E' => 0.3], // Quieto vs Reactivo
        36 => ['J' => 1.0, 'P' => 0.0], // Sistemático vs Casual
        37 => ['N' => 0.7, 'S' => 0.3], // Teoría vs Certeza
        38 => ['F' => 0.7, 'T' => 0.3], // Sensitivo vs Justo
        39 => ['T' => 0.7, 'F' => 0.3], // Argumentos vs Hechos
        40 => ['S' => 0.7, 'N' => 0.3], // Hechos vs Conceptos
        41 => ['P' => 0.7, 'J' => 0.3], // Libre vs Planificado
        42 => ['I' => 0.7, 'E' => 0.3], // Reservado vs Hablador
        43 => ['J' => 0.7, 'P' => 0.3], // Ordenado vs Desordenado
        44 => ['N' => 0.7, 'S' => 0.3], // Ideas vs Hechos
        45 => ['F' => 0.7, 'T' => 0.3], // Compasivo vs Previsor
        46 => ['T' => 0.5, 'F' => 0.5], // Beneficios vs Bendiciones
        47 => ['S' => 0.7, 'N' => 0.3], // Sentido común vs Teórico
        48 => ['I' => 0.7, 'E' => 0.3], // Pocos vs Muchos amigos
        49 => ['J' => 0.7, 'P' => 0.3], // Sistemático vs Espontáneo
        50 => ['N' => 1.0, 'S' => 0.0], // Imaginativo vs Concreto
        51 => ['F' => 0.7, 'T' => 0.3], // Tibio vs Objetivo
        52 => ['T' => 0.7, 'F' => 0.3], // Objetivo vs Pasional
        53 => ['S' => 0.7, 'N' => 0.3], // Construir vs Inventar
        54 => ['I' => 0.5, 'E' => 0.5], // Tranquilo vs Sociable
        55 => ['N' => 0.7, 'S' => 0.3], // Teorías vs Hechos
        56 => ['F' => 1.0, 'T' => 0.0], // Compasivo vs Lógico
        57 => ['T' => 0.7, 'F' => 0.3], // Analítico vs Sentimental
        58 => ['F' => 0.7, 'T' => 0.3], // Sensible vs Impresionable

        // Sección 3: Decisiones y juicios
        59 => ['J' => 0.7, 'P' => 0.3], // Lista vs Inmersión
        60 => ['I' => 1.0, 'E' => 0.0], // Dificultad social vs Facilidad
        61 => ['S' => 0.5, 'N' => 0.5], // Convencional vs Innovador
        62 => ['I' => 0.7, 'E' => 0.3], // Tiempo para conocer
        63 => ['N' => 0.7, 'S' => 0.3], // Conceptos vs Hechos
        64 => ['F' => 0.7, 'T' => 0.3], // Sentimientos vs Razón
        65 => ['P' => 0.7, 'J' => 0.3], // Flexibilidad vs Agenda
        66 => ['I' => 0.7, 'E' => 0.3], // Conocidos vs Grupo
        67 => ['E' => 0.7, 'I' => 0.3], // Hablar vs Escuchar
        68 => ['J' => 0.7, 'P' => 0.3], // Lista motivadora vs Desinterés
        69 => ['T' => 0.7, 'F' => 0.3], // Competente vs Compasivo
        70 => ['P' => 1.0, 'J' => 0.0], // Libertad vs Impulso
        71 => ['P' => 0.7, 'J' => 0.3], // Planear sobre la marcha vs División
        72 => ['E' => 0.7, 'I' => 0.3], // Intereses comunes vs Cualquiera
        73 => ['S' => 0.7, 'N' => 0.3], // Métodos probados vs Problemas nuevos
        74 => ['N' => 0.7, 'S' => 0.3], // Original vs Directo
        75 => ['S' => 0.5, 'N' => 0.5], // Común vs Extravagante

        // Sección 4: Estructura y organización
        76 => ['P' => 0.7, 'J' => 0.3], // Sentimiento vs Agenda
        77 => ['I' => 0.7, 'E' => 0.3], // Conversación limitada vs Fácil
        78 => ['T' => 0.7, 'F' => 0.3], // Eventos vs Sentimientos
        79 => ['N' => 0.7, 'S' => 0.3], // Imaginativo vs Realista
        80 => ['F' => 0.7, 'T' => 0.3], // Corazón vs Mente
        81 => ['T' => 0.7, 'F' => 0.3], // Mente justa vs Cuidadoso
        82 => ['S' => 0.7, 'N' => 0.3], // Producción vs Diseño
        83 => ['N' => 1.0, 'S' => 0.0], // Posibilidades vs Certezas
        84 => ['F' => 0.7, 'T' => 0.3], // Ternura vs Fuerza
        85 => ['T' => 0.7, 'F' => 0.3], // Práctico vs Sentimental
        86 => ['S' => 0.7, 'N' => 0.3], // Producir vs Crear
        87 => ['N' => 0.7, 'S' => 0.3], // Novedoso vs Conocido
        88 => ['F' => 0.7, 'T' => 0.3], // Simpatizar vs Analizar
        89 => ['T' => 0.7, 'F' => 0.3], // Firmeza vs Ternura
        90 => ['S' => 0.7, 'N' => 0.3], // Concreto vs Abstracto
        91 => ['F' => 0.5, 'T' => 0.5], // Fiel vs Determinado
        92 => ['S' => 1.0, 'N' => 0.0], // Probado vs Corazonada
        93 => ['S' => 0.7, 'N' => 0.3], // Práctico vs Innovador
    ];

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

        foreach ($answers as $question => $answer) {
            if (!isset(self::WEIGHTS[$question])) {
                continue;
            }

            foreach (self::WEIGHTS[$question] as $trait => $weight) {
                $isFirstOption = ($answer === 'a');
                $pair = self::getTraitPair($trait);
                
                if ($isFirstOption) {
                    $scores[$trait] += $weight;
                } else {
                    $scores[self::getOppositeTrait($trait)] += $weight;
                }
                
                $totals[$pair] += $weight;
            }
        }

        // Calcular porcentajes finales
        $percentages = [];
        foreach ($scores as $trait => $score) {
            $pair = self::getTraitPair($trait);
            $percentages[$trait] = ($totals[$pair] > 0) ? ($score / $totals[$pair]) * 100 : 50;
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
     * Obtiene el rasgo opuesto
     */
    private static function getOppositeTrait($trait) {
        $opposites = [
            'E' => 'I', 'I' => 'E',
            'S' => 'N', 'N' => 'S',
            'T' => 'F', 'F' => 'T',
            'J' => 'P', 'P' => 'J'
        ];
        return $opposites[$trait];
    }
}