#define DEBUG 1

#include <stdlib.h>
#include <ctype.h>
#include <stdio.h>
#include <string.h>


char *prog;
int errcode;
extern int argptr;
extern char *strings[];

void exec_line(double *answer);
void eval_exp(double *answer);
extern int isdelim(char c);
extern double pop();
extern void serror(int n);
extern double args[];

void main(void)
	{
	double answer;
	char *p;
	p=malloc(100);
	if(!p) {
		printf("memory allocation failure\n");
		exit(1);
		}

	/* process expressions until a blank line is entered */

	do {
		prog=p;
		printf("\nOK\n");
		gets(prog);
		if(!*prog) break;
#if DEBUG
		/* z$=input line */
		strings[26]=prog;
#endif

		exec_line(&answer);


		printf("ret: %.2f\n",answer);
		} while(*p);
	}

/* gets keyword and calls parser on rest.  If parser calls serror() or
   serror() is called in switch, errcode is set to error value.  This
   error code will flag stop execution of a program */

void exec_line(double *answer)
	{
	int major,minor,i,j,x,y,z,a,b,c;
	char keyword[100];
	register char *temp;
	temp=keyword;
	while(isspace(*prog)) ++prog; /* skip over white space */

	while(!isdelim(*prog)) *temp++ = *prog++;
	*temp='\0';
	strupr(keyword);
#if DEBUG
	printf("CMD [%s]\n",keyword);
	printf("ARG [%s]\n",prog);
	if (!*prog) printf("no parameter\n");
#endif
	if (!*prog) {
		*answer=0;
		argptr=0;
		}
	else {
		eval_exp(answer);
#if DEBUG
	printf("PARAMETERS Main: %.2f plus %d more\n",*answer,argptr);
#endif
		}

/* Execute the command */

	major=(keyword[0]);
	minor=(keyword[1]);
		switch (major)
		{
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
				}
				break;
			case 'P':
				switch (minor)
				{
				case 'R': /* print */
					printf(" %.2f",*answer);
					for(i=0;i<argptr;i++) printf(" %.2f",args[i]);
					printf("\n");
					argptr=0;
					break;
				case 'B':
					break;
				case 'C':
					break;
				case 'D':
					break;

				default: serror(4);
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
				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
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

				default: serror(4);
				}
				break;


		default:
		serror(4);
		}



	/* clean up stack */
#if DEBUG
	while (argptr) printf("POP %.2f\n",pop());
#else
	while (argptr) pop();
#endif
	return;
	}


