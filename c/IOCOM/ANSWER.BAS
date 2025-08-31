1 bold:color 3,4:cls
print "Test program for X-BASIC"
print "========================"
print ""
print ""
color 7,4
input "Enter IRQ for dialogic boards: ",i
maxring 1
maxdtmf 4
maxsec 10
n=startsys(i)
if n=0 then print "Cannot start dialogic system.":end
print n;" channels detected."
intro=vhopen("intro.vox")
50 gosub 8000 'go for an event
locate 23,1
GOSUB E*100
GOTO 50

100 print "MAXDT		";
        if handle then handle=0:vclose handle
        onhook channel();
ret
200 print "TERMDT		";
        if handle then handle=0:vclose handle
        onhook channel()
ret
300 print "DOS ERROR!":end stop()

ret
400 print "MAXSEC TIMEOUT	";
        if handle then handle=0:vclose handle
        onhook channel()
ret
800 print "OFFHOOK COMPLETE	";
        clrdtmf channel()
        play channel(),intro
ret
900 print "DIALING COMPLETE	";

ret
1000 print "MAXSIL timeout	";
        if handle then handle=0:vclose handle
	onhook channel()
ret
1100 print "EOF			";
        handle=vcreate(str$(channel())+".VOX")
        if handle=0 then print "Error opening file ";:end stop()
        record channel(),handle
ret
1200 print "LOOPSIG		";
        if handle then handle=0:vclose handle
        onhook channel()
ret
1300 if e=13 then print "DISK FULL!":end stop()

ret
1400 print "ONHOOK COMPLETE     ";
        clrdtmf channel()
ret
1800 print "CPA Complete	";

ret
2100 print "RING recieved	";
offhook channel();
ret
2900 print "Term digit recieved ";
        if handle then handle=0:vclose handle
        onhook channel();
ret


8000 a$=inkey$()
if a$=chr$(27) then quit stop()
e=event():if e=0 then goto 8000
color 4,4:locate channel()+10,1 : print channel;
color 3,4:print " EVENT: ";e;" data ";evdata();"  "
color 7,4:return

