/****************************************************************
*        NAME : sysinit(d4xint)
* DESCRIPTION : start d/4x system, set cst parameters, put line on
*             : hook and open vox files.
*       INPUT : d4xint = hardware interrupt level.
****************************************************************/
void sysinit(d4xint)
   int d4xint;
   {
      int channel;
      unsigned int rc;  /* return code from startsys() */

      /* check for the D/4x driver */
      if (!getvctr())  {
	 printf("DIALOG/4x voice driver not installed\n");
	 return;
      }

      /* make sure system is stopped before starting */
      stopsys();
      if (rc = startsys(d4xint,SM_EVENT,0,0,&channel))  {
	 printf("Unable to start voice system, Return code %d\n",rc);
	 return;
      }

      /* if more channels requested than actually exist, exit */
      if (maxchan > channel)  {
	 printf("Only %d in D/4x system\n",channel);
	 return;
      }

      /* if maximum channels set from the command line */
      if (maxchan==0 || maxchan>channel)  {
	 maxchan = channel;
      }

      /* maxchan can't be greater than MAXCHAN */
      if (maxchan>MAXCHAN) {
	 maxchan = MAXCHAN;
      }

      printf("Using %d lines\n",maxchan);

      /* set all channels to detect call */
      for (channel=1; channel<=maxchan; channel++)  {
	 /* auto-answer, enable loop signal and off-hook msgs */
	 setcst(channel,(C_LC+C_RING+C_OFFH+C_ONH),MAXRING);
	 sethook(channel,H_ONH); /* put line on hook */
       } /* for */

							if(++tempptr==10) tempptr=0;
					if (temps[tempptr]!=NULL) free(temps[tempptr]);
					temps[tempptr]=calloc(strlen(strargs[--argptr])+1,sizeof(char));
					strcpy(temps[tempptr],strargs[argptr]);
					strupr(temps[tempptr]);
					spush(temps[tempptr],0);
					break;
     }


/****************************************************************
*        NAME : play(channel,handle)
* DESCRIPTION : set R/W block and initiate playing a file
*       INPUT : channel = channel number
*             : handle = handle of vox file
*      OUTPUT : calls function that initiates play
*     RETURNS : error code from xplayf()
*    CAUTIONS : multi-tasking process
****************************************************************/
int play(channel,handle)
   int channel;
   int handle;
   {
      /* rewind to top of file */
      vhseek(handle,0L,0);

      clrrwb(&d4xrwb);             /* clear the D/4x read/write block */
      d4xrwb.filehndl = handle;    /* handle of file to play */
      d4xrwb.maxdtmf  = 1;         /* cause and event if max digits */
      d4xrwb.loopsig  = 1;         /* terminate on loop signal drop */

      /* play vox file on D/4x channel, normal play back */
      return (xplayf(channel,PM_NORM,&d4xrwb));
   }

/****************************************************************
*        NAME : record(channel,handle)
* DESCRIPTION : record message to file provided for channel.
*       INPUT : channel = channel number
*             : handle = handle of vox file
*      OUTPUT : initiate recording a message.
*     RETURNS : error code from recfile().
*    CAUTIONS : none.
****************************************************************/
int record(channel,handle)
   int channel,handle;
   {
      /* seek to the end of the file */
      vhseek(port[channel].msg_fh,(long int)0,2);

      /* set up read/write block for recording */
      clrrwb(&d4xrwb);
      d4xrwb.filehndl = handle;
      d4xrwb.maxsec   = 10;   /* maximum 10 seconds for the message   */
      d4xrwb.termdtmf = '#';  /* terminate if any dtmf                */
      d4xrwb.maxsil   = 5;    /* terminate after 5 seconds of silence */
      d4xrwb.loopsig  = 1;    /* terminate on loop signal             */
      d4xrwb.rwbflags = 0x02; /* enable beep before record            */
      d4xrwb.rwbdata1 = 3;    /* .6 second beep                       */

      return (recfile(channel,&d4xrwb,RM_NORM));
   }

