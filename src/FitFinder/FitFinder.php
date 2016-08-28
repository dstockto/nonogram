<?php
namespace FitFinder;

class FitFinder
{
    const EMPTY = '.';
    const FILLED = 'X';
    const UNKNOWN = '?';

    public function findBestFit(array $line, array $clue)
    {
        $this->validateLine($line, $clue);

        // if line is unsolved
        if ($line == array_fill(0, count($line), self::UNKNOWN)) {
            return $this->solveUnsolved($line, $clue);
        }

        return $this->solvePartial($line, $clue);
    }

    public function validateLine(array $line, array $clue)
    {
        // Detect clue with a 0 that's not by itself
        if (count($clue) > 1 && array_search(0, $clue) !== false) {
            throw new \InvalidArgumentException('Clue contains embedded zero');
        }

        $this->detectImpossibleLine($line, $clue);
    }

    /**
     * @param array $clue
     * @return number
     */
    public function getClueLength(array $clue)
    {
        return array_sum($clue) + (count($clue) - 1);
    }

    /**
     * @param array $line
     * @param array $clue
     * @return array
     */
    public function solveUnsolved(array $line, array $clue)
    {
        // If the clue is [0] then all values are X
        if ($clue == [0]) {
            return array_fill(0, count($line), self::EMPTY);
        }

        // If the clue has one value and it's the same as the line, fill it all
        if (count($clue) == 1 && $clue[0] == count($line)) {
            return array_fill(0, count($line), self::FILLED);
        }

        // If there's one clue and the line length - clue number > 1/2 line length, the difference is on both sides
        if (count($clue) == 1 && $clue[0] > (count($line) / 2)) {
            $gap = count($line) - $clue[0];
            $gapLine = array_fill(0, $gap, FitFinder::UNKNOWN);
            $fillLine = array_fill(0, count($line) - 2 * $gap, FitFinder::FILLED);
            return array_merge($gapLine, $fillLine, $gapLine);
        }

        // If the clue fits all the spaces, do it
        if ($this->getClueLength($clue) == count($line)) {
            $result = [];
            foreach ($clue as $number) {
                $result = array_merge($result, array_fill(0, $number, self::FILLED), [self::EMPTY]);
            }
            return str_split(trim(join('', $result), self::EMPTY));
        }

        // If the clue length leaves a gap that's less than some number in the clue, fill in what we can
        if ((count($line) - $this->getClueLength($clue)) < max($clue)) {
            $skip = count($line) - $this->getClueLength($clue);
            $newline = '';
            foreach ($clue as $number) {
                // number > skip
                if ($number > $skip) {
                    $newline .= str_repeat(self::UNKNOWN, $skip) . str_repeat(self::FILLED, $number - $skip) . self::UNKNOWN;
                    continue;
                }
                // number = skip
                // number < skip
                $length = min($number, $skip);
                $newline .= str_repeat(self::UNKNOWN, $length + 1);
            }

            // fill any missing with unknown
            if (strlen($newline) < count($line)) {
                $newline .= str_repeat(self::UNKNOWN, count($line) - strlen($newline));
            }

            return str_split($newline);
        }

        return $line;
    }

    private function solvePartial($line, $clue)
    {
        return $line;
    }

    private function detectImpossibleLine(array $line, array $clue)
    {
        // build line regex, see if it could work
        $matchRegex = collect($clue)
            ->map(function ($number) {
                if ($number == 0) {
                    return sprintf('[%s%s]+', self::UNKNOWN, self::EMPTY);
                }
                return sprintf('(?<!%s)[%s%s]{%d}(?!%s)', self::FILLED, self::FILLED, self::UNKNOWN, $number, self::FILLED);
            })->implode(sprintf('[%s%s]+', self::EMPTY, self::UNKNOWN));

        $hasMatch = preg_match("/$matchRegex/", join('', $line));
        if ($hasMatch === 0) {
            throw new \InvalidArgumentException('Clue cannot work on given line: ' . $matchRegex . ' - ' . join('', $line) . ' - [' . join(' ', $clue) . ']');
        }
    }
}