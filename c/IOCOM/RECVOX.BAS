1
bold:color 3,4:cls
print "Test program for X-BASIC"
print "========================":print "":print ""
color 7,4
input "Enter IRQ for dialogic boards: ",i
n=startsys(i):if n=0 then print "Cannot start dialogic system.":end
maxsec 360
print n;" channels detected."
10 n=n-1:if n=0 then goto 20
gosub 500
goto 10
20 input "Enter filename: ",a$
30 if a$="" then goto 990
40 input "Enter channel (1-4) ",chan
50 if event()=0 then goto 60 else goto 50
60 handle=vcreate(a$)
70 if handle=0 then print "Error opening file ";a$:goto 20
100 offhook chan
110 gosub 500
120 record chan,handle
130 gosub 500
140 vclose handle
150 onhook chan
160 gosub 500
170 goto 20
500 z=0:color 4,4
502 e=event():z=z+1:if z=9999 then print "Timeout!":end
504 print ".";
506 if e=0 then goto 502
508 color 3,4:print chr$(13)+"EVENT: ";e;" channel ";channel();" data ";evdata()
510 color 7,4:return
990 if handle then vclose handle
999 end stopsys()
