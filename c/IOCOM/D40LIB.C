
 /***********************************************************
 *                                                          *
 *   DDDDDD     4444    00000   LL       IIIIII   BBBBBB    *
 *   DD   DD   44 44   00   00  LL         II     BB   BB   *
 *   DD   DD  44  44   00   00  LL         II     BB   BB   *
 *   DD   DD  44  44   00   00  LL         II     BBBBBB    *
 *   DD   DD  4444444  00   00  LL         II     BB   BB   *
 *   DD   DD      44   00   00  LL         II     BB   BB   *
 *   DDDDDD       44    00000   LLLLLL   IIIIII   BBBBBB    *
 *                                                          *
 ************************************************************
 *   Version 3.90                               09/30/93    *
 ***********************************************************/

 /*	NOTICE !!!!!!!
 /*	This version of d40lib is modified for Borland Turbo C.
 /*	All changes from the original are commented. You may need
 /*	to change all instances of __TURBOC__ to __BORLANDC__
 /*
 /*	Rick Marlette, P O E Systems, Atlanta, Ga. (800) 722-3615.
 */

#include <string.h>              /* prototype for memset() */

/* ----------------------------- Borland Mod --------------------------	*/

#include "\dialogic\borland\d40.h" 	/* assumes files are here */
#include "\dialogic\borland\d40lib.h"	/* assumes files are here */
#include "\dialogic\borland\vfcns.h"	/* assumes files are here */


#ifdef __TURBOC__
#include <dos.h>
#endif
/* --------------------------- End Borland Mod ----------------------- */

static int           rc;
static unsigned char ral;
static unsigned int  rbx;
static unsigned int  rcx;
static unsigned int  rdx;
static unsigned int  rds;

static unsigned char *ralp = &ral;
static unsigned int  *rbxp = &rbx;
static unsigned int  *rcxp = &rcx;
static unsigned int  *rdxp = &rdx;

static int gtdampl1_min = GT_DEF_MIN;
static int gtdampl1_max = GT_DEF_MAX;
static int gtdampl2_min = GT_DEF_MIN;
static int gtdampl2_max = GT_DEF_MAX;
static int gtdampl_flag = 0;
static int tn_qualid;

/*
 * d40fcns.c prototypes.
 */
int calld40(unsigned char,unsigned char *,unsigned int *,unsigned int *,unsigned int *);
int calld40x(unsigned char,unsigned char *,unsigned int *,unsigned int *, unsigned char *);
int dskseek(unsigned int,unsigned int,unsigned int *,unsigned int *);
int dskcrea(unsigned char *,unsigned int *,unsigned int);
int dskclose(unsigned int);
int dskopen(unsigned char *,unsigned int *,unsigned int);


/*
 * public data - These variables are set by d40lib and can be accessed
 *               by the application as extern variables.
 */
int _d40act=0;         /* d40 system status (0=idle, 1=active)      */
int _amxact=0;         /* AMX8x status      (0=idle, 1=active)      */
int _d40derr=0;        /* DOS error for vhopen(),vhclose, or vhseek */


/*
 * int_level - This variable can be altered by the application if
 *             the default software interrupt level is not desired.
 *             If the application does not know which software
 *             vector was claimed by D40DRV, the function getvctr()
 *             will search all available user vectors for D40DRV.
 */
int int_level=0x6D;    /* standard software interrupt level*/

/*********************************************************************
 *       NAME : clrrwb(rwbp)
 *DESCRIPTION : Zero the RWB (Read Write Block)
 *              NOTE: For future compatibility, it is strongly
 *              recommended to call this function before
 *              initializing the RWB.
 *      INPUT : pointer to the RWB.
 *     OUTPUT : none.
 *    RETURNS : none.
 *      CALLS : memset()
 *   CAUTIONS : none.
 ********************************************************************/
void clrrwb(rwbp)
   RWB *rwbp;
   {
      memset(rwbp,0,RWB_SIZE);
   }


/*********************************************************************
 *       NAME : clrxrwb(rwbp)
 *DESCRIPTION : Zero the XRWB (Extended Read Write Block)
 *              NOTE: For future compatibility, it is strongly
 *              recommended to call this function before
 *              initializing the XRWB.
 *      INPUT : pointer to the XRWB.
 *     OUTPUT : none.
 *    RETURNS : none.
 *      CALLS : memset()
 *   CAUTIONS : none.
 ********************************************************************/
void clrxrwb(rwbp)
   RWB *rwbp;
   {
      memset(rwbp,0,RWB_SIZE+XRWB_SIZE);
   }


/*********************************************************************
 *       NAME : clrdcb(dcbp)
 *DESCRIPTION : Zero the DCB (Dialog Control Block)
 *              NOTE: For future compatibility, it is strongly
 *              recommended to call this function before
 *              initializing the DCB.
 *      INPUT : pointer to the DCB.
 *     OUTPUT : none.
 *    RETURNS : none.
 *      CALLS : memset()
 *   CAUTIONS : none.
 ********************************************************************/
void clrdcb(dcbp)
   DCB *dcbp;
   {
      memset(dcbp,0,sizeof(DCB));
   }


/********************************************************************
 *       NAME : clrcpb(cpbp)
 *DESCRIPTION : Zero the CPB (Channel Parameter Block)
 *              NOTE: For future compatibility, it is strongly
 *              recommended to call this function before
 *              initializing the CPB.
 *      INPUT : pointer to the CPB.
 *     OUTPUT : none.
 *    RETURNS : none.
 *      CALLS : memset()
 *   CAUTIONS : none.
 ********************************************************************/
void clrcpb(cpbp)
   CPB *cpbp;
   {
      memset(cpbp,0,sizeof(CPB));
   }


/********************************************************************
 *       NAME : isdrvact(swintr)
 *DESCRIPTION : Check to see if DIALOG/4x voice driver is installed.
 *      INPUT : swintr = DIALOG/4x driver software interrupt level.
 *                     (Driver defaults to 0x6D, but via a command line
 *                     option, can be loaded at any available vector).
 *     OUTPUT : none.
 *    RETURNS : 0 = D40DRV is not installed
 *              1 = if D/4x voice driver is installed
 *              2 = if D/4x (v2.92 and greater) installed with no
 *                  D/4x devices present.
 *      CALLS : none.
 *   CAUTIONS : none.
 ********************************************************************/
int isdrvact(swintr)
   unsigned char swintr;
   {
      unsigned short int ivector,drvsig;
      unsigned short int drvseg,drvoff;

      /* software vector address = 4 x software interrupt number */
      ivector = swintr*4;

      /* get the pointer (seg:off) that is stored at the vector */

/* ----------------------------- Borland Mod --------------------------	*/
#ifndef __TURBOC__

      peek(0,ivector,(char *)&drvoff,2);
      peek(0,ivector+2,(char *)&drvseg,2);

      /* then back up 2 bytes and get the data from memory */
      peek(drvseg,drvoff-2,(char *)&drvsig,2);
#else
      drvoff = peek(0,ivector);
      drvseg = peek(0,ivector+2);
      /* then back up 2 bytes and get the data from memory */
      drvsig = peek(drvseg,drvoff-2);
#endif
/* --------------------------- End Borland Mod ----------------------- */

      /* check for the ascii signature: ID */
      if (drvsig == 0x4449)
	 return(2);

      /* check for the ascii signature: DL */
      if (drvsig == 0x4C44)
	 return(1);

      return(0);
   }


/********************************************************************
 *       NAME : setgparm(dcbp)
 *DESCRIPTION : Set DIALOG/4x global parameters.
 *      INPUT : dcbp = pointer to DCB structure.
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : This function must be called prior to system start.
 ********************************************************************/
int setgparm(dcbp)
   DCB *dcbp;
   {
      ral = 0;
      rbx = 0;
      rcx = 0;

      return (calld40x(F_SPARMS,ralp,rbxp,rcxp,(unsigned char *) dcbp));
   }


/********************************************************************
 *       NAME : setxparm(dcbp)
 *DESCRIPTION : Set DIALOG/4x extended global parameters.
 *      INPUT : dcbp = pointer to extended DCB structure.
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : This function must be called prior to system start.
 ********************************************************************/
int setxparm(dcbp)
   DCB *dcbp;
   {
      ral = 0;
      rbx = 0;
      rcx = 0;

      return (calld40x(F_SXPARM,ralp,rbxp,rcxp,(unsigned char *) dcbp));
   }


/********************************************************************
 *       NAME : startsys(hwintr,mode,bufseg,nparag,chanp)
 *DESCRIPTION : Start the DIALOG/4x boards and device driver.
 *      INPUT : hwintr = DIALOG/4x hardware interrupt level (2-7)
 *                mode = system mode.
 *                     = 0 indicates polling (SM_POLL)
 *                     = 1 indicates event driven (SM_EVENT)
 *              bufseg = buffer segment (base)
 *              nparag = number of paragraphs in buffer (0=default buffers)
 *               chanp = pntr where to return number of chan's in sys.
 *     OUTPUT : *chanp set to number of channels in system.
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : Unpredictable results will occur if driver is not
 *              installed - Use the isdrvact() function to determine
 *              if the DIALOG/4x voice driver is installed.
 ********************************************************************/
int startsys(hwintr,mode,bufseg,nparag,chanp)
   unsigned int hwintr;
   unsigned int mode;
   unsigned int bufseg;
   unsigned int nparag;
   unsigned int *chanp;
   {
      ral = (unsigned char) hwintr;
      rbx = (mode&1);
      rcx = nparag;
      rdx = bufseg;

      /* perform the start sys */
      rc = calld40(F_SSTART,ralp,rbxp,rcxp,rdxp);

      /* return the number of d/4x channels */
      *chanp = *ralp;

      /* set status flag if system start was successful */
      if (!rc) {
         /* indicate system is active */
         _d40act = 1;
      }
      return (rc);
   }


