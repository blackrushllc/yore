#define DEBUG 0
/*************************************************************************
   BASIC Interpreter
 *************************************************************************/

#include <stdlib.h>
#include <ctype.h>
#include <stdio.h>
#include <string.h>
#include <stdio.h>
#include <dir.h>

/*D41B would be D0000 irq 5
d41E would be D2000 in dialogic.cfg id=0 irq 7
run d40drv -h5 (interrupt for E) */

#include "dialogic.h"
#include "basic.h"


extern char ops[];
extern double vars[];
char *literal;
char *prog;
int errcode;
extern int argptr;
extern char *strings[];
int temp_string_ptr=0;
char *temp_strings[100];
int tron,conditional,step;

void savefile(char *fi);
void loadfile(char *fi);
void lprep(char *l);
void exec_line(double *answer);
void eval_exp(double *answer);
void prep(void);
char temp_string(char *s);
void vset(char *v,char *s);
void run(void);
void gpush(void);
void gpop(void);
void input(void);

char org[256];
char *line[16];
char *dtmfs[24];

int running=0;
char *program[MAXPROG];
int currline,currseg,startseg;
int gosubptr=0;
int gosubstack[32];
int gosubseg[32];
int whileptr=0;
int whilestack[32];
int whileseg[32];
int forptr=0;
int forstack[32];
int forseg[32];
int ifs,thens,whiles,wends,fors,nexts;
char forexp[80];
int dataptr;
int of,ob;

/* make these parameters you can set */

int maxsec=60;
char termdtmf='#';
int maxsil=30;
int loopsig=1;
int maxdtmf=5;
int maxring=1;

#define DOUBLE 10
#define STRING 11
#define INTEGER 12

#define STRFLAG -94949.1
#define NULLFLAG -49494.1

extern int isdelim(char c);
extern double pop();
extern char *spop();
extern void serror(int n);
extern double args[];
extern char *strargs[];
extern int argtypes[];
extern int argtype(void);
extern int lastvar,lasttype;

void main(void)
	{
	double answer;
	char *p,*l;
	int i,x;
	for(i=0;i<100;i++) temp_strings[i]=NULL;
	p=malloc(100);
	l=malloc(256);
	if((!p) || (!l)) {
		printf("memory allocation failure\n");
		exit(1);
		}

	SGR(BOLD)
	COLOR(FGYELLOW,BGBLUE)
	of=FGYELLOW;
	ob=BGBLUE;
	CLS
	printf("X-BASIC Telephony Programming Language\n");
	printf("Copyright (C) 1995, Mermaid Software Products, All Rights Reserved.\n");
	printf("Created by Erik Olson, Clearwater, Florida\n\n");
	COLOR(FGWHITE,BGBLUE)
	printf("Enter a command\n");


	/* process expressions until a blank line is entered */

	do {

	    printf("\nOK\n");
	    for(i=0;i<256;i++) l[i]='\0';
	    gets(l);
	    sched();
	    strcpy(org,l);
	    if(*l) {
		if isdigit(l[0])
			{
			prog=org;
			while(isspace(*prog)) prog++; /*skip white space*/
			x=atoi(prog);                 /*get line no*/
			while(isdigit(*prog)) prog++; /*skip digits*/
			while(isspace(*prog)) prog++; /*skip white space*/
			if (program[x]!=NULL) free(program[x]);
			program[x]=calloc(strlen(org)+1,sizeof(char));
			if (program[x]==NULL)
				printf("Out of memory\n");
			else

				strcpy(program[x],prog);
			}
		else
		 {
		 lprep(l);
		 /* this next line may not be necessary if parser
		 exits on ':' anyway.  this just forces termination
		 with a null after each line segment */

		 /*for (i=0;i<256;i++) if (l[i]==':') l[i]='\0';*/

		 currline=0;
		 ifs=0;
		 thens=0;
		 for(i=0;line[i];i++)
		     {
		     prog=line[i];
		     exec_line(&answer);
		     if (currline!=0) break;
		     }
		     /* need to be able to reset i from within parser */
		     /* use a global variable lineptr,offsetptr */
		  }
		 }
		} while(1);
	}



