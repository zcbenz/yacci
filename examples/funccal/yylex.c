#include <stdio.h>
#include <stdlib.h>
#include "y.tab.h"
#include "calc.h"

extern union {
    double val;    /* For returning numbers.  */
    symrec *tptr;  /* For returning symbol-table pointers.  */
} yylval;

#include <ctype.h>
int yylex (void)
{
  int c;
  /* Ignore white space, get first nonwhite character.  */
  while ((c = getchar ()) == ' ' || c == '\t');
  if (c == EOF || c == '\n')
    return 0;
  /* Char starts a number => parse the number.         */
  if (c == '.' || isdigit (c))
    {
      ungetc (c, stdin);
      scanf ("%lf", &yylval.val);
      return NUMBER;
    }
  /* Char starts an identifier => read the name.       */
  if (isalpha (c))
    {
      symrec *s;
      static char *symbuf = 0;
      static int length = 0;
      int i;
      /* Initially make the buffer long enough
         for a 40-character symbol name.  */
      if (length == 0)
        length = 40, symbuf = (char *)malloc (length + 1);
      i = 0;
      do
        {
          /* If buffer is full, make it bigger.        */
          if (i == length)
            {
              length *= 2;
              symbuf = (char *) realloc (symbuf, length + 1);
            }
          /* Add this character to the buffer.         */
          symbuf[i++] = c;
          /* Get another character.                    */
          c = getchar ();
        }
      while (isalnum (c));
      ungetc (c, stdin);
      symbuf[i] = '\0';
      s = getsym (symbuf);
      if (s == 0)
        s = putsym (symbuf, VAR);
      yylval.tptr = s;
      return s->type;
    }
  /* Any other character is a token by itself.        */
  return c;
}
