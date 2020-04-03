#!/bin/bash
cd /root
mpijavac -cp "/usr/local/lib/mpi.jar" Client.java
mpirun --allow-run-as-root -np 2 java Client