/********************************************************************
 *       NAME : setcparm(chan,cpbp)
 *DESCRIPTION : Set DIALOG/4x channel parameters.
 *      INPUT : chan = channel number.
 *            : cpbp = pointer to CPB structure.
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : This function must be called after system start.
 ********************************************************************/
int setcparm(chan,cpbp)
   unsigned int chan;
   CPB *cpbp;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;

      return (calld40x(F_SCPARM,ralp,rbxp,rcxp,(unsigned char *) cpbp));
   }


/********************************************************************
 *       NAME : stopsys()
 *DESCRIPTION : Stop all DIALOG/4x operations
 *      INPUT : none.
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : none.
 ********************************************************************/
int stopsys()
   {
      ral = 0;
      rbx = 0;
      rcx = 0;

      rc = calld40(F_SSTOP,ralp,rbxp,rcxp,rdxp);

      /* clear status flag if system stop was successful */
      if (!rc) {
         /* indicate system is not active */
         _d40act = 0;
      }
      return (rc);
   }


/********************************************************************
 *       NAME : stopch(chan)
 *DESCRIPTION : Request a stop of current multitasking function.
 *      INPUT : chan = channel number.
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : If a multi-tasking function is active, a stop event
 *              (T_STOP) will be queued.
 *              If a multi-tasking function is NOT active, no event will
 *              be queued, even though stopch() returns a success status.
 ********************************************************************/
int stopch(chan)
   unsigned int chan;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;

      return (calld40(F_CHSTOP,ralp,rbxp,rcxp,rdxp));
   }


/********************************************************************
 *       NAME : getcstat(chan,csbp)
 *DESCRIPTION : Get channel status
 *      INPUT : chan = channel number.
 *              csbp  = pointer to Channel Status Block (CSB).
 *     OUTPUT : channel status information is copied into the callers
 *              local CSB.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : none.
 ********************************************************************/
int getcstat(chan,csbp)
   unsigned int chan;
   CSB *csbp;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;

      return (calld40x(F_CHSTAT,ralp,rbxp,rcxp,(unsigned char *) csbp));
   }


/********************************************************************
 *       NAME : sethook(chan,state)
 *DESCRIPTION : Set the specified channel to the specified hook status.
 *      INPUT : chan = channel number.
 *              state = desired hook status.
 *                      0 = offhook (H_OFFH)
 *                      1 = onhook (H_ONH)
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : This is a multi-tasking function, and should only
 *              be called when no other multi-tasking function is
 *              in progress.
 ********************************************************************/
int sethook(chan,state)
   unsigned int chan;
   unsigned int state;
   {
      ral = (unsigned char)chan;
      rbx = state;
      rcx = 0;

      return (calld40(F_SETH,ralp,rbxp,rcxp,rdxp));
   }


/********************************************************************
 *       NAME : setcst(chan,mask,nrings)
 *DESCRIPTION : Set Call Status Transition mask for a channel.
 *      INPUT : chan   = channel number.
 *              mask   = requested CST mask (C_LC, C_RING, C_ONH, etc.)
 *              nrings = number of incoming rings before pickup.
 *                       (nrings set to 0 will disable notificiation)
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : none.
 ********************************************************************/
int setcst(chan,mask,nrings)
   unsigned int chan;
   unsigned int mask;
   unsigned int nrings;
   {
      ral = (unsigned char)chan;
      rbx = mask;
      rcx = nrings;

      return (calld40(F_SETCST,ralp,rbxp,rcxp,rdxp));
   }


/********************************************************************
 *       NAME : clrdtmf(chan)
 *DESCRIPTION : Clear all digits from the digit buffer.
 *      INPUT : chan = channel number.
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : none.
 ********************************************************************/
int clrdtmf(chan)
   unsigned int chan;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;

      return (calld40(F_CLRDT,ralp,rbxp,rcxp,rdxp));
   }


/********************************************************************
 *       NAME : readdtmf(chan,digstrp)
 *DESCRIPTION : Get the next digit from the specified channels
 *              digit buffer.
 *      INPUT : chan = channel number.
 *              digstrp = pointer to where to put the digit.
 *     OUTPUT : Next digit (in ASCII)
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : none.
 ********************************************************************/
int readdtmf(chan,digstrp)
   unsigned int chan;
   unsigned char *digstrp;
   {
      ral = (unsigned char)chan;

      rc = calld40(F_GETDT,ralp,rbxp,rcxp,rdxp);

      if (!rc)
         /* return the digit to the application */
         *digstrp = (unsigned char)(rbx&0xff);
      else
         /* no digit available */
         *digstrp = 0;

      return(rc);      
   }


/********************************************************************
 *       NAME : recbuf(chan,rwbp)
 *DESCRIPTION : Record data to a buffer.
 *      INPUT : chan = channel number.
 *              rwbp  = pointer to Read/Write Block (RWB)
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : This is a multi-tasking function, and should only
 *              be called when no other multi-tasking function is
 *              in progress.
 ********************************************************************/
int recbuf(chan,rwbp)
   unsigned int chan;
   RWB *rwbp;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;

      return (calld40x(F_RECB,ralp,rbxp,rcxp,(unsigned char *) rwbp));
   }


/********************************************************************
 *       NAME : playbuf(chan,rwbp)
 *DESCRIPTION : Play data from a buffer.
 *      INPUT : chan = channel number.
 *              rwbp  = pointer to Read/Write Block (RWB)
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : This is a multi-tasking function, and should only
 *              be called when no other multi-tasking function is
 *              in progress.
 ********************************************************************/
int playbuf(chan,rwbp)
   unsigned int chan;
   RWB *rwbp;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;

      return (calld40x(F_PLAYB,ralp,rbxp,rcxp,(unsigned char *) rwbp));
   }


/********************************************************************
 *       NAME : recfile(chan,rwbp,mode)
 *DESCRIPTION : Record data to a file.
 *      INPUT : chan = channel number.
 *              rwbp  = pointer to Read/Write Block (RWB)
 *              mode  = record mode bit mask 
 *                      RM_NORM  = normal record
 *                      RM_SCOMP = silence compressed record
 *                      RM_RDISK = record to a ram disk (should only
 *                                 be set if the driver's buffers were
 *                                 in ems memory (-E option) and the
 *                                 user has loaded the driver using the
 *                                 -V option (allocate transfer buffer)
 *                      RM_ADPCM = ADPCM encoding
 *                      RM_PCM   = Mu Law PCM encoding
 *                      RM_SR6   = 6KHz sampling
 *                      RM_SR8   = 8KHz sampling
 *                      RM_NOAGC = Turn off Automatic Gain Control
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : Same as recbuf.
 ********************************************************************/
int recfile(chan,rwbp,mode)
   unsigned int chan;
   RWB *rwbp;
   unsigned int mode;
   {
      ral = (unsigned char)chan;
      rbx = mode;
      rcx = 0;

      return (calld40x(F_RECF,ralp,rbxp,rcxp,(unsigned char *) rwbp));
   }


/********************************************************************
 *       NAME : playfile(chan,rwbp)
 *DESCRIPTION : Play data from a file.
 *      INPUT : chan = channel number.
 *              rwbp  = pointer to Read/Write Block (RWB)
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : Same as playbuf.
 ********************************************************************/
int playfile(chan,rwbp)
   unsigned int chan;
   RWB *rwbp;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;

      return (calld40x(F_PLAYF,ralp,rbxp,rcxp,(unsigned char *) rwbp));
   }


/********************************************************************
 *       NAME : xplayf(chan,mode,rwbp)
 *DESCRIPTION : Extended play data from a file.
 *      INPUT : chan = channel number.
 *              mode  = play mode 
 *                      PM_NORM  = normal play (same as playfile)
 *                      PM_NDX   = indexed play
 *                      PM_RDISK = play from a ram disk (should only
 *                                 be set if the driver's buffers were
 *                                 in ems memory (-E option) and the
 *                                 user has loaded the driver using the
 *                                 -V option (allocate transfer buffer)
 *                      PM_FILES = Multiple file handles for PM_NDX
 *                      PM_ADPCM = ADPCM encoded file
 *                      PM_PCM   = Mu Law PCM encoded file
 *                      PM_SR6   = 6KHz sampled file
 *                      PM_SR8   = 8KHz sampled file
 *                   
 *              rwbp  = pointer to Read/Write Block (RWB)
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : Indexed play (PM_NDX) requires the application to set
 *              up an indexed play table.  This table (pointed to in
 *              the RWB), must be in static ram and can not be altered
 *              until the indexed play function has completed.
 *              This is a multi-tasking function, and should only
 *              be called when no other multi-tasking function is
 *              in progress.
 ********************************************************************/
int xplayf(chan,mode,rwbp)
   unsigned int chan;
   unsigned int mode;
   RWB *rwbp;
   {
      ral = (unsigned char)chan;
      rbx = mode;
      rcx = 0;

      return (calld40x(F_XPLAYF,ralp,rbxp,rcxp,(unsigned char *) rwbp));
   }


/********************************************************************
 *       NAME : dial(chan,digstrp)
 *DESCRIPTION : Dial an ASCIIZ string.
 *      INPUT : chan = channel number.
 *              digstrp = pointer to ASCIIZ string to dial.
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : A channel stop function will not stop dialing.
 *              Dial does not take channel offhook.
 *              This is a multi-tasking function, and should only
 *              be called when no other multi-tasking function is
 *              in progress.
 ********************************************************************/
