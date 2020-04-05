#!/bin/bash
cd /root
mpijavac -cp "/root/lib/*" Client.java
mpirun --allow-run-as-root -np 2 java -classpath "/root/lib/*" Client 1000000 10 2000000
