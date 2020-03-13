#include <stdio.h>
#include <stdlib.h>
#include <mpi.h>
#include "url2file.c"
#include <time.h>
#include <pthread.h>

int my_rank;

int **alloc_2d_int(int rows, int cols) {
    int *data = (int *)malloc(rows*cols*sizeof(int));
    int **ar= (int **)malloc(rows*sizeof(int*));
    int i;
    for (i=0; i<rows; i++)
        ar[i] = &(data[cols*i]);

    return ar;
}

int *alloc_1d_int(int cols) {
    int *ar= (int *)malloc(cols*sizeof(int));
    int i;
    for (i=0; i<cols; i++)
        ar[i] = 0;

    return ar;
}

int contentLength;
int threadsPerCore;
int coreCount;
int startTimeStamp;
int downloadIterations;
// Get the name of the processor
char my_name[MPI_MAX_PROCESSOR_NAME];
int name_len;


pthread_t *threads;
int **threadConfigs;

pthread_barrier_t barrier;

void *start_test(int my_thread_rank) {
    //gets current working directory
    char cwd[PATH_MAX];
    getcwd(cwd, sizeof(cwd));

    //pthread_barrier_wait(&barrier);
    char normalized_rank[18];
    char url[45];

    //char url[72];
    char *target_file;
    char *dir;


    sprintf(normalized_rank, "%d-%04d-%03d", startTimeStamp, my_rank, my_thread_rank);
    sprintf(url, "https://people.bath.ac.uk/jn475/00000200.txt");
//    sprintf(url, "http://dissertation.profisites.com/api/%s/%08d.txt", normalized_rank, contentLength);

    printf("Size: %lu, %lu, CWD: %s \n", sizeof(cwd), strlen(cwd), cwd);

        dir = (char *) malloc(sizeof(char) * (18 + 1 + strlen(cwd)));

    target_file = (char *) malloc(sizeof(char) * (42 + 1 + strlen(cwd)));


    sprintf(dir, "%s/results/%d", cwd, startTimeStamp);
    sprintf(target_file, "%s/%s.txt", dir, normalized_rank);

    if (my_rank == 0) {
        mkdir("results", 0777);
        mkdir(dir, 0777);
    }
    MPI_Barrier(MPI_COMM_WORLD);

    printf("rank: %d - %d, node %s, timestamp: %d, client: %s\n", my_rank, my_thread_rank, my_name, startTimeStamp, normalized_rank);
    //TODO - in production change to a while loop
    if (downloadIterations > 0) {
        int i;
        for (i = 0; i < downloadIterations; i++) {
            download_url(url, target_file);
        }
    } else {
        while(1) {
            download_url(url, target_file);
        }
    }
}

void create_subthreads(int n) {
    int i;
    if (n < 1) {
        n = 1;
    }
    threadConfigs = alloc_2d_int(n, 1);
    threads = malloc(n * sizeof(pthread_t));

    pthread_barrier_init(&barrier, NULL, n);

    for (i = 0; i < n; i++) {
        threadConfigs[i][0] = i;
    }

    for (i = 1; i < n; i++) { //start at 1, the first thread will be the main thread
        pthread_create(&threads[i], NULL, (void*) start_test, (int*) i);
    }

    start_test(0);

    for (i = 1; i < n; i++) { //start at 1, the first thread will be the main thread
        pthread_join(threads[i], NULL);
    }

    pthread_barrier_destroy(&barrier);
}

void init_test() {
    if (my_rank == 0) {
        startTimeStamp = (int)time(NULL);
        //TODO - log run configuration?
    }
    //broadcast timestamp - this is used to identify individual runs of the experiment
    MPI_Bcast(&startTimeStamp, 1, MPI_INT, 0, MPI_COMM_WORLD);

    //create pthreads
    create_subthreads(threadsPerCore);
}

/**
 * @param argc
 * @param argv
 * @return
 */
int main(int argc, char **argv) {
    //initial configuration from received arguments
    contentLength = atoi(argv[1]);
    downloadIterations = atoi(argv[2]);

    /* Initialize the infrastructure necessary for communication */
    MPI_Init(&argc, &argv);
    /* Identify this process */
    MPI_Comm_rank(MPI_COMM_WORLD, &my_rank);
    /* Find out how many total processes are active */
    MPI_Comm_size(MPI_COMM_WORLD, &coreCount);
    MPI_Get_processor_name(my_name, &name_len);

    MPI_Barrier(MPI_COMM_WORLD); /* IMPORTANT */

    init_test();

    /* Tear down the communication infrastructure */
    MPI_Barrier(MPI_COMM_WORLD); /* IMPORTANT */

    MPI_Finalize();
    return 0;
}