int dial(chan,digstrp)
   unsigned int chan;
   unsigned char *digstrp;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;

      return (calld40x(F_DIAL,ralp,rbxp,rcxp,digstrp));
   }


/********************************************************************
 *       NAME : callp(chan,digstrp)
 *DESCRIPTION : Call a number and monitor call progress.
 *      INPUT : chan = channel number.
 *              digstrp = pointer to ASCIIZ string to dial.
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : channel will be offhook after this fucntion is complete.
 *              A channel stop function will stop the operation
 *              only after the dialing has completed.
 *              This is a multi-tasking function, and should only
 *              be called when no other multi-tasking function is
 *              in progress.
 ********************************************************************/
int callp(chan,digstrp)
   unsigned int chan;
   unsigned char *digstrp;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;

      return (calld40x(F_CALL,ralp,rbxp,rcxp,digstrp));
   }

/********************************************************************
 *       NAME : xcallp(chan,rwbp)
 *DESCRIPTION : Call a number and monitor call progress with termination
 *              conditions.
 *      INPUT : chan = channel number.
 *              rwbp = pointer to Read/Write Block (RWB)
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : channel will be offhook after this fucntion is complete.
 *              A channel stop function or a satisfied terminating condition
 *              will stop the operation only after the dialing has completed.
 *              This is a multi-tasking function, and should only
 *              be called when no other multi-tasking function is
 *              in progress.
 ********************************************************************/
int xcallp(chan,rwbp)
   unsigned int chan;
   RWB *rwbp;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;

      return (calld40x(F_XCALL,ralp,rbxp,rcxp,(unsigned char *) rwbp));
   }

/********************************************************************
 *       NAME : getdtmfs(chan,rwbp)
 *DESCRIPTION : Get a ASCIIZ digit string.
 *      INPUT : chan = channel number.
 *              rwbp  = pointer to Read/Write Block (RWB)
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : This functions gets an ASCIIZ string.
 *              NOTE:  Make sure that the 'static' buffer (pointed
 *              to in the RWB) has enough room for the requested
 *              digits plus the '0' termination character.
 *              This is a multi-tasking function, and should only
 *              be called when no other multi-tasking function is
 *              in progress.
 ********************************************************************/
int getdtmfs(chan,rwbp)
   unsigned int chan;
   RWB *rwbp;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;

      return (calld40x(F_GETDTS,ralp,rbxp,rcxp,(unsigned char *) rwbp));
   }


/********************************************************************
 *       NAME : getevt(chanp,codep,datap)
 *DESCRIPTION : Get next event.
 *      INPUT : chanp = pointer to where to return the channel number
 *              codep = pointer to where to return the event code
 *              datap = pointer to where to return event data
 *     OUTPUT : *echanp = channel number of event
 *              *ecodep = event code
 *              *edatap = event data
 *    RETURNS : -1 if an event available
 *               0 if no event available
 *              >0 if error (value is return code)
 *      CALLS : calld40()
 *   CAUTIONS : Versions 2.92 or greater support all of Dialogic's
 *              installable device drivers.  The event queue is used
 *              for both the D4x/AMX events as well as the other
 *              Dialogic devices.  In order to distinguish between
 *              D4x events and other products (such as MF/40, DTI,
 *              VR/10, etc.), the application should use gtevtblk().
 ********************************************************************/
int getevt(chanp,codep,datap)
   unsigned int *chanp;
   unsigned int *codep;
   unsigned int *datap;
   {
      rc = calld40(F_GETEVT,ralp,rbxp,rcxp,rdxp);
      *chanp = *ralp;
      *codep = *rbxp;
      *datap = *rcxp;

      return ((*chanp) ?  -1 : rc);
   }


/********************************************************************
 *       NAME : Get_Next_Event(chanp,codep,datap)
 *DESCRIPTION : Get next event
 *      INPUT : none
 *     OUTPUT : Channel number of event. Event code. Event data.
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : THIS FUNCTION WILL NOT BE SUPPORTED IN LATER VERSIONS.
 *              USE THE getevt() FUNCTION.
 ********************************************************************/
int Get_Next_Event(chanp,codep,datap)
   unsigned int *chanp;
   unsigned int *codep;
   unsigned int *datap;
   {
      rc = calld40(F_GETEVT,ralp,rbxp,rcxp,rdxp);
      *chanp = *ralp;
      *codep = *rbxp;
      *datap = *rcxp;

      return (rc);
   }


/********************************************************************
 *       NAME : setdmask(chan,mask,method)
 *DESCRIPTION : Set Digit Control Mask
 *      INPUT : chan  = channel number.
 *              mask  = digit control mask.
 *                       0          = do not detect digits
 *                       1 (D_DTMF) = DTMF digit detection enabled
 *                       2 (D_LPD)  = pulse digit detection enabled
 *              method = method of operation.
 *                     = 0 - do not flush buffer.
 *                     = 1 - digit buffer is flushed.
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : none.
 ********************************************************************/
int setdmask(chan,mask,method)
   unsigned int chan;
   unsigned int mask;
   unsigned int method;
   {
      ral = (unsigned char)chan;
      rbx = mask;
      rcx = method;

      return (calld40(F_SETD,ralp,rbxp,rcxp,rdxp));
   }


/********************************************************************
 *       NAME : getver(hverp,lverp)
 *DESCRIPTION : Get DIALOG/4x device driver version number.
 *      INPUT : none.
 *     OUTPUT : *hverp = major version number.
 *              *lverp = minor version number.
 *    RETURNS : Return code.
 *      CALLS : calld40(), isdrvact()
 *   CAUTIONS : none.
 ********************************************************************/
int getver(hverp,lverp)
   unsigned int *hverp;
   unsigned int *lverp;
   {
      rbx = 0;
      rcx = 0;

      /* check for d40drv present */
      if (isdrvact((unsigned char)int_level)!=2)  {
         /* d40 driver has been loaded */
         ral = 0;
         rc = calld40(F_GETVER,ralp,rbxp,rcxp,rdxp);
         *hverp = rbx & 0xFF;
         *lverp = (rbx>>8) & 0xFF;
      }
      else  {
         /* no d40's present, get version from idds */
         ral = FI_GETREV;
         rc = calld40(F_DEVSRV,ralp,rbxp,rcxp,rdxp);
         *hverp = (rbx>>8) & 0xFF;
         *lverp = rbx & 0xFF;
      }

      return(rc);
   }


/********************************************************************
 *       NAME : sched()
 *DESCRIPTION : Give a time slice to the scheduler,
 *      INPUT : none.
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : none.
 ********************************************************************/
int sched()
   {
      ral = 0;
      rbx = 0;
      rcx = 0;

      rc = calld40(F_SCHED,ralp,rbxp,rcxp,rdxp);
      return(rc);
   }


/********************************************************************
 *       NAME : getcom(chan,ccbp)
 *DESCRIPTION : Get the Device Driver to application communication area.
 *      INPUT : Address of where to put the information.
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : none.
 ********************************************************************/
int getcom(chan,ccbp)
   unsigned int chan;
   DACCB *ccbp;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;

      return (calld40x(F_GETCOM,ralp,rbxp,rcxp,(unsigned char *) ccbp));
   }


/********************************************************************
 *       NAME : putcom(chan,ccbp)
 *DESCRIPTION : Put the Device Driver to application communication area.
 *      INPUT : chan = Channel number.
 *              ccbp  = Pointer to the application / Device Driver
                        communication area.
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : none.
 ********************************************************************/
int putcom(chan,ccbp)
   unsigned int chan;
   ADCCB *ccbp;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;

      return (calld40x(F_PUTCOM,ralp,rbxp,rcxp,(unsigned char *) ccbp));
   }


/********************************************************************
 *       NAME : playuser(chan,rwbp,errp)
 *DESCRIPTION : Play from the users buffer.
 *      INPUT : chan = channel number.
 *              rwbp = pointer to Read/Write Block (RWB)
 *              errp = pointer to error return code data.
 *     OUTPUT : *errp has the error return code
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : This is a multi-tasking function, and should only
 *              be called when no other multi-tasking function is
 *              in progress.
 ********************************************************************/
int playuser(chan,rwbp,errp)
   unsigned int chan;
   unsigned char *rwbp;
   unsigned int *errp;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;

      rc = calld40x(F_PLAYUB,ralp,rbxp,rcxp,(unsigned char *) rwbp);

      *errp = *rcxp;
      return(rc);
   }


/********************************************************************
 *       NAME : recuser(chan,rwbp,errp)
 *DESCRIPTION : Play from the users buffer.
 *      INPUT : chan = channel number.
 *              rwbp = pointer to RWB.
 *              errp = error return code data.
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : This is a multi-tasking function, and should only
 *              be called when no other multi-tasking function is
 *              in progress.
 ********************************************************************/
int recuser(chan,rwbp,errp)
   unsigned int chan;
   unsigned char *rwbp;
   unsigned int *errp;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;

      rc = calld40x(F_RECUB,ralp,rbxp,rcxp,(unsigned char *) rwbp);

      *errp = *rcxp;
      return(rc);
   }


/********************************************************************
 *       NAME : getcar(chan,carp)
 *DESCRIPTION : Get channel status
 *      INPUT : chan = channel number.
 *              carp = pointer to Call Analysis Results structure (CAR).
 *     OUTPUT : Call analysis results information is copied to the
 *              application CAR.
 *    RETURNS : Return code.
 *      CALLS : calld40x()
 *   CAUTIONS : none.
 ********************************************************************/