/* prep a line */
void lprep(char *l)
	{
	char temp[256];
	char astring[100];
	int i,j,k=0;
	int x=0;
	line[x]=l;
	for (i=0;l[i];i++) {
		if (l[i]==34)
			{        /* copy remainder of string, or to quote */
			for (
			    j=0;
			    (l[++i]!=34) && (l[i]);
			    j++
			    )               /* copy quoted text */
			    {
			    astring[j]=l[i];
			    }
			    astring[j]='\0'; /* nul terminate the copy */
			    /* store string in a temp var */
			    /* place temp var name in string */
			    temp[k++]=temp_string(astring);
			}
		else
		   /*if (l[i]!=32 && prog[i]!=9)*/
		   {

		   temp[k]=toupper(l[i]); /* copy prog to temp */
		   if (temp[k]==39) temp[k]='\0';
		   if (temp[k]==':') temp[k]='\0';
		   /* ELSE turns into :LS:*/
		   if (temp[k]=='E')
			{
			if (temp[k-1]=='S')
				{
				if (temp[k-2]=='L')
					{
					if (temp[k-3]=='E')
						{
						line[++x]=&l[k-2];
						line[++x]=&l[k+1];
						temp[k-3]='\0';
						temp[k]='\0';
						}
					}
				}
			}


		   /* THEN turns into :HE:*/
		   if (temp[k]=='N')
			{
			if (temp[k-1]=='E')
				{
				if (temp[k-2]=='H')
					{
					if (temp[k-3]=='T')
						{
						line[++x]=&l[k-2];
						line[++x]=&l[k+1];
						temp[k-3]='\0';
						temp[k]='\0';

						}
					}
				}
			}

		   k++;

		   if (l[i]==':') /* point to temp[k]   */
			{
			line[++x]=&l[k]; /* new line segment */
			}
		   }
		}
		temp[k++]='\0';

	for (i=0;i<k;i++) {
#if DEBUG
		if (temp[i])printf("%c",temp[i]);
		else printf("!");
#endif
		l[i]=temp[i];
		}
#if DEBUG
	printf("\n");
#endif

	line[++x]=NULL;
	}




/* gets keyword and calls parser on rest.  If parser calls serror() or
   serror() is called in switch, errcode is set to error value.  This
   error code will flag stop execution of a program */

