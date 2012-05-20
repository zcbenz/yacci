<?php
/**
 * Nonterminal symbol
 */
class YaccNonterminal extends YaccSymbol {
    public function __toString()
    {
        return $this->name;
    }
}