int getcar(chan,carp)
   unsigned int chan;
   CAR *carp;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;

      return (calld40x(F_GETCAR,ralp,rbxp,rcxp,(unsigned char *) carp));
   }


/********************************************************************
 *       NAME : startamx(hwintr,portp)
 *DESCRIPTION : This function causes the voice driver to verify that
 *              AMX8x(s) are in the system and to install an interrupt
 *              handler (if required).
 *      INPUT : hwintr = hardware interrupt level of the AMX8x.
 *                       (0 = do not install interrupt handler)
 *              portp = pointer to where to return number of ports
 *     OUTPUT : *nportp has the number of AMX8x ports available
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : This function should be called after performing a D/4x
 *              startsys() command.
 ********************************************************************/
int startamx(hwintr,portp)
   unsigned int hwintr;
   unsigned int *portp;
   {
      ral = (unsigned char)hwintr;
      rbx = 0;
      rcx = 0;

      rc = calld40(F_MSTART,ralp,rbxp,rcxp,rdxp);

      *portp = *ralp;

      /* set status flag if start amx was successful */
      if (!rc) {
         /* amx is active */
         _amxact = 1;
      }
      return (rc);
   }


/********************************************************************
 *       NAME : stopamx()
 *DESCRIPTION : Stop AMX8xs in system and remove interrupt handler, if any.
 *      INPUT : none.
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : This function should be called before performing a D/4x
 *              stopsys() command.
 ********************************************************************/
int stopamx()
   {
      ral = 0;
      rbx = 0;
      rcx = 0;

      rc = calld40(F_MSTOP,ralp,rbxp,rcxp,rdxp);

      /* clear status flag if stop amx was successful */
      if (!rc) {
         _amxact = 0;
      }
      return (rc);
   }


/********************************************************************
 *       NAME : sw_on(x,y)
 *DESCRIPTION : connect the indicated switch on the AMX8x.
 *      INPUT : x = switch coordinate (1-n)
 *              y = switch coordinate (1-n)
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : none.
 ********************************************************************/
int sw_on(x,y)
   unsigned int x;
   unsigned int y;
   {
      ral = 0;
      rbx = (x<<8)+y;
      rcx = 0;

      return (calld40(F_MMK,ralp,rbxp,rcxp,rdxp));
   }


/********************************************************************
 *       NAME : sw_off(x,y)
 *DESCRIPTION : disconnect the indicated switch on the AMX8x.
 *      INPUT : x = switch coordinate (1-n)
 *              y = switch coordinate (1-n)
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : none.
 ********************************************************************/
int sw_off(x,y)
   unsigned int x;
   unsigned int y;
   {
      ral = 0;
      rbx = (x<<8)+y;
      rcx = 0;

      return (calld40(F_MBRK,ralp,rbxp,rcxp,rdxp));
   }


/********************************************************************
 *       NAME : amx_off()
 *DESCRIPTION : This function causes all switch connections to be
 *              disconnected thereby breaking any any existing audio
 *              connections.
 *      INPUT : none.
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : none.
 ********************************************************************/
int amx_off()
   {
      ral = 0;
      rbx = 0;
      rcx = 0;

      return (calld40(F_MOFF,ralp,rbxp,rcxp,rdxp));
   }


/********************************************************************
 *       NAME : amx_msk(tel_id,mask)
 *DESCRIPTION : This function controls the capability of the specified
 *              telephone interface port to provide interrupt signalling
 *              and event generation.
 *      INPUT : tel_id = telephone interface port number. (0x8n)
 *              mask   = event enable bit mask.
 *                       0         = do not generate any events
 *                       1 (A_CON) = AMX8x connect (pickup).
 *                       2 (A_DIS) = AMX8x disconnect (hangup).
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : none.
 ********************************************************************/
int amx_msk(tel_id,mask)
   unsigned int tel_id;
   unsigned int mask;
   {
      ral = (unsigned char)tel_id;
      rbx = mask;
      rcx = 0;

      return (calld40(F_MMASK,ralp,rbxp,rcxp,rdxp));
   }


/********************************************************************
 *       NAME : ring(tel_id,rings)
 *DESCRIPTION : This function starts the the ring cycle for the
 *              indicated telephone interface port.
 *      INPUT : tel_id = telephone interface port number. (0x8n)
 *              rings  = maximum number of ring cycles.
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : This is a multi-tasking function, and should only
 *              be called when no other AMX multi-tasking function
 *              is in progress.
 ********************************************************************/
int ring(tel_id,rings)
   unsigned int tel_id;
   unsigned int rings;
   {
      ral = (unsigned char)tel_id;
      rbx = rings;
      rcx = 0;

      return (calld40(F_RSTART,ralp,rbxp,rcxp,rdxp));
   }


/********************************************************************
 *       NAME : set_ring(tel_id,bitcnt,intrvl,pattern)
 *DESCRIPTION : This function sets the ring pattern for the specified
 *              telephone interface port.
 *      INPUT : tel_id  = telephone interface port number (0x8n)
 *              bitcnt  = number of valid bits in the bit pattern
 *                        (counting from the least significant bit)
 *              intrvl  = bit duration interval in 1/18 seconds.
 *              pattern = ring cadence bit pattern (0=silence, 1=non-silence)
 *     OUTPUT : none.
 *    RETURNS : Return code.
 *      CALLS : calld40()
 *   CAUTIONS : none.
 ********************************************************************/
int set_ring(tel_id,bitcnt,intrvl,pattern)
   unsigned int bitcnt;
   unsigned int intrvl;
   unsigned int tel_id;
   unsigned int pattern;
   {
      ral = (unsigned char)tel_id;
      rbx = (bitcnt<<8)+intrvl;
      rcx = pattern;

      return (calld40(F_RSET,ralp,rbxp,rcxp,rdxp));
   }


/********************************************************************
 *       NAME : vhopen(filep,mode)
 *DESCRIPTION : open a file using the MSDOS file open function.
 *      INPUT : filep = pointer to name of file as a ASCIIZ string.
 *              mode = type of open - see #defines in vfcns.h.
 *     OUTPUT : none.
 *    RETURNS :  = dos file handle if successful.
 *               = 0, if unsuccessful, and DOS error is returned in
 *                 public variable _d40derr.
 *      CALLS : dskopen() dskcrea() dskseek()
 *   CAUTIONS : none.
 ********************************************************************/
int vhopen(filep,mode)
   unsigned char *filep;
   unsigned int mode;

   {
      unsigned int handle;
      unsigned int method;
      unsigned int p1,p2;

      switch (mode) {

      case READ:                      /* read mode */
	 /* Line below was modified to stop borland warning
	 if (_d40derr = dskopen(filep,&handle,0)) {
	 */
	 if ((_d40derr = dskopen(filep,&handle,0)) != 0) {
	    return(0);
	 }
	 return((int)handle);

      case RDWR:                      /* read write mode */
	 /* Line below was modified to stop borland warning
	 if (_d40derr = dskopen(filep,&handle,2)) {
	 */
	 if ((_d40derr = dskopen(filep,&handle,2)) != 0) {
            return(0);
         }
         return((int)handle);

      case CREATE:                    /* create mode (truncate) */
	 /* Line below was modified to stop borland warning
	 if (_d40derr = dskcrea(filep,&handle,0)) {
	 */
	 if ((_d40derr = dskcrea(filep,&handle,0)) != 0) {
	    return(0);
         }
         return((int)handle);

      case APPEND:                    /* append mode */
	 /* Line below was modified to stop borland warning
	 if (_d40derr = dskopen(filep,&handle,2)) {
	 */
	 if ((_d40derr = dskopen(filep,&handle,2)) != 0) {
	    return(0);
         }
         p1=0;
         p2=0;
         method = 2;
	 /* Line below was modified to stop borland warning
	 if (_d40derr = dskseek(handle,method,&p1,&p2)) {
	 */
	 if ((_d40derr = dskseek(handle,method,&p1,&p2)) != 0) {
            return(0);
         }
         return((int)handle);
      }
      return(0);
   }


/********************************************************************
 *       NAME : vhclose(handle)
 *DESCRIPTION : close a file using the MSDOS file close function.
 *      INPUT : handle = MSDOS file handle.
 *     OUTPUT : none.
 *    RETURNS : Error code if unsuccessful.
 *      CALLS : dskclose()
 *   CAUTIONS : none.
 ********************************************************************/
int vhclose(handle)
   unsigned int handle;
   {
      return((int)dskclose(handle));
   }


/********************************************************************
 *       NAME : vhseek(handle,relpos,method)
 *DESCRIPTION : move file pointer using the MSDOS file seek function.
 *      INPUT : handle = MSDOS file handle.
 *              relpos = relative file position.
 *              method = seek mode.
 *                     = 0 - position is relative to beginning of file.
 *                     = 1 - position is relative to current file position.
 *                     = 2 - position is relative to end of file.
 *     OUTPUT : none.
 *    RETURNS :  = actual file position if successful.
 *               = -1L if unsuccessful (see _d40derr for error code).
 *      CALLS : dskseek()
 *   CAUTIONS : none.
 ********************************************************************/
long int vhseek(handle,relpos,method)
   unsigned int handle;
   unsigned long int relpos;
   unsigned int method;
   {
      *rcxp = (int)((relpos>>16)&0xFFFF);
      *rdxp = (int)(relpos&0xFFFF);

      /* Line below was modified to stop borland warning
      if ((rc = dskseek(handle,method,rcxp,rdxp))) {
      */
      if ((rc = dskseek(handle,method,rcxp,rdxp)) != 0) {
         return((long int)-1);
      }
      return((long int)((long int)*rdxp<<16) + (long int)(*rcxp));
   }


