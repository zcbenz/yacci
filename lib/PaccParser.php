<?php
/**
 * Fills grammar from token stream
 */
class PaccParser
{
    /**
     * Token stream
     * @var PaccTokenStream
     */
    private $stream;

    /**
     * @var PaccGrammar
     */
    private $grammar;

    /**
     * @var array
     */
    private $grammar_options = array();

    /**
     * @var PaccSet<PaccNonterminal>
     */
    private $nonterminals;

    /**
     * @var PaccSet<PaccTerminal>
     */
    private $terminals;

    /**
     * @var PaccSet<PaccProduction>
     */
    private $productions;

    /**
     * Start symbol
     * @var PaccNonterminal
     */
    private $start;

    /**
     * Initializes instance
     * @param PaccTokenStream
     */
    public function __construct(PaccTokenStream $stream)
    {
        $this->stream = $stream;
        $this->terminals = new PaccSet('PaccTerminal');
        $this->nonterminals = new PaccSet('PaccNonterminal');
        $this->productions = new PaccSet('PaccProduction');
    }

    /**
     * Parse
     * @return PaccGrammar
     */
    public function parse()
    {
        if ($this->grammar === NULL) {
            $this->grammar_options['prologue'] = '';
            for (;;) {
                if ($this->stream->current() instanceof PaccDeclarationToken && 
                    $this->stream->current()->value === '%token')
                {
                    $this->stream->next();
                    $this->token();
                } else if ($this->stream->current() instanceof PaccPrologueToken)
                {
                    $this->grammar_options['prologue'] .= $this->stream->current()->value;
                    $this->stream->next();
                } else { break; }
            }

            // encounter %%
            if (!$this->stream->current() instanceof PaccSectionToken)
                throw new PaccUnexpectedToken($this->stream->current());
            $this->stream->next();

            // rules section
            $this->rules();

            // encounter %%
            if (!$this->stream->current() instanceof PaccSectionToken)
                throw new PaccUnexpectedToken($this->stream->current());

            // epilogue section
            $this->grammar_options['epilogue'] = $this->stream->remainder();

            $this->grammar = new PaccGrammar($this->nonterminals, $this->terminals, $this->productions, $this->start);
            $this->grammar->options = $this->grammar_options;
        }

        return $this->grammar;
    }

    /**
     * @return string
     */
    private function code()
    {
        if (!($this->stream->current() instanceof PaccSpecialToken &&
            $this->stream->current()->value === '{'))
        {
            throw new PaccUnexpectedToken($this->stream->current());
        }
        $this->stream->next();

        if (!($this->stream->current() instanceof PaccCodeToken)) {
            throw new PaccUnexpectedToken($this->stream->current());
        }
        $code = $this->stream->current()->value;
        $this->stream->next();

        if (!($this->stream->current() instanceof PaccSpecialToken &&
            $this->stream->current()->value === '}'))
        {
            throw new PaccUnexpectedToken($this->stream->current());
        }
        $this->stream->next();

        return $code;
    }

    /**
     * @return void
     */
    private function token()
    {
        while ($this->stream->current() instanceof PaccIdToken) {
            $t = $this->stream->current();
            $this->terminals->add(new PaccTerminal($t->value, $t->value, NULL));

            $this->stream->next();
        }
    }

    /**
     * @return void
     */
    private function rules()
    {
        do {
            if (!($this->stream->current() instanceof PaccIdToken)) {
                throw new PaccUnexpectedToken($this->stream->current());
            }

            $name = new PaccNonterminal($this->stream->current()->value);
            if (($found = $this->nonterminals->find($name)) !== NULL) { $name = $found; }
            else { $this->nonterminals->add($name); }
            $this->stream->next();

            if ($this->start === NULL) {
                $this->start = $name;
            }

            if (!($this->stream->current() instanceof PaccSpecialToken &&
                $this->stream->current()->value === ':'))
            {
                throw new PaccUnexpectedToken($this->stream->current());
            }
            $this->stream->next();

            do {
                list($terms, $code) = $this->expression();
                $production = new PaccProduction($name, $terms, $code);
                if (($found = $this->productions->find($production)) === NULL) {
                    $this->productions->add($production);
                }

            } while ($this->stream->current() instanceof PaccSpecialToken &&
                $this->stream->current()->value === '|' &&
                !($this->stream->next() instanceof PaccEndToken));

            if (!($this->stream->current() instanceof PaccSpecialToken &&
                $this->stream->current()->value === ';'))
            {
                throw new PaccUnexpectedToken($this->stream->current());
            }
            $this->stream->next();

        } while (!($this->stream->current() instanceof PaccEndToken) &&
                 !($this->stream->current() instanceof PaccSectionToken));
    }

    /**
     * @return array
     */
    private function expression()
    {
        $terms = $this->terms();

        $code = NULL;
        if ($this->stream->current() instanceof PaccSpecialToken &&
            $this->stream->current()->value === '{')
        {
            $code = $this->code();
        }

        return array($terms, $code);
    }

    /**
     * @return array
     */
    private function terms()
    {
        $terms = array();

        while (($this->stream->current() instanceof PaccIdToken ||
            $this->stream->current() instanceof PaccStringToken))
        {
            $t = $this->stream->current();
            $this->stream->next();

            if ($t instanceof PaccIdToken) {
                $term = new PaccTerminal($t->value, $t->value, NULL);
                if (($found = $this->terminals->find($term)) !== NULL) { // terminal
                    $term = $found;
                } else { // nonterminal
                    $term = new PaccNonterminal($t->value);
                    if (($found = $this->nonterminals->find($term)) !== NULL) { $term = $found; }
                    else { $this->nonterminals->add($term); }
                }
            } else {
                assert($t instanceof PaccStringToken);
                $term = new PaccTerminal($t->value, NULL, $t->value);
                if (($found = $this->terminals->find($term)) !== NULL) { $term = $found; }
                else { $this->terminals->add($term); }
            }

            $terms[] = $term;
        }

        return $terms;
    }
}