void exec_line(double *answer)
	{
	int major,minor,i,j,x,y,z,a,b,c;
	char keyword[100];
	register char *temp,*oldprog;
	oldprog=prog;
	literal=prog;
	temp=keyword;

	while(isspace(*prog)) ++prog; /* skip over white space */

	while(!isdelim(*prog)) *temp++ = *prog++;
     /*	while(isspace(*prog)) ++prog;*/ /* skip over white space */
	*temp='\0';
	strupr(keyword);
	conditional=1;
	if (prog[0]=='=') /* shortcut assignment */
		{
		prog=oldprog;
		strcpy(keyword,"LET");
		conditional=0; /* may want it to default to 1 otherwise */
		}


	major=keyword[0];
	minor=keyword[1];

	if (major=='?') {major='P';minor='R';}

#if DEBUG
	printf("CMD [%s]\n",keyword);
	printf("ARG [%s]\n",prog);
	printf("Line %d Seg %d Ifs %d Thens %d\n",currline,currseg,ifs,thens);
	if (!*prog) printf("no parameter\n");
#endif
	if(major=='!') {system(++literal);argptr=0;return;}
	if(major==39) {argptr=0;return;}

	if (ifs) {
		if ((major=='H')&&(minor=='E')) thens++;
		if ((major=='L')&&(minor=='S')) thens--;
#if DEBUG
		printf("IFS:THENS is now %d\n",thens);
#endif
		if (!thens) ifs=0;
		}
	else /*if not ifs then */
		if ((major=='L')&&(minor=='S')) {ifs++;thens++;}


#if DEBUG
	if (ifs) printf("IFS: Returning now\n");
#endif
	if (ifs) {argptr=0; return;}




	if (!*prog) {
		*answer=0;
		argptr=0;
		}
	else { /* if there is *prog then */

	if ((major=='L') && (minor=='E')) conditional=0;
	   /*
	if (major=='W')
		{if (minor=='E')
			{
			conditional=1;
			eval_exp(answer);
			if (!*answer) whiles++;


			argptr=0;
			return;
			} /*endif minor==E*/
		} /*endif major=W
	    */

	if (major=='I')
		{
		if (minor=='F')
			{
			conditional=1; /*set flag for handling of '='*/
			eval_exp(answer);
#if DEBUG
	if (!*answer)
		printf("IF false\n");
	else
		printf("IF true\n");
#endif
			if (!*answer)
				ifs++;
			argptr=0;
			return;

			} /*endif minor=F*/
		else      /* else if minor is not F*/
			{ /* Input */
			if (minor=='N')
				{

					eval_exp(answer);
					input();
					argptr=0;
					return;
				} /*endif minor=N*/
			} /*endif else if monor not F*/
		} /* endif major is I */
	/* otherwise execute the parser on the argument */
	else /* else if major not I */
	eval_exp(answer);
#if DEBUG
	printf("PARAMETERS Main: %g plus %d more\n",*answer,argptr);
#endif


/* Execute the command */

		sched();

		switch (major)
		{
			case 'B':
				switch (minor)
				{
				case 'O': SGR(BOLD)
					break;
				case 'L': SGR(BLINKIE)
					break;

				default: serror(4);
				}
				break;
			case 'C':
				switch (minor)
				{
				case 'L':
					if (keyword[2]=='S') /* cls*/
						{printf("%c[2J",27);
						break;}
					if (keyword[2]=='E')  /* clear */
						for(i=0;i<26;i++)
							{
							if (strings[i]!=NULL)
								free(strings[i]);
							strings[i]=NULL;
							vars[i]=0.0;
							break;
							}
					if (keyword[2]=='R') /* clrdtmf */
						{clrdtmf((int)*answer);break;}

					/* close */


				case 'H': /* chain */
					break;
				case 'O':     /* color, common? */
					printf("%c[%d;%dm",27,(int)*answer+30,(int)args[0]+40);
					of=(int)*answer+30; ob=(int)args[0]+40;
					break;
				case 'A': /*CALL*/
					break;

				default: serror(4);
				}
				break;
			case 'D':
				switch (minor)
				{
				case 'U': SGR(DIM)   /* DIM ..., with parameters */
					break;
				case 'O':  /* DO */
					break;
				case 'A': /* DATA */
					break;
				case 'E': /* DELETE */
					break;
								default: serror(4);
				}
				break;
			case 'E':
				switch (minor)
				{
				case 'N': /* END */
					running=0;
					break;
				case 'X': /* exit */
					break;
				case 'R': /* ERASE */
					break;
				case 'D': /* EDIT */
					break;

				default: serror(4);
				}
				break;
			case 'F':
				switch (minor)
				{
				case 'I': /* FIELD */
					break;
				case 'O': /* FOR */
					break;

				default: serror(4);
				}
				break;
			case 'G':
				switch (minor)
				{
				case 'O':
					if (keyword[2]=='S') /*GOSUB*/
						gpush();
						/* if string goto label*/
						currline=*answer;
						startseg=0;
					break;
				case 'E':/* GET, GETDTMFS */
      /* set up the read/write block for call to getdtmfs() */
      if (dtmfs[(int)*answer]!=NULL) free(dtmfs[(int)*answer]);
      dtmfs[(int)*answer]=calloc(64,sizeof(char));
      clrrwb(&d4xrwb);
      d4xrwb.xferoff  = d4getoff(dtmfs[(int)*answer]);  /* seg,ofst of bufr */
      d4xrwb.xferseg  = d4getseg(dtmfs[(int)*answer]);
      d4xrwb.maxdtmf  = maxdtmf; /* request x dtmf digits */
      d4xrwb.maxsec   = maxsec;  /* wait x seconds          */
      d4xrwb.loopsig  = loopsig; /* terminate on loop signal */
      d4xrwb.termdtmf = termdtmf;/* terminate if x dtmf */
      getdtmfs((int)*answer,&d4xrwb);
					break;

				default: serror(4);
				}
				break;
			case 'H':
				switch (minor)
				{
				case 'E': /* lprep THEN */
					break;
				default: serror(4);
				}
				break;
			case 'I':
				switch (minor)
				{
				case 'F': /* IF */
					if (!*answer)
						{
						currline++;
						startseg=0;
						}
					break;

				default: serror(4);
				}
				break;
			case 'K':
				switch (minor)
				{
				case 'I': /* KILL */
					remove(strargs[--argptr]);
					break;
				default: serror(4);
				}
				break;
			case 'L':
				switch (minor)
				{
				case 'E': /* LET */
					  /* just do nothing */
					break;
				case 'I':      /* LIST */
					for(i=1;i<MAXPROG;i++)
						{
						if (program[i])
						 printf("%d %s\n",i,program[i]);
						}
					break;
				case 'O': /* LOAD */
					if (keyword[2]=='A')
						{
						if (*answer==STRFLAG)
							loadfile(strargs[argptr-1]);
							break;
						}
						else
						{
						printf("%c[%d;%df",27,(int)*answer,(int)args[0]);
						}
				case 'S':  /* placeholder for ELSE */
					break;
				case 'L': /* LLIST */
					break;
				case 'P': /* LPRINT */
					break;

				default: serror(4);
				}
				break;
			case 'M':
				switch (minor)
				{
				case 'E': /* MERGE */
					break;
				case 'I': /* MID$ COMMAND */
					break;
				case 'A':
					switch(keyword[3])
					{
					case 'D':maxdtmf=(int)*answer;break;
					case 'S':maxsec=(int)*answer;break;
					case 'R':maxring=(int)*answer;break;
					default: serror(4);
					} break;
				default: serror(4);
				}
				break;
			case 'N':
				switch (minor)
				{
				case 'E':
					if (keyword[2]=='W')
						{ /* NEW */
						for (i=0;i<MAXPROG;i++)
							{
							if (program[i]!=NULL) free(program[i]);
							program[i]=NULL;
							}
						}
					else
						{ /* NEXT */
						}
					break;
				case 'N': /* NAME */
					break;
				default: serror(4);
				}
				break;
			case 'O':
				switch (minor)
				{
				case 'F':
					sethook((int)*answer,H_OFFH);
					break;
				case 'N':
					sethook((int)*answer,H_ONH);
					break;
				case 'P': /* OPEN */
					break;
				default: serror(4);
				}
				break;
			case 'P':
				switch (minor)
				{
				case 'R': /* print */
					if (*answer!=STRFLAG)
					   printf("%g",*answer);
					for(i=0;i<argptr;i++)
					 {
					 if(ops[i]==',') printf("\t");
					 if (argtypes[i]==DOUBLE)
						{
						if (args[i]!=NULLFLAG)
						printf("%g",args[i]);
						}
					 else
					 printf("%s",strargs[i]);
					 }


					/* detect a terminating
					   semicolon to supress
					   the newline */

					if (args[i-1]!=NULLFLAG)
						printf("\n");
					argptr=0;
					break;
				case 'U': /* PUT */
					break;
				case 'O': /* POKE */
					break;
				case 'L': /* PLAY */
					if ((*answer!=0) && (args[0]!=0) && argtypes[0]==DOUBLE)
      {
      vhseek((int)args[0],0L,0);

      clrrwb(&d4xrwb);             /* clear the D/4x read/write block */
      d4xrwb.filehndl = (int)args[0];    /* handle of file to play */
      d4xrwb.maxdtmf  = 1;         /* cause and event if max digits */
      d4xrwb.loopsig  = 1;         /* terminate on loop signal drop */

      /* play vox file on D/4x channel, normal play back */
      xplayf((int)*answer,PM_NORM,&d4xrwb);
      }
	else printf("Error in PLAY\n");
					break;
				default: serror(4);
				}
				break;
			case 'Q':
				switch (minor)
				{
				case 'U': /*quit*/
					stopsys();
					exit((int)*answer);
					break;
				default: serror(4);
				}
				break;
			case 'R':
				switch (minor)
				{
				case 'U': /* run */

					/* check for string arg and
					   load, or line number, or just
					   run */
					if (*answer==STRFLAG)
						loadfile(strargs[argptr-1]);
						argptr=0;
						run();
					 break;
				case 'E': /*return*/
					if (keyword[2]=='T')
					{gpop();
					break;}
					if (keyword[3]=='T')  /* RESTORE */
					{dataptr=(int)*answer;
					break;}
					if (keyword[2]=='C')
					{/* record */

      /* seek to the end of the file */
      vhseek(port[channel].msg_fh,(long int)0,2);

      /* set up read/write block for recording */
      clrrwb(&d4xrwb);
      d4xrwb.filehndl = (int)args[0];
      d4xrwb.maxsec   = maxsec;   /* maximum 10 seconds for the message   */
      d4xrwb.termdtmf = termdtmf;  /* terminate if any dtmf                */
      d4xrwb.maxsil   = maxsil;    /* terminate after 5 seconds of silence */
      d4xrwb.loopsig  = loopsig;    /* terminate on loop signal             */
      d4xrwb.rwbflags = 0x02; /* enable beep before record            */
      d4xrwb.rwbdata1 = 3;    /* .6 second beep                       */

      recfile((int)*answer,&d4xrwb,RM_NORM);




					break;}
					if (keyword[3]=='U') /* RESUME */
					{dataptr=*answer;
					break;}
				default: serror(4);
				}
				break;
			case 'S':
				switch (minor)
				{
				case 'A': /* SAVE */
					savefile(strargs[argptr-1]);
					argptr=0;
					break;
                case 'T': /* STEP toggle */
                    if (step) step=0; else step=1;
                    break;
				case 'W': /* SWAP */
					break;
				case 'Y': /* system */
					stopsys();
					exit((int)*answer);
					break;

				default: serror(4);
				}
				break;
			case 'T':
				switch (minor)
				{
				case 'R':
                    if (tron) tron=0; else tron=1;
					break;
				case 'I': /* TIMER */
					break;
				case 'E': /* TERMDTMF */
					termdtmf=(char)*answer;
					break;

				default: serror(4);
				}
				break;
			case 'U':
				switch (minor)
				{
				case 'S': /* USE */
					break;


				default: serror(4);
				}
				break;
			case 'V':
				switch (minor)
				{
				case 'P': /* VPLAY */
					break;
				case 'S': /* VSPEAK */
					break;
				case 'G': /* VGOTO */
					break;
				case 'C': /* VCLOSE */
					vhclose((int)*answer);
					break;

				default: serror(4);
				}
				break;
			case 'W':
				switch (minor)
				{
				case 'E':/* WEND */
					break;
				case 'H':/* WHILE */
					break;
				case 'I':/* WIDTH */
					break;
				case 'R':/* WRITE */
					break;

				default: serror(4);
				}
				break;
			case 'X':
				switch (minor)
				{
				case 'S': /* XSAVE */
					break;
				case 'L': /* XLOAD */
					break;
				default: serror(4);
				}
				break;

		default:
			serror(4);
		}

	/* clean up stack */
#if DEBUG
	while (argptr>0)
		if (argtype()==DOUBLE) printf("POP %g\n",pop());
		else printf("SPOP [%s]\n",spop());
#else
	/*while (argptr) pop();*/
	argptr=0;
#endif
	return;
	}




