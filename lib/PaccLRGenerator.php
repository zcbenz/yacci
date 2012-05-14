<?php
/**
 * Generates LR parser
 */
class PaccLRGenerator extends PaccGenerator
{
    /**
     * @var PaccGrammar
     */
    private $grammar;

    /**
     * Max symbol index (for table pitch)
     * @var int
     */
    private $table_pitch;

    /**
     * @var PaccSet<PaccLRItem>[]
     */
    private $states;

    /**
     * @var PaccLRJump[]
     */
    private $jumps;

    /**
     * @var PaccSymbol[]
     */
    private $index_map;

    /**
     * @var int[]
     */
    private $table = array();

    /**
     * @var string
     */
    private $generated;

    /**
     * Header of generated file
     * @var string
     */
    private $prologue;

    /**
     * Footer of generated file
     * @var string
     */
    private $epilogue;

    /**
     * Start number of tokens
     * @var int
     */
    private $tokenoff = 256;

    /**
     * Max number of tokens
     * @var int
     */
    private $tokenmax = 256;

    /**
     * In which state we accept
     * @var int
     */
    private $accpet_state = -1;

    /**
     * One indentation level
     * @var string
     */
    private $indentation = '    ';

    /**
     * End of line
     * @var string
     */
    private $eol = PHP_EOL;

    /**
     * Initializes generator
     * @param PaccGrammar
     */
    public function __construct(PaccGrammar $grammar)
    {
        $this->grammar = $grammar;

        // order sensitive actions!
        file_put_contents('php://stderr', 'augment... ');
        $this->augment();
        file_put_contents('php://stderr', 'indexes... ');
        $this->computeIndexes();
        file_put_contents('php://stderr', 'first... ');
        $this->computeFirst();
        file_put_contents('php://stderr', 'follow... ');
        $this->computeFollow();
        file_put_contents('php://stderr', 'states... ');
        $this->computeStates();
        file_put_contents('php://stderr', 'table... ');
        $this->computeTable();
        file_put_contents('php://stderr', "\n");

        foreach (array('epilogue', 'prologue', 'tokenoff', 'indentation', 'eol') as $name) {
            if (isset($grammar->options[$name])) {
                $this->$name = $grammar->options[$name];
            }
        }
    }

    /**
     * Generate parser
     * @return string
     */
    protected function generate()
    {
        if ($this->generated === NULL) { $this->doGenerate(); }

        return $this->generated;
    }

