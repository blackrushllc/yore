#include <stdlib.h>
#include <ctype.h>
#include <stdio.h>
#include <string.h>
#include <dir.h>

#include "basic.h"

#define DEBUG 0


extern char *dtmfs[];
extern int running,maxring;
extern int conditional;
extern char *literal;
extern unsigned char *prog;
extern int errcode;
char token[80];
char tok_type;
int argptr=0;
int lastvar,lasttype;

char *temps[10]={0,0,0,0,0,0,0,0,0,0};
int tempptr=0;

char *strings[26]={0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0};

double vars[26]={0.0,0.0,0.0,0.0,0.0,0.0,0.0,0.0,
		 0.0,0.0,0.0,0.0,0.0,0.0,
		 0.0,0.0,0.0,0.0,0.0,0.0,
		 0.0,0.0,0.0,0.0,0.0,0.0};

char ops[16];
double args[16]={0.0,0.0,0.0,0.0,0.0,0.0,0.0,0.0,
		 0.0,0.0,0.0,0.0,0.0,0.0,0.0,0.0};

char *strargs[16]={0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0};
int argtypes[16];

extern char *temp_strings[];

void tempalloc(int length);
void eval_exp(double *answer),eval_exp2(double *answer);
void eval_exp1a(double *answer);
void eval_exp1b(double *answer);
void eval_exp1(double *result);
void eval_exp3(double *answer),eval_exp4(double *answer);
void eval_exp5(double *answer);
void eval_exp5a(double *answer);
void eval_exp6(double *answer),atom(double *answer);
void get_token(void),putback(void);
void unary(char o,double *r);
void serror(int error);
double find_var(unsigned char *s);
double dofunction(char *s);
int isdelim(char c);
void spush(char *s,char op);
void push(double d,char op);
double pop(void);
int argtype(void);


/**
 **   Dialogic header files
 **/
#include "d40.h"           /* standard DIALOG/4x header  */
#include "d40lib.h"
#include "vfcns.h"

/**
 **   Definitions
 **/

#define MAXCHAN 4
#define MAXDTMF 20
#define MAXRING 2

/* Definition of states */

#define  ST_WTRING   1     /* waiting for an incoming call    */
#define  ST_OFFHK    2     /* going off hook to accept call   */
#define  ST_INTRO    3     /* play the intro.vox file         */
#define  ST_DIGIT    4     /* get dtmf digits (access code)   */
#define  ST_PLAY     5     /* play the caller's message file  */
#define  ST_RECORD   6     /* recording message from caller   */
#define  ST_INVLD    7     /* play invalid.vox (invalid code) */
#define  ST_GOODBY   8     /* play goodby.vox                 */
#define  ST_ONHK     9     /* go onhook                       */


/**
 **   Function prototypes
 **/
extern void sysexit(int);
extern void sysinit(int);
extern int  waitevt(EVTBLK *);

extern int  get_digits(int,char *);
extern int  play(int,int);
extern int  record(int,int);

extern RWB d4xrwb;             /* read/write block for the D/4x */
extern int maxchan;              /* max D/4x channels in system */
extern int d4xintr;        /* default d/4x hardware interrupt */
extern int channel;

extern EVTBLK d4evtblk;       /* event data block for the D/4x */
extern int errcode;           /* exit to DOS with errcode      */
extern int evtcode;           /* termination event code        */
extern int evtdata;           /* event data                    */



/* parser entry point */

void eval_exp(double *answer)
{
	int i;
#if DEBUG
	printf("PARSE: %s\n",prog);
#endif
	for(i=0;i<16;i++) ops[i]='\0';
	get_token();
	if (!*token) {
		return;
		}
	if(conditional) eval_exp1a(answer); /* bypass exp1 */
	else
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
			if (*answer==STRFLAG)
				{
				/* assign a string */
				if (strings[slot]!=NULL) free(strings[slot]);
				strings[slot]=calloc(strlen(strargs[argptr-1])+1,sizeof(char));
				if (strings[slot]==NULL) {
					printf("Out of memory\n");exit(1);}
				strcpy(strings[slot],strargs[--argptr]);
				}
			else
			/* assign a numeric */
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

	if (conditional) eval_exp1b(answer);
	else
	eval_exp2(answer);

	while ((op=*token)==',' || op==';') {
		get_token();
		if (conditional) eval_exp1b(&temp);
		else
		eval_exp2(&temp);

#if DEBUG
printf("COMMA: Answer is %.2f Pushing: %.2f\n",*answer,temp);
#endif
		if (temp!=STRFLAG) /*indicates a string*/
			{
			switch(op) {
				case ',':
					push(temp,op);
					break;
				case ';':
					push(temp,op);
					break;
				}
			}
		else
			ops[argptr-1]=op;
		}
	}

