#!/bin/bash
cd /root
mpijavac -cp "/root/lib/*" Server.java
java -classpath "/root/lib/*" Server
