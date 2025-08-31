
 /********************************************************************
 *                                                                   *
 *   DDDDDD     4444    00000   FFFFFFF   CCCCC   NN   NN   SSSSS    *
 *   DD   DD   44 44   00   00  FF       CC   CC  NNN  NN  SS   SS   *
 *   DD   DD  44  44   00   00  FF       CC       NNNN NN  SS        *
 *   DD   DD  44  44   00   00  FFFF     CC       NN NNNN   SSSSS    *
 *   DD   DD  4444444  00   00  FF       CC       NN  NNN       SS   *
 *   DD   DD      44   00   00  FF       CC   CC  NN   NN  SS   SS   *
 *   DDDDDD       44    00000   FF        CCCCC   NN   NN   SSSSS    *
 *                                                                   *
 *********************************************************************
 *   Version 3.90                                         09/30/93   *
 ********************************************************************/

 /*	NOTICE !!!!!!!
 /*	This version of d40fcns is modified for Borland Turbo C.
 /*	All changes from the original are commented. You may need
 /*	to change all instances of __TURBOC__ to __BORLANDC__
 /*
 /*	Rick Marlette, P O E Systems, Atlanta, Ga. (800) 722-3615.
 */

/* --------------------------- Borland Mod ----------------------------	*/

#include "\dialogic\borland\d40.h"	/* assumes files are here */
#include "\dialogic\borland\d40lib.h"	/* assumes files are here */

#define LATTICE  0                   /* Compiler define          */
				     /* 0 = Microsoft Compiler   */
				     /* 1 = Lattice Compiler     */

/* ----	Lattice Must Be Set To Zero For Borland To Operate Properly --- */
/* -------------------------- End Borland Mod ------------------------- */

/*
 * MSDOS function defintions
 */
#define F_CREAT   0x3C               /* MSDOS create file        */
#define F_OPNF    0x3D               /* MSDOS open file          */
#define F_CLSF    0x3E               /* MSDOS close file handle  */
#define F_SEEK    0x42               /* MSDOS seek function code */

extern int     int_level;            /* software interrupt level */


#if LATTICE

#define CC_CRY    0x0001             /* 8088 Carry flag          */

/*
 * Register structures for Lattice software interrupt function
 */

   struct WORDREGS {
      unsigned short int ax;
      unsigned short int bx;
      unsigned short int cx;
      unsigned short int dx;
      unsigned short int si;
      unsigned short int di;
      unsigned short int cflag;
      };

   struct BYTEREGS {                 /* byte registers */
      unsigned char al, ah;
      unsigned char bl, bh;
      unsigned char cl, ch;
      unsigned char dl, dh;
      };

   struct SREGS {                    /* segment registers */
      unsigned short int es,cs,ss,ds;
      };

   union REGS {                      /* structure for int86x() call */
      struct WORDREGS x;
      struct BYTEREGS h;
      };

   struct SO {                       /* structures for pointers */
      unsigned short int off;
      unsigned short int seg;
      };

   union LXXPTR {                    /* structure for d4get-off/seg */
      struct SO so;
      unsigned char *lptr;
      };

#else

/*
 * Register structures for Mircosoft
 */

#pragma pack(1)

#include    "dos.h"

   struct SO {                       /* structures for pointers */
      unsigned short int off;
      unsigned short int seg;
      };

   union LXXPTR {                    /* structure for d4get-off/seg */
      struct SO so;
      unsigned char far *lptr;
      };

#endif


/*
 * Storage allocation for d40fcns
 */

static union  REGS   ireg,oreg;
static struct SREGS  sr;
static union  LXXPTR lp;



/*
 * d40fcns.c prototypes.
 */
int calld40(unsigned char,unsigned char *,unsigned int *,unsigned int *,unsigned int *);
int calld40x(unsigned char,unsigned char *,unsigned int *,unsigned int *, unsigned char *);
int dskseek(unsigned int,unsigned int,unsigned int *,unsigned int *);
int dskcrea(unsigned char *,unsigned int *,unsigned int);
int dskclose(unsigned int);
int dskopen(unsigned char *,unsigned int *,unsigned int);
unsigned short int d4getoff(unsigned char *);
unsigned short int d4getseg(unsigned char *);


/*********************************************************************
 *       NAME : d4getoff(ptr)
 *DESCRIPTION : Get the offset value of the pointer.
 *      INPUT : ptr = a pointer.
 *     OUTPUT : none.
 *    RETURNS : offset value of the pointer.
 *      CALLS : none.
 *   CAUTIONS : none.
 *********************************************************************/
unsigned short int d4getoff(ptr)
   unsigned char *ptr;
   {
      lp.lptr = ptr;
      return((unsigned short int) lp.so.off);
   }


