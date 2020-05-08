/**
 * Modified by Jan Novotny for the Final Year Project
 */
/***************************************************************************
 *                                  _   _ ____  _
 *  Project                     ___| | | |  _ \| |
 *                             / __| | | | |_) | |
 *                            | (__| |_| |  _ <| |___
 *                             \___|\___/|_| \_\_____|
 *
 * Copyright (C) 1998 - 2019, Daniel Stenberg, <daniel@haxx.se>, et al.
 *
 * This software is licensed as described in the file COPYING, which
 * you should have received as part of this distribution. The terms
 * are also available at https://curl.haxx.se/docs/copyright.html.
 *
 * You may opt to use, copy, modify, merge, publish, distribute and/or sell
 * copies of the Software, and permit persons to whom the Software is
 * furnished to do so, under the terms of the COPYING file.
 *
 * This software is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY
 * KIND, either express or implied.
 *
 ***************************************************************************/
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include <curl/curl.h>
#include <errno.h>
#include <sys/stat.h>

int is_setup = 0;
CURL *curl_handle;
FILE *pagefile;
char nl[] = "|00000000\n";

typedef long long u64;

u64 start_time_stamp, end_time_stamp;
struct timeval tv;

u64 get_time_stamp() {
    gettimeofday(&tv,NULL);
    return (1000000*tv.tv_sec) + tv.tv_usec;
}

static size_t write_data(void *ptr, size_t size, size_t nmemb, void *stream) {
    end_time_stamp = get_time_stamp();
    size_t written = fwrite(ptr, size, nmemb, (FILE *) stream);
    //append execution time of the download
    sprintf(nl, "|%08lld\n", (end_time_stamp - start_time_stamp));
    fwrite(&nl, sizeof(char), strlen((const char *) &nl), (FILE *) stream);
    written+= strlen((const char *) &nl);
    //printf("Duration: %lld %lld %lld \n", end_time_stamp, start_time_stamp, (end_time_stamp - start_time_stamp));
    return written;
}


void setup(char url[], char filename[]) {
    is_setup = 1;

    curl_global_init(CURL_GLOBAL_ALL);
    /* init the curl session */
    curl_handle = curl_easy_init();
    /* set URL to get here */
    curl_easy_setopt(curl_handle, CURLOPT_URL, url);
    /* Switch on full protocol/debug output while testing */
    curl_easy_setopt(curl_handle, CURLOPT_VERBOSE, 0L);
    /* disable progress meter, set to 0L to enable it */
    curl_easy_setopt(curl_handle, CURLOPT_NOPROGRESS, 1L);
    /* send all data to this function  */
    curl_easy_setopt(curl_handle, CURLOPT_WRITEFUNCTION, write_data);
    /* open the file */
    pagefile = fopen(filename, "wb");
    if (pagefile) {
        /* write the page body to this file handle */
        curl_easy_setopt(curl_handle, CURLOPT_WRITEDATA, pagefile);
    } else {
        printf(" Value of errno: %d\n ", errno);
    }
}

long int findSize(const char *file_name) {
    struct stat st; /*declare stat variable*/
    /*get the size using stat()*/
    return (stat(file_name, &st) == 0) ? st.st_size : 0;
}

void download_url(char url[], char filename[]) {
    if (!is_setup) {
        setup(url, filename);
    }
    start_time_stamp = get_time_stamp();
    curl_easy_perform(curl_handle)
}

