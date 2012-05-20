<?php
/**
 * Represents jump from one state to another
 */
class YaccLRJump
{
    /**
     * Begining state
     * @var YaccSet<YaccLRItem>
     */
    public $from;

    /**
     * @var YaccSymbol
     */
    public $symbol;

    /**
     * Ending state
     * @var YaccSet<YaccLRItem>
     */
    public $to;

    /**
     * Initializes instance
     * @param YaccSet<YaccLRItem>
     * @param YaccSymbol
     * @param YaccSet<YaccLRItem>
     */
    public function __construct($from, $symbol, $to)
    {
        $this->from = $from;
        $this->symbol = $symbol;
        $this->to = $to;
    }
}
