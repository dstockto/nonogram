<?php

class FitFinder
{
    const EMPTY = 'X';
    const FILLED = '#';
    const UNKNOWN = '?';

    public function findBestFit(array $line, array $clue)
    {
        $this->validateLine($line, $clue);

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

        return array_fill(0, count($line), self::UNKNOWN);
    }

    public function validateLine(array $line, array $clue)
    {
        // Detect clue that is too long for line
        $clueLength = $this->getClueLength($clue);
        if ($clueLength > count($line)) {
            throw new InvalidArgumentException('Clue has too much to fit in line');
        }

        // Detect clue with a 0 that's not by itself
        if (count($clue) > 1 && array_search(0, $clue) !== false) {
            throw new InvalidArgumentException('Clue contains embedded zero');
        }
    }

    /**
     * @param array $clue
     * @return number
     */
    public function getClueLength(array $clue)
    {
        return array_sum($clue) + (count($clue) - 1);
    }
}