/****************************************************************
*        NAME : get_digits(channel,bufp)
* DESCRIPTION : set up RWB and call getdtmfs()
*       INPUT : channel = channel number
*             : bufp = pointer to dtmf buffer
*      OUTPUT : none
*     RETURNS : error code from getdtmfs()
*    CAUTIONS : none
****************************************************************/
int get_digits(channel,bufp)
   int channel;
   char *bufp;
   {
      /* set up the read/write block for call to getdtmfs() */
      clrrwb(&d4xrwb);
      d4xrwb.xferoff  = d4getoff(bufp);  /* seg,ofst of bufr */
      d4xrwb.xferseg  = d4getseg(bufp);
      d4xrwb.maxdtmf  = MAXDTMF; /* request 4 dtmf digits */
      d4xrwb.maxsec   = 1;       /* wait 1 seconds           */
      d4xrwb.loopsig  = 1;       /* terminate on loop signal */
      d4xrwb.termdtmf = '#';  /* terminate if any dtmf                */
      return (getdtmfs(channel,&d4xrwb));
   }




/** function STARTSYS([interrupt]) starts dialogic driver
 ** and returns the number of channels installed.  Returns
 ** 0 if unable to initialize.  Interrupt is optional.
 ********************************************************/

struct bwb_variable *
fnc_f001( int argc, struct bwb_variable *argv )
   {

   static struct bwb_variable nvar;
   static int init = FALSE;
      int channel;
      int d4xint;
      int rc;  /* return code from startsys() */
   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   /* check for correct number of parameters */

   if ( fnc_checkargs( argc, argv, 0, 1 ) == FALSE )
      {
      return NULL;
      }

      d4xint=5;
      if (argc=1) d4xint = var_getival( &( argv[ 0 ] ));


      /* check for the D/4x driver */
      if (!getvctr())  {
	 printf("DIALOG/4x voice driver not installed\n");
	   * var_findival( &nvar, nvar.array_pos ) = 0;
	   return(&nvar); /* return value here */
      }

      /* make sure system is stopped before starting */
      stopsys();
      if (rc = startsys(d4xint,SM_EVENT,0,0,&channel))  {
	 printf("Unable to start voice system, Return code %d\n",rc);
	   * var_findival( &nvar, nvar.array_pos ) = 0;
	   return(&nvar); /* return value here */
      }

      /* if more channels requested than actually exist, exit */
      if (maxchan > channel)  {
	 printf("Only %d in D/4x system\n",channel);
	   * var_findival( &nvar, nvar.array_pos ) = 0;
	   return(&nvar); /* return value here */	 return NULL;
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
	 setcst(channel,(C_LC+C_RING+C_OFFH+C_ONH),MAXRING);
	 sethook(channel,H_ONH); /* put line on hook */
       } /* for */

   * var_findival( &nvar, nvar.array_pos ) = maxchan;
   return(&nvar); /* return value here */
   }


/*GETDTMF$*************************************************************/

struct bwb_variable *
fnc_f002( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   /* check for correct number of parameters */

   if ( fnc_checkargs( argc, argv, 2, 3 ) == FALSE )
      {
      return NULL;
      }
   * var_findival( &nvar, nvar.array_pos ) = (int) 2;
   return(&nvar); /* return value here */
   }

/*GETTONE$*************************************************************/

struct bwb_variable *
fnc_f003( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   /* check for correct number of parameters */

   if ( fnc_checkargs( argc, argv, 1, 1 ) == FALSE )
      {
      return NULL;
      }


   return &nvar; /* return value here */
   }



/*LISTEN*************************************************************/

struct bwb_variable *
fnc_f004( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   /* check for correct number of parameters */

   if ( fnc_checkargs( argc, argv, 2, 3 ) == FALSE )
      {
      return NULL;
      }


   return NULL; /* return value here */
   }


/*EVENT*************************************************************/

struct bwb_variable *
fnc_f005( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;
   int x;
   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

	 if (gtevtblk(&d4evtblk) == -1)    {
			 x=d4evtblk.evtcode;
			 channel = d4evtblk.devchan;
			 evtcode = d4evtblk.evtcode;
			 evtdata = d4evtblk.evtdata;
					  }
	 else
			x=0;

	 channel = d4evtblk.devchan;
	 evtcode = d4evtblk.evtcode;
	 evtdata = d4evtblk.evtdata;

	 * var_findival( &nvar, nvar.array_pos ) = x;

	 return &nvar; /* return value here */
   }



