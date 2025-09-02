/**
 **   Dialogic header files
 **/
#include "d40.h"           /* standard DIALOG/4x header  */
#include "d40lib.h"
#include "vfcns.h"


/**
 **   Definitions
 **/

#define MAXCHAN 24
#define MAXDTMF 20
#define MAXRING 2


/**
 **   Data structure which defines the states for each channel
 **/
struct {
   int state;               /* state of channel             */
   int msg_fh;              /* handle for message files     */
   char buffer[64];         /* buffer for dtmf digits       */
} port[MAXCHAN] = {0};



/**
 **   Global data
 **/


RWB d4xrwb = {0};             /* read/write block for the D/4x */
int maxchan = 0;              /* max D/4x channels in system */
int d4xintr = 5;        /* default d/4x hardware interrupt */
int channel = 0;

EVTBLK d4evtblk;       /* event data block for the D/4x */
int errcode;           /* exit to DOS with errcode      */
int evtcode;           /* termination event code        */
int evtdata;           /* event data                    */

