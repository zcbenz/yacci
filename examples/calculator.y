%{
/*
 * Test program: Calculator
 * by Zhao Cheng 5/13/2012
 */
#define YYSTYPE double
%}

%token NUMBER
%left '+' '-'
%left '*' '/'
%right '^'

%{
#include <stdio.h>
#include <stdlib.h>
#include <math.h>
%}

%%

statement
    : /* empty */ { exit(0); }
    | expression { printf("= %f\n", $$); }
    ;

expression
    : factor { $$ = $1; }
    | expression '*' expression { $$ = $1 * $3; }
    | expression '/' expression { $$ = $1 / $3; }
    | expression '+' expression { $$ = $1 + $3; }
    | expression '-' expression { $$ = $1 - $3; }
    | expression '^' expression { $$ = pow($1, $3); }
    ;

factor
    : NUMBER { $$ = $1; }
    | '-' NUMBER { $$ = -$1; }
    | '(' expression ')' { $$ = $2; }
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
