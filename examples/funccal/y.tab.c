static const char yysccsid[] = "Yacc by Zhao Cheng (zcbenz@gmail.com) 5/13/2012";

/*
 * Test program: Advanced Calculator
 * by Zhao Cheng 5/20/2012
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <math.h>
#include "calc.h"  /* Contains definition of `symrec'.  */
struct yyproduction_t { int left, len; } yyproductions[] = {
  {14,0},{14,1},{13,1},{13,1},{13,3},{13,4},{13,3},{13,3},{13,3},{13,3},{13,3},{13,2},{13,3},{15,1}
};
int yytermmap[] = {
  0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,11,12,7,5,0,6,0,8,0,0,0,0,0,0,0,0,0,0,0,0,0,4,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,9,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0
};
int yytable[49][15] = {
  {-1,3,4,5,0,0,6,0,0,0,0,7,0,1,2},{-2,0,0,0,0,8,9,10,11,12,0,0,0,0,0},{0,0,0,0,0,0,0,0,0,0,0,0,0,0,0},{-3,0,0,0,0,-3,-3,-3,-3,-3,0,0,0,0,0},{-4,0,0,0,13,-4,-4,-4,-4,-4,0,0,0,0,0},{0,0,0,0,0,0,0,0,0,0,0,14,0,0,0},{0,3,4,5,0,0,6,0,0,0,0,7,0,15,0},{0,17,18,19,0,0,20,0,0,0,0,21,0,16,0},{0,3,4,5,0,0,6,0,0,0,0,7,0,22,0},{0,3,4,5,0,0,6,0,0,0,0,7,0,23,0},{0,3,4,5,0,0,6,0,0,0,0,7,0,24,0},{0,3,4,5,0,0,6,0,0,0,0,7,0,25,0},{0,3,4,5,0,0,6,0,0,0,0,7,0,26,0},{0,3,4,5,0,0,6,0,0,0,0,7,0,27,0},{0,17,18,19,0,0,20,0,0,0,0,21,0,28,0},{-12,0,0,0,0,-12,-12,-12,-12,-12,0,0,0,0,0},{0,0,0,0,0,29,30,31,32,33,0,0,34,0,0},{0,0,0,0,0,-3,-3,-3,-3,-3,0,0,-3,0,0},{0,0,0,0,35,-4,-4,-4,-4,-4,0,0,-4,0,0},{0,0,0,0,0,0,0,0,0,0,0,36,0,0,0},{0,17,18,19,0,0,20,0,0,0,0,21,0,37,0},{0,17,18,19,0,0,20,0,0,0,0,21,0,38,0},{-9,0,0,0,0,-9,-9,10,11,12,0,0,0,0,0},{-10,0,0,0,0,-10,-10,10,11,12,0,0,0,0,0},{-7,0,0,0,0,-7,-7,-7,-7,12,0,0,0,0,0},{-8,0,0,0,0,-8,-8,-8,-8,12,0,0,0,0,0},{-11,0,0,0,0,-11,-11,-11,-11,12,0,0,0,0,0},{-5,0,0,0,0,8,9,10,11,12,0,0,0,0,0},{0,0,0,0,0,29,30,31,32,33,0,0,39,0,0},{0,17,18,19,0,0,20,0,0,0,0,21,0,40,0},{0,17,18,19,0,0,20,0,0,0,0,21,0,41,0},{0,17,18,19,0,0,20,0,0,0,0,21,0,42,0},{0,17,18,19,0,0,20,0,0,0,0,21,0,43,0},{0,17,18,19,0,0,20,0,0,0,0,21,0,44,0},{-13,0,0,0,0,-13,-13,-13,-13,-13,0,0,0,0,0},{0,17,18,19,0,0,20,0,0,0,0,21,0,45,0},{0,17,18,19,0,0,20,0,0,0,0,21,0,46,0},{0,0,0,0,0,-12,-12,-12,-12,-12,0,0,-12,0,0},{0,0,0,0,0,29,30,31,32,33,0,0,47,0,0},{-6,0,0,0,0,-6,-6,-6,-6,-6,0,0,0,0,0},{0,0,0,0,0,-9,-9,31,32,33,0,0,-9,0,0},{0,0,0,0,0,-10,-10,31,32,33,0,0,-10,0,0},{0,0,0,0,0,-7,-7,-7,-7,33,0,0,-7,0,0},{0,0,0,0,0,-8,-8,-8,-8,33,0,0,-8,0,0},{0,0,0,0,0,-11,-11,-11,-11,33,0,0,-11,0,0},{0,0,0,0,0,29,30,31,32,33,0,0,-5,0,0},{0,0,0,0,0,29,30,31,32,33,0,0,48,0,0},{0,0,0,0,0,-13,-13,-13,-13,-13,0,0,-13,0,0},{0,0,0,0,0,-6,-6,-6,-6,-6,0,0,-6,0,0},
};

