1 bold:color 3,4:cls
2 print "Test program for X-BASIC"
3 print "========================":print "":print ""
4 color 7,4
5 input "Enter IRQ for dialogic boards: ",i
6 n=startsys(i):if n=0 then print "Cannot start dialogic system.":end
7 print n;" channels detected."
8 n=n-1:if n=0 then goto 20
9 gosub 500
10 goto 8
20 print "VOX FILES"
30 print "---------"
40 DIR *.VOX
50 input "Enter filename: ",a$
55 if a$="" then end
60 input "Enter channel (1-4) ",c
70 if event()=0 then goto 80 else goto 70
80 x=vhopen(a$)
90 if x=0 then print "Error opening file.":goto 50
100 offhook c
110 gosub 500
120 play c,x
130 gosub 500
140 vclose x
150 onhook c
160 gosub 500
170 goto 50
500 z=0:color 4,4
502 e=event():z=z+1:if z=9999 then print "Timeout!":end
504 print ".";
506 if e=0 then goto 502
508 color 3,4:print chr$(13)+"EVENT: ";e;" channel ";channel();" data ";evdata()
510 color 7,4:return