void prep(void)
	{
	char temp[100];
	char astring[100];
	int i,j,k=0;

	for (i=0;prog[i];i++) {
		if (prog[i]==34)
			{        /* copy remainder of string, or to quote */
			for (
			    j=0;
			    (prog[++i]!=34) && (prog[i]);
			    j++
			    )               /* copy quoted text */
			    {
			    astring[j]=prog[i];
			    }
			    astring[j]='\0'; /* nul terminate the copy */
#if DEBUG
	printf("QUOTED: %s\n",astring);
#endif
			    /* store string in a temp var */
			    /* place temp var name in string */
			    temp[k++]=temp_string(astring);
			}
		else

		   if (prog[i]!=32 && prog[i]!=9)
		   temp[k++]=prog[i]; /* copy prog to temp */
		}
		temp[k++]='\0';
#if DEBUG
	printf("PREP STRING: %s\n",temp);
#endif
	strcpy(prog,temp);
	}


char temp_string(char *s)
	{
	char varname;
	char *str;

	/* allows 100 quoted strings per prepped procedure */
	if (temp_string_ptr==100) temp_string_ptr=0; /* circle back */
	varname = (char)temp_string_ptr+150; /* a hi ascii char for var name */
	if (temp_strings[temp_string_ptr]!=NULL) {
		free(temp_strings[temp_string_ptr]);
#if DEBUG
	printf("Free temp string\n");
#endif
		}
#if DEBUG
	printf("About to allocate %d bytes\n",strlen(s)+1);
#endif

/*	str=calloc(5,sizeof(char));
	if (str==NULL) printf("Alloc no workee\n"); else free(str); */

	temp_strings[temp_string_ptr]=calloc(strlen(s)+1,sizeof(char));
	if (temp_strings[temp_string_ptr]==NULL) {
		printf("Out of memory\n"); exit(1); }

	strcpy(temp_strings[temp_string_ptr],s);
#if DEBUG
	printf("TEMP STRING %d is %s\n",temp_string_ptr,temp_strings[temp_string_ptr]);
#endif
	temp_string_ptr++; /* incr for next call */
	return (varname);
	}

