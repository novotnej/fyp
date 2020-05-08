#include <stdio.h>
#include <stdlib.h>
#include <mpi.h>
#include "url2file.c"
#include <time.h>
#include <unistd.h>
#include <libgen.h>


int contentLength, coreCount, startTimeStamp, downloadIterations, sleepTime;
char *experimentName;

// Get the name of the processor
char my_name[MPI_MAX_PROCESSOR_NAME];
int my_rank, name_len;

char cwd[PATH_MAX];

void save_config() {
    char *config_file = (char *) malloc(sizeof(char) * (30 + strlen(experimentName) + strlen(cwd)));

    sprintf(config_file, "%s/results/%s/%d/config.json", cwd, experimentName, startTimeStamp);
    //printf("EXP: %s\n", config_file);
    FILE *f = fopen(config_file, "w");
    fprintf(f, "{\"timestamp\": %d, \"iterations\": %d, \"sleep\": %d, \"threads\": %d, \"length\" : %d}",
            startTimeStamp, downloadIterations, sleepTime, coreCount, contentLength);
    fclose(f);
}

void *start_test(int my_thread_rank) {
    //gets current working directory
    getcwd(cwd, sizeof(cwd));

    char normalized_rank[18];

    char *url;
    char *target_file;
    char *dir;

    url = (char *) malloc(sizeof(char) * (53 + strlen(my_name)));

    //Normalize client ID (client ID = normalized_rank)
    sprintf(normalized_rank, "%d-%04d-%03d", startTimeStamp, my_rank, my_thread_rank);
    //establish the endpoint URL
    sprintf(url, "http://dissertation.profisites.com/api/%s/%08d.txt", my_name, contentLength);

    dir = (char *) malloc(sizeof(char) * (18 + 1 + strlen(experimentName) + strlen(cwd)));
    target_file = (char *) malloc(sizeof(char) * (42 + 1 + strlen(experimentName) + strlen(cwd)));

    //establish results files directory
    sprintf(dir, "%s/results/%s/%d", cwd, experimentName, startTimeStamp);

    //establish results file name
    sprintf(target_file, "%s/%s.txt", dir, normalized_rank);

    if (my_rank == 0) {
        //root process creates the results directories and stores run configuration
        mkdir("results", 0777);

        char *exp_dir = (char *) malloc(sizeof(char) * (8 + strlen(experimentName)));
        sprintf(exp_dir, "results/%s", experimentName);

        mkdir(exp_dir, 0777);
        mkdir(dir, 0777);
        save_config();
    }
    MPI_Barrier(MPI_COMM_WORLD); //synchronize processes to start after results dirs have been created

    //if iterations are specified, execute N number of times, otherwise repeat indefinitely until process is killed
    if (downloadIterations > 0) {
        int i;
        for (i = 0; i < downloadIterations; i++) {
            download_url(url, target_file);
            usleep(sleepTime);
        }
    } else {
        while(1) {
            download_url(url, target_file);
            usleep(sleepTime);
        }
    }
}

void init_test() {
    if (my_rank == 0) {
        startTimeStamp = (int)time(NULL);
        printf("Threads: %d; Time: %d; Length: %d; Iter: %d; Sleep: %d; Name: %s\n",
                coreCount, startTimeStamp, contentLength, downloadIterations, sleepTime, experimentName);
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
    contentLength = (argc > 1) ? atoi(argv[1]) : 100;
    downloadIterations = (argc > 2) ? atoi(argv[2]) : 10;
    sleepTime = (argc > 3) ? atoi(argv[3]) : 1000;

    if (argc > 4) {
        experimentName = (char *) malloc(sizeof(char) * (strlen(argv[4])));
        sprintf(experimentName, "%s", argv[4]);
    } else {
        experimentName = (char *) malloc(sizeof(char) * 4);
        sprintf(experimentName, "%s", "temp");
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