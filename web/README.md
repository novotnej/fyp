#Web server for scenarios 1 and 2
Includes PHP sources for processing the results, launched in CLI

##Installation
- Create folders temp, log in the web folder
- Install a MySQL database
- Copy app/config.local.neon.sample into config.local.neon and change the login details for the database
- In the root directory, run "make docker-build"
- Launch the server by running "make docker-web"

##Running CLI commands
- docker exec web_container_name php /var/www/www/index.php app:calculateAverageFromExperiment

##Docker installation on Windows
- if Docker is restarting and docker logs shows:  standard_init_linux.go:211: exec user process caused "no such file or directory"
-- run dos2unix.exe on docker/entrypoint.sh and docker/vhost.conf
-- rebuild the docker image
-- remove the docker container
-- start the docker container again
- project files have to be placed somewhere outside of the Users home directory, because of reasons. Docker will not mount the volumes required to run the webserver.
-- I suggest putting the project folder into C://www