    /**
     * Really generates parser
     * @return string
     */
    private function doGenerate()
    {
        // predefined tokens
        $terminals_types = '';
        foreach ($this->grammar->terminals as $terminal) {
            if ($terminal->type !== NULL) {
                $this->tokenmax++;
                $terminals_types .= "#define {$terminal->type} " . ($terminal->index + $this->tokenoff) . $this->eol;
            }
        }

        // y.tab.h
        file_put_contents('y.tab.h', $terminals_types);

        // header
        $this->generated .= <<<E
static const char yysccsid[] = "Yacc by Zhao Cheng (zcbenz@gmail.com) 5/13/2012";

E;

        $this->generated .= $this->prologue;

        // productions
        $productions = array();
        $reductions = '';
        $i = 0;
        foreach ($this->grammar->productions as $production) {
            $length = count($production->right);
            $productions[] = "{{$production->left->index},{$length}}";

            if ($production->code !== NULL) {
                $code = $this->variables2C($production->code, count($production->right));
                $reductions .= 'case ' . $i . ':' . $this->eol;
                $reductions .= $this->indentation . '{ ' . $code . ' }' . $this->eol;
                $reductions .= $this->indentation . 'break;' . $this->eol;
            }
            $i++;
        }

        $this->generated .= "struct yyproduction_t { int left, len; } yyproductions[] = {\n  ";
        $this->generated .= implode(',', $productions) . $this->eol . '};' . $this->eol;

        // map chars to indexes
        $terminals_map = array_pad(array(), 256, 0);
        foreach ($this->grammar->terminals as $terminal)
            if ($terminal->type === NULL && $terminal->value !== NULL)
                $terminals_map[ord($terminal->value)] = $terminal->index;

        $this->generated .= "int yytermmap[] = {\n  " . implode(',', $terminals_map) . "\n};" . $this->eol;

        // jump table
        $nstates = count($this->states);
        $this->generated .= "int yytable[{$nstates}][{$this->table_pitch}] = {\n  ";
        for ($i = 0; $i < $nstates; $i++) {
            $states = array();
            for ($j = 0; $j < $this->table_pitch; $j++) {
                if (isset($this->table[$i * $this->table_pitch + $j]))
                    $states[] = $this->table[$i * $this->table_pitch + $j];
                else
                    $states[] = '0';
            }
            $this->generated .= '{' . implode(',', $states) . '},';
        }
        $this->generated .= $this->eol . '};' . $this->eol;

        // constants
        $this->generated .= <<<E

const int yytokenoff = {$this->tokenoff};
const int yytokenmax = {$this->tokenmax};
const int yyaccept_index = {$this->accpet_state};

{$terminals_types}
#ifndef YYSTYPE
typedef int YYSTYPE;
#endif

int yylex();
void yyerror(const char *msg);

#define YYINITSTACKSIZE 500
#define YYMAXDEPTH  500

typedef struct {
    unsigned stacksize;
    short    *s_base;
    short    *s_mark;
    short    *s_last;
    YYSTYPE  *l_base;
    YYSTYPE  *l_mark;
} YYSTACKDATA;
int      yychar;
YYSTYPE  yyval;
YYSTYPE  yylval;

/* variables for the parser stack */
static YYSTACKDATA yystack;

#ifdef YYDEBUG
#include <stdio.h>
#endif

#include <stdlib.h>	/* needed for malloc, etc */
#include <string.h>	/* needed for memset */

/* allocate initial stack or double stack size, up to YYMAXDEPTH */
static int yygrowstack(YYSTACKDATA *data)
{
    int i;
    unsigned newsize;
    short *newss;
    YYSTYPE *newvs;

    if ((newsize = data->stacksize) == 0)
        newsize = YYINITSTACKSIZE;
    else if (newsize >= YYMAXDEPTH)
        return -1;
    else if ((newsize *= 2) > YYMAXDEPTH)
        newsize = YYMAXDEPTH;

    i = data->s_mark - data->s_base;
    newss = (short *)realloc(data->s_base, newsize * sizeof(*newss));
    if (newss == 0)
        return -1;

    data->s_base = newss;
    data->s_mark = newss + i;

    newvs = (YYSTYPE *)realloc(data->l_base, newsize * sizeof(*newvs));
    if (newvs == 0)
        return -1;

    data->l_base = newvs;
    data->l_mark = newvs + i;

    data->stacksize = newsize;
    data->s_last = data->s_base + newsize - 1;
    return 0;
}

static void yyfreestack(YYSTACKDATA *data)
{
    free(data->s_base);
    free(data->l_base);
    memset(data, 0, sizeof(*data));
}

#define YYABORT  goto yyabort;
#define YYREJECT goto yyabort;
#define YYACCEPT goto yyaccept;

int yyparse()
{
    int yystate;

    /* init stack */
    memset(&yystack, 0, sizeof(yystack));

    if (yystack.s_base == NULL && yygrowstack(&yystack)) goto yyoverflow;
    yystack.s_mark = yystack.s_base;
    yystack.l_mark = yystack.l_base;
    *yystack.s_mark = 0;

    yychar = yylex();
    for (;;) {
        yystate = *yystack.s_mark;
        int terminal = 0;

        // translate token to index
        if (yychar > yytokenmax) {
            yyerror("invalid token");
            YYABORT;
        } else if (yychar > yytokenoff) { // %token stuff
            terminal = yychar - yytokenoff;
        } else if (yychar > 0) {
            terminal = yytermmap[yychar];
            if (terminal == 0) {
                yyerror("invalid char");
                YYABORT;
            }
        } else if (yychar < 0) {
            yyerror("invalid input");
            YYABORT;
        }

        int action = yytable[yystate][terminal];
        if (action == 0) { // => accept?
            if (yystate * {$this->table_pitch} + terminal != yyaccept_index) {
                yyerror("invalid action");
                YYABORT;
            }
#ifdef YYDEBUG
            printf("Accept\\n");
#endif
            YYACCEPT;
        } else if (action > 0) { // => shift
#ifdef YYDEBUG
            printf("Shift to %d\\n", action);
#endif
            if (yystack.s_mark >= yystack.s_last && yygrowstack(&yystack))
            {
                goto yyoverflow;
            }
            *++yystack.s_mark = action;
            *++yystack.l_mark = yylval;
            yychar = yylex();
        } else { // action < 0 => reduce
            action = -action - 1;

            int yym = yyproductions[action].len;
            if (yym)
                yyval = yystack.l_mark[1-yym];
            else
                memset(&yyval, 0, sizeof yyval);

            switch (action) {
$reductions
            }

            yystack.s_mark -= yym;
            yystack.l_mark -= yym;
            
            int go = yytable[*yystack.s_mark][yyproductions[action].left];

#ifdef YYDEBUG
            printf("Goto %d\\n", go);
#endif
            *++yystack.s_mark = go;
            *++yystack.l_mark = yyval;
        }
    }

yyoverflow:
    yyerror("yacc stack overflow");
    yyfreestack(&yystack);
    return 2;

yyabort:
    yyfreestack(&yystack);
    return 1;

yyaccept:
    yyfreestack(&yystack);
    return 0;
}
E;
        $this->generated .= $this->epilogue;
    }

