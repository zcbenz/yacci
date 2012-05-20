<?php
/**
 * Grammar production
 */
class YaccProduction
{
    /**
     * @var YaccNonterminal
     */
    public $left;

    /**
     * @var YaccSymbol[]
     */
    public $right;

    /**
     * @var int
     */
    public $index;

    /**
     * @var int
     */
    public $precedence = NULL;

    /**
     * @var string
     */
    public $code;

    /**
     * Initializes production
     * @param YaccNonterminal
     * @param YaccSymbol[]
     * @param string
     */
    public function __construct(YaccNonterminal $left, array $right, $code = NULL)
    {
        $this->left = $left;

        foreach ($right as $symbol) {
            if (!($symbol instanceof YaccSymbol)) {
                throw new InvalidArgumentException('Right has to be array of YaccSymbol.');
            }

            // each rule gets its precedence from
            // the last terminal symbol mentioned in the components.
            if ($symbol instanceof YaccTerminal)
            {
                $this->precedence = $symbol->precedence;
            }
        }
        $this->right = $right;

        $this->code = $code;
    }

    /**
     * @return bool
     */
    public function __eq($o)
    {
        if ($o instanceof self &&
            $this->left->__eq($o->left) &&
            count($this->right) === count($o->right) &&
            $this->code === $o->code)
        {
            for ($i = 0, $len = count($this->right); $i < $len; ++$i) {
                if (!$this->right[$i]->__eq($o->right[$i])) { return FALSE; }
            }

            return TRUE;
        }

        return FALSE;
    }
}
