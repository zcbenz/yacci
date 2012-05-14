%{
#include <stdlib.h>
#include "y.tab.h"
extern double yylval;
%}

%option noyywrap

%%

[ \t]
\n	    { return 0; }
[0-9]+|[0-9]+\.[0-9]+|\.[0-9]+ { yylval = atof(yytext); return NUMBER; }
.       { return yytext[0]; }

%%

