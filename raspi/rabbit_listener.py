#!/usr/bin/env python
import pika

RABBIT_HOST = "172.17.0.7"
QUEUE_NAME = "hi"

connection = pika.BlockingConnection(pika.ConnectionParameters(RABBIT_HOST))
channel = connection.channel()
channel.queue_declare(queue=QUEUE_NAME)


def callback(ch, method, properties, body):
    print(" [x] Received %r" % body)


channel.basic_consume(queue=QUEUE_NAME,
                      auto_ack=True,
                      on_message_callback=callback)

print(' [*] Waiting for messages. To exit press CTRL+C')
channel.start_consuming()

connection.close()

print("Goodbye, World!")
