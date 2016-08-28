<?php
namespace FitFinderTest;

use FitFinder\FitFinder;

class FitFinderTest extends \PHPUnit_Framework_TestCase
{
    /** @var FitFinder */
    private $fitFinder;

    public function setUp()
    {
        $this->fitFinder = new FitFinder();
    }

    /**
     * @param $line
     * @param $clue
     * @param $expectedLine
     * @test
     * @dataProvider lineProvider
     */
    public function it_will_find_the_best_fit($line, $clue, $expectedLine)
    {
        $this->assertEquals($expectedLine, $this->fitFinder->findBestFit($line, $clue));
    }


    public function lineProvider()
    {
        return [
            // Puzzles that don't need any information
            '1 blank' => [
                $this->getBlankLine(1), [0], [FitFinder::EMPTY],
            ],
            '15 blank' => [
                $this->getBlankLine(15), [0], array_fill(0, 15, FitFinder::EMPTY),
            ],
           '1 filled' => [
                $this->getBlankLine(1), [1], [FitFinder::FILLED],
            ],
            '15 filled' => [
                $this->getBlankLine(15), [15], array_fill(0, 15, FitFinder::FILLED)
            ],
            '14 clue for 15 line' => [
                $this->getBlankLine(15), [14], array_merge([FitFinder::UNKNOWN], array_fill(1, 13, FitFinder::FILLED), [FitFinder::UNKNOWN]),
            ],
            '1 clue for 3 line' => [
                $this->getBlankLine(3), [1], $this->getLineFromString('???'),
            ],
            '2 clue for 3 line' => [
                $this->getBlankLine(3), [2], $this->getLineFromString('?X?'),
            ],
            '3 clue for 3 line' => [
                $this->getBlankLine(3), [3], $this->getLineFromString('XXX'),
            ],
            '1 1 for 3' => [
                $this->getBlankLine(3), [1, 1], $this->getLineFromString('X.X'),
            ],
            '1 1 1 for 5' => [
                $this->getBlankLine(5), [1, 1, 1], $this->getLineFromString('X.X.X'),
            ],
            '3 1 for 5' => [
                $this->getBlankLine(5), [3, 1], $this->getLineFromString('XXX.X'),
            ],
            ' 2 2 for 5' => [
                $this->getBlankLine(5), [2, 2], $this->getLineFromString('XX.XX'),
            ],
            '3 for 5' => [
                $this->getBlankLine(5), [3], $this->getLineFromString('??X??'),
            ],
            '4 4 for 10' => [
                $this->getBlankLine(10), [4, 4], $this->getLineFromString('?XXX??XXX?'),
            ],
            '3 4 for 10' => [
                $this->getBlankLine(10), [3, 4], $this->getLineFromString('??X???XX??'),
            ],
            '4 3 for 10' => [
                $this->getBlankLine(10), [4, 3], $this->getLineFromString('??XX???X??'),
            ],
            '6 in 10' => [
                $this->getBlankLine(10), [6], $this->getLineFromString('????XX????')
            ],
            '1 6 in 10' => [
                $this->getBlankLine(10), [1, 6], $this->getLineFromString('????XXXX??')
            ],
            '1 6 1 in 10' => [
                $this->getBlankLine(10), [1, 6, 1], $this->getLineFromString('X.XXXXXX.X'),
            ],
            '1 5 1 in 10' => [
                $this->getBlankLine(10), [1, 5, 1], $this->getLineFromString('???XXXX???'),
            ],
            // Puzzles that have some parts solved
            'edge expand' => [
                $this->getLineFromString('X??'), [2], $this->getLineFromString('XX.'),
            ],
            'edge 1 mark empty' => [
                $this->getLineFromString('X??'), [1], $this->getLineFromString('X.?'),
            ],
            'edge marked, clue to end of line' => [
                $this->getLineFromString('X??'), [3], $this->getLineFromString('XXX'),
            ],
//            'PS 1 1 in 3' => [
//                $this->getLineFromString('??#'), [1, 1], $this->getLineFromString('#X#'),
//            ],
//            'PS 2 in 3' => [
//                $this->getLineFromString('X#?'), [2], $this->getLineFromString('X##'),
//            ],
        ];
    }

    protected function getBlankLine($size)
    {
        return array_fill(0, $size, FitFinder::UNKNOWN);
    }

    /**
     * @param $badLine
     * @param $badClue
     * @dataProvider invalidLineProvider
     * @test
     */
    public function it_will_throw_an_exception_for_a_bad_line($badLine, $badClue)
    {
        try {
            $this->fitFinder->findBestFit($badLine, $badClue);
        } catch (\InvalidArgumentException $e) {
            return;
        }

        $this->fail('Invalid line/clue not detected');
    }

    public function invalidLineProvider()
    {
        return [
            'tooLong' => [$this->getBlankLine(1), [2]],
            'zeroWithNumber' => [$this->getBlankLine(15), [0, 1]],
            'bigTooLong' => [$this->getBlankLine(10), [3, 3, 3]],
            'bad blanks 3 in 1' => [$this->getLineFromString('...'), [1]],
            'bad blanks 2 in 3' => [$this->getLineFromString('.?.'), [2]],
            'overfilled 3 in 1' => [$this->getLineFromString('XXX'), [1]],
            'too much matched in 3 for 2' => [$this->getLineFromString('#X#X#'), [1, 1]],
            'overfilled 2 in 1' => [$this->getLineFromString('XX?'), [1]],
            'overfilled 2 in 1 alt' => [$this->getLineFromString('?XX'), [1]],
            'blocked 2 in 3' => [$this->getLineFromString('..X'), [2]],
        ];
    }

    public function getLineFromString($string)
    {
        return str_split($string);
    }
}