typedef union {
    double val;    /* For returning numbers.  */
    symrec *tptr;  /* For returning symbol-table pointers.  */
} YYSTYPE;

const int yytokenoff = 256;
const int yyaccept_index = 30;

#define NUMBER 257
#define VAR 258
#define FNCT 259
#define NEG 266


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
        if (yychar > yytokenoff) { // %token stuff
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
            if (yystate * 15 + terminal != yyaccept_index) {
                yyerror("invalid action");
                YYABORT;
            }
#ifdef YYDEBUG
            printf("Accept\n");
#endif
            YYACCEPT;
        } else if (action > 0) { // => shift
#ifdef YYDEBUG
            printf("Shift to %d\n", action);
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
case 0:
    {  exit(0);  }
    break;
case 1:
    {  printf("= %f\n", yystack.l_mark[0].val);  }
    break;
case 2:
    {  yyval.val = yystack.l_mark[0].val;  }
    break;
case 3:
    {  yyval.val = yystack.l_mark[0].tptr->value.var;  }
    break;
case 4:
    {  yyval.val = yystack.l_mark[0].val; yystack.l_mark[-2].tptr->value.var = yystack.l_mark[0].val;  }
    break;
case 5:
    {  yyval.val = (*(yystack.l_mark[-3].tptr->value.fnctptr))(yystack.l_mark[-1].val);  }
    break;
case 6:
    {  yyval.val = yystack.l_mark[-2].val * yystack.l_mark[0].val;  }
    break;
case 7:
    {  yyval.val = yystack.l_mark[-2].val / yystack.l_mark[0].val;  }
    break;
case 8:
    {  yyval.val = yystack.l_mark[-2].val + yystack.l_mark[0].val;  }
    break;
case 9:
    {  yyval.val = yystack.l_mark[-2].val - yystack.l_mark[0].val;  }
    break;
case 10:
    {  yyval.val = pow(yystack.l_mark[-2].val, yystack.l_mark[0].val);  }
    break;
case 11:
    {  yyval.val = -yystack.l_mark[0].val;  }
    break;
case 12:
    {  yyval.val = yystack.l_mark[-1].val;  }
    break;

            }

            yystack.s_mark -= yym;
            yystack.l_mark -= yym;
            
            int go = yytable[*yystack.s_mark][yyproductions[action].left];

#ifdef YYDEBUG
            printf("Goto %d\n", go);
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

struct init
{
    char const *fname;
    double (*fnct) (double);
};

struct init const arith_fncts[] =
{
    "sin"   , sin   , 
    "asin"  , asin  , 
    "cos"   , cos   , 
    "acos"  , acos  , 
    "tan"   , tan   , 
    "atan"  , atan  , 
    "ceil"  , ceil  , 
    "floor" , floor , 
    "abs"   , fabs  , 
    "ln"    , log   , 
    "log"   , log10 , 
    "lg"    , log2  , 
    "exp"   , exp   , 
    "sqrt"  , sqrt  , 
    0       , 0
};

/* The symbol table: a chain of `struct symrec'.  */
symrec *sym_table;

/* Put arithmetic functions in table.  */
void init_table (void)
{
    int i;
    symrec *ptr;
    for (i = 0; arith_fncts[i].fname != 0; i++) {
        ptr = putsym (arith_fncts[i].fname, FNCT);
        ptr->value.fnctptr = arith_fncts[i].fnct;
    }
}

int main()
{
    init_table();

    while (yyparse() == 0)
        ;

    return 0;
}

void yyerror(const char *msg)
{
    fprintf(stderr, "Error: %s\n", msg);
}

symrec *
putsym (char const *sym_name, int sym_type)
{
  symrec *ptr;
  ptr = (symrec *) malloc (sizeof (symrec));
  ptr->name = (char *) malloc (strlen (sym_name) + 1);
  strcpy (ptr->name,sym_name);
  ptr->type = sym_type;
  ptr->value.var = 0; /* Set value to 0 even if fctn.  */
  ptr->next = (struct symrec *)sym_table;
  sym_table = ptr;
  return ptr;
}

symrec *
getsym (char const *sym_name)
{
  symrec *ptr;
  for (ptr = sym_table; ptr != (symrec *) 0;
       ptr = (symrec *)ptr->next)
    if (strcmp (ptr->name,sym_name) == 0)
      return ptr;
  return 0;
}
