#test baseline time generation with unloaded server, measure network latency
#single thread, variable content length, 1000 repeats
mpirun -np 1 --host=104.248.161.131,142.93.45.43,142.93.45.179,142.93.35.255,104.248.168.220 ./a.out 100 1000 50000
mpirun -np 1 --host=104.248.161.131,142.93.45.43,142.93.45.179,142.93.35.255,104.248.168.220 ./a.out 500 1000 50000
mpirun -np 1 --host=104.248.161.131,142.93.45.43,142.93.45.179,142.93.35.255,104.248.168.220 ./a.out 1000 1000 50000
mpirun -np 1 --host=104.248.161.131,142.93.45.43,142.93.45.179,142.93.35.255,104.248.168.220 ./a.out 1500 1000 50000
mpirun -np 1 --host=104.248.161.131,142.93.45.43,142.93.45.179,142.93.35.255,104.248.168.220 ./a.out 2000 1000 50000
mpirun -np 1 --host=104.248.161.131,142.93.45.43,142.93.45.179,142.93.35.255,104.248.168.220 ./a.out 2500 1000 50000
mpirun -np 1 --host=104.248.161.131,142.93.45.43,142.93.45.179,142.93.35.255,104.248.168.220 ./a.out 3000 1000 50000
mpirun -np 1 --host=104.248.161.131,142.93.45.43,142.93.45.179,142.93.35.255,104.248.168.220 ./a.out 3500 1000 50000
mpirun -np 1 --host=104.248.161.131,142.93.45.43,142.93.45.179,142.93.35.255,104.248.168.220 ./a.out 4000 1000 50000
mpirun -np 1 --host=104.248.161.131,142.93.45.43,142.93.45.179,142.93.35.255,104.248.168.220 ./a.out 4500 1000 50000
mpirun -np 1 --host=104.248.161.131,142.93.45.43,142.93.45.179,142.93.35.255,104.248.168.220 ./a.out 5000 1000 50000
mpirun -np 1 --host=104.248.161.131,142.93.45.43,142.93.45.179,142.93.35.255,104.248.168.220 ./a.out 6000 1000 50000
mpirun -np 1 --host=104.248.161.131,142.93.45.43,142.93.45.179,142.93.35.255,104.248.168.220 ./a.out 7000 1000 50000
mpirun -np 1 --host=104.248.161.131,142.93.45.43,142.93.45.179,142.93.35.255,104.248.168.220 ./a.out 8000 1000 50000
mpirun -np 1 --host=104.248.161.131,142.93.45.43,142.93.45.179,142.93.35.255,104.248.168.220 ./a.out 9000 1000 50000
mpirun -np 1 --host=104.248.161.131,142.93.45.43,142.93.45.179,142.93.35.255,104.248.168.220 ./a.out 10000 1000 50000