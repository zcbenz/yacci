%{
/*
 * Test program: Calculator
 * by Zhao Cheng 5/13/2012
 */
%}

%token <val> NUMBER
%left '+' '-'
%left '*' '/'
%right '^'
%left NEG

%type <val> expression

%{
#include <stdio.h>
#include <stdlib.h>
#include <math.h>
%}

%union {
    double val;
}

%%

statement
    : /* empty */ { exit(0); }
    | expression { printf("= %f\n", $1); }
    ;

expression
    : NUMBER { $$ = $1; }
    | '(' expression ')' { $$ = $2; }
    | expression '*' expression { $$ = $1 * $3; }
    | expression '/' expression { $$ = $1 / $3; }
    | expression '+' expression { $$ = $1 + $3; }
    | expression '-' expression { $$ = $1 - $3; }
    | expression '^' expression { $$ = pow($1, $3); }
    | '-' expression %prec NEG { $$ = -$2; }
    ;

%%

int main()
{
    while (yyparse() == 0)
        ;

    return 0;
}

void yyerror(const char *msg)
{
    fprintf(stderr, "Error: %s\n", msg);
}
