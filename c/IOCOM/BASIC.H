#define DELIMITER 1
#define VARIABLE  2
#define NUMBER    3
#define FUNCTION  4
#define COMMA     5

#define DOUBLE 10
#define STRING 11
#define INTEGER 12

#define STRFLAG -94949.1
#define NULLFLAG -49494.1

#define MAXPROG		8192

#define NORMAL          0
#define BOLD            1
#define DIM             2
#define BLINKIE         5
#define INVERSE         7
#define FGBLACK         30
#define BGBLACK         40
#define FGRED           31
#define BGRED           41
#define FGGREEN         32
#define BGGREEN         42
#define FGYELLOW        33
#define BGYELLOW        43
#define FGBLUE          34
#define BGBLUE          44
#define FGMAGENTA       35
#define BGMAGENTA       45
#define FGCYAN          36
#define BGCYAN          46
#define FGWHITE         37
#define BGWHITE         47

#define  CLS            printf("%c[2J",27);
#define  COLOR(x,y)     printf("%c[%d;%dm",27,x,y);
#define  LOCATE(y,x)    printf("%c[%d;%df",27,y,x);
#define  SAVECURSOR     printf("%c[s",27);
#define  RESTCURSOR     printf("%c[u",27);
#define  SGR(x)         printf("%c[%dm",27,x);


