FROM python:3.6
MAINTAINER Jan Novotny <novotny@profisites.it>

RUN pip install mysql-connector-python
RUN pip install pika
RUN pip install pyserial

ADD ./entrypoint.sh /bin/entrypoint.sh
VOLUME ["/var/www"]
ENTRYPOINT ["bin/entrypoint.sh"]