/********************************************************************
 *        NAME : char *d4xerr(errcode)
 * DESCRIPTION : display error message that corresponds to error code
 *       INPUT : errcode = error code of last function
 *      OUTPUT : none.
 *     RETURNS : pointer to the error message
 *    CAUTIONS : none.
 ********************************************************************/
char *d4xerr(errcode)
   unsigned int errcode;
   {
      switch (errcode) {

      case E_FAILST:
         return("Board failed self test");
      case E_NODT:
         return("DTMF buffer empty");
      case E_SACT:
         return("System already active");
      case E_SNACT:
         return("System not active");
      case E_BADDL:
         return("D4x hardware error");
      case E_BADFCN:
         return("Invalid function code requested");
      case E_BADINT:
         return("Interrupt level not available");
      case E_BADDCB:
         return("DCB parameter error");
      case E_BADCH:
         return("Invalid channel number");
      case E_MTACT:
         return("Multitasking function already active");
      case E_MTNACT:
         return("Multitasking function not active");
      case E_BADPAR:
         return("Bad parameter");
      case E_BADVER:
         return("Incorrect version of firmware code");
      case E_NOTIMP:
         return("Function not implemented/available");
      case E_NOTERM:
         return("Terminating condition not specified");
      case E_NOMEM:
         return("Insufficent buffer mem available");
      case E_DOSERR:
         return("DOS error (DOS error returned in AL register)");
      case E_DSKCNT:
         return("Error in bytes read/written to disk");
      case E_NOAMX:
         return("No AMX8x boards present");
      case E_AMXON:
         return("AMX8x already started");
      case E_AMXOFF:
         return("AMX8x already stopped");
      case E_BADXY:
         return("Invalid X,Y coordinate");
      case E_BADPRT:
         return("Bad AMX8x tel. station ID specified");
      case E_BADCUR:
         return("Bad cursor position specified");
      case E_EMSSW:
         return("EMM not installed or corrupted");
      case E_EMSERR:
         return("EMM reported error");
      case E_NOVBUF:
         return("No buffer allocated for EMS ram disk");
      case E_TSBADSLOT:
         return("Bad timeslot number");
      case E_TIMEOUT:
         return("Timeout");
      case E_BADPROD:
         return("Not supported by product");
      case E_TONEID:
         return("Bad tone template ID");
      case E_TNPARM:
         return("Invalid parameter in tone template");
      case E_MAXTMPLT:
         return("Maximum number of templates already defined");
      case E_MAXSVCB:
         return("invalid number of SVCB blocks");
      case E_SVMTTYPE:
         return("invalid table type specified");
      case E_SVMTRANGE:
         return("entry in SVMT was out of range");
      case E_BADSVCB:
         return("invalid sv condition block(SVCB)");
      case E_BADADJSIZ:
         return("invalid sv adjustment size");

      case EI_REGERR:
         return("Driver registration error");
      case EI_BADHND:
         return("Bad handle");
      case EI_BADDEV:
         return("Bad device type specified");
      case EI_MAXQS:
         return("Maximum number of queues");
      case EI_BADQT:
         return("Queue type error");
      case EI_QFULL:
         return("Queue full");
      case EI_QMPTY:
         return("Queue empty");
      case EI_BADFCN:
         return("Bad function request");
      case EI_BADPAR:
         return("Bad parameter");
      case EI_VECUSD:
         return("Vector already in use");
      case EI_NOIMP:
         return("Function not implemented");

      }
      return("");
   }


/********************************************************************
 *        NAME : getvctr()
 * DESCRIPTION : The purpose of his routine is to get the D/4x driver
 *               software interrupt vector
 *       INPUT : none.
 *      OUTPUT : none.
 *     RETURNS : 0 if d40drv not present, else d40drv s/w int vector
 *       CALLS : isdrvact()
 *    CAUTIONS : none.
 ********************************************************************/
unsigned int getvctr()
   {
      unsigned char vector;
      int type;

      /* first check the default interrupt vector */
      if (isdrvact((unsigned char)int_level)!=0)
         return((unsigned int)int_level);

      /* search for voice driver software interrupt vector */
      /* Major problem on my machine. Caused EMM386 protection error
      /* Changed ending vector and worked fine
      /* for (vector=0x40; vector<=0xfe; vector++)  {
	 */
	 for (vector=0x40; vector<=0xfa; vector++)  {
	 type = isdrvact(vector);
	 if (type!=0)
	    break;
      }
      /* if no vector's found, return 0 */
      if (type==0)
         return(0);

      /* else save D40DRV's software interrupt vector */
      int_level = (unsigned int)vector;

      return((unsigned int)vector);
   }


/********************************************************************
 *        NAME : gtevtblk(evntp)
 * DESCRIPTION : The purpose of this function is to return an event
 *               block off the event queue (if one exists).
 *       INPUT : evntp = pointer users event block structure
 *      OUTPUT : *evntp gets copy of the event block
 *     RETURNS : -1 if an event available
 *                0 if no event available
 *               >0 if error (value is return code)
 *       CALLS : sched(), calld40x()
 *    CAUTIONS : SYSTEMS USING BOARDS OTHER THAN D/4x AND AMX (such as
 *               DTI, VR/10, etc.) MUST USE THIS FUNCTION INSTEAD OF
 *               getevt() IN ORDER TO IDENTIFY THE SOURCE OF THE EVENT.
 ********************************************************************/
int gtevtblk(evntp)
   EVTBLK *evntp;
   {
      int rc;

      /* call the scheduler */
      sched();

      ral = FI_GETQ;
      rbx = Q_STDEVT;
      rcx = 0;
      if ((rc=calld40x(F_DEVSRV,ralp,rbxp,rcxp,(unsigned char *)evntp)) == EI_QMPTY) {
         return(0);
      }
      return((rc) ? rc:-1);
   }


/********************************************************************
 *        NAME : putevt(chan,code,data)
 * DESCRIPTION : put an application event onto the system's event queue
 *       INPUT : chan = channel number
 *               code = event code
 *               data = event data
 *      OUTPUT : none.
 *     RETURNS : 0 if the event was placed onto the event queue
 *       CALLS : calld40x()
 *    CAUTIONS : Using event codes that are similar to D4x (or other
 *               Dialogic) events may confuse the application.
 *               Event codes can only be read using gtevtblk(),
 ********************************************************************/
int putevt(chan,code,data)
   unsigned int chan;
   unsigned int code;
   unsigned int data;
   {
      EVTBLK applevt;

      ral = FI_USREVT;
      rbx = 0;
      rcx = 0;

      /* build the event block data structure */
      applevt.devtype = DV_USER1;
      applevt.devchan = chan;
      applevt.evtcode = code;
      applevt.evtdata = data;
 
      return(calld40x(F_DEVSRV,ralp,rbxp,rcxp,(unsigned char *)&applevt));
   }


/********************************************************************
 *        NAME : setiparm(chan,parm,data)
 * DESCRIPTION : set individual channel parameters
 *       INPUT : chan = channel number
 *               parm = parameter id
 *               data = parameter value
 *      OUTPUT : none.
 *     RETURNS : Return code.
 *       CALLS : calld40()
 *    CAUTIONS : This is a multi-tasking function, and should only
 *               be called when no other multi-tasking function is
 *               in progress.
 ********************************************************************/
int setiparm(chan,parm,data)
   unsigned int chan;
   unsigned int parm;
   unsigned int data;
   {
      ral = (unsigned char)chan;
      rbx = parm;
      rcx = data;

      return(calld40(F_STPARM,ralp,rbxp,rcxp,rdxp));
   }


/********************************************************************
 *        NAME : wink(chan)
 * DESCRIPTION : set wink parameter for a specific channel
 *       INPUT : chan = channel number
 *      OUTPUT : none.
 *     RETURNS : Return code.
 *       CALLS : calld40()
 *    CAUTIONS : This is a multi-tasking function, and should only
 *               be called when no other multi-tasking function is
 *               in progress.
 ********************************************************************/
int wink(chan)
   unsigned int chan;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;
      rdx = 0;

      return(calld40(F_WINK,ralp,rbxp,rcxp,rdxp));
   }


/********************************************************************
 *        NAME : sb_route(chan,tslot)
 * DESCRIPTION : Perform timeslot assignment for a specific channel
 *       INPUT : chan  = channel number
 *               tslot = timeslot number
 *      OUTPUT : none.
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : calld40()
 *    CAUTIONS : channel must be idle.
 *
 *
 ********************************************************************/
int sb_route(chan,tslot)
   unsigned int chan;
   int tslot;
   {
      ral = (unsigned char)chan;
      rbx = tslot;
      rcx = tslot;
      rdx = 0;
      return(calld40(F_ROUTETS,ralp,rbxp,rcxp,rdxp));
   }



/********************************************************************
 *        NAME : sb_rtrcvxmt(chan,rx_tslot,tx_tslot)
 * DESCRIPTION : Perform independant timeslot assignment to a channel
 *       INPUT : chan  = channel number
 *               rx_tslot = receive timeslot number
 *               tx_tslot = transmit timeslot number
 *      OUTPUT : none.
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : calld40()
 *    CAUTIONS : channel must be idle.
 *
 *
 ********************************************************************/
int sb_rtrcvxmt(chan,rx_tslot,tx_tslot)
   unsigned int chan;
   int rx_tslot;                           
   int tx_tslot;
   {
      ral = (unsigned char)chan;
      rbx = rx_tslot;
      rcx = tx_tslot;
      rdx = 0;
      return(calld40(F_ROUTETS,ralp,rbxp,rcxp,rdxp));
   }

