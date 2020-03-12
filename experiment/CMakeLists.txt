cmake_minimum_required(VERSION 3.15)
project(MPI_Web_saturator C)
include_directories(/usr/include/mpi/)
find_package(CURL REQUIRED)
set(CMAKE_C_STANDARD 11)
set(CMAKE_C_COMPILER "/usr/bin/mpicc")

add_executable(mpi_web_saturator mpi_web_saturator.c)
target_link_libraries(mpi_web_saturator
        curl)