void eval_exp1b(double *answer)
	{
	register char op;
	double temp;
	char *build;
	eval_exp2(answer);

	while ((op=*token)=='=' || (op=='>') || (op=='<') || (op=='!')) {
		get_token();
		eval_exp2(&temp);
		switch(op) {
			case '!':
			case '>':
			case '<':
			case '=':
#if DEBUG
	printf("Equals sign:");
#endif
				if (temp==STRFLAG) {
#if DEBUG
	printf(" compare %s to %s\n",strargs[argptr-2],strargs[argptr-1]);
#endif
					if(strcmp(strargs[argptr-2],strargs[argptr-1])==0)
						*answer=1;
					else
						*answer=0;
					--argptr;--argptr;
					}
				else

#if DEBUG
	{
	printf(" compare %.2f to %.2f\n",*answer,temp);
#endif
				*answer=(*answer==temp);
#if DEBUG
	}
	printf(" answer is %.2f\n",*answer);
#endif
				break;
			}
		}
	}

/* add or substract two terms. */
void eval_exp2(double *answer)
	{
	register char op;
	double temp;
	char *build,*first;
	eval_exp3(answer);
	if (*answer==STRFLAG) first=strargs[argptr-1];

	while ((op=*token)=='+' || op=='-') {
		get_token();
		eval_exp3(&temp);
		switch(op) {
			case '-':
				*answer=*answer-temp;
				break;
			case '+':
				if (temp==STRFLAG) {
					build=calloc(strlen(first)+strlen(strargs[argptr-1])+1,sizeof(char));
					strcpy(build,strargs[argptr-2]);
					strcat(build,strargs[--argptr]);
					--argptr;
					spush(build,'+');
					}
				else
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
	static struct ffblk ffblk;
	int major,minor,minnie,rc,d4xint;
	char fun[100];
	static int done=-1;
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
	minnie=(fun[2]);
		switch (major)
		{
			case 'A':
				switch (minor)
				{
				case 'B': /* ABS */
					*answer=(float) abs((int)*answer);
					break;
				case 'S': /* ASC */
					*answer=(float)atoi(strargs[--argptr]);
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
				case 'H':
					if (fun[2]=='A') *answer=(float)channel;
					if (fun[2]=='R')
						{

					if(++tempptr==10) tempptr=0;
					if (temps[tempptr]!=NULL) free(temps[tempptr]);
					temps[tempptr]=calloc(10,sizeof(char));
					temps[tempptr][0]=(unsigned char)*answer;
					temps[tempptr][1]='\0';
					spush(temps[tempptr],0);
					*answer=STRFLAG;
						}
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
				case 'T':
					if(++tempptr==10) tempptr=0;
					if (temps[tempptr]!=NULL) free(temps[tempptr]);
					temps[tempptr]=calloc(64,sizeof(char));
					strcpy(temps[tempptr],dtmfs[(int)*answer]);
					spush(temps[tempptr],0);
					*answer=STRFLAG;
					break;
				case 'I':
					if(++tempptr==10) tempptr=0;
					if (temps[tempptr]!=NULL) free(temps[tempptr]);
					temps[tempptr]=calloc(20,sizeof(char));

					if (done||(argptr>0))
					   done = findfirst(strargs[--argptr],&ffblk,0);
					else
					   done = findnext(&ffblk);

					sprintf(temps[tempptr],"%s\0",ffblk.ff_name);
					spush(temps[tempptr],0);
					*answer=STRFLAG;

					break;

				default: serror(3);
				}
				break;
			case 'E':
				switch (minor)
				{
				case 'V':
					if (fun[2]=='E')
					{
	 if (gtevtblk(&d4evtblk) == -1) *answer=(float)d4evtblk.evtcode;
			 else *answer=0;

			 channel = d4evtblk.devchan;
			 evtcode = d4evtblk.evtcode;
			 evtdata = d4evtblk.evtdata;
					 break;
					 }
					if (fun[2]=='C') *answer=(float)evtcode;
					if (fun[2]=='D') *answer=(float)evtdata;
					break;
				case 'N': /* environ */
					break;
				case 'O': /* eof */
					break;
				case 'R': /* error */
					break;

				default: serror(3);
				}
				break;
			case 'F':
				switch (minor)
				{
				case 'I': /* FILE */
					break;
				default: serror(3);
				}
				break;
			case 'G':
				switch (minor)
				{
				case 'E': /* GET$ */
					break;
				default: serror(3);
				}
				break;
			case 'H':
				switch (minor)
				{
				case 'E': /* HEX */
					break;
				default: serror(3);
				}
				break;
			case 'I':
				switch (minor)
				{
				case 'S': /* IS.. */
				/* isdigit isalpha ismtask isopen ... */
					break;
				case 'N': /* INKEY$ / INPUT$ / INT / INSTR */
					switch (minnie)
					{
					case 'K':
					tempalloc(10);
					temps[tempptr][1]='\0';
					if (!kbhit())
						temps[tempptr][0]='\0';
					else
						{
						temps[tempptr][0]=getch();
						if(temps[tempptr][0]=='\0')
							{
							temps[tempptr][0]=getch();
							}
						}
					*answer=STRFLAG;
					spush(temps[tempptr],0);
					break;
					case 'P':
					case 'T':
					case 'S':
					default:serror(3);
					break;
					} break;

				case 'F': /* IF() */
					if (*answer!=0) *answer=1;
					break;
				default: serror(3);
				}
				break;
			case 'L':
				switch (minor)
				{
				case 'E': /* LEFT$, LEN */
					*answer=(float)strlen(strargs[--argptr]);
					break;
				case 'T': /* LTRIM$ */
					break;
				case 'C':  /* LCASE$ */
					break;
				case 'I': /* LINE$ */
					break;
				case 'O': /* LOC / LOF */

				default: serror(3);
				}
				break;
			case 'M':
				switch (minor)
				{
				case 'I': /* MID$ */
					break;

				default: serror(3);
				}
				break;
			case 'N':
				switch (minor)
				{
				case 'E': /* NEXT$  (findnext) */
					break;
				default: serror(3);
				}
				break;
			case 'O':
				switch (minor)
				{
				case 'C': /* OCT$ */
					break;

				default: serror(3);
				}
				break;
			case 'P':
				switch (minor)
				{
				case 'O': /* POS */
					break;

				default: serror(3);
				}
				break;
			case 'R':
				switch (minor)
				{
				case 'I': /* RIGHT$ */
					break;
				case 'T':  /* RTRIM$ */
					break;
				case 'N': /* RND */
					break;
				case 'E': /* RECNO */
					break;

				default: serror(3);
				}
				break;
			case 'S':
				switch (minor)
				{
				case 'T': /* Startsys/Stopsys */
					if (fun[2]=='O'){
						*answer=(float)0;
						stopsys();
						break;}
					if (fun[2]=='R'){ /*STR$*/
					if(++tempptr==10) tempptr=0;
					if (temps[tempptr]!=NULL) free(temps[tempptr]);
					temps[tempptr]=calloc(20,sizeof(char));
					sprintf(temps[tempptr],"%g",*answer);
					spush(temps[tempptr],0);
					*answer=STRFLAG;
					break;
						}
					if (fun[2]=='A') /*startsys*/
						{
   /* initialize the variable if necessary */

      d4xint = (int) *answer;
      if (d4xint==0) d4xint=0;

      /* check for the D/4x driver */
      if (!getvctr())  {
	 printf("DIALOG/4x voice driver not installed\n");
	 *answer=0;break;
      }

      /* make sure system is stopped before starting */
      stopsys();
      if (rc = startsys(d4xint,SM_EVENT,0,0,&channel))  {
	 printf("Unable to start voice system, Return code %d\n",rc);
	   *answer=0;break;
      }

      /* if more channels requested than actually exist, exit */
      if (maxchan > channel)  {
	 printf("Only %d in D/4x system\n",channel);
	*answer=0;break;
      }

      /* if maximum channels set from the command line */
      if (maxchan==0 || maxchan>channel)  {
	 maxchan = channel;
      }

      /* maxchan can't be greater than MAXCHAN */
      if (maxchan>MAXCHAN) {
	 maxchan = MAXCHAN;
      }

   /*   printf("Using %d channels, IRQ %d\n",maxchan,d4xint); */

      /* set all channels to detect call */
      for (channel=1; channel<=maxchan; channel++)  {
	 /* auto-answer, enable loop signal and off-hook msgs */
	 setcst(channel,(C_LC+C_RING+C_OFFH+C_ONH),maxring);
	 sethook(channel,H_ONH); /* put line on hook */
       } /* for */

   *answer = (float)maxchan; break;
   }                                   if (fun[2]=='R')
						{
						/*STR$*/

						break;
						}


				case 'P': /* SPACE$ */
					break;
				default: serror(3);
				}
				break;
			case 'T':
				switch (minor)
				{
				case 'A':/*TAB*/
					break;
				case 'I': /* TIME$/TIMER */
					break;

				default: serror(3);
				}
				break;
			case 'U':
				switch (minor)
				{
				case 'C': /*UCASE$*/
					if(++tempptr==10) tempptr=0;
					if (temps[tempptr]!=NULL) free(temps[tempptr]);
					temps[tempptr]=calloc(strlen(strargs[--argptr])+1,sizeof(char));
					strcpy(temps[tempptr],strargs[argptr]);
					strupr(temps[tempptr]);
					spush(temps[tempptr],0);
					break;

				default: serror(3);
				}
				break;
			case 'V':
				switch (minor)
				{
				case 'H': /* VHOPEN or */
				case 'O': /* VOPEN */
					*answer=(float) vhopen(strargs[--argptr],READ);
					break;
				case 'C': /* VCREATE*/
					*answer=(float) vhopen(strargs[--argptr],CREATE);
				case 'A': /* VAL */
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
			*answer=NULLFLAG;
		       /*serror(0);*/
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
		if (error==4)
			{
			if (literal[0]=='!') literal++;
			system(literal);
			}
		else
		{
		static char *e[]={
		"syntax error",
		"unbalanced parenthesis",
		"no expression present",
		"unknown function",
		"unknown command",
		"illegal function call",
		};
		running=0;
		errcode=error;
		printf("%s\n",e[error]);
		}
	}

/* return the next token */
void get_token(void)
	{
	char *temp;
	tok_type=0;
	temp=token;
	*temp='\0';
	if(!*prog) return; /* at end of expression */
	while(isspace(*prog)) ++prog; /* skip over white space */
#if DEBUG
	printf("GetToken: ASCII %d\n",(int) prog[0]);
#endif
	if(strchr("+-*/%^=(),;",*prog)){
		tok_type=DELIMITER;
		/*advance to next char */
		*temp++ = *prog++;
		}

	else if(isalpha(*prog) || ((int)prog[0]>149)){
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

double find_var(unsigned char *s)
	{
	if(!isalpha(*s) && ((int) s[0] < 150)){
		serror(1);
		return 0.0;
		}

	strupr(s);


#if DEBUG
	printf("FIND_VAR: s is %s, prog is %s\n",s,prog);
#endif
	lastvar=toupper(*s)-'A';

	if (strchr(s,'$')) {                  /* it is a string variable */
		lasttype=STRING;
		if (strings[lastvar]==NULL)
			strings[lastvar]=calloc(1,sizeof(char));
		spush(strings[lastvar],0);
		return(STRFLAG);
			}
	if ((int)s[0] > 149) /* temp string */
		{
#if DEBUG
	printf("Spush %s\n",temp_strings[s[0]-150]);
#endif
		spush(temp_strings[s[0]-150],0);
		/*return (double)strlen(temp_strings[s[0]-150]);*/
		return STRFLAG;
		}
	lasttype=DOUBLE;
	return vars[lastvar];
	}



int argtype(void)
	{
	if (argptr==0) serror(5);
	return argtypes[argptr-1];
	}

void spush(char *d,char op)
	{
	argtypes[argptr]=STRING;
	ops[argptr]=op;
	args[argptr]=0;
	strargs[argptr++]=d;
	return;
	}

void push(double d, char op)
	{
	argtypes[argptr]=DOUBLE;
	ops[argptr]=op;
	args[argptr++]=d;
	return;
	}

double pop(void)
	{
	if (argptr<1) serror(5);
	return (args[--argptr]);
	}
char *spop(void)
	{
	if (argptr<1) serror(5);
	return (strargs[--argptr]);
	}


void tempalloc(int length)
	{
	if(++tempptr==10) tempptr=0;
	if (temps[tempptr]!=NULL) free(temps[tempptr]);
	temps[tempptr]=calloc(length,sizeof(char));
	spush(temps[tempptr],0);
	}