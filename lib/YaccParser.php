<?php
/**
 * Fills grammar from token stream
 */
class YaccParser
{
    /**
     * Token stream
     * @var YaccTokenStream
     */
    private $stream;

    /**
     * @var YaccGrammar
     */
    private $grammar;

    /**
     * @var array
     */
    private $grammar_options = array();

    /**
     * @var YaccSet<YaccNonterminal>
     */
    private $nonterminals;

    /**
     * @var YaccSet<YaccTerminal>
     */
    private $terminals;

    /**
     * @var YaccSet<YaccProduction>
     */
    private $productions;

    /**
     * @var int
     */
    private $current_precedence = 1;

    /**
     * Start symbol
     * @var YaccNonterminal
     */
    private $start;

    /**
     * Initializes instance
     * @param YaccTokenStream
     */
    public function __construct(YaccTokenStream $stream)
    {
        $this->stream = $stream;
        $this->terminals = new YaccSet('YaccTerminal');
        $this->nonterminals = new YaccSet('YaccNonterminal');
        $this->productions = new YaccSet('YaccProduction');
    }

    /**
     * Parse
     * @return YaccGrammar
     */
    public function parse()
    {
        if ($this->grammar === NULL) {
            $this->grammar_options['prologue'] = '';
            for (;;) {
                if ($this->stream->current() instanceof YaccDeclarationToken && 
                    $this->stream->current()->value === '%token')
                {
                    $this->stream->next();
                    $this->token();
                } else if ($this->stream->current() instanceof YaccDeclarationToken && 
                    $this->stream->current()->value === '%left')
                {
                    $this->stream->next();
                    $this->precedence(true);
                } else if ($this->stream->current() instanceof YaccDeclarationToken && 
                    $this->stream->current()->value === '%right')
                {
                    $this->stream->next();
                    $this->precedence(false);
                } else if ($this->stream->current() instanceof YaccPrologueToken)
                {
                    $this->grammar_options['prologue'] .= $this->stream->current()->value;
                    $this->stream->next();
                } else { break; }
            }

            // encounter %%
            if (!$this->stream->current() instanceof YaccSectionToken)
                throw new YaccUnexpectedToken($this->stream->current());
            $this->stream->next();

            // rules section
            $this->rules();

            // encounter %%
            if (!$this->stream->current() instanceof YaccSectionToken)
                throw new YaccUnexpectedToken($this->stream->current());

            // epilogue section
            $this->grammar_options['epilogue'] = $this->stream->remainder();

            $this->grammar = new YaccGrammar($this->nonterminals, $this->terminals, $this->productions, $this->start);
            $this->grammar->options = $this->grammar_options;
        }

        return $this->grammar;
    }

    /**
     * @return string
     */
    private function code()
    {
        if (!($this->stream->current() instanceof YaccSpecialToken &&
            $this->stream->current()->value === '{'))
        {
            throw new YaccUnexpectedToken($this->stream->current());
        }
        $this->stream->next();

        if (!($this->stream->current() instanceof YaccCodeToken)) {
            throw new YaccUnexpectedToken($this->stream->current());
        }
        $code = $this->stream->current()->value;
        $this->stream->next();

        if (!($this->stream->current() instanceof YaccSpecialToken &&
            $this->stream->current()->value === '}'))
        {
            throw new YaccUnexpectedToken($this->stream->current());
        }
        $this->stream->next();

        return $code;
    }

    /**
     * @return void
     */
    private function token()
    {
        while ($this->stream->current() instanceof YaccIdToken) {
            $t = $this->stream->current();
            $this->terminals->add(new YaccTerminal($t->value, $t->value, NULL));

            $this->stream->next();
        }
    }