/********************************************************************
 *        NAME : dl_addtone(chan,digit,digtype)
 * DESCRIPTION : Bind temporary tone to channel
 *       INPUT : chan  = channel number
 *               digit = optional digit to associate with tone
 *               digtype = digit type(5-9)
 *      OUTPUT : none.
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : calld40()
 *    CAUTIONS : channel must be idle.
 *
 ********************************************************************/
int dl_addtone(chan,digit,digtype)
   unsigned int chan;
   unsigned char digit;
   unsigned char digtype;
   {
      ral = (unsigned char)chan;
      rbx = digit;
      rcx = digtype;
      rdx = 0;
      return(calld40(F_ADDTONE,ralp,rbxp,rcxp,rdxp));
   }


/********************************************************************
 *        NAME : dl_deltones(chan)
 * DESCRIPTION : Delete all added tones from channel                
 *       INPUT : chan  = channel number
 *      OUTPUT : none.
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : calld40()
 *    CAUTIONS : channel must be idle.
 *
 ********************************************************************/
int dl_deltones(chan)
   unsigned int chan;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;
      rdx = 0;
      return(calld40(F_DELTONES,ralp,rbxp,rcxp,rdxp));
   }

/********************************************************************
 *        NAME : dl_enbtone(chan,tid,evt_mask)
 * DESCRIPTION : Enable detection of tone on channel
 *       INPUT : chan   = channel number
 *               tid    = tone template ID
 *               evt_mask = event mask
 *                          TONE_ON  - enable tone on detection
 *                          TONE_OFF - enable tone off detection
 *                          TONE_ALL - enable ALL tone detection
 *                                     on the channel
 *      OUTPUT : none.
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : calld40()
 *    CAUTIONS : channel must be idle.
 *
 ********************************************************************/
int dl_enbtone(chan,tid,evt_mask)
   unsigned int chan;
   unsigned int tid;
   unsigned int evt_mask;
   {
      ral = (unsigned char)chan;
      rbx = tid;
      rcx = evt_mask;
      rdx = 1;
      return(calld40(F_TNSTAT,ralp,rbxp,rcxp,rdxp));
   }


/********************************************************************
 *        NAME : dl_distone(chan,tid,evt_mask)
 * DESCRIPTION : Disable detection of tone on channel
 *       INPUT : chan   = channel number
 *               tid    = tone template ID
 *               evt_mask = event mask
 *                          TONE_ON  - disable tone on detection
 *                          TONE_OFF - disable tone off detection
 *                          TONE_ALL - disable ALL tone detection
 *                                     on the channel
 *      OUTPUT : none.
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : calld40()
 *    CAUTIONS : channel must be idle.
 *
 ********************************************************************/
int dl_distone(chan,tid,evt_mask)
   unsigned int chan;
   unsigned int tid;
   unsigned int evt_mask;
   {
      ral = (unsigned char)chan;
      rbx = tid;
      rcx = evt_mask;
      rdx = 0;
      return(calld40(F_TNSTAT,ralp,rbxp,rcxp,rdxp));
   }

/********************************************************************
 *        NAME : dl_blddt(tid,freq1,fq1dev,freq2,fq2dev,mode)
 * DESCRIPTION : Build temporary tone(dual tone)
 *       INPUT : tid    = tone ID to assign
 *               freq1  = frequency 1 in Hz
 *               fq1dev = frequency 1 deviation in Hz
 *               freq2  = frequency 2 in Hz
 *               fq2dev = frequency 2 deviation in Hz
 *               mode   = leading or trailing edge
 *      OUTPUT : none.
 *     RETURNS : Return code.
 *    CAUTIONS : none.
 *
 ********************************************************************/
int dl_blddt(tid,freq1,fq1dev,freq2,fq2dev,mode)
  unsigned int tid;
  unsigned int freq1;
  unsigned int fq1dev;
  unsigned int freq2;
  unsigned int fq2dev;
  unsigned int mode;
   {
      return(buildtone(1,tid,freq1,fq1dev,freq2,fq2dev,mode,0,0,0,0,0));
   }


/********************************************************************
 *        NAME : dl_blddtcad(tid,freq1,fq1dev,freq2,fq2dev,ontime,
 *                           ontdev,offtime,offtdev,repcnt)
 * DESCRIPTION : Build temporary tone(dual cadence tone)
 *       INPUT : tid     = tone ID to assign
 *               freq1   = frequency 1 in Hz
 *               fq1dev  = frequency 1 deviation in Hz
 *               freq2   = frequency 2 in Hz
 *               fq2dev  = frequency 2 deviation in Hz
 *               ontime  = tone on time in 10ms
 *               ontdev  = on time deviation in 10ms
 *               offtime = tone off time in 10ms
 *               offtdev = off time deviation in 10ms
 *               repcnt  = repetition count
 *      OUTPUT : none.
 *     RETURNS : Return code.
 *    CAUTIONS : none.
 *
 ********************************************************************/
int dl_blddtcad(tid,freq1,fq1dev,freq2,fq2dev,ontime,ontdev,offtime,offtdev,repcnt)
  unsigned int tid;
  unsigned int freq1;
  unsigned int fq1dev;
  unsigned int freq2;
  unsigned int fq2dev;
  unsigned int ontime;
  unsigned int ontdev;
  unsigned int offtime;
  unsigned int offtdev;
  unsigned int repcnt;
   {
      return(buildtone(1,tid,freq1,fq1dev,freq2,fq2dev,0,ontime,ontdev,offtime,offtdev,repcnt));
   }


/********************************************************************
 *        NAME : dl_bldst(tid,freq,fqdev,mode)
 * DESCRIPTION : Build temporary tone(single tone)
 *       INPUT : tid    = tone ID to assign
 *               freq   = frequency in Hz
 *               fqdev  = frequency deviation in Hz
 *               mode   = leading or trailing edge
 *      OUTPUT : none.
 *     RETURNS : Return code.
 *    CAUTIONS : none.
 *
 ********************************************************************/
int dl_bldst(tid,freq,fqdev,mode)
  unsigned int tid;
  unsigned int freq;
  unsigned int fqdev;
  unsigned int mode;
   {
      return(buildtone(0,tid,freq,fqdev,0,0,mode,0,0,0,0,0));
   }


/********************************************************************
 *        NAME : dl_bldstcad(tid,freq,fqdev,ontime,ontdev,offtime,offtdev,repcnt)
 * DESCRIPTION : Build temporary tone(single cadence tone)
 *       INPUT : tid     = tone ID to assign
 *               freq    = frequency in Hz
 *               fqdev   = frequency deviation in Hz
 *               ontime  = tone on time in 10ms
 *               ontdev  = on time deviation in 10ms
 *               offtime = tone off time in 10ms
 *               offtdev = off time deviation in 10ms
 *               repcnt  = repetition count
 *      OUTPUT : none.
 *     RETURNS : Return code.
 *    CAUTIONS : none.
 *
 ********************************************************************/
int dl_bldstcad(tid,freq,fqdev,ontime,ontdev,offtime,offtdev,repcnt)
  unsigned int tid;
  unsigned int freq;
  unsigned int fqdev;
  unsigned int ontime;
  unsigned int ontdev;
  unsigned int offtime;
  unsigned int offtdev;
  unsigned int repcnt;
   {
      return(buildtone(0,tid,freq,fqdev,0,0,0,ontime,ontdev,offtime,offtdev,repcnt));
   }


/********************************************************************
 *        NAME : buildtone(p0,p1,p2,p3,p4,p5,p6,p7,p8,p9,p10,p11)
 * DESCRIPTION : Build INTERNAL tone structure for driver
 *    CAUTIONS : Application should never call this function directly.
 *
 ********************************************************************/
int buildtone(p0,p1,p2,p3,p4,p5,p6,p7,p8,p9,p10,p11)
  unsigned int p0,p1,p2,p3,p4,p5,p6,p7,p8,p9,p10,p11;
   {
     unsigned int temp[18];

     /* first zero out temp */
     memset(temp,0,sizeof(temp));

     if (gtdampl_flag) {
        p0 += 0x100;
     }

     temp[0] = p0;
     temp[1] = p2 - p3;
     temp[2] = p2 + p3;
     temp[3] = p4 - p5;
     temp[4] = p4 + p5;
     temp[5] = 6;
     temp[6] = 6;
     temp[7] = p1;
     temp[14] = (unsigned int)gtdampl1_min;
     temp[15] = (unsigned int)gtdampl1_max;
     temp[16] = (unsigned int)gtdampl2_min;
     temp[17] = (unsigned int)gtdampl2_max;
     switch (p6) {
        case TN_LEADING:
           break;
        case TN_TRAILING:
           temp[10] = 0;
           temp[11] = 0xffff;
           break;
        default:
           temp[9] = p11;
           temp[10] = p7 - p8;
           temp[11] = p7 + p8;
           temp[12] = p9 - p10;
           temp[13] = p9 + p10;
           break;
     }
     ral = (unsigned char)0;
     rbx = 0;
     rcx = 0;
     return(calld40x(F_BUILDTONE,ralp,rbxp,rcxp,(unsigned char *)temp));
   }

/********************************************************************
 *        NAME : dl_setgtdamp(ampl1,ampl2)
 * DESCRIPTION : Sets the amplitude of GTD to be used.
 *       INPUT : ampl1_min = min. amplitude of first tone
 *               ampl1_max = max. amplitude of first tone
 *               ampl2_min = min. amplitude of second tone
 *               ampl2_max = max. amplitude of second tone
 *      OUTPUT : none.
 *     RETURNS : none.
 *       CALLS : none
 *    CAUTIONS : This function must called prior to dl_bld functions.
 *
 ********************************************************************/
