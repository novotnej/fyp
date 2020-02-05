#!/usr/bin/env python
import pika

RABBIT_HOST = "172.17.0.7"
QUEUE_NAME = "hi"

connection = pika.BlockingConnection(pika.ConnectionParameters(RABBIT_HOST))
channel = connection.channel()
channel.queue_declare(queue=QUEUE_NAME)

channel.basic_publish(exchange='',
                      routing_key=QUEUE_NAME,
                      body='I am a god!')
print(" [x] Sent 'Hello World!'")

connection.close()


print("Goodbye, World!")