    /**
     * @return void
     */
    private function precedence($left = true)
    {
        while ($this->stream->current() instanceof YaccIdToken ||
               $this->stream->current() instanceof YaccStringToken)
        {
            $t = $this->stream->current();
            if ($t instanceof YaccIdToken) {
                $term = new YaccTerminal($t->value, $t->value, NULL);
                if (($found = $this->terminals->find($term)) !== NULL) { $term = $found; }
                else { $this->terminals->add($term); }
                $term->precedence = $this->current_precedence;
            } else {
                assert($t instanceof YaccStringToken);
                $term = new YaccTerminal($t->value, NULL, $t->value);
                if (($found = $this->terminals->find($term)) !== NULL) { $term = $found; }
                else { $this->terminals->add($term); }
                $term->precedence = $this->current_precedence;
            }

            if (!$left) $term->precedence = -$term->precedence;
            $this->stream->next();
        }

        $this->current_precedence++;
    }

    /**
     * @return void
     */
    private function rules()
    {
        do {
            if (!($this->stream->current() instanceof YaccIdToken)) {
                throw new YaccUnexpectedToken($this->stream->current());
            }

            $name = new YaccNonterminal($this->stream->current()->value);
            if (($found = $this->nonterminals->find($name)) !== NULL) { $name = $found; }
            else { $this->nonterminals->add($name); }
            $this->stream->next();

            if ($this->start === NULL) {
                $this->start = $name;
            }

            if (!($this->stream->current() instanceof YaccSpecialToken &&
                $this->stream->current()->value === ':'))
            {
                throw new YaccUnexpectedToken($this->stream->current());
            }
            $this->stream->next();

            do {
                list($terms, $code, $precedence) = $this->expression();
                $production = new YaccProduction($name, $terms, $code);
                if ($precedence !== NULL) {
                    $production->precedence = $precedence;
                }
                if (($found = $this->productions->find($production)) === NULL) {
                    $this->productions->add($production);
                }

            } while ($this->stream->current() instanceof YaccSpecialToken &&
                $this->stream->current()->value === '|' &&
                !($this->stream->next() instanceof YaccEndToken));

            if (!($this->stream->current() instanceof YaccSpecialToken &&
                $this->stream->current()->value === ';'))
            {
                throw new YaccUnexpectedToken($this->stream->current());
            }
            $this->stream->next();

        } while (!($this->stream->current() instanceof YaccEndToken) &&
                 !($this->stream->current() instanceof YaccSectionToken));
    }

    /**
     * @return array
     */
    private function expression()
    {
        list($terms, $precedence) = $this->terms();

        $code = NULL;
        if ($this->stream->current() instanceof YaccSpecialToken &&
            $this->stream->current()->value === '{')
        {
            $code = $this->code();
        }

        return array($terms, $code, $precedence);
    }

    /**
     * @return array
     */
    private function terms()
    {
        $terms = array();

        while (($this->stream->current() instanceof YaccIdToken ||
            $this->stream->current() instanceof YaccStringToken))
        {
            $t = $this->stream->current();
            $this->stream->next();

            if ($t instanceof YaccIdToken) {
                $term = new YaccTerminal($t->value, $t->value, NULL);
                if (($found = $this->terminals->find($term)) !== NULL) { // terminal
                    $term = $found;
                } else { // nonterminal
                    $term = new YaccNonterminal($t->value);
                    if (($found = $this->nonterminals->find($term)) !== NULL) { $term = $found; }
                    else { $this->nonterminals->add($term); }
                }
            } else {
                assert($t instanceof YaccStringToken);
                $term = new YaccTerminal($t->value, NULL, $t->value);
                if (($found = $this->terminals->find($term)) !== NULL) { $term = $found; }
                else { $this->terminals->add($term); }
            }

            $terms[] = $term;
        }

        // contextual precedence
        $precedence = NULL;
        if ($this->stream->current() instanceof YaccDeclarationToken && 
            $this->stream->current()->value === '%prec')
        {
            $this->stream->next();
            if ($this->stream->current() instanceof YaccIdToken)
            {
                $t = $this->stream->current();
                $term = new YaccTerminal($t->value, $t->value, NULL);
                if (($found = $this->terminals->find($term)) !== NULL) { $term = $found; }
                else { throw new YaccUnexpectedToken($t); }

                $precedence = $term->precedence;
                $this->stream->next();
            }
        }

        return array($terms, $precedence);
    }
}
