
 /**************************************************
 *                                                 *
 *   VV   VV  FFFFFFF   CCCCC   NN   NN   SSSSS    *
 *   VV   VV  FF       CC   CC  NNN  NN  SS   SS   *
 *   VV   VV  FF       CC       NNNN NN  SS        *
 *    V   V   FFFF     CC       NN NNNN   SSSSS    *
 *    VV VV   FF       CC       NN  NNN       SS   *
 *    VV VV   FF       CC   CC  NN   NN  SS   SS   *
 *     VVV    FF        CCCCC   NN   NN   SSSSS    *
 *                                                 *
 ***************************************************
 *   Version 3.90                      09/30/93    *
 **************************************************/

/*
 * VOPEN function definitions
 */
#define READ    0
#define RDWR    1
#define CREATE  2
#define APPEND  3

int   vhopen(unsigned char *,unsigned int);
int   vhclose(unsigned int);
long  vhseek(unsigned int,unsigned long,unsigned int);

unsigned short int d4getoff(unsigned char *);
unsigned short int d4getseg(unsigned char *);