void  dl_setgtdamp(ampl1_min,ampl1_max,ampl2_min,ampl2_max)
    int ampl1_min;
    int ampl1_max;
    int ampl2_min;
    int ampl2_max;
   {
      gtdampl1_min = ampl1_min;
      gtdampl1_max = ampl1_max;
      gtdampl2_min = ampl2_min;
      gtdampl2_max = ampl2_max;
      gtdampl_flag = 1;
      return;
   }

/********************************************************************
 *        NAME : dl_playtone(chan,rwbp)
 * DESCRIPTION : General tone generation
 *       INPUT : chan  = channel number
 *               rwbp  = pointer to RWB
 *      OUTPUT : none.
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : calld40x()
 *    CAUTIONS : channel must be idle.
 *
 ********************************************************************/
int dl_playtone(chan,rwbp)
   unsigned int chan;
   RWB        * rwbp;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;
      return(calld40x(F_PLAYGTG,ralp,rbxp,rcxp,(unsigned char *)rwbp));
   }


/********************************************************************
 *        NAME : dl_bldtngen(tngenp,freq1,freq2,ampl1,ampl2,duration)
 * DESCRIPTION : Build tone generation template
 *       INPUT : tngenp = pointer to user's TN_GEN
 *               freq1  = frequency of 1st tone (Hz)
 *               freq2  = frequency of 2nd tone (Hz)
 *                        (set to 0 for single tone)
 *               ampl1  = amplitude for 1st tone (dB)
 *               ampl2  = amplitude for 2nd tone (dB)
 *               duration = duration of tone (10ms)
 *              
 *      OUTPUT : none.
 *     RETURNS : none.                                
 *    CAUTIONS : none.
 *
 ********************************************************************/
void dl_bldtngen(tngenp,freq1,freq2,ampl1,ampl2,duration)
  TN_GEN * tngenp;
  unsigned int freq1;
  unsigned int freq2;
  int ampl1;
  int ampl2;
  int duration;

  {
      tngenp->tg_dflag = (freq2 == 0) ? TN_SINGLE : TN_DUAL;
      tngenp->tg_freq1 = freq1;
      tngenp->tg_freq2 = freq2;
      tngenp->tg_ampl1 = ampl1;
      tngenp->tg_ampl2 = ampl2;
      tngenp->tg_dur = duration;

      return;
  }


/********************************************************************
 *        NAME : dl_adjsv(chan,tabletype,action,adjsize)
 * DESCRIPTION : Modifies speed or volume of playback
 *       INPUT : chan      = channel number
 *               tabletype = speed or volume
 *                            SV_SPEEDTBL
 *                            SV_VOLUMETBL
 *               action    = how to modify
 *                            SV_ABSPOS
 *                            SV_RELCURPOS
 *                            SV_TOGGLE
 *               adjsize   = adjustment size
 *      OUTPUT : none.
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : calld40()
 *    CAUTIONS : none.
 *
 ********************************************************************/
int dl_adjsv(chan,tabletype,action,adjsize)
   unsigned int chan;
   unsigned int tabletype;
   unsigned int action;
   int          adjsize;
   {
      ral = (unsigned char)chan;
      rbx = tabletype | action;
      rcx = adjsize;
      rdx = 0;
      return(calld40(F_ADJSV,ralp,rbxp,rcxp,rdxp));
   }


/********************************************************************
 *        NAME : dl_setsvcond(chan,numblks,svcbp)
 * DESCRIPTION : Sets speed/volume adjustment conditions.
 *       INPUT : chan    = channel number
 *               numblks = number of conditions (max 20)
 *               svcbp   = pointer to array of SVCBs
 *      OUTPUT : none.
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : calld40x()
 *    CAUTIONS : channel must be idle.
 *
 ********************************************************************/
int dl_setsvcond(chan,numblks,svcbp)
   unsigned int chan;
   unsigned int numblks;
   SVCB         *svcbp;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = numblks;
      return(calld40x(F_SETSVCOND,ralp,rbxp,rcxp,(unsigned char *)svcbp));
   }


/********************************************************************
 *        NAME : dl_clrsvcond(chan)
 * DESCRIPTION : Clears any previous adjustment conditions(SVCBs).
 *       INPUT : chan = channel number
 *      OUTPUT : none.
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : calld40()
 *    CAUTIONS : channel must be idle.
 *
 ********************************************************************/
int dl_clrsvcond(chan)
   unsigned int chan;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;
      rdx = 0;
      return(calld40(F_CLRSVCOND,ralp,rbxp,rcxp,rdxp));
   }



/********************************************************************
 *        NAME : dl_getcursv(chan,curvolp,curspeedp)
 * DESCRIPTION : Returns current volume and speed values from SVMT.
 *       INPUT : chan    = channel number
 *               curvolp = current volume return buffer
 *               curspeedp = current speed return buffer
 *      OUTPUT : Returns speed and volume
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : calld40x()
 *    CAUTIONS : channel must be idle.
 *
 ********************************************************************/
int dl_getcursv(chan,curvolp,curspeedp)
   unsigned int chan;
   int          *curvolp;
   int          *curspeedp;
   {
      ral = (unsigned char)chan;
      rbx = d4getoff((char *)curvolp);
      rcx = d4getseg((char *)curvolp);
      return(calld40x(F_GETCURSV,ralp,rbxp,rcxp,(unsigned char *)curspeedp));
   }


/********************************************************************
 *        NAME : dl_setsvmt(chan,tabletype,svmtp,flag)
 * DESCRIPTION : Modifies either the speed or volume modification.
 *               table(SVMT)
 *       INPUT : chan  = channel number
 *               tabletype = speed or volume:
 *                            SV_SPEEDTBL
 *                            SV_VOLUMETBL
 *               svmtp = pointer to user's SVMT structure
 *               flag  = 
 *                         SV_WRAPMOD    - circular table
 *                         SV_SETDEFAULT - reset to default
 *                         0             - normal
 *      OUTPUT : none.
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : calld40x()
 *    CAUTIONS : channel must be idle.
 *
 ********************************************************************/
int dl_setsvmt(chan,tabletype,svmtp,flag)
   unsigned int chan;
   unsigned int tabletype;
   SVMT         *svmtp;
   unsigned int flag;
   {
      ral = (unsigned char)chan;
      rbx = tabletype | flag;
      rcx = 0;
      return(calld40x(F_SETSVMT,ralp,rbxp,rcxp,(unsigned char *)svmtp));
   }


/********************************************************************
 *        NAME : dl_getsvmt(chan,tabletype,svmtp)
 * DESCRIPTION : Returns Speed/Volume Modification Table(SVMT).
 *       INPUT : chan = channel number
 *               tabletype = speed or volume:
 *                            SV_SPEEDTBL
 *                            SV_VOLUMETBL
 *               svmtp = return buffer
 *      OUTPUT : none.
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : calld40x()
 *    CAUTIONS : none.
 *
 ********************************************************************/
int dl_getsvmt(chan,tabletype,svmtp)
   unsigned int chan;
   unsigned int tabletype;
   SVMT         *svmtp;
   {
      ral = (unsigned char)chan;
      rbx = tabletype;
      rcx = 0;
      return(calld40x(F_GETSVMT,ralp,rbxp,rcxp,(unsigned char *)svmtp));
   }


/********************************************************************
 *        NAME : dl_addspddig(chan,digit,adjval)
 * DESCRIPTION : Sets a dtmf condition to change speed of playback
 *       INPUT : chan = channel number
 *               digit = DTMF digit(ASCII)
 *               adjval = adjustment value(#define see d40.h)
 *      OUTPUT : none.
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : dl_setsvcond
 *    CAUTIONS : channel must be idle.
 *               Assumes default Speed Modification Table.
 *
 ********************************************************************/
int dl_addspddig(chan,digit,adjval)
   unsigned int  chan;
   unsigned char digit;
   unsigned int  adjval;
   {
   SVCB  svcb;
      svcb.type = SV_SPEEDTBL | SV_RELCURPOS;
      svcb.adjsize = adjval;
      svcb.digit = digit;
      svcb.digtype = 0;
      if (adjval == SV_NORMAL) {
        svcb.type |= SV_BEGINPLAY;
        svcb.adjsize = 0;
      }
      return(dl_setsvcond(chan,1,&svcb));
   }


/********************************************************************
 *        NAME : dl_addvoldig(chan,digit,adjval)
 * DESCRIPTION : Sets a dtmf condition to change volume of playback 
 *       INPUT : chan  = channel number
 *               digit = DTMF digit(ASCII)
 *               adjval = adjustment value(#define see d40.h)
 *      OUTPUT : none.
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : dl_setsvcond
 *    CAUTIONS : channel must be idle.
 *               Assumes default Volume Modification Table.
 *
 ********************************************************************/
int dl_addvoldig(chan,digit,adjval)
   unsigned int  chan;
   unsigned char digit;
   unsigned int  adjval;
   {
   SVCB  svcb;
      svcb.type = SV_VOLUMETBL | SV_RELCURPOS;
      svcb.adjsize = adjval;
      svcb.digit = digit;
      svcb.digtype = 0;
      if (adjval == SV_NORMAL) {
        svcb.type |= SV_BEGINPLAY;
        svcb.adjsize = 0;
      }
      return(dl_setsvcond(chan,1,&svcb));
   }