    /**
     * Converts special variables to C stack variables
     * @param string
     * @return string
     */
    protected function variables2C($s, $len)
    {
        $r = preg_replace('/\$\$/S', 'yyval', $s);
        if ($r !== NULL)
            $s = $r;

        $r = preg_replace_callback('/\$([1-9]+)/S', function ($matches) use ($len) {
            return 'yystack.l_mark[' . ($matches[1] - $len) . ']';
        }, $s);
        if ($r !== NULL)
            $s = $r;

        return $s;
    }


    /**
     * Adds new start nonterminal and end terminal
     * @return void
     */
    private function augment()
    {
        $newStart = new PaccNonterminal('$start');
        $this->grammar->startProduction = new PaccProduction($newStart, array($this->grammar->start), NULL);
        $this->grammar->productions->add($this->grammar->startProduction);
        $this->grammar->nonterminals->add($newStart);
        $this->grammar->start = $newStart;

        $this->grammar->epsilon = new PaccTerminal('$epsilon');
        $this->grammar->epsilon->index = -1;

        $this->grammar->end = new PaccTerminal('$end');
        $this->grammar->end->index = 0;
        $this->grammar->end->first = new PaccSet('integer');
        $this->grammar->end->first->add($this->grammar->end->index);
    }

    /**
     * Compute grammar symbols and productions indexes
     * @return void
     */
    private function computeIndexes()
    {
        $i = 1;
        foreach ($this->grammar->terminals as $terminal) {
            $terminal->index = $i++;
            $terminal->first = new PaccSet('integer');
            $terminal->first->add($terminal->index);
            $this->index_map[$terminal->index] = $terminal;
        }
        $this->grammar->terminals->add($this->grammar->end);

        foreach ($this->grammar->nonterminals as $nonterminal) {
            $nonterminal->first = new PaccSet('integer');
            $nonterminal->follow = new PaccSet('integer');
            $nonterminal->index = $i++;
            $this->index_map[$terminal->index] = $terminal;
        }

        $this->table_pitch = $i - 1;

        $i = 1;
        foreach ($this->grammar->productions as $production) {
            $production->index = $i++;
        }
    }

    /**
     * @return void
     */
    private function computeFirst()
    {
        foreach ($this->grammar->productions as $production) {
            if (count($production->right) === 0) {
                $production->left->first->add($this->grammar->epsilon->index);
            }
        }

        do {
            $done = TRUE;
            foreach ($this->grammar->productions as $production) {
                foreach ($production->right as $symbol) {
                    foreach ($symbol->first as $index) {
                        if ($index !== $this->grammar->epsilon->index &&
                            !$production->left->first->contains($index))
                        {
                            $production->left->first->add($index);
                            $done = FALSE;
                        }
                    }

                    if (!$symbol->first->contains($this->grammar->epsilon->index)) { break; }
                }
            }
        } while (!$done);
    }

