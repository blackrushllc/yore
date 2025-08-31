
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
 *   Version 3.90                                09/30/93   *
 ***********************************************************/

/*
 * Defines for compatability to old d40lib versions.
 */
#define Sys_Start                startsys
#define Set_Global_Params(S,O)   setgparm(O)
#define Sys_Stop                 stopsys
#define Chn_Stop                 stopch
#define Chn_Status(C,O,S)        getcstat(C,O)
#define Sethook                  sethook
#define Set_CST_Mask             setcst
#define Clear_DTMF               clrdtmf
#define Read_DTMF                readdtmf
#define Record_Buffer(C,O,S)     recbuf(C,O)
#define Play_Buffer(C,O,S)       playbuf(C,O)
#define Record_File(C,O,S,M)     recfile(C,O,M)
#define Play_File(C,O,S)         playfile(C,O)
#define Dial                     dial
#define Get_DTMF_String(C,O,S)   getdtmfs(C,O)
#define vopen(S,O,M)             vhopen(O,M)
#define vclose                   vhclose

/*
 * VOPEN function access mode definitions
 */
#define READ    0
#define RDWR    1
#define CREATE  2
#define APPEND  3


/* ---------------------------- Borland Mod --------------------------- */
#ifdef __cplusplus
extern "C"{
#endif
/* -------------------------- End Borland Mod ------------------------- */

/*
 * D40lib function prototypes
 */
void clrrwb(RWB *);
void clrxrwb(RWB *);
void clrdcb(DCB *);
void clrcpb(CPB *);
int isdrvact(unsigned char);
int startsys(unsigned int,unsigned int,unsigned int,unsigned int,unsigned int *);
int setgparm(DCB *);
int setxparm(DCB *);
int setcparm(unsigned int,CPB *);
int stopsys(void);
int stopch(unsigned int);
int getcstat(unsigned int,CSB *);
int sethook(unsigned int,unsigned int);
int setcst(unsigned int,unsigned int,unsigned int);
int clrdtmf(unsigned int);
int readdtmf(unsigned int,unsigned char *);
int recbuf(unsigned int,RWB *);
int playbuf(unsigned int,RWB *);
int recfile(unsigned int,RWB *,unsigned int);
int playfile(unsigned int,RWB *);
int xplayf(unsigned int,unsigned int,RWB *);
int dial(unsigned int,unsigned char *);
int callp(unsigned int,unsigned char *);
int xcallp(unsigned int,RWB *);
int getdtmfs(unsigned int,RWB *);
int getevt(unsigned int *,unsigned int *,unsigned int *);
int Get_Next_Event(unsigned int *,unsigned int *,unsigned int *);
int setdmask(unsigned int,unsigned int,unsigned int);
int getver(unsigned int *,unsigned int *);
int sched(void);
int getcom(unsigned int,DACCB *);
int putcom(unsigned int,ADCCB *);
int playuser(unsigned int,unsigned char *,unsigned int *);
int recuser(unsigned int,unsigned char *,unsigned int *);
int getcar(unsigned int,CAR *);
int startamx(unsigned int,unsigned int *);
int stopamx(void);
int sw_on(unsigned int,unsigned int);
int sw_off(unsigned int,unsigned int);
int amx_off(void);
int amx_msk(unsigned int,unsigned int);
int ring(unsigned int,unsigned int);
int set_ring(unsigned int,unsigned int,unsigned int,unsigned int);
char *d4xerr(unsigned int);
int gtevtblk(EVTBLK *);
unsigned int getvctr(void);
int putevt(unsigned int,unsigned int,unsigned int);
int setiparm(unsigned int,unsigned int,unsigned int);
int wink(unsigned int);
int sb_route(unsigned int, int);
int sb_rtrcvxmt(unsigned int, int, int);

/* ---------------------------- Borland Mod --------------------------- */
#ifndef __TURBOC__
void peek(unsigned int, unsigned int, char *, unsigned int);
#endif
/* -------------------------- End Borland Mod ------------------------- */

int dl_addtone(unsigned int, unsigned char, unsigned char);
int dl_deltones(unsigned int);
int dl_enbtone(unsigned int, unsigned int, unsigned int);
int dl_distone(unsigned int, unsigned int, unsigned int);
int dl_blddt(unsigned int, unsigned int, unsigned int \
	    ,unsigned int, unsigned int, unsigned int);
int dl_blddtcad(unsigned int, unsigned int, unsigned int \
	       ,unsigned int, unsigned int, unsigned int \
	       ,unsigned int, unsigned int, unsigned int \
	       ,unsigned int);
int dl_bldst(unsigned int, unsigned int, unsigned int, unsigned int);
int dl_bldstcad(unsigned int, unsigned int, unsigned int \
	       ,unsigned int, unsigned int, unsigned int \
	       ,unsigned int, unsigned int);
int buildtone(unsigned int, unsigned int, unsigned int, unsigned int,
	      unsigned int, unsigned int, unsigned int, unsigned int,
	      unsigned int, unsigned int, unsigned int, unsigned int);
void dl_setgtdamp(int,int,int,int);
int dl_playtone(unsigned int, RWB *);
void dl_bldtngen(TN_GEN *,unsigned int,unsigned int, int, int, int);
int dl_adjsv(unsigned int, unsigned int, unsigned int, int);
int dl_setsvcond(unsigned int, unsigned int, SVCB *);
int dl_clrsvcond(unsigned int);
int dl_getcursv(unsigned int, int *, int *);
int dl_setsvmt(unsigned int, unsigned int, SVMT *, unsigned int);
int dl_getsvmt(unsigned int, unsigned int, SVMT *);
int dl_addspddig(unsigned int, unsigned char, unsigned int);
int dl_addvoldig(unsigned int, unsigned char, unsigned int);
int dl_gtsernum(unsigned int, char *);
int dl_initcallp(unsigned int);
int dl_chgfreq(int,int,int,int,int);
int dl_chgdur(int,int,int,int,int);
int dl_chgrepcnt(int,int);
int dl_chgqualid(int);
int dl_gettninfo(int, TN_INFO *);
void dl_flushtn(void);
int dl_getqual(unsigned int, unsigned int, TN_QLT *);
int dl_setqual(unsigned int, unsigned int, TN_QLT *);
void dl_selqual(unsigned int);

/* ---------------------------- Borland Mod --------------------------- */
#ifdef __cplusplus
}
#endif
/* -------------------------- End Borland Mod ------------------------- */
