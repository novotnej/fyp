#Scenarios 1 and 2
Install an a web server in the web directory

(scenario 2 - also install varnish and point it to the installed webserver)

Modify mpi_web_saturator.c to point to this web server

compile mpi_web_saturator.c with -lcurl

launch the program with mpirun