    /**
     * @return void
     */
    private function computeFollow()
    {
        $this->grammar->start->follow->add($this->grammar->end->index);

        foreach ($this->grammar->productions as $production) {
            for ($i = 0, $len = count($production->right) - 1; $i < $len; ++$i) {
                if ($production->right[$i] instanceof PaccTerminal) { continue; }
                foreach ($production->right[$i + 1]->first as $index) {
                    if ($index === $this->grammar->epsilon->index) { continue; }
                    $production->right[$i]->follow->add($index);
                }
            }
        }

        do {
            $done = TRUE;
            foreach ($this->grammar->productions as $production) {
                for ($i = 0, $len = count($production->right); $i < $len; ++$i) {
                    if ($production->right[$i] instanceof PaccTerminal) { continue; }

                    $empty_after = TRUE;
                    for ($j = $i + 1; $j < $len; ++$j) {
                        if (!$production->right[$j]->first->contains($this->grammar->epsilon->index)) {
                            $empty_after = FALSE;
                            break;
                        }
                    }

                    if ($empty_after && !$production->right[$i]->follow->contains($production->left->follow)) {
                        $production->right[$i]->follow->add($production->left->follow);
                        $done = FALSE;
                    }
                }
            }
        } while (!$done);
    }

    /**
     * @return void
     */
    private function computeStates()
    {
        $items = new PaccSet('PaccLRItem');
        $items->add(new PaccLRItem($this->grammar->startProduction, 0, $this->grammar->end->index));
        $this->states = array($this->closure($items));
        $symbols = new PaccSet('PaccSymbol');
        $symbols->add($this->grammar->nonterminals);
        $symbols->add($this->grammar->terminals);

        for ($i = 0; $i < count($this->states); ++$i) { // intentionally count() in second clause
            foreach ($symbols as $symbol) {
                $jump = $this->jump($this->states[$i], $symbol);
                if ($jump->isEmpty()) { continue; }
                $already_in = FALSE;
                foreach ($this->states as $state) {
                    if ($state->__eq($jump)) {
                        $already_in = TRUE;
                        $jump = $state;
                        break;
                    }
                }

                if (!$already_in) {
                    $this->states[] = $jump;
                }
                
                $this->jumps[] = new PaccLRJump($this->states[$i], $symbol, $jump);
            }
        }
    }

    /**
     * @return void
     */
    private function computeTable()
    {
        for ($state = 0, $len = count($this->states); $state < $len; ++$state) {
            $items = $this->states[$state];

            // shifts
            foreach ($this->grammar->terminals as $terminal) {
                $do_shift = FALSE;

                foreach ($items as $item) {
                    if (current($item->afterDot()) !== FALSE &&
                        current($item->afterDot())->__eq($terminal))
                    {
                        $do_shift = TRUE;
                        break;
                    }
                }

                if ($do_shift) {
                    $this->table[$state * $this->table_pitch + $terminal->index] =
                        $this->getNextState($items, $terminal);
                    if ($this->table[$state * $this->table_pitch + $terminal->index] === NULL) {
                        throw new Exception('Cannot get next state for shift.');
                    }
                }
            }

            // reduces/accepts
            foreach ($items as $item) {
                if (count($item->afterDot()) > 0) { continue; }
                $tableindex = $state * $this->table_pitch + $item->terminalindex;

                if ($item->production->__eq($this->grammar->startProduction)) { // accept
                    $this->accpet_state = $tableindex;
                    $this->table[$tableindex] = 0;
                } else {
                    $do_reduce = TRUE;
                    if (isset($this->table[$tableindex])) {
                        if ($this->table[$tableindex] > 0) {
                            $terminal = $this->index_map[$item->terminalindex];
                            assert($terminal instanceof PaccTerminal);
                            if ($item->production->precedence === NULL &&
                                $terminal->precedence === NULL)
                            {
                                throw new Exception('Shift-reduce conflict: ' . $item);
                            } else if ($item->production->precedence === NULL ||
                                       $terminal->precedence === NULL)
                            {
                                // shift, do nothing
                                $do_reduce = FALSE;
                            } else if (abs($item->production->precedence) < abs($terminal->precedence))
                            {
                                // shift, do nothing
                                $do_reduce = FALSE;
                            } else if (abs($item->production->precedence) == abs($terminal->precedence))
                            {
                                // shift/reduce by associativity of production
                                $do_reduce = $terminal->precedence > 0;
                            } else
                            {
                                // reduce
                                $do_reduce = TRUE;
                            }
                        } else if ($this->table[$tableindex] < 0) {
                            throw new Exception('Reduce-reduce conflict: ' . $item);
                        } else {
                            throw new Exception('Accpet-reduce conflict: ' . $item);
                        }
                    }

                    if ($do_reduce)
                        $this->table[$tableindex] = -$item->production->index;
                }
            }

            // gotos
            foreach ($this->grammar->nonterminals as $nonterminal) {
                $this->table[$state * $this->table_pitch + $nonterminal->index] =
                    $this->getNextState($items, $nonterminal);
            }
        }
    }

