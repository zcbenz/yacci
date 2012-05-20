<?php
/**
 * Represents grammar
 */
class YaccGrammar
{
    /**
     * Options
     * @var array
     */
    public $options = array();

    /**
     * @var YaccSet<YaccNonterminal>
     */
    public $nonterminals;

    /**
     * @var YaccSet<YaccTerminal>
     */
    public $terminals;

    /**
     * @var YaccSet<YaccProduction>
     */
    public $productions;

    /**
     * @var YaccNonterminal
     */
    public $start;

    /**
     * Initializes grammar G = (N, T, P, S)
     * @param YaccSet<YaccNonterminal>
     * @param YaccSet<YaccTerminal>
     * @param YaccSet<YaccProduction>
     * @param YaccNonterminal
     */
    public function __construct(YaccSet $nonterminals, YaccSet $terminals, YaccSet $productions, YaccNonterminal $start)
    {
        // check
        if ($nonterminals->getType() !== 'YaccNonterminal') {
            throw new InvalidArgumentException(
                'YaccSet<YaccNonterminal> expected, YaccSet<' . 
                $nonterminals->getType() . '> given.'
            );
        }

        if ($terminals->getType() !== 'YaccTerminal') {
            throw new InvalidArgumentException(
                'YaccSet<YaccTerminal> expected, YaccSet<' .
                $terminals->getType() . '> given.'
            );
        }

        if ($productions->getType() !== 'YaccProduction') {
            throw new InvalidArgumentException(
                'YaccSet<YaccProduction> expected, YaccSet<' .
                $productions->getType() . '> given.'
            );
        }

        // initialize
        $this->nonterminals = $nonterminals;
        $this->terminals = $terminals;
        $this->productions = $productions;
        $this->start = $start;
    }
}