/*EVDATA*************************************************************/

struct bwb_variable *
fnc_f006( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   * var_findival( &nvar, nvar.array_pos ) = evtdata;

   return &nvar; /* return value here */

}
/*D4ERROR*************************************************************/

struct bwb_variable *
fnc_f007( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   /* check for correct number of parameters */

   if ( fnc_checkargs( argc, argv, 2, 3 ) == FALSE )
      {
      return NULL;
      }


   return NULL; /* return value here */
   }


/*INKEY$*************************************************************/

struct bwb_variable *
fnc_f008( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int ini = FALSE;
   static char *tbuf;

   /* initialize the variable if necessary */

   if ( ini == FALSE )
      {
      ini = TRUE;
      var_make( &nvar, STRING );
      if ( ( tbuf = calloc( 4, sizeof( char ) )) == NULL )
	 {
	 bwb_error( err_getmem );
	 }
      }

   tbuf[1]='\0'; /* make tbuf 2nd, 3rd chars nulls */
   tbuf[2]='\0';
   if (!kbhit()) tbuf[0]='\0'; else  /* return null unless kbhit */
	tbuf[0] = (char) getch();

   str_ctob( var_findsval( &nvar, nvar.array_pos ), tbuf );

   return &nvar;
   }



/*CHANNEL******************************************************/

struct bwb_variable *
fnc_f009( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   * var_findival( &nvar, nvar.array_pos ) = channel;

   return &nvar; /* return value here */
   }


/*INKEY*********************************************************/

struct bwb_variable *
fnc_f010( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;
   int x;
   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }


	 if (!kbhit()) x=0;
	 else
		     x=getch();

	 * var_findival( &nvar, nvar.array_pos ) = x;

	 return &nvar; /* return value here */
   }





/*FREE*********************************************************/

struct bwb_variable *
fnc_f011( int argc, struct bwb_variable *argv )

   {
   static struct bwb_variable nvar;
   static int init = FALSE;
   int x;
   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }


	 x=(int) (farcoreleft()/1024);

	 * var_findival( &nvar, nvar.array_pos ) = x;

	 return &nvar; /* return value here */
   }


/**************************************************************/

struct bwb_variable *
fnc_f012( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   /* check for correct number of parameters */

   if ( fnc_checkargs( argc, argv, 2, 3 ) == FALSE )
      {
      return NULL;
      }


   return NULL; /* return value here */
   }



/**************************************************************/

struct bwb_variable *
fnc_f013( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   /* check for correct number of parameters */

   if ( fnc_checkargs( argc, argv, 2, 3 ) == FALSE )
      {
      return NULL;
      }


   return NULL; /* return value here */
   }



/**************************************************************/

struct bwb_variable *
fnc_f014( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   /* check for correct number of parameters */

   if ( fnc_checkargs( argc, argv, 2, 3 ) == FALSE )
      {
      return NULL;
      }


   return NULL; /* return value here */
   }



/**************************************************************/

struct bwb_variable *
fnc_f015( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   /* check for correct number of parameters */

   if ( fnc_checkargs( argc, argv, 2, 3 ) == FALSE )
      {
      return NULL;
      }


   return NULL; /* return value here */
   }



/**************************************************************/

struct bwb_variable *
fnc_f016( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   /* check for correct number of parameters */

   if ( fnc_checkargs( argc, argv, 2, 3 ) == FALSE )
      {
      return NULL;
      }


   return NULL; /* return value here */
   }



/**************************************************************/

struct bwb_variable *
fnc_f017( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   /* check for correct number of parameters */

   if ( fnc_checkargs( argc, argv, 2, 3 ) == FALSE )
      {
      return NULL;
      }


   return NULL; /* return value here */
   }



/**************************************************************/

struct bwb_variable *
fnc_f018( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   /* check for correct number of parameters */

   if ( fnc_checkargs( argc, argv, 2, 3 ) == FALSE )
      {
      return NULL;
      }


   return NULL; /* return value here */
   }



/**************************************************************/