    /**
     * @return int
     */
    private function getNextState(PaccSet $items, PaccSymbol $symbol)
    {
        if ($items->getType() !== 'PaccLRItem') {
            throw new InvalidArgumentException(
                'Bad type - expected PaccSet<LRItem>, given PaccSet<' .
                $items->getType() . '>.'
            );
        }

        foreach ($this->jumps as $jump) {
            if ($jump->from->__eq($items) && $jump->symbol->__eq($symbol)) {
                for ($i = 0, $len = count($this->states); $i < $len; ++$i) {
                    if ($jump->to->__eq($this->states[$i])) {
                        return $i;
                    }
                }
            }
        }

        return NULL;
    }

    /**
     * @return PaccSet<PaccLRItem>
     */
    private function closure(PaccSet $items)
    {
        if ($items->getType() !== 'PaccLRItem') {
            throw new InvalidArgumentException(
                'Bad type - expected PaccSet<LRItem>, given PaccSet<' .
                $items->getType() . '>.'
            );
        }

        do {
            $done = TRUE;

            $itemscopy = clone $items;

            foreach ($items as $item) {
                if (!(count($item->afterDot()) >= 1 &&
                    current($item->afterDot()) instanceof PaccNonterminal))
                {
                    continue;
                }

                $newitems = new PaccSet('PaccLRItem');
                $beta_first = new PaccSet('integer');
                if (count($item->afterDot()) > 1) {
                    $beta_first->add(next($item->afterDot())->first);
                    $beta_first->delete($this->grammar->epsilon->index);
                }

                if ($beta_first->isEmpty()) {
                    $beta_first->add($item->terminalindex);
                }
                $B = current($item->afterDot());

                foreach ($this->grammar->productions as $production) {
                    if ($B->__eq($production->left)) {
                        foreach ($beta_first as $terminalindex) {
                            $newitems->add(new PaccLRItem($production, 0, $terminalindex));
                        }
                    }
                }

                if (!$newitems->isEmpty() && !$itemscopy->contains($newitems)) {
                    $itemscopy->add($newitems);
                    $done = FALSE;
                }
            }

            $items = $itemscopy;

        } while (!$done);

        return $items;
    }

    /**
     * @param PaccSet<PaccLRItem>
     * @param PaccSymbol
     * @return PaccSet<PaccLRItem>
     */
    private function jump(PaccSet $items, PaccSymbol $symbol)
    {
        if ($items->getType() !== 'PaccLRItem') {
            throw new InvalidArgumentException(
                'Bad type - expected PaccSet<LRItem>, given PaccSet<' .
                $items->getType() . '>.'
            );
        }

        $ret = new PaccSet('PaccLRItem');

        foreach ($items as $item) {
            if (!(current($item->afterDot()) !== FALSE &&
                current($item->afterDot())->__eq($symbol)))
            {
                continue;
            }

            $ret->add(new PaccLRItem($item->production, $item->dot + 1, $item->terminalindex));
        }

        return $this->closure($ret);
    }
}