void vset(char *varname,char *s)
	{
	int i;

	i=(toupper(varname[0])-'A');
	if (strings[i]!=NULL) {
		free(strings[i]);
#if DEBUG
	printf("Free string %s\n",varname);
#endif
		}
#if DEBUG
	printf("About to allocate %d bytes\n",strlen(s)+1);
#endif

	strings[i]=calloc(strlen(s)+1,sizeof(char));
	if (strings[i]==NULL) {
		printf("Out of memory\n"); exit(1); }

	strcpy(strings[i],s);
#if DEBUG
	printf("STRING %s is %s\n",varname,strings[i]);
#endif
	return;
	}


void run(void)
	{
	int i,x;
	double answer;
	char *l,*where_else,ch;

	for(i=0;i<100;i++) temp_strings[i]=NULL;
	l=malloc(256);
	if(!l) {
		printf("memory allocation failure\n");
		exit(1);
		}

	/* process expressions until a blank line is entered */
	x=1;
	startseg=0;
	running=1;
	do {
	while((!program[x]) && (x<MAXPROG) ) x++;
	if (x==MAXPROG) return;
	if (!running) return;
	if (tron) printf("(%d)",x);
	strcpy(l,program[x++]);
	    currline=x; /*if progline changes currline, x will be set to it*/
	    ifs=0;
	    if(*l) {
		 lprep(l);
		 /* this next line may not be necessary if parser
		 exits on ':' anyway.  this just forces termination
		 with a null after each line segment */

		 for (i=0;i<256;i++) if (l[i]==':') l[i]='\0';
			 for(i=startseg;line[i];i++)
			     {
			     prog=line[i];
			     currseg=i;
			     if (step)
				{
				SAVECURSOR
				COLOR(FGBLACK,BGWHITE)
				LOCATE(1,1)
				printf("Line %d Stmt %d [%s]",x,i,line[i]);
				COLOR(of,ob)
				RESTCURSOR
				while(!kbhit());
				ch=getch();
				if (ch==27) {running=0;break;}
				if (toupper(ch)=='R') step=0;
				}
			     exec_line(&answer);

			     /* exit if a GOTO/GOSUB has occurred */
			     if (x!=currline) {x=currline;break;} else startseg=0;

			     /* no change in line number will cause startseg
				to initialize to zero, however if a line changes,
				there may be a segment change along with it, so
				we won't init startseg. */

			     }

		     /* need to be able to reset i from within parser */
		     /* use a global variable lineptr,offsetptr */
		   }


	     } while(1);
	}