/*********************************************************************
 *       NAME : d4getseg(ptr)
 *DESCRIPTION : Get the segment value of the pointer.
 *      INPUT : ptr = a pointer.
 *     OUTPUT : none.
 *    RETURNS : segment value of the pointer.
 *      CALLS : none.
 *   CAUTIONS : none.
 *********************************************************************/
unsigned short int d4getseg(ptr)
   unsigned char *ptr;
   {
      /* condition is always true using borland
      if (sizeof(ptr) == 2) {
	 offset only
	 segread(&sr);
	 return((unsigned short int) sr.ds);
      }
      */

      lp.lptr = ptr;
      return((unsigned short int) lp.so.seg);
   }

/* --------------------------- Borland Mod ----------------------------	*/

#ifndef __TURBOC__
#if !LATTICE
/*****************************************************************************
 *       NAME : peek(seg,ofst,bufp,count)
 *DESCRIPTION : get the data from arbitrary memory location(s)
 *      INPUT : seg  = segment of location to be examined
 *              ofst = offset of location to be examined
 *              bufp = pointer to buffer in which to place memory data
 *              count = number of bytes to place into the buffer
 *     OUTPUT : data transferred to 'bufp'
 *    RETURNS : none
 *      CALLS : none
 *   CAUTIONS : none.
 *****************************************************************************/
void peek(seg,ofst,bufp,count)
   unsigned int seg,ofst;
   char *bufp;
   unsigned int count;
   {
      lp.so.seg = seg;
      lp.so.off = ofst;

      while (count-- > 0)  {
	 *bufp++ = *lp.lptr++;
      }
   }
#endif
#endif


/**********************************************************************
 *       NAME : calld40(rah,ralp,rbxp,rcxp,rdxp)
 *DESCRIPTION : Call the DIALOG/4x device driver without a pointer parm.
 *      INPUT : rah  = value required in the ah register.
 *              ralp = a pointer to the value required in the al register.
 *              rbxp = a pointer to the value required in the bx register.
 *              rcxp = a pointer to the value required in the cx register.
 *              rdxp = a pointer to the value required in the dx register.
 *     OUTPUT : none.
 *    RETURNS : value returned in ah register by DIALOG/4x device driver.
 *      CALLS : int86x()
 *   CAUTIONS : none.
 **********************************************************************/
int calld40(rah,ralp,rbxp,rcxp,rdxp)
   unsigned char rah;
   unsigned char *ralp;
   unsigned int *rbxp;
   unsigned int *rcxp;
   unsigned int *rdxp;
   {

      ireg.h.ah = rah;
      ireg.h.al = *ralp;
      ireg.x.bx = *rbxp;
      ireg.x.cx = *rcxp;
      ireg.x.dx = *rdxp;

      int86x(int_level,&ireg,&oreg,&sr);



      *ralp = oreg.h.al;
      *rbxp = oreg.x.bx;
      *rcxp = oreg.x.cx;
      *rdxp = oreg.x.dx;

      return((int)oreg.h.ah);
   }


/**********************************************************************
 *       NAME : calld40x(rah,ralp,rbxp,rcxp,ptr)
 *DESCRIPTION : Call the DIALOG/4x device driver with a pointer parm.
 *      INPUT : rah  = value required in the ah register.
 *              ralp = a pointer to the value required in the al register.
 *              rbxp = a pointer to the value required in the bx register.
 *              rcxp = a pointer to the value required in the cx register.
 *              ptr  = a pointer required by the DIALOG/4x device driver.
 *     OUTPUT : none.
 *    RETURNS : value returned in ah register by DIALOG/4x device driver.
 *      CALLS : int86x() d4getoff() d4getseg()
 *   CAUTIONS : none.
 **********************************************************************/
int calld40x(rah,ralp,rbxp,rcxp,ptr)
   unsigned char rah;
   unsigned char *ralp;
   unsigned int *rbxp;
   unsigned int *rcxp;
   unsigned char *ptr;
   {
      ireg.h.ah = rah;
      ireg.h.al = *ralp;
      ireg.x.bx = *rbxp;
      ireg.x.cx = *rcxp;

      if (ptr) {
         /* get segment and offset */
         ireg.x.dx = d4getoff(ptr);
         sr.ds     = d4getseg(ptr);
      } else {
         /* pointer not used */
         ireg.x.dx = 0;
         sr.ds     = 0;
      }

      int86x(int_level,&ireg,&oreg,&sr);

      *ralp = oreg.h.al;
      *rbxp = oreg.x.bx;
      *rcxp = oreg.x.cx;

      return((int)oreg.h.ah);
   }


