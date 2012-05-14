%{
/*
 * Test program: Calculator
 * by Zhao Cheng 5/13/2012
 */
#define YYSTYPE double
%}

%token NUMBER

%{
#include <stdio.h>
%}

%%

statement
    : expression { printf("= %f\n", $$); }
    ;

expression
    : /* nothing */ { $$ = 0; }
    | component { $$ = $1; }
    | expression '+' component { $$ = $1 + $3; }
    | expression '-' component { $$ = $1 - $3; }
    ;

factor
    : NUMBER { $$ = $1; }
    | '(' expression ')' { $$ = $2; }
    ;

component
    : factor { $$ = $1; }
    | component '*' factor { $$ = $1 * $3; }
    | component '/' factor { $$ = $1 / $3; }
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