void gpush(void)
	{
	gosubstack[gosubptr]=currline;
	gosubseg[gosubptr++]=currseg+1;
	return;
	}

void gpop(void)
	{
	currline=gosubstack[--gosubptr];
/*	startseg=gosubseg[gosubptr];*/
	startseg=0;
	return;
	}

void loadfile(char *fi)
	{
	char temp[256];
	char *p,*org;
	FILE *in;
	int x,y;
	struct ffblk ffblk;
	if (findfirst(fi,&ffblk,0)) {printf("File not found\n");return;}
	if( (in=fopen(fi,"rt"))==NULL ) {printf("Error\n");return;}
	while (!feof(in))
		{
		fgets(temp, 256, in);
		temp[255]='\0'; /* just in case */
		for (x=0;x<255;x++)
			if ((temp[x]=='\r')||(temp[x]=='\n')) temp[x]='\0';
		org=temp;
		p=temp;
		while(isspace(*p)) p++; /*skip white space*/
		x=atoi(p);                 /*get line no*/
		if (x!=0) {
			y=x;
			while(isdigit(*p)) p++; /*skip digits*/
			while(isspace(*p)) p++; /*skip white space*/
			  }
		else
			x=++y;
		if (program[x]!=NULL) free(program[x]);
		program[x]=calloc(strlen(org)+1,sizeof(char));
			if (program[x]==NULL)
				printf("Out of memory\n");
			else
				strcpy(program[x],p);

		}
	fclose(in);
	return;
	}


void savefile(char *fi)
	{
	int x;
	FILE *out;
	char temp[256];
	struct ffblk ffblk;
	if ((out=fopen(fi,"wt"))==NULL) {printf("Error\n");return;}
	for (x=1;x<MAXPROG;x++)
		{
		if (program[x]) {
			sprintf(temp,"%d %s\n",x,program[x]);
			fputs(temp,out);
				}
		}
	fclose(out);
	return;
	}

void input(void)
	{
	char inbuf[100];
	sched();
	if (argptr==2) printf(strargs[0]);
	gets(inbuf);
	if (lasttype==STRING)
		{
		if (strings[lastvar]!=NULL) free(strings[lastvar]);
		strings[lastvar]=calloc(strlen(inbuf),sizeof(char));
		strcpy(strings[lastvar],inbuf);
		}
	else
		{
		if (!isdigit(inbuf[0]) && (inbuf[0]!='\0'))
			while(!isdigit(inbuf[0]) && (inbuf[0]!='\0'))
				{
				printf("Must be numeric-Redo\n");
				gets(inbuf);
				}
		vars[lastvar]=atof(inbuf);
		}
	}