struct bwb_variable *
fnc_f019( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   /* check for correct number of parameters */

   if ( fnc_checkargs( argc, argv, 2, 3 ) == FALSE )
      {
      return NULL;
      }


   return NULL; /* return value here */
   }



/**************************************************************/

struct bwb_variable *
fnc_f020( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   /* check for correct number of parameters */

   if ( fnc_checkargs( argc, argv, 2, 3 ) == FALSE )
      {
      return NULL;
      }


   return NULL; /* return value here */
   }



/**************************************************************/

struct bwb_variable *
fnc_f021( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   /* check for correct number of parameters */

   if ( fnc_checkargs( argc, argv, 2, 3 ) == FALSE )
      {
      return NULL;
      }


   return NULL; /* return value here */
   }



/**************************************************************/

struct bwb_variable *
fnc_f022( int argc, struct bwb_variable *argv )
   {
   static struct bwb_variable nvar;
   static int init = FALSE;

   /* initialize the variable if necessary */

   if ( init == FALSE )
      {
      init = TRUE;
      var_make( &nvar, INTEGER );
      }

   /* check for correct number of parameters */

   if ( fnc_checkargs( argc, argv, 2, 3 ) == FALSE )
      {
      return NULL;
      }


   return NULL; /* return value here */
   }





















/**************************************************************/
struct bwb_line *
bwb_c001( struct bwb_line *l ) /* BASIC LOCATE STATEMENT */
   {
   struct exp_ese *e;
   int x,y;
   static int p;
   char tbuf[ MAXSTRINGSIZE + 1 ];
   adv_element( l->buffer, &( l->position ), tbuf );
   switch( l->buffer[ l->position ] )
      {
      case '\0':
      case '\n':
      case '\r':
      case ':':
	 bwb_error( err_syntax );
	 l->next->position = 0;
	 return l->next;
      default:
	 break;
      }

   p=0;
   e = bwb_exp( tbuf, FALSE, &p );
   x=exp_getival(e);

  ++( l->position );
  adv_element( l->buffer, &( l->position ), tbuf );

   p=0;
   e = bwb_exp( tbuf, FALSE, &p );

   y=exp_getival(e);

   gotoxy(y,x);
   l->next->position = 0;
   return l->next;
   }

/*TEST*************************************************************/
struct bwb_line *
bwb_c002( struct bwb_line *l )
   {

   /* test command for getting parameters */
   struct exp_ese *e;
   int x,y;
   static int p;
   char tbuf[ MAXSTRINGSIZE + 1 ];

   printf("starting - [%s]\n",l->buffer);
   printf("%d\n",l->position);


   adv_element( l->buffer, &( l->position ), tbuf );
   printf("%d - [%s]\n",l->position,tbuf);

   ++( l->position );
   adv_element( l->buffer, &( l->position ), tbuf );
   printf("%d - [%s]\n",l->position,tbuf);

   ++( l->position );
   adv_element( l->buffer, &( l->position ), tbuf );
   printf("%d - [%s]\n",l->position,tbuf);

   l->next->position = 0;
   return l->next;
   }

/*DIAL*************************************************************/
struct bwb_line *
bwb_c003( struct bwb_line *l )
   {
   /* do stuff here */
   return l->next;
   }

/*OFFHOOK*************************************************************/
struct bwb_line *
bwb_c004( struct bwb_line *l )
   {
   /* do stuff here */

   struct exp_ese *e;
   int channel,errcode;
   static int p;

   char tbuf[ MAXSTRINGSIZE + 1 ];
   adv_element( l->buffer, &( l->position ), tbuf );
   p=0;
   e = bwb_exp( tbuf, FALSE, &p );
   channel=exp_getival(e);
   if (channel > 0)
	 errcode = sethook(channel,H_OFFH);
   l->next->position = 0;
   return l->next;
   }


/*ONHOOK*******************************************************/
struct bwb_line *
bwb_c005( struct bwb_line *l )
   {
   /* do stuff here */
   struct exp_ese *e;
   int channel,errcode;
   static int p;

   char tbuf[ MAXSTRINGSIZE + 1 ];
   adv_element( l->buffer, &( l->position ), tbuf );
   p=0;
   e = bwb_exp( tbuf, FALSE, &p );
   channel=exp_getival(e);
   if (channel > 0)
	 errcode = sethook(channel,H_ONH);
   l->next->position = 0;
   return l->next;
   }

