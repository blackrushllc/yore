#include <stdlib.h>
#include <ctype.h>
#include <stdio.h>
#include <string.h>


#define DEBUG 1

#define DELIMITER 1
#define VARIABLE  2
#define NUMBER    3
#define FUNCTION  4
#define COMMA     5

#define DOUBLE 10
#define STRING 11



extern char *prog;
extern int errcode;
char token[80];
char tok_type;
int argptr=0;


char *strings[26]={0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0};

double vars[26]={0.0,0.0,0.0,0.0,0.0,0.0,0.0,0.0,
		 0.0,0.0,0.0,0.0,0.0,0.0,
		 0.0,0.0,0.0,0.0,0.0,0.0,
		 0.0,0.0,0.0,0.0,0.0,0.0};

double args[16]={0.0,0.0,0.0,0.0,0.0,0.0,0.0,0.0,
		 0.0,0.0,0.0,0.0,0.0,0.0,0.0,0.0};

void eval_exp(double *answer),eval_exp2(double *answer);
void eval_exp1a(double *answer);
void eval_exp1(double *result);
void eval_exp3(double *answer),eval_exp4(double *answer);
void eval_exp5(double *answer);
void eval_exp5a(double *answer);
void eval_exp6(double *answer),atom(double *answer);
void get_token(void),putback(void);
void unary(char o,double *r);
void serror(int error);
double find_var(char *s);
double dofunction(char *s);
int isdelim(char c);
void push(double d);
double pop(void);


/* parser entry point */

void eval_exp(double *answer)
{
	get_token();
	if (!*token) {
		serror(2);
		return;
		}
	eval_exp1(answer);
/*
	while (argptr) printf("POP %.2f\n",pop());
*/
}

/* process an assignment */
void eval_exp1(double *answer)
	{
	int slot;
	char ttok_type;
	char temp_token[80];

	if (tok_type==VARIABLE) {
		/*save old token*/
		strcpy(temp_token,token);
		ttok_type = tok_type;

		/* compute the index of the variable */
		slot=toupper(*token)-'A';

		get_token();
		if (*token != '=') {
			putback();
			strcpy(token,temp_token);
			tok_type=ttok_type;
			}
		else {
			get_token();
			eval_exp2(answer);
			vars[slot]=*answer;
			return;
			}
		}
		eval_exp1a(answer);
	}


void eval_exp1a(double *answer)
	{
	register char op;
	double temp;

	eval_exp2(answer);
	while ((op=*token)==',' || op==';') {
		get_token();
		eval_exp2(&temp);

#if DEBUG
printf("COMMA: Answer is %.2f Pushing: %.2f\n",*answer,temp);
#endif
		switch(op) {
			case ',':
				push(temp);
				break;
			case ';':
				push(temp);
				break;
			}
		}
	}

/* add or substract two terms. */
void eval_exp2(double *answer)
	{
	register char op;
	double temp;

	eval_exp3(answer);
	while ((op=*token)=='+' || op=='-') {
		get_token();
		eval_exp3(&temp);
		switch(op) {
			case '-':
				*answer=*answer-temp;
				break;
			case '+':
				*answer=*answer+temp;
				break;
			}
		}
	}

/* multiply or divide two factors */
void eval_exp3(double *answer)
	{
	register char op;
	double temp;

	eval_exp4(answer);
	while ((op=*token)=='*' || op == '/' || op == '%') {
		get_token();
		eval_exp4(&temp);
		switch(op) {
			case '*':
				*answer=*answer * temp;
				break;
			case '/':
				*answer=*answer / temp;
				break;
			case '%':
				*answer = (int) *answer % (int) temp;
				break;
			}
		}
	}

/* process an exponent */
void eval_exp4(double *answer)
	{
	double temp,ex;
	register int t;
	eval_exp5(answer);
	if(*token=='^') {
		get_token();
		eval_exp4(&temp);
		ex=*answer;
		if(temp==0.0) {*answer=1.0;
		return;
		}
	for (t=temp;t>0;--t) *answer = (*answer) * (double)ex;
		}
	}


