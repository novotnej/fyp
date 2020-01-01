fix-permissions:
	HTTPDUSER=`ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
	sudo setfacl -R -m u:"$$HTTPDUSER":rwX -m u:`whoami`:rwX var
	sudo setfacl -dR -m u:"$$HTTPDUSER":rwX -m u:`whoami`:rwX var

docker-build:
	docker build --build-arg USER_ID=`id -u docker` -t novotnej/fyp_web .

docker-web:
	docker run --name fyp_web -d -h=`hostname` --restart=always -v `pwd`:/var/www -p 80:80 novotnej/fyp_web

docker-composer-install:
	docker run --rm -it -v `pwd`:/var/www -v ~/.composer:/home/harambe/.composer profisites/harambe composer install


make-dirs:
	mkdir temp
	chmod 777 temp
	mkdir vendor
	chmod 777 vendor
	mkdir log
	chmod 777 log