/*COLOR********************************************************/
struct bwb_line *
bwb_c006( struct bwb_line *l )
   {
   struct exp_ese *e;
   int x,y;
   static int p;
   char tbuf[ MAXSTRINGSIZE + 1 ];
   adv_element( l->buffer, &( l->position ), tbuf );
   switch( l->buffer[ l->position ] )
      {
      case '\0':
      case '\n':
      case '\r':
      case ':':
	 bwb_error( err_syntax );
	 l->next->position = 0;
	 return l->next;
      default:
	 break;
      }

   p=0;
   e = bwb_exp( tbuf, FALSE, &p );
   x=exp_getival(e);

  ++( l->position );
  adv_element( l->buffer, &( l->position ), tbuf );

   p=0;
   e = bwb_exp( tbuf, FALSE, &p );

   y=exp_getival(e);

   COLOR(x+30,y+40)

   l->next->position = 0;
   return l->next;
   }

/*PLAY*********************************************************/
struct bwb_line *
bwb_c007( struct bwb_line *l )
   {
   /* do stuff here */
   l->next->position = 0;
   return l->next;
   }

/*VOPEN********************************************************/
struct bwb_line *
bwb_c008( struct bwb_line *l )
   {
   /* do stuff here */
   l->next->position = 0;
   return l->next;
   }

/*VCLOSE*******************************************************/
struct bwb_line *
bwb_c009( struct bwb_line *l )
   {
   /* do stuff here */
   l->next->position = 0;
   return l->next;
   }

/*RECORD*******************************************************/
struct bwb_line *
bwb_c010( struct bwb_line *l )
   {
   /* do stuff here */
   l->next->position = 0;
   return l->next;
   }


/**************************************************************/
struct bwb_line *
bwb_c011( struct bwb_line *l )
   {
   /* do stuff here */
   l->next->position = 0;
   return l->next;
   }


/*RECORD*******************************************************/
struct bwb_line *
bwb_c012( struct bwb_line *l )
   {
   /* do stuff here */
   l->next->position = 0;
   return l->next;
   }

/*RECORD*******************************************************/
struct bwb_line *
bwb_c013( struct bwb_line *l )
   {
   /* do stuff here */
   l->next->position = 0;
   return l->next;
   }


/*RECORD*******************************************************/
struct bwb_line *
bwb_c014( struct bwb_line *l )
   {
   /* do stuff here */
   l->next->position = 0;
   return l->next;
   }


/*RECORD*******************************************************/
struct bwb_line *
bwb_c015( struct bwb_line *l )
   {
   /* do stuff here */
   l->next->position = 0;
   return l->next;
   }


/*RECORD*******************************************************/
struct bwb_line *
bwb_c016( struct bwb_line *l )
   {
   /* do stuff here */
   l->next->position = 0;
   return l->next;
   }


/*RECORD*******************************************************/
struct bwb_line *
bwb_c017( struct bwb_line *l )
   {
   /* do stuff here */
   l->next->position = 0;
   return l->next;
   }


/*RECORD*******************************************************/
struct bwb_line *
bwb_c018( struct bwb_line *l )
   {
   /* do stuff here */
   l->next->position = 0;
   return l->next;
   }


/*RECORD*******************************************************/
struct bwb_line *
bwb_c019( struct bwb_line *l )
   {
   /* do stuff here */
   l->next->position = 0;
   return l->next;
   }


/*RECORD*******************************************************/
struct bwb_line *
bwb_c020( struct bwb_line *l )
   {
   /* do stuff here */
   l->next->position = 0;
   return l->next;
   }


/*RECORD*******************************************************/
struct bwb_line *
bwb_c021( struct bwb_line *l )
   {
   /* do stuff here */
   l->next->position = 0;
   return l->next;
   }


/*RECORD*******************************************************/
struct bwb_line *
bwb_c022( struct bwb_line *l )
   {
   /* do stuff here */
   l->next->position = 0;
   return l->next;
   }

