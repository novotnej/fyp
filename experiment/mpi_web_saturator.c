#include <stdio.h>
#include <stdlib.h>
#include <mpi.h>
#include "url2file.c"
#include <time.h>
#include <unistd.h>

int my_rank;

int contentLength;
int coreCount;
int startTimeStamp;
int downloadIterations;
int sleepTime;
// Get the name of the processor
char my_name[MPI_MAX_PROCESSOR_NAME];
int name_len;

char cwd[PATH_MAX];

void save_config() {
    char *config_file = (char *) malloc(sizeof(char) * (30 + strlen(cwd)));
    sprintf(config_file, "%s/results/%d/config.json", cwd, startTimeStamp);
    FILE *f = fopen(config_file, "w");
    fprintf(f, "{\"timestamp\": %d, \"iterations\": %d, \"sleep\": %d, \"threads\": %d, \"length\" : %d}", startTimeStamp, downloadIterations, sleepTime, coreCount, contentLength);
    fclose(f);
}


void *start_test(int my_thread_rank) {
    //gets current working directory
    getcwd(cwd, sizeof(cwd));

    char normalized_rank[18];
    //char url[45];

    char url[72];
    char *target_file;
    char *dir;


    sprintf(normalized_rank, "%d-%04d-%03d", startTimeStamp, my_rank, my_thread_rank);
    //sprintf(url, "https://people.bath.ac.uk/jn475/00000200.txt");
    sprintf(url, "http://dissertation.profisites.com/api/%s/%08d.txt", normalized_rank, contentLength);

    //printf("Size: %lu, %lu, CWD: %s \n", sizeof(cwd), strlen(cwd), cwd);
    dir = (char *) malloc(sizeof(char) * (18 + 1 + strlen(cwd)));
    target_file = (char *) malloc(sizeof(char) * (42 + 1 + strlen(cwd)));


    sprintf(dir, "%s/results/%d", cwd, startTimeStamp);
    sprintf(target_file, "%s/%s.txt", dir, normalized_rank);

    if (my_rank == 0) {
        mkdir("results", 0777);
        mkdir(dir, 0777);
        save_config();
    }
    MPI_Barrier(MPI_COMM_WORLD);

    printf("rank: %d - %d, node %s, timestamp: %d, client: %s\n", my_rank, my_thread_rank, my_name, startTimeStamp, normalized_rank);
    //TODO - in production change to a while loop
    if (downloadIterations > 0) {
        int i;
        for (i = 0; i < downloadIterations; i++) {
            download_url(url, target_file);
            sleep(sleepTime);
        }
    } else {
        while(1) {
            download_url(url, target_file);
            sleep(sleepTime);
        }
    }
}

void init_test() {
    if (my_rank == 0) {
        startTimeStamp = (int)time(NULL);
        //TODO - log run configuration?
    }
    //broadcast timestamp - this is used to identify individual runs of the experiment
    MPI_Bcast(&startTimeStamp, 1, MPI_INT, 0, MPI_COMM_WORLD);

    start_test(0);
}

/**
 * @param argc
 * @param argv
 * @return
 */
int main(int argc, char **argv) {
    //initial configuration from received arguments
    if (argc > 1) {
        contentLength = atoi(argv[1]);
    } else {
        contentLength = 100;
    }
    if (argc > 2) {
        downloadIterations = atoi(argv[2]);
    } else {
        downloadIterations = 10;
    }
    if (argc > 3) {
        sleepTime = atoi(argv[3]);
    } else {
        sleepTime = 1;
    }

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