mpirun -np 1 -hostfile ../experiment//hostfile java -classpath "./lib/*" Client 100 100 10000
mpirun -np 1 -hostfile ../experiment//hostfile java -classpath "./lib/*" Client 1000 100 10000
mpirun -np 1 -hostfile ../experiment//hostfile java -classpath "./lib/*" Client 10000 100 10000
mpirun -np 1 -hostfile ../experiment//hostfile java -classpath "./lib/*" Client 100000 100 10000
mpirun -np 1 -hostfile ../experiment//hostfile java -classpath "./lib/*" Client 1000000 100 10000
mpirun -np 1 -hostfile ../experiment//hostfile java -classpath "./lib/*" Client 10000000 100 10000