%pure_parser

%start input

%token SYMBOL GROUP ALTERNATIVE OMIT

%%

input:
    expression                { $$ = new AST\Expression($1); }
  | input expression          { printf("Разобрано: input -> input expression %s\n", $2); }
  ;

expression:
    group                     { $$ = $1; }
  | sequence                  { $$ = $1; }
  | alternative               { $$ = $1; }
  | omission                  { $$ = $1; }
  ;

group:
    '(' sequence ')'          {
                                $$ = new AST\Group($1);
                              }
  | '[' sequence ']'          {
                                asprintf(&$$, "[%s]", $2);
                                free($2);
                              }
  ;

sequence:
    SYMBOL                    { $$ = strdup($1); }
  | sequence '-' SYMBOL       {
                                asprintf(&$$, "%s-%s", $1, $3);
                                free($1); free($3);
                              }
  | sequence '-' group        {
                                asprintf(&$$, "%s-%s", $1, $3);
                                free($1); free($3);
                              }
  ;

alternative:
    expression '//' expression {
                                 asprintf(&$$, "%s // %s", $1, $3);
                                 free($1); free($3);
                               }
  ;

omission:
  OMIT                     { $$ = strdup("..."); }
;