/* evaluate a unary */
void eval_exp5(double *answer)
	{
	register char op;
	op=0;
	if((tok_type==DELIMITER) && *token=='+' || *token=='-') {
		op=*token;
		get_token();
		}
	eval_exp5a(answer);
	if(op=='-') *answer=-(*answer);
	}

/* evaluate a function */
void eval_exp5a(double *answer)
	{
	int major,minor;
	char fun[100];
	fun[0]=0;
	if(tok_type==FUNCTION) {
		strcpy(fun,token);
		strupr(fun);
		get_token();
		}
	eval_exp6(answer);

	if(fun[0]) {

#if DEBUG
	printf("FUNCTION: %s, argument %.2f\n",fun,*answer);
	/*
	while (argptr) printf("FUNC POP %.2f\n",pop());
	*/
#endif

	major=(fun[0]);
	minor=(fun[1]);
		switch (major)
		{
			case 'A':
				switch (minor)
				{
				case 'B': /* ABS */
					*answer=(float) abs((int)*answer);
					break;

				default: serror(3);
				}
				break;

			case 'B':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'C':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'D':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'E':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'F':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'G':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'H':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'I':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'J':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'K':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'L':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'M':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'N':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'O':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'P':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'Q':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;
				case 'E': /* PEEK */
					*answer=(float) peekb((unsigned)*answer,(unsigned) pop());
					break;
				default: serror(3);
				}
				break;
			case 'R':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'S':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'T':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'U':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'V':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'W':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'X':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'Y':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;
			case 'Z':
				switch (minor)
				{
				case 'A':
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(3);
				}
				break;


		default:
		serror(3);
		}

	}


	}

void eval_exp6(double *answer)
	{
	if ((*token == '(')) {
		get_token();
		eval_exp1a(answer);
#if DEBUG
	printf("end parens: token is %s, answer is %.2f\n",token,*answer);
#endif
		if (*token !=')') serror(1);

		get_token();
		}
	else
		atom(answer);
	}





void atom(double *answer)
	{
	switch (tok_type) {
		case VARIABLE:
			*answer=find_var(token);
			get_token();
			return;
		case NUMBER:
			*answer=atof(token);
			get_token();
			return;

		default:
			serror(0);
		}
	}

/* return a token to the input stream */
void putback(void)
	{
	char *t;
	t=token;

	for (;*t;t++) prog--;
	}

/* display syntax error */
void serror(int error)
	{
		static char *e[]={
		"syntax error",
		"unbalanced parenthesis",
		"no expression present",
		"unknown function"
		"unknown command"
		};
		errcode=error;
		printf("%s\n",e[error]);
	}

/* return the next token */
void get_token(void)
	{
	register char *temp;
	tok_type=0;
	temp=token;
	*temp='\0';
	if(!*prog) return; /* at end of expression */
	while(isspace(*prog)) ++prog; /* skip over white space */
	if(strchr("+-*/%^=(),;",*prog)){
		tok_type=DELIMITER;
		/*advance to next char */
		*temp++ = *prog++;
		}
	else if(isalpha(*prog)){
		while(!isdelim(*prog)) *temp++=*prog++;
		if (*prog=='(') {
			tok_type=FUNCTION;
			}
		else tok_type=VARIABLE;
		}
	else if(isdigit(*prog)){
		while(!isdelim(*prog)) *temp++=*prog++;
		tok_type=NUMBER;
		}
	*temp='\0';
	}

/*return true if c is a delimiter */
isdelim(char c)
	{
	if(strchr(" +-/*%^=(),;",c) || c==9 || c=='\r' || c==0)
		return 1;
	return 0;
	}

double find_var(char *s)
	{
	if(!isalpha(*s)){
		serror(1);
		return 0.0;
		}

	strupr(s);


#if DEBUG
	printf("FIND_VAR: s is %s, prog is %s\n",s,prog);
#endif
	return vars[toupper(*s)-'A'];
	}


void push(double d)
	{
	args[argptr++]=d;
	return;
	}

double pop(void)
	{
	return (args[--argptr]);
	}