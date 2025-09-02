<?php 

function parseArithmeticExpression($expression) {
    $tokens = str_split(preg_replace('/\s+/', '', $expression));
    $position = 0;

    function parseExpression(&$tokens, &$position) {
        $result = parseTerm($tokens, $position);

        while ($position < count($tokens) && ($tokens[$position] == '+' || $tokens[$position] == '-')) {
            $operator = $tokens[$position];
            $position++;
            $term = parseTerm($tokens, $position);

            if ($operator == '+') {
                $result += $term;
            } else {
                $result -= $term;
            }
        }

        return $result;
    }

    function parseTerm(&$tokens, &$position) {
        $result = parseFactor($tokens, $position);

        while ($position < count($tokens) && ($tokens[$position] == '*' || $tokens[$position] == '/')) {
            $operator = $tokens[$position];
            $position++;
            $factor = parseFactor($tokens, $position);

            if ($operator == '*') {
                $result *= $factor;
            } else {
                if ($factor == 0) {
                    throw new Exception("Division by zero");
                }
                $result /= $factor;
            }
        }

        return $result;
    }

    function parseFactor(&$tokens, &$position) {
        if ($tokens[$position] == '(') {
            $position++;
            $result = parseExpression($tokens, $position);
            if ($position >= count($tokens) || $tokens[$position] != ')') {
                throw new Exception("Mismatched parentheses");
            }
            $position++;
            return $result;
        }

        $start = $position;
        while ($position < count($tokens) && (is_numeric($tokens[$position]) || $tokens[$position] == '.')) {
            $position++;
        }

        $number = implode('', array_slice($tokens, $start, $position - $start));
        if (!is_numeric($number)) {
            throw new Exception("Invalid number: $number");
        }

        return floatval($number);
    }

    return parseExpression($tokens, $position);
}

// Example usage:
$result = parseArithmeticExpression("3 + 4 * (2 - 1) / 5");
echo $result; // Outputs: 4.8
