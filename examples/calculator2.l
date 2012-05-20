%{
#include <stdlib.h>
#include "y.tab.h"
extern union { double val; } yylval;
%}

%option noyywrap

%%

[ \t]
\n	    { return 0; }
[0-9]+|[0-9]+\.[0-9]+|\.[0-9]+ { yylval.val = atof(yytext); return NUMBER; }
.       { return yytext[0]; }

%%

