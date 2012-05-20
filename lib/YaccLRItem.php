<?php
/**
 * Production with dot and terminal
 */
class YaccLRItem
{
    /**
     * @var YaccProduction
     */
    public $production;

    /**
     * @var int
     */
    public $dot = 0;

    /**
     * @var int
     */
    public $terminalindex;

    /**
     * Initializes instance
     * @param YaccProduction
     * @param int
     * @param int
     */
    public function __construct(YaccProduction $production, $dot, $terminalindex)
    {
        $this->production = $production;
        $this->dot = $dot;
        $this->terminalindex = $terminalindex;
    }

    /**
     * @return YaccSymbol[]
     */
    public function beforeDot()
    {
        return array_slice($this->production->right, 0, $this->dot);
    }

    /**
     * @return YaccSymbol[]
     */
    public function afterDot()
    {
        return array_slice($this->production->right, $this->dot);
    }

    /**
     * @return bool
     */
    public function __eq($o)
    {
        if ($o instanceof self &&
            $this->production->__eq($o->production) &&
            $this->dot === $o->dot &&
            $this->terminalindex === $o->terminalindex)
        {
            return TRUE;
        }

        return FALSE;
    }

    public function __toString()
    {
        $ret = '[' .
            $this->production->left->name . 
            ' -> ';

        $syms = array();
        foreach ($this->beforeDot() as $symbol) { $syms[] = (string) $symbol; }
        $ret .= implode(' ', $syms);

        $ret .= ' . ';

        $syms = array();
        foreach ($this->afterDot() as $symbol) { $syms[] = (string) $symbol; }
        $ret .= implode(' ', $syms);
    
        $ret .= ', ' .
            $this->terminalindex .
            ']';
        return $ret;
    }
}
