#!/usr/bin/env python
import pika

RABBIT_HOST = "172.17.0.6"
EXCHANGE_NAME = "messages_exchange"
QUEUE_NAME = "messages_queue"
message = "I am a god!"

TARGET = "device-4"

connection = pika.BlockingConnection(pika.ConnectionParameters(RABBIT_HOST))
channel = connection.channel()

channel.exchange_declare(exchange=EXCHANGE_NAME, exchange_type='topic', durable=True)

channel.basic_publish(
    exchange=EXCHANGE_NAME, routing_key=TARGET, body=message)
print(" [x] Sent %r:%r" % (TARGET, message))
connection.close()


print("Goodbye, World!")