/**********************************************************************
 *       NAME : dskopen(fnp,fhp,mode)
 *DESCRIPTION : Open a file using low level DOS functions.
 *      INPUT : fnp  = pointer to the ASCIIZ file name.
 *              fhp  = filehandle returned by DOS.
 *              mode = mode in which file is to be opened.
 *     OUTPUT : none.
 *    RETURNS : DOS filehandle.
 *      CALLS : intdosx() d4getoff() d4getseg()
 *   CAUTIONS : none.
 **********************************************************************/
int dskopen(fnp,fhp,mode)
   unsigned char *fnp;
   unsigned int *fhp;
   unsigned int mode;
   {
      unsigned int rc;

      ireg.h.ah = F_OPNF;
      ireg.h.al = (unsigned char)mode;
      ireg.x.dx = d4getoff(fnp);
      sr.ds     = d4getseg(fnp);

      rc = intdosx(&ireg,&oreg,&sr);
#if LATTICE
      if (rc & CC_CRY) {
         return((int)rc);
      }
#else
      if (oreg.x.cflag) {
         return(oreg.x.ax);
      }
#endif
      *fhp = oreg.x.ax;
      /* added to stop borland warning */
      if (rc == 0)
	return((int)0);
      return((int)0);
   }



/**********************************************************************
 *       NAME : dskclose(fh)
 *DESCRIPTION : Close a file using low level DOS functions.
 *      INPUT : fh = DOS filehandle.
 *     OUTPUT : none.
 *    RETURNS : zero if successful.
 *      CALLS : intdosx()
 *   CAUTIONS : none.
 **********************************************************************/
int dskclose(fh)
   unsigned int fh;
   {
      unsigned int rc;

      ireg.h.ah = F_CLSF;
      ireg.x.bx = fh;

      rc = intdosx(&ireg,&oreg,&sr);
#if LATTICE
      if (rc & CC_CRY) {
         return((int)rc);
      }
#else
      if (oreg.x.cflag) {
         return(oreg.x.ax);
      }
#endif
      /* added to stop borland warning */
      if (rc == 0)
	return((int)0);
      return((int)0);
   }


/**********************************************************************
 *       NAME : dskseek(fh,method,rcxp,rdxp)
 *DESCRIPTION : Move file pointer to given position using the method indicated.
 *      INPUT : fh     = DOS filehandle of file to be operated on.
 *              method = method in which to move file pointer.
 *              rcxp   = pointer to high order value of requested position.
 *              rdxp   = pointer to low order value of requested position.
 *     OUTPUT : rdxp   = points to high order value of new position.
 *              rcxp   = points to low order value of new position.
 *    RETURNS : DOS filehandle.
 *      CALLS : intdosx()
 *   CAUTIONS : none.
 **********************************************************************/
int dskseek(fh,method,rcxp,rdxp)
   unsigned int fh;
   unsigned int method;
   unsigned int *rcxp,*rdxp;
   {
      unsigned int rc;

      ireg.h.ah = F_SEEK;
      ireg.h.al = (unsigned char)method;
      ireg.x.bx = fh;
      ireg.x.cx = *rcxp;
      ireg.x.dx = *rdxp;

      rc = intdosx(&ireg,&oreg,&sr);
#if LATTICE
      if (rc & CC_CRY) {
         return((int)rc);
      }
#else
      if (oreg.x.cflag) {
         return(oreg.x.ax);
      }
#endif
      *rcxp = oreg.x.ax;
      *rdxp = oreg.x.dx;
      /* added to stop borland warning */
      if (rc == 0)
	return((int)0);
      return((int)0);
   }


/**********************************************************************
 *       NAME : dskcrea(fnp,fhp,mode)
 *DESCRIPTION : Create a file using low level DOS functions.
 *      INPUT : fnp  = pointer to the ASCIIZ file name.
 *              fhp  = filehandle returned by DOS.
 *              mode = mode in which file is to be opened.
 *     OUTPUT : none.
 *    RETURNS : DOS filehandle.
 *      CALLS : intdosx() d4getoff() d4getseg()
 *   CAUTIONS : none.
 **********************************************************************/
int dskcrea(fnp,fhp,mode)
   unsigned char *fnp;
   unsigned int *fhp;
   unsigned int mode;
   {
      unsigned int rc;

      ireg.h.ah = F_CREAT;
      ireg.x.cx = mode;
      ireg.x.dx = d4getoff(fnp);
      sr.ds     = d4getseg(fnp);

      rc = intdosx(&ireg,&oreg,&sr);
#if LATTICE
      if (rc & CC_CRY) {
         return((int)rc);
      }
#else
      if (oreg.x.cflag) {
         return(oreg.x.ax);
      }
#endif
      *fhp = oreg.x.ax;
      /* added to stop borland warning */
      if (rc == 0)
	return((int)0);
      return((int)0);
   }