/********************************************************************
 *        NAME : dl_gtsernum(chan,sernump)
 * DESCRIPTION : Get the serial of the board associated with this
 *               channel
 *       INPUT : chan    = channel number
 *               sernump = pointer to user's serial number buffer
 *      OUTPUT : Serial number copied to user buffer.
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : calld40x()
 *    CAUTIONS : This only works on a SpringBoard or D/4xD board
 ********************************************************************/
int dl_gtsernum(chan,sernump)
   unsigned int chan;
   char * sernump;
   {
      ral = (unsigned char)chan;
      rbx = 0;
      rcx = 0;
      return(calld40x(F_SERNUM,ralp,rbxp,rcxp,(unsigned char *)sernump));
   }


/********************************************************************
 *        NAME : dl_initcallp(chan)
 * DESCRIPTION : Initialize call analysis
 *       INPUT : chan  = channel number
 *      OUTPUT : none.
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : calld40()
 *    CAUTIONS : channel must be idle.
 *
 ********************************************************************/
int dl_initcallp(chan)
   unsigned int chan;
   {
      int i;
      int class;

      for (i=TID_FIRST; i<=TID_LAST; i++) {
         switch(i) {
         case TID_DIAL_LCL:
         case TID_DIAL_INTL:
         case TID_DIAL_XTRA:
            class = 0;
            break;
         case TID_RNGBK1:
         case TID_RNGBK2:
            class = TNTYP_RNGBACK;
            break;
         case TID_FAX1:
         case TID_FAX2:
            class = TNTYP_FAX;
            break;
         case TID_BUSY1:
         case TID_BUSY2:
            class = TNTYP_BUSY;
            break;

         default:
            class = -1;
            break;
         }

         if (class == -1) {
            continue;
         }

         ral = (unsigned char)chan;
         rbx = 0;
         rcx = class;
         rdx = i;
	 /* Line Below was modified to stop borland warning
	 if (rc = (calld40(F_ADDTONE,ralp,rbxp,rcxp,rdxp))) {
	 */
	 if ((rc = (calld40(F_ADDTONE,ralp,rbxp,rcxp,rdxp))) != 0) {
            return(rc);
         }

      }
      return(0);
   }

             
/********************************************************************
 *        NAME : dl_chgfreq(tonetype,freq1,freq1dev,freq2,freq2dev)
 * DESCRIPTION : Modify call analysis tone(frequency)
 *       INPUT : tonetype = tone id
 *               freq1    = frequency 1 in Hz
 *               freq1dev = frequency 1 deviation in Hz
 *               freq2    = frequency 2 in Hz
 *               freq2dev = frequency 2 deviation in Hz
 *      OUTPUT : none.
 *     RETURNS : return code.
 *       CALLS : calld40x()
 *    CAUTIONS : none.
 *
 ********************************************************************/
int dl_chgfreq(tonetype,freq1,freq1dev,freq2,freq2dev)
   int tonetype;
   int freq1;
   int freq1dev;
   int freq2;
   int freq2dev;

   {
   int buf[12];

      ral = (unsigned char)0;
      rbx = 0;
      rcx = 0;
      buf[0] = tonetype;
      buf[1] = 0;
      buf[2] = freq2 ? 1 : 0;
      buf[3] = 2;
      buf[4] = freq1 - freq1dev;
      buf[5] = 4;
      buf[6] = freq1 + freq1dev;
      buf[7] = 6;
      buf[8] = freq2 - freq2dev;
      buf[9] = 8;
      buf[10] = freq2 + freq2dev;
      buf[11] = -1;

      return(calld40x(F_CHGCALLP,ralp,rbxp,rcxp,(unsigned char *)buf));
   }

/********************************************************************
 *        NAME : dl_chgdur(tonetype,ontime,ontimedev,offtime,offtimedev)
 * DESCRIPTION : Modify call analysis tone(duration)
 *       INPUT : tonetype   = tone id
 *               ontime     = on time
 *               ontimedev  = on time deviation
 *               offtime    = off time
 *               offtimedev = off time deviation
 *      OUTPUT : none.
 *     RETURNS : return code.
 *       CALLS : calld40x()
 *    CAUTIONS : none.
 *
 ********************************************************************/
int dl_chgdur(tonetype,ontime,ontimedev,offtime,offtimedev)
   int tonetype;
   int ontime;
   int ontimedev;
   int offtime;
   int offtimedev;
   {
   int buf[10];

      ral = (unsigned char)0;
      rbx = 0;
      rcx = 0;
      buf[0] = tonetype;
      buf[1] = 20;
      buf[2] = ontime - ontimedev;
      buf[3] = 22;
      buf[4] = ontime + ontimedev;
      buf[5] = 24;
      buf[6] = offtime - offtimedev;
      buf[7] = 26;
      buf[8] = offtime + offtimedev;
      buf[9] = -1;

      return(calld40x(F_CHGCALLP,ralp,rbxp,rcxp,(unsigned char *)buf));
   }


/********************************************************************
 *        NAME : dl_chgrepcnt(tonetype,repcnt)
 * DESCRIPTION : Modify call analysis tone(cadence count)
 *       INPUT : tonetype = tone id
 *               repcnt   = repetition count
 *      OUTPUT : none.
 *     RETURNS : return code.
 *       CALLS : calld40x()
 *    CAUTIONS : none.
 *
 ********************************************************************/
int dl_chgrepcnt(tonetype,repcnt)
   int tonetype;
   int repcnt;
   {
   int buf[4];

      ral = (unsigned char)0;
      rbx = 0;
      rcx = 0;
      buf[0] = tonetype;
      buf[1] = 18;
      buf[2] = repcnt;
      buf[3] = -1;

      return(calld40x(F_CHGCALLP,ralp,rbxp,rcxp,(unsigned char *)buf));
   }


/********************************************************************
 *        NAME : dl_chgqualid(tonetype)
 * DESCRIPTION : 
 *       INPUT : tonetype = tone id
 *      OUTPUT : none.
 *     RETURNS : return code.
 *       CALLS : calld40x()
 *    CAUTIONS : none.
 *
 ********************************************************************/
int dl_chgqualid(tonetype)
   int tonetype;
   {
   int buf[6];

      ral = (unsigned char)0;
      rbx = 0;
      rcx = 0;
      buf[0] = tonetype;
      buf[1] = 10;
      buf[2] = tn_qualid;
      buf[3] = 12;
      buf[4] = tn_qualid;
      buf[5] = -1;

      return(calld40x(F_CHGCALLP,ralp,rbxp,rcxp,(unsigned char *)buf));
   }

/********************************************************************
 *        NAME : dl_gettnfo(handle,tnptr)
 * DESCRIPTION : read tone information
 *       INPUT : handle = tone info handle
 *               tnptr  = return buffer for tone info
 *      OUTPUT : tone info is copied into return buffer
 *     RETURNS : 0 if success, otherwise failure code.
 *       CALLS : calld40x()
 *    CAUTIONS : For INTERNAL use only
 ********************************************************************/
int dl_gettninfo(handle,tnptr)
   int handle;
   TN_INFO  * tnptr;
   {
      ral = 0;
      rbx = handle;
      rcx = 0;
      return(calld40x(F_TNINFO,ralp,rbxp,rcxp,(unsigned char *)tnptr));
   }

/********************************************************************
 *        NAME : dl_flushtn()
 * DESCRIPTION : flush tone info buffer
 *       INPUT : none                  
 *      OUTPUT : none
 *     RETURNS : void
 *       CALLS : calld40()
 *    CAUTIONS : For INTERNAL use only
 ********************************************************************/
void dl_flushtn(void)
   {
      ral = 0;
      rbx = 0;
      rcx = 0;
      rdx = 0;
      calld40(F_TNFLUSH,ralp,rbxp,rcxp,rdxp);
   }

/********************************************************************
 *        NAME : dl_getqual(chan,qualid,qltp)
 * DESCRIPTION : read qualification template
 *       INPUT : chan   = channel number
 *               qualid = qualification template ID
 *               qltp   = return buffer for qualification template
 *      OUTPUT : qualification template copied into return buffer
 *     RETURNS : 0 if success, otherwise failure code
 *       CALLS : calld40x()
 *    CAUTIONS : For INTERNAL use only
 ********************************************************************/
int dl_getqual(chan,qualid,qltp)
   unsigned int chan;
   unsigned int qualid;
   TN_QLT * qltp;
   {
      ral = (unsigned char)chan;
      rbx = qualid;
      rcx = 0;
      return(calld40x(F_READQUAL,ralp,rbxp,rcxp,(unsigned char *)qltp));
   }

/********************************************************************
 *        NAME : dl_setqual(chan,qualid,qltp)
 * DESCRIPTION : update qualification template
 *       INPUT : chan   = channel number
 *               qualid = qualification template ID
 *               qltp   = pointer to qualification template
 *      OUTPUT : none
 *     RETURNS : 0 if success, otherwise failure code
 *       CALLS : calld40x()
 *    CAUTIONS : For INTERNAL use only
 ********************************************************************/
int dl_setqual(chan,qualid,qltp)
   unsigned int chan;
   unsigned int qualid;
   TN_QLT * qltp;
   {
      ral = (unsigned char)chan;
      rbx = qualid;
      rcx = 0;
      return(calld40x(F_UPDQUAL,ralp,rbxp,rcxp,(unsigned char *)qltp));
   }

/********************************************************************
 *        NAME : dl_selqual(qualid)
 * DESCRIPTION : select qualification template used for tone detection
 *       INPUT : qualid = qualification template ID
 *      OUTPUT : none
 *     RETURNS : void
 *       CALLS : none
 *    CAUTIONS : For INTERNAL use only
 ********************************************************************/
void dl_selqual(qualid)
   unsigned int qualid;
   {
     tn_qualid = qualid;
   }


