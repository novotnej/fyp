docker-build:
	docker build -t novotnej/fyp_raspi .

docker-run:
	docker run --name fyp_raspi -d -h=`hostname` --device=/dev/ttyUSB0 --restart=always -v `pwd`:/var/www --link some-rabbit novotnej/fyp_raspi

run:
	docker run -v `pwd`:/var/www --link some-rabbit novotnej/fyp_raspi python /var/www/rabbit_listener.py