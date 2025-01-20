grammar Filter;

// Parser rules
expr: filterExpr actionExpr;
actionExpr: 'set';
filterExpr: '(' and ')' ('or' '(' and ')')+ | and;
and: op ('and' op)*;
op: equal | like | around;
equal: (string_factor | numeric_factor | other_factor) '=' (STRING | NUMBER);
like: string_factor '~' STRING;
around: numeric_factor '~' NUMBER;
string_factor: NAME_KYW | DIRECTION_KYW | MERCHANT_KYW | DESCRIPTION_KYW;
numeric_factor: DATE_KYW | VALUE_KYW;
other_factor: DIRECTION_KYW;


// Lexer rules
NAME_KYW: 'name';
DATE_KYW: 'date';
VALUE_KYW: 'value';
DIRECTION_KYW: 'dir';
MERCHANT_KYW: 'merch';
DESCRIPTION_KYW: 'desc';

DIRECTION: 'INCOME' | 'EXPEND';
NUMBER: [0-9]+ ('.' [0-9]+)?;
DATE: ([0-9]{4})'-'([0-9]{2})'-'([0-9]{2}) ([0-9]{2})':'([0-9]{2})':'([0-9]{2})?;
STRING: '"' [a-zA-Z0-9_]+ '"';
WS: [ \t\r\n]+ -> skip;
